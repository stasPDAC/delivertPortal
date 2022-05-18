<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'projects';
global $icons;
global $user_type;
global $date_terms_confirmed;
global $user_name_connect;
$edit = false;
setcookie('first_time', 'good cookie', time() + 60 * 60 * 12 * 180, '/');
//unset($_COOKIE['first_time']);
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg) {
    case 'pdfOk':
        $msg = 'קובץ הוסף בהצלחה';
        break;
    case 'onlyPdf':
        $msg = 'אפשר לעלות רק קבצי PDF';
        break;
    case 'errorPdf':
        $msg = 'משהו השתבש, נסו שוב';
        break;
    case 'createOk':
        $msg = 'עדכון חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}
$report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
checkReportPermission($report_serial);
if (isset($report_serial) && $user_type == 4 && $date_terms_confirmed == '0000-00-00 00:00:00') {
    header('Location: terms.php?id=' . $report_serial);
    exit;
}
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if ($action) {
    $body_text = filter_input(INPUT_POST, 'body_text');
    if ($body_text == null) {
        $body_text = html_entity_decode($body_text);
    }
    $dateTest = filter_input(INPUT_POST, 'dateTest', FILTER_SANITIZE_SPECIAL_CHARS);

    $report_serial = filter_input(INPUT_POST, 'report_serial', FILTER_SANITIZE_SPECIAL_CHARS);

    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_SPECIAL_CHARS) ? 1 : 0;
}
switch ($action) {
    case 'upload_pdf':

        $random = rand(1111, 9999);
        $newFileName = $random . basename($_FILES['file']['name']);
        $targetFolder = "uploads/";
        $targetFolder = $targetFolder . $newFileName;
        $ok = 1;
        $file_type = $_FILES['file']['type'];
        if ($file_type == "application/pdf") {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFolder)) {
                uploadPdfFile($newFileName, $report_serial);
                echo "The file " . basename($_FILES['file']['name']) . " is uploaded";
                header('Location: report.php?id=' . $report_serial . '&msg=pdfOk');
                exit();
            } else {
                echo "Problem uploading file";
                exit();
            }
        } else {
            echo "You may only upload PDFs, JPEGs or GIF files.<br>";
            header('Location: report.php?id=' . $report_serial . '&msg=onlyPdf');
            exit();
        }

        uploadPdfFile();
        break;
    case 'performing_the_test':
        updatePerformingTheTest($body_text, $dateTest, $report_serial);
        break;
    case 'finish':
        $status = 2;
        if ($active == 1){
            updateReportBySerialNumber($report_serial, $status);
        }
        break;
    case 'first_time':
        setcookie('first_time', 'good cookie', time() + 60 * 60 * 12 * 180, '/');
        break;
    default;
}
if (!$report_serial) {
    $report_serial = filter_input(INPUT_GET, 'edit', FILTER_SANITIZE_SPECIAL_CHARS);
    $edit = true;
}
if ($report_serial) {
    $phone_checker = getPhoneCheckerBySerialId($report_serial);
    $client = getClientBySerialId($report_serial);
    $client_name = $client['st_user_name'];
    $client_phone_first = $client['st_phone_first'];
    $client_phone_second = $client['st_phone_second'];
    if(!$client){
        error404();
    }
    $faults = getAllFaultsBySerialId($report_serial);
    $current_category = 0;
    $project = getProjectByProjectId($client['project_id']);
    if ($client['i_status'] == 2) {
        $status_check_count = 0;
        foreach ($faults as $status_check) {
            if ($status_check['i_fault_status'] == 4 || $status_check['i_fault_status'] == 5) {
                $status_check_count++;
            }
        }
        if ($status_check_count == count($faults) && $status_check_count != 0) {
            $status = 3;
            updateReportStatusBySerialNumber($report_serial, $status, $client_name, $client_phone_first, $client_phone_second);
        }
    }
}

include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg . '</p></div>' : '' ?>
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close"><?= $icons['close'] ?></span>
        <div class="container__title">מבצע הבדיקה</div>
        <div class="ticket">
            <div><?= $icons['engineering'] ?></div>
            <div>
                <p>שם:</p>
                <div class="pre_main"><?= $client['st_name_checker'] != '' ? $client['st_name_checker'] : '<span class="td_empty">אין נתומים</span>' ?></div>
                <br>
                <p>נייד:</p>
                <div class="pre_main"><?= $phone_checker['st_phone_checker'] != '' ? $phone_checker['st_phone_checker'] : '<span class="td_empty">אין נתומים</span>' ?></div>
            </div>
        </div>
        <div class="line"></div>
        <p>פרטים:</p>
        <div class="pre_main"><?= $client['st_checker'] != '' ? $client['st_checker'] : '<span class="td_empty">אין נתומים</span>' ?></div>
        <?php if ($client['i_status'] == 1 && $user_type == 4) : ?>
            <br>
            <a class="btn btn_center" href="examiner.php?id=<?= $report_serial ?>">עריכה</a>
        <?php endif; ?>
    </div>
