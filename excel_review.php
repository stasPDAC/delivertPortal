<?php require_once '../../../private/app/includes/config.php'; ?>
<?php

//autoload
require_once "/home/oferv2/web/ofer-il4.pdactech.com/private/vendor/autoload.php";

//Excel sheet importer
require_once '/home/oferv2/web/ofer-il4.pdactech.com/private/app/controllers/SheetImporter.php';
require_once '/home/oferv2/web/ofer-il4.pdactech.com/private/app/controllers/Scheme.php';
require_once '/home/oferv2/web/ofer-il4.pdactech.com/private/app/controllers/ParsedRow.php';
const UPLOAD_DIR = "/home/oferv2/web/ofer-il4.pdactech.com/public_html/uploads/sheet_imports";

use PhpOffice\PhpSpreadsheet\IOFactory;

//session_start();

$action = filter_input(INPUT_POST, "action");
$targetEntity = filter_input(INPUT_GET, "te");

//TODO: get allowed target entities from SheetImporter class.
const ALLOWED_ENTITIES = ["site", "group"];

if(!in_array($targetEntity, ALLOWED_ENTITIES)){
    $targetEntity = "group";
}


//Todo: get group id and site id from user
$targetGroupId = filter_input(INPUT_GET, "group_id");
$targetSiteId = filter_input(INPUT_GET, "site_id");

$sController = new SiteController;
$uController = new UserController;
$hController = new HierarchyController;

$accessRequest = [
    'site_id' => $targetSiteId,
];

$user = $uController->validateUser($accessRequest);

/* Forbid access if provided siteId not exists in user's sites */
if($user->super != 1 && !in_array($targetSiteId, $user->sites)){
    redirect('sites.php');
}

if ($targetEntity == "site"){
    if($user->super != 1){
        redirect('sites.php');
    }
}
else{
    if($user->super != 1 && $user->permissions['bool_manage_group_entities'] != 1){
        redirect('sites.php');
    }
}

/* Build global values */
$siteData = $sController->models->site->getSiteById($targetSiteId);
$typesDic = buildTypeDictionary($siteData['i_site_type']);
$displayTitle = 'תצוגת '.$typesDic['userTypes'];
$headTitle = $siteData['st_site_name'] . ' - ' . HEAD_TITLE;
$lotSpace = Utils::getLotSpace($targetSiteId);
$viewType = $siteData['i_view_type'];


$serverData = $hController->getGroupEditorData($targetSiteId, $targetGroupId);
$group = $serverData->groupData;

$pageMode = "new";

switch ($action){
    case "upload":
        $res = handleFileUpload();
        if(!$res->status){
            $pageMode = "upload_error";
        }
        else{
            $pageMode = "review";
            if(isset($_SESSION['EXCEL_PARSER'])){
                unlink(UPLOAD_DIR . '/' . $_SESSION['EXCEL_PARSER']['CURRENT_FILE']);
                unset($_SESSION['EXCEL_PARSER']);
            }

            $split = filter_input(INPUT_POST, "bool_split");
            if($split){
                $split = true;
            }
            else{
                $split = false;
            }

            //allowed cams list
            $allowedList = [];
            if(!empty($_POST['allowedCams'])) {
                foreach($_POST['allowedCams'] as $cam) {
                    $allowedList[] = filter_var($cam, FILTER_VALIDATE_INT);
                }
            }


            $_SESSION['EXCEL_PARSER']['GROUP_ID'] = $targetGroupId;
            $_SESSION['EXCEL_PARSER']['SITE_ID'] = $targetSiteId;
            $_SESSION['EXCEL_PARSER']['CURRENT_FILE'] = $res->file;
            $_SESSION['EXCEL_PARSER']['TARGET_ENTITY'] = $targetEntity;
            $_SESSION['EXCEL_PARSER']['DO_SPLIT'] = $split;
            $_SESSION['EXCEL_PARSER']['ALLOWED_CAM_LIST'] = $allowedList;
        }
        break;
    case "apply":
        $res = handleApply();
        if($res){
            $pageMode = "done";
            if($targetEntity == "group"){
                redirect("group_info.php?site_id=$targetSiteId&group_id=$targetGroupId");
            }
            else{
                //groups_struct.php?site_id=8
                redirect("groups_struct.php?site_id=$targetSiteId");
            }
            exit;
        }
        else{
            echo "error";
            exit;
        }
        break;
    default:
        unset($_SESSION['EXCEL_PARSER']);
        redirect("excel_importer.php?site_id=$targetSiteId&group_id=$targetGroupId");
        break;
}

