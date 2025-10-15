<?php

namespace dcms\update\includes;

use dcms\update\includes\Database;

class Plugin{

    public function __construct(){
        register_activation_hook( DCMS_UPDATE_BASE_NAME, [ $this, 'dcms_activation_plugin'] );
        register_deactivation_hook( DCMS_UPDATE_BASE_NAME, [ $this, 'dcms_deactivation_plugin'] );
    }

    // Activate plugin - create options and database table
    public function dcms_activation_plugin(){
        // Create table
        $db = new Database();
        $db->create_tables();
        $db->create_view(); //optimization
    }

    // Deactivate plugin
    public function dcms_deactivation_plugin(){
        $db = new Database();
        $db->drop_tables();

        update_option('dcms_last_modified_file', 0);

        wp_clear_scheduled_hook( 'dcms_cron_hook' );
    }

}
