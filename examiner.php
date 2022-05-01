<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'reports';
global $icons;
global $user_type;
$edit = false;
$report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if($report_serial){
    $report = getReportBySerialId($report_serial);
    $phone_checker = getPhoneCheckerBySerialId($report_serial);
}else{
    header('Location: /');
    exit;
}
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if($action){
    $body_text = filter_input(INPUT_POST, 'body_text');
    if ($body_text == null) {
        $body_text = html_entity_decode($body_text);
    }

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);

    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);

    $report_serial = filter_input(INPUT_POST, 'report_serial', FILTER_SANITIZE_SPECIAL_CHARS);
}

switch ($action) {
    case 'performing_the_test':
        updatePerformingTheTest($body_text, $name, $phone, $report_serial);
        break;
    default;
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<div class="main_container">

    <div class="main_container__header">
        <div class="main_container__title">פרטי מבצע הבדיקה</div>
        <div class="flex">
            <a class="btn outline_btn" href="report.php?id=<?=$report_serial?>">חזרה</a>
        </div>
    </div>
    <div class="container__box">
        <form class="form_editor fault" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="performing_the_test">
            <input type="hidden" name="report_serial" value="<?= $report_serial ?>">

            <label for="name">שם של הבודק</label>
            <input type="text" name="name" id="name" placeholder="הזן כותרת" value="<?= htmlentities($report['st_name_checker']) ?>">

            <label for="phone">מספר נייד של בודק</label>
            <input type="tel" name="phone" id="phone" placeholder="הזן כותרת" value="<?= $phone_checker['st_phone_checker'] ?>">

            <label for="articleBodyEditor">פרטים</label>
            <textarea id="articleBodyEditor" name="body_text" placeholder="הזן פרטים"><?= html_entity_decode($report['st_checker']) ?></textarea>
            <input id="imageInput" type="file" accept="image/*" style="display: none">
            <input class="btn btn_center" value="שמור" type="submit">
        </form>
    </div>
</div>
<script src="js/reports.js?var=9"></script>
<?php include_once 'includes/footer.php'; ?>
