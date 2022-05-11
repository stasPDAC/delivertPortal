<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'reports';
global $icons;
global $user_type;
$edit = false;
$report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if($report_serial || $user_type == 4){
    $fault = getFaultBySerialId($report_serial);
    $contractor_types = getAllContractorTypes();
    $contractor_check = checkKitchenOrBathroomContractorBySerialNumber($report_serial);
    $contractor_check = array_column($contractor_check,"i_contractor_type");
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
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_NUMBER_INT);

    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);

    $report_serial = filter_input(INPUT_POST, 'report_serial', FILTER_SANITIZE_SPECIAL_CHARS);
}

switch ($action) {
    case 'create':
        createNewFault($body_text, $category, $title, $report_serial);
        break;
    case 'update':
        $idToEdit = filter_input(INPUT_POST, 'idToEdit', FILTER_SANITIZE_SPECIAL_CHARS);
        updateFaultById($idToEdit, $body_text, $category, $title, $report_serial);
        break;

    default;
}

if (isset($_GET['edit'])) {
    $idToEdit = filter_input(INPUT_GET, 'edit');
    $fault = getFaultById($idToEdit);

    $title = "עריכת תקלה";
    $action = "update";
    $btn = "שמור";
} else {
    $title = "תקלה חדשה";
    $action = "create";
    $btn = "הוסף תקלה";
}

include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<div class="main_container">

    <div class="main_container__header">
        <div class="main_container__title"><?=$title?></div>
        <div class="flex">
            <a class="btn outline_btn" href="report.php?id=<?=$report_serial?>">חזרה</a>
        </div>
    </div>
    <div class="container__box">
        <form class="form_editor fault" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?=$action?>">
            <input type="hidden" name="idToEdit" value="<?= isset($idToEdit) ? $idToEdit : '' ?>">
            <input type="hidden" name="report_serial" value="<?= $report_serial ?>">
            <label for="category">קטגוריה</label>
            <select id="category" name="category" required>
                <option value="" selected disabled hidden>בחר קטגוריה</option>
                <?php foreach ($contractor_types AS $contractor_type) : ?>
                    <?php if(!in_array($contractor_type['id'],$contractor_check)) : ?>
                        <option value="<?=$contractor_type['id']?>" <?= isset($idToEdit) && $contractor_type['id'] == $fault['i_category']  ?'selected' : ''?>><?=$contractor_type['st_contractor_type']?></option>
                    <?php endif ?>
                <?php endforeach; ?>
            </select>
            <label for="title">כותרת</label>
            <input type="text" name="title" id="title" placeholder="הזן כותרת" value="<?= isset($idToEdit) ? htmlentities($fault['st_title']) : '' ?>" required
                   oninvalid="this.setCustomValidity('אנא הזן כותרת')" oninput="this.setCustomValidity('')">
            <label for="articleBodyEditor">תאור תקלה</label>
            <textarea id="articleBodyEditor" name="body_text" placeholder="הזן תאור תקלה"><?= isset($idToEdit) ? html_entity_decode($fault['st_fault_content']) : '' ?></textarea>
            <input id="imageInput" type="file" accept="image/*" style="display: none">
            <input class="btn btn_center" value="<?=$btn?>" type="submit">
        </form>
    </div>
</div>
<script src="js/reports.js?var=9"></script>
<?php include_once 'includes/footer.php'; ?>
