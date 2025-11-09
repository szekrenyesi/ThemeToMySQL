
<?php

function uploadFile($location,$fileToUpload){

	$config = parse_ini_file('../conf/config.ini');
	$max_upload = $config['max_file_size'];

	$newline = "<br>";
	$target_dir = $location;
	$target_file = $target_dir . $fileToUpload . '.txt';
	$uploadOk = 1;
	$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

	// Check if file already exists
	if (file_exists($target_file)) {
		echo "Sorry, file already exists.${newline}";
		$uploadOk = 0;
	}
	// Check file size
	if ($_FILES["${fileToUpload}"]["size"] > $max_upload) {
		$mbfile_size = $max_upload / 1000000;
		echo "Sorry, your file is too large. Maximum $mbfile_size Mb is allowed. ${newline}";
		$uploadOk = 0;
	}
	// Allow certain file formats
	if($fileType != "txt") {
		echo "Sorry, only txt files are allowed.${newline}";
		$uploadOk = 0;
	}
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		echo "Sorry, your file was not uploaded.${newline}";
	// if everything is ok, try to upload file
	} else {
		if (move_uploaded_file($_FILES["${fileToUpload}"]["tmp_name"], $target_file)) {
			echo "The file ". basename( $_FILES["${fileToUpload}"]["name"]). " has been uploaded.${newline}";
		} else {
			echo "Sorry, there was an error uploading your file.${newline}";
		}
	}
	return $uploadOk;
}
?>
