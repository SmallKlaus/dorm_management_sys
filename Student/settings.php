<?php
include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!isstudentlogged())
{
    header('location:../index.php');
}

$student_id = $_SESSION['student_id'];

//pre-change student data
$query = "SELECT * FROM students_accounts WHERE student_id = '$student_id'";
$statement = $connect->query($query);
$student_data = $statement->fetch(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';
if(isset($_POST['account_apply']))
{
    $form_data = array();
    if(!empty($_POST['student_username']))
    {
        $exists= $connect->query("SELECT * FROM students_accounts WHERE student_username = '".$_POST['student_username']."' AND student_id != '$student_id'");
        if($exists->rowCount()>0)
        {
            $error_message .= '<li>Username already registered.</li>';
        }
        else
        {
            $form_data[':student_username'] = $_POST['student_username'];
        }
    }
    else
    {
        $error_message .= '<li>Username field must be filled.</li>';
    }
    if(!empty($_POST['student_email']))
    {
        if(filter_var($_POST['student_email'], FILTER_VALIDATE_EMAIL))
        {
            $exists= $connect->query("SELECT * FROM students_accounts WHERE student_email = '".$_POST['student_email']."' AND student_id != '$student_id'");
            if($exists->rowCount()>0)
            {
                $error_message .= '<li>Email address already registered.</li>';
            }
            else
            {
                $form_data[':student_email'] = $_POST['student_email'];
            }
        }
        else
        {
            $error_message .= '<li>Email address must be valid.</li>';
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
    
    if($error_message == '')
    {
        //update student info
        $update_query = "UPDATE students_accounts SET student_username = :student_username, student_email = :student_email, student_password = :student_password WHERE student_id = '$student_id'";
        $statement = $connect->prepare($update_query);
        $statement->execute($form_data);
        $record_id = uniqid();
        $record_type = 'student';
        $description = 'Student '.$student_id.' has made modification to his account On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
    }
}
//post-change student data
$query = "SELECT * FROM students_accounts WHERE student_id = '$student_id'";
$statement = $connect->query($query);
$student_data = $statement->fetch(PDO::FETCH_ASSOC);
include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Settings</title>
</head>
<body class="responsive_body">
<section class="flex_column">
        <div class="flex_row card classic" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                ACCOUNT
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
        <div class="flex_column card " style="margin-top: 3em; justify-content:flex-start; width: 80%; max-width: 1200px;">
            <div class="flex_column modern" style="row-gap: 1em; margin-top: 3em; max-width: 800px;">
                <h1 style="font-size:1.5em;">
                    ACCOUNT
                </h1>
                <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($student_data['student_picture']);?>" alt="manager_icon" style="border: 3px solid white; object-fit: cover; width: 8em; height:8em;border-radius: 300px;">
                <div class="flex_column">
                    <h2><?php echo $student_data['student_name'] ?></h2>
                    <span><?php echo $student_data['student_id'] ?></span>
                </div>
            </div> 
            <div class="content" style="border-left: 0px; width: 100%; padding-left: 0em; min-width: fit-content;">
                <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                    <div class="grid_container">
                        <div class="flex_row grid_ele">
                            <label style="margin-right: auto;">Full Name : </label>
                            <a ><?php echo $student_data['student_name'] ?></a>
                        </div>  
                        <div class="flex_row grid_ele">
                            <label style="margin-right: auto;">Joined On : </label>
                            <a ><?php echo $student_data['created_on'] ?></a>
                        </div>  
                        <div class="flex_row grid_ele">
                            <label style="margin-right: auto;">Building Number : </label>
                            <a ><?php echo $student_data['student_building'] ?></a>
                        </div>  
                        <div class="flex_row grid_ele">
                            <label style="margin-right: auto;">Room Number : </label>
                            <a ><?php echo $student_data['student_room'] ?></a>
                        </div>  
                        <div class="flex_row grid_ele">
                            <label style="margin-right: auto;">Current Total Bill : </label>
                            <a ><?php echo $student_data['student_total_bill'] ?> $</a>
                        </div>  
                        <div class="flex_row grid_ele">
                            <label style="margin-right: auto;">Balance : </label>
                            <a><?php echo $student_data['student_balance'] ?> $</a>
                        </div>  
                    </div>
                    <div class="flex_column inp">
                        <label for="student_username" style="margin-right: auto;">Username : </label>
                        <input type="text" name="student_username" id="student_username" value="<?php echo $student_data['student_username']?>">
                    </div>
                    <div class="flex_column inp">
                            <label for="student_email" style="margin-right: auto;">Email : </label>
                            <input type="text" name="student_email" id="student_email" value="<?php echo $student_data['student_email']?>">
                    </div>
                    <div class="flex_column inp">
                            <label for="student_password" style="margin-right: auto;">Password : </label>
                            <input type="password" name="student_password" id="student_password" style="margin-right: auto;" value="<?php echo $student_data['student_password']?>">
                            <span style="font-weight: 100; font-size: .6em; margin-left: auto">(at least 8 characters, one uppercase, one lowercase, one digit and one special character)</span>
                            <input type="checkbox" onclick="showPwd('student_password')" style="margin-left: auto;"> 
                    </div>  
                    <input class="butt" type="submit" name="account_apply" value="Apply Changes">
                </form>
            </div>
        </div>
</section>
</body>
</html>