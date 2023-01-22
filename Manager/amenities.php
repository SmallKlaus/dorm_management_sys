<?php

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';

if(!ismanagerlogged())
{
    header('location:../index.php');
}
$manager_id = $_SESSION['manager_id'];
$error_message = '';
$success_message = '';

//inserting building data
if(isset($_POST['add_building']))
{
    $form_data = array();
    //validating form data
    if(!empty($_POST['building_number']))
    {
        $exists_query = "SELECT * FROM buildings_table WHERE building_number = '".$_POST['building_number']."' ";
        $statement = $connect->query($exists_query);
        if($statement->rowCount()>0)
        {
            $error_message .= '<li>Building number already registered.</li>';
        }
        else
        {
            $form_data[':building_number']=$_POST['building_number'];
        }
    }
    else
    {
        $error_message .= '<li>Building number field must be filled.</li>';
    }
    if(!empty($_POST['building_rooms']))
    {
        $form_data[':building_rooms'] = $_POST['building_rooms'];
        $form_data[':building_empty_rooms'] = $_POST['building_rooms'];
    }
    else
    {
        $error_message .= '<li>Number of rooms field must be filled.</li>';
    }
    if(!empty($_POST['building_floors']))
    {
        $form_data[':building_floors'] = $_POST['building_floors'];
    }
    else
    {
        $error_message .= '<li>Building floors field must be filled.</li>';
    }
    if(isset($_POST['building_status']))
    {
        $form_data[':building_status'] = 'Open';
    }
    else
    {
        $form_data[':building_status'] = 'Closed';
    }
    //inserting data
    if($error_message == '')
    {
        $insert_query = "INSERT INTO buildings_table VALUES(:building_number, :building_rooms, :building_empty_rooms, :building_floors, :building_status)";
        $statement = $connect->prepare($insert_query);
        $statement->execute($form_data);
        //inserting corresponding rooms
        $i = 1;
        $room_data = array(
            ':building_number' => $form_data[':building_number'],
            ':living_status' => 'Unoccupied',
            ':room_bills' => 0,
            ':room_status' => 'Closed'
        );

        for($i=1;$i<=$form_data[':building_rooms'];$i++)
        {
            $room_data['room_number'] = $i;
            $insert_query = "INSERT INTO rooms_table VALUES(:building_number, :room_number, :living_status, :room_bills, :room_status)";
            $statement = $connect->prepare($insert_query);
            $statement->execute($room_data);
        }
        $success_message = '<li>Building added successfully</li>';
    }

}

//updating building status or room_status
if(isset($_GET['operation'])  && $_GET['operation'] == 'status')
{
    $update_query="";
    $building_number = $_GET['building'];
    $building_status = $connect->query("SELECT building_status FROM buildings_table WHERE building_number=$building_number")->fetch(PDO::FETCH_ASSOC)['building_status'];
    if(isset($_GET['room']))
    {
        $room_number = $_GET['room'];
        $room_status = $connect->query("SELECT room_status FROM rooms_table WHERE building_number= $building_number AND room_number = $room_number")->fetch(PDO::FETCH_ASSOC)['room_status'];
        if($room_status =='Closed')
        {
            if($building_status =='Closed')
            {
                $error_message .= '<li>Building is closed, couldn\'t alter room status</li>';
            }
            else
            {
                $update_query = "UPDATE rooms_table SET room_status = 'Open' WHERE building_number = $building_number AND room_number = $room_number";
            }
        }
        else if($room_status == 'Open')
        {
            $update_query = "UPDATE rooms_table SET room_status = 'Closed' WHERE building_number = $building_number AND room_number = $room_number";
        }
        if($update_query != "")
        {
            $statement = $connect->query($update_query);
            header('location:amenities.php?tab=amenities&sidetab=room&content=room');
        }
    }
    else
    {     
        if($building_status == 'Closed')
        {
            $update_query = "UPDATE buildings_table SET building_status =  'Open' WHERE building_number = $building_number";
        }
        else if($building_status == 'Open')
        {
            $occupied_query = "SELECT * FROM rooms_table WHERE building_number = $building_number AND living_status = 'Occupied'";
            $occupied_rooms = $connect->query($occupied_query);
            if($occupied_rooms->rowCount()>0)
            {
                $update_query = "";
                $error_message .= '<li>Building contains occupied rooms.</li>';
            }
            else
            {
                $update_query = "UPDATE buildings_table SET building_status =  'Closed' WHERE building_number = $building_number";
                $connect->query("UPDATE rooms_table SET room_status = 'Closed' WHERE building_number = $building_number");
            }
        }
        if($update_query != "")
        {
            $statement = $connect->query($update_query);
            header('location:amenities.php?tab=amenities&sidetab=building&content=building');
        }
    }
}

