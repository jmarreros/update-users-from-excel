<div class='wrap'>

    <section class="form-container">
        <form action="" enctype="multipart/form-data" method="post" id="form-upload">
            <div>
                <span>Selecciona el archivo xls: </span>
                <input type="file" id="file" name="upload-file"/>
            </div>
            <input class="button button-primary" type="submit" id="submit" value="Enviar archivo" />
        </form>

        <div id="message"></div>
    </section>

    <a id='process-upload-ajax' class='button button-primary' href='#'>Procesar</a>
</div>
<hr/>
<div class='process-info'>
</div>