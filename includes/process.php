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

        foreach ($items as $item) {
            // Insert or update a user
            $id_user = $this->save_user( $item );

            if ( $id_user ){
                $table->update_item_table($item->id);
            } else{
                $table->exclude_item_table($item->id);
            }

        }
    }

    // Inserte new user
    private function save_user($item){
        $user_data  = [];
        $user_data['display_name']  = $item->name;
        $user_data['user_login']    = $item->number;
        $user_data['first_name']    = $item->name;
        $user_data['last_name']     = $item->first_lastname . ' ' . $item->second_lastname;

        if ( ! is_null($item->user_id) ) {
            // update user
            $user_data['ID'] = $item->user_id;
            $user_data['user_email'] = validate_email_user($item->email, $item->user_id);
        } else {
            // insert user
            $user_data['user_pass'] = md5($item->number);
            $user_data['user_email'] = validate_email_user($item->email);
        }
        $item->email = $user_data['user_email']; // for user meta

        $id_user = wp_insert_user($user_data);

        // Validate
        if ( ! is_wp_error( $id_user ) ) {
            // Add meta data
            $this->save_user_additional_fields($id_user, $item);

        } else {
            error_log("Error al crear o actualizar el usuario con número {$item->number}");
            error_log($id_user->get_error_message());
            return false;
        }

        return $id_user;
    }

    // Update new user
    private function save_user_additional_fields($id_user, $item){
        $fields = get_config_fields();

        foreach ($fields as $key => $value) {
            update_user_meta($id_user, $key, $item->{$key});
        }
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
                    $row[$key] = $item[$value];
                }
            }

            $row['date_file'] =  $last_modified;

            $table->insert_data($row);
        }

    }

}
