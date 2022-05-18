<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'projects';
global $icons;
global $user_type;
global $domain;
if($user_type != 1 && $user_type != 2 && $user_type != 5){
    error404();
}
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg){
    case 'GroupCreateOk':
        $msg = 'הדיירים החדשים נוספו בהצלחה';
        break;
    case 'createOk':
        $msg = 'דייר חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    case 'mail':
        $msg = 'המייל נשלח בהצלחה';
        break;
    case 'sms':
        $msg = 'הסמס נשלח בהצחה';
        break;
    default;
}

$project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if($project_id){
    $project = getProjectByProjectId($project_id);
    if (!$project){
        header('Location: /');
        exit;
    }
    $allClients = getAllClientsByProjectId($project_id);
}else{
    header('Location: /');
    exit;
}
$sms = filter_input(INPUT_GET, 'sms', FILTER_SANITIZE_SPECIAL_CHARS);
if($sms){
    $clinet = getClientById($sms);
    $user_name = $clinet['st_user_name'];
    $phone_first = $clinet['st_phone_first'];
    $phone_second = $clinet['st_phone_second'];
    $serial_number = $clinet['i_serial_number'];
    $message = $user_name . ' שלום רב,';
    $message .= 'לכניסה לפורטל דוחות מסירה יש ללחוץ על הקישור הבא:';
    $message .= $domain . '?report=' . $serial_number;
    if($phone_second != ''){
        $phone_first = $phone_first . ',' . $phone_second;
    }
    send_sms($phone_first,$message,$serial_number);

    header('Location: project.php?id=' . $project_id . '&msg=sms');
    exit;
}
$mail = filter_input(INPUT_GET, 'mail', FILTER_SANITIZE_SPECIAL_CHARS);
if($mail){
    $clinet = getClientById($mail);
    $user_name = $clinet['st_user_name'];
    $email = $clinet['st_mail'];
    $phone_first = $clinet['st_phone_first'];
    $phone_second = $clinet['st_phone_second'];
    $serial_number = $clinet['i_serial_number'];

    sendMailToClient($user_name, $email, $serial_number);

    header('Location: project.php?id=' . $project_id . '&msg=mail');
    exit;
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg .'</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">דיירים בפרויקט</div>
        <?php if($user_type == 1) : ?>
            <div class="flex">
                <a title="עריכת פרויקט" class="btn_header" href="projectsEditor.php?edit=<?= $project['project_id'] ?>"><?=$icons['edit']?></a>
                <a title="הוספת דיירים באקסל" style="margin-left: 15px" class="btn outline_btn" href="excelEditor.php?id=<?= $project['project_id'] ?>">אקסל</a>
                <a title="הוספת דייר חדש" class="btn" href="clientEditor.php?id=<?= $project['project_id'] ?>">הוספת דייר חדש</a>
            </div>
        <?php endif ?>
    </div>
    <div class="main_container__box">
        <div class="main_container__item">
            <div class="title">שם פרוייקט</div>
            <div><?=$project['st_project_name']?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">כתובת</div>
            <div><?=$project['st_project_address']?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מנהל הפרויקט</div>
            <div><?=$project['st_user_name']?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">תאריך אכלוס</div>
            <div><?=$project['date_occupancy']?></div>
        </div>
    </div>

    <div class="line"></div>
    <table id="projects_table" class="table table-striped">
        <thead>
        <tr>
            <th>מ.סידורי</th>
            <th>שם דייר</th>
            <th>מספר נייד</th>
            <th>דוא"ל</th>
            <th>דירה</th>
            <th>קומה</th>
            <th>סוג נכס</th>
            <th>מספר נכס</th>
            <th>סטטוס</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allClients as $clients) : ?>
            <tr <?= $clients['i_active'] == '1' ? '' : 'class="disable_user"' ?>>
                <td><?= $clients['i_serial_number'] != '' ? $clients['i_serial_number'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $clients['st_user_name'] != '' ? $clients['st_user_name'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td class="td_link">
                    <?php
                        if ($clients['st_phone_second'] != '') {
                            echo '<div class="td_link_hover">שני מספרים';
                            echo '<div class="td_hover_list">';
                                echo '<p>ראשי: ' . $clients['st_phone_first'] . '</p>';
                                echo '<p>משני: ' . $clients['st_phone_second'] . '</p>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo $clients['st_phone_first'];
                        }
                    ?>
                </td>
                <td><?= $clients['st_mail'] != '' ? $clients['st_mail'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $clients['st_apartment'] != '' ? $clients['st_apartment'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $clients['st_floor'] != '' ? $clients['st_floor'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $clients['st_property_type'] != '' ? $clients['st_property_type'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $clients['st_property_number'] != '' ? $clients['st_property_number'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $clients['i_active'] == '1' ? 'פעיל' : 'מבוטל' ?></td>
                <td class="actions">
                    <?php if($user_type == 1) : ?>
                        <a title="עריכת דייר" href="clientEditor.php?edit=<?= $clients['id'] ?>"><?= $icons['edit'] ?></a>
                        <a title="שלח מייל לדייר" href="project.php?id=<?=$project_id?>&mail=<?= $clients['id'] ?>"><?= $icons['mail'] ?></a>
                        <a title="שלח סמס לדייר" href="project.php?id=<?=$project_id?>&sms=<?= $clients['id'] ?>"><?= $icons['sms'] ?></a>
                    <?php endif ?>
                    <a title="קישור לדוח" href="report.php?id=<?= $clients['i_serial_number'] ?>"><?= $icons['report'] ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="js/project.js?var=2"></script>
<?php include_once 'includes/footer.php'; ?>
