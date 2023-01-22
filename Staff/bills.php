<?php

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!isstafflogged())
{
    header('location:../index.php');
}

$staff_id = $_SESSION['staff_id'];
$error_message = '';
$success_message = '';

//paying bills
if(isset($_GET['operation']) && $_GET['operation']=='pay')
{
    if(isset($_GET['bill']) && !empty($_GET['bill']))
    {
        //creating record and changing bill_status
        $record_id = uniqid();
        $record_type = 'bill';
        $description = 'Staff '.$staff_id.' has paid bill number : '.$_GET['bill'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        $update_query = "UPDATE bills_table SET bill_status = 1 WHERE bill_id = '".$_GET['bill']."'";
        $connect->query($update_query);
        //updating student_total_bill
        $bill = $connect->query("SELECT * FROM bills_table WHERE bill_id = '".$_GET['bill']."'")->fetch(PDO::FETCH_ASSOC);
        $amount = $bill['bill_amount'];
        $building = $bill['bill_building'];
        $room = $bill['bill_room'];
        $update_query = "UPDATE students_accounts SET student_total_bill = student_total_bill - $amount WHERE student_building = '$building' AND student_room = '$room' ";
        $connect->query($update_query);
        header('location:bills.php?tab=bills');
    }
}

//fetching staff-data
$query = "SELECT * FROM staff_accounts WHERE staff_id = '$staff_id'";
$statement = $connect->query($query);
$staff_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching bill-data
if(isset($_GET['building']) && isset($_GET['room']))
{
    $query = "SELECT * FROM bills_table WHERE bill_status = 0 AND bill_building = '".$_GET['building']."' AND bill_room = '".$_GET['room']."'";
}
else
{
    $query = "SELECT * FROM bills_table  WHERE bill_status = 0";
}
$bill_data = $connect->query($query);
include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Bills</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                BILLS
            </h2>
            <span style="border-left: 0.2em solid grey; padding-left: 2em; height: 4em; width: 1em;"></span>
            <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($staff_data['staff_picture']);?>" alt="manager_icon" style=" object-fit: cover; width: 4em; height:4rem;border-radius: 50px;">
            <div class="flex_column">
                <h2><?php echo $staff_data['staff_name'] ?></h2>
                <span><?php echo $staff_data['staff_id'] ?></span>
            </div>
        </div>
        <?php
            //start of error messages
                if($error_message != '')
                {
            ?>
                    <div id="message" class="flex_column error message">
                        <a class="error x_er" href="javascript:closemessage();">&#10006;</a>
                        <ul>
                            <?php echo $error_message?>
                        </ul>
                    </div>
            <?php
                }
            //end of error messages
                else if($success_message != '')
                {
            ?>
                    <div id="message" class="flex_column success message">
                        <a class="success x_suc" href="javascript:closemessage();">&#10006;</a>
                        <ul>
                            <?php echo $success_message?>
                        </ul>
                    </div>
            <?php
                }
        ?>
        <div class="flex_row card" style="margin-top: 3em; justify-content:flex-start; width: 80%"> 
            <div class="content" style="border-left: 0px; width: 100%; padding-left: 0em;">
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Bill ID</th>
                                <th>Building Number</th>
                                <th>Room Number</th>
                                <th>Fee</th>
                                <th>Issued On</th>
                                <th>Bill Type</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                                if($bill_data->rowCount()>0)
                                {
                                    $i=0;
                                    foreach($bill_data->fetchAll() as $bill)
                                    {
                                        echo '
                                        <tr>
                                            <td><a href="#">'.$bill['bill_id'].'</a></td>
                                            <td>'.$bill['bill_building'].'</td>
                                            <td>'.$bill['bill_room'].'</td>
                                            <td>'.$bill['bill_amount'].'</td>
                                            <td>'.$bill['created_on'].'</td>
                                            <td>'.$bill['bill_type'].'</td>
                                            <td>
                                            <div id="wrapper'.$i.'" class="operations_wrapper">
                                                <button id="'.$i.'" class="options">&centerdot; &centerdot; &centerdot;</button>
                                                <div id="dropdown'.$i.'" class="dropdown_content flex_column">
                                                    <a href="javascript: alertingMessage(\'Are you sure you want to pay bill number: '.$bill['bill_id'].'?\', \'bills.php?operation=pay&bill='.$bill['bill_id'].'&issuedate='.$bill['created_on'].'&type='.$bill['bill_type'].'\')">
                                                            Pay Bill
                                                    </a>
                                                </div>
                                            </div>
                                            </td>
                                        </tr>
                                        ';
                                        $i++;
                                    }
                                }
                ?>
                            </tbody>
                        </table>
            </div>
        </div>
    </section>
</body>
</html>