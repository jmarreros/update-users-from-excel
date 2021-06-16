<?php

namespace dcms\update\includes;

use dcms\update\includes\Database;

class Plugin{

    public function __construct(){
        // Activation/Desactivation
        register_activation_hook( DCMS_UPDATE_BASE_NAME, [ $this, 'dcms_activation_plugin'] );
        register_deactivation_hook( DCMS_UPDATE_BASE_NAME, [ $this, 'dcms_deactivation_plugin'] );
    }

    // Activate plugin - create options and database table
    public function dcms_activation_plugin(){

        // Default
        if ( ! get_option('dcms_last_modified_file') ){
            update_option('dcms_last_modified_file', 0);
        }

        // Create table
        $db = new Database();
        $db->create_table();
        $db->create_view(); //optimization

        // // Create cron
        if( ! wp_next_scheduled( 'dcms_cron_hook' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'dcms_interval', 'dcms_cron_hook' );
        }

    }

    // Deactivate plugin
    public function dcms_deactivation_plugin(){
        $db = new Database();
        $db->drop_table();

        update_option('dcms_last_modified_file', 0);

        wp_clear_scheduled_hook( 'dcms_cron_hook' );
    }

}
