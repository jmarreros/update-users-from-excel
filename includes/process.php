<?php

namespace dcms\update\includes;

use dcms\update\includes\Database;
use dcms\update\includes\Readfile;

class Process{
    public function __construct(){
        add_action( 'admin_post_process_form', [$this, 'process_force_update'] );
    }

    // Manual process update - with redirection
    public function process_force_update(){
        $this->process_update(true);
    }

    // Automatic process update
    public function process_update( $redirection = false ){
        $file = new Readfile();

        // Validation
        if ( ! $file->file_exists() ) {
            exit_process(0, $redirection);
            error_log('Excel File does not exists');
        }

        $last_modified =  $file->file_has_changed();

        // Validate if the file has changed, then insert into database
        if ( $last_modified >= get_option('dcms_last_modified_file') ){

            $this->rows_into_table($file, $last_modified);
            update_option('dcms_last_modified_file', $last_modified );
        }

        // update users in batch process
        $this->update_users(DCMS_COUNT_BATCH_PROCESS);

        exit_process(1, $redirection);

    }

    // Update products stock
    private function update_users($count){

        $table = new Database();

        $items = $table->select_table_filter($count);

        // TODO
        // - Obtener el user_id, si existe será actualización
        // - Sino existe será un usuario nuevo

        foreach ($items as $item) {

            // Insert or update a user
            $id_user = $this->insert_new_user( $item );

            if ( $id_user ){
                $table->update_item_table($item->id);
            } else{
                $table->exclude_item_table($item->id);
            }

        }
    }

    // Inserte new user
    private function insert_new_user($item){
        $user_data  = [];
        $user_data['display_name'] = $item->name;
        $user_data['user_login'] = $item->number;

        if ( ! is_null($item->user_id) ) {
            // update user
            $user_data['ID'] = $item->user_id;
            $user_data['user_email'] = validate_email_user($item->email, $item->user_id);
        } else {
            // insert user
            $user_data['user_pass'] = md5($item->number);
            $user_data['user_email'] = validate_email_user($item->email);
        }

        $id_user = wp_insert_user($user_data);

        // Validate
        if ( ! is_wp_error( $id_user ) ) {
            // Add meta data
            error_log(print_r($id_user,true));

        } else {
            error_log("Error al crear o actualizar el usuario con número {$item->number}");
            error_log($id_user->get_error_message());
            return false;
        }

        return $id_user;
    }

    // Update new user
    private function update_user($item){

    }


    // Insert data rows into custom table
    private function rows_into_table($file, $last_modified){

        $data = $file->get_data_from_file();

        // Validate get data from file
        if ( ! $data ) {
            error_log("No data or incorrect sheet");
            return false;
        };

        $headers_ids = $file->get_headers_ids();
        $config_required_fields = get_config_required_fields();

        // Validation required fields
        foreach ($config_required_fields as $key => $value) {
            if ( $headers_ids[$key] < 0 ) {
                error_log("Error Field: {$key} is required");
                return false;
            }
        }

        // Data base
        $table = new Database();

        // Clear data
        $table->truncate_table();

        foreach ($data as $key => $item) {
            if ( $key == 0 ) continue; // Exclude first line

            $row = [];

            foreach ($headers_ids as $key => $value) {
                if ( ! empty($item[$value]) ){

                    if ( $key == 'birth' ){ // for dates
                        $time = strtotime($item[$value]);
                        $newformat = date('Y-m-d',$time);
                        $row[$key] = $newformat;
                    } else {
                        $row[$key] = $item[$value];
                    }

                }
            }

            $row['date_file'] =  $last_modified;

            $table->insert_data($row);
        }

    }

}



// // Get the items to work with in batch process
// $items = $table->select_table_filter($count);

// error_log(Date("h:i:sa").' - Actualizaremos '. $count.' registros');

// foreach ($items as $item) {

//     // Get the product object
//     $product = wc_get_product($item->post_id);

//     // Validate only simple products
//     if ( $product->get_type() == 'simple'){
//         $price = $product->get_price();
//         $stock = $product->get_stock_quantity();

//         // If price has changed
//         if ( ! is_null($item->price) && $price !== $item->price){
//             $this->update_product_price($product, $item->price);
//         }

//         // If stock has changed
//         if ( $stock !== $item->stock ){
//             wc_update_product_stock($product, $item->stock);
//         }

//         // Update table log
//         $table->update_item_table($item->id);

//     } else {
//         // Exclude item because is not simple product
//         $table->exclude_item_table($item->id);
//     }

// }