<?php
require_once 'includes/config.php';
global $pdo;
global $icons;
global $mail_title;
$page = 'users';
$sub_page = 'admins';
include_once 'includes/global.php';

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

    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_SPECIAL_CHARS) ? 1 : 0;

    $manager_id = filter_input(INPUT_POST, 'manager_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $type = 1;

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
            createNewAdmin($user_name, $phone_first, $mail, $type);
        }
        break;
    case 'update':
        if(!$phone_first){
            $msg_phone = 'מספר נייד שהוזן שגוי';
            break;
        }
        $idToEdit = filter_input(INPUT_POST, 'idToEdit', FILTER_SANITIZE_SPECIAL_CHARS);
        updateAdminById($user_name, $idToEdit, $phone_first, $mail, $active);
        break;
    default;
}
if (isset($_GET['edit'])) {
    $idToEdit = filter_input(INPUT_GET, 'edit');

    $admin = getAdminById($idToEdit);
    if(!$admin){
        header('Location: /');
        exit;
    }
    $project = getProjectByProjectId($admin['i_project_id']);

    $title = "עריכת מנהל מערכת - " . $admin['st_user_name'];
    $action = "update";
    $btn = "שמור";
} else {
    $title = "הוספת מנהל מערכת חדש";
    $action = "create";
    $btn = "הוסף מנהל";
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>

<div class="main_container editor">
    <div class="main_container__header">
        <div class="main_container__title"><?=$title?></div>
        <a class="btn outline_btn" href="users.php">חזרה</a>
    </div>
    <div class="main_container__box">
        <form class="form_editor" action="" method="post">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="idToEdit" value="<?= isset($idToEdit) ? $idToEdit : '' ?>">
            <input type="hidden" name="project_id" value="<?= isset($project_id) ? $project_id : $admin['i_project_id'] ?>">

            <label for="user_name">*שם מנהל מערכת</label>
            <input type="text" name="user_name" id="user_name" placeholder="הזן את שם המנהל"
                   value="<?= isset($idToEdit) ? html_entity_decode($admin['st_user_name']) : '' ?><?= isset($user_name) && !isset($idToEdit) ? $user_name : '' ?>" required
                   oninvalid="this.setCustomValidity('אנא הזן שם מלא')" oninput="this.setCustomValidity('')">

            <label for="phone_first">*מספר נייד</label>
            <input type="tel" name="phone_first" id="phone_first" placeholder="הזן את מספר הנייד"
                   value="<?= isset($idToEdit) ? $admin['st_phone_first'] : '' ?><?= isset($phone_first) && !isset($idToEdit) ? $phone_first : '' ?>" pattern="^[0-9\-\+]{9,15}$" required
                   oninvalid="this.setCustomValidity('אנא הזן מספר נייד תקני')" oninput="this.setCustomValidity('')">
            <?= isset($msg_phone) ? '<p class="error">' . $msg_phone . '</p>' : '' ?>


            <label for="mail"><?= $mail_title ?></label>
            <input type="email" name="mail" id="mail" placeholder="הזן <?= $mail_title ?>"
                   value="<?= isset($idToEdit) ? $admin['st_mail'] : '' ?><?= isset($mail) && !isset($idToEdit) ? $mail : '' ?>" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                   oninvalid="this.setCustomValidity('אנא הזן <?= $mail_title ?> תקני')" oninput="this.setCustomValidity('')">
            <?= isset($msg_mail) ? '<p class="error">' . $msg_mail . '</p>' : '' ?>

            <?php if(isset($idToEdit)) : ?>
                <label for="active" class="container" style="width: 100%">משתמש פעיל
                    <input type="checkbox" id="active" name="active" <?= $admin['i_active'] == 1 ? 'checked' : '' ?>>
                    <span class="checkmark"></span>
                </label>
            <?php endif; ?>

            <input class="btn btn_center" value="<?= $btn ?>" type="submit">
        </form>
    </div>
</div>
<script src="js/managers.js?var=2"></script>
<?php include_once 'includes/footer.php'; ?>
