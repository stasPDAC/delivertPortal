<?php
require_once 'includes/config.php';
global $pdo;
global $icons;
global $mail_title;
$page = 'users';
$sub_page = 'contractors';
include_once 'includes/global.php';
global $user_type;
if($user_type != 1){
    error404();
}
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if ($action) {
    $user_name = filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($user_name == null) {
        $user_name = html_entity_decode($user_name);
    }

    $phone_first = filter_input(INPUT_POST, 'phone_first', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($phone_first) {
        $phone_first = str_replace('-', '', $phone_first);
        if(strlen($phone_first) != 10){
            $phone_first = null;
        }
    }

    $mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!$mail) {
        $mail = null;
    }
    $checkboxes = [];
    foreach (getAllContractorTypes() as $checkbox) {
        $checked = filter_input(INPUT_POST, $checkbox['st_contractor_type_en'], FILTER_SANITIZE_SPECIAL_CHARS) ? 1 : 0;
        $checkboxes[$checkbox['id']] = $checked;
    }
    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_SPECIAL_CHARS) ? 1 : 0;

    $manager_id = filter_input(INPUT_POST, 'manager_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $type = 3;

}

switch ($action) {
    case 'create':
        if(!$phone_first){
            $msg_phone = 'מספר נייד שהוזן שגוי';
            break;
        }
        $phone_check = getAllPhonesUsersWithoutClients($phone_first);
        $mail_check = getAllMailUsersWithoutClients($mail);
        if($phone_check){
            $msg_phone = 'מספר הנייד שהזנת כבר קיים במערכת';
        }elseif($mail_check){
            $msg_mail = 'דוא"ל שהזנת כבר קיים במערכת';
        }else{
            createNewContractor($user_name, $phone_first, $mail, $type, $checkboxes);
        }
        break;
    case 'update':
        if(!$phone_first){
            $msg_phone = 'מספר נייד שהוזן שגוי';
            break;
        }
        $idToEdit = filter_input(INPUT_POST, 'idToEdit', FILTER_SANITIZE_SPECIAL_CHARS);
        updateContractorById($user_name, $idToEdit, $phone_first, $mail, $checkboxes, $active);
        break;
    default;
}
if (isset($_GET['edit'])) {
    $idToEdit = filter_input(INPUT_GET, 'edit');

    $client = getClientById($idToEdit);
    if(!$client){
        header('Location: /');
        exit;
    }
    $project = getProjectByProjectId($client['i_project_id']);

    $title = "עריכת קבלן - " . $client['st_user_name'];
    $action = "update";
    $btn = "שמור";
} else {
    $title = "הוספת קבלן חדש";
    $action = "create";
    $btn = "הוסף קבלן";
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>

<div class="main_container editor">
    <div class="main_container__header">
        <div class="main_container__title"><?= $title ?></div>
        <a class="btn outline_btn" href="contractors.php">חזרה</a>
    </div>
    <div class="main_container__box">
        <form class="form_editor" action="" method="post">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="idToEdit" value="<?= isset($idToEdit) ? $idToEdit : '' ?>">
            <input type="hidden" name="project_id"
                   value="<?= isset($project_id) ? $project_id : $client['i_project_id'] ?>">

            <label for="user_name">שם הקבלן</label>
            <input type="text" name="user_name" id="user_name" placeholder="הזן את שם הקבלן"
                   value="<?= isset($idToEdit) ? htmlentities($client['st_user_name']) : '' ?><?= isset($user_name) && !isset($idToEdit) ? $user_name : '' ?>" required
            oninvalid="this.setCustomValidity('אנא הזן שם מלא')" oninput="this.setCustomValidity('')">

            <label for="phone_first">*מספר נייד</label>
            <input class="input_ltr" type="tel" name="phone_first" id="phone_first" placeholder="הזן את מספר הנייד"
                   value="<?= isset($idToEdit) ? $client['st_phone_first'] : '' ?><?= isset($phone_first) && !isset($idToEdit) ? $phone_first : '' ?>" pattern="^[0-9\-\+]{9,15}$" required
                   oninvalid="this.setCustomValidity('אנא הזן מספר נייד תקני')" oninput="this.setCustomValidity('')">
            <?= isset($msg_phone) ? '<p class="error">' . $msg_phone . '</p>' : '' ?>


            <label for="mail"><?= $mail_title ?></label>
            <input class="input_ltr" type="email" name="mail" id="mail" placeholder="הזן <?= $mail_title ?>"
                   value="<?= isset($idToEdit) ? $client['st_mail'] : '' ?><?= isset($mail) && !isset($idToEdit) ? $mail : '' ?>" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                   oninvalid="this.setCustomValidity('אנא הזן <?= $mail_title ?> תקני')" oninput="this.setCustomValidity('')">
            <?= isset($msg_mail) ? '<p class="error">' . $msg_mail . '</p>' : '' ?>

            <?php if(isset($idToEdit)) : ?>
                <label for="active" class="container" style="width: 100%">משתמש פעיל
                    <input type="checkbox" id="active" name="active" <?= $client['i_active'] == 1 ? 'checked' : '' ?>>
                    <span class="checkmark"></span>
                </label>
            <?php endif; ?>

            <p class="middle_title">תחומים</p>
            <div class="domains__box">
                <?php if (isset($idToEdit)) : ?>
                    <?php foreach (getAllContractorTypesByUserId($idToEdit) as $checkbox) : ?>
                        <label for="<?= $checkbox['st_contractor_type_en'] ?>"
                               class="container">קבלן <?= $checkbox['st_contractor_type'] ?>
                            <input type="checkbox" id="<?= $checkbox['st_contractor_type_en'] ?>"
                                   name="<?= $checkbox['st_contractor_type_en'] ?>" <?= $checkbox['i_contractor_type'] != null ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                        </label>
                    <?php endforeach ?>
                <?php else : ?>
                    <?php foreach (getAllContractorTypes() as $checkbox) : ?>
                        <label for="<?= $checkbox['st_contractor_type_en'] ?>"
                               class="container"> קבלן <?= $checkbox['st_contractor_type'] ?>
                            <input type="checkbox" id="<?= $checkbox['st_contractor_type_en'] ?>"
                                   name="<?= $checkbox['st_contractor_type_en'] ?>">
                            <span class="checkmark"></span>
                        </label>
                    <?php endforeach ?>
                <?php endif ?>
            </div>
            <input class="btn btn_center" value="<?= $btn ?>" type="submit">
        </form>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
