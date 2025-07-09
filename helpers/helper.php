<?php

namespace dcms\update\helpers;

final class Helper {

	public static function get_config_fields(): array {
		return [
			'identify'           => 'Identificativo', // Login column
			'pin'                => 'PIN', // Password Column
			'number'             => 'Numero',
			'reference'          => 'Referencia',
			'nif'                => 'N.I.F.',
			'name'               => 'Nombre',
			'lastname'           => 'Apellidos',
			'birth'              => 'Fecha Nacimiento',
			'sub_type'           => 'Tipo de Abono',
			'address'            => 'Domicilio Completo',
			'postal_code'        => 'Código Postal',
			'local'              => 'Localidad',
			'email'              => 'E-MAIL',
			'phone'              => 'Teléfono',
			'mobile'             => 'Teléfono Móvil',
			'soc_type'           => 'Tipo de Socio',
			'observation7'       => 'Observa 7',
			'observation5'       => 'Observa 5',
			'sub_permit'         => 'Permiso Abono',
			'observation_person' => 'Observaciones Persona',
			'roles'              => 'Roles', // User roles separated by comma
		];
	}

	public static function get_config_required_fields(): array {
		return [
			'pin'       => 'PIN',
			'number'    => 'Numero',
			'reference' => 'Referencia',
			'name'      => 'Nombre',
		];
	}

	public static function get_style_header_cells(): array {
		return [
			'font' => [
				'bold' => true,
			],
			'fill' => [
				'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
				'startColor' => [
					'argb' => 'FFFFFE55',
				],
			],
		];
	}

	// Validate email
	public static function validate_email_user( $email, $user_id = - 1 ): string {

		if ( empty( $email ) || ! is_email( $email ) ) {
			return uniqid() . '@emailtemp.com';
		} else {
			$id = email_exists( $email );
			if ( is_int( $id ) && $user_id != $id ) {
				return uniqid() . '@emailexists.com';
			}
		}

		return $email;
	}
}

