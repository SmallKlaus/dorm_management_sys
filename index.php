<?php

include_once 'res/assets/connect.php';
include_once 'res/assets/global.php';


$error_message = '';
//manager login validation
if(isset($_POST['login_manager']))
{
    $form_data = array();
    if(empty($_POST['manager_id']))
    {
        $error_message .= '<li>-Manger ID field required.</li>';
    }
    else
    {
        $form_data[':manager_id'] = $_POST['manager_id'];
    }
    if(empty($_POST['manager_password']))
    {
        $error_message .= '<li>-Manger password field required.</li>';
    }
    else
    {
        $form_data[':manager_password'] = $_POST['manager_password'];
    }
    if($error_message == '')
    {
        $query = "SELECT * FROM manager_accounts WHERE manager_id = :manager_id AND manager_password = :manager_password";
        $statement = $connect->prepare($query);
        $statement->execute($form_data);
        if($statement->rowCount()>0)
        {
            $_SESSION['manager_id'] = $form_data[':manager_id'];
            header('location:Manager/settings.php?tab=settings&sidetab=dorm&content=dorm');
        }
        else
        {
            $error_message .= '<li>-Manager matching credentials not found.</li>';
        }
    }
}
//staff login validation
if(isset($_POST['login_staff']))
{
    $form_data = array();
    if(empty($_POST['staff_username']))
    {
        $error_message .= '<li>-Staff username field required.</li>';
    }
    else
    {
        $form_data[':staff_username'] = $_POST['staff_username'];
    }
    if(empty($_POST['staff_password']))
    {
        $error_message .= '<li>-Staff password field required.</li>';
    }
    else
    {
        $form_data[':staff_password'] = $_POST['staff_password'];
    }
    if($error_message == '')
    {
        $query = "SELECT * FROM staff_accounts WHERE staff_username = :staff_username AND staff_password = :staff_password";
        $statement = $connect->prepare($query);
        $statement->execute($form_data);
        if($statement->rowCount()>0)
        {
            $_SESSION['staff_id'] = $statement->fetch(PDO::FETCH_ASSOC)['staff_id'];
            header('location:Staff/settings.php?tab=settings&sidetab=account&content=account');
        }
        else
        {
            $error_message .= '<li>-Staff matching credentials not found.</li>';
        }
    }
}
//student login validation
if(isset($_POST['login_student']))
{
    $form_data = array();
    if(empty($_POST['student_username']))
    {
        $error_message .= '<li>-Student username field required.</li>';
    }
    else
    {
        $form_data[':student_username'] = $_POST['student_username'];
    }
    if(empty($_POST['student_password']))
    {
        $error_message .= '<li>-Student password field required.</li>';
    }
    else
    {
        $form_data[':student_password'] = $_POST['student_password'];
    }
    if($error_message == '')
    {
        $query = "SELECT * FROM students_accounts WHERE student_username = :student_username AND student_password = :student_password";
        $statement = $connect->prepare($query);
        $statement->execute($form_data);
        if($statement->rowCount()>0)
        {
            $_SESSION['student_id'] = $statement->fetch(PDO::FETCH_ASSOC)['student_id'];
            header('location:Student/settings.php?tab=settings&sidetab=account&content=account');
        }
        else
        {
            $error_message .= '<li>-Student matching credentials not found.</li>';
        }
    }
}

