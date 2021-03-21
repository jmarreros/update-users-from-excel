<?php

namespace dcms\update\includes;

use dcms\update\includes\Database;
use dcms\update\helpers\Helper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Class for the operations of plugin
class Export{

    public function __construct(){
        add_action('admin_post_process_export_users_imported', [$this, 'process_export_data_user']);
    }

    // Export data
    public function process_export_data_user(){

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $fields = Helper::get_config_fields();

        $i = 1;
        foreach ($fields as $key => $value) {
            $sheet->setCellValueByColumnAndRow($i, 1, $value);
            $i++;
        }

        $styleArray = Helper::get_style_header_cells();
        $sheet->getStyle('A1:T1')->applyFromArray($styleArray);


        // foreach ($users as $user) {
        //     $user_meta = $db->get_custom_meta_user($user->ID);

        //     // if (isset( $user_meta['identify']->meta_value)){
        //     //     error_log(print_r('Establecido',true));
        //     // }
        //     $tmp = $user_meta['pin']->meta_value;
        //     error_log(print_r($tmp,true));

        // }


        $filename = 'list_users.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='. $filename);
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        wp_die();

        // Get all the users
        // $db = new Database();
        // $users= get_users(['role__not_in' => 'Administrator']);


        // foreach ($users as $user) {
        //     $user_meta = $db->get_custom_meta_user($user->ID);

        //     // if (isset( $user_meta['identify']->meta_value)){
        //     //     error_log(print_r('Establecido',true));
        //     // }
        //     $tmp = $user_meta['pin']->meta_value;
        //     error_log(print_r($tmp,true));

        // }

        // wp_redirect( admin_url(DCMS_UPDATE_SUBMENU).'&page=update-users-excel');
        // wp_die();
    }

    //     $db = new Database();

    //     $spreadsheet = new Spreadsheet();
    //     $writer = new Xlsx($spreadsheet);

    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Headers
    //     $sheet->setCellValue('A1', 'Identificador');
    //     $sheet->setCellValue('B1', 'PIN');
    //     $sheet->setCellValue('C1', 'Correo');
    //     $sheet->setCellValue('D1', 'Fecha');

    //     // Get data from table
    //     $data = $db->select_log_table(0, 'ASC');

    //     $i = 2;
    //     foreach ($data as $row) {
    //         $sheet->setCellValue('A'.$i, $row->identify);
    //         $sheet->setCellValue('B'.$i, $row->pin);
    //         $sheet->setCellValue('C'.$i, $row->email);
    //         $sheet->setCellValue('D'.$i, $row->date);
    //         $i++;
    //     }

    //     $filename = 'pin_sent.xlsx';

    //     header('Content-Type: application/vnd.ms-excel');
    //     header('Content-Disposition: attachment;filename='. $filename);
    //     header('Cache-Control: max-age=0');
    //     $writer->save('php://output');

    //     wp_redirect( admin_url(DCMS_PIN_SUBMENU).'&page=send-pin');
    //     wp_die();

}