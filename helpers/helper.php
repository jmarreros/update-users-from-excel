<?php

// Columns
function get_config_fields(){
    return [
        'identify'  => 'Identify',
        'pin'       => 'PIN',
        'number'    => 'Number',
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

function get_config_required_fields(){
    return [
        'pin'       => 'PIN',
        'number'    => 'Number',
        'reference' => 'Reference',
        'name'      => 'Name',
    ];
}


// Exit process
function exit_process($process_ok = 1, $redirection){
    $cad = (strpos(DCMS_SUBMENU,'?')) ? "&" : '?';
    if ( $redirection ) wp_redirect( admin_url( DCMS_SUBMENU . $cad . 'page=update-users-excel&tab=advanced&process='.$process_ok) );
    exit();
}

// Validate email
function validate_email_user($email, $user_id = -1){
    if ( empty( $email) ){
        return  uniqid().'@email_empty.com';
    } else {
        $id = email_exists($email);
        if ( is_int($id) && $user_id != $id ) {
            return uniqid().'@email_exists.com';
        }
    }
    return $email;
}

