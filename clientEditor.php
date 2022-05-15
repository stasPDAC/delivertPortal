<?php
require_once 'includes/config.php';
global $pdo;
global $icons;
global $mail_title;
global $user_type;
$page = 'projects';
$contractor_check = '';
$user_name_check = true;
$phone_first_check = true;
require_once 'includes/global.php';
require_once 'includes/functions.php';
if($user_type != 1){
    error404();
}
$project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if($project_id){
    $contractor_check = checkKitchenOrBathroomContractorByProjectId($project_id);
}
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if ($action) {
    $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_SPECIAL_CHARS);
    if(!$user_name){
        $msg_user_name = 'חובה להזין שם דייר';
        $user_name_check = false;
    }
    $phone_first = filter_input(INPUT_POST, 'phone_first', FILTER_SANITIZE_NUMBER_INT);
    if ($phone_first != '') {
        $phone_first = str_replace('-', '', $phone_first);
        if(strlen($phone_first) != 10){
            $msg_phone_first = 'מספר נייד שהוזן שגוי';
            $phone_first_check = false;
        }
    }else{
        $msg_phone_first = 'חובה להזין מספר נייד';
        $phone_first_check = false;
    }

    $phone_second = filter_input(INPUT_POST, 'phone_second', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($phone_second == null) {
        $phone_second = html_entity_decode($phone_second);
    }
    $mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!$mail) {
        $mail = null;
    }
    $property_type = filter_input(INPUT_POST, 'property_type', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($property_type == null) {
        $property_type = html_entity_decode($property_type);
    }
    $property_number = filter_input(INPUT_POST, 'property_number', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($property_number == null) {
        $property_number = html_entity_decode($property_number);
    }

    $floor = filter_input(INPUT_POST, 'floor', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($floor == null) {
        $floor = html_entity_decode($floor);
    }

    $apartment = filter_input(INPUT_POST, 'apartment', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($apartment == null) {
        $apartment = html_entity_decode($apartment);
    }
    $project_id = filter_input(INPUT_POST, 'project_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $type = 4;

    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_SPECIAL_CHARS) ? 1 : 0;

    $kitchen_name = filter_input(INPUT_POST, 'kitchen_name', FILTER_SANITIZE_SPECIAL_CHARS);
    if(!$kitchen_name){
        $kitchen_name = null;
    }

    $kitchen_number = filter_input(INPUT_POST, 'kitchen_number', FILTER_SANITIZE_SPECIAL_CHARS);
    if(!$kitchen_number){
        $kitchen_number = null;
    }

    $bathroom_name = filter_input(INPUT_POST, 'bathroom_name', FILTER_SANITIZE_SPECIAL_CHARS);
    if(!$bathroom_name){
        $bathroom_name = null;
    }

    $bathroom_number = filter_input(INPUT_POST, 'bathroom_number', FILTER_SANITIZE_SPECIAL_CHARS);
    if(!$bathroom_number){
        $bathroom_number = null;
    }
}
switch ($action) {
    case 'create':
        if($user_name_check != false && $phone_first_check != false){
            createNewClient($project_id, $user_name, $phone_first, $phone_second, $mail, $property_type, $property_number, $floor, $apartment, $type, $kitchen_name, $kitchen_number, $bathroom_name, $bathroom_number);
        }
        break;
    case 'update':
        $idToEdit = filter_input(INPUT_POST, 'idToEdit', FILTER_SANITIZE_SPECIAL_CHARS);
        if($user_name_check != false && $phone_first_check != false) {
            updateClientById($user_name, $project_id, $idToEdit, $phone_first, $phone_second, $mail, $property_type, $property_number, $floor, $apartment, $active, $kitchen_name, $kitchen_number, $bathroom_name, $bathroom_number);
        }
        break;
    default;
}
if (isset($_GET['edit'])) {
    $project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
    $idToEdit = filter_input(INPUT_GET, 'edit');

    $client = getClientById($idToEdit);
    $project = getProjectByProjectId($client['i_project_id']);
    $report = getReportBySerialId($client['i_serial_number']);
    $contractor_check = checkKitchenOrBathroomContractorByProjectId($client['i_project_id']);

    $title = "עריכת דייר - " . $client['st_user_name'];
    $action = "update";
    $btn = "שמור";
} else {
    $title = "הוספת דייר חדש";
    $action = "create";
    $btn = "הוסף דייר";
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>

<div class="main_container editor">
    <div class="main_container__header">
        <div class="main_container__title"><?=$title?></div>
        <a class="btn outline_btn" href="project.php?id=<?= isset($project_id) ? $project_id : $client['i_project_id'] ?>">חזרה</a>
    </div>
    <div class="main_container__box">
        <form class="form_editor" action="" method="post">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="idToEdit" value="<?= isset($idToEdit) ? $idToEdit : '' ?>">
            <input type="hidden" name="project_id" value="<?= isset($project_id) ? $project_id : $client['i_project_id'] ?>">

            <label for="user_name">*שם דייר</label>
            <input type="text" name="user_name" id="user_name" placeholder="הזן את שם הדייר"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_user_name']) : '' ?><?=isset($user_name) && !isset($idToEdit)?$user_name:''?>" required
                   oninvalid="this.setCustomValidity('אנא הזן את שם הדייר')" oninput="this.setCustomValidity('')">
            <?= isset($msg_user_name) ? '<p class="error">' . $msg_user_name . '</p>' : '' ?>

            <label for="phone_first">*מספר נייד</label>
            <input class="input_ltr" type="tel" name="phone_first" id="phone_first" placeholder="הזן את מספר הנייד"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_phone_first']) : '' ?><?=isset($phone_first) && !isset($idToEdit)?$phone_first:''?>" pattern="^[0-9\-\+]{9,15}$" required
                   oninvalid="this.setCustomValidity('אנא הזן את מספר הנייד')" oninput="this.setCustomValidity('')">
            <?= isset($msg_phone_first) ? '<p class="error">' . $msg_phone_first . '</p>' : '' ?>

            <label for="phone_second">מספר נייד משני</label>
            <input class="input_ltr" type="tel" name="phone_second" id="phone_second" placeholder="הזן את מספר הנייד משני"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_phone_second']) : '' ?><?=isset($phone_second) && !isset($idToEdit)?$phone_second:''?>" pattern="^[0-9\-\+]{9,15}$"
                   oninvalid="this.setCustomValidity('אנא הזן את מספר הנייד')" oninput="this.setCustomValidity('')">

            <label for="mail"><?= $mail_title ?></label>
            <input class="input_ltr" type="email" name="mail" id="mail" placeholder="הזן <?= $mail_title ?>"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_mail']) : '' ?><?=isset($mail) && !isset($idToEdit)?$mail:''?>" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                   oninvalid="this.setCustomValidity('אנא הזן <?= $mail_title ?> תקני')" oninput="this.setCustomValidity('')">

            <label for="property_type">סוג נכס</label>
            <input type="text" name="property_type" id="property_type" placeholder="הזן סוג נכס"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_property_type']) : '' ?><?=isset($property_type) && !isset($idToEdit)?$property_type:''?>">

            <label for="property_number">מספר נכס</label>
            <input type="text" name="property_number" id="property_number" placeholder="הזן מספר נכס"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_property_number']) : '' ?><?=isset($property_number) && !isset($idToEdit)?$property_number:''?>">

            <label for="floor">קומה</label>
            <input type="text" name="floor" id="floor" placeholder="הזן קומה"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_floor']) : '' ?><?=isset($floor) && !isset($idToEdit)?$floor:''?>">

            <label for="apartment">דירה</label>
            <input type="text" name="apartment" id="apartment" placeholder="הזן מספר דירה"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_apartment']) : '' ?><?=isset($apartment) && !isset($idToEdit)?$apartment:''?>">

            <?php foreach($contractor_check AS $check) : ?>
                <?php if($check['i_contractor_type'] == 8) : ?>
                    <label>קבלן מטבח</label>
                    <div class="flex_inputs">
                        <input type="text" name="kitchen_name" placeholder="הזן שם"
                               value="<?= isset($idToEdit) ? htmlentities($report['st_kitchen_name']) : '' ?><?=isset($kitchen_name) && !isset($idToEdit)?$kitchen_name:''?>"required
                               oninvalid="this.setCustomValidity('אנא הזן את שם הקבלן')" oninput="this.setCustomValidity('')">

                        <input type="tel" name="kitchen_number" placeholder="הזן מספר טלפון"
                               value="<?= isset($idToEdit) ? htmlentities($report['st_kitchen_number']) : '' ?><?=isset($kitchen_number) && !isset($idToEdit)?$kitchen_number:''?>" pattern="^[0-9\-\+]{9,15}$" required
                        oninvalid="this.setCustomValidity('אנא הזן את מספר הטלפון')" oninput="this.setCustomValidity('')">
                    </div>
                <?php elseif($check['i_contractor_type'] == 9) : ?>
                    <label>קבלן ארונות אמבט</label>
                    <div class="flex_inputs">
                        <input type="text" name="bathroom_name" placeholder="הזן שם"
                            value="<?= isset($idToEdit) ? htmlentities($report['st_bathroom_name']) : '' ?><?=isset($bathroom_name) && !isset($idToEdit)?$bathroom_name:''?>"required
                               oninvalid="this.setCustomValidity('אנא הזן את שם הקבלן')" oninput="this.setCustomValidity('')">

                        <input type="tel" name="bathroom_number" placeholder="הזן מספר טלפון"
                            value="<?= isset($idToEdit) ? htmlentities($report['st_bathroom_number']) : '' ?><?=isset($bathroom_number) && !isset($idToEdit)?$bathroom_number:''?>" pattern="^[0-9\-\+]{9,15}$" required
                               oninvalid="this.setCustomValidity('אנא הזן את מספר הטלפון')" oninput="this.setCustomValidity('')">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if(isset($idToEdit)) : ?>
                <label for="active" class="container" style="width: 100%">משתמש פעיל
                    <input type="checkbox" id="active" name="active" <?= $client['i_active'] == 1 ? 'checked' : '' ?>>
                    <span class="checkmark"></span>
                </label>
            <?php endif; ?>

            <input class="btn btn_center" value="<?= $btn ?>" type="submit">
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
