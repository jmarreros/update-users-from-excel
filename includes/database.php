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

        $last_modified = get_option('dcms_last_modified_file');
        $table_postmeta = $this->wpdb->prefix."postmeta";

        $sql = "SELECT us.*, pm.post_id FROM {$this->table_name} us
                INNER JOIN {$table_postmeta} pm ON us.sku = pm.meta_value AND pm.meta_key = '_sku'
                WHERE us.date_file = {$last_modified} AND us.date_update IS NULL AND us.excluded = 0 AND us.state = 1";

        if ( $limit > 0 ) $sql .= " LIMIT {$limit}";

        return $this->wpdb->get_results($sql);
    }

    // Update log table
    public function update_item_table($id_table){
        $sql = "UPDATE {$this->table_name} SET date_update = NOW(), updated = 1
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
                    `email` varchar(100) DEFAULT NULL,
                    `phone` varchar(50) DEFAULT NULL,
                    `mobile` varchar(50) DEFAULT NULL,
                    `observation` varchar(250) DEFAULT NULL,
                    `id_user` int(10) unsigned DEFAULT NULL,
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
}


// CREATE TABLE IF NOT EXISTS