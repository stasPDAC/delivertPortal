<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
 $page = 'users';
$sub_page = 'clients';
global $icons;
global $user_type;
if($user_type != 1){
    error404();
}
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg){
    case 'createOk':
        $msg = 'מנהל חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'deleteOk':
        $msg = 'מנהל נמחק בהצחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}
$clients = getAllClients();
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg .'</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">דיירים</div>
    </div>

    <div class="line"></div>
    <table id="projects_table" class="table table-striped">
        <thead>
        <tr>
            <th>מס סידורי</th>
            <th>שם דייר</th>
            <th>מספר נייד</th>
            <th>דוא"ל</th>
            <th>פרויקט</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($clients as $client) : ?>
            <tr <?= $client['user_activity'] == '1' ? '' : 'class="disable_user"' ?>>
                <td><?= $client['i_serial_number'] != '' ? $client['i_serial_number'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $client['st_user_name'] != '' ? $client['st_user_name'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $client['st_phone_first'] != '' ? $client['st_phone_first'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $client['st_mail'] != '' ? $client['st_mail'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $client['st_project_name'] != '' ? '<a href="project.php?id=' . $client['i_project_id'] . '">' . $client['st_project_name'] . '</a>' : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td class="actions">
                    <a title="קישור לדוח" href="report.php?id=<?= $client['i_serial_number'] ?>"><?= $icons['report'] ?></a>
                    <a title="עריכת דייר" href="clientEditor.php?edit=<?= $client['client_id'] ?>"><?= $icons['edit'] ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="js/clinets.js"></script>
<?php include_once 'includes/footer.php'; ?>
