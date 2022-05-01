<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'users';
$sub_page = 'admins';
global $icons;

$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg){
    case 'createOk':
        $msg = 'מנהל מערכת חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}
$managers = getAllAdmins();
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg .'</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">מנהלי מערכת</div>
        <a class="btn" href="userEditor.php">הוספת מנהל חדש</a>
    </div>

    <div class="line"></div>
    <table id="projects_table" class="table table-striped">
        <thead>
        <tr>
            <th>שם מנהל מערכת</th>
            <th>מספר נייד</th>
            <th>דוא"ל</th>
            <th>סטטוס</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($managers as $manager) : ?>
            <tr <?= $manager['i_active'] == '1' ? '' : 'class="disable_user"' ?>>
                <td><?= $manager['st_user_name'] != '' ? $manager['st_user_name'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $manager['st_phone_first'] != '' ? $manager['st_phone_first'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $manager['st_mail'] != '' ? $manager['st_mail'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $manager['i_active'] == '1' ? 'פעיל' : 'מבוטל' ?></td>
                <td class="actions">
                    <a title="עריכת מנהל מערכת" href="userEditor.php?edit=<?= $manager['id'] ?>"><?= $icons['edit'] ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="js/admins.js"></script>
<?php include_once 'includes/footer.php'; ?>
