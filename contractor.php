<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'contractor';
global $icons;
global $user_type;
global $date_terms_confirmed;
global $user_name_connect;
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
$project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if($project_id){
    checkFaultReportPermission($project_id);
    $user_id = filter_input(INPUT_GET, 'u', FILTER_SANITIZE_SPECIAL_CHARS);

    $faults = getAllReportsFaultsByContractorsIdAndProjectId($user_id, $project_id);
    $project = getProjectByProjectId($project_id);
    $current_category = 0;
}

include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg . '</p></div>' : '' ?>
<div class="main_container">

    <div class="main_container__header">
        <div>
            <p class="main_container__title">דו"ח ליקויים לקבלן - <?= $faults['0']['st_user_name'] ?></p>
        </div>
        <div class="flex">
            <a href="pdf_faultReport.php?id=<?= $project_id ?>&u=<?=$user_id?>" class="btn_header" target="_blank" title='יצוא דוח תקלות לקבלן'><?= $icons['upload_pdf'] ?></a>

            <a class="btn outline_btn" href="projects.php">חזרה</a>
        </div>
    </div>

    <div class="main_container__box">
        <div class="main_container__item">
            <div class="title">שם פרוייקט</div>
            <div><?= $project['st_project_name'] ?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">כתובת</div>
            <div><?= $project['st_project_address'] ?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מנהל פרויקט</div>
            <div><?= $project['st_user_name'] ?></div>
        </div>


    </div>

    <div class="line"></div>

    <div class="main_container__header" style="justify-content: center">
        <div class="container__title fault">ממצאים</div>
    </div>


    <div class="container__box fault">
        <?php $count = 1 ?>
        <?php foreach ($faults as $fault) : ?>
        <?php $client = getClientBySerialId($fault['i_serial_number']);?>
            <?php if ($fault['type_id'] != $current_category) : ?>
                <?php $count = 1 ?>
                <p class="fault_title">תחום <?= $fault['st_contractor_type'] ?></p>
                <div class="fault_line"></div>
            <?php endif ?>
            <div class="accordion">
                <?= $count ?>: <?= $fault['st_title'] ?><b> (דירה: <?=$client['st_apartment']?>)</b>
                <span><?= $icons['down'] ?></span>
            </div>

            <div class="fault_content panel">
                <div class="pre"><?= html_entity_decode($fault['st_fault_content']) ?></div>
                <br>
                <?php $notes = getAllNotesByFaultId($fault['id']); ?>
                <?php if ($notes) : ?>
                    <div class="notes">
                        <?php foreach ($notes as $note) : ?>
                            <div class="notes_item">
                                <div class="notes_item__date_and_status">
                                    <div class="notes_item__status">
                                        <span class="bold"><?= date('d.m.Y', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                        <span class="bold">&nbsp;<?= date('H:i', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                        <span>&nbsp;<?= $note['user_type'] ?>&nbsp;(<?= $note['st_user_name'] ?>)</span>
                                    </div>
                                    <div class="notes_item__date">
                                        <span><?= $note['st_last_fault_status_name'] ?>&nbsp;</span>
                                        <?= $icons['west'] ?>
                                        <span class="bold">&nbsp;<?= $note['st_fault_status_name'] ?><div class="notes_item__color" style="background-color: <?= $note['st_color_status'] ?>"></div></span>
                                    </div>
                                </div>
                                <?php if ($note['st_note']) : ?>
                                    <div class="notes_item__text"><span class="bold">הערות: </span><?= $note['st_note'] ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
<!--                <a class="btn" href="note.php?id=--><?//= $fault['i_serial_number'] ?><!--&fault=--><?//= $fault['id'] ?><!--">הוסף עדכון</a>-->
            </div>
            <?php $current_category = $fault['type_id']; ?>
            <?php $count++; ?>
            <div class="fault_gray_line"></div>
        <?php endforeach ?>
    </div>
</div>
<script src="js/report.js"></script>
<?php include_once 'includes/footer.php'; ?>
