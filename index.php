<?php
SESSION_START();
$document_root = $_SERVER['DOCUMENT_ROOT']; //curent document root dir
include_once $document_root . "/core/include.php";

$router = new Router();
$router->dispatch();
?>