<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: /classroom/views/auth/login.php");
        exit();
    }
}

function checkAdmin() {
    checkLogin();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        header("Location: /classroom/index.php");
        exit();
    }
}