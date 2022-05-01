<?php
//$_SESSION['id'] = null;
//unset($_SESSION['id']);
session_destroy();
header('Location: /');
exit;
//var_dump($_SESSION['id']);
?>