</div>

<div id="welcome" class="modal" <?= $user_type == 4 && $client['i_status'] == 1 && !isset($_COOKIE['first_time']) ? 'style="display:flex"' : '' ?>>
    <div class="modal-content">
        <div id="view_1"
             class="views" <?= $user_type == 4 && $client['i_status'] == 1 && !isset($_COOKIE['first_time']) ? 'style="display:block"' : '' ?>>
            <div class="container__title">ברוכים הבאים <?= $user_name_connect ?></div>
            <pre class="pre_main">דיירים יקרים, ברוכים הבאים הביתה. ברכות לרגל רכישת הדירה!</pre>
            <br>
            <a id="step_1" class="btn btn_center">המשך</a>
        </div>
        <div id="view_2" class="views">
            <div class="container__title">הסבר ללקוח</div>
            <pre class="pre_main">באפשרותך להזין תקלות לבד או בעזרת עזרת איש מקצוע, במידה ותבחר בעזרת בעל המקצוע יש להוסיף את פרטיו בעזרת כפתור "הוסף בודק", שים לב כי בעת הוספת הבודק, הוא יקבל גישה לדוח ואף יוכל להגישו בעזרת קישור הכניסה, על אחריות הדייר לאכוף זאת. </pre>
            <br>
            <a id="step_2" class="btn btn_center">המשך</a>
        </div>
        <div id="view_3" class="views">
            <div class="container__title">מזל טוב</div>
            <pre class="pre_main">לאחר ביצוע הבדיקה, יתאפשר לך להזין את התקלות הרלונטיות, בעזרת לחיצה על "הוספת תקלה" יפתח לך מסך לפירוט התקלה, צירוף תמונות ואפשרות לצירוף קובץ PDF בלבד. הדוח ישמר בצורה אוטומטית ותוכל לערוך אותו בכל עת עד לסיום הדוח והגשתו לבדיקה, שים לב עד שלא תגיש באופן סופי זה לא יחשף למנהל הפרויקט, הגשת הדוח היא פעולה חד פעמים ולא ניתן לערוך אותו בשנית. </pre>
            <br>
            <a id="step_3" class="btn btn_center">סגור</a>
        </div>
    </div>
</div>

<div id="reportCompletion" class="modal">
    <div class="modal-content">
        <span class="close"><?= $icons['close'] ?></span>
        <p class="container__title">דייר יקר שים לב!</p>
        <pre class="pre">פעולת הגשת הדוח היא פעולה סופית.
לא ניתן לחזור לערוך או להגיש דוח נוסף.
במידה ולא תגיש את הדוח, פרטי התקלות שרשמת ישמרו בצורה אוטומטית.
שים לב כי רק לאחר הגשת הדוח הוא יועבר לבדיקת הקבלן ולטיפול.</pre>
        <form action="" method="post">
            <input type="hidden" name="action" value="finish">
            <input type="hidden" name="report_serial" value="<?= $report_serial ?>">
            <label for="active" class="container" style="width: 100%; position: relative; padding-right: 40px; cursor: pointer">אני מאשר את ביצוע הפעולה הסופית.
                <input type="checkbox" id="active" name="active"  onchange="myFunctionFinish()">
                <span class="checkmark"></span>
            </label>
            <button id="finish_btn" class="btn btn_center finish_btn" type="submit" onclick="if (!confirm('לאחר סיום לא יהיה ניתן לערוך את הדוח')) return false;">סיום דוח</button>
        </form>
    </div>
</div>

