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
            },
            success: function (res) {
                $('.process-info').html('<span>' + res.message + '</span>');
                process_upload(1);
            }
        });

    });


    // Click process button
    $('#process-upload-ajax').click(function (e) {
        e.preventDefault();
        process_upload(1);
    });

    // Process every step
    function process_upload(step, total = null) {

        $.ajax({
            url: update_users_vars.ajaxurl,
            type: 'post',
            data: {
                action: 'dcms_process_batch_ajax',
                nonce: update_users_vars.ajaxnonce,
                total,
                step,
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 0) {
                    $('.process-info').addClass('processing').html(`<span>Procesados ${res.count} de ${res.total}
                                            <br> Lote: ${res.step} de ${Math.round(res.total / res.batch)}</span>`);
                    process_upload(res.step, res.total)
                } else {
                    $('.process-info').removeClass('processing').html('<span>Finalizado</span>');
                }
            }

        });
    }

})(jQuery);