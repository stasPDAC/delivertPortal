<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="./../images/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="robots" content="noindex"/>
    <title>פורטל דוחות מסירה</title>
    <link rel="stylesheet" type="text/css" href="../css/reset_style.css">
    <link rel="stylesheet" type="text/css" href="../css/datatables.css?ver=<?= rand(0, 9999) ?>">
    <link rel="stylesheet" type="text/css" href="../css/style.css?ver=<?= rand(0, 9999) ?>">
    <script src="../js/jquery-3.6.0.js"></script>
    <script src="../js/datatables.min.js"></script>
    <script src="../libs/tinymce/tinymce.min.js"></script>

    <?php
        global $icons;
        if (isset($_SESSION["invert"]) && $_SESSION["invert"] == "true") {echo '<style>body{filter: invert(100%)}</style>';}
        if (isset($_SESSION["font_size"]) && $_SESSION["font_size"] == "false") {echo '<style>body{font-size: 20px}</style>';}
    ?>

</head>
<body>
<button aria-label="אפשרויות נגישת" aria-expanded="false" class="accessibility_btn show_accessibility"><?= $icons['accessible'] ?></button>
<div class="accessibility_open">
    <button aria-label="סגירת אפשרויות נגישת" class="show_accessibility"><?= $icons['close'] ?></button>
    <button aria-pressed="true" aria-label="גודל טקסט סטנדרטי" id="small_text"><?= $icons['standardText'] ?></button>
    <button aria-pressed="false" aria-label="הגדלת גודל טקסט" id="large_text"><?= $icons['enlargeText'] ?></button>
    <button aria-pressed="false" aria-label="היפוך צבעים לנגטיב" id="invert"><?= $icons['invert'] ?></button>
    <button role="link" aria-label="עבוד לדף נצהרת נגישות" onclick="location.href='/accessibility'"><?= $icons['info'] ?></button>
</div>
<script src="../js/head.js"></script>