<?php
// hadchi bach nlogoutiw luser
session_start();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
?>
