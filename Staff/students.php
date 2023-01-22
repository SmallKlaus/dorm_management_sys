<?php

use PhpOffice\PhpSpreadsheet\Calculation\LookupRef\Filter;

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!isstafflogged())
{
    header('location:../index.php');
}

$staff_id = $_SESSION['staff_id'];

$error_message = '';
$success_message = '';
//inserting students
if(isset($_POST['add_student']))
{
    $student_id = hexdec(uniqid());
    $form_data = array(
        ':student_id' => $student_id,
        ':student_username' => $student_id,
        ':student_building' => '',
        ':student_room' =>  '',
        ':student_total_bill' => 0.00
    );
    if(!empty($_POST['student_name']))
    {
        $form_data[':student_name'] = $_POST['student_name'];
    }
    else
    {
        $error_message .= '<li>Name field must be filled.</li>';
    }
    if(!empty($_POST['student_email']))
    {
        if(filter_var($_POST['student_email'], FILTER_VALIDATE_EMAIL))
        {
            $form_data[':student_email'] = $_POST['student_email'];
        }
        else
        {
            $error_message .= '<li>Invalid Email.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Email field must be filled.</li>';
    }
    if(!empty($_POST['student_password']))
    {
        if(validatepassword($_POST['student_password']))
        {
            $form_data[':student_password'] = $_POST['student_password'];
        }
        else
        {
            $error_message .= '<li>Password must match requirements.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Password field must be filled.</li>';
    }
    if(!empty($_POST['student_balance']))
    {
        $form_data[':student_balance'] = $_POST['student_balance'];
    }
    else
    {
        $error_message .= '<li>Balance field must be filled.</li>';
    }
    if(isset($_FILES['student_picture']))
    {
        if(!empty($_FILES['student_picture']['tmp_name']))
        {     
           $form_data[':student_picture'] = file_get_contents($_FILES['student_picture']['tmp_name']);
        }
        else
        {
            $error_message .= '<li>Student picture is required.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Student picture is required.</li>';
    }
    if($error_message == '')
    {
        $insert_query='INSERT INTO students_accounts 
                        VALUES(:student_id, :student_name, :student_username, :student_email, :student_password, :student_building, :student_room, :student_total_bill, CURRENT_DATE(), :student_picture, :student_balance)';
        $statement=$connect->prepare($insert_query);
        $statement->execute($form_data);
        $save_path = "../res/images/general_faces/";
        $extension = strtolower(pathinfo($_FILES["student_picture"]['name'],PATHINFO_EXTENSION));
        $file_name = $form_data[':student_id'].'.'.$extension;
        move_uploaded_file($_FILES['student_picture']['tmp_name'], $save_path.$file_name);
        $record_id = uniqid();
        $record_type = 'staff';
        $description = 'Staff '.$staff_id.' has registered a new student account under ID : '.$form_data[':student_id'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        $success_message = '<li>Student account added successfully</li>';
    }
}
//editing students
if(isset($_POST['edit_student']))
{
    //fetching pre-change student information
    $query = "SELECT * FROM students_accounts WHERE student_id = '".$_POST['student_id']."'";
    $curr_student = $connect->query($query)->fetch(PDO::FETCH_ASSOC);
    $form_data = array(
        ':student_id' => $curr_student['student_id'],
        ':student_username' => $curr_student['student_username'],
        ':student_password' => $curr_student['student_password'],
        ':student_name' => $curr_student['student_name'],
        ':student_email' => $curr_student['student_email'],
        ':student_balance' => $curr_student['student_balance'],
        ':student_picture' => $curr_student['student_picture']
    );

    //validating form_data
    if(!empty($_POST['student_username']))
    {
        $exists_query = "SELECT * FROM students_accounts WHERE student_id != '".$curr_student['student_id']."' AND student_username = '".$_POST['student_username']."'";
        $statement = $connect->query($exists_query);
        if($statement->rowCount()>0)
        {
            $error_message .= '<li>Username already registred.</li>';
        }
        else
        {
            $form_data[':student_username'] = $_POST['student_username'];
        }
    }
    else
    {
        $error_message .= '<li>Username field is required.</li>';
    }
    if(!empty($_POST['student_name']))
    {
        $form_data[':student_name'] = $_POST['student_name'];
    }
    else
    {
        $error_message .= '<li>Studnet name field is required.</li>';
    }
    if(!empty($_POST['student_password']))
    {
        if(validatepassword($_POST['student_password']))
        {
            $form_data[':student_password'] = $_POST['student_password'];
        }
        else
        {
            $error_message .= '<li>Password must match requirements.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Student password field is required.</li>';
    }
    if(!empty($_POST['student_email']))
    {
        if(filter_var($_POST['student_email'], FILTER_VALIDATE_EMAIL))
        {
            $form_data[':student_email'] = $_POST['student_email'];
        }
        else
        {
            $error_message .= '<li>Email address must be valid.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Email field is required.</li>';
    }
    if(!empty($_POST['student_balance']))
    {
        $form_data[':student_balance'] = $_POST['student_balance'];
    }
    else
    {
        $error_message .= '<li>Balance field is required.</li>';
    }
    if(isset($_FILES['student_picture']))
    {
        if(!empty($_FILES['student_picture']['tmp_name']))
        {     
           $form_data[':student_picture'] = file_get_contents($_FILES['student_picture']['tmp_name']);
        }
    }

    if($error_message == '')
    {
        $update_query='UPDATE students_accounts 
                       SET student_username = :student_username,
                            student_name = :student_name,
                            student_email = :student_email,
                            student_password = :student_password,
                            student_balance = :student_balance,
                            student_picture = :student_picture
                        WHERE student_id = :student_id';
        $statement=$connect->prepare($update_query);
        $statement->execute($form_data);
        if(isset($_FILES['student_picture']))
        {
            if(!empty($_FILES['student_picture']['tmp_name']))
            {   
                //delete previous image
                $save_path = "../res/images/general_faces/";
                $file_name = $form_data[':student_id'];
                if(file_exists($save_path.$file_name.'.jpg'))
                {
                    unlink($save_path.$file_name.'.jpg');
                }
                if(file_exists($save_path.$file_name.'.png'))
                {
                    unlink($save_path.$file_name.'.png');
                }
                if(file_exists($save_path.$file_name.'.jpeg'))
                {
                    unlink($save_path.$file_name.'.jpeg');
                }
                //insert new image
                $save_path = "../res/images/general_faces/";
                $extension = strtolower(pathinfo($_FILES['student_picture']['name'], PATHINFO_EXTENSION));
                $file_name = $form_data[':student_id'].'.'.$extension;
                move_uploaded_file($_FILES['student_picture']['tmp_name'],$save_path.$file_name);
            }
        }
        $success_message = '<li>Student information edited successfully.</li>';
        $record_id = uniqid();
        $record_type = 'staff';
        $description = 'Staff '.$staff_id.' has made modifications to student account : '.$form_data[':student_id'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
    }
}
//deleting students
if(isset($_GET['operation']) && $_GET['operation']=='delete')
{
    if(isset($_GET['student']) && !empty($_GET['student']))
    {
        $delete_query = "DELETE FROM students_accounts WHERE student_id = '".$_GET['student']."'";
        $connect->query($delete_query);
        $save_path = "../res/images/general_faces/";
        $file_name = $_GET['student'];
        if(file_exists($save_path.$file_name.'.jpg'))
        {
            unlink($save_path.$file_name.'.jpg');
        }
        if(file_exists($save_path.$file_name.'.png'))
        {
            unlink($save_path.$file_name.'.png');
        }
        if(file_exists($save_path.$file_name.'.jpeg'))
        {
            unlink($save_path.$file_name.'.jpeg');
        }
        $record_id = uniqid();
        $record_type = 'staff';
        $description = 'Staff '.$staff_id.' has deleted student account : '.$_GET['student'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        header('location:students.php?tab=students&content=students');
    }
}
//Assigning Students
if(isset($_POST['assign_student']))
{
    $assigned_query = "SELECT * FROM students_accounts WHERE student_id = '".$_POST['student_id']."'";
    $student = $connect->query($assigned_query)->fetch(PDO::FETCH_ASSOC);
    if($student['student_building'] != '' && $student['student_room'] != '')
    {
        $error_message .= '<li>Student already assigned to a room.</li>';
    }
    $form_data = array(
        ':student_id' => $_POST['student_id']
    );
    if(empty($_POST['student_building']))
    {
        $error_message .= '<li>Building must be selected.</li>';
    }
    else
    {
        $form_data[':student_building'] = $_POST['student_building'];
    }
    if(empty($_POST['student_room']))
    {
        $error_message .= '<li>Room must be selected.</li>';
    }
    else
    {
        $form_data[':student_room'] = $_POST['student_room'];
    }
    if($error_message == '')
    {
        $update_query = "UPDATE students_accounts 
                            SET student_room = :student_room,
                                student_building = :student_building
                            WHERE  student_id = :student_id";
        $connect->prepare($update_query)->execute($form_data);
        $update_query = "UPDATE rooms_table
                            SET living_status = 'Occupied'
                            WHERE building_number = '".$form_data[':student_building']."' AND room_number = '".$form_data[':student_room']."'"; 
        $connect->query($update_query);
        $update_query = "UPDATE buildings_table
                            SET building_empty_rooms = building_empty_rooms - 1
                            WHERE building_number = '".$form_data[':student_building']."'"; 
        $connect->query($update_query);
        $record_id = uniqid();
        $record_type = 'staff';
        $description = 'Staff '.$staff_id.' has assigned student account : '.$form_data[':student_id'].' To room : B'.$form_data[':student_building'].' R'.$form_data[':student_room'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        header('location:students.php?tab=students&content=students');
    }
}
//Unassigning student
if(isset($_GET['operation']) && $_GET['operation']=='unassign')
{
    //check bills and assignment then assign
    $assigned_query = "SELECT * FROM students_accounts WHERE student_id = '".$_GET['student']."'";
    $student = $connect->query($assigned_query)->fetch(PDO::FETCH_ASSOC);
    if($student['student_building'] != '' && $student['student_room'] != '')
    {
        if($student['student_total_bill'] == 0)
        {
            $update_query = "UPDATE rooms_table SET living_status = 'Unoccupied' WHERE building_number = '".$student['student_building']."' AND room_number = '".$student['student_room']."'";
            $connect->query($update_query);
            $update_query = "UPDATE buildings_table
                            SET building_empty_rooms = building_empty_rooms + 1
                            WHERE building_number = '".$form_data[':student_building']."'"; 
        $connect->query($update_query);
            $update_query = "UPDATE students_accounts SET student_building = '', student_room = '' WHERE student_id = '".$student['student_id']."'";
            $connect->query($update_query);
            $record_id = uniqid();
            $record_type = 'staff';
            $description = 'Staff '.$staff_id.' has unassigned student account : '.$_GET['student'].' On: '.getdateandtime();
            $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
            header('location:students.php?tab=students&content=students');
        }
        else
        {
            $error_message .= '<li>Student needs to pay bills before being unassigned.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Student isn\'t assigned to a room yet.</li>';
    }
}

//fetching staff-data
$query = "SELECT * FROM staff_accounts WHERE staff_id = '$staff_id'";
$statement = $connect->query($query);
$staff_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching student data
$query = "SELECT * FROM students_accounts";
$student_data = $connect->query($query);
include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Students</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                STUDENTS
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
        <?php
            if(isset($_GET['content']))
            {
                if($_GET['content']=='students')
                {
                ?>
                        <button onclick="window.location='students.php?tab=students&content=add_student'" class="butt" style="position:absolute; top:2em; right:2em; font-size: .9rem;">Add Student</button>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Username</th>
                                <th>Student Room</th>
                                <th>Student Bills</th>
                                <th>Email</th>
                                <th>Balance</th>
                                <th>Added On</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                                if($student_data->rowCount()>0)
                                {
                                    $i=0;
                                    foreach($student_data->fetchAll() as $student)
                                    {
                                        echo '
                                        <tr>
                                            <td><a href="#"><b>'.$student['student_id'].'</b></a></td>
                                            <td>'.$student['student_name'].'</td>
                                            <td>'.$student['student_username'].'</td>
                                            <td>B'.$student['student_building'].' R'.$student['student_room'].'</td>
                                            <td><a href="bills.php?tab=bills&building='.$student['student_building'].'&room='.$student['student_room'].'">'.$student['student_total_bill'].'</a></td>
                                            <td><a href="mailto:'.$student['student_email'].'">'.$student['student_email'].'</a></td>
                                            <td>'.$student['student_balance'].'</td>
                                            <td>'.$student['created_on'].'</td>
                                            <td>
                                            <div id="wrapper'.$i.'" class="operations_wrapper">
                                                <button id="'.$i.'" class="options">&centerdot; &centerdot; &centerdot;</button>
                                                <div id="dropdown'.$i.'" class="dropdown_content flex_column">
                                                    <a href="students.php?content=assign&tab=students&student='.$student['student_id'].'">Assign</a>
                                                    <a href="javascript: alertingMessage(\'Are you sure you want to unassign student : '.$student['student_id'].'?\' ,\'students.php?operation=unassign&tab=students&student='.$student['student_id'].'&content=students\')">
                                                            Unassign
                                                    </a>
                                                    <a href="students.php?content=edit_student&tab=students&student='.$student['student_id'].'">Edit Student</a>
                                                    <a href="javascript: alertingMessage(\'Are you sure you want to delete student : '.$student['student_id'].'?\' ,\'students.php?operation=delete&student='.$student['student_id'].'\')">
                                                            Delete Student
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
                <?php
                }elseif($_GET['content']=='add_student')
                {
                ?>
                    <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                            <img src="../res/icons/add.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                            <h2>New Student Account</h2>
                        </div>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_name" style="margin-right: auto;">Full Name : </label>
                                <input type="text" name="student_name" id="student_name" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_email" style="margin-right: auto;">E-mail : </label>
                                <input type="text" name="student_email" id="student_email" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_password" style="margin-right: auto;">Password : </label>
                                <input type="password" name="student_password" id="student_password" style="margin-right: auto;">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">(at least 8 characters, one uppercase, one lowercase, one digit and one special character)</span>
                                <input type="checkbox" onclick="showPwd('student_password')" style="margin-left: auto;"> 
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_balance" style="margin-right: auto;">Balance : </label>
                                <input type="number" step="0.01" name="student_balance" id="student_balance" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="image" style="margin-right: auto;">Student Picture :</label>
                                <label for="image" class="butt" style="margin-right: auto;">
                                    Upload Image
                                    <input type="file" name="student_picture" id="image" accept=".png,.jpeg,.jpg" style="margin-right: auto;" />
                                </label>
                                <img id="preview" src="#" alt="Student Icon" style="object-fit: cover; width: 6em; height:6em;border-radius: 20px;">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">Image must be .png or .jpeg file</span>
                            </div>
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="add_student" value="Add">
                                <a class="butt cancel" href='students.php?tab=students&content=students'">Cancel</a>
                            </div>
                        </form>
                <?php
                }elseif($_GET['content']=='edit_student')
                {
                        //fetching student to be edited
                        if(isset($_GET['student']) && !empty($_GET['student']))
                        {
                            $query = "SELECT * FROM students_accounts WHERE student_id = '".$_GET['student']."'";
                            $student = $connect->query($query)->fetch(PDO::FETCH_ASSOC);
                        
                ?>
                        <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                            <img src="../res/icons/edit.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                            <h2>Edit Student</h2>
                        </div>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_username" style="margin-right: auto;">Username : </label>
                                <input type="text" name="student_username" id="student_username" style="margin-right: auto;" value="<?php echo $student['student_username'];?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_name" style="margin-right: auto;">Full Name : </label>
                                <input type="text" name="student_name" id="student_name" style="margin-right: auto;" value="<?php echo $student['student_name'];?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_balance" style="margin-right: auto;">Balance : </label>
                                <input type="number" step="0.01" name="student_balance" id="student_balance" style="margin-right: auto;" value="<?php echo $student['student_balance'];?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_email" style="margin-right: auto;">E-mail Address : </label>
                                <input type="text" name="student_email" id="student_email" style="margin-right: auto;" value="<?php echo $student['student_email'];?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="student_password" style="margin-right: auto;">Password : </label>
                                <input type="password" name="student_password" id="student_password" style="margin-right: auto;" value="<?php echo $student['student_password'];?>">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">(at least 8 characters, one uppercase, one lowercase, one digit and one special character)</span>
                                <input type="checkbox" onclick="showPwd('student_password')" style="margin-left: auto;"> 
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="image" style="margin-right: auto;">Student Picture :</label>
                                <label for="image" class="butt" style="margin-right: auto;">
                                    Upload Image
                                    <input type="file" name="student_picture" id="image" accept=".png,.jpeg,.jpg" style="margin-right: auto;" />
                                </label>
                                <img id="preview" src="#" alt="Student Icon" style="object-fit: cover; width: 6em; height:6em;border-radius: 20px;">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">Image must be .png or .jpeg file</span>
                            </div>
                            <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="edit_student" value="Edit">
                                <a class="butt cancel" href='students.php?tab=students&content=students'>Cancel</a>
                            </div>
                        </form>
                <?php
                        }
                }elseif($_GET['content']=='assign')
                {
                    //fetching student to be edited
                    if(isset($_GET['student']) && !empty($_GET['student']))
                    {
                        $query = "SELECT * FROM students_accounts WHERE student_id = '".$_GET['student']."'";
                        $student = $connect->query($query)->fetch(PDO::FETCH_ASSOC);
            ?>
                    <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                        <img src="../res/icons/edit.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                        <h2>Assign Room to Student </h2>
                    </div>
                    <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                        <div class="flex_column" style="width: 80%; row-gap: .5em;">
                            <label for="student_building" style="margin-right: auto;">Building Number: </label>
                            <select onchange="buildingAssigned('<?php echo $_GET['student'];?>')"  name="student_building" id="student_building" style="margin-right: auto;">
                                    <option disabled <?php if(!isset($_GET['building'])) echo 'selected';?> value="">Select a Building</option>
                                    <?php
                                    $building_numbers_query = "SELECT building_number FROM buildings_table WHERE building_status = 'Open'";
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
                        <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label <?php if(!isset($_GET['building'])) echo 'hidden'; ?> for="student_room" style="margin-right: auto;">Room Number : </label>
                                <select <?php if(!isset($_GET['building'])) echo 'hidden'; ?>  name="student_room" id="student_room">
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
                        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                        <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                            <input class="butt" type="submit" name="assign_student" value="Assign">
                            <a class="butt cancel" href='students.php?tab=students&content=students'>Cancel</a>
                        </div>
                    </form>
            <?php
                    }
                }
            }
        ?>
            </div>
        </div>
    </section>
    <script>
        let image = document.getElementById('image')
        let preview= document.getElementById('preview')
        image.addEventListener("change", (e)=>{
            let logo = e.target.files[0]
            let fileReader = new FileReader()
            fileReader.readAsDataURL(logo)
            fileReader.onload = function()
            {
                preview.setAttribute('src', fileReader.result)
                preview.style.visibility = 'visible'
            }
        })  
    </script>
</body>
</html>