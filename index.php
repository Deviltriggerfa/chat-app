<?php
require_once 'auth.php';

if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin.php");
    } elseif (is_specialist()) {
        header("Location: specialist.php");
    } else {
        header("Location: user.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>