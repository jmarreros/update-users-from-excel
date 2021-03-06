<br><hr><br>

<style>
.user-email-wrap{
    display:none;
}
</style>

<h3><?php _e('Custom User Data', 'dcms-update-users-excel'); ?></h3>
<table class="form-table">


        <?php
            $fields = get_config_fields();

            foreach ($fields as $key => $value) {
            ?>
            <tr>
                <th>
                    <label for="<?= $key ?>"><?= $value ?></label>
                </th>
                <td>
                    <input type="text" name="<?= $key ?>" id="<?= $key ?>" class="regular-text"
                    value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" />
                </td>
            </tr>
            <?php
            }
        ?>
</table>