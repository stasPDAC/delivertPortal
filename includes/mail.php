<?php
function sendRegistrationConfirmationMail($email, $name)
{
    if ($email) {
//        $html =  '<div style="width: 100%; display: flex; justify-content: center; flex-direction: column">
//                <div style="width: 90%; max-width: 600px; background: linear-gradient(0deg, rgba(172,221,110,1) 0%, rgba(135,178,112,1) 100%); margin: auto; padding: 30px; border-radius: 10px; margin: 20px auto">
//                    <p style="color: white; font-weight: bold; text-align: center; font-size: 30px; margin: 0">שלום ' . $name . '</p>
//                    <p style="color: white; text-align: center; font-size: 24px; margin: 10px auto 20px">מעולה! ההרשמה הסתיימה בהצלחה</p>
//                    <p style="color: white; text-align: center; font-size: 24px; margin: 10px auto 20px">נשאר לנו רק לאשר את החשבון ותכף תוכלו להיכנס לאתר.</p>
//                    <a href="https://bd5.pdactech.com/login.php" style="padding: 8px 16px; border-radius: 8px; box-shadow: 0px 1px 5px 0px rgba(75,81,91,.2); color: white; background-color: #0080b9; display: block; margin: auto; width: 200px; text-align: center; font-weight: bold; text-decoration: none; direction: rtl">בואו נתחיל ❯❯</a>
//                </div>
//            </div>';
        $domain = 'https://bd5.pdactech.com/';
        $html = '<div dir="rtl" style="width: 100%; display: flex; justify-content: center; flex-direction: column">
                        <div style="width: 100%; max-width: 900px; padding: 30px; border-radius: 10px; margin: 20px auto">
                            <div>
                                <img style="height: 50px; display: block; margin: 17px auto" src="' . $domain . 'images/logo.png" alt="BUILDEAL">
                                <p style="text-align: center; margin: 0; font-size: 20px; color: #222">BUILDEAL לוח עודפי הבניה של ישראל</p>
                            </div>
                            <div style="width: 100%; height: 40px; background: linear-gradient(90deg, rgba(180, 220, 250, 1) 0%, rgba(84, 121, 156, 1) 100%);"></div>
                            <div style="width: 100%; height: 40px; background: linear-gradient(270deg, rgba(172, 221, 110, 1) 0%, rgba(135, 178, 112, 1) 100%);"></div>
                            <div style="width: 90%; margin: 15px auto">
                                <p style="font-weight: bold; text-align: center; font-size: 30px; margin: 0 0 10px 0; color: #222">!תודה שנרשמת למערכת</p>
                                <p style="text-align: center; font-size: 20px; margin: 0 0 10px 0; line-height: normal; color: #222">עודפיו של אחד הם אוצרו של אחר! יחד נשמור על הסביבה,</p>
                                <p style="text-align: center; font-size: 20px; margin: 0; line-height: normal; color: #222">אתר BUILDEAL הוקם מתוך המחשבה על שימור חומרים באתר בנייה. בענף הבנייה יש שימוש בכל כך הרבה חומרי גלם שהולכים למטמנה. לוח קח-תן חכם זה, ביוזמת שיכון ובינוי נותן מענה למנהלי פרויקטים רבים, להם יש גישה אל אותם חומרי גלם, פרסום חומרי הגלם זמינה למגוון קבלני בנייה מכל הארץ, קלה לשימוש. </p>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; width: max-content; margin: auto">
                                <a href="' . $domain . 'login" style="padding: 8px 16px; border-radius: 8px; box-shadow: 0 1px 5px 0 rgba(75,81,91,.2); color: #222; background-color: #FFCE13; display: block; margin: 10px; width: 200px; text-align: center; text-decoration: none; font-size: 20px">בואו נתחיל ❯❯</a>
                            </div>
                            <div style="margin: 30px 0 20px; height: 1px; width: 100%; background-color: #979696"></div>
                            <div style="width: max-content; display: flex; margin: auto">
                                <a href=""><img style="width: 45px; height: 45px; margin: 0 4px" src="' . $domain . 'images/mail.png" alt=""></a>
                                <a href=""><img style="width: 45px; height: 45px; margin: 0 4px" src="' . $domain . 'images/mail.png" alt=""></a>
                                <a href=""><img style="width: 45px; height: 45px; margin: 0 4px" src="' . $domain . 'images/youtube_icon.png" alt=""></a>
                            </div>
                            <p style="text-align: center; margin: 5px 0 0; font-size: 20px; color: #222">BUILDEAL מייל זה נשלח כהודעה אוטומטית ממערכת</p>
                            <p style="text-align: center; margin: 0; font-size: 20px; color: #222"> לעזרה בכל נושא ניתן ליצור איתנו קשר במייל <a style="color: black" href="mailto:buildeal@solbo.co.il">buildeal@solbo.co.il</a></p>
                        </div>
                    </div>';

        $subject = "Mail from BUILDEAL site";
        $to = $email;
        $headers = "Content-type: text/html; charset=utf-8" . "\r\n";
        $headers .= "From: BuilDeal web server <noreply@buildeal.com>" . "\r\n";

        if (mail($to, $subject, $html, $headers)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

?>