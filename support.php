<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
$page = 'support';
global $icons;
global $user_type;
$mail_title = 'דוא&quot;ל' ;

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if($action){
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);

    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($phone) {
        $phone = str_replace('-', '', $phone);
        if(strlen($phone) != 10){
            $phone = null;
        }
    }

    $mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!$mail) {
        $mail = null;
    }

    $msg = filter_input(INPUT_POST, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
}
switch ($action) {
    case 'submit':
        if(!$phone){
            $msg_phone = 'מספר נייד שהוזן שגוי';
            break;
        }
        supportMail($name, $phone, $mail, $msg);
        break;
    default;
}
include_once 'includes/head.php';
?>
<main style="display: flex; justify-content: center; flex-direction: column">
<div class="main_container">
    <div class="main_container__header" style="justify-content: center">
        <div class="main_container__title">תמיכה</div>
    </div>
    <div class="container__box">
        <form class="form_editor fault" action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="submit">

            <label for="name">*שם מלא</label>
            <input type="text" id="name" name="name" placeholder="הזן שם מלא" required
                   oninvalid="this.setCustomValidity('אנא הזן שם מלא')" oninput="this.setCustomValidity('')">

            <label for="phone">*מספר נייד</label>
            <input type="tel" id="phone" name="phone" placeholder="הזן נייד" required
                   oninvalid="this.setCustomValidity('אנא הזן מספר נייד תקני')" oninput="this.setCustomValidity('')">
            <?= isset($msg_phone) ? '<p class="error">' . $msg_phone . '</p>' : '' ?>

            <label for="mail">*<?= $mail_title ?></label>
            <input type="email" id="mail" name="mail" placeholder="הזן <?= $mail_title ?>" required
                   oninvalid="this.setCustomValidity('אנא הזן <?= $mail_title ?> תקני')" oninput="this.setCustomValidity('')">
            <?= isset($msg_mail) ? '<p class="error">' . $msg_mail . '</p>' : '' ?>

            <label for="msg">הערות</label>
            <textarea id="msg" name="msg" placeholder="הזן הערות"></textarea>

            <input class="btn btn_center" value="שלח" type="submit">
        </form>
    </div>
</div>
</main>
</body>
</html>