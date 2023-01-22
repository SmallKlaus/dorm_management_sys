<?php
function ismanagerlogged()
{
    if(isset($_SESSION['manager_id'])) return true;
    else return false;
}

function isstudentlogged()
{
    if(isset($_SESSION['student_id'])) return true;
    else return false;
}

function isstafflogged()
{
    if(isset($_SESSION['staff_id'])) return true;
    else return false;
}

function getdateandtime()
{
    return date("Y-m-d H:i:s", strtotime(date('h:i:sa')));
}

function validatepassword($password)
{
    $upper = preg_match('@[A-Z]@', $password);
    $lower = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $special = preg_match('@[^\w]@', $password);

    if(!$upper || !$lower || !$number || !$special || strlen($password) < 8)
        return false;
    else return true;
}

function validatephonenumber($phonenumber)
{
    if(preg_match('/^[0-9]{10}+$/', $phonenumber)) return true;
    else return false;
}

?>