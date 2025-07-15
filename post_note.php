<?php
session_start();
require 'config.php';
if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }

if($_SERVER['REQUEST_METHOD']==='POST'){
    $note=$_POST['note'] ?? '';
    if($note!==''){
        $ins=$db->prepare("INSERT INTO notes(user_id,content) VALUES(?,?)");
        $ins->execute([$_SESSION['user_id'],$note]);
    }
    header('Location: dashboard.php');
    exit;
}
header('Location: dashboard.php');
