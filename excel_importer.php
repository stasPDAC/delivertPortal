<?php
require_once '../../../private/app/includes/config.php';
$page = 'groups_flat';
global $icons;


$siteId = filter_input(INPUT_GET, 'site_id', FILTER_SANITIZE_SPECIAL_CHARS);
$groupId = filter_input(INPUT_GET, 'group_id', FILTER_SANITIZE_SPECIAL_CHARS);
$targetEntity = filter_input(INPUT_GET, "te");



//TODO: get allowed target entities from SheetImporter class.

$emptySheetLink = "/uploads/sheet_patterns/group_import.xlsx";
if($targetEntity == "group"){
    $emptySheetLink = "/uploads/sheet_patterns/group_import.xlsx";
}
elseif ($targetEntity == "site"){
    $emptySheetLink = "/uploads/sheet_patterns/site_import.xlsx";
}


$sController = new SiteController;
$uController = new UserController;
$hController = new HierarchyController;

$accessRequest = [
    'site_id' => $siteId,
];

$user = $uController->validateUser($accessRequest);

/* Forbid access if provided siteId not exists in user's sites */
if($user->super != 1 && !in_array($siteId, $user->sites)){
    redirect('sites.php');
}


if ($targetEntity == "site"){
    if($user->super != 1){
        redirect('sites.php');
    }
    //Retrieve list of cameras for group allowed cameras list

    $siteCameras = $sController->models->camera->getSiteCameras($siteId);
}
else{
    if($user->super != 1 && $user->permissions['bool_manage_group_entities'] != 1){
        redirect('sites.php');
    }
}

/* Build global values */
$siteData = $sController->models->site->getSiteById($siteId);
$typesDic = buildTypeDictionary($siteData['i_site_type']);
$displayTitle = 'תצוגת '.$typesDic['userTypes'];
$headTitle = $siteData['st_site_name'] . ' - ' . HEAD_TITLE;
$lotSpace = Utils::getLotSpace($siteId);
$viewType = $siteData['i_view_type'];

$serverData = $hController->getGroupEditorData($siteId, $groupId);
$group = $serverData->groupData;

$pageSettings = [
    'self' => basename($_SERVER['PHP_SELF']),
    'excel_review' => 'excel_review.php',
];


include '../templates/head.php';
include '../templates/header.php';
?>

    <div class="container">
        <div class="container_header one_btn">
            <div class="container_title">ייבוא קובץ אקסל</div>
            <div class="container_btns">
                <?php if($targetEntity == "group"): ?>
                <button onclick="location.href='group_info.php?site_id=<?=$siteId?>&group_id=<?=$groupId?>'" class="btn"><?= $icons['back'] ?>חזרה</button>
                <?php else:?>
                <?php if($siteData['i_view_type'] == 2): ?>
                <button onclick="location.href='groups_struct.php?site_id=<?=$siteId?>'" class="btn"><?= $icons['back'] ?>חזרה</button>
                <?php else: ?>
                <button onclick="location.href='groups_flat.php?site_id=<?=$siteId?>'" class="btn"><?= $icons['back'] ?>חזרה</button>
                <? endif;?>
                <? endif;?>
            </div>
        </div>
        <?php if($targetEntity == "group"): ?>
        <p class="container_title" style="font-size: 1em;"><?= $typesDic['groupTypeAlt'] ?> מייבאת: <?= $group['st_group_name'] ?></p>
        <?php else: ?>
        <p class="container_title" style="font-size: 1em;">חניון מייבא: <?= $siteData['st_site_name'] ?></p>
        <?php endif;?>
        <div class="container_content">
            <div class="content_editor">
                <div class="content_editor_single_form">
                    <p class="content_title">הוראות</p>
                    <div class="editor_form">
                        <div class="editor_form_inputs">
                            <p>תהליך זה מאפשר העלאה מרוכזת של מורשי כניסה לחניות החברה או המשפחה.
                                יש להוריד את קובץ אקסל הדוגמא הרצ&quot;ב ולמלא את השדות לפי הכותרות. לאחר מכן לבצע
                                שמירה של הקובץ בשמו המקורי או בשם רצוי אחר.</p>
                            <br>

                            <a href="<?= $emptySheetLink ?>"><img class="csv_file" src="/assets/svg/excel.svg" alt="">
                                <p class="description">הורד קובץ</p></a>
                            <p>לאחר שהקובץ נשמר נקיש על הכפתור &quot;יבוא קובץ&quot; הרצ&quot;ב ונבחר את הקובץ אותו שמרנו.
                                המערכת תסרוק את הקובץ, אם הוא תקין היבוא יתבצע ואם לא יוצגו הערות לתיקון.</p>

                            <div class="content_btns_row" style="box-shadow: none !important;">
                                <form id="excel-import-form" method="post" enctype="multipart/form-data" action="<?=$pageSettings['excel_review']?>?site_id=<?= $siteId ?>&group_id=<?= $groupId ?>&te=<?= $targetEntity ?>">
                                    <?php if($targetEntity == "site"): ?>
                                        <p class="content_title">פיצול לוחיות רישוי</p>
                                    <label class="checkbox_label">פיצול
                                        <input id="bool_split" name="bool_split" type="checkbox" autocomplete="off">
                                        <span class="checkmark"></span>
                                    </label>
                                        <p class="content_title">מצלמות מורשות עבור קבוצות לייבוא</p>
                                    <?php foreach($siteCameras as $cam): ?>
                                    <label class="checkbox_label"><?= $cam['st_camera_alias'] ?>
                                        <input id="cam<?= $cam['id']?>" name="allowedCams[]" type="checkbox" autocomplete="off" value="<?= $cam['id'] ?>" checked>
                                        <span class="checkmark"></span>
                                    </label>
                                    <?php endforeach; ?>
                                    <?php endif;?>
                                    <div class="btn uploadExcel">
                                        <div class="file-upload">
                                            <div class="file-select">
                                                <div id="noFile" class="file-select-name"><?= $icons['add'] ?>ייבוא קובץ</div>
                                                    <input name="action" value="upload" type="hidden">
                                                    <input onchange="$('#excel-import-form').submit();" id="excel-file-input" name="spreadsheet" type="file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <p style="text-align: center">בהצלחה!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include '../templates/footer.php'; ?>