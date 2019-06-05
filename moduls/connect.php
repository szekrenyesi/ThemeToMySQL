<?php
function connect_db($database){
	
	$config = parse_ini_file('../conf/config.ini');

	$servername = $config['servername'];
	$username = $config['username'];
	$password = $config['password'];

	$newline = "<br>";
	$conn = mysqli_connect($servername, $username, $password, $database);

	if (!$conn) {
		die("Connection failed: " . $conn->connect_error . "${newline}");
	}
	#echo "Connected successfully" . "${newline}";
	return $conn;
}
?>
