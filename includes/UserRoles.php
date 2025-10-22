<?php

namespace dcms\update\includes;

/**
 * Class for creating a dashboard submenu
 */
class UserRoles {
	public function __construct() {
	}


	public static function update_custom_roles( $id_user, $item ): string {

		// Validate if the constant DCMS_CUSTOMAREA_ROLES from the custom-area-sporting plugin is defined
		if ( ! defined( 'DCMS_CUSTOMAREA_ROLES' ) ) {
			return '';
		}

		// Recolectar roles en un array, permitiendo valores separados por comas en cada campo
		$roles_arr = self::add_roles_to_item( $item );

		$user = new \WP_User( $id_user );

		// Remove all custom roles
		foreach ( DCMS_CUSTOMAREA_ROLES as $role ) {
			if ( $user->has_cap( $role ) ) {
				$user->remove_role( $role );
			}
		}

		if ( ! empty( $roles_arr ) ) {
			foreach ( $roles_arr as $role ) {
				$user->add_role( $role );
			}
		}

		return implode( ', ', $roles_arr );
	}

	// Agrega roles desde varios campos al campo de roles y devuelve un array de roles únicos y validados
	// Se puede enviar roles en la columna roles (acepta varios roles separado por comas), observaciones_persona, tipo_abono, tipo_de_socio, observa_11
	// Para el observa7 es un % que define el cambio de rol DCMS_CUSTOMAREA_ROLES_LEALTAD, si es >= 90 es nivel_rojiblanco, >= 100 es nivel_dorado y viceversa, si es <90 es nivel_general
	public static function add_roles_to_item( $item ): array {
		$roles_arr = [];

		// Procesar la columna roles ya que puede contener varios roles separados por comas
		if ( ! empty( $item->roles ) ) {
			$parts = array_map( 'trim', explode( ',', (string) $item->roles ) );
			foreach ( $parts as $p ) {
				if ( $p !== '' ) {
					$roles_arr[] = $p;
				}
			}
		}

		// Campos adicionales para agregar a los roles
		$extra_fields = [ 'observation_person', 'sub_type', 'soc_type', 'observation11' ];
		foreach ( $extra_fields as $prop ) {
			$val = trim( $item->{$prop} ?? '' );
			if ( $val === '' ) {
				continue;
			}
			$roles_arr[] = $val;
		}

		$roles_arr = array_unique( $roles_arr );
		$roles_arr = array_map( function ( $role ) {
			return strtolower( str_replace( ' ', '_', trim( $role ) ) );
		}, $roles_arr );


		// Procesar el campo observa7 para asignar roles de lealtad según el porcentaje
		$observa7 = trim( $item->observation7 ?? '' );

		if ( $observa7 !== ''){
			// Puede ser un número o número con %, solo quedarse con el número, ejemplo 100, 100%, 85, 85%
			$observa7 = rtrim( $observa7, '%' );
			if ( is_numeric( $observa7 ) ) {

				// Quitar los roles de lealtad si existen en el array
				$roles_arr = array_filter( $roles_arr, function ( $role ) {
					return ! in_array( $role, DCMS_CUSTOMAREA_ROLES_LEALTAD );
				} );

				$observa7 = floatval( $observa7 );
				if ( $observa7 >= 100 ) {
					$roles_arr[] = 'nivel_dorado';
				} elseif ( $observa7 >= 90 ) {
					$roles_arr[] = 'nivel_rojiblanco';
				} else {
					$roles_arr[] = 'nivel_general';
				}
			}
		}

		return array_intersect( $roles_arr, DCMS_CUSTOMAREA_ROLES);
	}


	// Validate roles and build roles in a string format
	public static function get_valid_custom_roles( $current_roles ): string {
		$final_roles = '';

		if ( defined( 'DCMS_CUSTOMAREA_ROLES' ) ) {
			$intercepted_roles = array_intersect( $current_roles, DCMS_CUSTOMAREA_ROLES ?? [] );
			$final_roles       = implode( ', ', $intercepted_roles );
		}

		return $final_roles;
	}

}