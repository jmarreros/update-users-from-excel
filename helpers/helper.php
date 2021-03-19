<?php

namespace dcms\update\helpers;

final class Helper{

    public static function get_config_fields(){
        return [
            'identify'  => 'Identify',
            'pin'       => 'PIN', // Password Column
            'number'    => 'Number', // Login column
            'reference' => 'Reference',
            'nif'       => 'NIF',
            'name'      => 'Name',
            'first_lastname'    => 'First Lastname',
            'second_lastname'   => 'Second Lastname',
            'birth'     => 'Birth date',
            'sub_type'  => 'Subscriber Type',
            'address'   => 'Address',
            'postal_code'   => 'Postal Code',
            'local'     => 'Locate',
            'email'     => ' Email',
            'phone'     => 'Phone',
            'mobile'    => 'Mobile',
            'soc_type'  => 'Socio Type',
            'observation7'   => 'Observation 7',
            'observation5'   => 'Observation 5',
            'sub_permit'=> 'Subscription permit'
        ];
    }

    public static function get_config_required_fields(){
        return [
            'pin'       => 'PIN',
            'number'    => 'Number',
            'reference' => 'Reference',
            'name'      => 'Name',
        ];
    }


    // Exit process
    public static function exit_process($process_ok = 1, $redirection){
        $cad = (strpos(DCMS_UPDATE_SUBMENU,'?')) ? "&" : '?';
        if ( $redirection ) wp_redirect( admin_url( DCMS_UPDATE_SUBMENU . $cad . 'page=update-users-excel&process='.$process_ok) );
        exit();
    }

    // Validate email
    public static function validate_email_user($email, $user_id = -1){
        if ( empty( $email) ){
            return  uniqid().'@emailempty.com';
        } else {
            $id = email_exists($email);
            if ( is_int($id) && $user_id != $id ) {
                return uniqid().'@emailexists.com';
            }
        }
        return $email;
    }


}

