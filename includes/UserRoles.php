<?php

namespace dcms\update\includes;

/**
 * Class for creating a dashboard submenu
 */
class UserRoles{
	public function __construct() {
	}

	public static function update_custom_roles($id_user, $current_roles): string{
		// Validate if the constant DCMS_CUSTOMAREA_ROLES from the custom-area-sporting plugin is defined
		if ( ! defined( 'DCMS_CUSTOMAREA_ROLES' ) ) {
			return '';
		}

		$custom_roles = DCMS_CUSTOMAREA_ROLES;

		$user = new \WP_User( $id_user );

		// Remove all custom roles
		foreach ( $custom_roles as $role ) {
			if ( $user->has_cap( $role ) ) {
				$user->remove_role( $role );
			}
		}

		$final_roles = [];
		if ( ! empty( $current_roles ) ) {
			// Convert to lowercase and add underscore in spaces
			$roles = explode( ',', $current_roles );
			$roles = array_map( function ( $role ) {
				return strtolower( str_replace( ' ', '_', trim( $role ) ) );
			}, $roles );

			// Add new custom roles
			foreach ( $roles as $role ) {
				if ( ! empty( $role ) && in_array( $role, $custom_roles ) ) {
					$user->add_role( $role );
					$final_roles[] = $role;
				} else {
					error_log( "El rol {$role} no es válido para el usuario {$id_user}" );
				}
			}
		}

		return implode( ',', $final_roles );
	}



}