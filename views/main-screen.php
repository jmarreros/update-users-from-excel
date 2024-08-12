<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
if ( ! current_user_can( 'manage_options' ) ) {
	return;
} // only administrator

$plugin_tabs             = [];
$plugin_tabs['log']      = "Log";
$plugin_tabs['settings'] = __( "Settings", 'dcms-update-users-excel' );
$plugin_tabs['advanced'] = __( "Avanzado", 'dcms-update-users-excel' );

// Get Current tab
$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'log';
?>

    <div class="wrap">

        <h1><?php _e( 'Users List', 'dcms-update-users-excel' ) ?></h1>

		<?php
		print_tab_selection( $current_tab, $plugin_tabs );

		switch ( $current_tab ) {
			case 'log':
				include_once 'partials/list-users.php';
				break;
			case 'settings':
				include_once 'partials/settings.php';
				break;
			case 'advanced':
				include_once 'partials/advanced.php';
				break;
		}

		?>
    </div>


<?php


// Create tabs and activate current tab
function print_tab_selection( $current_tab, $plugin_tabs ) {
	$cad = ( strpos( DCMS_UPDATE_SUBMENU, '?' ) ) ? "&" : '?';

	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $plugin_tabs as $tab_key => $tab_caption ) {
		$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
		echo "<a class='nav-tab " . $active . "' href='" . admin_url( DCMS_UPDATE_SUBMENU . $cad . "page=update-users-excel&tab=" . $tab_key ) . "'>" . $tab_caption . '</a>';
	}
	echo '</h2>';
}

