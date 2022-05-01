<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'notes';
global $icons;
global $user_type;
$edit = false;
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg){
    case 'createOk':
        $msg = 'עדכון חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if($action){
    $body_text = filter_input(INPUT_POST, 'body_text');
    if ($body_text == null) {
        $body_text = html_entity_decode($body_text);
    }
    $dateTest = filter_input(INPUT_POST, 'dateTest', FILTER_SANITIZE_SPECIAL_CHARS);

    $report_serial = filter_input(INPUT_POST, 'report_serial', FILTER_SANITIZE_SPECIAL_CHARS);
}
switch ($action){
    case 'performing_the_test':
        updatePerformingTheTest($body_text, $dateTest, $report_serial);
        break;
    case 'finish':
        $status = 2;
        updateReportBySerialNumber($report_serial, $status);
        break;
    default;
}
//$report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$fault_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);

if($fault_id){
    $fault = getFaultById($fault_id);
    $report_serial = $fault['i_serial_number'];
    $client = getClientBySerialId($report_serial);
    $project = getProjectByProjectId($client['i_project_id']);
}else{
    header('Location: /');
    exit;
}

include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg .'</p></div>' : '' ?>

<div class="main_container">

    <div class="main_container__header">
        <div>
            <p class="main_container__title">תקלה מתחום - <?=$fault['st_contractor_type']?></p>
            <p class="main_container__des"> (סטטוס תקלה: <?=$fault['st_fault_status_name']?>)</p>
        </div>
        <div class="flex">
            <a class="btn outline_btn" href="notes.php">חזרה</a>
        </div>
    </div>

    <div class="main_container__box">
        <div class="main_container__item">
            <div class="title">שם פרוייקט</div>
            <div><?=$project['st_project_name']?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מנהל פרויקט</div>
            <div><?=$project['st_user_name']?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מספר נכס</div>
            <div><?=$client['st_property_number'] != '' ? $client['st_property_number'] : '<span class="td_empty">אין נתונים</span>'?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מספר דירה</div>
            <div><?=$client['st_apartment'] != '' ? $client['st_apartment'] : '<span class="td_empty">אין נתונים</span>'?></div>
        </div>
    </div>
        <div class="container__title">פרטי תקלה</div>
    <div class="container__box fault">
                <p class="fault_title"><?=$fault['st_title']?></p>
                <div class="fault_line"></div>
            <div class="fault_content">
                <div class="pre"><?=html_entity_decode($fault['st_fault_content'])?></div>
                <br>
                <?php if($client['i_status'] != 1 && $user_type != 4) : ?>
                    <?php $notes = getAllNotesByFaultId($fault['id']); ?>
                    <?php if($notes) : ?>
                        <div class="notes">
                            <?php foreach($notes AS $note) : ?>
                                <div class="notes_item">
                                    <div class="notes_item__date_and_status">
                                        <div class="notes_item__status">
                                            <span class="bold"><?= date('d.m.Y', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                            <span class="bold">&nbsp;<?= date('H:i', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                            <span>&nbsp;<?= $note['user_type'] ?>&nbsp;(<?= $note['st_user_name'] ?>)</span>
                                        </div>
                                        <div class="notes_item__date">
                                            <span><?=$note['st_last_fault_status_name']?>&nbsp;</span>
                                            <?=$icons['west']?>
                                            <span class="bold">&nbsp;<?=$note['st_fault_status_name']?><div class="notes_item__color" style="background-color: <?=$note['st_color_status']?>"></div></span>
                                        </div>
                                    </div>
                                    <?php if($note['st_note']) : ?>
                                        <div class="notes_item__text"><span class="bold">הערות: </span><?=$note['st_note']?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <a class="btn" href="note.php?id=<?=$report_serial?>&fault=<?=$fault['id']?>">הוסף עדכון</a>
                <?php endif; ?>
            </div>
    </div>








    <?php if($client['i_status'] == 1 && $user_type == 4) : ?>
        <form action="" method="post">
            <input type="hidden" name="action" value="finish">
            <input type="hidden" name="report_serial" value="<?= $report_serial ?>">
            <button class="btn btn_center" type="submit" onclick="if (!confirm('לאחר סיום לא יהיה ניתן לערוך את הדוח')) return false;">סיימתי את הדוח</button>
        </form>
    <?php endif; ?>
</div>
<script src="js/report.js"></script>
<?php include_once 'includes/footer.php'; ?>
