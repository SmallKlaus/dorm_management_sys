<?php
include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!isstudentlogged())
{
    header('location:../index.php');
}

$student_id = $_SESSION['student_id'];

$success_message = '';
$error_message = '';

//fetching student data
$query = "SELECT * FROM students_accounts WHERE student_id = '$student_id'";
$statement = $connect->query($query);
$student_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching bill data
$query = "SELECT * FROM bills_table WHERE bill_building = '".$student_data['student_building']."' AND bill_room = '".$student_data['student_room']."' AND bill_status = 0";
$bill_data = $connect->query($query);
//paying bills
if(isset($_GET['operation']) && $_GET['operation']=='pay')
{
    if(isset($_GET['bill']))
    {
        $bill = $connect->query("SELECT * FROM bills_table WHERE bill_id = '".$_GET['bill']."'")->fetch(PDO::FETCH_ASSOC);
        if($bill['bill_amount']>$student_data['student_balance'])
        {
            $error_message .= '<li>Balance insufficient to pay selected bill.</li>';
        }
        else
        {
            //update bill status 
            $connect->query("UPDATE bills_table SET bill_status = 1 WHERE bill_id = '".$_GET['bill']."'");
            //update student balance and student total bill
            $connect->query("UPDATE students_accounts SET student_balance = student_balance - ".$bill['bill_amount'].", student_total_bill = student_total_bill - ".$bill['bill_amount']." WHERE student_id = '$student_id'");
            //inserting record 
            $record_id = uniqid();
            $record_type = 'bill';
            $description = 'Student '.$student_id.' has paid bill number : '.$bill['bill_id'].' On: '.getdateandtime();
            $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
            //reload page
            header('location:bills.php?tab=bills');
        }
    }
}
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
<body class="responsive_body">
<section class="flex_column">
        <div class="flex_row card classic" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                MY BILLS
            </h2>
            <span style="border-left: 0.2em solid grey; padding-left: 2em; height: 4em; width: 1em;"></span>
            <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($student_data['student_picture']);?>" alt="manager_icon" style=" object-fit: cover; width: 4rem; height:4rem;border-radius: 50px;">
            <div class="flex_column">
                <h2><?php echo $student_data['student_name'] ?></h2>
                <span><?php echo $student_data['student_id'] ?></span>
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
        <div class="flex_column card" style="margin-top: 3em; justify-content:flex-start; width: 80%; max-width: 1200px;">
            <div class="flex_column modern" style="row-gap: 1em; margin-top: 3em; max-width: 800px;">
                <h1 style="font-size:1.5em;">
                    MY BILLS
                </h1>
                <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($student_data['student_picture']);?>" alt="manager_icon" style="border: 3px solid white; object-fit: cover; width: 8em; height:8em;border-radius: 300px;">
                <div class="flex_column">
                    <h2><?php echo $student_data['student_name'] ?></h2>
                    <span><?php echo $student_data['student_id'] ?></span>
                </div>
            </div>
            <div class="content" style="border-left: 0px; width: 100%; padding-left: 0em; min-width: fit-content; text-align: center;">  
                <table id="datatable" class="table" style="width: 100%;">
                    <thead>
                        <th>Bill Type</th>
                        <th>Fee</th>
                        <th>Issued On</th>
                        <th>Operations</th>
                    </thead>
                    <tbody>
        <?php
                        if($bill_data->rowCount()>0)
                        {
                            foreach($bill_data->fetchAll() as $bill)
                            {
                                echo '
                                <tr>
                                    <td>'.$bill['bill_type'].'</td>
                                    <td>'.$bill['bill_amount'].'</td>
                                    <td>'.$bill['created_on'].'</td>
                                    <td>
                                        <button class="butt" onclick="alertingMessage(\'Are you sure you want to pay bill number: '.$bill['bill_id'].'?\', \'bills.php?operation=pay&bill='.$bill['bill_id'].'\')">
                                            Pay
                                        </button>
                                    </td>
                                </tr>
                                ';
                            }
                        }
        ?>
                    </tbody>
                </table>
            </div> 
            <span style="font-weight: 100; font-size: .8em; margin-bottom: 3em;">All bills must be paid within 30 days of their issue date. Any late payments will be punished accordingly.</span>
            <span style="margin-bottom: 1em;">BALANCE : <a><?php echo $student_data['student_balance']?> $</a> </span>
        </div>
</section>

</body>
</html>