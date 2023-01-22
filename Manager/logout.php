<?php
session_start();
unset($_SESSION['manager_id']);
header('location:../index.php');
?>