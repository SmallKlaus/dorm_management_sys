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

//treating requests
if(isset($_POST['request_treated']))
{
    if($_POST['verification']=='yes')
    {
        //clearing request from database
        $request_id = $_POST['request_id'];                                                                                             
        $connect->query("DELETE FROM requests_table WHERE request_id = '$request_id'");
        $record_id = uniqid();
        $record_type = 'staff';
        $description = 'Staff '.$staff_id.' has declared request : '.$request_id.' treated On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        header('location:requests.php?tab=requests&sidetab=transfers&content=transfers');
    }
}
//switching status
if(isset($_GET['operation']) && $_GET['operation']=='status')
{
    $request_id = $_GET['request'];
    $connect->query("UPDATE requests_table SET `status` = 1 WHERE request_id = '$request_id'");
    $record_id = uniqid();
    $record_type = 'staff';
    $description = 'Staff '.$staff_id.' has started treating request : '.$request_id.' On: '.getdateandtime();
    $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
    header('location:requests.php?tab=requests&sidetab=treating&content=treating');
}

//fetching staff data
$query = "SELECT * FROM staff_accounts WHERE staff_id = '$staff_id'";
$statement = $connect->query($query);
$staff_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching transfer requests data
$query = "SELECT * FROM requests_table WHERE request_type = 'Transfer' AND status=0";
$transfer_data = $connect->query($query);
//fetching complaint data
$query = "SELECT * FROM requests_table WHERE request_type = 'Complaint' AND status=0";
$complaint_data = $connect->query($query);
//fetching in-treatment data
$query = "SELECT * FROM requests_table WHERE status=1";
$treatment_data = $connect->query($query);

