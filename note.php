<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'notes';
global $icons;
global $user_type;
$edit = false;
$report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$fault_id = filter_input(INPUT_GET, 'fault', FILTER_SANITIZE_SPECIAL_CHARS);
if($report_serial){
    $report = getReportBySerialId($report_serial);
    $fault = getFaultById($fault_id);
    $statuses = getAllFaultStatuses();
}else{
    header('Location: /');
    exit;
}
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if($action){
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

    $report_serial = filter_input(INPUT_POST, 'report_serial', FILTER_SANITIZE_SPECIAL_CHARS);

    $fault_id = filter_input(INPUT_POST, 'note_id', FILTER_SANITIZE_SPECIAL_CHARS);

    $fault_status = filter_input(INPUT_POST, 'fault_status', FILTER_SANITIZE_NUMBER_INT);

    $note = filter_input(INPUT_POST, 'note', FILTER_SANITIZE_SPECIAL_CHARS);
}

switch ($action) {
    case 'edit':
        createNewNote($status, $report_serial, $fault_id, $note, $fault_status);
        break;
    default;
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">עדכון תקלה</div>
        <div class="flex">
            <?php if($user_type == 3) : ?>
                <a class="btn outline_btn" href="noteView.php?id=<?=$fault_id?>">חזרה</a>
            <?php else : ?>
                <a class="btn outline_btn" href="report.php?id=<?=$report_serial?>">חזרה</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="container__box">
        <form class="form_editor fault" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="report_serial" value="<?= $report_serial ?>">
            <input type="hidden" name="note_id" value="<?= $fault_id ?>">
            <input type="hidden" name="fault_status" value="<?= $fault['i_fault_status'] ?>">

            <label for="status">סטטוס תקלה</label>
            <select name="status" id="status" required>
                <option value="" hidden disabled selected>בחר סטטוס</option>
                <?php foreach ($statuses AS $status) : ?>
                    <?php if($user_type == 1 && $status['id'] !=0 && $status['id'] !=1 ) : ?>
                        <option value="<?=$status['id']?>"><?=$status['st_fault_status_name']?></option>
                    <?php elseif($user_type == 2 && $status['id'] !=0 && $status['id'] !=1 && $status['id'] !=3) : ?>
                        <option value="<?=$status['id']?>"><?=$status['st_fault_status_name']?></option>
                    <?php elseif($user_type == 3 && $status['id'] !=0 && $status['id'] !=1 && $status['id'] !=4 && $status['id'] !=5) : ?>
                        <option value="<?=$status['id']?>"><?=$status['st_fault_status_name']?></option>
                    <?php elseif($user_type == 5 && $status['id'] !=0 && $status['id'] !=1 && $status['id'] !=3 && $status['id'] !=5) : ?>
                        <option value="<?=$status['id']?>"><?=$status['st_fault_status_name']?></option>
                    <?php endif ?>
                <?php endforeach ?>
            </select>

            <label for="note">הערות</label>
            <textarea id="note" name="note" placeholder="הזן הערות"></textarea>
            <input class="btn btn_center" value="שמור" type="submit">
        </form>
    </div>
</div>
<script src="js/reports.js"></script>
<?php include_once 'includes/footer.php'; ?>
