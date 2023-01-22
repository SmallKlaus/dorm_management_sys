<?php

include_once '../res/assets/connect.php';
include_once '../res/assets/global.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\SpreadSheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

if(!ismanagerlogged())
{
    header('location:../index.php');
}

$manager_id=$_SESSION['manager_id'];
$error_message = '';
$success_message = '';

//adding bills
if(isset($_POST['add_bill']))
{
    $form_data = array(
        ':bill_id' => uniqid()
    );
    //validating form data
    if(!empty($_POST['bill_building']))
    {
        $form_data[':bill_building'] = $_POST['bill_building'];
    }
    else
    {
        $error_message  .= '<li>Building number field is required.</li>';
    }
    if(!empty($_POST['bill_room']))
    {
        $form_data[':bill_room'] = $_POST['bill_room'];
    }
    else
    {
        $error_message  .= '<li>Room number field is required.</li>';
    }
    if(!empty($_POST['bill_type']))
    {
        $form_data[':bill_type'] = $_POST['bill_type'];
    }
    else
    {
        $error_message  .= '<li>Bill type field is required.</li>';
    }
    if(!empty($_POST['bill_amount']))
    {
        $form_data[':bill_amount'] = $_POST['bill_amount'];
    }
    else
    {
        $error_message  .= '<li>Bill Fee field is required.</li>';
    }

    //inserting to database
    if($error_message == '')
    {
        $insert_query = "INSERT INTO bills_table VALUES(:bill_id, :bill_building, :bill_room, :bill_amount, CURRENT_DATE, :bill_type, 0)";
        $statement = $connect->prepare($insert_query);
        $statement->execute($form_data);

        $student_array = array(
            ':bill_amount' => $form_data[':bill_amount'],
            ':bill_building' => $form_data[':bill_building'],
            ':bill_room' => $form_data[':bill_room']
        );
        $update_student = "UPDATE students_accounts SET student_total_bill = student_total_bill + :bill_amount WHERE student_building = :bill_building AND student_room = :bill_room";
        $statement = $connect->prepare($update_student);
        $statement->execute($student_array);
        $success_message = "<li>Bill has been added successfully.</li>";
    }
}

//deleting bills 
if(isset($_GET['operation']) && $_GET['operation']=='delete')
{
    if(isset($_GET['bill']) && !empty($_GET['bill']))
    {
        //updating student_total_bill
        $bill = $connect->query("SELECT * FROM bills_table WHERE bill_id = '".$_GET['bill']."'")->fetch(PDO::FETCH_ASSOC);
        $amount = $bill['bill_amount'];
        $building = $bill['bill_building'];
        $room = $bill['bill_room'];
        $update_query = "UPDATE students_accounts SET student_total_bill = student_total_bill - $amount WHERE student_building = '$building' AND student_room = '$room' ";
        $connect->query($update_query);
        //deleting bill from database
        $delete_query = "DELETE FROM bills_table WHERE bill_id = '".$_GET['bill']."'";
        $connect->query($delete_query);
        header('location:bills.php?tab=bills&content=bills&sidetab=bills');
    }
}

//clearing paid bills from database
if(isset($_GET['operation']) && $_GET['operation']=='clear')
{
    //deleting all paid bills from database
    $clear_query = "DELETE FROM bills_table WHERE bill_status = 1";
    $connect->query($clear_query);
    header('location:bills.php?tab=bills&content=paid_bills&sidetab=paid_bills');
    
}

//paying bills
if(isset($_GET['operation']) && $_GET['operation']=='pay')
{
    if(isset($_GET['bill']) && !empty($_GET['bill']))
    {
        //creating record and changing bill_status
        $record_id = uniqid();
        $record_type = 'bill';
        $description = 'Manager '.$manager_id.' has paid bill number : '.$_GET['bill'].' On: '.getdateandtime();
        $connect->query("INSERT INTO records_table VALUES('$record_id', '$record_type','$description')");
        $update_query = "UPDATE bills_table SET bill_status = 1 WHERE bill_id = '".$_GET['bill']."'";
        $connect->query($update_query);
        //updating student_total_bill
        $bill = $connect->query("SELECT * FROM bills_table WHERE bill_id = '".$_GET['bill']."'")->fetch(PDO::FETCH_ASSOC);
        $amount = $bill['bill_amount'];
        $building = $bill['bill_building'];
        $room = $bill['bill_room'];
        $update_query = "UPDATE students_accounts SET student_total_bill = student_total_bill - $amount WHERE student_building = '$building' AND student_room = '$room' ";
        $connect->query($update_query);
        header('location:bills.php?tab=bills&content=bills&sidetab=bills');
    }
}
//importing bills
if(isset($_POST['save_file']))
{
    if(isset($_FILES['import_excel']) && $_FILES['import_excel']['error']!=UPLOAD_ERR_NO_FILE)
    {
        $arr_file = explode('.', $_FILES['import_excel']['name']);
        $extension = end($arr_file);
        if('csv' == $extension) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        }else{
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        } 
        $spreadsheet = $reader->load($_FILES['import_excel']['tmp_name']);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        if(!empty($sheetData))
        {
            for($i=1; $i<count($sheetData); $i++)
            {
                $building = $sheetData[$i][0];
                $room = $sheetData[$i][1];
                $amount = $sheetData[$i][2];
                $type = $sheetData[$i][3];
                $id = uniqid();
                $check_query = "SELECT * FROM rooms_table WHERE building_number = '$building' AND room_number = '$room' AND living_status = 'Occupied'";
                if($connect->query($check_query)->rowCount()>0)
                {
                    $query = "INSERT INTO bills_table VALUES('$id', '$building', '$room', $amount, CURRENT_DATE, '$type', 0)";
                    $statement = $connect->query($query);
                    $update_query = "UPDATE students_accounts SET student_total_bill = student_total_bill + $amount WHERE student_building = '$building' AND student_room = '$room'";
                    $statemtn = $connect->query($update_query);
                }
            }
            $success_message = '<li>Spreadsheet added successfully.</li>';
        }
    }
    else
    {
        $error_message .= '<li>Import a Spreadsheet file first.</li>';
    }
}


