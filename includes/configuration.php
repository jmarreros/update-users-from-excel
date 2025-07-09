<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;

class Configuration {

	public function __construct() {
		add_action( 'admin_init', [ $this, 'init_configuration' ] );
	}

	// Register sections and fields
	public function init_configuration() {
		register_setting( 'dcms_user_excel_options_bd', 'dcms_user_excel_options' );

		// Excel Fields section
		add_settings_section( 'dcms_usexcel_section_excel',
			__( 'Excel Structure', 'dcms-update-users-excel' ),
			[ $this, 'dcms_section_cb' ],
			'dcms_usexcel_sfields' );

		add_settings_field( 'dcms_usexcel_sheet_field',
			__( 'Sheet Number', 'dcms-update-users-excel' ),
			[ $this, 'dcms_section_input_cb' ],
			'dcms_usexcel_sfields',
			'dcms_usexcel_section_excel',
			[
				'label_for'   => 'dcms_usexcel_sheet_field',
				'description' => __( 'Enter a sheet page number', 'dcms-update-users-excel' ),
				'required'    => true
			]
		);

		$config_fields = Helper::get_config_fields();

		foreach ( $config_fields as $key => $value ) {
			$field_name = "dcms_usexcel_{$key}_field";
			$field_msg  = "$value column name";
			add_settings_field( $field_name,
				__( $field_msg, 'dcms-update-users-excel' ),
				[ $this, 'dcms_section_input_cb' ],
				'dcms_usexcel_sfields',
				'dcms_usexcel_section_excel',
				[ 'label_for' => $field_name ]
			);

		}

	}

	// Callback section
	public function dcms_section_cb() {
		echo '<hr/>';
	}

	// Callback input field callback
	public function dcms_section_input_cb( $args ) {
		$id    = $args['label_for'];
		$req   = isset( $args['required'] ) ? 'required' : '';
		$class = isset( $args['class'] ) ? "class='" . $args['class'] . "'" : '';
		$desc  = isset( $args['description'] ) ? $args['description'] : '';

		$options = get_option( 'dcms_user_excel_options' );
		$val     = isset( $options[ $id ] ) ? $options[ $id ] : '';

		printf( "<input id='%s' name='dcms_user_excel_options[%s]' type='text' value='%s' %s %s>",
			$id, $id, $val, $req, $class );

		if ( $desc ) {
			printf( "<p class='description'>%s</p> ", $desc );
		}

	}


	public function dcms_section_check_cb( $args ) {
		$id      = $args['label_for'];
		$desc    = isset( $args['description'] ) ? $args['description'] : '';
		$options = get_option( 'dcms_user_excel_options' );
		$val     = checked( isset( $options[ $id ] ), true, false );

		printf( "<input id='%s' name='dcms_user_excel_options[%s]' type='checkbox' %s > %s",
			$id, $id, $val, $desc );

	}


	// Inputs fields sanitation and validation
	public function dcms_validate_cb( $input ) {
		$output = array();

		// Sanitization
		foreach ( $input as $key => $value ) {
			if ( isset( $input[ $key ] ) ) {
				$output[ $key ] = strip_tags( $input[ $key ] );
			}
		}

		// Validation
		$path_file = $output['dcms_usexcel_input_file'];

		// Validate file path
		if ( $this->validate_path_file( $path_file ) ) {
			// validate columns
			$this->validate_columns_file( $path_file, $output );
		}

		return $output;
	}


	// Validate that the file exists
	private function validate_path_file( $path_file ) {
		if ( ! file_exists( $path_file ) ) {
			add_settings_error( 'dcms_messages', 'dcms_file_error', __( 'File doesn\'t exists', 'dcms-update-users-excel' ), 'error' );

			return false;
		}

		return true;
	}

	// Validate that columns' file exists
	private function validate_columns_file( $path_file, $output ) {

		// Get input column values
		$sheet_number = $output['dcms_usexcel_sheet_field'];

		$columns       = [];
		$config_fields = Helper::get_config_fields();

		foreach ( $config_fields as $key => $value ) {
			$name              = "dcms_usexcel_{$key}_field";
			$columns[ $value ] = $output[ $name ];
		}

		// Read file and validate sheet_number headers
		$readfile = new Readfile( $path_file );
		$headers  = $readfile->get_header( $sheet_number );
		$sheets   = $readfile->get_sheets();

		if ( ! $headers ) {
			add_settings_error( 'dcms_messages', 'dcms_file_error', __( 'Headers columns in .xlsx file doesn\'t exists', 'dcms-update-users-excel' ), 'error' );
			add_settings_error( 'dcms_messages', 'dcms_file_error', __( 'Try this sheets number: ', 'dcms-update-users-excel' ) . $sheets, 'error' );


			return false;
		}

		// Validate each header
		foreach ( $columns as $key => $column ) {
			if ( ! empty( $column ) && ! in_array( $column, $headers ) ) {
				add_settings_error( 'dcms_messages', 'dcms_file_error', __( $key . ' column "' . $column . '" doesn\'t exists  in .xls file', 'dcms-update-users-excel' ), 'error' );
			}
		}

	}

}

