<?php

namespace dcms\update\includes;

class Database {
	private $wpdb;
	private $table_name;
	private $table_meta;
	private $view_users;

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;

		$this->table_name = $this->wpdb->prefix . 'dcms_update_users';
		$this->view_users = $this->wpdb->prefix . 'dcms_view_users';
		$this->table_meta = $this->wpdb->prefix . 'usermeta';
	}

	// Insert data
	public function insert_data( $row ) {
		return $this->wpdb->insert( $this->table_name, $row );
	}

	// Read table with current lastmodified date file
	public function select_table_resume( $limit = 100 ) {
		$last_modified = get_option( 'dcms_last_modified_file', null );

		$sql = "SELECT * FROM {$this->table_name} WHERE date_file = {$last_modified} AND date_update IS NOT NULL ORDER BY ID DESC LIMIT {$limit}";

		return $this->wpdb->get_results( $sql );
	}


	// Count pending items to import
	public function count_pending_imported() {
		$last_modified = get_option( 'dcms_last_modified_file', null );

		$sql = "SELECT COUNT(id) FROM {$this->table_name} WHERE date_file = {$last_modified} AND date_update IS NULL AND excluded = 0";

		return $this->wpdb->get_var( $sql );
	}


	// Get un-processed users from log table in batch
	public function get_import_users_by_batch( $limit = 0 ) {
		$table_user = $this->wpdb->prefix . "users";

		$sql = "SELECT *, u.id user_id FROM $this->table_name uu
                LEFT JOIN $table_user u ON uu.identify = u.user_login
                WHERE uu.date_update IS NULL AND uu.excluded = 0";

		if ( $limit > 0 ) {
			$sql .= " LIMIT $limit";
		}

		return $this->wpdb->get_results( $sql );
	}

	//Get total import users
	public function get_total_import_users(): int {
		$sql = "SELECT COUNT(*) FROM $this->table_name";

		return $this->wpdb->get_var( $sql );
	}

	// Update date field log table
	public function update_date_item_log_table( $id_table ): void {
		$sql = "UPDATE {$this->table_name} SET date_update = NOW()
                WHERE id = {$id_table}";

		$this->wpdb->query( $sql );
	}

	// Update exclude field log table
	public function update_exclude_item_log_table( $id_table ): void {
		$sql = "UPDATE {$this->table_name} SET excluded = 1
                WHERE id = {$id_table}";

		$this->wpdb->query( $sql );
	}

	// Init activation create table
	public function create_table() {
		$sql = " CREATE TABLE IF NOT EXISTS {$this->table_name} (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
                    `email` varchar(100) DEFAULT NULL,
                    `phone` varchar(50) DEFAULT NULL,
                    `mobile` varchar(50) DEFAULT NULL,
                    `soc_type` varchar(50) DEFAULT NULL,
                    `observation7` varchar(250) DEFAULT NULL,
                    `observation5` varchar(250) DEFAULT NULL,
                    `sub_permit`  varchar(100) DEFAULT NULL,
                    `observation_person`  varchar(100) DEFAULT NULL,
                    `roles` varchar(250) DEFAULT NULL,
                    -- `id_user` int(10) unsigned DEFAULT NULL,
                    `date_update` datetime DEFAULT NULL,
                    `date_file` int(10) unsigned NOT NULL DEFAULT '0',
                    `excluded` tinyint(1) DEFAULT '0',
                    PRIMARY KEY (`id`)
          )";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	// Optimization, create View
	public function create_view() {
		$sql = "CREATE OR REPLACE VIEW {$this->view_users} AS
                SELECT user_id,
                    GROUP_CONCAT(CASE WHEN meta_key = 'identify' THEN meta_value END) as 'identify',
                    GROUP_CONCAT(CASE WHEN meta_key = 'pin' THEN meta_value END) as 'pin',
                    GROUP_CONCAT(CASE WHEN meta_key = 'number' THEN meta_value END) as 'number',
                    GROUP_CONCAT(CASE WHEN meta_key = 'reference' THEN meta_value END) as 'reference',
                    GROUP_CONCAT(CASE WHEN meta_key = 'nif' THEN meta_value END) as 'nif',
                    GROUP_CONCAT(CASE WHEN meta_key = 'first_name' THEN meta_value END) as 'name',
                    GROUP_CONCAT(CASE WHEN meta_key = 'lastname' THEN meta_value END) as 'lastname',
                    GROUP_CONCAT(CASE WHEN meta_key = 'birth' THEN meta_value END) as 'birth',
                    GROUP_CONCAT(CASE WHEN meta_key = 'sub_type' THEN meta_value END) as 'sub_type',
                    GROUP_CONCAT(CASE WHEN meta_key = 'address' THEN meta_value END) as 'address',
                    GROUP_CONCAT(CASE WHEN meta_key = 'postal_code' THEN meta_value END) as 'postal_code',
                    GROUP_CONCAT(CASE WHEN meta_key = 'local' THEN meta_value END) as 'local',
                    GROUP_CONCAT(CASE WHEN meta_key = 'email' THEN meta_value END) as 'email',
                    GROUP_CONCAT(CASE WHEN meta_key = 'phone' THEN meta_value END) as 'phone',
                    GROUP_CONCAT(CASE WHEN meta_key = 'mobile' THEN meta_value END) as 'mobile',
                    GROUP_CONCAT(CASE WHEN meta_key = 'soc_type' THEN meta_value END) as 'soc_type',
                    GROUP_CONCAT(CASE WHEN meta_key = 'observation7' THEN meta_value END) as 'observation7',
                    GROUP_CONCAT(CASE WHEN meta_key = 'observation5' THEN meta_value END) as 'observation5',
                    GROUP_CONCAT(CASE WHEN meta_key = 'sub_permit' THEN meta_value END) as 'sub_permit',
                    GROUP_CONCAT(CASE WHEN meta_key = 'observation_person' THEN meta_value END) as 'observation_person',
                    GROUP_CONCAT(CASE WHEN meta_key = 'roles' THEN meta_value END) as 'roles'
                FROM
                    {$this->table_meta} WHERE
                    meta_key in ('identify', 'pin', 'number', 'reference', 'nif', 'first_name', 'lastname',
                                'birth', 'sub_type', 'address', 'postal_code', 'local', 'email', 'phone', 'mobile',
                                'soc_type', 'observation7', 'observation5', 'sub_permit', 'observation_person', 'roles')
                GROUP BY user_id";

		$result = $this->wpdb->query($sql);
		if ($result === false) {
			error_log('Error al crear vista: ' . $this->wpdb->last_error);
		}
		return $result;
	}

	// Get all user data from view
	public function get_custom_users_with_meta() {
		$sql = "SELECT * FROM {$this->view_users}
                WHERE identify <> '' ORDER BY cast(identify as unsigned)";

		return $this->wpdb->get_results( $sql );
	}


	// Truncate table
	public function truncate_table() {
		$sql = "TRUNCATE TABLE {$this->table_name};";
		$this->wpdb->query( $sql );
	}

	// Detelete table on desactivate
	public function drop_table() {
		$sql = "DROP TABLE IF EXISTS {$this->table_name};";
		$this->wpdb->query( $sql );
	}


	// Check if there are errors
	public function count_excluded_items(): int {
		$sql = "SELECT COUNT(id) FROM {$this->table_name} WHERE excluded = 1";

		return $this->wpdb->get_var( $sql );
	}

	public function update_password_user_import($id_user, $password){
		$user_table = $this->wpdb->prefix . 'users';
		$sql = "UPDATE $user_table SET user_pass = MD5('$password') WHERE ID = $id_user";

		return $this->wpdb->query($sql);
	}

	// Get metadata user
	// public function get_custom_meta_user($id_user){
	//     $table = $this->wpdb->prefix . 'usermeta';

	//     $key_in = Helper::get_config_fields_keys();

	//     $sql = "SELECT meta_key, meta_value FROM {$table} WHERE user_id = {$id_user} AND meta_key in ({$key_in})";

	//     return $this->wpdb->get_results($sql, OBJECT_K);
	// }
}
