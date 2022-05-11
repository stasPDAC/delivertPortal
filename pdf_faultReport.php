<?php
require_once 'includes/config.php';
include_once 'includes/global.php';

//autoload
require_once "/home/delivery/.composer/vendor/autoload.php";

//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf = new App\ShikunBinuiPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//
$fontname = TCPDF_FONTS::addTTFfont('fonts/Assistant-Regular.ttf', 'TrueTypeUnicode', '', 96);
$pdf->SetFont($fontname, '', 14, '', false);


$tagvs = ['div' => [['h' => 0, 'n' => 0], ['h' => 0, 'n' => 0]]];
$pdf->setHtmlVSpace($tagvs);

// set default header data
//$PDF_HEADER_LOGO = "logo.png";//any image file. check correct path.
//$PDF_HEADER_LOGO_WIDTH = "20";
//$PDF_HEADER_TITLE = "Dunken the KING";
//$PDF_HEADER_STRING = "Tel 1234567896 Fax 987654321\n"
//    . "E abc@gmail.com\n"
//    . "www.abc.com";
//$pdf->SetHeaderData($PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING);
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);


// set margins
$pdf->SetMargins(10, 30, 10);
$pdf->SetHeaderMargin(10, 30, 10);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->setRTL(true);

// add a page
$pdf->AddPage();
global $user_name_connect;


$project_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
$user_id = filter_input(INPUT_GET, 'u', FILTER_SANITIZE_SPECIAL_CHARS);

$faults = getAllReportsFaultsByContractorsIdAndProjectId($user_id, $project_id);
$project = getProjectByProjectId($project_id);
$contactor_name = 'דו"ח ריק';
if(isset($faults['0']['st_user_name'])){
    $contactor_name = 'דו"ח ליקויים לקבלן - ' . $faults['0']['st_user_name'];
}

$current_category = 0;
$count = 1;
$foreach = '';


// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('שיכון ובינוי - סולל בונה תשתיות בע"מ');
$pdf->SetTitle('דו"ח ליקויים לקבלן');

foreach($faults AS $fault) {
    $client = getClientBySerialId($fault['i_serial_number']);
    if($client['st_apartment'] != ''){
        $apartment = $client['st_apartment'];
    }else{
        $apartment = 'אין';
    }
//    check($client);
    if($fault['type_id'] != $current_category) {
        $count = 1;
        if($foreach){
            $foreach .= "</div>";
        }
        $foreach .= '<div style="page-break-inside:auto;"><div style="border-bottom: 1px solid #15386F; color: #15386F; font-size: 14px; font-weight: bold;">תחום '. $fault['st_contractor_type'] .'</div><br>';
    }
    $foreach .= '<table style="padding: 5px; margin: 15px 0;">';
    $foreach .= '<tr>';
    $foreach .= '<td style="border: 1px solid #cccccc; font-size: 12px; width: 4%;"><b>' . $count . '</b></td>';
    $foreach .= '<td style="border: 1px solid #cccccc; font-size: 12px; width: 10%;"><b style="color: #15386F">דירה: ' . $apartment . '</b></td>';
    $foreach .= '<td style="border: 1px solid #cccccc; font-size: 12px; width: 86%">' . $fault['st_title'] . '</td>';
    $foreach .= '</tr>';
    $notes = getAllFinishNotesByFaultId($fault['id']);
    if($fault['st_fault_content']){
        $foreach .= '<tr><td colspan="3" style="border: 1px solid #cccccc; font-size: 12px; width: 100%">' . $fault['st_fault_content'] . '</td></tr>';
    }else{
        $foreach .= '<tr><td colspan="3" style="border: 1px solid #cccccc; font-size: 12px; color: #cccccc">אין הערות</td></tr>';
    }
    $foreach .= '</table>';
    $foreach .= '<div></div>';
    $current_category = $fault['type_id'];
    $count++;

};



$html = '
<html dir="rtl">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
*{
font-family: DejaVu Sans, sans-serif;
}

td{
font-size: 12px;
}

</style>
<p style="text-align: center; color: #15386F; font-weight: bold; font-size: 25px; text-decoration: underline;">' . $contactor_name . '</p>
    <table>
        <tr>
            <td style="line-height: 0; text-align: right;">לכבוד: <b>' . $user_name_connect . '</b></td>
            <td style="line-height: 0; text-align: left;">תאריך הפקת דוח: <b>' . date("m.d.Y") . '</b></td>
        </tr>
    </table>
    <div></div>
    <table style="border: 1px solid #cccccc; padding: 5px">
        <tr style="font-weight: bold">
            <td style="border-left: 1px solid #cccccc;">שם פרוייקט</td>
            <td style="border-left: 1px solid #cccccc;">כתובת</td>
            <td style="border-left: 1px solid #cccccc;">מנהל פרויקט</td>
        </tr>
        <tr>
            <td style="border-left: 1px solid #cccccc;">' . $project['st_project_name'] . '</td>
            <td style="border-left: 1px solid #cccccc;">' . $project['st_project_address'] . '</td>
            <td style="border-left: 1px solid #cccccc;">' . $project['st_user_name'] . '</td>
        </tr>
    </table>
    <h3 style="text-align: center">רשימת ליקויים</h3>
    <div>' . $foreach . '</div>
</html>
';

$temphtml = new DOMDocument();
$temphtml->loadHTML($html);

$imgs = $temphtml->getElementsByTagName("img");

//$ps = $temphtml->getElementsByTagName("p");
//for($ii = 0; $ii < count($ps); $ii++){
//    $ps[$ii]->setAttribute("style", "page-break-inside:avoid");
//}
$toDelete = [];
$types = [
  "image/jpeg" => "jpg",
  "image/png" => "png",
];
for($ii = 0; $ii < count($imgs); $ii++){
    $b64 = $imgs[$ii]->getAttribute("src");
    $imgs[$ii]->setAttribute("style", "page-break-inside:avoid; display: block; margin-left: auto; margin-right: auto; width: 216px; text-align: center;height: auto;");
//    $imgs[$ii]->setAttribute("style", "text-align: center;");
    if(strpos($b64, ";base64,") === false){
        continue;
    }
    $imageContent = file_get_contents($b64);
    //$path = tempnam("./", 'tcpdftempimg');
    $ext = "jpg";

    if(strpos($b64, "image/jpeg") != false){
        $ext =  "jpg";
    }
    if(strpos($b64, "image/png") != false){
        $ext =  "png";
    }

    $path = bin2hex(openssl_random_pseudo_bytes(16));


    file_put_contents ($path, $imageContent);
    $toDelete[] = $path;
    $imgs[$ii]->setAttribute("src", 'https://deliveryportal.pdactech.com/' . $path);
    $imgs[$ii]->setAttribute("width", '216');
    $imgs[$ii]->setAttribute("height", 'auto');
//    $imgs[$ii]->setAttribute("align", 'left');
//    $ps[$ii]->setAttribute("align", "left");
//    $imgs[$ii]->setAttribute("dir", 'ltr');

}

$h = ($temphtml->saveHTML($temphtml->documentElement));
//$h = str_replace("<img", "<img style=\"text-align: left\"", $h);
//

//echo $h;
//exit();

$pdf->setRTL(true);

$pdf->writeHTML($h, true, false, true, false, '');


foreach($toDelete as $d){
    unlink($d);
}




//// add a page
//$pdf->AddPage();
////
//$html = '<h4>Second page</h4>';
//$pdf->setRTL(true);
//$pdf->writeHTML($h, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();
//Close and output PDF document
$pdf->Output('exportReport'. $project_id .'.pdf', 'D');