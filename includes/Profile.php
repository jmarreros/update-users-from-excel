<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;

class Profile {

	public function __construct() {
		add_action( 'show_user_profile', [ $this, 'add_custom_section' ] );
		add_action( 'edit_user_profile', [ $this, 'add_custom_section' ] );

		add_action( 'personal_options_update', [ $this, 'save_custom_section' ], 100 );
		add_action( 'edit_user_profile_update', [ $this, 'save_custom_section' ], 100 );
	}

	// Add custom section
	public function add_custom_section( $user ) {
		include_once( DCMS_UPDATE_PATH . '/views/profile.php' );
	}


	// Save custom section
	public function save_custom_section( $user_id ): void {
		// validation
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		// Save every field
		$fields = Helper::get_config_fields();
		$user_meta = [];
		foreach ( $fields as $key => $value ) {
			if ( isset( $_POST[ $key ] ) ) {
				$field = sanitize_text_field( $_POST[ $key ] );
				$user_meta[ $key ] = $field;
				update_user_meta( $user_id, $key, $field );
			}
		}


		// Get user roles
		$roles = $_POST['dcms_user_roles']??[];
		// Separar por comas, eliminar espacios y convertir a minúsculas
		$roles = array_map( function ( $role ) {
			return strtolower( str_replace( ' ', '_', trim( $role ) ) );
		}, $roles );
		// array to string
		$roles = implode( ',', $roles );

		// For custom user data table
		if ( ! empty( $user_meta ) ) {
			$db = new Database();
			$db->insert_or_update_user_data( $user_id, $user_meta );
		}

	}
}