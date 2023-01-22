<?php

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

//fetch dorm-logo
$dorm_logo = $connect->query("SELECT * FROM dorm_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC)['dorm_logo'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="data:image/png;charset=utf8;base64,<?php echo base64_encode($dorm_logo);?>" type="image/x-icon">
    <link rel="stylesheet" href="../res/assets/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../res/assets/styles.css">
    <script src="../res/assets/jquery-3.5.1.js"></script>
    <script src="../res/assets/jquery.dataTables.min.js"></script>
    <script src="../res/assets/dataTables.bootstrap5.min.js"></script>
    <script src="../res/assets/scripts.js" defer></script>
    <script>
        $(document).ready(function () {
        $('#datatable').DataTable();
        $('.clickable_row').click(function (){
            window.location.href = $(this).data("href");
        })
        });
    </script>
</head>
<body class="container" style="background-color: rgba(150,150,150,0.3);">
    <div class="navigation_bar">
        <div class="flex_row" style="margin-right: auto; margin-left: 2em; column-gap: .5em; cursor:pointer;" onclick="window.location.href='../index.php'">
            <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($dorm_logo);?>" alt="icon" style="width: 2.5em;">
            <h2>DMS</h2>
        </div>
        <ul class="navigation_links">
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='students') {echo 'class="isactive"';}}?> href="students.php?tab=students&content=students">Students</a>
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='bills') {echo 'class="isactive"';}}?> href="bills.php?tab=bills">Bills</a>
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='requests') {echo 'class="isactive"';}}?> href="requests.php?tab=requests&sidetab=transfers&content=transfers">Requests</a>
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='settings') {echo 'class="isactive"';}}?> href="settings.php?tab=settings&sidetab=account&content=account">Settings</a>
        </ul>
    </div>
</body>
</html>