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
			'sub_type'           => 'Tipo de Abono', // Define Rol: Tipo Abono
			'address'            => 'Domicilio Completo',
			'country'            => 'País',
			'postal_code'        => 'Código Postal',
			'local'              => 'Localidad',
			'zone'               => 'ID Zona Abono',
			'ayuntamiento'       => 'Ayuntamiento',
			'email'              => 'E-MAIL',
			'phone'              => 'Teléfono',
			'mobile'             => 'Teléfono Móvil',
			'soc_type'           => 'Tipo de Socio', // Define Rol: Tipo de Socio
			'observation7'       => 'Observa 7', // Define Rol indirectamente - Asistencia partidos
			'observation5'       => 'Observa 5',
			'observation10'      => 'Observa 10',
			'observation11'      => 'Observa 11', // Define Rol: Localización
			'observation19'      => 'Observa 19',
			'sub_permit'         => 'Permiso Abono',
			'observation_person' => 'Observaciones Persona', // Define Role: Antigüedad
			'date_register'      => 'Fecha Alta',
			'roles'              => 'Roles', // Consolidado de roles separados por comas
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

