<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
if ( ! current_user_can( 'manage_options' ) ) {
    return;
} // only administrator

$plugin_tabs             = [];
$plugin_tabs['upload']   = __( "Importar/Exportar", 'dcms-update-users-excel' );
$plugin_tabs['settings'] = __( "Settings", 'dcms-update-users-excel' );
$plugin_tabs['advanced'] = __( "Avanzado", 'dcms-update-users-excel' );


// Get Current tab
$current_tab = $_GET['tab'] ?? 'upload';
?>

    <div class="wrap">

        <h1><?php _e( 'Gestión de usuarios', 'dcms-update-users-excel' ) ?></h1>

        <?php
        print_tab_selection( $current_tab, $plugin_tabs );

        switch ( $current_tab ) {
            case 'upload':
                wp_enqueue_script( 'update-users-script' );
                wp_enqueue_style( 'update-users-style' );

                include_once 'partials/import.php';
                break;
            case 'settings':
                include_once 'partials/settings.php';
                break;
            case 'advanced':
                wp_enqueue_script( 'update-users-script' );
                wp_enqueue_style( 'update-users-style' );

                include_once 'partials/advanced.php';
                break;
        }

        ?>
    </div>


<?php


// Create tabs and activate current tab
function print_tab_selection( $current_tab, $plugin_tabs ): void {
    $cad = ( strpos( DCMS_UPDATE_SUBMENU, '?' ) ) ? "&" : '?';

    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $plugin_tabs as $tab_key => $tab_caption ) {
        $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
        echo "<a class='nav-tab " . $active . "' href='" . admin_url( DCMS_UPDATE_SUBMENU . $cad . "page=update-users-excel&tab=" . $tab_key ) . "'>" . $tab_caption . '</a>';
    }
    echo '</h2>';
}

