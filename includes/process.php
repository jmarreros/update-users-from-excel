<?php

namespace dcms\update\includes;

use dcms\update\includes\Database;
use dcms\update\includes\Readfile;
use dcms\update\helpers\Helper;

class Process{
    public function __construct(){
        add_action( 'admin_post_process_form', [$this, 'process_force_update'] );
        add_action( 'admin_post_reset_log', [$this, 'process_reset_log'] );
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
            Helper::exit_process(0, $redirection);
            error_log('Excel File does not exists');
        }

        $last_modified =  $file->file_has_changed();

        // Validate if the file has changed, then insert into database
        if ( $last_modified >= get_option('dcms_last_modified_file') ){

            $this->rows_into_table($file, $last_modified);
            update_option('dcms_last_modified_file', $last_modified );
        }

        // update users in batch process
        $this->update_users(DCMS_UPDATE_COUNT_BATCH_PROCESS);

        Helper::exit_process(1, $redirection);

    }

    // Update products stock
    private function update_users($count){
        $db = new Database();
        $items = $db->select_table_filter($count);

        foreach ($items as $item) {

            // Insert or update a user
            $id_user = $this->save_user( $item);

            // Update log table
            if ( $id_user ){
                $db->update_item_table($item->id);
            } else{
                $db->exclude_item_table($item->id);
            }

        }
    }

    // Inserte new user
    private function save_user($item){

        // general data wp_users
        $user_data  = [];
        $user_data['display_name']  = $item->name;
        $user_data['user_login']    = $item->identify;
        $user_data['first_name']    = $item->name;
        $user_data['last_name']     = $item->lastname;

        $id_user = $item->user_id;

        if ( ! is_null($id_user) ) {
            // update wp_users
            $user_data['ID'] = $id_user;

            // Only update if the user doesn't sent pin
            $pin_sent = get_user_meta($item->user_id, DCMS_PIN_SENT, true);
            if ( ! $pin_sent ){
                $user_data['user_email'] = Helper::validate_email_user($item->email, $item->user_id);
            }

            $item->email = ! $pin_sent ? $user_data['user_email']: NULL; // update item email for user meta

            $id_user = wp_update_user($user_data);

        } else {
            // insert wp_users
            $user_data['user_pass'] = $item->pin;
            $user_data['user_email'] = Helper::validate_email_user($item->email);
            $item->email = $user_data['user_email']; // update item email for user meta

            $id_user = wp_insert_user($user_data);
        }

        // Validate
        if ( ! is_wp_error( $id_user ) ) {

            // Add user meta data wp_usermeta
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
        $fields = Helper::get_config_fields();

        foreach ($fields as $key => $value) {
            if ( ! is_null( $item->{$key}) ){ // Validate for a value for updating
                update_user_meta($id_user, $key, $item->{$key});
            }
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
        $config_required_fields = Helper::get_config_required_fields();

        // Validation required fields
        foreach ($config_required_fields as $key => $value) {
            if ( $headers_ids[$key] < 0 ) {
                error_log("Error Field: {$key} is required");
                return false;
            }
        }

        // Data base
        $db = new Database();

        // Clear data
        $db->truncate_table();

        foreach ($data as $key => $item) {
            if ( $key == 0 ) continue; // Exclude first line

            $row = [];

            foreach ($headers_ids as $key => $value) {
                if ( ! empty($item[$value]) ){
                    $row[$key] = $item[$value];
                }
            }

            $row['date_file'] =  $last_modified;

            $db->insert_data($row);
        }
    }

    // Reset process
    public function process_reset_log(){
        $db = new Database();
        $db->truncate_table();

        update_option('dcms_last_modified_file', 0); // update wp_option
        Helper::exit_process(1, true);
    }


}