//opening building rooms
if(isset($_GET['operation']) && $_GET['operation'] == 'open_room')
{
    $building_number = $_GET['building'];
    $building_status = $connect->query("SELECT building_status FROM buildings_table WHERE building_number=$building_number")->fetch(PDO::FETCH_ASSOC)['building_status'];
    if($building_status == 'Closed')
    {
        $error_message .= '<li>Building needs to be open first.</li>';
    }
    else
    {
        $update_query = "UPDATE rooms_table SET room_status = 'Open' WHERE building_number = $building_number";
        $connect->query($update_query);
        $success_message .= '<li>Rooms of building '.$building_number.' have been opened.</li>';
    }
}

//delete building from database
if(isset($_GET['operation']) && $_GET['operation'] == "delete")
{
    $building_number = $_GET['building'];
    if(isset($_GET['room']))
    {
        $room_number = $_GET['room'];
        $living_status = $connect->query("SELECT living_status FROM rooms_table WHERE building_number= $building_number AND room_number = $room_number")->fetch(PDO::FETCH_ASSOC)['living_status'];
        if($living_status == 'Occupied')
        {
            $error_message .= '<li>Room currently occupied, couldn\'t delete room</li>';
        }
        else
        {
            $delete_query = "DELETE FROM rooms_table WHERE building_number = $building_number AND room_number = $room_number";
            $statement = $connect->query($delete_query);
            $update_query = "UPDATE buildings_table SET building_rooms = building_rooms - 1, building_empty_rooms = building_empty_rooms - 1 WHERE building_number = $building_number";
            $statement = $connect->query($update_query);
            //TODO remove corresponding bills
        }

    }
    else
    {
        $delete_query = "DELETE FROM buildings_table WHERE building_number = $building_number";
        $statement = $connect->query($delete_query);
        $delete_query = "DELETE FROM rooms_table WHERE building_number = $building_number";
        $statement = $connect->query($delete_query);
        header('location:amenities.php?tab=amenities&sidetab=building&content=building');
    }
}

//inserting room data
if(isset($_POST['add_room']))
{
    $form_data = array();
    if(!empty($_POST['building_number']))
    {
        if(!empty($_POST['room_number']))
        {
            $exists_query = "SELECT * FROM rooms_table WHERE building_number = '".$_POST['building_number']."' AND room_number = '".$_POST['room_number']."'";
            $statement = $connect->query($exists_query);
            if($statement->rowCount()>0)
            {
                $error_message .= '<li>Room already registered</li>';
            }
            else
            {
                $form_data[':building_number'] = $_POST['building_number'];
                $form_data[':room_number'] = $_POST['room_number'];
                $form_data[':room_bills'] = 0;
                $form_data[':living_status'] = 'Unoccupied';
            }
        }
        else
        {
            $error_message.= '<li>Room number is required</li>';
        }
    }
    else
    {
        $error_message.= '<li>Building number is required</li>';
    }
    if(!empty($_POST['room_bills']))
    {
        $form_data[':room_bills'] = $_POST['room_bills'];
    }
    if(isset($_POST['room_status']))
    {
        $form_data[':room_status'] = 'Open';
    }
    else
    {
        $form_data[':room_status'] = 'Closed';  
    }
    //inserting data and updating number of rooms
    if($error_message == '')
    {
        $insert_query = "INSERT INTO rooms_table VALUES(:building_number, :room_number, :living_status, :room_bills, :room_status)";
        $update_query = "UPDATE buildings_table SET building_rooms = building_rooms + 1, building_empty_rooms = building_empty_rooms + 1 WHERE building_number = '".$form_data[':building_number']."'";
        //updating number of rooms
        $statement = $connect->query($update_query);
        //inserting room data
        $statement = $connect->prepare($insert_query);
        $statement->execute($form_data);
        $success_message = '<li>Room added successfully</li>';
    }
}

