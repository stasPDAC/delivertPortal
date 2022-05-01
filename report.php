<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'reports';
global $icons;
global $user_type;
global $date_terms_confirmed;
$edit = false;
//unset($_COOKIE['first_time']);
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($msg){
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
if(isset($report_serial) && $user_type == 4 && $date_terms_confirmed == '0000-00-00 00:00:00'){
    header('Location: terms.php?id=' . $report_serial);
    exit;
}
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
if($action){
    $body_text = filter_input(INPUT_POST, 'body_text');
    if ($body_text == null) {
        $body_text = html_entity_decode($body_text);
    }
    $dateTest = filter_input(INPUT_POST, 'dateTest', FILTER_SANITIZE_SPECIAL_CHARS);

    $report_serial = filter_input(INPUT_POST, 'report_serial', FILTER_SANITIZE_SPECIAL_CHARS);
}
switch ($action){
    case 'performing_the_test':
        updatePerformingTheTest($body_text, $dateTest, $report_serial);
        break;
    case 'finish':
        $status = 2;
        updateReportBySerialNumber($report_serial, $status);
        break;
    case 'first_time':
        setcookie('first_time', 'good cookie', time() + 60 * 60 * 12 * 180, '/');
        break;
    default;
}
if(!$report_serial){
    $report_serial = filter_input(INPUT_GET, 'edit', FILTER_SANITIZE_SPECIAL_CHARS);
    $edit = true;
}
if($report_serial){
    $phone_checker = getPhoneCheckerBySerialId($report_serial);
    $client = getClientBySerialId($report_serial);
//    check($client);
    $faults = getAllFaultsBySerialId($report_serial);
    $current_category = 0;
    $project = getProjectByProjectId($client['project_id']);
//    check($project);
    if($client['i_status'] == 2){
        $status_check_count = 0;
        foreach($faults AS $status_check){
            if($status_check['i_fault_status'] == 4 || $status_check['i_fault_status'] == 5){
                $status_check_count++;
            }
        }
        if($status_check_count == count($faults) && $status_check_count != 0){
            $status = 3;
            updateReportStatusBySerialNumber($report_serial, $status);
        }
    }
}else{
    header('Location: /');
    exit;
}

include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg .'</p></div>' : '' ?>
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close"><?=$icons['close']?></span>
        <div class="container__title">מבצע הבדיקה</div>
        <div class="ticket">
            <div><?=$icons['engineering']?></div>
            <div>
                <p>שם:</p>
                <div class="pre_main"><?=$client['st_name_checker'] != '' ? $client['st_name_checker'] : '<span class="td_empty">אין נתומים</span>'?></div>
                <br>
                <p>נייד:</p>
                <div class="pre_main"><?=$phone_checker['st_phone_checker'] != '' ? $phone_checker['st_phone_checker'] : '<span class="td_empty">אין נתומים</span>'?></div>
            </div>
        </div>
        <div class="line"></div>
        <p>פרטים:</p>
        <div class="pre_main"><?=$client['st_checker'] != '' ? $client['st_checker'] : '<span class="td_empty">אין נתומים</span>'?></div>
        <br>
        <a class="btn btn_center" href="examiner.php?id=<?=$report_serial?>">עריכה</a>
    </div>
</div>

<div class="main_container">

    <div class="main_container__header">
        <div>
            <p class="main_container__title">מ.דוח - <?=$report_serial?></p>
            <p class="main_container__des"> (סטטוס דוח: <?=$client['st_report_status_name']?>)</p>
        </div>
        <div class="flex">
            <?php if($user_type == 4) : ?>
                <?php if($client['st_name_checker'] != '' || $phone_checker != '' || $client['st_checker'] != '') : ?>
                    <div><span id="myBtn" class="btn_header" title="פרטי מאנדס"><?=$icons['engineering']?></span></div>
                <?php elseif($client['i_status'] == 1) : ?>
                    <div><a href="examiner.php?id=<?=$report_serial?>" class="btn_header" title="הוספת פרטח מאנדס"><?=$icons['add_engineering']?></a></div>
                <?php else : ?>
                    <div><span id="myBtn" class="btn_header" title="פרטי מאנדס"><?=$icons['engineering']?></span></div>
                <?php endif ?>

                <?php if($client['i_status'] == 1) : ?>
                    <a class="btn btn_center" href="fault.php?id=<?=$report_serial?>">הוספת תקלה</a>
                <?php endif ?>
            <?php else : ?>
                <div><span id="myBtn" class="btn_header" title="פרטי מאנדס"><?=$icons['engineering']?></span></div>
            <?php endif ?>
            <?php if($user_type != 4) : ?>
                <a class="btn outline_btn" href="reports.php?id=<?=$project['id']?>">חזרה</a>
            <?php endif ?>
        </div>
    </div>

    <div class="main_container__box">
        <div class="main_container__item">
            <div class="title">שם פרוייקט</div>
            <div><?=$project['st_project_name']?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מנהל פרויקט</div>
            <div><?=$project['st_user_name']?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מספר נכס</div>
            <div><?=$client['st_property_number'] != '' ? $client['st_property_number'] : '<span class="td_empty">אין נתונים</span>'?></div>
        </div>
        <div class="main_container__line"></div>
        <div class="main_container__item">
            <div class="title">מספר דירה</div>
            <div><?=$client['st_apartment'] != '' ? $client['st_apartment'] : '<span class="td_empty">אין נתונים</span>'?></div>
        </div>
    </div>
<!--    --><?php //if($user_type == 4 && $client['i_status'] == 1 && !isset($_COOKIE['first_time'])) : ?>
<!--        <div class="container__title">הסבר ללקוח</div>-->
<!--        <div class="container__box">-->
<!--          <pre class="pre_main">לורם איפסום דולור סיט אמט, קונסקטורר אדיפיסינג אלית גולר מונפרר סוברט לורם שבצק יהול, לכנוץ בעריר גק ליץ, לפרומי בלוף קינץ תתיח לרעח. לת צשחמי צש בליא, מנסוטו צמלח לביקו ננבי, צמוקו בלוקריה שיצמה ברורק. קוואזי במר מודוף. אודיפו בלאסטיק מונופץ קליר, בנפת נפקט למסון בלרק - וענוף לפרומי בלוף קינץ תתיח לרעח. לת צשחמי צש בליא, מנסוטו צמלח לביקו ננבי, צמוקו בלוקריה שיצמה ברורק.-->
<!--קולורס מונפרד אדנדום סילקוף, מרגשי ומרגשח. עמחליף קולורס מונפרד אדנדום סילקוף, מרגשי ומרגשח. עמחליף קונדימנטום קורוס בליקרה, נונסטי קלובר בריקנה סטום, לפריקך תצטריק לרטי.-->
<!--נולום ארווס סאפיאן - פוסיליס קוויס, אקווזמן קוואזי במר מודוף. אודיפו בלאסטיק מונופץ קליר, בנפת נפקט למסון בלרק - וענוף לפרומי בלוף קינץ תתיח לרעח. לת צשחמי נולום ארווס סאפיאן - פוסיליס קוויס, אקווזמן הועניב היושבב שערש שמחויט - שלושע ותלברו חשלו שעותלשך וחאית נובש ערששף. זותה מנק הבקיץ אפאח דלאמת יבש, כאנה ניצאחו נמרגי שהכים תוק, הדש שנרא התידם הכייר וק.-->
<!--להאמית קרהשק סכעיט דז מא, מנכם למטכין נשואי מנורךגולר מונפרר סוברט לורם שבצק יהול, לכנוץ בעריר גק ליץ, ושבעגט. להאמית קרהשק סכעיט דז מא, מנכם למטכין נשואי מנורך. לורם איפסום דולור סיט אמט, קונסקטורר אדיפיסינג אלית. סת אלמנקום ניסי נון ניבאה. דס איאקוליס וולופטה דיאם. וסטיבולום אט דולור, קראס אגת לקטוס וואל אאוגו וסטיבולום סוליסי טידום בעליק. קונדימנטום קורוס בליקרה, נונסטי קלובר בריקנה סטום, לפריקך תצטריק לרטי.-->
<!--</pre>-->
<!--            <br>-->
<!--            <form method="post" action="report.php?id=--><?//=$report_serial?><!--">-->
<!--                <input type="hidden" name="action" value="first_time">-->
<!--                <button class="btn btn_center" type="submit">לא להציג הודעה זו שוב</button>-->
<!--            </form>-->
<!--        </div>-->
<!--    --><?php //endif ?>
    <?php if($faults) : ?>
        <div class="container__title">ממצאים</div>
    <?php else : ?>
        <div class="container__title">עדיין לא הוספו ממצאים</div>
    <?php endif ?>
    <div class="container__box fault">
        <?php $count = 1 ?>
        <?php foreach ($faults AS $fault) : ?>
            <?php if($fault['i_category'] != $current_category) : ?>
                <?php $count = 1 ?>
                <p class="fault_title">תחום <?=$fault['st_contractor_type']?></p>
                <div class="fault_line"></div>
            <?php endif ?>
            <div class="accordion">
                <?php if($client['i_status'] != 1 && $user_type != 4) : ?>
                    <div title="<?=$fault['st_fault_status_name']?>" class="fault_status" style="background-color: <?=$fault['st_color_status']?>"></div>
                <?php elseif($client['i_status'] != 1 && $user_type == 4) : ?>
                    <?php if($fault['i_fault_status'] == 4 || $fault['i_fault_status'] == 5) : ?>
                        <div title="<?=$fault['st_fault_status_name']?>" class="fault_status" style="background-color: <?=$fault['st_color_status']?>"></div>
                    <?php else : ?>
                        <div title="בטיפול" class="fault_status" style="background-color: #E85353"></div>
                    <?php endif; ?>
                <?php endif; ?>
                <?=$count?>: <?=$fault['st_title']?>
                <span><?=$icons['down']?></span>
            </div>
            <div class="fault_content panel">
                <div class="pre"><?=html_entity_decode($fault['st_fault_content'])?></div>
                <br>
                <?php if($client['i_status'] == 1 && $user_type == 4) : ?>
                    <a class="btn" href="fault.php?id=<?=$report_serial?>&edit=<?=$fault['fault_id']?>">עריכה</a>
                <?php endif; ?>
                <?php if($client['i_status'] != 1 && $user_type != 4) : ?>
                    <?php $notes = getAllNotesByFaultId($fault['fault_id']); ?>
                    <?php if($notes) : ?>
                        <div class="notes">
                            <?php foreach($notes AS $note) : ?>
                                <div class="notes_item">
                                    <div class="notes_item__date_and_status">
                                        <div class="notes_item__status">
                                            <span class="bold"><?= date('d.m.Y', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                            <span class="bold">&nbsp;<?= date('H:i', strtotime($note['st_note_date'])) ?>&nbsp;</span>
                                            <span>&nbsp;<?= $note['user_type'] ?>&nbsp;(<?= $note['st_user_name'] ?>)</span>
                                        </div>
                                        <div class="notes_item__date">
                                            <span><?=$note['st_last_fault_status_name']?>&nbsp;</span>
                                            <?=$icons['west']?>
                                            <span class="bold">&nbsp;<?=$note['st_fault_status_name']?><div class="notes_item__color" style="background-color: <?=$note['st_color_status']?>"></div></span>
                                        </div>
                                    </div>
                                    <?php if($note['st_note']) : ?>
                                        <div class="notes_item__text"><span class="bold">הערות: </span><?=$note['st_note']?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <a class="btn" href="note.php?id=<?=$report_serial?>&fault=<?=$fault['fault_id']?>">הוסף עדכון</a>
                <?php elseif ($client['i_status'] != 1 ) : ?>
                    <div class="notes">
                        <div class="notes_item">
                            <div class="notes_item__date_and_status">
                                <div class="notes_item__status">
                                    <span>סטטוס תקלה: </span>
                                    <?php if($fault['i_fault_status'] == 4 || $fault['i_fault_status'] == 5) : ?>
                                        <span class="bold">&nbsp;<?=$fault['st_fault_status_name']?><div class="notes_item__color" style="background-color: <?=$fault['st_color_status']?>"></div></span>
                                    <?php else : ?>
                                        <span class="bold">&nbsp;בטיפול<div class="notes_item__color" style="background-color: #E85353"></div></span>
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
    <?php if($client['i_status'] == 1 && $user_type == 4) : ?>
        <form action="" method="post">
            <input type="hidden" name="action" value="finish">
            <input type="hidden" name="report_serial" value="<?= $report_serial ?>">
            <button class="btn btn_center" type="submit" onclick="if (!confirm('לאחר סיום לא יהיה ניתן לערוך את הדוח')) return false;">סיימתי את הדוח</button>
        </form>
    <?php endif; ?>
</div>
<script src="js/report.js"></script>
<?php include_once 'includes/footer.php'; ?>
