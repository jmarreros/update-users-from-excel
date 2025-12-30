(function ($) {

    $('#form-upload').submit(function (e) {
        e.preventDefault();

        const fd = new FormData();
        const files = $('#file')[0].files;

        if (files.length <= 0) {
            alert('Tienes que seleccionar algún archivo');
            return;
        }

        const size = (files[0].size / 1024 / 1024).toFixed(2);
        if (size > 8) {
            alert(`Tu archivo pesa ${size}MB. No puedes subir archivos mayores a 8MB`);
            return;
        }

        fd.append('file', files[0]);
        fd.append('action', 'dcms_ajax_add_file');
        fd.append('nonce', update_users_vars.ajaxnonce);

        $.ajax({
            url: update_users_vars.ajaxurl,
            type: 'post',
            dataType: 'json',
            data: fd,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('.process-info').removeClass('processing').html('<span>Enviando...</span>');
                $('#form-upload').find('input, button').attr('disabled', true);
                $('#submit-export').attr('disabled', true);
            },
            success: function (res) {
                $('.process-info').html('<span>' + res.message + '</span>');
                process_upload(1, null, 0,0);
            },
            complete: function () {
                $('#form-upload').find('input, button').attr('disabled', false);
                $('#submit-export').attr('disabled', false);
            }
        });

    });


    // Click process button
    $('#process-upload-ajax').click(function (e) {
        e.preventDefault();
        process_upload(1, null, 0, 0);
    });

    // Process every step
    function process_upload(step, total = null, last_id = 0, processed = 0) {

        $.ajax({
            url: update_users_vars.ajaxurl,
            type: 'post',
            data: {
                action: 'dcms_process_batch_ajax',
                nonce: update_users_vars.ajaxnonce,
                total,
                step,
                last_id,
                processed,
                delete: $('#delete-users').is(':checked') ? 1 : 0
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 0) {
                    const percentage = Math.round((res.processed / res.total) * 100);

                    $('.process-info').addClass('processing').html(
                        `<span>Procesados ${res.processed} de ${res.total} (${percentage}%)
                    <br> Lote: ${res.step}</span>`
                    );

                    // Llamada recursiva con el nuevo last_id y los procesados
                    process_upload(res.step, res.total, res.last_id, res.processed);
                } else {
                    if (res.count_errors > 0) {
                        $('.process-info').html('<span>Hubo ' + res.count_errors + ' error(es) al realizar la importación</span>');
                    } else {
                        $('.process-info').removeClass('processing').html('<span>Importación finalizada</span>');
                        $('#file').val('');
                    }
                }
            },
            error: function (xhr, status, error) {
                $('.process-info').removeClass('processing').html('<span>Error en el procesamiento: ' + error + '</span>');
                console.error('Error AJAX:', xhr.responseText);
            }
        });
    }


})(jQuery);