function handleFileUpload(){
    $res = new stdClass();
    $allowed_sheets = ['Xlsx' => 'xlsx', 'Xls' => 'xls'];
    if(!isset($_FILES['spreadsheet'])){
        $res->status = false;
        $res->reason = "no sheet";
        return $res;
    }

    $file = $_FILES['spreadsheet'];

    //Todo: mime check


    try{
        $fileValidation = IOFactory::identify($file['tmp_name']);
    }
    catch(Exception $e){
        $res->status = false;
        $res->reason = $e;
        return $res;
    }

    if(!in_array($fileValidation, array_keys($allowed_sheets))){
        $res->status = false;
        $res->reason = "file not allowed";
        return $res;
    }

//    $ext = pathinfo($file['tmp_name'], PATHINFO_EXTENSION);
    $ext = $allowed_sheets[$fileValidation];
    $newFileName = date("Y-m-d-H:i:s") . "-" . bin2hex(openssl_random_pseudo_bytes(4)) . "." . $ext;
    $fullPath = UPLOAD_DIR . "/" . $newFileName;
    move_uploaded_file($file['tmp_name'], $fullPath);

    $res->status = true;
    $res->file = $newFileName;
    return $res;
}

function handleReview(){
    $inputFileName = UPLOAD_DIR . '/' . $_SESSION['EXCEL_PARSER']['CURRENT_FILE'];
    $targetGroupId = $_SESSION['EXCEL_PARSER']['GROUP_ID'];
    $targetSiteId = $_SESSION['EXCEL_PARSER']['SITE_ID'];
    $targetEntity = $_SESSION['EXCEL_PARSER']['TARGET_ENTITY'];
    $split = $_SESSION['EXCEL_PARSER']['DO_SPLIT'];

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($inputFileName);
    $reader->setReadDataOnly(true);
    $reader->setReadEmptyCells(false);
    $spreadsheet = $reader->load($inputFileName);

    /* older method */
    //$spreadsheet = IOFactory::load($inputFileName);

    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);

    //todo: gobal scheme building
    if($targetEntity == "group"){
        $scheme = new \OferExcelParser\Scheme(\OferExcelParser\Scheme::SCHEME_TYPE_EXISTING_GROUP);
    }
    elseif ($targetEntity == "site"){
        $scheme = new \OferExcelParser\Scheme(\OferExcelParser\Scheme::SCHEME_TYPE_EXISTING_SITE);
    }

    //Todo: set table headers from backend instead of user inuput.
    //$tableHeaders = $sheetData[0];
    $tableHeaders = array_column($scheme->getScheme(), "header");


    $parser = new \OferExcelParser\SheetImporter($scheme, $targetGroupId, $targetSiteId, $split);
    $parsed = $parser->parse($sheetData);
    return ["PARSER"=> $parser, "PARSED" => $parsed, "TABLE_HEADERS" => $tableHeaders];
}

