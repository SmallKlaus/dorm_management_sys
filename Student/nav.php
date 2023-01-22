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
        $('textarea').keyup(function() {
        var characterCount = $(this).val().length,
        current = $('#current'),
        maximum = $('#maximum'),
        theCount = $('#the-count');
      
        current.text(characterCount);
        });
        });
        
    </script>
</head>
<body class="container responsive_body" style="background-color: rgba(150,150,150,0.3);">
    <div class="responsive_navbar">
        <div class="flex_row containing">
            <div class="flex_row contained" onclick="window.location.href='../index.php'">
                <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($dorm_logo);?>" alt="icon" style="width: 2.5em;">
                <h2>DMS</h2>
            </div>
            <a id="menu_toggle" href="javascript:void(0);" onclick="opennavbar()"><img style="height: 3em; filter:invert(50%);" src="../res/icons/Menuicon.png" alt="Navigation Links"></a>
        </div>
        <ul class="flex_row responsive_navlinks" id="responsive_navlinks">
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='bills') {echo 'class="isactive"';}}?> href="bills.php?tab=bills">My Bills</a>
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='requests') {echo 'class="isactive"';}}?> href="requests.php?tab=requests&sidetab=complaints&content=complaints">My Requests</a>
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='settings') {echo 'class="isactive"';}}?> href="settings.php?tab=settings&sidetab=account&content=account">Account</a>
            <a <?php if(isset($_GET['tab'])) {if($_GET['tab']=='logout') {echo 'class="isactive"';}}?> href="logout.php?tab=logout&sidetab=account&content=account">Log out</a>
        </ul>
    </div>
</body>
</html>