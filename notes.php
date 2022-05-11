<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
global $pdo;
global $icons;
global $user_type;
global $user_id;
$page = 'notes';
if($user_type == 3){
    $project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($project_id){
        $faults = getAllReportsFaultsByContractorsIdAndProjectId($user_id, $project_id);
    }else{
        $faults = getAllReportsFaultsByContractorsId($user_id);
    }
}else{
    $faults = getAllReportsFaults();
}
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
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg . '</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <?php
        if ($project_id && isset($faults[0]['st_project_name'])){
            echo '<div class="main_container__title">(' . count($faults) . ') תקלות בפרויקט - ' . $faults[0]['st_project_name'] . '</div>';
        }else{
            echo '<div class="main_container__title">(' . count($faults) . ') תקלות</div>';
        }
        ?>
<!--        <div class="main_container__title">תקלות (--><?//=count($faults)?><!--)</div>-->
        <?php if(isset($project_id)) : ?>
            <div class="flex">
                <a class="btn outline_btn" href="notes.php">לכל התקלות</a>
            </div>
        <?php endif ?>
    </div>
    <div class="line"></div>
        <table id="projects_table" class="table table-striped">
            <thead>
            <tr>
                <th>מס תקלה</th>
                <th>סטטוס תקלה</th>
                <th>תחום</th>
                <th>שם הפרויקט</th>

                <th></th>
            </tr>
            </thead>
            <tbody>
                <?php foreach ($faults as $fault) : ?>
                    <tr>
                        <td><?= $fault['id'] ?></td>
                        <td>
                            <div class="notes_item__color" style="background-color: <?= $fault['st_color_status'] ?>"></div>
                            <span class="order_status"><?= $fault['status_id'] ?></span>
                            <?= $fault['st_fault_status_name'] ?>
                        </td>
                        <td><?= $fault['st_contractor_type'] ?></td>
                        <td><?= $fault['st_project_name'] ?></td>
                        <td class="actions">
                            <a title="צפייה בתקלה" href="noteView.php?id=<?= $fault['id'] ?>"><?= $icons['note'] ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>
<script src="./js/notes.js?var=2"></script>
<?php include_once 'includes/footer.php'; ?>

