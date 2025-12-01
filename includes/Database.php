<?php

namespace dcms\update\includes;

use dcms\update\helpers\Helper;

class Database {
	private \wpdb $wpdb;
	private string $table_tmp_import;
	private string $table_user_data;
	private string $table_meta;
	private string $view_users;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->table_tmp_import = $this->wpdb->prefix . 'dcms_temp_import';
		$this->table_user_data  = $this->wpdb->prefix . 'dcms_user_data';
		$this->view_users       = $this->wpdb->prefix . 'dcms_view_users';
		$this->table_meta       = $this->wpdb->prefix . 'usermeta';
	}

	// Insert data tmp table to import
	public function insert_tmp_import_data( $row ): \mysqli_result|bool|int|null {
		return $this->wpdb->insert( $this->table_tmp_import, $row );
	}

	// Get unprocessed users from the log table in batch
	public function get_import_users_by_batch( $limit = 0, $offset = 0 ): array|object|null {
		$table_user = $this->wpdb->prefix . "users";

		$sql = "SELECT *, u.id user_id FROM $this->table_tmp_import uu
                LEFT JOIN $table_user u ON uu.identify = u.user_login
                WHERE uu.excluded = 0
                ORDER BY uu.id ASC";

		if ( $limit > 0 ) {
			$sql .= $this->wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset );
		}

		return $this->wpdb->get_results( $sql );
	}

	//Get total import users
	public function get_total_import_users(): int {
		$sql = "SELECT COUNT(*) FROM $this->table_tmp_import";

		return $this->wpdb->get_var( $sql );
	}


	// Init activation creates table
	public function create_tables(): void {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$common_fields = "
                    `identify` int(10) unsigned DEFAULT NULL,
                    `pin` varchar(50) DEFAULT NULL,
                    `number` int(10) unsigned DEFAULT NULL,
                    `reference` varchar(50) DEFAULT NULL,
                    `nif` varchar(50) DEFAULT NULL,
                    `name` varchar(150) DEFAULT NULL,
                    `lastname` varchar(150) DEFAULT NULL,
                    `birth` varchar(50) DEFAULT NULL,
                    `sub_type` varchar(150) DEFAULT NULL,
                    `address` varchar(250) DEFAULT NULL,
                    `postal_code` varchar(50) DEFAULT NULL,
                    `local` varchar(100) DEFAULT NULL,
                    `country` varchar(100) DEFAULT NULL,
                    `ayuntamiento` varchar(100) DEFAULT NULL,   
                    `zone` varchar(100) DEFAULT NULL,
                    `email` varchar(100) DEFAULT NULL,
                    `phone` varchar(50) DEFAULT NULL,
                    `mobile` varchar(50) DEFAULT NULL,
                    `soc_type` varchar(50) DEFAULT NULL,
                    `observation7` varchar(250) DEFAULT NULL,
                    `observation5` varchar(250) DEFAULT NULL,
                    `observation10` varchar(250) DEFAULT NULL,
                    `observation11` varchar(250) DEFAULT NULL,
                    `observation19` varchar(250) DEFAULT NULL,
                    `sub_permit`  varchar(100) DEFAULT NULL,
                    `observation_person`  varchar(100) DEFAULT NULL,
                    `date_register` datetime DEFAULT NULL,
                    `roles` varchar(250) DEFAULT NULL";


		// Create a temp import table
		$sql_tmp_import = " CREATE TABLE IF NOT EXISTS {$this->table_tmp_import} (
     				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    $common_fields,
                    `date_update` datetime DEFAULT NULL,
                    `user_id` bigint(20) DEFAULT NULL,
                    `excluded` tinyint(1) DEFAULT '0',
                    PRIMARY KEY (`id`)
          )";

		dbDelta( $sql_tmp_import );

		// Create a user data table
		$sql_user_data = " CREATE TABLE IF NOT EXISTS $this->table_user_data (
     				`user_id` bigint(20) unsigned NOT NULL,
                    $common_fields,
                    PRIMARY KEY (`user_id`)
          )";

		dbDelta( $sql_user_data );
	}

	// Get all user data from view
	public function get_custom_users_with_meta() {
		$sql = "SELECT * FROM $this->table_user_data
				WHERE identify <> '' ORDER BY cast(identify as unsigned)";

		return $this->wpdb->get_results( $sql );
	}


	// Truncate table
	public function truncate_table_import(): void {
		$sql = "TRUNCATE TABLE $this->table_tmp_import";
		$this->wpdb->query( $sql );
	}

	// Delete table on desactivate
	public function drop_tables(): void {
		$sql = "DROP TABLE IF EXISTS $this->table_tmp_import";
		$this->wpdb->query( $sql );

//		$sql = "DROP TABLE IF EXISTS $this->table_user_data";
//		$this->wpdb->query( $sql );
	}


	// Check if there are errors
	public function count_excluded_items(): int {
		$sql = "SELECT COUNT(id) FROM $this->table_tmp_import WHERE excluded = 1";

		return $this->wpdb->get_var( $sql );
	}

	public function update_password_user_import( $id_user, $password ): \mysqli_result|bool|int|null {
		$user_table = $this->wpdb->prefix . 'users';
		$sql        = "UPDATE $user_table SET user_pass = MD5('$password') WHERE ID = $id_user";

		return $this->wpdb->query( $sql );
	}


	// Truncate user data table
	public function truncate_table_user_data(): void {
		$sql = "TRUNCATE TABLE $this->table_user_data";
		$this->wpdb->query( $sql );
	}


	// Insert data user data
	public function insert_data_user_data( $user_data ): \mysqli_result|bool|int|null {
		return $this->wpdb->insert( $this->table_user_data, $user_data );
	}

	// Insert or update user data
	public function insert_or_update_user_data( $id_user, $user_data ): \mysqli_result|bool|int|null {
		$user_data['user_id'] = $id_user;

		$table = $this->table_user_data;

		$fields         = Helper::get_config_fields();
		$all_field_keys = array_keys( $fields );
		$default_data   = array_fill_keys( $all_field_keys, null );
		$user_data      = array_merge( $default_data, $user_data );

		// Prepara las columnas y los marcadores de posición para la parte INSERT
		$columns      = '`' . implode( '`, `', array_keys( $user_data ) ) . '`';
		$placeholders = implode( ', ', array_fill( 0, count( $user_data ), '%s' ) );

		// Prepara la parte ON DUPLICATE KEY UPDATE
		$update_pairs = [];
		foreach ( $user_data as $key => $value ) {
			if ( $key !== 'user_id' ) { // No es necesario actualizar la clave primaria
				$update_pairs[] = "`$key` = VALUES(`$key`)";
			}
		}
		$update_clause = implode( ', ', $update_pairs );

		// Construye la consulta final
		$sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)
	            ON DUPLICATE KEY UPDATE $update_clause";


		// Prepara y ejecuta la consulta
		$query = $this->wpdb->prepare( $sql, array_values( $user_data ) );

		error_log(print_r('Consulta',true));
		error_log(print_r($query,true));

		return $this->wpdb->query( $query );
	}

	// Delete user data
	public function delete_user_data( $id_user ): \mysqli_result|bool|int|null {
		$sql = $this->wpdb->prepare( "DELETE FROM $this->table_user_data WHERE user_id = %d", $id_user );

		return $this->wpdb->query( $sql );
	}

	public function delete_user_not_imported(): void {
		$users_table       = $this->wpdb->users;
		$usermeta_table    = $this->wpdb->usermeta;
		$user_data_table   = $this->table_user_data;
		$tmp_import_table  = $this->table_tmp_import;
		$capabilities_key  = $this->wpdb->prefix . 'capabilities';
		$temp_delete_table = 'temp_users_to_delete';

		// 1. Crear una tabla temporal para almacenar los IDs de los usuarios a eliminar.
		$this->wpdb->query( "CREATE TEMPORARY TABLE {$temp_delete_table} (user_id BIGINT(20) NOT NULL, PRIMARY KEY (user_id))" );

		// 2. Identificar y guardar los IDs de los usuarios que no son administradores y no están en la importación.
		$sql_select_ids = $this->wpdb->prepare(
			"INSERT INTO {$temp_delete_table} (user_id)
         SELECT u.ID
         FROM {$users_table} u
         LEFT JOIN {$usermeta_table} um ON u.ID = um.user_id AND um.meta_key = %s AND um.meta_value LIKE %s
         WHERE
             u.user_login NOT IN (SELECT identify FROM {$tmp_import_table} WHERE identify IS NOT NULL AND identify <> '')
             AND um.user_id IS NULL", // um.user_id será NULL si el usuario no es administrador
			$capabilities_key,
			'%administrator%'
		);

		$this->wpdb->query( $sql_select_ids );

		// 3. Eliminar los registros de las tablas correspondientes usando la tabla temporal.
		// Se usa JOIN para un borrado más eficiente.
		$this->wpdb->query( "DELETE ud FROM {$user_data_table} ud JOIN {$temp_delete_table} tmp ON ud.user_id = tmp.user_id" );
		$this->wpdb->query( "DELETE um FROM {$usermeta_table} um JOIN {$temp_delete_table} tmp ON um.user_id = tmp.user_id" );
		$this->wpdb->query( "DELETE u FROM {$users_table} u JOIN {$temp_delete_table} tmp ON u.ID = tmp.user_id" );

		// 4. Eliminar la tabla temporal.
		$this->wpdb->query( "DROP TEMPORARY TABLE IF EXISTS {$temp_delete_table}" );
	}


	public function synchronize_user_meta(): void {

		$this->truncate_table_user_data();

		$sql = "
		    INSERT INTO $this->table_user_data (
		        user_id, identify, pin, number, reference, nif, name, lastname, birth,
		        sub_type, address, postal_code, local, country, ayuntamiento, zone, email,
		        phone, mobile, soc_type, observation7, observation5, observation10,
		        observation11, observation19, sub_permit, observation_person, date_register, roles
		    )
		    SELECT
		        user_id,
		        GROUP_CONCAT(CASE WHEN meta_key = 'identify' THEN meta_value END) as identify,
		        GROUP_CONCAT(CASE WHEN meta_key = 'pin' THEN meta_value END) as pin,
		        GROUP_CONCAT(CASE WHEN meta_key = 'number' THEN meta_value END) as number,
		        GROUP_CONCAT(CASE WHEN meta_key = 'reference' THEN meta_value END) as reference,
		        GROUP_CONCAT(CASE WHEN meta_key = 'nif' THEN meta_value END) as nif,
		        GROUP_CONCAT(CASE WHEN meta_key = 'first_name' THEN meta_value END) as name,
		        GROUP_CONCAT(CASE WHEN meta_key = 'lastname' THEN meta_value END) as lastname,
		        GROUP_CONCAT(CASE WHEN meta_key = 'birth' THEN meta_value END) as birth,
		        GROUP_CONCAT(CASE WHEN meta_key = 'sub_type' THEN meta_value END) as sub_type,
		        GROUP_CONCAT(CASE WHEN meta_key = 'address' THEN meta_value END) as address,
		        GROUP_CONCAT(CASE WHEN meta_key = 'postal_code' THEN meta_value END) as postal_code,
		        GROUP_CONCAT(CASE WHEN meta_key = 'local' THEN meta_value END) as local,
		        GROUP_CONCAT(CASE WHEN meta_key = 'country' THEN meta_value END) as country,
		        GROUP_CONCAT(CASE WHEN meta_key = 'ayuntamiento' THEN meta_value END) as ayuntamiento,
		        GROUP_CONCAT(CASE WHEN meta_key = 'zone' THEN meta_value END) as zone,
		        GROUP_CONCAT(CASE WHEN meta_key = 'email' THEN meta_value END) as email,
		        GROUP_CONCAT(CASE WHEN meta_key = 'phone' THEN meta_value END) as phone,
		        GROUP_CONCAT(CASE WHEN meta_key = 'mobile' THEN meta_value END) as mobile,
		        GROUP_CONCAT(CASE WHEN meta_key = 'soc_type' THEN meta_value END) as soc_type,
		        GROUP_CONCAT(CASE WHEN meta_key = 'observation7' THEN meta_value END) as observation7,
		        GROUP_CONCAT(CASE WHEN meta_key = 'observation5' THEN meta_value END) as observation5,
		        GROUP_CONCAT(CASE WHEN meta_key = 'observation10' THEN meta_value END) as observation10,
		        GROUP_CONCAT(CASE WHEN meta_key = 'observation11' THEN meta_value END) as observation11,
		        GROUP_CONCAT(CASE WHEN meta_key = 'observation19' THEN meta_value END) as observation19,
		        GROUP_CONCAT(CASE WHEN meta_key = 'sub_permit' THEN meta_value END) as sub_permit,
		        GROUP_CONCAT(CASE WHEN meta_key = 'observation_person' THEN meta_value END) as observation_person,
		        COALESCE(NULLIF(GROUP_CONCAT(CASE WHEN meta_key = 'date_register' THEN meta_value END), ''), NULL) as date_register,
		        GROUP_CONCAT(CASE WHEN meta_key = 'roles' THEN meta_value END) as roles
		    FROM
		        $this->table_meta
		    WHERE
		        meta_key IN (
		            'identify', 'pin', 'number', 'reference', 'nif', 'first_name', 'lastname', 'birth',
		            'sub_type', 'address', 'postal_code', 'local', 'country', 'ayuntamiento', 'zone',
		            'email', 'phone', 'mobile', 'soc_type', 'observation7', 'observation5',
		            'observation10', 'observation11', 'observation19', 'sub_permit', 'observation_person',
		            'date_register', 'roles'
		        )
		    GROUP BY user_id
		    HAVING MAX(CASE WHEN meta_key = 'number' THEN meta_value END) <> ''";

		$this->wpdb->query( $sql );
	}
}


