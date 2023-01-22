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
//fetch student_data
$query = "SELECT * FROM students_accounts WHERE student_id = '$student_id'";
$statement = $connect->query($query);
$student_data = $statement->fetch(PDO::FETCH_ASSOC);
//submitting complaints
if(isset($_POST['submit_complaint']))
{
    $form_data = array(
        ':request_id' => uniqid(),
        ':request_type' => 'Complaint'
    );
    if(!empty($_POST['message']))
    {
        $form_data[':message'] = $_POST['message'];
    }
    else
    {
        $error_message .= '<li>Message field must be filled.</li>';
    }
    if($error_message == '')
    {
        //insert request
        
        $insert_query = "INSERT INTO requests_table (request_id, student_id, request_type, created_on, message) VALUES(:request_id, '$student_id', :request_type, CURRENT_DATE, :message)";
        $connect->prepare($insert_query)->execute($form_data);
        $record_id = uniqid();
        $record_type = 'student';
        $description = 'Student '.$student_id.' has submitted complaint : '.$form_data[':request_id'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        $success_message .= '<li>Complaint submitted successfully.</li>';
    }
}
//submitting transfers
if(isset($_POST['submit_transfer']))
{
    $form_data = array(
        ':request_id' => uniqid(),
        ':request_type' => 'Transfer'
    );
    if(empty($_POST['building_number']))
    {
        $error_message .= '<li>Building number must be selected.</li>';
    }
    else
    {
        $form_data[':building_number'] = $_POST['building_number'];
    }
    if(empty($_POST['room_number']))
    {
        $error_message .= '<li>Room number must be selected.</li>';
    }
    else
    {
        $form_data[':room_number'] = $_POST['room_number'];
    }
    if(!empty($_POST['message']))
    {
        $form_data[':message'] = $_POST['message'];
    }
    else
    {
        $error_message .= '<li>Message field must be filled.</li>';
    }
    if($error_message == '')
    {
        //insert request
        $insert_query = "INSERT INTO requests_table VALUES(:request_id, '$student_id', :request_type, CURRENT_DATE, :message, :building_number, :room_number)";
        $connect->prepare($insert_query)->execute($form_data);
        $record_id = uniqid();
        $record_type = 'student';
        $description = 'Student '.$student_id.' has submitted transfer request : '.$form_data[':request_id'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        $success_message .= '<li>Transfer request submitted successfully.</li>';
    }
}
//deleting requests
if(isset($_GET['operation']) && $_GET['operation']=='delete')
{
    if(isset($_GET['request']))
    {
        $delete_query = "DELETE FROM requests_table WHERE request_id = '".$_GET['request']."'";
        $connect->query($delete_query);
        $record_id = uniqid();
        $record_type = 'student';
        $description = 'Student '.$student_id.' has deleted request : '.$_GET['request'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        header('location:requests.php?tab=requests&sidetab=requests&content=requests');
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
    <title>DMS . Requests</title>
</head>

<body class="responsive_body">
    <section class="flex_column">
            <div class="flex_row card classic" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
                <h2 style="padding-right: 2em;">
                    MY REQUESTS
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
            <div class="flex_row card holder" style="margin-top: 3em; justify-content:flex-start; width: 80%">
                <div class="flex_column modern" style="row-gap: 1em; margin-top: 3em; max-width: 800px; margin-bottom: 1em;">
                    <h1 style="font-size:1.5em;">
                        MY REQUESTS
                    </h1>
                    <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($student_data['student_picture']);?>" alt="manager_icon" style="border: 3px solid white; object-fit: cover; width: 8em; height:8em;border-radius: 300px;">
                    <div class="flex_column">
                        <h2><?php echo $student_data['student_name'] ?></h2>
                        <span><?php echo $student_data['student_id'] ?></span>
                    </div>
                </div>
                <div class="flex_column side_menu">
                    <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='complaints') echo 'class="side_tab_active"'; } ?> href="requests.php?tab=requests&sidetab=complaints&content=complaints">Submit Complaint</a>
                    <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='transfers') echo 'class="side_tab_active"'; } ?> href="requests.php?tab=requests&sidetab=transfers&content=transfers">Apply for Transfer</a>
                    <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='requests') echo 'class="side_tab_active"'; } ?> href="requests.php?tab=requests&sidetab=requests&content=requests">My Requests</a>
                </div>
                <div class="content" style="padding-bottom: 0em;">
                    <?php
                if(isset($_GET['content']))
                {
                    if($_GET['content']=='complaints')
                    {
                    ?>
                    <form method="POST" spellcheck="false" autocomplete="off" enctype="multipart/form-data">
                        <div class="flex_column" style="row-gap: 1em;">
                            <label style="margin-right: auto;" for="message">Specify your complaint :</label>
                            <textarea spellcheck="false" rows="5" type="text" id="message" name="message" maxlength="1000"></textarea>
                            <div id="counter" style="margin-left:auto; font-weight: 100; font-size: .8em; margin-bottom: 3em;">
                                <span id="current">0</span>
                                <span id="current">/1000</span>
                            </div>
                            <input class="butt" type="submit" value="Submit" name="submit_complaint">
                            <span style="font-weight: 100; font-size: .8em; margin-bottom: 3em;">The request will be treated as soon as possible and an email reply will be sent to you.</span>
                        </div>
                    </form>
                    <?php
                    }else if($_GET['content']=='transfers')
                    {
                    ?>
                    <form method="POST" style="display: flex; flex-direction:column; row-gap: 2em;" spellcheck="false" autocomplete="off"  enctype="multipart/form-data">
                        <div class="flex_column" style="row-gap: 1em;">
                            <span style="font-weight: 100; font-size: .8em; margin-bottom: 3em;">Chose the desired room to transfer to.</span>
                            <label style="margin-right: auto;" for="building_number">Building Number :</label>
                            <select onchange="window.location.href='requests.php?tab=requests&sidetab=transfers&content=transfers&building='+document.getElementById('building_number').value"  name="building_number" id="building_number" style="margin-right: auto;">
                                    <option disabled <?php if(!isset($_GET['building'])) echo 'selected';?> value="">Select a Building</option>
                                    <?php
                                    $building_numbers_query = "SELECT building_number FROM buildings_table WHERE building_status = 'Open' AND building_empty_rooms > 0";
                                    $building_numbers = $connect->query($building_numbers_query);
                                    if($building_numbers->rowCount()>0)
                                    {
                                        foreach($building_numbers as $building_number)
                                        {
                                    echo '<option '.( (isset($_GET['building']) && $_GET['building']==$building_number['building_number']) ? 'selected' : '') .' value="'.$building_number['building_number'].'">'.$building_number['building_number'].'</option>';
                                        }
                                    }
                                    ?>
                            </select>
                        </div>
                        <div class="flex_column" style="row-gap: 1em;">
                            <label <?php if(!isset($_GET['building'])) echo 'hidden'; ?> for="room_number" style="margin-right: auto;">Room Number : </label>
                            <select <?php if(!isset($_GET['building'])) echo 'hidden'; ?>  name="room_number" id="room_number">
                                <option disabled selected value="">Select a Room</option>
                                <?php
                                if(isset($_GET['building']))
                                {
                                    $room_numbers_query = "SELECT room_number FROM rooms_table WHERE building_number = '".$_GET['building']."' AND room_status = 'Open' AND living_status = 'Unoccupied' ORDER BY room_number +0";
                                    $room_numbers = $connect->query($room_numbers_query);
                                    if($room_numbers->rowCount()>0)
                                    {
                                        foreach($room_numbers as $room_number)
                                        {
                                    echo '<option value="'.$room_number['room_number'].'">'.$room_number['room_number'].'</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="flex_column" style="row-gap: 1em;">
                            <label style="margin-right: auto;" for="message">Specify your transfer request :</label>
                            <textarea spellcheck="false" rows="5" type="text" id="message" name="message" maxlength="1000"></textarea>
                            <div id="counter" style="margin-left:auto; font-weight: 100; font-size: .8em; margin-bottom: 3em;">
                                <span id="current">0</span>
                                <span id="current">/1000</span>
                            </div>
                            <input class="butt" type="submit" value="Submit" name="submit_transfer">
                            <span style="font-weight: 100; font-size: .8em; margin-bottom: 3em;">The request will be treated as soon as possible and an email reply will be sent to you.</span>
                        </div>
                    </form>
                    <?php
                    }elseif($_GET['content'] == 'requests')
                    {
                        //fetching requests
                        $query = "SELECT * FROM requests_table WHERE student_id = '$student_id'";
                        $requests_data = $connect->query($query);
                    ?>
                    <table id="datatable" class="table" style="width: 100%;">
                        <thead>
                            <th>Request ID</th>
                            <th>Type</th>
                            <th>Submitted On</th>
                            <th>Operation</th>
                        </thead>
                        <tbody>
                        <?php
                            if($requests_data->rowCount()>0)
                            {
                                foreach($requests_data->fetchAll() as $request)
                                {
                                    echo '
                                    <tr>
                                        <td>'.$request['request_id'].'</td>
                                        <td>'.$request['request_type'].'</td>
                                        <td>'.$request['created_on'].'</td>
                                        <td>
                                            <button class="butt cancel" onclick="alertingMessage(\'Are you sure you want to cancel request: '.$request['request_id'].'?\', \'requests.php?operation=delete&request='.$request['request_id'].'\')">
                                                Cancel
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
                }
                    ?>
                </div>
            </div>
    </section> 
</body>
</html>