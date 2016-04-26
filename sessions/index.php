<?php
//simple counter to test sessions. should increment on each page reload.
session_start();

if (isset($_SESSION['count'])) {
    $count = $_SESSION['count'];
}else{
    $count = 1;
}

echo $count;

$_SESSION['count'] = ++$count;