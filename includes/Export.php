<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Class for the operations of plugin
class Export{

    public function __construct(){
        add_action('admin_post_process_export_users_imported', [$this, 'process_export_data_user']);
    }

    // Export data
    public function process_export_data_user(): void {
        // Create an Excel file
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);
        $sheet = $spreadsheet->getActiveSheet();

        // Headers excel
        $fields = Helper::get_config_fields();
        $icol = 1;
        foreach ($fields as $key => $value) {
            $sheet->setCellValueByColumnAndRow($icol, 1, $value);
            $icol++;
        }
        $styleArray = Helper::get_style_header_cells();
        $sheet->getStyle('A1:AB1')->applyFromArray($styleArray);

        // Get all the users
        $db = new Database();

        $users = $db->get_custom_users_with_meta();

        $irow = 2;
        foreach ($users as $user) {
            $icol = 1;

            foreach ($fields as $key => $value) {
                if ( $user->$key <> '' ){
                    $content = $user->$key;

					if ( $key === 'roles'){
						$content = format_string_uppercase( $user->roles );
					}

	                $sheet->setCellValueByColumnAndRow($icol, $irow, $content);
                }
                $icol++;
            }
			
            $irow++;
        }

        // Send excel
        $filename = 'list_users.xlsx';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='. $filename);
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
}