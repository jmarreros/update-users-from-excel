<?php

use dcms\update\includes\Database;

$db = new Database();
$rows = $db->select_table();
$pending = $db->select_table_filter();
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

    table.dcms-table tr td:nth-child(4){
        font-weight:bold;
    }

    table tr.updated{
        background-color:#F4FFED;
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

</style>

<section class="msg-top">
<span><?php echo DCMS_COUNT_BATCH_PROCESS . __(' Items', 'dcms-update-users-excel') ?></span>
<span><?php echo __('every ', 'dcms-update-users-excel') . DCMS_INTERVAL_SECONDS . "s" ?></span>
-
<strong><?php echo __('Pending items: ', 'dcms-update-users-excel') . count($pending) ?></strong>
-
<strong><?php echo __('Last modified Excel file process: ', 'dcms-update-users-excel') . date('d/m/Y - H:m:s', $last_modified_file) ?></strong>
</section>

<?php
    $fields = get_config_fields();
?>

<table class="dcms-table">
    <tr>
        <th>#</th>
        <?php
        $i = 0;
        foreach($fields as $key => $field) {
            if ( $i<6 ) echo "<th>" . $field . "</th>";
            $i++;
        }
        ?>
        <th class="internal">Update</th>
        <th class="internal">Excluded</th>
    </tr>
<?php foreach ($rows as $key => $items):  ?>
    <tr <?php if ( ! is_null($items->date_update) ) echo "class='updated'"?>>
    <?php
        $i = 0;
        foreach($items as $key => $item) {
            if ( $i<=6 ) echo "<td>" . $item . "</td>";
            $i++;
        }
        ?>
        <td class="divider"><?= $items->date_update ?></td>
        <td><?= $items->excluded ?></td>
    </tr>
<?php endforeach; ?>
</table>
