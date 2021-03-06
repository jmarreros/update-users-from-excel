<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! current_user_can( 'manage_options' ) ) return; // only administrator

$plugin_tabs = [];
$plugin_tabs['log'] = "Log";
$plugin_tabs['settings'] = __("Settings", 'dcms-update-users-excel');
$plugin_tabs['advanced'] = __("Avanzado", 'dcms-update-users-excel');

// Get Current tab
$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'log';
?>

<div class="wrap">

<h1><?php _e('Users List', 'dcms-update-users-excel') ?></h1>

<?php
plugin_options_tabs($current_tab, $plugin_tabs);

switch ($current_tab){
    case 'log':
        tab_log();
        break;
    case 'settings':
        tab_settings();
        break;
    case 'advanced':
        tab_advanced();
        break;
}

?>
</div>


<?php
// - Tab General

function tab_log(){
    include_once('list-users.php');
}

// - Tab Settings
function tab_settings(){ ?>
        <form action="options.php" method="post">
            <?php
                if ( isset( $_GET['settings-updated'] ) ) {
                    add_settings_error( 'dcms_messages', 'dcms_messages', __( 'Settings Saved', 'dcms-update-users-excel' ), 'updated' );
                }
                settings_errors( 'dcms_messages' );

                settings_fields('dcms_user_excel_options_bd');
                do_settings_sections('dcms_usexcel_sfields');
                submit_button();
            ?>
        </form>
        <?php
}

// - Tab Advanced
function tab_advanced(){
    ?>
    <h2><?php _e('Force update', 'dcms-update-users-excel') ?></h2>
    <p class="description"><?php _e('Dont wait for the cron process', 'dcms-update-users-excel')   ?></p>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
        <input type="hidden" name="action" value="process_form">
        <input type="submit" class="button button-primary" value="<?php _e('Force update', 'dcms-update-users-excel') ?>">
    </form>
    <hr>
    <h2><?php _e('Reset log', 'dcms-update-users-excel') ?></h2>
    <p class="description"><?php _e('Reset file date and remove temporal log data', 'dcms-update-users-excel')   ?></p>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
        <input type="hidden" name="action" value="reset_log">
        <input type="submit" class="button button-primary" value="<?php _e('Reset', 'dcms-update-users-excel') ?>">
    </form>

    <?php
}


// Create tabs and activate current tab
function plugin_options_tabs($current_tab, $plugin_tabs) {
    $cad = (strpos(DCMS_UPDATE_SUBMENU,'?')) ? "&" : '?';

    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $plugin_tabs as $tab_key => $tab_caption ) {
        $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
        echo "<a class='nav-tab " . $active . "' href='".admin_url( DCMS_UPDATE_SUBMENU . $cad . "page=update-users-excel&tab=" . $tab_key )."'>" . $tab_caption . '</a>';
    }
    echo '</h2>';
}