include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Requests</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                REQUESTS
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
        <div class="flex_row card holder" style="margin-top: 3em; justify-content:flex-start; width: 80%; max-width: 80%"> 
            <div class="flex_column side_menu">
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='transfers') echo 'class="side_tab_active"'; } ?> href="requests.php?tab=requests&sidetab=transfers&content=transfers">Transfer Requests</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='complaints') echo 'class="side_tab_active"'; } ?> href="requests.php?tab=requests&sidetab=complaints&content=complaints">Complaints</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='treating') echo 'class="side_tab_active"'; } ?> href="requests.php?tab=requests&sidetab=treating&content=treating">In-Treatment</a>
            </div>
            <div class="content">
                <?php if(isset($_GET['content']))
                { 
                    if($_GET['content']=='transfers')
                    {
                ?>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Request ID</th>
                                <th>Student ID</th>
                                <th>Submitted On</th>
                                <th>Building Number</th>
                                <th>Room Number</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                                if($transfer_data->rowCount()>0)
                                {
                                    foreach($transfer_data->fetchAll() as $transfer)
                                    {
                                        echo '
                                        <tr>
                                            <td><a href="#">'.$transfer['request_id'].'</a></td>
                                            <td>'.$transfer['student_id'].'</td>
                                            <td>'.$transfer['created_on'].'</td>
                                            <td>'.$transfer['building_number'].'</td>
                                            <td>'.$transfer['room_number'].'</td>
                                            <td>
                                                    <button class="butt" onclick="window.location.href=\'requests.php?tab=requests&content=expand&request='.$transfer['request_id'].'\'">
                                                            Expand
                                                    </button>
                                            </td>
                                        </tr>
                                        ';
                                    }
                                }
                ?>
                            </tbody>
                        </table>
                <?php
                    }
                    elseif($_GET['content']=='complaints')
                    {
                ?>
                        <table id="datatable" class="table" style="width: 100%;">
                        <thead>
                            <th>Request ID</th>
                            <th>Student ID</th>
                            <th>Submitted On</th>
                            <th>Operations</th>
                        </thead>
                        <tbody>
            <?php
                            if($complaint_data->rowCount()>0)
                            {
                                foreach($complaint_data->fetchAll() as $complaint)
                                {
                                    echo '
                                    <tr>
                                        <td><a href="#">'.$complaint['request_id'].'</a></td>
                                        <td>'.$complaint['student_id'].'</td>
                                        <td>'.$complaint['created_on'].'</td>
                                        <td>
                                                <button class="butt" onclick="window.location.href=\'requests.php?tab=requests&content=expand&request='.$complaint['request_id'].'\'">
                                                        Expand
                                                </button>
                                        </td>
                                    </tr>
                                    ';
                                }
                            }
            ?>
                        </tbody>
                    </table>
            <?php
                    }
                    elseif($_GET['content']=='treating')
                    {
                ?>
                        <table id="datatable" class="table" style="width: 100%;">
                        <thead>
                            <th>Request ID</th>
                            <th>Student ID</th>
                            <th>Submitted On</th>
                            <th>Operations</th>
                        </thead>
                        <tbody>
            <?php
                            if($treatment_data->rowCount()>0)
                            {
                                foreach($treatment_data->fetchAll() as $treating)
                                {
                                    echo '
                                    <tr>
                                        <td><a href="#">'.$treating['request_id'].'</a></td>
                                        <td>'.$treating['student_id'].'</td>
                                        <td>'.$treating['created_on'].'</td>
                                        <td>
                                                <button class="butt" onclick="window.location.href=\'requests.php?tab=requests&content=expand&request='.$treating['request_id'].'\'">
                                                        Expand
                                                </button>
                                        </td>
                                    </tr>
                                    ';
                                }
                            }
            ?>
                        </tbody>
                    </table>
            <?php
                    }
                    elseif($_GET['content']=='expand')
                    {
                        $request_id = $_GET['request'];
                        $request = $connect->query("SELECT * FROM requests_table WHERE request_id = '$request_id'")->fetch(PDO::FETCH_ASSOC);
                        $student_id = $request['student_id'];
                        $student = $connect->query("SELECT * FROM students_accounts WHERE student_id = '$student_id'")->fetch(PDO::FETCH_ASSOC);
                        if($request['status']==0)
                        {
            ?>
                        <a class="butt cancel" href=<?php echo 'requests.php?operation=status&request='.$request_id ?> style="position:absolute; top:2em; right:2em; font-size: .9rem;">Send To In-Treatment</a>
            <?php
                        }
            ?>
                        <h2>REQUEST ID: <a href="#"><?php echo $request_id ?></a></h2>
                        <form method="POST" class="flex_column" style="row-gap: 2em; margin: 3em 0 0 0;" enctype="multipart/form-data">
                            <div class="grid_container">
                                <div class="flex_row grid_ele">
                                    <label style="margin-right: auto;">Student ID: </label>
                                    <a ><?php echo $request['student_id'] ?></a>
                                </div>  
                                <div class="flex_row grid_ele">
                                    <label style="margin-right: auto;">Submitted On: </label>
                                    <a ><?php echo $request['created_on'] ?></a>
                                </div>  
                                <div class="flex_row grid_ele">
                                    <label style="margin-right: auto;">Student Bill: </label>
                                    <a ><?php echo $student['student_total_bill'] ?> $</a>
                                </div>  
                                <div class="flex_row grid_ele">
                                    <label style="margin-right: auto;">Current Room: </label>
                                    <a ><?php echo 'B'.$student['student_building'].' R'.$student['student_room'] ?></a>
                                </div>  
                                <div class="flex_row grid_ele">
                                    <label style="margin-right: auto;">Request Type: </label>
                                    <a ><?php echo $request['request_type'] ?></a>
                                </div>  
                                <?php
                        if($request['request_type'] == 'Transfer')
                        {
            ?>
                                <div class="flex_row grid_ele">
                                    <label style="margin-right: auto;">Desired Room: </label>
                                    <a ><?php echo 'B'.$request['building_number'].' R'.$request['room_number'] ?></a>
                                </div>  
            <?php
                        }
            ?>       
                            </div>
                            <div class="flex_column" style="margin-top: 2em; width: 90%; max-width: 1000px; row-gap: 1em;">
                                <label style="margin-right: auto;">Attachement: </label>
                                <span style="text-align: start;"><?php echo $request['message'] ?></span>
                            </div>
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly; margin-top: 3em;">
                                <a class="butt cancel" href="mailto:<?php echo $student['student_email']?>">Reply</a>
                                <input type="hidden" name="request_id" id="request_id" value="<?php echo $request['request_id']?>">
                                <input type="hidden" name="verification" id="verification" value="no">
                                <input class="butt" type="submit" name="request_treated" value="Done" onclick="alertingBoolMessage('Are you sure the request has been fully treated?')">
                                <a class="butt cancel" href='requests.php?tab=requests&content=transfers&sidetab=transfers'">Cancel</a>
                            </div>
                        </form>
            <?php
                    }
                }
                ?>
            </div>
        </div>
    </section>
</body>
</html>