//fetching manager data
$query = "SELECT * FROM manager_accounts WHERE manager_id = '$manager_id'";
$statement = $connect->query($query);
$manager_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching building data
$query = "SELECT * FROM buildings_table";
$building_data = $connect->query($query);
//fetching room data
$query = "SELECT * FROM rooms_table";
if(isset($_GET['building_query'])) $query = "SELECT * FROM rooms_table WHERE building_number = '".$_GET['building_query']."'";
$room_data = $connect->query($query);

include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Amenities</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                AMENITIES
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
        <div class="flex_row card holder" style="margin-top: 3em; justify-content:flex-start; width:80%;"> 
            <div class="flex_column side_menu">
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='building') echo 'class="side_tab_active"'; } ?> href="amenities.php?tab=amenities&sidetab=building&content=building">Dorm Buildings</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='room') echo 'class="side_tab_active"'; } ?> href="amenities.php?tab=amenities&sidetab=room&content=room">Dorm Rooms</a>
            </div>
            <div class="content">
                <?php
                if(isset($_GET['content']))
                {
                    if($_GET['content']=='add_building')
                    {
                ?>
                        <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                            <img src="../res/icons/add.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                            <h2>New Building:</h2>
                        </div>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="building_number" style="margin-right: auto;">Building Number : </label>
                                <input type="text" name="building_number" id="building_number" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="building_rooms" style="margin-right: auto;">Building Rooms : </label>
                                <input type="number" name="building_rooms" id="building_rooms" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="building_floors" style="margin-right: auto;">Building Floors : </label>
                                <input type="number" name="building_floors" id="building_floors" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="building_status" style="margin-right: auto;">Building Status : </label>
                                <div class="flex_row" style="margin-right: auto; padding: 1em 1em; width: 100%; justify-content: flex-start; column-gap: 2em;">
                                    <div class="check-box" >
                                        <input name="building_status" id="building_status" type="checkbox">
                                    </div>
                                    <p id="status_preview" class="Closed" style="width: fit-content; font-size: 1rem; min-width: 5em; text-align:center">Closed</p>
                                </div>
                            </div>
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="add_building" value="Add">
                                <a class="butt cancel" href='amenities.php?tab=amenities&content=building&sidetab=building'">Cancel</a>
                            </div>
                        </form>
                <?php
                    }else if($_GET['content']=='building')
                    {
                ?>
                        <button onclick="window.location='amenities.php?tab=amenities&sidetab=building&content=add_building'" class="butt" style="position:absolute; top:2em; right:2em; font-size: .9rem;">Add Building</button>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Building Num.</th>
                                <th>Num. of Floors</th>
                                <th>Num. of Rooms</th>
                                <th>Empty Rooms</th>
                                <th>Status</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                    if($building_data->rowCount()>0)
                    {
                        $i=0;
                        foreach($building_data->fetchAll() as $building)
                        {
                            echo '
                        <tr>
                            <td><a href="amenities.php?tab=amenities&sidetab=room&content=room&building_query='.$building['building_number'].'">'.$building['building_number'].'</a></td>
                            <td>'.$building['building_floors'].'</td>
                            <td>'.$building['building_rooms'].'</td>
                            <td>'.$building['building_empty_rooms'].'</td>
                            <td style="width: 15%;"><p class="'.$building['building_status'].'">'.$building['building_status'].'</p></td>
                            <td>
                                <div id="wrapper'.$i.'" class="operations_wrapper">
                                    <button id="'.$i.'" class="options">&centerdot; &centerdot; &centerdot;</button>
                                    <div id="dropdown'.$i.'" class="dropdown_content flex_column">
                                        <a href="amenities.php?operation=status&building='.$building['building_number'].'&tab=amenities&sidetab=building&content=building">Alter Status</a>
                                        <a href="amenities.php?operation=open_room&building='.$building['building_number'].'&tab=amenities&sidetab=building&content=building">Open Rooms</a>
                                        <a href="javascript: alertingMessage(\'Are you sure you want to delete Building number: '.$building['building_number'].'?\' ,\'amenities.php?operation=delete&building='.$building['building_number'].'\')">
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
                    }else if($_GET['content'] == 'add_room')
                    {
                ?>
                        <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                            <img src="../res/icons/add.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                            <h2>New Room :</h2>
                        </div>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="building_number" style="margin-right: auto;">Building Number : </label>
                                <select name="building_number" id="building_number" style="margin-right: auto;">
                                    <option value="" selected disabled hidden>Select Building</option>
                                    <?php
                                    if($building_data->rowCount()>0)
                                    {
                                        foreach($building_data->fetchAll() as $building)
                                        {
                                            echo '<option value="'.$building['building_number'].'">'.$building['building_number'].'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="room_number" style="margin-right: auto;">Room Number : </label>
                                <input type="text" name="room_number" id="room_number" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="room_bills" style="margin-right: auto;">Room Bills : </label>
                                <input type="number" name="room_bills" id="room_bills" style="margin-right: auto;">
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="room_status" style="margin-right: auto;">Room Status : </label>
                                <div class="flex_row" style="margin-right: auto; padding: 1em 1em; width: 100%; justify-content: flex-start; column-gap: 2em;">
                                    <div class="check-box" >
                                        <input name="room_status" id="room_status" type="checkbox">
                                    </div>
                                    <p id="status_preview" class="Closed" style="width: fit-content; font-size: 1rem; min-width: 5em; text-align:center">Closed</p>
                                </div>
                            </div>
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="add_room" value="Add">
                                <a class="butt cancel" href='amenities.php?tab=amenities&content=room&sidetab=room'">Cancel</a>
                            </div>
                        </form>
                <?php
                    }else if($_GET['content']=='room')
                    {
                ?>
                        <button onclick="window.location='amenities.php?tab=amenities&sidetab=room&content=add_room'" class="butt" style="position:absolute; top:2em; right:2em; font-size: .9rem;">Add Room</button>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Building Num.</th>
                                <th>Room Num.</th>
                                <th>Living Status</th>
                                <th>Room Status</th>
                                <th>Room Bill</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                    if($room_data->rowCount()>0) //TODO add clickable row that takes you to a certain room's bill.
                    {
                        $i=0;
                        foreach($room_data->fetchAll() as $room)
                        {
                            echo '
                        <tr>
                            <td>'.$room['building_number'].'</td>
                            <td>'.$room['room_number'].'</td>
                            <td><p class="'.$room['living_status'].'">'.$room['living_status'].'</p></td>
                            <td><p class="'.$room['room_status'].'">'.$room['room_status'].'</p></td>
                            <td>'.$room['room_bills'].'</td>
                            <td>
                                <div id="wrapper'.$i.'" class="operations_wrapper">
                                    <button id="'.$i.'" class="options">&centerdot; &centerdot; &centerdot;</button>
                                    <div id="dropdown'.$i.'" class="dropdown_content flex_column">
                                        <a href="amenities.php?tab=amenities&sidetab=room&content=room&operation=status&building='.$room['building_number'].'&room='.$room['room_number'].'">Alter Status</a>
                                        <a href="javascript: alertingMessage(\'Are you sure you want to delete Room '.$room['room_number'].' from Building '.$room['building_number'].'?\' ,\'amenities.php?tab=amenities&sidetab=room&content=room&operation=delete&building='.$room['building_number'].'&room='.$room['room_number'].'\')">
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
                    }
                }
                ?>
            </div>
        </div>
    </section>
    <script>
        let status_preview = document.getElementById('status_preview');
        let status_checkbox = document.getElementById('building_status');
        if(status_checkbox == undefined) status_checkbox = document.getElementById('room_status');
        status_checkbox.addEventListener('change', function(){
            if(this.checked)
            {
                status_preview.innerText='Open';
                status_preview.classList.remove('Closed');
                status_preview.classList.add('Open');
            }
            else
            {
                status_preview.innerText='Closed';
                status_preview.classList.remove('Open');
                status_preview.classList.add('Closed');
            }
        })
    </script>
</body>
</html>