//fetching manager data
$query = "SELECT * FROM manager_accounts WHERE manager_id = '$manager_id'";
$statement = $connect->query($query);
$manager_data = $statement->fetch(PDO::FETCH_ASSOC);
//fetching bills data
$query = "SELECT * FROM bills_table WHERE bill_status =0";
$bill_data = $connect->query($query);
$query = "SELECT * FROM bills_table WHERE bill_status =1";
$paid_bill_data = $connect->query($query);


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
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                ROOM BILLS
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
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='bills') echo 'class="side_tab_active"'; } ?> href="bills.php?tab=bills&sidetab=bills&content=bills">Room Bills</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='paid_bills') echo 'class="side_tab_active"'; } ?> href="bills.php?tab=bills&sidetab=paid_bills&content=paid_bills">Paid Bills</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='import') echo 'class="side_tab_active"'; } ?> href="bills.php?tab=bills&sidetab=import&content=import">Import Bills</a>
            </div>
            <div class="content">
                <?php
                if(isset($_GET['content']))
                {
                    if($_GET['content']=="bills")
                    {
                       
                ?>
                        <button onclick="window.location='bills.php?tab=bills&content=add_bills'" class="butt" style="position:absolute; top:2em; right:2em; font-size: .9rem;">Add Bill</button>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Bill ID</th>
                                <th>Building Nr.</th>
                                <th>Room Nr.</th>
                                <th>Fee</th>
                                <th>Issued On</th>
                                <th>Bill Type</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                                if($bill_data->rowCount()>0)
                                {
                                    $i=0;
                                    foreach($bill_data->fetchAll() as $bill)
                                    {
                                        echo '
                                        <tr>
                                            <td><a href="#">'.$bill['bill_id'].'</a></td>
                                            <td>'.$bill['bill_building'].'</td>
                                            <td>'.$bill['bill_room'].'</td>
                                            <td>'.$bill['bill_amount'].'</td>
                                            <td>'.$bill['created_on'].'</td>
                                            <td>'.$bill['bill_type'].'</td>
                                            <td>
                                            <div id="wrapper'.$i.'" class="operations_wrapper">
                                                <button id="'.$i.'" class="options">&centerdot; &centerdot; &centerdot;</button>
                                                <div id="dropdown'.$i.'" class="dropdown_content flex_column">
                                                    <a href="javascript: alertingMessage(\'Are you sure you want to pay bill number: '.$bill['bill_id'].'?\', \'bills.php?operation=pay&bill='.$bill['bill_id'].'&issuedate='.$bill['created_on'].'&type='.$bill['bill_type'].'\')">
                                                            Pay Bill
                                                    </a>
                                                    <a href="javascript: alertingMessage(\'Are you sure you want to delete bill number: '.$bill['bill_id'].'?\',\'bills.php?operation=delete&bill='.$bill['bill_id'].'\')">
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
                    }else if($_GET['content']=="paid_bills")
                    {
                ?>
                        <button onclick="alertingMessage('Are you sure you want to clear all paid bills?', 'bills.php?operation=clear')" class="butt" style="position:absolute; top:2em; right:2em; font-size: .9rem;">Clear Bills</button>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Bill ID</th>
                                <th>Building Nr.</th>
                                <th>Room Nr.</th>
                                <th>Fee</th>
                                <th>Issued On</th>
                                <th>Bill Type</th>
                                <th>Operations</th>
                            </thead>
                            <tbody>
                <?php
                                if($paid_bill_data->rowCount()>0)
                                {
                                    $i=0;
                                    foreach($paid_bill_data->fetchAll() as $bill)
                                    {
                                        echo '
                                        <tr>
                                            <td><a href="#">'.$bill['bill_id'].'</a></td>
                                            <td>'.$bill['bill_building'].'</td>
                                            <td>'.$bill['bill_room'].'</td>
                                            <td>'.$bill['bill_amount'].'</td>
                                            <td>'.$bill['created_on'].'</td>
                                            <td>'.$bill['bill_type'].'</td>
                                            <td>
                                            <div id="wrapper'.$i.'" class="operations_wrapper">
                                                <button id="'.$i.'" class="options">&centerdot; &centerdot; &centerdot;</button>
                                                <div id="dropdown'.$i.'" class="dropdown_content flex_column">
                                                    <a href="javascript: alertingMessage(\'Are you sure you want to delete bill number: '.$bill['bill_id'].'?\', \'bills.php?operation=delete&bill='.$bill['bill_id'].'\')">
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
                    }else if($_GET['content']=="add_bills")
                    {
                ?>
                        <div class="flex_row" style="column-gap: 1em; margin-bottom:4em; width:100%; justify-content: flex-start;">
                            <img src="../res/icons/add.png" alt="add" style="width: 1.2em; filter: invert(0.7);">
                            <h2>New Bill</h2>
                        </div>
                        <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="bill_building" style="margin-right: auto;">Building Number : </label>
                                <select onchange="buildingSelected()" name="bill_building" id="bill_building">
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
                                <label <?php if(!isset($_GET['building'])) echo 'hidden'; ?> for="bill_room" style="margin-right: auto;">Room Number : </label>
                                <select <?php if(!isset($_GET['building'])) echo 'hidden'; ?>  name="bill_room" id="bill_room">
                                    <option disabled selected value="">Select a Room</option>
                                    <?php
                                    if(isset($_GET['building']))
                                    {
                                        $room_numbers_query = "SELECT room_number FROM rooms_table WHERE building_number = '".$_GET['building']."' AND living_status = 'Occupied' ORDER BY room_number +0";
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
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="bill_type" style="margin-right: auto;">Bill Type : </label>
                                <select name="bill_type" id="bill_type">
                                    <option selected disabled value="">Select a Type</option>
                                    <option value="Water & Electricity">Water & Electricity</option>
                                    <option value="Insurance">Insurance</option>
                                    <option value="Monthly Rent">Monthly Rent</option>
                                </select>
                            </div>
                            <div class="flex_column" style="width: 80%; row-gap: .5em;">
                                <label for="bill_amount" style="margin-right: auto;">Bill Fee : </label>
                                <input type="number" step="0.01" name="bill_amount" id="bill_amount">
                                <span style="font-weight: 100; font-size: .6rem; margin-left: auto">Fee in Dollars ($)</span>
                            </div>
                            <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="add_bill" value="Add">
                                <a class="butt cancel" href='bills.php?tab=bills&content=bills&sidetab=bills'">Cancel</a>
                            </div>
                        </form>
                <?php
                    }else if($_GET['content'] == "import")
                    {
                ?>
                    <form method="POST" spellcheck="false" autocomplete="off" class="flex_column" style="row-gap: 2em;" enctype="multipart/form-data">
                        <h2>Import Bills Into the Database From an Excel Sheet</h2>
                        <div id="file_selected" style="border: 1px solid grey; border-radius: 50px; padding: .5em 1em;">
                            No File Selected
                        </div>
                        <label for="import_excel" class="butt">Upload SpreadSheet
                            <input type="file" name="import_excel" id="import_excel" accept=".xlsx, .xls, .csv" style="margin-right: auto;" >
                        </label>
                        <h5>Rules:</h5>
                        <span style="font-weight: 100; font-size: .8rem;">-Make sure the order of the columns corresponds to the order displayed on the table.(building, room, amount, type) <br>-The system won't check for duplicate imports, so careful while using this option. <br>-The bill ID should be unique and will be therefore assigned by the system.<br>-The first row of the spreadsheet should be a header not data.<br>-The bills will be automatically filtered to register only bills for occupied rooms. <br>-File must be .csv, .xls or .xlsx .</span>
                        <div class="flex_row" style="width: 60%; justify-content: space-evenly;">
                                <input class="butt" type="submit" name="save_file" value="Import">
                                <a class="butt cancel" href='bills.php?tab=bills&content=bills&sidetab=bills'">Cancel</a>
                        </div>
                    </form>
                <?php
                    }
                }
                ?>
            </div>
    </section>
    <script>
        let import_excel = document.getElementById('import_excel');
        let file_selected = document.getElementById('file_selected');
        import_excel.onchange = function() {
            let filename = import_excel.files[0].name;
            file_selected.innerText = filename;
        }
    </script>
</body>
</html>