<?php
require_once 'config/session.php';

// Redirect berdasarkan status login
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: kasir/dashboard.php');
    }
} else {
    header('Location: login.php');
}
exit();

