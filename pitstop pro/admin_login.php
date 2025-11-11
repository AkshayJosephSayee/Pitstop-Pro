<?php
// admin_login.php
require_once 'config.php';

session_start();

if ($_POST) {
    $Username = $_POST['Username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT admin_id, Username, password FROM tbl_admins WHERE Username = ?");
    $stmt->bind_param("s", $Username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        // For demo - in production use password_verify()
        if ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['Username'] = $admin['Username'];
            header('Location: admin.php');
            exit;
        }
    }
    
    header('Location: login.html?error=1');
    exit;
}
?>