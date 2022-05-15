<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
global $pdo;
global $icons;
global $user_type;
global $user_id;
if($user_type != 1 && $user_type != 2 && $user_type != 5){
    error404();
}
$page = 'projects';
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
            <th>התקדמות</th>
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
                <?php $all_faults = getAllFaultsBySerialId($report['i_serial_number']) ?>
                <?php $all_faults_count = count($all_faults) ?>
                <?php
                    $fault_status_0 = 0;
                    $fault_status_1 = 0;
                    $fault_status_2 = 0;
                    $fault_status_3 = 0;
                    $fault_status_4 = 0;
                    $fault_status_5 = 0;
                    foreach ($all_faults AS $fault_status){
                        if($fault_status['i_fault_status'] == 0){
                            $fault_status_0++;
                        }
                        if($fault_status['i_fault_status'] == 1){
                            $fault_status_1++;
                        }
                        if($fault_status['i_fault_status'] == 2){
                            $fault_status_2++;
                        }
                        if($fault_status['i_fault_status'] == 3){
                            $fault_status_3++;
                        }
                        if($fault_status['i_fault_status'] == 4){
                            $fault_status_4++;
                        }
                        if($fault_status['i_fault_status'] == 5){
                            $fault_status_5++;
                        }
                    }
                    $fault_status_0_percentage = getPercentage($fault_status_0, $all_faults_count);
                    $fault_status_1_percentage = getPercentage($fault_status_1, $all_faults_count);
                    $fault_status_2_percentage = getPercentage($fault_status_2, $all_faults_count);
                    $fault_status_3_percentage = getPercentage($fault_status_3, $all_faults_count);
                    $fault_status_4_percentage = getPercentage($fault_status_4, $all_faults_count);
                    $fault_status_5_percentage = getPercentage($fault_status_5, $all_faults_count);
            ?>
                <td class="td_progress">
                    <label for="project_<?=$report['project_id']?>">
                        <?= $fault_status_5 != 0 ? '<span title="אין צורך בטיפול: ' . $fault_status_5 . '" style="color: #2b7647">' . $fault_status_5 . '</span>' : '' ?>
                        <?= $fault_status_4 != 0 ? '<span title="אישור טיפול: ' . $fault_status_4 . '" style="color: #13B74F">' . $fault_status_4 . '</span>' : '' ?>
                        <?= $fault_status_3 != 0 ? '<span title="הסתיים טיפול: ' . $fault_status_3 . '" style="color: #F1C929">' . $fault_status_3 . '</span>' : '' ?>
                        <?= $fault_status_2 != 0 ? '<span title="בטיפול: ' . $fault_status_2 . '" style="color: #E85353">' . $fault_status_2 . '</span>' : '' ?>
                        <?= $fault_status_1 != 0 ? '<span title="הוגש לטיפול: ' . $fault_status_1 . '" style="color: #CCCCCC">' . $fault_status_1 . '</span>' : '' ?>
                        <?= $fault_status_0 != 0 ? '<span title="טיוטא: ' . $fault_status_0 . '" style="color: #222">טיוטא: ' . $fault_status_0 . '</span>' : '' ?>
                        <?= $fault_status_0 == 0 && $fault_status_1 == 0 && $fault_status_2 == 0 && $fault_status_3 == 0 && $fault_status_4 == 0 && $fault_status_5 == 0 ? '<span class="td_empty">אין נתונים</span>' : '' ?>
                    </label>
                    <div class="progress">
                        <div title="אין צורך בטיפול: <?=$fault_status_5?>" class="progress_line" style="width: <?=$fault_status_5_percentage?>; background-color: #2b7647"></div>
                        <div title="אישור טיפול: <?=$fault_status_4?>" class="progress_line" style="width: <?=$fault_status_4_percentage?>; background-color: #13B74F"></div>
                        <div title="הסתיים טיפול: <?=$fault_status_3?>" class="progress_line" style="width: <?=$fault_status_3_percentage?>; background-color: #F1C929"></div>
                        <div title="בטיפול: <?=$fault_status_2?>" class="progress_line" style="width: <?=$fault_status_2_percentage?>; background-color: #E85353"></div>
                        <div title="הוגש לטיפול: <?=$fault_status_1?>" class="progress_line" style="width: <?=$fault_status_1_percentage?>; background-color: #CCCCCC"></div>
                        <div title="טיוטא: <?=$fault_status_0?>" class="progress_line" style="width: <?=$fault_status_0_percentage?>; background-color: #E8E7E7"></div>
                    </div>
                </td>

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
                        <a title="קישור לדוח לקוח" href="/?logOut=logOut&report=<?= $report['i_serial_number'] ?>"><?= $icons['link'] ?></a>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="./js/reports.js"></script>
<?php include_once 'includes/footer.php'; ?>

