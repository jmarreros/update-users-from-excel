<div class='wrap'>

    <section class="form-container export">
        <form action="<?php echo admin_url( 'admin-post.php' ) ?>" method="post" id="form-export">
            <input type="hidden" name="action" value="process_export_users_imported">
            <input class="button button-primary" type="submit" id="submit-export" value="<?php _e( 'Exportar usuarios', 'dcms-update-users-excel' ) ?>"/>
        </form>
    </section>
    <section class="form-container">
        <form action="" enctype="multipart/form-data" method="post" id="form-upload">
            <div>
                <span>Selecciona el archivo xls: </span>
                <input type="file" id="file" name="upload-file"/>
            </div>
            <input class="button button-primary" type="submit" id="submit" value="Enviar archivo"/>
        </form>

    </section>

    <a id='process-upload-ajax' style="display:none;" class='button button-primary' href='#'>Procesar</a>

    <br>

    <div class='process-info'>
    </div>

</div>
