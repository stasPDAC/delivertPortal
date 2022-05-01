<?php
require_once 'includes/config.php';
global $pdo;
global $icons;
$page = 'projects';
$project_check = true;
$project_name_check = true;
include_once 'includes/global.php';
$all_contractor_types = getAllContractorTypes();
$manageress = getAllManageressActivities();
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if ($action) {
    $project_name = filter_input(INPUT_POST, 'project_name', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($project_name) {
        $project_name_check = getAllProjectNames($project_name);
        if ($project_name_check){
            $msg_project_name = 'שם פרויקט זה כבר קיים במערכת';
            $project_name_check = false;
        }
    }else{
        $msg_project_name = 'חובה להזין את השם של הפרויקט';
        $project_check = false;
    }

    $project_manager = filter_input(INPUT_POST, 'project_manager', FILTER_SANITIZE_NUMBER_INT);
    if ($project_manager == '') {
        $project_manager = 0;
    }

    $project_address = filter_input(INPUT_POST, 'project_address', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($project_address) {
        $project_address = html_entity_decode($project_address);
    }else{
        $msg_project_address = 'חובה להזין כתובת של הפרויקט';
        $project_check = false;
    }
    $selections = [];
    foreach (getAllContractorTypes() as $select) {
        $selected = filter_input(INPUT_POST, $select['st_contractor_type_en'], FILTER_SANITIZE_SPECIAL_CHARS);
        $selections[$select['id']] = $selected;
    }

    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_SPECIAL_CHARS) ? 1 : 0;

    $date_occupancy = filter_input(INPUT_POST, 'date_occupancy', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!$date_occupancy){
        $msg_project_date_occupancy = 'חובה לבחור את תאריך האכלוס';
        $project_check = false;
    }
}

switch ($action) {
    case 'create':
        if($project_check != false || $project_name_check != false){
            createNewProject($project_name, $project_manager, $project_address, $date_occupancy, $selections);
        }
        break;
    case 'update':
        $idToEdit = filter_input(INPUT_POST, 'idToEdit', FILTER_SANITIZE_SPECIAL_CHARS);
        if($project_check != false){
            updateProjectById($project_name, $project_manager, $project_address, $date_occupancy, $idToEdit, $selections, $active);
        }
        break;
    default;
}

if (isset($_GET['edit'])) {
    $idToEdit = filter_input(INPUT_GET, 'edit');
    $project = getProjectByProjectId($idToEdit);
    if (!$project){
        header('Location: /');
        exit;
    }
    $action = "update";
    $btn = "שמור";
    $title = 'עריכת פרוייקט - ' . $project['st_project_name'];
} else {
    $action = "create";
    $btn = "צור פרוייקט";
    $title = 'פרוייקט חדש';
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>

<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title"><?=$title?></div>
        <a class="btn outline_btn" href="projects.php">חזרה</a>
    </div>

    <div class="main_container__box">
        <form class="form_editor" action="" method="post">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="idToEdit" value="<?= isset($idToEdit) ? $idToEdit : '' ?>">

            <label for="project_name">*שם הפרויקט</label>
            <input type="text" name="project_name" id="project_name" placeholder="הזן שם הפרויקט" value="<?= isset($idToEdit) ? htmlentities($project['st_project_name']) : '' ?><?=isset($project_name)?$project_name:''?>" required
                   oninvalid="this.setCustomValidity('אנא הזן שם הפרויקט')" oninput="this.setCustomValidity('')">
            <?= isset($msg_project_name) ? '<p class="error">' . $msg_project_name . '</p>' : '' ?>

            <label for="project_address">*כתובת</label>
            <input type="text" name="project_address" id="project_address" placeholder="הזן כתובת"
                   value="<?= isset($idToEdit) ? htmlentities($project['st_project_address']) : '' ?><?=isset($project_address)?$project_address:''?>" required
                   oninvalid="this.setCustomValidity('אנא הזן את הכתובת של הפרויקט')" oninput="this.setCustomValidity('')">
            <?= isset($msg_project_address) ? '<p class="error">' . $msg_project_address . '</p>' : '' ?>

            <label for="project_manager">מנהל פרוייקט</label>
            <select name="project_manager" id="project_manager">
                <option value="" disabled selected hidden>בחר מנהל פרוייקט</option>
                <?php foreach ($manageress as $manager) : ?>
                    <option <?= isset($idToEdit) && $project['i_project_manager'] == $manager['id'] ? 'selected' : '' ?>
                            value="<?= $manager['id'] ?>"><?= $manager['st_user_name'] ?></option>
                <?php endforeach ?>
            </select>

            <label for="date_occupancy">*תאריך אכלוס</label>
            <input type="date" name="date_occupancy" id="date_occupancy"
                   value="<?= isset($idToEdit) ? $project['date_occupancy'] : '' ?><?=isset($date_occupancy)?$date_occupancy:''?>" required
                   oninvalid="this.setCustomValidity('אנא בכר את תאריך האכלוס')" oninput="this.setCustomValidity('')">
            <?= isset($msg_project_date_occupancy) ? '<p class="error">' . $msg_project_date_occupancy . '</p>' : '' ?>

            <?php if(isset($idToEdit)) : ?>
                <label for="active" class="container" style="width: 100%">משתמש פעיל
                    <input type="checkbox" id="active" name="active" <?= $project['i_active'] == 1 ? 'checked' : '' ?>>
                    <span class="checkmark"></span>
                </label>
            <?php endif; ?>

            <p class="middle_title" style="">קבלנים</p>
            <div class="domains__box">
                <?php if (isset($idToEdit)) : ?>
                    <?php foreach ($all_contractor_types as $type) : ?>
                        <div class="domains__item">
                            <label for="<?= $type['st_contractor_type_en'] ?>">קבלן <?= $type['st_contractor_type'] ?></label>
                            <select name="<?= $type['st_contractor_type_en'] ?>" id="<?= $type['st_contractor_type_en'] ?>">
                                <option value="" disabled selected hidden>בחר <?= $type['st_contractor_type'] ?></option>
                                <?php $contractors = getAllContractorsByTypeId($type['id'], $idToEdit) ?>
<!--                                --><?php //if($type['id'] == 8 || $type['id'] == 9) : ?><!--<option value="-1">פרטני לכל דייר</option>--><?php //endif ?>
                                <?php foreach ($contractors as $contractor) : ?>
                                    <option <?=$contractor['IS_SELECTED'] != null ? 'selected' : '' ?> value="<?= $contractor['user_id'] ?>"><?= $contractor['st_user_name'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    <?php endforeach ?>
                <?php else : ?>
                    <?php foreach ($all_contractor_types as $type) : ?>
                        <div class="domains__item">
                            <label for="<?= $type['st_contractor_type_en'] ?>">קבלן <?= $type['st_contractor_type'] ?></label>
                            <select name="<?= $type['st_contractor_type_en'] ?>" id="<?= $type['st_contractor_type_en'] ?>">
                                <option value="" disabled selected hidden>בחר <?= $type['st_contractor_type'] ?></option>
                                <?php $contractors = getAllContractorByTypesId($type['id']) ?>
<!--                                --><?php //if($type['id'] == 8 || $type['id'] == 9) : ?><!--<option value="-1">פרטני לכל דייר</option>--><?php //endif ?>
                                <?php foreach ($contractors as $contractor) : ?>
                                    <option value="<?= $contractor['user_id'] ?>"><?= $contractor['st_user_name'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    <?php endforeach ?>
                <?php endif ?>
            </div>
            <input class="btn btn_center" value="<?= $btn ?>" type="submit">
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

