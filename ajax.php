<?php require_once 'includes/config.php'; ?>
<?php
	global $pdo;
	if (isset($_POST["invert"]) && ($_POST["invert"] == "true" || $_POST["invert"] == "false")) {
		$_SESSION["invert"] = $_POST["invert"];
	}
	if (isset($_POST["font_size"]) && ($_POST["font_size"] == "true" || $_POST["font_size"] == "false")) {
		$_SESSION["font_size"] = $_POST["font_size"];
	}
?>
<?php
	$catId = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_NUMBER_INT);
	$stmt = $pdo->prepare("SELECT * FROM categories WHERE iChildOf = ? AND iStatus = 1");
	$stmt->bindParam(1,$catId);
	$stmt->execute();
	//$sub_categories = $stmt->fetchAll();
	


	$response = array();
	
	while($row = $stmt->fetch()){
		$response[$row['id']] = $row['stName'];
	}

	echo json_encode($response);
	$stmt = null;	
?>