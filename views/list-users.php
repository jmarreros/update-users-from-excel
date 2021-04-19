<?php

use dcms\update\includes\Database;
use dcms\update\helpers\Helper;

$db = new Database();
$rows = $db->select_table_resume(DCMS_UPDATE_COUNT_BATCH_PROCESS);
$pending = $db->count_pending_imported();
$last_modified_file = get_option('dcms_last_modified_file',0);
?>
<style>
    table.dcms-table{
        width:100%;
        background-color:white;
        border-spacing: 0;
    }

    table.dcms-table th,
    table.dcms-table td{
        text-align:left;
        padding:6px;
        border-bottom:1px solid #ccc;
    }

    table.dcms-table tr th:first-child,
    table.dcms-table tr td:first-child{
        width:40px;
        background-color:#aaa;
    }

    table.dcms-table th,
    table.dcms-table tr th:first-child{
        background-color:#23282d;
        color:white;
    }

    table.dcms-table tr td:nth-child(1),
    table.dcms-table tr td:nth-child(2){
        font-weight:bold;
    }

    section.msg-top{
        padding:10px;
        background-color:#ccc;
    }

    table.dcms-table th.internal{
        background-color: #757575;
    }

    table.dcms-table td.divider{
        border-left:1px solid #aaa;
    }

    .frm-export{
        float:right;
        margin-top:10px;
    }

</style>

<form method="post" id="frm-export" class="frm-export" action="<?php echo admin_url( 'admin-post.php' ) ?>" >
        <input type="hidden" name="action" value="process_export_users_imported">
        <button type="submit" class="btn-export button button-primary"><?php _e('Export all', 'dcms-update-users-excel') ?></button>
</form>

<h2><?php _e('Latest imported', 'dcms-update-users-excel') ?></h2>

<section class="msg-top">
<span><?php echo DCMS_UPDATE_COUNT_BATCH_PROCESS . __(' Items', 'dcms-update-users-excel') ?></span>
<span><?php echo __('every ', 'dcms-update-users-excel') . DCMS_UPDATE_INTERVAL_SECONDS . "s" ?></span>
-
<strong><?php echo __('Pending items: ', 'dcms-update-users-excel') . $pending ?></strong>
-
<strong><?php echo __('Last modified Excel file process: ', 'dcms-update-users-excel') . date('d/m/Y - H:m:s', $last_modified_file) ?></strong>
</section>

<?php
    $fields = Helper::get_config_fields();
?>

<table class="dcms-table">
    <tr>
        <?php
        $i = 0;
        foreach($fields as $key => $field) {
            if ( $i<6 ) echo "<th>" . $field . "</th>";
            $i++;
        }
        ?>
        <th>Email</th>
        <th class="internal">Update</th>
        <th class="internal">Excluded</th>
    </tr>
<?php foreach ($rows as $key => $items):  ?>
    <tr>
    <?php
        $i = 1;
        foreach($items as $key => $item) {
            if ( $key != 'id'){
                if ( $i<=6 ) echo "<td>" . $item . "</td>";
                $i++;
            }
        }
        ?>
        <td><?= strtolower($items->email) ?></td>
        <td class="divider"><?= $items->date_update ?></td>
        <td><?= $items->excluded ?></td>
    </tr>
<?php endforeach; ?>
</table>
