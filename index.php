<?php require_once 'includes/config.php'; ?>
<?php require_once 'includes/functions.php'; ?>
<?php require_once 'includes/icons.php'; ?>
<?php
global $pdo;
$mail_msg = filter_input(INPUT_GET, 'mail', FILTER_SANITIZE_SPECIAL_CHARS);
if ($mail_msg) {
    $mail_msg = 'המייל נשלח בהצלחה';
}
$logOut = filter_input(INPUT_GET, 'logOut', FILTER_SANITIZE_SPECIAL_CHARS);
if ($logOut) {
    setcookie('remember_me', '', time() - 60, '/');
    $report_id = filter_input(INPUT_GET, 'report', FILTER_SANITIZE_NUMBER_INT);
    if ($report_id != '') {
        header('location: /?report=' . $report_id);
    } else {
        header('location: /');
    }
    exit();
}
if (isset($_COOKIE['remember_me'])) {
    $query = 'SELECT id, i_type, i_serial_number FROM tb_users WHERE i_active = 1 AND st_remember_token LIKE :st_remember_token';
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':st_remember_token', $_COOKIE['remember_me']);
    $stmt->execute();
    $result = $stmt->fetch();
    $stmt = null;
    if ($result) {
        $_SESSION['user_id'] = $result['id'];
        if ($result['i_type'] == 1 || $result['i_type'] == 5) {
            header('location: projects.php');
            exit();
        } elseif ($result['i_type'] == 2) {
            $projects = getAllProjectsByManagerId($result['id']);
            if (count($projects) > 1) {
                header('location: projects.php');
                exit();
            } else {
                header('location: reports.php?id=' . $projects['0']['project_id']);
                exit();
            }
        } elseif ($result['i_type'] == 3) {
            header('location: notes.php');
            exit();
        } elseif ($result['i_type'] == 4) {
            header('location: report.php?id=' . $result['i_serial_number']);
            exit();
        }
    }
}


$remember_me = '';
$report_id = filter_input(INPUT_GET, 'report', FILTER_SANITIZE_NUMBER_INT);
$submit = filter_input(INPUT_POST, 'submit', FILTER_SANITIZE_SPECIAL_CHARS);

if ($submit) {
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
}
    if (isset($phone) && preg_match("/^[0-9\-\+]{9,15}$/", $phone)) {
        if(!$report_id){
            $query = 'SELECT id, st_phone_first, st_phone_second, i_type FROM tb_users WHERE i_active = 1 AND st_phone_first = :st_phone_first';
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':st_phone_first', $phone);
            $stmt->execute();
            $result = $stmt->fetch();
            $stmt = null;
        }else{
            $query = 'SELECT id, st_phone_first FROM tb_users WHERE (st_phone_first = :st_phone_first OR st_phone_second = :st_phone_second OR st_phone_checker = :st_phone_checker) AND i_active = 1 AND i_type = 4 AND i_serial_number = :i_serial_number';
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':st_phone_first', $phone);
            $stmt->bindParam(':st_phone_second', $phone);
            $stmt->bindParam(':st_phone_checker', $phone);
            $stmt->bindParam(':i_serial_number', $report_id);
            $stmt->execute();
            $result = $stmt->fetch();
            $stmt = null;
        }
        if ($result) {
            $user_id = $result['id'];
            $otp_token = bin2hex(openssl_random_pseudo_bytes(32));
            $otp_pass = rand(100000,999999);
            try {
                $stmt = $pdo->prepare('UPDATE tb_users SET
                    OTP_token = :OTP_token,
                    OTP_pass = :OTP_pass,
                    OTP_date = NOW()
                    WHERE id = :id'
                );

                $stmt->bindParam(':OTP_token', $otp_token);
                $stmt->bindParam(':OTP_pass', $otp_pass);
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                $stmt = null;

                $_SESSION['OTP_token'] = $otp_token;
                $_SESSION['pass'] = $otp_pass;

//                $message = $otp_pass . ' הוא קוד האימות החד-פעמי שלך, והוא בתוקף ל-5 הדקות הקרובות. צוות שיכון ובינוי';
//                $sms_log = send_sms($phone,$message,$user_id);

                if($report_id){
                    header('Location: authentication.php?report=' . $report_id);
                }else{
                    header('Location: authentication.php');
                }
                exit;
            } catch (Exception $e) {

            }
        } else {
            $msg = 'מספר נייד לא קיים (פנה לתמיכה לצורך רישום)';
        }
    }

session_destroy();
?>
<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="../images/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="noindex"/>
    <title>פורטל דוחות מסירה</title>
    <link rel="stylesheet" type="text/css" href="./css/reset_style.css">
    <link rel="stylesheet" type="text/css" href="./css/login_style.css?ver=<?= rand(0, 9999); ?>">
    <script src="../js/jquery-3.6.0.js"></script>

    <?php
    global $icons;
    if (isset($_SESSION["invert"]) && $_SESSION["invert"] == "true") {
        echo '<style>body{filter: invert(100%)}</style>';
    }
    if (isset($_SESSION["font_size"]) && $_SESSION["font_size"] == "false") {
        echo '<style>body{font-size: 20px}</style>';
    }
    ?>

</head>
<body>
<button aria-label="אפשרויות נגישת" aria-expanded="false"
        class="accessibility_btn show_accessibility"><?= $icons['accessible'] ?></button>
<div class="accessibility_open">
    <button aria-label="סגירת אפשרויות נגישת" class="show_accessibility"><?= $icons['close'] ?></button>
    <button aria-pressed="true" aria-label="גודל טקסט סטנדרטי" id="small_text"><?= $icons['standardText'] ?></button>
    <button aria-pressed="false" aria-label="הגדלת גודל טקסט" id="large_text"><?= $icons['enlargeText'] ?></button>
    <button aria-pressed="false" aria-label="היפוך צבעים לנגטיב" id="invert"><?= $icons['invert'] ?></button>
    <button role="link" aria-label="עבוד לדף נצהרת נגישות"
            onclick="location.href='/accessibility'"><?= $icons['info'] ?></button>
</div>
<?= isset($mail_msg) ? '<div class="msg" id="msg"><p>' . $mail_msg .'</p></div>' : '' ?>
<div class="login">
    <a href="/"><img class="login__logo" src="svg/SolelBuilds_logo.svg" alt=""></a>
<!--    <div class="line"></div>-->
    <p class="title">פורטל דוחות מסירה</p>
    <p>הזן מספר נייד לקבלת אימות OTP</p>
    <form action="" method="post">
        <input class="input_ltr" type="tel" name="phone" placeholder="מספר נייד"
               value="<?= isset($phone) ? $phone : '' ?>" pattern="^[0-9\-\+]{9,15}$" autocomplete="off" required
               oninvalid="this.setCustomValidity('אנא הזן מספר נייד תקני')" oninput="this.setCustomValidity('')">

        <?= isset($msg) ? '<p class="error">' . $msg . '</p>' : '' ?>

        <input class="btn" name="submit" type="submit" value="שלח">
        <a class="support" href="support.php" target="_blank">יצירת קשר לתמיכה</a>
    </form>
</div>
<script>
    setTimeout(() => {
    const box = document.getElementById('msg')
    if(box){
    box.style.display = 'none';
    }
    }, 2000);
</script>
<script src="../js/head.js"></script>
</body>
</html>