// Optimization, create View
//	public function create_view() {
//		$sql = "CREATE OR REPLACE VIEW {$this->view_users} AS
//                SELECT user_id,
//                    GROUP_CONCAT(CASE WHEN meta_key = 'identify' THEN meta_value END) as 'identify',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'pin' THEN meta_value END) as 'pin',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'number' THEN meta_value END) as 'number',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'reference' THEN meta_value END) as 'reference',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'nif' THEN meta_value END) as 'nif',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'first_name' THEN meta_value END) as 'name',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'lastname' THEN meta_value END) as 'lastname',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'birth' THEN meta_value END) as 'birth',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'sub_type' THEN meta_value END) as 'sub_type',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'address' THEN meta_value END) as 'address',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'postal_code' THEN meta_value END) as 'postal_code',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'local' THEN meta_value END) as 'local',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'email' THEN meta_value END) as 'email',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'phone' THEN meta_value END) as 'phone',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'mobile' THEN meta_value END) as 'mobile',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'soc_type' THEN meta_value END) as 'soc_type',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'observation7' THEN meta_value END) as 'observation7',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'observation5' THEN meta_value END) as 'observation5',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'sub_permit' THEN meta_value END) as 'sub_permit',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'observation_person' THEN meta_value END) as 'observation_person',
//                    GROUP_CONCAT(CASE WHEN meta_key = 'roles' THEN meta_value END) as 'roles'
//                FROM
//                    {$this->table_meta} WHERE
//                    meta_key in ('identify', 'pin', 'number', 'reference', 'nif', 'first_name', 'lastname',
//                                'birth', 'sub_type', 'address', 'postal_code', 'local', 'email', 'phone', 'mobile',
//                                'soc_type', 'observation7', 'observation5', 'sub_permit', 'observation_person', 'roles')
//                GROUP BY user_id";
//
//		$result = $this->wpdb->query( $sql );
//		if ( $result === false ) {
//			error_log( 'Error al crear vista: ' . $this->wpdb->last_error );
//		}
//
//		return $result;
//	}