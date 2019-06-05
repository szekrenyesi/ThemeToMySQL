<?php


function create_project($dbname){
	
	$newline = "<br>";

	$config = parse_ini_file('../conf/config.ini');

        $servername = $config['servername'];
        $username = $config['username'];
        $password = $config['password'];

	$conn = mysqli_connect($servername, $username, $password);

        // Check connection
        if (!$conn) {
                die("Connection failed: " . $conn->connect_error . "${newline}");
        }
        echo "Connected successfully${newline}";

	$sql = "DROP DATABASE IF EXISTS ${dbname};";

	if (mysqli_query($conn, $sql)) {
		echo "Database droped successfully${newline}";
		ob_end_flush();
		flush();
	} else {
		echo "Error dropping database: " . mysqli_error($conn) . "${newline}";
	}

	$sql = "CREATE DATABASE ${dbname};";


	if (mysqli_query($conn, $sql)) {
		echo "Database created successfully${newline}";		
		flush();
	} else {
		echo "Error creating database: " . mysqli_error($conn) . "${newline}";
	}

	$sql = "USE ${dbname}";

	if (mysqli_query($conn, $sql)) {
		echo "Database selected${newline}";
	} else {
		echo "Error selecting database: " . mysqli_error($conn) . "${newline}";
	}

	$sql = "CREATE TABLE unique_patterns (
	id	INT PRIMARY KEY NOT NULL,
	Length	INT NOT NULL,
	Level INT NOT NULL,
	N_actors INT NOT NULL,
	N_switches	INT NOT NULL,
	patstring TEXT
	);";

	if (mysqli_query($conn, $sql)) {
		echo "Table 'unique patterns' is successfuly created${newline}";
		flush();
	} else {
		echo "Error creating table: " . mysqli_error($conn) . "${newline}";
	}


	$sql = "CREATE TABLE pattern_locations (
	id INT PRIMARY KEY,
	dataname VARCHAR(50) NOT NULL,
	pattern	INT NOT NULL,
	sample INT NOT NULL,
	starttime INT NOT NULL,
	endtime	INT NOT NULL,
	duration INT NOT NULL,
	FOREIGN KEY (pattern) references unique_patterns(id)
	);";

	if (mysqli_query($conn, $sql)) {
		echo "Table 'pattern_locations' is successfuly created${newline}";
		flush();
	} else {
		echo "Error creating table: " . mysqli_error($conn) . "${newline}";
	}
}
?>
