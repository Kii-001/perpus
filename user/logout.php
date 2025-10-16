<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

session_destroy();
header('Location: ../login.php');
exit();
?>