<?php
use dcms\update\helpers\Helper;
?>

<br><hr><br>

<h3><?php _e('Custom User Data', 'dcms-update-users-excel'); ?></h3>
<table class="form-table">


        <?php
            $fields = Helper::get_config_fields();

            // Remover $field de roles ya que lo actualizará el plugin custom-area-sporting
            unset($fields['roles']);

            foreach ($fields as $key => $value) {
            ?>
            <tr>
                <th>
                    <label for="<?= $key ?>"><?= $value ?></label>
                </th>
                <td>
                    <?php
                        $required = $key == 'email' ? 'required': '';
                    ?>
                    <input type="text" name="<?= $key ?>" id="<?= $key ?>" class="regular-text"
                    value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>"
                    <?php echo $required ?> />
                </td>
            </tr>
            <?php
            }
        ?>
</table>

<script>
    // readonly for the email wordpress
    (function( $ ) {
	'use strict';

        $( document ).ready(function() {
            $('.user-email-wrap #email').attr('readonly', true);
        });

    })( jQuery );

</script>