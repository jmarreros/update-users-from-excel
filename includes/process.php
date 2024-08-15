<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;

class Process {

	private string $path_file = '';

	public function __construct() {
		add_action( 'admin_post_reset_log', [ $this, 'process_reset_log' ] );

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

		$this->rows_into_table();

		$db    = new Database();
		$items = $db->select_table_filter( DCMS_UPDATE_COUNT_BATCH_PROCESS );

		add_filter( 'send_email_change_email', '__return_false' );

		foreach ( $items as $item ) {
			// Insert or update a user
			$id_user = $this->save_import_user( $item );

			// Update log table
			if ( $id_user ) {
				$db->update_date_item_log_table( $item->id );
			} else {
				$db->update_exclude_item_log_table( $item->id );
			}
		}
		add_filter( 'send_email_change_email', '__return_true' );
	}


	// Inserte new user
	private function save_import_user( $item ): int {

		// general data wp_users
		$user_data                 = [];
		$user_data['display_name'] = $item->name;
		$user_data['user_login']   = $item->identify;
		$user_data['first_name']   = $item->name;
		$user_data['last_name']    = $item->lastname;

		$id_user = $item->user_id;

		if ( ! is_null( $id_user ) ) {
			// update wp_users
			$user_data['ID'] = $id_user;

			$user_data['user_email'] = Helper::validate_email_user( $item->email );
			$item->email             = $user_data['user_email']; // update item email for user meta


			$id_user = wp_update_user( $user_data );

		} else {
			// insert wp_users
			$user_data['user_pass']  = $item->pin;
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

	// Update new user
	private function save_user_additional_fields( $id_user, $item ): void {
		$fields = Helper::get_config_fields();

		foreach ( $fields as $key => $value ) {
			if ( ! is_null( $item->{$key} ) ) { // Validate for a value for updating
				update_user_meta( $id_user, $key, $item->{$key} );
			}
		}
	}


	// Insert data rows from file into custom DB table
	private function rows_into_table(): void {
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
		$db->truncate_table();

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

			$row['date_file'] = 0;

			// Insert data
			$db->insert_data( $row );
		}
	}

	// Reset process
	public function process_reset_log(): void {
		$db = new Database();
		$db->truncate_table();

		update_option( 'dcms_last_modified_file', 0 );
		wp_redirect( $_SERVER['HTTP_REFERER'] );
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
				$res = [
					'status'  => 1,
					'message' => "El archivo se agregó correctamente"
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


	// Process upload file ajax
	public function process_batch_ajax(): void {
		$batch  = DCMS_UPDATE_COUNT_BATCH_PROCESS;
		$total  = $_REQUEST['total'] ?? false;
		$step   = $_REQUEST['step'] ?? 0;
		$count  = $step * $batch;
		$status = 0;

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'update-users-nonce' ) ) {
			error_log( 'Error de Nonce!' );

			return;
		}

		// Procesamos la información
		sleep( 0.25 );
		error_log( "step: " . $step . " - count: " . $count );
		// ----

		$step ++;

		// Get the total
		if ( ! $total ) {
			$total = 10000;
		}

		// Comprobamos la finalización
		if ( $count > $total ) {
			$status = 1;
		}

		// Construimos la respuesta
		$res = [
			'status' => $status,
			'step'   => $step,
			'count'  => $count,
			'total'  => $total,
		];

		echo json_encode( $res );
		wp_die();
	}

}



// Only update if the user doesn't sent pin
//			$pin_sent = get_user_meta( $item->user_id, DCMS_PIN_SENT, true );
//			if ( ! $pin_sent ) {
//				$user_data['user_email'] = Helper::validate_email_user( $item->email, $item->user_id );
//			}
//
//			$item->email = ! $pin_sent ? $user_data['user_email'] : null; // update item email for user meta