<div class="main_container">

    <div class="main_container__header">
        <div>
            <p class="main_container__title">מ.דוח - <?= $report_serial ?></p>
            <p class="main_container__des"> (סטטוס דוח: <?= $client['st_report_status_name'] ?>)</p>
        </div>
        <div class="flex">
            <?php if ($user_type == 4) : ?>
                <div><span id="info" class="btn_header info_btn" title="הסבר ללקוח"><?= $icons['info'] ?></span></div>

                <?php if ($client['i_status'] == 1) : ?>
                    <?php if ($client['st_name_checker'] != '' || $phone_checker['st_phone_checker'] != '' || $client['st_checker'] != '') : ?>
                        <div><span id="myBtn" class="btn_header" title="פרטי מהנדס"><?= $icons['engineering'] ?></span>
                        </div>
                    <?php else : ?>
                        <div><a href="examiner.php?id=<?= $report_serial ?>" class="btn_header"
                                title="הוספת פרטי מהנדס"><?= $icons['add_engineering'] ?></a></div>
                    <?php endif ?>
                    <form id="pdf_upload_form" method="post" enctype="multipart/form-data">
                        <div class="file-upload">
                            <div class="file-select btn outline_btn">
                                <input type="hidden" name="action" value="upload_pdf">
                                <input type="hidden" name="report_serial" value="<?= $report_serial ?>">
                                <div id="noFile" class="file-select-name">העלת PDF</div>
                                <input accept="application/pdf" onchange="$('#pdf_upload_form').submit()" type="file"
                                       name="file" size="50">
                            </div>
                        </div>
                    </form>

                    <a id="reportCompletionBtn" class="btn">סיום דוח</a>

