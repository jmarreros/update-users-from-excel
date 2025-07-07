<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;
use dcms\update\libs\SimpleXLSX;

class Readfile {

	private $xlsx;
	private $path_file;

	public function __construct( $path_file ) {
		$options = get_option( 'dcms_user_excel_options' );

		$this->path_file    =  $path_file;
		$this->sheet_number = $options['dcms_usexcel_sheet_field'];

		if ( file_exists( $this->path_file ) ) {
			$this->xlsx = new SimpleXLSX( $this->path_file );
		}
	}


	// Get data from file as array
	public function get_data_from_file() {
		$data = false;
		$data = $this->xlsx->rows( $this->sheet_number );

		return $data;
	}


	// Get header file
	public function get_header( $sheet_number = false ) {

		if ( $sheet_number === false ) {
			$options      = get_option( 'dcms_user_excel_options' ); // Get sheet number from database
			$sheet_number = $options['dcms_usexcel_sheet_field'];
		}

		$rows = $this->xlsx->rows( $sheet_number );

		if ( ! isset( $rows[0] ) ) {
			return false;
		}

		return $rows[0];
	}

	// Get ids by column name in an array, column -1 if not exits
	public function get_headers_ids() {

		$config_fields = Helper::get_config_fields();
		$headers       = $this->get_header( $this->sheet_number );
		$options       = get_option( 'dcms_user_excel_options' );
		$headers_id    = [];

		foreach ( $config_fields as $key => $value ) {

			$text = $options["dcms_usexcel_${key}_field"];

			$found              = array_search( $text, $headers );
			$headers_id[ $key ] = ( ! empty( $text ) && $found !== false ) ? $found : - 1;
		}

		error_log(print_r('Los Headers',true));
		error_log(print_r($headers_id,true));

		return $headers_id;
	}

	// Is update file, validate if the file has changed
//	public function file_has_changed() {
//		$modified_bd   = get_option( 'dcms_last_modified_file', 0 );
//		$modified_file = filemtime( $this->path_file );
//
//		if ( $modified_file > $modified_bd ) {
//			return $modified_file;
//		}
//
//		return false;
//	}

	// Public get_sheets file
	public function get_sheets() {
		$sheets = array_keys( $this->xlsx->sheetNames() );

		return implode( ',', $sheets );

	}
}


//empty( $path_file ) ? $options['dcms_usexcel_input_file'] :