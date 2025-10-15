<div class='wrap'>
	<section class="form-container synchronize">
        <?php
        if ( isset( $_GET['sync'] ) && 'success' === $_GET['sync'] ) {
            echo '<div class="updated notice is-dismissible"><p>Sincronización completada correctamente.</p></div>';
        }
        ?>
        <form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post" id="form-sync">
            <p>
                Forzar Sincronización con los usuarios de WordPress en caso de que no se haya completado correctamente una importación.
            </p>
            <?php wp_nonce_field( 'dcms_sync_users_action_sync', 'dcms_sync_users_nonce' ); ?>
            <input type="hidden" name="action" value="dcms_synchronize_users">
            <input class="button button-primary" type="submit" id="submit" value="Forzar Sincronización"/>
        </form>
	</section>
</div>
