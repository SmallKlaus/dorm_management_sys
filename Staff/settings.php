<?php

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!isstafflogged())
{
    header('location:../index.php');
}

$staff_id = $_SESSION['staff_id'];
//pre-change staff-data
$query = "SELECT * FROM staff_accounts WHERE staff_id = '$staff_id'";
$statement = $connect->query($query);
$staff_data = $statement->fetch(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';
//staff account form validation
if(isset($_POST['account_apply']))
{
    $form_data = array(
        ':staff_username'    =>  $staff_data['staff_username'],
        ':staff_password' => $staff_data['staff_password'],
        ':phone_number'    =>  $staff_data['phone_number']
    );
    if(!empty($_POST['staff_username']))
    {
        $query = "SELECT * FROM staff_accounts WHERE staff_id != '$staff_id' AND staff_username = '".$_POST['staff_username']."'";
        if($connect->query($query)->rowCount()>0)
        {
            $error_message .= 'Username unavailable';
        }
        else
        {
            $form_data[':staff_username'] = $_POST['staff_username'];
        }
    }
    else
    {
        $error_message .= '<li>Username field must be filled.</li>';
    }
    if(!empty('phone_number'))
    {
        if(!validatephonenumber($_POST['phone_number']))
        {
            $error_message.='<li>Phone number must be valid.</li>';
        }
        else
        {
            $form_data[':phone_number'] = $_POST['phone_number'];
        }    
    }
    else
    {
        $error_message .= '<li>Phone number field must be filled.</li>';
    }
    if(!empty($_POST['staff_password']) && !empty($_POST['confirm_password']))
    {
        if(!validatepassword($_POST['staff_password']))
        {
            $error_message .= '<li>Password must match requirements.</li>';
        }
        else
        {
            if($_POST['confirm_password'] === $_POST['staff_password'])
            {
                $form_data[':staff_password'] = $_POST['staff_password'];
            }
            else
            {
                $error_message .= '<li>Passwords must match.</li>';
            }
        }
    }
    if($error_message == '')
    {
        $update_query ="UPDATE `staff_accounts`
                        SET staff_username = :staff_username,
                        staff_password = :staff_password,
                        phone_number = :phone_number
                        WHERE staff_id = '$staff_id'";
        $statement = $connect->prepare($update_query);
        $statement->execute($form_data);
        $record_id = uniqid();
        $record_type = 'staff';
        $description = "Staff ".$staff_id." has made modifications to his account on ".getdateandtime().".";
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        $success_message = '<li>Changes applied successfully</li>';
    }
}

//post-change staff-data
$query = "SELECT * FROM staff_accounts WHERE staff_id = '$staff_id'";
$statement = $connect->query($query);
$staff_data = $statement->fetch(PDO::FETCH_ASSOC);
include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Staff Settings</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                SETTINGS
            </h2>
            <span style="border-left: 0.2em solid grey; padding-left: 2em; height: 4em; width: 1em;"></span>
            <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($staff_data['staff_picture']);?>" alt="manager_icon" style=" object-fit: cover; width: 4rem; height:4rem;border-radius: 50px;">
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
        <div class="flex_row card holder" style="margin-top: 3em; justify-content:flex-start; width: 80%"> 
            <div class="flex_column side_menu">
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='account') echo 'class="side_tab_active"'; } ?> href="settings.php?tab=settings&sidetab=account&content=account">Account Settings</a>
                <a href="logout.php">Logout</a>
            </div>
            <div class="content">
                <?php if(isset($_GET['content']))
                { 
                    if($_GET['content']=='account')
                    {
                ?>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_username" style="margin-right: auto;">Staff Username :</label>
                                <input type="text" name="staff_username" id="staff_username" style="margin-right: auto;" value="<?php echo $staff_data['staff_username']; ?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="phone_number" style="margin-right: auto;">Staff Phone Number :</label>
                                <input type="text" name="phone_number" id="phone_number" style="margin-right: auto;" value="<?php echo $staff_data['phone_number']; ?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_password" style="margin-right: auto;">Staff Password :</label>
                                <input type="password" name="staff_password" id="staff_password" style="margin-right: auto;" value="<?php echo $staff_data['staff_password']; ?>">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">(at least 8 characters, one uppercase, one lowercase, one digit and one special character)</span>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="confirm_password" style="margin-right: auto;">Confirm Staff Password :</label>
                                <input type="password" name="confirm_password" id="confirm_password" style="margin-right: auto;" value="<?php echo $staff_data['staff_password']; ?>">
                            </div>
                            <input class="butt" type="submit" name="account_apply" value="Apply Changes">
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