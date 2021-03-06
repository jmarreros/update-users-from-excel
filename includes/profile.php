<?php
namespace dcms\update\includes;

class Profile{

    public function __construct(){
        add_action( 'show_user_profile', [ $this, 'add_custom_section' ] );
        add_action( 'edit_user_profile', [ $this, 'add_custom_section' ] );

        add_action( 'personal_options_update', [ $this, 'save_custom_section' ] );
        add_action( 'edit_user_profile_update', [ $this, 'save_custom_section' ] );
    }

    // Add custom section
    public function add_custom_section( $user ){
        include_once( DCMS_UPDATE_PATH . '/views/profile.php' );
    }


    // Save custom section
    public function save_custom_section($user_id){
        // validation
        if ( !current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        // Save everyfield
        $fields = get_config_fields();
        foreach ($fields as $key => $value) {
            if ( isset($_POST[$key]) ){
                $field = sanitize_text_field( $_POST[$key] );
                update_user_meta($user_id, $key, $field);
            }
        }

    }
}