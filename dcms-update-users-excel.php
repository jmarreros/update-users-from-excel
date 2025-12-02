<?php
/*
Plugin Name: Sporting update users Excel
Plugin URI: https://webservi.es
Description: Update users from an excel file
Version: 2.5.6
Author: Webservi.es
Author URI: https://decodecms.com
Text Domain: dcms-update-users-excel
Domain Path: languages
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

namespace dcms\update;

require __DIR__ . '/vendor/autoload.php';

use dcms\update\includes\Plugin;
use dcms\update\includes\UserRoles;
use dcms\update\includes\Submenu;
use dcms\update\includes\Configuration;
use dcms\update\includes\Enqueue;
use dcms\update\includes\Process;
use dcms\update\includes\Profile;
use dcms\update\includes\Export;
use dcms\update\includes\Synchronize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin class to handle settings constants and loading files
 **/
final class Loader {

	// Define all the constants we need
	public function define_constants(): void {
		define( 'DCMS_UPDATE_VERSION', '2.5.6' );
		define( 'DCMS_UPDATE_PATH', plugin_dir_path( __FILE__ ) );
		define( 'DCMS_UPDATE_URL', plugin_dir_url( __FILE__ ) );
		define( 'DCMS_UPDATE_BASE_NAME', plugin_basename( __FILE__ ) );
		define( 'DCMS_UPDATE_SUBMENU', 'edit.php?post_type=events_sporting' );
		define( 'DCMS_UPDATE_COUNT_BATCH_PROCESS', 500 ); // Amount of registers to update every time
		define( 'DCMS_UPDATE_INTERVAL_SECONDS', 900 ); // For cron taks
		define( 'DCMS_UPDATE_DIRECTORY_UPLOAD', '/file-users-import/' );
		define( 'DCMS_UPDATE_FILE_NAME_IMPORT', 'list-users-import.xlsx' );

		if ( ! defined( 'DCMS_PIN_SENT' ) ) {
			define( 'DCMS_PIN_SENT', 'dcms-pin-sent' );
		}
	}

	// Load tex domain
	public function load_domain(): void {
		add_action( 'plugins_loaded', function () {
			$path_languages = dirname( DCMS_UPDATE_BASE_NAME ) . '/languages/';
			load_plugin_textdomain( 'dcms-update-users-excel', false, $path_languages );
		} );
	}

	// Add link to plugin list
	public function add_link_plugin(): void {
		add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
			$cad = ( strpos( DCMS_UPDATE_SUBMENU, '?' ) ) ? "&" : '?';

			return array_merge( array(
				'<a href="' . esc_url( admin_url( DCMS_UPDATE_SUBMENU . $cad . 'page=update-users-excel' ) ) . '">' . __( 'Settings', 'dcms-update-users-excel' ) . '</a>'
			), $links );
		} );
	}

	// Initialize all
	public function init(): void {
		$this->define_constants();
		$this->load_domain();
		$this->add_link_plugin();
		new Plugin();
		new SubMenu();
		new Configuration();
		new Process();
		new Profile();
		new Export();
		new Enqueue();
		new Synchronize();
		new UserRoles();
	}

}

$dcms_update_process = new Loader();
$dcms_update_process->init();