<!--                    <form action="" method="post">-->
<!--                        <input type="hidden" name="action" value="finish">-->
<!--                        <input type="hidden" name="report_serial" value="--><?//= $report_serial ?><!--">-->
<!--                        <button class="btn" type="submit"-->
<!--                                onclick="if (!confirm('לאחר סיום לא יהיה ניתן לערוך את הדוח')) return false;">סיום דוח-->
<!--                        </button>-->
<!--                    </form>-->
                <?php endif; ?>


            <?php else : ?>
                <div><span id="myBtn" class="btn_header" title="פרטי מהנדס"><?= $icons['engineering'] ?></span></div>
            <?php endif ?>
            <?php if ($client['i_status'] != 1) : ?>
                <a href="pdf_clientReport.php?id=<?= $report_serial ?>" class="btn_header" target="_blank"
                   title='דו"ח ביקורת ליקויים'><?= $icons['client_report_pdf'] ?></a>
            <?php endif ?>
            <?php if ($user_type == 4 && $client['i_status'] == 3) : ?>
                <a href="pdf_exportReport.php?id=<?= $report_serial ?>" class="btn_header" target="_blank"
                   title='דו"ח ביקורת איכות'><?= $icons['report_pdf'] ?></a>
            <?php elseif ($user_type != 4) : ?>
                <a href="pdf_exportReport.php?id=<?= $report_serial ?>" class="btn_header" target="_blank"
                   title='דו"ח ביקורת איכות'><?= $icons['report_pdf'] ?></a>
                <a class="btn outline_btn" href="reports.php?id=<?= $project['id'] ?>">חזרה</a>
            <?php endif ?>
        </div>
    </div>


    <div class="main_container__box">
        <div class="main_container__item">
            <div class="title">שם פרוייקט</div>
            <div><?= $project['st_project_name'] ?></div>
        </div>
        <div class="main_container__line desc"></div>
        <div class="main_container__item desc">
            <div class="title">מנהל פרויקט</div>
            <div><?= $project['st_user_name'] ?></div>
        </div>
        <div class="main_container__line desc"></div>
        <div class="main_container__item desc">
            <div class="title">מספר נכס</div>
            <div><?= $client['st_property_number'] != '' ? $client['st_property_number'] : '<span class="td_empty">אין נתונים</span>' ?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מספר דירה</div>
            <div><?= $client['st_apartment'] != '' ? $client['st_apartment'] : '<span class="td_empty">אין נתונים</span>' ?></div>
        </div>
    </div>


    <div class="line"></div>

    <div class="main_container__header" <?= $user_type == 4 && $client['i_status'] == 1 ? '' : 'style="justify-content: center"' ?>>
        <div>
            <?php if ($faults) : ?>
                <div class="container__title fault">ממצאים</div>
            <?php else : ?>
                <div class="container__title fault">עדיין אין ממצאים</div>
            <?php endif ?>
        </div>
        <div class="flex">
            <?php if ($user_type == 4 && $client['i_status'] == 1) : ?>
                <a class="btn btn_center" href="fault.php?id=<?= $report_serial ?>">הוספת תקלה</a>
            <?php endif ?>
        </div>
    </div>


    <div class="container__box fault">
        <?php $count = 1 ?>
        <?php foreach ($faults as $fault) : ?>
            <?php if ($fault['i_category'] != $current_category) : ?>
                <?php $count = 1 ?>
                <p class="fault_title">תחום <?= $fault['st_contractor_type'] ?></p>
                <div class="fault_line"></div>
            <?php endif ?>
            <div class="accordion">
                <?php if ($client['i_status'] != 1 && $user_type != 4) : ?>
                    <div title="<?= $fault['st_fault_status_name'] ?>" class="fault_status"
                         style="background-color: <?= $fault['st_color_status'] ?>"></div>
                <?php elseif ($client['i_status'] != 1 && $user_type == 4) : ?>
                    <?php if ($fault['i_fault_status'] == 4 || $fault['i_fault_status'] == 5) : ?>
                        <div title="<?= $fault['st_fault_status_name'] ?>" class="fault_status"
                             style="background-color: <?= $fault['st_color_status'] ?>"></div>
                    <?php else : ?>
                        <div title="בטיפול" class="fault_status" style="background-color: #E85353"></div>
                    <?php endif; ?>
                <?php endif; ?>
                <?= $count ?>: <?= $fault['st_title'] ?>
                <span><?= $icons['down'] ?></span>
            </div>
            <div class="fault_content panel">
                <div class="pre"><?= html_entity_decode($fault['st_fault_content']) ?></div>
                <br>
                <?php if ($client['i_status'] == 1 && $user_type == 4) : ?>
                    <a class="btn" href="fault.php?id=<?= $report_serial ?>&edit=<?= $fault['fault_id'] ?>">עריכה</a>
                <?php endif; ?>
                <?php if ($client['i_status'] != 1 && $user_type != 4) : ?>
                    <?php $notes = getAllNotesByFaultId($fault['fault_id']); ?>
                    <?php if ($notes) : ?>
                        <div class="notes">
                            <?php foreach ($notes as $note) : ?>
                                <div class="notes_item">
                                    <div class="notes_item__date_and_status">
                                        <div class="notes_item__status">
                                            <span class="bold"><?= date('d.m.Y', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                            <span class="bold">&nbsp;<?= date('H:i', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                            <span>&nbsp;<?= $note['user_type'] ?>&nbsp;(<?= $note['st_user_name'] ?>)</span>
                                        </div>
                                        <div class="notes_item__date">
                                            <span><?= $note['st_last_fault_status_name'] ?>&nbsp;</span>
                                            <?= $icons['west'] ?>
                                            <span class="bold">&nbsp;<?= $note['st_fault_status_name'] ?><div
                                                        class="notes_item__color"
                                                        style="background-color: <?= $note['st_color_status'] ?>"></div></span>
                                        </div>
                                    </div>
                                    <?php if ($note['st_note']) : ?>
                                        <div class="notes_item__text"><span
                                                    class="bold">הערות: </span><?= $note['st_note'] ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <a class="btn" href="note.php?id=<?= $report_serial ?>&fault=<?= $fault['fault_id'] ?>">הוסף
                        עדכון</a>
                <?php elseif ($client['i_status'] != 1) : ?>
                    <div class="notes">
                        <div class="notes_item">
                            <div class="notes_item__date_and_status">
                                <div class="notes_item__status">
                                    <span>סטטוס תקלה: </span>
                                    <?php if ($fault['i_fault_status'] == 4 || $fault['i_fault_status'] == 5) : ?>
                                        <span class="bold">&nbsp;<?= $fault['st_fault_status_name'] ?><div
                                                    class="notes_item__color"
                                                    style="background-color: <?= $fault['st_color_status'] ?>"></div></span>
                                    <?php else : ?>
                                        <span class="bold">&nbsp;בטיפול<div class="notes_item__color"
                                                                            style="background-color: #E85353"></div></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php $current_category = $fault['i_category']; ?>
            <?php $count++; ?>
            <div class="fault_gray_line"></div>
        <?php endforeach ?>
    </div>


    <?php if (isset($client['st_pdf_file']) && $client['st_pdf_file'] != null) : ?>
        <div class="line"></div>
        <div class="main_container__header" style="justify-content: center">
            <div>
                <div class="container__title fault">קובץ PDF</div>
            </div>
        </div>
        <div class="container__box" style="max-width: 180px; margin: auto">
            <a class="excel_box" href="uploads/<?= $client['st_pdf_file'] ?>" target="_blank">
                <img src="svg/pdf.svg" alt="">
                <p>צפייה בקובץ</p>
            </a>
        </div>
    <?php endif ?>


</div>
<script>
    function myFunctionFinish() {
        if(document.getElementById("active").checked){
            document.getElementById("finish_btn").style.opacity = "1";
            document.getElementById("finish_btn").style.userSelect = "inherit";
            document.getElementById("finish_btn").style.pointerEvents  = "inherit";
        }else{
            document.getElementById("finish_btn").style.opacity = "0.4";
            document.getElementById("finish_btn").style.userSelect = "none";
            document.getElementById("finish_btn").style.pointerEvents  = "none";
        }
    }
</script>
<script src="js/report.js"></script>
<?php include_once 'includes/footer.php'; ?>
