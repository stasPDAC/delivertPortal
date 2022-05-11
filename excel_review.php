<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'projects';
//autoload
require_once "/home/delivery/.composer/vendor/autoload.php";
//session_start();

//Excel sheet importer

const UPLOAD_DIR = "/home/delivery/web/deliveryportal.pdactech.com/private/uploads/sheet_imports";

use PhpOffice\PhpSpreadsheet\IOFactory;

//session_start();

$action = filter_input(INPUT_POST, "action");
$targetEntity = filter_input(INPUT_GET, "te");

//TODO: get allowed target entities from SheetImporter class.
const ALLOWED_ENTITIES = ["site", "group"];

if(!in_array($targetEntity, ALLOWED_ENTITIES)){
    $targetEntity = "group";
}


$projectId = filter_input(INPUT_GET, "project_id");



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

            $_SESSION['EXCEL_PARSER']['PROJECT_ID'] = $projectId;
            $_SESSION['EXCEL_PARSER']['CURRENT_FILE'] = $res->file;
        }
        break;
    case "apply":
        $res = handleApply();
        if($res){
            $pageMode = "done";
            header('Location: project.php?id=' . $projectId . '&msg=GroupCreateOk');
            exit;
        }
        else{
            echo "error message";
            exit;
        }
        break;
    default:
        unset($_SESSION['EXCEL_PARSER']);
        echo "Redirect me to error message";
        exit;
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
        $fileValidation = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file['tmp_name']);
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
    $projectId = $_SESSION['EXCEL_PARSER']['PROJECT_ID'];

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($inputFileName);
    $reader->setReadDataOnly(true);
    $reader->setReadEmptyCells(false);
    $spreadsheet = $reader->load($inputFileName);

    /* older method */
    //$spreadsheet = IOFactory::load($inputFileName);

    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);

    //todo: gobal scheme building
    $scheme = new \App\Scheme(\App\Scheme::SCHEME_TYPE_DEFAULT);


    //Todo: set table headers from backend instead of user inuput.
    //$tableHeaders = $sheetData[0];
    $tableHeaders = array_column($scheme->getScheme(), "header");


    $parser = new \App\SheetImporter($scheme, $projectId);
    $parsed = $parser->parse($sheetData);
    return ["PARSER"=> $parser, "PARSED" => $parsed, "TABLE_HEADERS" => $tableHeaders];
}

function handleApply(){
    if(!isset($_SESSION['EXCEL_PARSER'])){
        return false;
    }

    $inputFileName = UPLOAD_DIR . '/' . $_SESSION['EXCEL_PARSER']['CURRENT_FILE'];
    $projectId = $_SESSION['EXCEL_PARSER']['PROJECT_ID'];

    //TODO: check clients permission to this site & group ids

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($inputFileName);
    $reader->setReadDataOnly(true);
    $reader->setReadEmptyCells(false);
    $spreadsheet = $reader->load($inputFileName);


    //$spreadsheet = IOFactory::load($inputFileName);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);

    $scheme = new \App\Scheme(\App\Scheme::SCHEME_TYPE_DEFAULT);

    $parser = new \App\SheetImporter($scheme, $projectId);
    $parsed = $parser->parse($sheetData);


    try{
         return $parser->insertToProject();
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
function buildRowParsed($row_tag, \App\ParsedRow $parsedRow){
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

        if($status_idx === \App\ParsedRow::VALID_STATUS){
            $html .= "<$row_tag>" . htmlentities($d) . "</$row_tag>";
        }
        else{
            $html .= '<' . $row_tag . '>' .
                     '   <span class="td_info"><a title="' . $status . '">' . $icons['info'] . '</a>' . htmlentities($d) . '</span>' .
                     '   <p class="td_error">' . $status .'</p>' .
                     '</'. $row_tag . '>' . "\n";
        }

//        $cellClass = $status_idx === \App\ParsedRow::VALID_STATUS ? "valid" : "invalid-text";
//        $invalidIcon = $status_idx === \App\ParsedRow::VALID_STATUS ? "" : getInvalidIcon($status);
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
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">הוספת דיירים באקסל</div>
            <div class="flex">
                <a title="עריכת פרויקט" class="btn outline_btn" href="excelEditor.php?id=<?=$projectId?>">חזרה</a>
                <!--                <a title="הוספת דייר חדש" class="btn" href="clientEditor.php?id=">הוספת דייר חדש</a>-->
            </div>
    </div>
    <div class="container__box excel">
        <?php if(!$isValid && $parsed > 0): ?>
            <p class="error">התגלו נתונים לא תקינים בקובץ אקסל שהועלה. אנא תקנו והעלו את הקובץ מחדש.</p>
        <?php elseif(!$parsed && $parser->getValidationStatus() == $parser->sheetValidationStatuses['SHEET_BAD_HEADER']): ?>
            <p class="error">כותרות הקובץ לא תואמות לכותרות המצופות ע"י המערכת. אנא השתמשו בקובץ לדוגמא.</p>
        <?php elseif(!$parsed && $parser->getValidationStatus() == $parser->sheetValidationStatuses['SHEET_EMPTY']): ?>
            <p class="error">לא נמצא מידע תקין הניתן לייבא למערכת</p>
        <?php endif; ?>

        <?php if(!$isValid && $parsed > 0 || !$parsed): ?>
            <form id="excel_upload_form" method="post" action="excel_review.php?project_id=<?=$projectId?>" enctype="multipart/form-data">
                <div class="file-upload">
                    <div class="file-select btn">
                        <input type="hidden" name="action" value="upload">
                        <div id="noFile" class="file-select-name">העלאת קובץ</div>
                        <input onchange="$('#excel_upload_form').submit()" id="chooseNewFile" type="file" name="spreadsheet">
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p>הנתונים תקינים להמשך נא ללחוץ על כפתור שמור</p>
            <form id="excel-apply-form" method="post" action="excel_review.php?project_id=<?=$projectId?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="apply">
                <input class="btn btn_center" onchange="$('#excel-apply-form').submit()" type="submit" value="שמור">
            </form>
        <?php endif; ?>
    </div>

    <?php if($parsed && count($parsed) > 0): ?>


    <div class="line"></div>
    <table id="projects_table" class="table table-striped excel_table">
        <thead>
        <tr>
            <?= buildRow("th", $tableHeaders)?>
        </tr>
        </thead>
        <tbody>
        <?php
        //Sort parsed rows by having invalid rows first.
        function cmpParsedRow(\App\ParsedRow $row_1, \App\ParsedRow $row_2){
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

    <?php endif; ?>


</div>







<!--<script>-->
<!--    $("#excel-apply-btn").on("click", function(){-->
<!--        $("#excel-apply-form").submit();-->
<!--    });-->
<!--</script>-->
<script src="js/excelEditor.js"></script>
<?php include_once 'includes/footer.php'; ?>
