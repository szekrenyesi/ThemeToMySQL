<?php
ob_start();
session_start();
set_time_limit(300);
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
require_once 'dbconnect.php';
include 'export_eaf.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
// select logged in users detail
$res = $conn->query("SELECT * FROM users WHERE id=" . $_SESSION['user']);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);
?>

<?php
	$user_id = $_SESSION['user'];
	$pname = $_GET['prname'];
	$zipid = $_GET['id'];
	$dir = "../data/${user_id}/projects/${pname}/eaf/";
	if (file_exists($dir)){
		$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,
			     RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
		    if ($file->isDir()){
			rmdir($file->getRealPath());
		    } else {
			unlink($file->getRealPath());
		    }
		}
		rmdir($dir);
	}
	if (!mkdir($dir, 0777, true)) {
		die("Failed to create folder: ${dir}<br>");
	}
	$project = $_GET['projectid'];
	$conditions = 'duration > 1';
	if ($_GET['patstring'] != ""){
		$stype =  $_GET['stype'];
		$expr= $_GET['patstring'];
		if (  $stype == "regexp"  ){
			$operator = "REGEXP";
			$sselect = "p.patstring $operator '$expr'";
		}
		if ( $stype == "substr" ){
			$sselect = "LOCATE('$expr',p.patstring) > 0";
		}
		if ( $stype == "exactm" ){
			$sselect = "p.patstring = '$expr'";
		}
		if ($conditions != ""){
			$conditions = $conditions . " AND $sselect";
		} else {
			$conditions = $sselect;
		}

	}
	if ($_GET['dataname'] != "" && $_GET['dataname'] != 'undefined'){
		if ($conditions != ""){
			 $conditions = $conditions . " AND dataname = '" . $_GET['dataname'] . "'";
		} else {
			$conditions =  "dataname = '" . $_GET['dataname'] . "'";
		}
	}
	if ($conditions != ""){
		$conditions = "WHERE " . $conditions;
	}
	$sql = "SELECT dataname,pattern,starttime,endtime,duration,patstring 
	FROM pattern_locations INNER JOIN unique_patterns as p
	ON p.id = pattern_locations.pattern
	$conditions
	ORDER BY dataname,pattern,starttime;";
	export_eaf($project,$dir,$sql,'no');

	$rootPath = realpath($dir);
	$filesindir = glob($dir . '/*.eaf');
	$countFile = 0;
	if ($filesindir != false){
	    $countFile = count($filesindir);
	}
	if ($countFile == 0){
		die("Error: failed to create EAF files for this project!");
	}
	
	$zip = new ZipArchive();
	if (file_exists("../data/${user_id}/projects/${pname}/$zipid.zip")){
		unlink("../data/${user_id}/projects/${pname}/$zipid.zip");
	}
	$zip->open("../data/${user_id}/projects/${pname}/$zipid.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);
	$filesToDelete = array();

	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($rootPath),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ($files as $name => $file)
	{
		if (!$file->isDir())
		{
			$filePath = $file->getRealPath();
			$relativePath = substr($filePath, strlen($rootPath) + 1);
			$zip->addFile($filePath, $relativePath);
			$filesToDelete[] = $filePath;
		}
	}

	$zip->close();

	foreach ($filesToDelete as $file)
	{
		unlink($file);
	}
	echo "../data/${user_id}/projects/${pname}/$zipid.zip";
?>

