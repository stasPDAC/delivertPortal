<?php
require_once 'includes/config.php';
global $user_id;
require_once 'includes/global.php';
require_once 'includes/functions.php';
$report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
switch ($action) {
    case 'confirm':
        $serial_id = filter_input(INPUT_POST, 'serial_id', FILTER_SANITIZE_SPECIAL_CHARS);
        updateDateTermsConfirmed($serial_id);
        break;
    default;
}

include_once 'includes/head.php';
include_once 'includes/header.php';
?>

<div class="main_container editor">
    <div class="main_container__header">
        <div class="main_container__title">תקנון שימוש</div>
        <div class="flex">
            <form action="" method="post">
                <input type="hidden" name="action" value="confirm">
                <input type="hidden" name="serial_id" value="<?= $report_serial ?>">
                <input class="btn btn_center" value="מסכים" type="submit">
            </form>
        </div>
    </div>
    <div class="main_container__box">
        <pre class="pre_main">דייר יקר שים לב
השימוש באתר מעיד על הסכמתך לתנאי השימוש, כלשונם ובמלואם, ומהווה הסכמה בהתנהגות לתנאי השימוש. המשתמש מתבקש לקרוא את תנאי השימוש בקפידה.
האתר מנוהל ומופעל על-ידי חברת שיכון ובינוי.
תנאי השימוש באתר מנוסחים בלשון זכר לצרכי נוחות בלבד, והם מתייחסים לנשים וגברים כאחד.
הגישה לאתר והשימוש בו ובכלל זה בתכנים הכלולים בו ובשירותים השונים הפועלים בו כפופים לתנאים המפורטים להלן (להלן: "תנאי השימוש") המסדירים את היחסים בין החברה לבין כל גולש/ת, צופה, משתמש/ת באתר או במידע המצוי בו ו/או מקבל/ת מידע ו/או שירות המפורסם באתר, במישרין או בעקיפין (להלן: "המשתמש/ת" או "המשתמשים").
תנאי השימוש חלים על השימוש באתר ובתכנים הכלולים בו באמצעות כל מחשב או מכשיר תקשורת אחר (כדוגמת טלפון סלולארי, סמארטפון, טאבלט וכיו"ב). כמו כן תנאי השימוש חלים על השימוש באתר בין באמצעות רשת האינטרנט ובין באמצעות כל רשת או אמצעי תקשורת אחרים.
בתנאי השימוש המונחים "מידע" או "תוכן" או "תכנים" כוללים מידע מכל מין וסוג, לרבות כל תוכן מילולי, חזותי, קולי, אור קולי (audio visual) או כל שילוב ביניהם וכן עיצוב, עיבוד, עריכה, הפצה ודרך הצגתם של התכנים לרבות (אך לא רק): תמונה, צילום, איור, הנפשה, תרשים, הדמיה, סרטון, קובץ קולי וכן כל אובייקט, תוכנה, קובץ, קוד מחשב, יישום, תסדיר (format), פרוטוקול, טופס אלקטרוני, מאגר נתונים וממשק וכל תו, סימן סמל וצלמית (icon).
המשתמש מתחייב שלא לבצע פעולות באתר העלולות להגביל או למנוע מאחרים שימוש באתר, ושלא להשתמש באתר באופן שאינו תואם לכל דין, לרבות כל שינוי או מחיקה של מידע או תוכן.
 המשתמש מתחייב שלא לבצע על פעולה המהווה זיוף, התחזות,הטעיה או הונאה.
 ככל שהמשתמש קיבל סיסמא או קוד גישה או הרשאה לצורך השימוש באתר או בכל חלק שלו, הוא מתחייב שלא לגלות את הסיסמא או קוד הגישה לכל אדם אחר ולא לאפשר לכל אדם אחר לעשות בהם או בהרשאה שימוש. המשתמש יהיה אחראי לכל שימוש שיעשה תוך שימוש בסיסמא, בקוד הגישה או בהרשאה שלו. </pre>
    </div>
    <form action="" method="post">
        <input type="hidden" name="action" value="confirm">
        <input type="hidden" name="serial_id" value="<?= $report_serial ?>">
        <input style="margin-top: 30px" class="btn btn_center" value="מסכים" type="submit">
    </form>
</div>

<?php include_once 'includes/footer.php'; ?>
