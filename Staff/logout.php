<?php
session_start();
unset($_SESSION['staff_id']);
header('location:../index.php');
?>