<h2><?php _e( 'Force update', 'dcms-update-users-excel' ) ?></h2>
<p class="description"><?php _e( 'Dont wait for the cron process', 'dcms-update-users-excel' ) ?></p>
<form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
    <input type="hidden" name="action" value="process_form">
    <input type="submit" class="button button-primary"
           value="<?php _e( 'Force update', 'dcms-update-users-excel' ) ?>">
</form>
<hr>
<h2><?php _e( 'Reset log', 'dcms-update-users-excel' ) ?></h2>
<p class="description"><?php _e( 'Reset file date and remove temporal log data', 'dcms-update-users-excel' ) ?></p>
<form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
    <input type="hidden" name="action" value="reset_log">
    <input type="submit" class="button button-primary" value="<?php _e( 'Reset', 'dcms-update-users-excel' ) ?>">
</form>