function handleApply(){
    if(!isset($_SESSION['EXCEL_PARSER'])){
        return false;
    }

    $inputFileName = UPLOAD_DIR . '/' . $_SESSION['EXCEL_PARSER']['CURRENT_FILE'];
    $targetGroupId = $_SESSION['EXCEL_PARSER']['GROUP_ID'];
    $targetSiteId = $_SESSION['EXCEL_PARSER']['SITE_ID'];
    $targetEntity = $_SESSION['EXCEL_PARSER']['TARGET_ENTITY'];
    $split = $_SESSION['EXCEL_PARSER']['DO_SPLIT'];
    $allowedCamList = $_SESSION['EXCEL_PARSER']['ALLOWED_CAM_LIST'];

    //TODO: check clients permission to this site & group ids

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($inputFileName);
    $reader->setReadDataOnly(true);
    $reader->setReadEmptyCells(false);
    $spreadsheet = $reader->load($inputFileName);


    //$spreadsheet = IOFactory::load($inputFileName);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);

    if($targetEntity == "group"){
        $scheme = new \OferExcelParser\Scheme(\OferExcelParser\Scheme::SCHEME_TYPE_EXISTING_GROUP);
    }
    elseif ($targetEntity == "site"){
        $scheme = new \OferExcelParser\Scheme(\OferExcelParser\Scheme::SCHEME_TYPE_EXISTING_SITE);
    }

    $parser = new \OferExcelParser\SheetImporter($scheme, $targetGroupId, $targetSiteId, $split);
    $parsed = $parser->parse($sheetData);


    try{
        switch($targetEntity){
            default:
            case "group":
                $parser->insertToGroup();
                break;
            case "site":
                $parser->insertToSite($allowedCamList);
                break;
        }
    }
    Catch(Exception $e){
        echo $e;
        exit;
    }
    return true;
}


/* Element builders */
function buildRow($row_tag, $arr){
    $html = "<tr>\n";

    foreach($arr as $d){
        $html .= "<$row_tag>" . htmlentities($d) ."</$row_tag>";
    }

    $html .= "</tr>\n";
    return $html;
}

/* Element builders */
function buildRowParsed($row_tag, \OferExcelParser\ParsedRow $parsedRow){
    $html = "<tr>\n";
    global $icons;
    $data = $parsedRow->raw;
    $dataParsed = $parsedRow->row;

    $idx = 0;
    foreach($data as $ii => $d){
        $status_idx = $parsedRow->getCellValidationStatus($idx);
        $status = $parsedRow->getHebrew($status_idx);
        $type = $parsedRow->scheme->getScheme()[$ii]['type'];

        if($type == "PHONE"){
            $d = $dataParsed[$ii];
        }

        if($status_idx === \OferExcelParser\ParsedRow::VALID_STATUS){
            $html .= "<$row_tag>" . htmlentities($d) . "</$row_tag>";
        }
        else{
            $html .= '<' . $row_tag . '>' .
                     '   <span><a title="' . $status . '">' . $icons['info'] . '</a>' . htmlentities($d) . '</span>' .
                     '   <p class="tdError">' . $status .'</p>' .
                     '</'. $row_tag . '>' . "\n";
        }

//        $cellClass = $status_idx === \OferExcelParser\ParsedRow::VALID_STATUS ? "valid" : "invalid-text";
//        $invalidIcon = $status_idx === \OferExcelParser\ParsedRow::VALID_STATUS ? "" : getInvalidIcon($status);
//
//        $html .= "<$row_tag><div class='cell $cellClass'>" . htmlentities($d) . "<br>" . $invalidIcon . "</div></$row_tag>";
        $idx++;
    }

    $html .= "</tr>\n";
    return $html;
}

function getInvalidIcon($title){
    global $icons;
    $title = htmlentities($title);
    $icon = $icons['info'];
    return "<a title='$title'>$icon</a><span class='error' style='font-size: 0.8em; margin-right: -16px;'>$title</span>";
}

const EXPECTED_ROW_LEN = 6;



if($pageMode === "review"){
    $reviewData = handleReview();
    $tableHeaders = $reviewData['TABLE_HEADERS'];
    $parsed = $reviewData['PARSED'];
    $parser = $reviewData['PARSER'];

    $isValid = $parser->isValidSheet();
    $disableApplyButton = $isValid ? "" : "disabled";

}
elseif($pageMode == "done"){
    echo "done. ";
    exit;
}


$pageSettings = [
    'self' => basename($_SERVER['PHP_SELF']),
    'excel_review' => 'excel_review.php',
    'excel_importer' => 'excel_importer.php'
];

$split = filter_input(INPUT_POST, "bool_split");
?>