//fetch dorm-data
$dorm_data = $connect->query("SELECT * FROM dorm_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$dorm_logo = $dorm_data['dorm_logo'];
$dorm_name = $dorm_data['dorm_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="data:image/png;charset=utf8;base64,<?php echo base64_encode($dorm_logo);?>" type="image/x-icon">
    <title>Login . DMS</title>
    <link rel="stylesheet" href="res/assets/styles.css">
    <script src="res/assets/scripts.js"></script>
</head>

<body class="container flex_column responsive_body">
    <section id="content" class="flex_row" style="width: 100%;">
        <section id="tabs" class="flex_column">
            <div class="flex_column">
                <img src="data:image/png;charset=utf8;base64,<?php echo base64_encode($dorm_logo);?>" alt="DMS Icon" style="width: 15%;">
                <h2><?php echo $dorm_name; ?><br>Dormitory Management System</h2>
            </div>
            <div class="tab_bar flex_row" style="justify-content: space-evenly;">
                <label for="student" class="tab_window isactive">Student</label>
                <label for="staff" class="tab_window">Staff</label>
                <label for="manager" class="tab_window">Manager</label>
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
            ?>
            <div class="tabs_content">
                <form method="POST" spellcheck="false" autocomplete="off" id="student" class="flex_column" style="row-gap: 2em;">
                    <div class="flex_column" style="width: 80%; row-gap: .5em;">
                        <label for="student_username" style="margin-right: auto;">Student ID :</label>
                        <input type="text" name="student_username" id="student_username" style="margin-right: auto;">
                    </div>
                    <div class="flex_column" style="width: 80%; row-gap: .5em;">
                        <label for="student_password" style="margin-right: auto;">Student Password :</label>
                        <input type="password" name="student_password" id="student_password" style="margin-right: auto;">
                    </div>
                    <input class="butt" type="submit" name="login_student" value="Log In">
                </form>
                <form method="POST" spellcheck="false" autocomplete="off" id="staff" class="flex_column hidden" style="row-gap: 2em;">
                    <div class="flex_column" style="width: 80%; row-gap: .5em;">
                        <label for="staff_username" style="margin-right: auto;">Staff ID :</label>
                        <input type="text" name="staff_username" id="staff_username" style="margin-right: auto;">
                    </div>
                    <div class="flex_column" style="width: 80%; row-gap: .5em;">
                        <label for="staff_password" style="margin-right: auto;">Staff Password :</label>
                        <input type="password" name="staff_password" id="staff_password" style="margin-right: auto;">
                    </div>
                    <input class="butt" type="submit" name="login_staff" value="Log In">
                </form>
                <form method="POST" spellcheck="false" autocomplete="off" id="manager" class="flex_column hidden" style="row-gap: 2em;">
                    <div class="flex_column" style="width: 80%; row-gap: .5em;">
                        <label for="manager_id" style="margin-right: auto;">Manager ID :</label>
                        <input type="text" name="manager_id" id="manager_id" style="margin-right: auto;">
                    </div>
                    <div class="flex_column" style="width: 80%; row-gap: .5em;">
                        <label for="manager_password" style="margin-right: auto;">Manager Password :</label>
                        <input type="password" name="manager_password" id="manager_password" style="margin-right: auto;">
                    </div>
                    <input class="butt" type="submit" name="login_manager" value="Log In">
                </form>
            </div>
        </section>
        <section id="title" class="flex_column">
            <h1>Log in to the system.</h1>
            <h2>Chose the right type of user login.</h2>
            <div class="sessions" style="margin-top: 5em;">
    <?php
            if(isstafflogged())
            {
    ?>
                <button class="butt" onclick='window.location.href="Staff/settings.php?tab=settings&sidetab=account&content=account"'>Staff</button>
    
    <?php
            }
            if(ismanagerlogged())
            {
    ?>
                <button class="butt" onclick='window.location.href="Manager/settings.php?tab=settings&sidetab=dorm&content=dorm"'>Manager</button>
    <?php
            }
            if(isstudentlogged())
            {
    ?>
                <button class="butt" onclick='window.location.href="Student/settings.php?tab=settings&sidetab=account&content=account"'>Student</a>
    <?php
            }
    ?>
            </div>
        </section>
    </section>
    
    <footer>
        <div>
            <label for="email">Email: </label>
            <a href="mailto:<?php echo $dorm_data['dorm_email']; ?>"><?php echo $dorm_data['dorm_email']; ?></a>
        </div>
        <div>
            <label for="email">Contact Number: </label>
            <a href=""><?php echo $dorm_data['dorm_contact_number']; ?></a>
        </div>
    </footer>
    <script>
        //switch between tabs
        let forms = document.querySelectorAll('form');
        let tabs = document.querySelectorAll('.tab_window');
        tabs.forEach(tab => tab.addEventListener("click", function(){
            if(!tab.classList.contains('isactive'))
            {
                tabs.forEach(tab => tab.classList.remove('isactive'));
                tab.classList.add('isactive');
                forms.forEach(form => {
                    if(form.id == tab.getAttribute('for')) form.classList.remove('hidden');
                    else form.classList.add('hidden')});
            }
        }))
    </script>

</body>

</html>