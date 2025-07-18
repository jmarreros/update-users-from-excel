<?php

namespace dcms\update\includes;

/**
 * Class for creating a dashboard submenu
 */
class Submenu{
    // Constructor
    public function __construct(){
        add_action('admin_menu', [$this, 'register_submenu']);
    }

    // Register submenu
    public function register_submenu(){
        add_submenu_page(
            DCMS_UPDATE_SUBMENU,
            __('Importar Usuarios','dcms-update-users-excel'),
            __('Importar Usuarios','dcms-update-users-excel'),
            'manage_options',
            'update-users-excel',
            [$this, 'submenu_page_callback']
        );
    }

    // Callback, show view
    public function submenu_page_callback(){
        include_once (DCMS_UPDATE_PATH. '/views/main-screen.php');
    }
}