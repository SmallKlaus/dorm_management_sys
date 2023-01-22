<?php
session_start();
unset($_SESSION['student_id']);
header('location:../index.php');
?>