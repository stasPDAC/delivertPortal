<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
global $pdo;
global $icons;
global $user_type;
global $user_id;
$page = 'projects';
if($user_type == 1){
    $projects = getAllProjects();
}elseif($user_type == 2){
    $projects = getAllProjectsByManagerId($user_id);
}elseif($user_type == 3){
    $projects = getAllProjects();
}else{
    header('Location: /');
    exit;
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
            <th>התקדמות</th>
            <th>כתובת</th>
            <?php if($user_type != 2) : ?>
                <th>מנהל הפרויקט</th>
            <?php endif; ?>
            <th>קבלנים</th>
            <th>תאריך אכלוס</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $project) : ?>
            <tr title="דוחות דיירים" class="target <?= $project['project_activity'] == '1' ? '' : 'disable_user' ?>" onclick="location.href='reports.php?id=<?= $project['project_id'] ?>'">
                <td><?= $project['st_project_name'] ?></td>
                <td class="td_progress">
                    <?php $all_reports = getAllReportsByProjectId($project['project_id']) ?>
                    <?php $all_reports_count = count($all_reports) ?>

                    <?php $all_finish_reports = getAllReportsFinishedByProjectId($project['project_id']) ?>
                    <?php $all_finish_reports_count = count($all_finish_reports) ?>

                    <?php $all_at_work_reports = getAllReportsAtWorkByProjectId($project['project_id']) ?>
                    <?php $all_at_work_reports_count = count($all_at_work_reports) ?>

                    <?php $all_draft_reports = getAllReportsDraftByProjectId($project['project_id']) ?>
                    <?php $all_draft_reports_count = count($all_draft_reports) ?>

                    <?php $finish_percentage = getPercentage($all_finish_reports_count, $all_reports_count) ?>
                    <?php $at_work_percentage = getPercentage($all_at_work_reports_count, $all_reports_count) ?>
                    <?php $draft_percentage = getPercentage($all_draft_reports_count, $all_reports_count) ?>
                    <label for="project_<?=$project['project_id']?>">
                        <div class="order_status"><?=$draft_percentage . $at_work_percentage . $finish_percentage?></div>
                        <?= $all_finish_reports_count != 0 ? '<span>מוכן: ' . $all_finish_reports_count . '</span>' : '' ?>
                        <?= $all_at_work_reports_count != 0 ? '<span>בטיפול: ' . $all_at_work_reports_count . '</span>' : '' ?>
                        <?= $all_draft_reports_count != 0 ? '<span>טיוטא: ' . $all_draft_reports_count . '</span>' : '' ?>
                        <?= $all_finish_reports_count == 0 && $all_at_work_reports_count == 0 && $all_draft_reports_count ==0 ? '<span class="td_empty">אין נתונים</span>' : '' ?>
                    </label>
                    <div class="progress">
                        <div class="progress_line" style="width: <?=$finish_percentage?>; background-color: #13B74F"></div>
                        <div class="progress_line" style="width: <?=$at_work_percentage?>; background-color: #6fc18d"></div>
                        <div class="progress_line" style="width: <?=$draft_percentage?>; background-color: #CCCCCC"></div>
                    </div>
                </td>
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
                        echo '<div class="td_hover_list">';
                        foreach ($contractors as $contractor) {
//                            if($contractor['i_contractor_type'] == 8 && $contractor['i_contractor_id'] == -1){
//                                $contractor['st_user_name'] = 'פרטני לכל דייר' ;
//                            }
//                            if($contractor['i_contractor_type'] == 9 && $contractor['i_contractor_id'] == -1){
//                                $contractor['st_user_name'] = 'פרטני לכל דייר' ;
//                            }
                            echo '<p>' . $contractor['st_contractor_type'] . ': <span class="bold">' . $contractor['st_user_name'] . '</span></p>';
                        }
                        echo '</div>';
                        echo '</div>';
                    } else {
                        echo '<span class="td_empty">לא נבחר</span>';
                    }
                    ?>
                </td>
                <td><?= date('d.m.Y', strtotime($project['date_occupancy'])) ?>&nbsp;</td>

                <td class="actions">
                    <?php if($user_type == 1) : ?>
                        <a title="עריכת פרויקט" href="projectsEditor.php?edit=<?= $project['project_id'] ?>"><?= $icons['edit'] ?></a>
                        <a title="רשימת דיירים" href="project.php?id=<?= $project['project_id'] ?>"><?= $icons['group'] ?></a>
                    <?php endif ?>
                    <a title="דוחות דיירים" href="reports.php?id=<?= $project['project_id'] ?>"><?= $icons['reports'] ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="./js/projects.js?var=<?= rand(0, 9999) ?>"></script>
<?php include_once 'includes/footer.php'; ?>

