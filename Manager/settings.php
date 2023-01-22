<?php

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!ismanagerlogged())
{
    header('location:../index.php');
}
$manager_id = $_SESSION['manager_id'];
//pre-change dorm-data
$query = "SELECT * FROM dorm_settings LIMIT 1";
$statement = $connect->query($query);
$dorm_data = $statement->fetch(PDO::FETCH_ASSOC);
//pre-change manager-data
$query = "SELECT * FROM manager_accounts WHERE manager_id = '$manager_id'";
$statement = $connect->query($query);
$manager_data = $statement->fetch(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';
//dorm form validation
if(isset($_POST['dorm_apply']))
{
    $form_data = array(
        ':dorm_name'    => $dorm_data['dorm_name'],
        ':dorm_address'    => $dorm_data['dorm_address'],
        ':dorm_email'    => $dorm_data['dorm_email'],
        ':dorm_contact_number'    => $dorm_data['dorm_contact_number'],
        ':dorm_logo'    => $dorm_data['dorm_logo'],
        ':dorm_logo_name'   => $dorm_data['dorm_logo_name'],
        ':open' =>  $_POST['open'],
        ':close'    =>  $_POST['close']
    );
    if(!empty($_POST['dorm_name']))
    {
        $form_data[':dorm_name'] = $_POST['dorm_name'];
    }
    if(!empty($_POST['dorm_address']))
    {
        $form_data[':dorm_address'] = $_POST['dorm_address'];
    }
    if(!empty($_POST['dorm_email']))
    {
        if(filter_var($_POST['dorm_email'], FILTER_VALIDATE_EMAIL))
        {
            $form_data[':dorm_email'] = $_POST['dorm_email'];
        }
        else
        {
            $error_message .= '<li>Invalid Email.</li>';
        }
    }
    if(!empty($_POST['dorm_contact_number']))
    {
        if(validatephonenumber($_POST['dorm_contact_number']))
        {
            $form_data[':dorm_contact_number'] = $_POST['dorm_contact_number'];
        }
        else
        {
            $error_message .= '<li>Contact number must be valid.</li>';
        }
    }
    if(isset($_FILES['dorm_logo']))
    {
        if(!empty($_FILES['dorm_logo']['tmp_name']))
        {     
           $form_data[':dorm_logo'] = file_get_contents($_FILES['dorm_logo']['tmp_name']);
           $form_data[':dorm_logo_name'] = $_FILES['dorm_logo']['name'];
        }
    }
    
    if($error_message == '')
    {
        $update_query = "UPDATE `dorm_settings`
                        SET dorm_name = :dorm_name,
                            dorm_address = :dorm_address,
                            dorm_email = :dorm_email,
                            dorm_contact_number = :dorm_contact_number,
                            dorm_logo = :dorm_logo,
                            dorm_logo_name = :dorm_logo_name,
                            open = :open,
                            close = :close";
                            
        $statement = $connect->prepare($update_query);
        $statement->execute($form_data);
        $success_message = '<li>Changes applied successfully</li>';
    }
}

//manager form validation
if(isset($_POST['account_apply']))
{
    $form_data = array(
        ':manager_name'    =>           $manager_data['manager_name'],
        ':manager_password'    =>       $manager_data['manager_password'],
        ':manager_pic'    =>            $manager_data['manager_pic']
    );
    if(!empty($_POST['manager_name']))
    {
        $form_data[':manager_name'] = $_POST['manager_name'];
    }
    if(!empty($_POST['manager_password']) && !empty($_POST['confirm_password']))
    {
        if(!validatepassword($_POST['manager_password']))
        {
            $error_message .= '<li>Password must match requirements.</li>';
        }
        else
        {
            if($_POST['confirm_password'] === $_POST['manager_password'])
            {
                $form_data[':manager_password'] = $_POST['manager_password'];
            }
            else
            {
                $error_message .= '<li>Password must match.</li>';
            }
        }
    }
    if(isset($_FILES['manager_pic']))
    {
        if(!empty($_FILES['manager_pic']['tmp_name']))
        {
           $form_data[':manager_pic'] = file_get_contents($_FILES['manager_pic']['tmp_name']);
        }
    }
    
    if($error_message == '')
    {
        $update_query ="UPDATE `manager_accounts`
                        SET manager_name = :manager_name,
                            manager_password = :manager_password,
                            manager_pic = :manager_pic
                        WHERE manager_id = '$manager_id'";
                            
        $statement = $connect->prepare($update_query);
        $statement->execute($form_data);
        $success_message = '<li>Changes applied successfully</li>';
    }
}


//fetching post-change manager data
$query = "SELECT * FROM manager_accounts WHERE manager_id = '$manager_id'";
$statement = $connect->query($query);
$manager_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching post-change dorm data
$query = "SELECT * FROM dorm_settings LIMIT 1";
$statement = $connect->query($query);
$dorm_data = $statement->fetch(PDO::FETCH_ASSOC);

include 'nav.php';

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Manager Settings</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                SETTINGS
            </h2>
            <span style="border-left: 0.2em solid grey; padding-left: 2em; height: 4em; width: 1em;"></span>
            <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($manager_data['manager_pic']);?>" alt="manager_icon" style=" object-fit: cover; width: 4em; height:4rem;border-radius: 50px;">
            <div class="flex_column">
                <h2><?php echo $manager_data['manager_name'] ?></h2>
                <span><?php echo $manager_data['manager_id'] ?></span>
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
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='dorm') echo 'class="side_tab_active"'; } ?> href="settings.php?tab=settings&sidetab=dorm&content=dorm">Dorm Settings</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='account') echo 'class="side_tab_active"'; } ?> href="settings.php?tab=settings&sidetab=account&content=account">Account Settings</a>
                <a href="logout.php">Logout</a>
            </div>
            <div class="content">
                <?php
                if(isset($_GET['content']))
                {
                    if($_GET['content']=='dorm')
                    {
                ?>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="dorm_name" style="margin-right: auto;">Dormitory Name :</label>
                                <input type="text" name="dorm_name" id="dorm_name" style="margin-right: auto;" value="<?php echo $dorm_data['dorm_name']; ?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="image" style="margin-right: auto;">Dormitory Icon :</label>
                                <label for="image" class="butt" style="margin-right: auto;">
                                    Upload Image
                                    <input type="file" name="dorm_logo" id="image" accept=".png" style="margin-right: auto;" />
                                </label>
                                <img id="preview" src="#" alt="DMS logo">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">Icon must be .png file</span>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="dorm_address" style="margin-right: auto;">Dormitory Address :</label>
                                <input type="text" name="dorm_address" id="dorm_address" style="margin-right: auto;" value="<?php echo $dorm_data['dorm_address']; ?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="dorm_email" style="margin-right: auto;">Dormitory Email :</label>
                                <input type="text" name="dorm_email" id="dorm_email" style="margin-right: auto;" value="<?php echo $dorm_data['dorm_email']; ?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="dorm_contact_number" style="margin-right: auto;">Dormitory Contact Number :</label>
                                <input type="text" name="dorm_contact_number" id="dorm_contact_number" style="margin-right: auto;" value="<?php echo $dorm_data['dorm_contact_number']; ?>">
                            </div>
                            <div class="flex_row" style="width: 80%;">
                                <div class="flex_row" style="margin-right: auto;">
                                    <label for="open" style="white-space:nowrap; margin-right: auto;">Opens at :</label>
                                    <input type="time" name="open" id="open" value="<?php $date = date("H:i", strtotime($dorm_data['open']));echo $date; ?>">
                                </div>
                                <div class="flex_row">
                                    <label for="close" style="white-space:nowrap; margin-right: auto;">Closes at :</label>
                                    <input type="time" name="close" id="close" value="<?php $date = date("H:i", strtotime($dorm_data['close']));echo $date; ?>">
                                </div>
                            </div>
                            <input class="butt" type="submit" name="dorm_apply" value="Apply Changes">
                        </form>
                <?php
                    }else if($_GET['content']=='account')
                    {
                ?>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="manager_name" style="margin-right: auto;">Manager Name :</label>
                                <input type="text" name="manager_name" id="manager_name" style="margin-right: auto;" value="<?php echo $manager_data['manager_name']; ?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="image" style="margin-right: auto;">Manager Picture :</label>
                                <label for="image" class="butt" style="margin-right: auto;">
                                    Upload Image
                                    <input type="file" name="manager_pic" id="image" accept=".png,.jpeg,.jpg" style="margin-right: auto;" />
                                </label>
                                <img id="preview" src="#" alt="Manager Icon" style="object-fit: cover; width: 6em; height:6em;border-radius: 20px;">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">Image must be .png or .jpeg file</span>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="manager_password" style="margin-right: auto;">Manager Password :</label>
                                <input type="password" name="manager_password" id="manager_password" style="margin-right: auto;" value="<?php echo $manager_data['manager_password']; ?>">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">(at least 8 characters, one uppercase, one lowercase, one digit and one special character)</span>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="confirm_password" style="margin-right: auto;">Confirm Manager Password :</label>
                                <input type="password" name="confirm_password" id="confirm_password" style="margin-right: auto;" value="<?php echo $manager_data['manager_password']; ?>">
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