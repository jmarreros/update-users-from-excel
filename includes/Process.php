<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;

class Process {

	private string $path_file = '';

	public function __construct() {
		// Process upload file ajax
		add_action( 'wp_ajax_dcms_ajax_add_file', [ $this, 'upload_file_ajax' ] );
		add_action( 'wp_ajax_dcms_process_batch_ajax', [ $this, 'process_batch_ajax' ] );
	}

	public function create_file_path(): void {
		$upload_dir = wp_upload_dir();

		$content_directory = $upload_dir['basedir'] . DCMS_UPDATE_DIRECTORY_UPLOAD;
		if ( ! is_dir( $content_directory ) ) {
			wp_mkdir_p( $content_directory );
		}

		$this->path_file = $content_directory . DCMS_UPDATE_FILE_NAME_IMPORT;
	}


	// Automatic process import users, create or update
	public function process_import_data(): void {
		$db    = new Database();
		$items = $db->get_import_users_by_batch( DCMS_UPDATE_COUNT_BATCH_PROCESS );

		add_filter( 'send_email_change_email', '__return_false' );

		foreach ( $items as $item ) {
			// Insert or update a user and metadata
			$id_user = $this->save_import_user( $item );

			// Insert or update custom user data table
			if ( $id_user ) {
				$user_data = $this->get_user_data( $item );
				$db->insert_or_update_user_data( $id_user, $user_data );
			}
		}
		add_filter( 'send_email_change_email', '__return_true' );
	}

	// Inserte new user or update existing user
	private function save_import_user( $item ): int {

		// General data for wp_users
		$user_data                 = [];
		$user_data['display_name'] = $item->name;
		$user_data['user_login']   = $item->identify;
		$user_data['first_name']   = $item->name;
		$user_data['last_name']    = $item->lastname;

		$password = str_pad( $item->pin, 4, "0", STR_PAD_LEFT );

		$id_user = $item->user_id;

		if ( ! is_null( $id_user ) ) {
			// update wp_users
			$user_data['ID']         = $id_user;
			$user_data['user_email'] = Helper::validate_email_user( $item->email, $id_user );
			$item->email             = $user_data['user_email']; // update item email for user meta

			$id_user = wp_update_user( $user_data );

			$db = new Database();
			$db->update_password_user_import( $id_user, $password );

		} else {
			// insert wp_users
			$user_data['user_pass']  = $password;
			$user_data['user_email'] = Helper::validate_email_user( $item->email );
			$item->email             = $user_data['user_email']; // update item email for user meta

			$id_user = wp_insert_user( $user_data );
		}

		// Validate
		if ( ! is_wp_error( $id_user ) ) {

			// Add user meta data wp_usermeta
			$this->save_user_additional_fields( $id_user, $item );

		} else {
			error_log( "Error al crear o actualizar el usuario con número {$item->number}" );
			error_log( $id_user->get_error_message() );

			return 0;
		}


		return $id_user;
	}


	private function get_user_data( $item ): array {
		$fields    = Helper::get_config_fields();
		$user_data = [];
		foreach ( $fields as $key => $value ) {
			if ( ! is_null( $item->{$key} ) ) { // Validate for a value for updating
				$user_data[ $key ] = $item->{$key};
			}
		}

		return $user_data;
	}

	// Update new user meta data
	private function save_user_additional_fields( $id_user, $item ): void {
		$fields = Helper::get_config_fields();

		foreach ( $fields as $key => $value ) {
			if ( ! is_null( $item->{$key} ) ) { // Validate for a value for updating
				update_user_meta( $id_user, $key, $item->{$key} );
			}

			if ( $key === 'roles' ) {
				UserRoles::update_custom_roles( $id_user, $item->roles );
			}
		}
	}


