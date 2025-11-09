<?php

require_once("../moduls/validate.php"); // include validator

function uploadFile($location, $fileToUpload){

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

    // Allow only txt files
    if($fileType != "txt") {
        echo "Sorry, only txt files are allowed.${newline}";
        $uploadOk = 0;
    }

    // If upload is blocked
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.${newline}";
        return 0;
    }

    // Try to upload file
    if (!move_uploaded_file($_FILES["${fileToUpload}"]["tmp_name"], $target_file)) {
        echo "Sorry, there was an error uploading your file.${newline}";
        return 0;
    }

    echo "The file ". basename($_FILES["${fileToUpload}"]["name"]) ." has been uploaded.${newline}";

    // ---------------- VALIDATION STEP -----------------
    // Infer file type for validator
    if ($fileToUpload === "patstr") {
        $filetype = "patstring";
    } elseif ($fileToUpload === "patdur") {
        $filetype = "patdur";
    } else {
        $filetype = "unknown";
    }

    // Extract userid and project name from $location
    $parts = explode('/', trim($location, '/'));
    $userid = $parts[count($parts) - 3];  // ../data/<userid>/projects/<pname>/
    $pname  = $parts[count($parts) - 1];
    if ($pname === "") $pname = $parts[count($parts) - 2];

    if ($filetype !== "unknown") {
        $log_path = "../data/$userid/projects/$pname/validation.log";
        $result = validate_file_with_logging($target_file, $filetype, $log_path);

        // Show warnings
        foreach ($result['warnings'] as $warning) {
            echo "<span style='color:orange;'>Warning:</span> $warning${newline}";
        }

        // Stop upload if errors found
        if (!empty($result['errors'])) {
            echo "<span style='color:red; font-weight:bold;'>Validation Failed:</span>${newline}";
            foreach ($result['errors'] as $error) {
                echo "<span style='color:red;'>- $error</span>${newline}";
            }
            echo "<br><b>Please fix the file and re-upload.</b>${newline}";

            // Remove the uploaded file to prevent invalid files staying on server
            if (file_exists($target_file)) {
                unlink($target_file);
            }

            return 0; // indicate failure
        }

        echo "<span style='color:green;'><b>Validation Passed.</b></span>${newline}";
    }

    return 1; // success
}
?>

