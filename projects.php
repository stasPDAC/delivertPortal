<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
global $pdo;
global $icons;
global $user_type;
global $user_id;
$page = 'projects';
if($user_type == 1 || $user_type == 5){
    $projects = getAllProjects();
}elseif($user_type == 2){
    $projects = getAllProjectsByManagerId($user_id);
}elseif($user_type == 3){
    $projects = getAllProjectsByContractorIdForProjectsPage($user_id);
}else{
    error404();
}
if(isset($msg)){
    $msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
    switch ($msg) {
        case 'createOk':
            $msg = 'פרויקט חדש נוצר בהצלחה';
            break;
        case 'editOk':
            $msg = 'הנתונים שמרו בהצלחה';
            break;
        case 'error':
            $msg = 'משהו השתבש נסו שוב מאוחר יותר';
            break;
        default;
    }
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg . '</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">פרוייקטים</div>
        <?php if($user_type == 1) : ?>
            <a class="btn" href="projectsEditor.php">הוסף פרויקט חדש</a>
        <?php endif ?>
    </div>
    <div class="line"></div>
    <table id="projects_table" class="table table-striped">
        <thead>
        <tr>
            <th>שם הפרויקט</th>
            <?php if($user_type != 3) : ?>
                <th>התקדמות</th>
            <?php endif; ?>
            <th>כתובת</th>
            <?php if($user_type != 2) : ?>
                <th>מנהל הפרויקט</th>
            <?php endif; ?>
            <th>קבלנים</th>
            <th>תאריך אכלוס</th>
            <?php if($user_type == 3) : ?>
                <th>תקלות</th>
            <?php endif ?>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $project) : ?>
        <?php if($user_type == 3) : ?>
                <?php $faults = getAllReportsFaultsByContractorsIdAndProjectId($user_id, $project['project_id']);?>
            <tr title="תקלות בפרויקט" class="<?= count($faults) != '0' ? '' : 'disable_user' ?>">
        <?php else : ?>
            <tr title="דוחות דיירים" class="<?= $project['project_activity'] == '1' ? '' : 'disable_user' ?>">
        <?php endif; ?>
                <td><?= $project['st_project_name'] ?></td>
            <?php if($user_type != 3) : ?>
                <td class="td_progress">
                    <?php $all_reports = getAllReportsByProjectId($project['project_id']) ?>
                    <?php $all_reports_count = count($all_reports) ?>
                    <?php
                        $reports_status_1 = 0;
                        $reports_status_2 = 0;
                        $reports_status_3 = 0;
                        foreach($all_reports AS $report_item){
                            if($report_item['i_status'] == 1){
                                $reports_status_1++;
                            }elseif ($report_item['i_status'] == 2){
                                $reports_status_2++;
                            }elseif ($report_item['i_status'] == 3){
                                $reports_status_3++;
                            }
                        }
                        $reports_status_1_percentage = getPercentage($reports_status_1, $all_reports_count);
                        $reports_status_2_percentage = getPercentage($reports_status_2, $all_reports_count);
                        $reports_status_3_percentage = getPercentage($reports_status_3, $all_reports_count);
                    ?>
                    <label for="project_<?=$project['project_id']?>">
                        <div class="order_status"><?=$reports_status_1_percentage . $reports_status_2_percentage . $reports_status_3_percentage?></div>
                        <?= $reports_status_3 != 0 ? '<span style="color: #13B74F">מוכן: ' . $reports_status_3 . '</span>' : '' ?>
                        <?= $reports_status_2 != 0 ? '<span style="color: #6fc18d">בטיפול: ' . $reports_status_2 . '</span>' : '' ?>
                        <?= $reports_status_1 != 0 ? '<span style="color: #CCCCCC">טיוטא: ' . $reports_status_1 . '</span>' : '' ?>
                        <?= $reports_status_3 == 0 && $reports_status_2 == 0 && $reports_status_1 ==0 ? '<span class="td_empty">אין נתונים</span>' : '' ?>
                    </label>
                    <div class="progress">
                        <div class="progress_line" style="width: <?=$reports_status_3_percentage?>; background-color: #13B74F"></div>
                        <div class="progress_line" style="width: <?=$reports_status_2_percentage?>; background-color: #6fc18d"></div>
                        <div class="progress_line" style="width: <?=$reports_status_1_percentage?>; background-color: #CCCCCC"></div>
                    </div>
                </td>
            <?php endif; ?>
                <td><?= $project['st_project_address'] ?></td>
                <?php if($user_type != 2) : ?>
                    <td><?= $project['st_user_name'] ?></td>
                <?php endif; ?>
                <td class="td_link">
                    <?php
                    $count = count(getAllContractorByProjectId($project['project_id']));
                    $contractors = getAllContractorByProjectId($project['project_id']);
                    if ($count > 0) {
                        echo '<div class="td_link_hover">' . $count . ' קבלנים';
                        echo '<div class="td_hover_list" id="td_hover_list_' . $project['project_id'] . '">';
                        foreach ($contractors as $contractor) {
                             $faults_for_report = getAllReportsFaultsByContractorsIdAndProjectId($contractor['i_contractor_id'], $project['project_id']);
                             $count_faults = count($faults_for_report);

                            if($user_type < 3 && $count_faults != 0){
                                echo '<p>' . $contractor['st_contractor_type'] . ': <span class="bold">' . $contractor['st_user_name'] . '</span><a href="contractor.php?id=' . $project['project_id'] . '&u=' . $contractor['i_contractor_id'] . '" title="דוח תקלות לקבלן">' . $icons['note'] . '</a></p>';
                            }else{
                                echo '<p>' . $contractor['st_contractor_type'] . ': <span class="bold">' . $contractor['st_user_name'] . '</span></p>';
                            }
                        }
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<span class="td_empty">לא נבחר</span>';
                    }
                    ?>
                </td>
                <td><?= date('d.m.Y', strtotime($project['date_occupancy'])) ?>&nbsp;</td>
            <?php if($user_type == 3) : ?>
                <td><?=count($faults)?></td>
            <?php endif ?>
                <td class="actions">
                    <?php if($user_type == 1) : ?>
                        <a title="עריכת פרויקט" href="projectsEditor.php?edit=<?= $project['project_id'] ?>"><?= $icons['edit'] ?></a>
                        <a title="רשימת דיירים" href="project.php?id=<?= $project['project_id'] ?>"><?= $icons['group'] ?></a>
                    <?php endif ?>
            <?php if($user_type == 3) : ?>
                <?php if(count($faults) > 0) :?>
                    <a href="pdf_faultReport.php?id=<?=$project['project_id']?>&u=<?=$user_id?>" class="btn_header" target="_blank" title='דו"ח תקלות בפקויקר'><?=$icons['upload_pdf']?></a>
                <?php endif; ?>
                    <a title="דוחות דיירים" href="notes.php?id=<?= $project['project_id'] ?>"><?= $icons['reports'] ?></a>
            <?php else : ?>
                <a title="דוחות דיירים" href="reports.php?id=<?= $project['project_id'] ?>"><?= $icons['reports'] ?></a>
            <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="./js/projects.js?var=<?= rand(0, 9999) ?>"></script>
<?php include_once 'includes/footer.php'; ?>

