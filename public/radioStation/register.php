<?php
session_start();
if(!empty($_POST['fname'])){
    $_SESSION['id']   = strtotime(date("YmdHis"));
    $_SESSION['name']   = trim($_POST['fname'].' '.$_POST['lname']);
    echo "OK"; 
}
?> 