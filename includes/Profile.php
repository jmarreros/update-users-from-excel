<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;

class Profile {

	public function __construct() {
		add_action( 'show_user_profile', [ $this, 'add_custom_section' ] );
		add_action( 'edit_user_profile', [ $this, 'add_custom_section' ] );

		add_action( 'personal_options_update', [ $this, 'save_custom_section' ], 100, 20 );
		add_action( 'edit_user_profile_update', [ $this, 'save_custom_section' ], 100, 20 );

		add_action ('delete_user', [ $this, 'delete_user_data' ], 10, 1);
	}

	// Add a custom section
	public function add_custom_section( $user ): void {
		include_once( DCMS_UPDATE_PATH . '/views/profile.php' );
	}

	// Save custom section
	public function save_custom_section( $user_id ): void {
		// validation
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		// Save every field
		$fields    = Helper::get_config_fields();
		$user_meta = [];
		foreach ( $fields as $key => $value ) {
			if ( isset( $_POST[ $key ] ) ) {
				$field             = sanitize_text_field( $_POST[ $key ] );
				$user_meta[ $key ] = $field;
				update_user_meta( $user_id, $key, $field );
			}
		}

		// For roles y user data table
		$roles             = $_POST['dcms_user_roles'] ?? [];
		$final_roles      = UserRoles::build_custom_roles( $roles );

		$user_meta['roles'] = $final_roles;

		// Save for custom user data table
		if ( ! empty( $user_meta ) ) {
			$db = new Database();
			$db->insert_or_update_user_data( $user_id, $user_meta );
		}
	}

	// Delete user data on user deletion
	public function delete_user_data( $user_id ): void {
		$db = new Database();
		$db->delete_user_data( $user_id );
	}
}