<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'users';
$sub_page = 'inspectors';
global $icons;
global $user_type;
if($user_type != 1){
    error404();
}
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg){
    case 'createOk':
        $msg = 'מבקר חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'deleteOk':
        $msg = 'מבקר נמחק בהצחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}
$inspectors = getAllInspectors();
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg .'</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">מבקרי איכות</div>
        <a class="btn" href="inspectorEditor.php">הוספת מבקר חדש</a>
    </div>

    <div class="line"></div>
    <table id="projects_table" class="table table-striped">
        <thead>
        <tr>
            <th>שם מנהל</th>
            <th>מספר נייד</th>
            <th>דוא"ל</th>
            <th>סטטוס</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($inspectors as $inspector) : ?>
            <tr <?= $inspector['i_active'] == '1' ? '' : 'class="disable_user"' ?>>
                <td><?= $inspector['st_user_name'] != '' ? $inspector['st_user_name'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $inspector['st_phone_first'] != '' ? $inspector['st_phone_first'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $inspector['st_mail'] != '' ? $inspector['st_mail'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $inspector['i_active'] == '1' ? 'פעיל' : 'מבוטל' ?></td>
                <td class="actions">
                    <a title="עריכת מבקר איכות" href="inspectorEditor.php?edit=<?= $inspector['id'] ?>"><?= $icons['edit'] ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="js/inspectors.js"></script>
<?php include_once 'includes/footer.php'; ?>
