<?php

namespace dcms\update\includes;

class Database{
    private $wpdb;
    private $table_name;

    public function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->table_name = $this->wpdb->prefix . 'dcms_update_users';
    }

    // Insert data
    public function insert_data( $row ){
        return $this->wpdb->insert($this->table_name, $row);
    }

    // Read table with current lastmodified date file
    public function select_table( $last_modified = false ){

        if ( ! $last_modified ){
            $last_modified = get_option('dcms_last_modified_file');
        }

        $sql = "SELECT * FROM {$this->table_name} WHERE date_file = {$last_modified}";
        return $this->wpdb->get_results($sql);
    }

    // Select table for last modified date and not date_modified related with product id
    public function select_table_filter($limit = 0){
        // Tmp delete users
        // for ($i=2; $i <=9; $i++) {
        //     wp_delete_user($i);
        // }


        $last_modified  = get_option('dcms_last_modified_file');
        $table_user     = $this->wpdb->prefix."users";

        $sql = "SELECT *, u.id user_id FROM {$this->table_name} uu
                LEFT JOIN {$table_user} u ON uu.number = u.user_login
                WHERE uu.date_file = {$last_modified} AND uu.date_update IS NULL AND uu.excluded = 0";

        if ( $limit > 0 ) $sql .= " LIMIT {$limit}";

        return $this->wpdb->get_results($sql);
    }

    // Update log table
    public function update_item_table($id_table){
        $sql = "UPDATE {$this->table_name} SET date_update = NOW()
                WHERE id = {$id_table}";

        $this->wpdb->query($sql);
    }

    // Update exluded register
    public function exclude_item_table($id_table){
        $sql = "UPDATE {$this->table_name} SET excluded = 1
                WHERE id = {$id_table}";

        $this->wpdb->query($sql);
    }

    // Init activation create table
    public function create_table(){
        $sql = " CREATE TABLE IF NOT EXISTS {$this->table_name} (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `identify` int(10) unsigned DEFAULT NULL,
                    `pin` int(10) unsigned DEFAULT NULL,
                    `number` int(10) unsigned DEFAULT NULL,
                    `reference` varchar(50) DEFAULT NULL,
                    `nif` varchar(50) DEFAULT NULL,
                    `name` varchar(150) DEFAULT NULL,
                    `first_lastname` varchar(150) DEFAULT NULL,
                    `second_lastname` varchar(150) DEFAULT NULL,
                    `birth` datetime DEFAULT NULL,
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
                    -- `id_user` int(10) unsigned DEFAULT NULL,
                    `date_update` datetime DEFAULT NULL,
                    `date_file` int(10) unsigned NOT NULL DEFAULT '0',
                    `excluded` tinyint(1) DEFAULT '0',
                    PRIMARY KEY (`id`)
          )";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Truncate table
    public function truncate_table(){
        $sql = "TRUNCATE TABLE {$this->table_name};";
        $this->wpdb->query($sql);
    }

    // Detelete table on desactivate
    public function drop_table(){
        $sql = "DROP TABLE IF EXISTS {$this->table_name};";
        $this->wpdb->query($sql);
    }
}


// CREATE TABLE IF NOT EXISTS