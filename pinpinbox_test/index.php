<?php
//echo '1<br>';
echo "<script>console.log(".json_encode("\index.php:1").");</script>";
include 'core/Load.php';
//echo '2<br>';
echo "<script>console.log(".json_encode("\index.php:2").");</script>";
new Core();
//echo '3<br>';
echo "<script>console.log(".json_encode("\index.php:3").");</script>";