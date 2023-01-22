<?php

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!ismanagerlogged())
{
    header('location:../index.php');
}

$manager_id=$_SESSION['manager_id'];
$error_message = '';
$success_message = '';
//inserting staff_data
if(isset($_POST['add_staff']))
{
    $staff_id = hexdec(uniqid());
    $form_data = array(
        ':staff_id' => $staff_id,
        ':staff_username' => $staff_id
    );
    //validating form_data
    if(!empty($_POST['staff_name']))
    {
        $form_data[':staff_name'] = $_POST['staff_name'];
    }
    else
    {
        $error_message .= '<li>Staff name field is required.</li>';
    }
    if(!empty($_POST['staff_password']))
    {
        if(validatepassword($_POST['staff_password']))
        {
            $form_data[':staff_password'] = $_POST['staff_password'];
        }
        else
        {
            $error_message .= '<li>Password must match requirements.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Staff password field is required.</li>';
    }
    if(!empty($_POST['phone_number']))
    {
        if(validatephonenumber($_POST['phone_number']))
        {
            $form_data[':phone_number'] = $_POST['phone_number'];
        }
        else
        {
            $error_message .= '<li>Phone number must be valid.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Phone number field is required.</li>';
    }
    if(isset($_POST['staff_role']) && $_POST['staff_role'] != 'NULL')
    {
        $form_data[':staff_role'] = $_POST['staff_role'];
    }
    else
    {
        $error_message .= '<li>Staff role must be selected.</li>';
    }
    if(isset($_FILES['staff_picture']))
    {
        if(!empty($_FILES['staff_picture']['tmp_name']))
        {     
           $form_data[':staff_picture'] = file_get_contents($_FILES['staff_picture']['tmp_name']);
        }
        else
        {
            $error_message .= '<li>Staff picture is required.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Staff picture is required.</li>';
    }

    if($error_message == '')
    {
        $insert_query='INSERT INTO staff_accounts 
                        VALUES(:staff_id, :staff_username, :staff_password, :staff_name, CURRENT_DATE(), :phone_number, :staff_role, :staff_picture)';
        $statement=$connect->prepare($insert_query);
        $statement->execute($form_data);
        $save_path = "../res/images/general_faces/";
        $extension = strtolower(pathinfo($_FILES["staff_picture"]['name'],PATHINFO_EXTENSION));
        $file_name = $form_data[':staff_id'].'.'.$extension;
        move_uploaded_file($_FILES['staff_picture']['tmp_name'], $save_path.$file_name);
        $success_message = '<li>Staff member added successfully</li>';
    }
}

//editing staff_data
if(isset($_POST['edit_staff']))
{
    //fetching pre-change staff information
    $query = "SELECT * FROM staff_accounts WHERE staff_id = '".$_POST['staff_id']."'";
    $curr_staff = $connect->query($query)->fetch(PDO::FETCH_ASSOC);
    $form_data = array(
        ':staff_id' => $curr_staff['staff_id'],
        ':staff_username' => $curr_staff['staff_username'],
        ':staff_password' => $curr_staff['staff_password'],
        ':staff_name' => $curr_staff['staff_name'],
        ':phone_number' => $curr_staff['phone_number'],
        ':staff_role' => $curr_staff['staff_role'],
        ':staff_picture' => $curr_staff['staff_picture']
    );
    //validating form_data
    if(!empty($_POST['staff_username']))
    {
        $exists_query = "SELECT * FROM staff_accouts WHERE staff_id != '".$curr_staff['staff_id']."' AND staff_username = '".$_POST['staff_username']."'";
        $statement=$connect->query($exists_query);
        if($statement->rowCount() > 0)
        {
            $error_message .= '<li>Username already registered.</li>';
        }
        else
        {
            $form_data[':staff_username'] = $_POST['staff_username'];
        }
    }
    else
    {
        $error_message .= '<li>Username field is required.</li>';
    }
    if(!empty($_POST['staff_name']))
    {
        $form_data[':staff_name'] = $_POST['staff_name'];
    }
    else
    {
        $error_message .= '<li>Staff name field is required.</li>';
    }
    if(!empty($_POST['staff_password']))
    {
        if(validatepassword($_POST['staff_password']))
        {
            $form_data[':staff_password'] = $_POST['staff_password'];
        }
        else
        {
            $error_message .= '<li>Password must match requirements.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Staff password field is required.</li>';
    }
    if(!empty($_POST['phone_number']))
    {
        if(validatephonenumber($_POST['phone_number']))
        {
            $form_data[':phone_number'] = $_POST['phone_number'];
        }
        else
        {
            $error_message .= '<li>Phone number must be valid.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Phone number field is required.</li>';
    }
    if(isset($_POST['staff_role']) && $_POST['staff_role'] != 'NULL')
    {
        $form_data[':staff_role'] = $_POST['staff_role'];
    }
    else
    {
        $error_message .= '<li>Staff role must be selected.</li>';
    }
    if(isset($_FILES['staff_picture']))
    {
        if(!empty($_FILES['staff_picture']['tmp_name']))
        {     
           $form_data[':staff_picture'] = file_get_contents($_FILES['staff_picture']['tmp_name']);
        }
    }

    if($error_message == '')
    {
        $update_query='UPDATE staff_accounts 
                       SET staff_username = :staff_username,
                            staff_name = :staff_name,
                            phone_number = :phone_number,
                            staff_password = :staff_password,
                            staff_role = :staff_role,
                            staff_picture = :staff_picture
                        WHERE staff_id = :staff_id';
        $statement=$connect->prepare($update_query);
        $statement->execute($form_data);
        if(isset($_FILES['staff_picture']))
        {
            if(!empty($_FILES['staff_picture']['tmp_name']))
            {   
                //delete previous image
                $save_path = "../res/images/general_faces/";
                $file_name = $form_data[':staff_id'];
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
                $extension = strtolower(pathinfo($_FILES['staff_picture']['name'], PATHINFO_EXTENSION));
                $file_name = $form_data[':staff_id'].'.'.$extension;
                move_uploaded_file($_FILES['staff_picture']['tmp_name'],$save_path.$file_name);
            }
        }
        $success_message = '<li>Staff member information edited successfully</li>';
    }
}

//deleting staff information
if(isset($_GET['operation']) && $_GET['operation']=='delete')
{
    if(isset($_GET['staff']) && !empty($_GET['staff']))
    {
        $delete_query = "DELETE FROM staff_accounts WHERE staff_id = '".$_GET['staff']."'";
        $connect->query($delete_query);
        $save_path = "../res/images/general_faces/";
        $file_name = $_GET['staff'];
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
        header('location:staff.php?tab=staff&content=staff&sidetab=staff');
    }
}

//fetching manager data
$query = "SELECT * FROM manager_accounts WHERE manager_id = '$manager_id'";
$statement = $connect->query($query);
$manager_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching staff_accounts data
$query = "SELECT * FROM staff_accounts";
$staff_data = $connect->query($query);

include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Staff</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                STAFF MEMBERS
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
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='staff') echo 'class="side_tab_active"'; } ?> href="staff.php?tab=staff&sidetab=staff&content=staff">Staff Members</a>
            </div>
            <div class="content">
                <?php
                if(isset($_GET['content']))
                {
                    if($_GET['content']=="staff")
                    {
                       
                ?>
                        <button onclick="window.location='staff.php?tab=staff&content=add_staff'" class="butt" style="position:absolute; top:2em; right:2em; font-size: .9rem;">Add Staff</button>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Staff ID</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Joined On</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                                if($staff_data->rowCount()>0)
                                {
                                    $i=0;
                                    foreach($staff_data->fetchAll() as $staff)
                                    {
                                        echo '
                                        <tr>
                                            <td><a href="#">'.$staff['staff_id'].'</a></td>
                                            <td>'.$staff['staff_username'].'</td>
                                            <td>'.$staff['staff_password'].'</td>
                                            <td>'.$staff['staff_name'].'</td>
                                            <td>'.$staff['phone_number'].'</td>
                                            <td>'.$staff['created_on'].'</td>
                                            <td>
                                            <div id="wrapper'.$i.'" class="operations_wrapper">
                                                <button id="'.$i.'" class="options">&centerdot; &centerdot; &centerdot;</button>
                                                <div id="dropdown'.$i.'" class="dropdown_content flex_column">
                                                    <a href="staff.php?content=edit_staff&tab=staff&staff='.$staff['staff_id'].'">Edit Staff</a>
                                                    <a href="javascript: alertingMessage(\'Are you sure you want to delete Staff member: '.$staff['staff_id'].'?\' ,\'staff.php?operation=delete&staff='.$staff['staff_id'].'\')">
                                                            Delete
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
                    }else if($_GET['content']=="add_staff")
                    {
                ?>
                        <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                            <img src="../res/icons/add.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                            <h2>New Staff Member</h2>
                        </div>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_name" style="margin-right: auto;">Full Name : </label>
                                <input type="text" name="staff_name" id="staff_name" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="phone_number" style="margin-right: auto;">Phone Number : </label>
                                <input type="text" name="phone_number" id="phone_number" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_password" style="margin-right: auto;">Password : </label>
                                <input type="password" name="staff_password" id="staff_password" style="margin-right: auto;">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">(at least 8 characters, one uppercase, one lowercase, one digit and one special character)</span>
                                <input type="checkbox" onclick="showPwd('staff_password')" style="margin-left: auto;"> 
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_role" style="margin-right: auto;">Role : </label>
                                <select name="staff_role" id="staff_role">
                                    <option disabled selected value="NULL">Select a Role</option>
                                    <option value="Security Guard">Security Guard</option>
                                    <option value="Entry Clerk">Entry Clerk</option>
                                    <option value="Office Staff">Office Staff</option>
                                </select>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="image" style="margin-right: auto;">Staff Picture :</label>
                                <label for="image" class="butt" style="margin-right: auto;">
                                    Upload Image
                                    <input type="file" name="staff_picture" id="image" accept=".png,.jpeg,.jpg" style="margin-right: auto;" />
                                </label>
                                <img id="preview" src="#" alt="Staff Icon" style="object-fit: cover; width: 6em; height:6em;border-radius: 20px;">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">Image must be .png or .jpeg file</span>
                            </div>
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="add_staff" value="Add">
                                <a class="butt cancel" href='staff.php?tab=staff&content=staff&sidetab=staff'">Cancel</a>
                            </div>
                        </form>
                <?php
                    }else if($_GET['content']=="edit_staff")
                    {
                        //fetching staff to be edited
                        if(isset($_GET['staff']) && !empty($_GET['staff']))
                        {
                            $query = "SELECT * FROM staff_accounts WHERE staff_id = '".$_GET['staff']."'";
                            $staff = $connect->query($query)->fetch(PDO::FETCH_ASSOC);
                        
                ?>
                        <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                            <img src="../res/icons/edit.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                            <h2>Edit Staff Member</h2>
                        </div>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_username" style="margin-right: auto;">Username : </label>
                                <input type="text" name="staff_username" id="staff_username" style="margin-right: auto;" value="<?php echo $staff['staff_username'];?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_name" style="margin-right: auto;">Full Name : </label>
                                <input type="text" name="staff_name" id="staff_name" style="margin-right: auto;" value="<?php echo $staff['staff_name'];?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="phone_number" style="margin-right: auto;">Phone Number : </label>
                                <input type="text" name="phone_number" id="phone_number" style="margin-right: auto;" value="<?php echo $staff['phone_number'];?>">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_password" style="margin-right: auto;">Password : </label>
                                <input type="password" name="staff_password" id="staff_password" style="margin-right: auto;" value="<?php echo $staff['staff_password'];?>">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">(at least 8 characters, one uppercase, one lowercase, one digit and one special character)</span>
                                <input type="checkbox" onclick="showPwd('staff_password')" style="margin-left: auto;"> 
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="staff_role" style="margin-right: auto;">Role : </label>
                                <select name="staff_role" id="staff_role">
                                    <option disabled selected value="NULL">Select a Role</option>
                                    <option <?php echo ($staff['staff_role']=="Security Guard") ?'selected':''; ?> value="Security Guard">Security Guard</option>
                                    <option <?php echo ($staff['staff_role']=="Entry Clerk") ?'selected':''; ?> value="Entry Clerk">Entry Clerk</option>
                                    <option <?php echo ($staff['staff_role']=="Office Staff") ?'selected':''; ?> value="Office Staff">Office Staff</option>
                                </select>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="image" style="margin-right: auto;">Staff Picture :</label>
                                <label for="image" class="butt" style="margin-right: auto;">
                                    Upload Image
                                    <input type="file" name="staff_picture" id="image" accept=".png,.jpeg,.jpg" style="margin-right: auto;" />
                                </label>
                                <img id="preview" src="#" alt="Staff Icon" style="object-fit: cover; width: 6em; height:6em;border-radius: 20px;">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">Image must be .png or .jpeg file</span>
                            </div>
                            <input type="hidden" name="staff_id" value="<?php echo $staff['staff_id']; ?>">
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="edit_staff" value="Edit">
                                <a class="butt cancel" href='staff.php?tab=staff&content=staff&sidetab=staff'">Cancel</a>
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