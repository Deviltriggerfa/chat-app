<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_specialist() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'specialist';
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

function require_admin() {
    if (!is_admin()) {
        header("Location: index.php");
        exit();
    }
}

function require_specialist() {
    if (!is_specialist()) {
        header("Location: index.php");
        exit();
    }
}
?>