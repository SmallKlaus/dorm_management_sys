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

//exporting records
if(isset($_GET['operation']) && $_GET['operation']=='export')
{
    if(isset($_GET['type']))
    {
        $query = "SELECT description FROM records_table WHERE record_type = '".$_GET['type']."'";
        $records = $connect->query($query);
        if($records->rowCount()>0)
        {
            $text = array();
            foreach($records->fetchAll() as $record)
            {
                $text[] = $record['description'];
            }
            $output = implode("\n", $text);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
            header("Content-Transfer-Encoding: binary;\n");
            header("Content-Disposition: attachment; filename=\"".$_GET['type'].date("Y-m-d").".txt\";\n");
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header("Content-Description: File Transfer");
            header("Content-Length: ".strlen($output).";\n");
            echo $output;
        }
        else
        {
            $error_message .= "<li>No records to export.</li>";
        }
    }
}

//clearing records
if(isset($_GET['operation']) && $_GET['operation']=='clear')
{
    if(isset($_GET['type']))
    {
        $query = "DELETE FROM records_table WHERE record_type = '".$_GET['type']."'";
        $connect->query($query);
        header('location:records.php?tab=records&sidetab='.$_GET['type'].'&content='.$_GET['type']);
    }
}

//fetching manager data
$query = "SELECT * FROM manager_accounts WHERE manager_id = '$manager_id'";
$statement = $connect->query($query);
$manager_data = $statement->fetch(PDO::FETCH_ASSOC);
include 'nav.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS . Records</title>
</head>
<body>
    <section class="flex_column">
        <div class="flex_row card" style="column-gap: 1em; margin-top: 3em; max-width: 800px;">
            <h2 style="padding-right: 2em;">
                RECORDS
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
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='staff') echo 'class="side_tab_active"'; } ?> href="records.php?tab=records&sidetab=staff&content=staff">Staff Records</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='student') echo 'class="side_tab_active"'; } ?> href="records.php?tab=records&sidetab=student&content=student">Student Records</a>
                <a <?php if(isset($_GET['sidetab'])){ if($_GET['sidetab']=='bill') echo 'class="side_tab_active"'; } ?> href="records.php?tab=records&sidetab=bill&content=bill">Bills Records</a>
            </div>
            <div class="content">
        <?php
            if(isset($_GET['content']))
            {
                if($_GET['content'] == 'staff')
                {
        ?>
                        <div style="position:absolute; top:2em; right:2em; font-size: .9rem;">
                            <button onclick="window.location='records.php?operation=export&type=staff&tab=records&sidetab=staff&content=staff'" class="butt" >Export Records</button>
                            <button onclick="window.location='records.php?operation=clear&type=staff'" class="butt" >Clear Records</button>
                        </div>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Record ID</th>
                                <th>Description</th>
                            </thead>
                            <tbody>
        <?php
                            $query = "SELECT * FROM records_table WHERE record_type = 'staff'";
                            $staff_records = $connect->query($query);
                                if($staff_records->rowCount()>0)
                                {
                                    foreach($staff_records->fetchAll() as $record)
                                    {
                                        echo '
                                        <tr>
                                            <td>'.$record['record_id'].'</td>
                                            <td>'.$record['description'].'</td>
                                        </tr>
                                        ';
                                    }
                                }
        ?>
                            </tbody>
                        </table>
        <?php
                }elseif($_GET['content'] == 'student')
                {
        ?>
                        <div style="position:absolute; top:2em; right:2em; font-size: .9rem;">
                            <button onclick="window.location='records.php?operation=export&type=student&tab=records&sidetab=student&content=student'" class="butt" >Export Records</button>
                            <button onclick="window.location='records.php?operation=clear&type=student'" class="butt" >Clear Records</button>
                        </div>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Record ID</th>
                                <th>Description</th>
                            </thead>
                            <tbody>
        <?php
                            $query = "SELECT * FROM records_table WHERE record_type = 'student'";
                            $staff_records = $connect->query($query);
                                if($staff_records->rowCount()>0)
                                {
                                    foreach($staff_records->fetchAll() as $record)
                                    {
                                        echo '
                                        <tr>
                                            <td>'.$record['record_id'].'</td>
                                            <td>'.$record['description'].'</td>
                                        </tr>
                                        ';
                                    }
                                }
        ?>
                            </tbody>
                        </table>
        <?php

                }elseif($_GET['content'] == 'bill')
                {
        ?>
                        <div style="position:absolute; top:2em; right:2em; font-size: .9rem;">
                            <button onclick="window.location='records.php?operation=export&type=bill&tab=records&sidetab=bill&content=bill'" class="butt" >Export Records</button>
                            <button onclick="window.location='records.php?operation=clear&type=bill'" class="butt" >Clear Records</button>
                        </div>
                        <table id="datatable" class="table" style="width: 100%;">
                            <thead>
                                <th>Record ID</th>
                                <th>Description</th>
                            </thead>
                            <tbody>
        <?php
                            $query = "SELECT * FROM records_table WHERE record_type = 'bill'";
                            $staff_records = $connect->query($query);
                                if($staff_records->rowCount()>0)
                                {
                                    foreach($staff_records->fetchAll() as $record)
                                    {
                                        echo '
                                        <tr>
                                            <td>'.$record['record_id'].'</td>
                                            <td>'.$record['description'].'</td>
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