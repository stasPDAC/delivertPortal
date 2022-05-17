<?php
    date_default_timezone_set("Asia/Jerusalem");
	session_start();
	$dsn = "mysql:host=localhost;dbname=delivery_db_drp;charset=utf8";
	$options = [
	  PDO::ATTR_EMULATE_PREPARES   => false,
	  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	];
	$pdo = new PDO($dsn, "delivery_uder_drp", "6qfpVrvvnssg4ZLv", $options);



	// check errors
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

