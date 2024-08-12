<form action="options.php" method="post">
	<?php
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error( 'dcms_messages', 'dcms_messages', __( 'Settings Saved', 'dcms-update-users-excel' ), 'updated' );
	}
	settings_errors( 'dcms_messages' );

	settings_fields( 'dcms_user_excel_options_bd' );
	do_settings_sections( 'dcms_usexcel_sfields' );
	submit_button();
	?>
</form>