	// Upload file ajax
	public function upload_file_ajax(): void {
		$res = [];

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'update-users-nonce' ) ) {
			error_log( 'Error de Nonce!' );

			return;
		}

		if ( isset( $_FILES['file'] ) && count( $_FILES['file'] ) ) {

			$name_file = $_FILES['file']['name'];
			$tmp_name  = $_FILES['file']['tmp_name'];

			$this->validate_extension_file( $name_file );
			$this->create_file_path();

			if ( move_uploaded_file( $tmp_name, $this->path_file ) ) {

				$this->data_file_into_table();

				$res = [
					'status'  => 1,
					'message' => "El archivo se agregó correctamente... procesando..."
				];
			}

		} else {
			$res = [
				'status'  => 0,
				'message' => "Existe un error en la subida del archivo"
			];
		}

		wp_send_json( $res );
	}

	// Extension file validation
	private function validate_extension_file( $name_file ): void {
		$path_parts       = pathinfo( $name_file );
		$ext              = $path_parts['extension'];
		$allow_extensions = [ 'xls', 'xlsx' ];

		if ( ! in_array( $ext, $allow_extensions ) ) {
			$res = [
				'status'  => 0,
				'message' => "Extensión de archivo no permitida"
			];

			wp_send_json( $res );
		}

	}


	// Insert data rows from file into custom DB table
	private function data_file_into_table(): void {
		$file = new Readfile( $this->path_file );

		$data = $file->get_data_from_file();

		// Validate get data from file
		if ( ! $data ) {
			error_log( "No data or incorrect sheet" );
		};

		$headers_ids            = $file->get_headers_ids();
		$config_required_fields = Helper::get_config_required_fields();

		// Validation required fields
		foreach ( $config_required_fields as $key => $value ) {
			if ( $headers_ids[ $key ] < 0 ) {
				error_log( "Error Field: {$key} is required" );

				return;
			}
		}

		// Clear data table
		$db = new Database();
		$db->truncate_table_import();

		foreach ( $data as $key => $item ) {

			// Exclude first line
			if ( $key == 0 ) {
				continue;
			}

			$row = [];
			foreach ( $headers_ids as $header_key => $value ) {
				if ( isset( $item[ $value ] ) && strlen( $item[ $value ] ) > 0 ) {
					$row[ $header_key ] = $item[ $value ];
				}
			}

			// Insert data
			$db->insert_tmp_import_data( $row );
		}
	}

	// Process upload file ajax
	public function process_batch_ajax(): void {
		$batch        = DCMS_UPDATE_COUNT_BATCH_PROCESS;
		$total        = $_REQUEST['total'] ?? false;
		$step         = $_REQUEST['step'] ?? 0;
		$count        = $step * $batch;
		$status       = 0;
		$count_errors = 0;

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'update-users-nonce' ) ) {
			error_log( 'Error de Nonce!' );

			return;
		}

		$step ++;

		// Get the total
		if ( ! $total ) {
			$total = $this->get_total_import_users();
		}

		// Comprobamos la finalización
		if ( $count > $total ) {
			$status       = 1;
			$count_errors = $this->count_excluded_items();
		}

		$this->process_import_data();

		// Construimos la respuesta
		$res = [
			'status'       => $status,
			'step'         => $step,
			'count'        => $count,
			'batch'        => $batch,
			'total'        => $total,
			'count_errors' => $count_errors,
		];

		echo json_encode( $res );
		wp_die();
	}

	private function count_excluded_items(): int {
		$db = new Database();

		return $db->count_excluded_items();
	}

	private function get_total_import_users(): int {
		$db = new Database();

		return $db->get_total_import_users();
	}

}



// Only update if the user doesn't sent pin
//			$pin_sent = get_user_meta( $item->user_id, DCMS_PIN_SENT, true );
//			if ( ! $pin_sent ) {
//				$user_data['user_email'] = Helper::validate_email_user( $item->email, $item->user_id );
//			}
//
//			$item->email = ! $pin_sent ? $user_data['user_email'] : null; // update item email for user meta