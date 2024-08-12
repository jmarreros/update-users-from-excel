<?php

namespace dcms\update\includes;

class Enqueue{

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts_backend' ] );
	}

	// Backend scripts
	public function register_scripts_backend() :void{

		wp_register_script( 'update-users-script',
			DCMS_UPDATE_URL . '/assets/script.js',
			[ 'jquery'],
			DCMS_UPDATE_VERSION,
			true );

		wp_register_style( 'update-users-style',
			DCMS_UPDATE_URL . '/assets/style.css',
			[],
			DCMS_UPDATE_VERSION );

		wp_localize_script('update-users-script','update_users_vars',[
			'ajaxurl'=>admin_url('admin-ajax.php'),
			'ajaxnonce' => wp_create_nonce('update-users-nonce'),
		]);
	}

}