<?php $page = 'parkingCounter'; ?>
<?php global $icons ?>
<?php include '../templates/head.php'; ?>
<?php include '../templates/header.php'; ?>
    <div class="container">
        <div class="container_header">
            <div class="container_title">סקירה לפני יבוא</div>
            <div class="container_btns">
                <div class="mobile_btn_more">
                    <button class="btn more_options_btn"><?= $icons['more'] ?></button>
                    <div class="open_options">
                        <?php if($isValid) : ?>
                        <form id="excel-apply-form" method="post" enctype="multipart/form-data" action="<?=$pageSettings['excel_review']?>?site_id=<?= $targetSiteId ?>&group_id=<?= $targetGroupId ?>&te=<?= $targetEntity ?>">
                            <input name="action" value="apply" type="hidden">
                        </form>
                        <button id="excel-apply-btn" class="btn"><?= $icons['save'] ?>ייבא</button>
                        <?php else: ?>
                            <button class="btn vertical-align uploadExcel">
                                <div class="file-upload">
                                    <div class="file-select">
                                        <div id="noFile" class="file-select-name"><?= $icons['list'] ?>העלאת קובץ</div>
                                        <form id="excel-import-form" method="post" enctype="multipart/form-data" action="<?=$pageSettings['excel_review']?>?site_id=<?= $targetSiteId ?>&group_id=<?= $targetGroupId ?>&te=<?= $targetEntity ?>">
                                            <input name="action" value="upload" type="hidden">
                                            <?php if(isset($split) && $split): ?>
                                            <input name="bool_split" type="hidden" value="on">
                                            <?php endif; ?>
                                            <input onchange="$('#excel-import-form').submit();" id="excel-file-input" name="spreadsheet" type="file" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
                                        </form>
                                    </div>
                                </div>
                            </button>
                            <?php if($targetEntity == "site" && $split): ?>
                                    <input id="bool_split" name="bool_split" type="hidden" value="1" autocomplete="off">
                            <?php endif;?>
                        <?php endif; ?>
                        <button onclick="location.href='excel_importer.php?site_id=<?=$targetSiteId?>&group_id=<?=$targetGroupId?>&te=<?= $targetEntity ?>'" class="btn"><?= $icons['back'] ?>חזרה</button>
                    </div>
                </div>
            </div>
        </div>
        <?php if($targetEntity == "group"): ?>
            <p class="container_title" style="font-size: 1em;"><?= $typesDic['groupTypeAlt'] ?> מייבאת: <?= $group['st_group_name'] ?></p>
        <?php else: ?>
            <p class="container_title" style="font-size: 1em;">חניון מייבא: <?= $siteData['st_site_name'] ?></p>
        <?php endif;?>

        <?php if(!$isValid && $parsed > 0): ?>
        <p class="description error">התגלו נתונים לא תקינים בקובץ אקסל שהועלה. אנא תקנו והעלו את הקובץ מחדש.</p>
        <?php elseif(!$parsed && $parser->getValidationStatus() == $parser->sheetValidationStatuses['SHEET_BAD_HEADER']): ?>
        <p class="description error">כותרות הקובץ לא תואמות לכותרות המצופות ע"י המערכת. אנא השתמשו בקובץ לדוגמא.</p>
        <?php elseif(!$parsed && $parser->getValidationStatus() == $parser->sheetValidationStatuses['SHEET_EMPTY']): ?>
            <p class="description error">לא נמצא מידע תקין הניתן לייבא למערכת</p>
        <?php endif; ?>
        <?php if($parsed && count($parsed) > 0): ?>
        <div class="container_content">
            <div class="table_responsive" style="overflow-x: auto">
            <table>
                <thead>
                <?= buildRow("th", $tableHeaders)?>
                </thead>
                <tbody>
                <?php
                    //Sort parsed rows by having invalid rows first.
                    function cmpParsedRow(\OferExcelParser\ParsedRow $row_1, \OferExcelParser\ParsedRow $row_2){
                        if($row_1->isValidRow() && $row_2->isValidRow()){
                            return 0;
                        }
                        return $row_1->isValidRow() < $row_2->isValidRow() ? - 1 : 1;
                    }

                    usort($parsed, "cmpParsedRow");

                    for($ii = 0; $ii < count($parsed); $ii++){
                        $p = $parsed[$ii];
                        echo buildRowParsed("td", $p);
                    }
                ?>
                </tbody>
            </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
<script>
    $("#excel-apply-btn").on("click", function(){
        $("#excel-apply-form").submit();
    });
</script>
<?php include '../templates/footer.php'; ?>