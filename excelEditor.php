<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'projects';
global $icons;
global $user_type;
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg){
    case 'createOk':
        $msg = 'דייר חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}

$project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$allClients = '' ;
if($project_id){
    $project = getProjectByProjectId($project_id);
    if (!$project){
        header('Location: /');
        exit;
    }
    $allClients = getAllClientsByProjectId($project_id);
    $allClients = '';
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
        <div class="main_container__title">הוספת דיירים באקסל</div>
        <?php if($user_type == 1) : ?>
            <div class="flex">
                <a title="עריכת פרויקט" class="btn outline_btn" href="project.php?id=<?=$project_id?>">חזרה</a>
<!--                <a title="הוספת דייר חדש" class="btn" href="clientEditor.php?id=">הוספת דייר חדש</a>-->
            </div>
        <?php endif ?>
    </div>
    <div class="container__box excel">
        <p>תהליך זה מאפשר העלאה מרוכזת של מורשי כניסה לחניות החברה או המשפחה. יש להוריד את קובץ אקסל הדוגמא הרצ"ב ולמלא את השדות לפי הכותרות. לאחר מכן לבצע שמירה של הקובץ בשמו המקורי או בשם רצוי אחר.</p>

        <a class="excel_box" href="uploads/import_sheet.xlsx">
            <img src="svg/excel.svg" alt="">
            <p>הורד קובץ</p>
        </a>
        <p>לאחר שהקובץ נשמר נקיש על הכפתור "יבוא קובץ" הרצ"ב ונבחר את הקובץ אותו שמרנו. המערכת תסרוק את הקובץ, אם הוא תקין היבוא יתבצע ואם לא יוצגו הערות לתיקון.</p>
        <form id="excel_upload_form" method="post" action="excel_review.php?project_id=<?=$project_id?>" enctype="multipart/form-data">
            <div class="file-upload">
                <div class="file-select btn">
                    <input type="hidden" name="action" value="upload">
                    <div id="noFile" class="file-select-name">העלאת קובץ</div>
                    <input onchange="$('#excel_upload_form').submit()" id="chooseNewFile" type="file" name="spreadsheet">
                </div>
            </div>
        </form>
    </div>
</div>
<script src="js/excelEditor.js"></script>
<?php include_once 'includes/footer.php'; ?>
