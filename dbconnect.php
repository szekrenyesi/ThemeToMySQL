<?php

$config = parse_ini_file('conf/config.ini'); 

$db_host = $config['servername'];
$db_user = $config['username'];
$db_pass = $config['password'];
$db_name = $config['dbname'];

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

