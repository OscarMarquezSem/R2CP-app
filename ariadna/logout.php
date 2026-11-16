<?php
declare(strict_types=1);
require __DIR__ . '/inc/app.php';
start_session();
$_SESSION = [];
session_destroy();
redirect('login.php');
