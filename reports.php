<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
global $pdo;
global $icons;
global $user_type;
global $user_id;
$page = 'reports';
$project_id = '';
$project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if($user_type == 2){
    if($project_id){
        $reports = getAllReportsByProjectId($project_id);
    }else{
        $reports = getAllReportsByManagerId($user_id);
    }
}else{
    if($project_id){
        $reports = getAllReportsByProjectId($project_id);
    }else{
        $reports = getAllReports();
    }
}
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg) {
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg . '</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <?php if($project_id) : ?>
            <div class="main_container__title"><?= isset($reports['0']['st_project_name']) ? 'דוחות דיירים - ' . $reports['0']['st_project_name'] : 'אין דוחות' ?></div>
            <div class="flex">
                <a class="btn outline_btn" href="projects.php">חזרה</a>
            </div>
        <?php else : ?>
            <div class="main_container__title">דוחות דיירים</div>
        <?php endif ?>
    </div>
    <div class="line"></div>
    <table id="projects_table" class="table table-striped">
        <thead>
        <tr>
            <th>סטטוס דוח</th>
            <th>מס סידורי</th>

            <th>שם דייר</th>
            <th>טלפון דייר</th>
            <th>מספר דירה</th>
            <?php if(!$project_id) : ?>
                <th>שם הפרויקט</th>
            <?php endif ?>
            <th>תאריך אכלוס</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $report) : ?>
            <tr <?= $report['user_activity'] == '1' ? '' : 'disable_user' ?>">
                <td><span class="order_status"><?=$report['i_status']?></span><div class="notes_item__color" style="background-color: <?= $report['st_color_status'] ?>"></div><?= $report['st_report_status_name'] ?></td>
                <td><?= $report['i_serial_number'] ?></td>
                <td><?= $report['st_user_name'] != '' ? $report['st_user_name'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $report['st_phone_first'] != '' ? $report['st_phone_first'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $report['st_apartment'] != '' ? $report['st_apartment'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <?php if(!$project_id) : ?>
                    <td><a href="project.php?id=<?= $report['i_project_id'] ?>"><?= $report['st_project_name'] ?></a></td>
                <?php endif ?>
                <td><?= $report['date_occupancy'] ?></td>
                <td class="actions">
                    <a title="קישור לדוח" href="report.php?id=<?= $report['i_serial_number'] ?>"><?= $icons['report'] ?></a>
                    <?php if($user_type == 1) : ?>
                        <a title="קישור לדוח לקוח" href="/?report=<?= $report['i_serial_number'] ?>"><?= $icons['link'] ?></a>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="./js/reports.js"></script>
<?php include_once 'includes/footer.php'; ?>

