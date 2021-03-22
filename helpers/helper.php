<?php

namespace dcms\update\helpers;

final class Helper{

    public static function get_config_fields(){
        return [
            'identify'  => 'Identificativo',
            'pin'       => 'PIN', // Password Column
            'number'    => 'Número', // Login column
            'reference' => 'Referencia',
            'nif'       => 'NIF',
            'name'      => 'Nombre',
            'lastname'    => 'Apellidos',
            'birth'     => 'Fecha Nacimiento',
            'sub_type'  => 'Tipo de Abono',
            'address'   => 'Domicilio completo',
            'postal_code'   => 'Código Postal',
            'local'     => 'Localidad',
            'email'     => 'E-Mail',
            'phone'     => 'Teléfono',
            'mobile'    => 'Móvil',
            'soc_type'  => 'Tipo de socio',
            'observation7'   => 'Observa 7',
            'observation5'   => 'Observa 5',
            'sub_permit'=> 'Permiso Abono'
        ];
    }

    public static function get_config_required_fields(){
        return [
            'pin'       => 'PIN',
            'number'    => 'Número',
            'reference' => 'Referencia',
            'name'      => 'Nombre',
        ];
    }

    public static function get_style_header_cells(){
        return [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFE55',
                ],
            ],
        ];
    }

    public static function get_config_fields_keys(){
        return '"' . implode('","', array_keys(self::get_config_fields())) . '"';
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

