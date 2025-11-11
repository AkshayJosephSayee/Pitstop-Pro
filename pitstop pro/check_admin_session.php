<?php
// check_admin_session.php
session_start();
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['Username'])) {
    header('Location: login.html');
    exit;
}
?>