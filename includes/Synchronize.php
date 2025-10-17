<?php


namespace dcms\update\includes;

use dcms\update\helpers\Helper;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class for synchronizing WordPress users
 */
class Synchronize {

	public function __construct() {
		add_action( 'admin_post_dcms_synchronize_users', [ $this, 'process_synchronize_all_users' ] );
	}

	#[NoReturn]
	public function process_synchronize_all_users(): void {

		// Verify nonce
		if ( ! isset( $_POST['dcms_sync_users_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dcms_sync_users_nonce'] ) ), 'dcms_sync_users_action_sync' ) ) {
			wp_die( 'Nonce verification failed', 'Error', [ 'response' => 403 ] );
		}

		$db = new Database();
		$db->synchronize_user_meta();

		// Redirect back to the admin page with a success message
		wp_redirect( admin_url( 'edit.php?post_type=events_sporting&page=update-users-excel&tab=advanced&sync=success' ) );
		exit;
	}

}


//		$db = new Database();
//		$db->truncate_table_user_data();
//
//		// Get all users wordpress
//		$users = get_users();
//
//		// Loop through each user and update meta data
//		foreach ( $users as $user ) {
//			$user_id = $user->ID;
//
//			// Get all user meta for the user
//			$user_meta = get_user_meta( $user_id );
//
//			// Validate required fields
//			$required_fields = [ 'pin', 'number' ];
//
//			// Check if required fields are present
//			$missing_required = false;
//			foreach ( $required_fields as $key) {
//				if ( ! isset( $user_meta[ $key ] ) || empty( $user_meta[ $key ][0] ) ) {
//					$missing_required = true;
//					break;
//				}
//			}
//
//			if ( $missing_required ) {
//				continue; // Skip this user if any required field is missing
//			}
//
//			// Valid fields metadata user
//			$fields = Helper::get_config_fields();
//
//			// Prepare user data array
//			$user_data = [];
//			foreach ( $fields as $key => $value ) {
//				if ( isset( $user_meta[ $key ] ) ) {
//					$user_data[ $key ] = $user_meta[ $key ][0];
//				}
//			}
//
//			// Add user ID to the data array
//			$user_data['user_id'] = $user_id;
//
//			// Insert user data into custom table
//			$db->insert_data_user_data( $user_data );
//		}
