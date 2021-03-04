<?php

// Columns
function get_config_fields(){
    $config_fields = [
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
        'email'     => ' Email',
        'phone'     => 'Phone',
        'mobile'    => 'Mobile',
        'observation'   => 'Observation'
    ];
    return $config_fields;
}


// Exit process
function exit_process($process_ok = 1, $redirection){
    $cad = (strpos(DCMS_SUBMENU,'?')) ? "&" : '?';
    if ( $redirection ) wp_redirect( admin_url( DCMS_SUBMENU . $cad . 'page=update-users-excel&process='.$process_ok) );
    exit();
}

