<?php
// File: admin/edit_service.php
// Redirects to services_admin.php with the edit param
session_start();
include "../database.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    header("Location: services_admin.php?edit=$id");
} else {
    header("Location: services_admin.php");
}
exit();
