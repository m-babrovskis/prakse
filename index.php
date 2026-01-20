<?php
SESSION_START();
$document_root = $_SERVER['DOCUMENT_ROOT']; //curent document root dir
include_once $document_root . "/core/include.php";

include $document_root . "/theme/view/default/index.php";
?>