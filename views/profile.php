<br><hr><br>

<style>
.user-email-wrap p.description:after{
    display:block;
    content:'👉 Should be the same email in excel data';
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