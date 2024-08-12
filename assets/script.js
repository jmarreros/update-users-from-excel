
(function($){

    // Click process button
    $('#process-upload-ajax').click( function(e){
        e.preventDefault();
        process_upload(1);
    });

    // Process every step
    function process_upload(step, total = null) {

        $.ajax({
            url : update_users_vars.ajaxurl,
            type: 'post',
            data: {
                action : 'dcms_process_batch_ajax',
                nonce  : update_users_vars.ajaxnonce,
                total,
                step,
            },
            dataType: 'json',
            success: function(res){
                if ( res.status  === 0){
                    $('.process-info').html(`<strong>Procesados ${res.count} de ${res.total}
                                            <br> Paso: ${res.step}</strong>`);
                    process_upload(res.step, res.total)
                } else {
                    $('.process-info').text('Finalizado');
                }
            }

        });
    }

})(jQuery);