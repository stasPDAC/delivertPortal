<?php
    global $pdo;

    if ($_SESSION ['user_id']) {
        $query = 'SELECT * FROM tb_users WHERE id = :id';
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        $user_conn = $stmt->fetch();
        $stmt = null;

        $user_id = $user_conn['id'];
        $user_type = $user_conn['i_type'];
        $user_name_connect = $user_conn['st_user_name'];
        $date_terms_confirmed = $user_conn['date_terms_confirmed'];
        $domain = 'https://deliveryportal.pdactech.com/';
    } else {
        header('location: /');
        exit();
    }

    include_once 'icons.php';
    require_once 'functions.php';
    $mail_title = 'דוא&quot;ל' ;

