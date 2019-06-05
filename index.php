<?php
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
ob_start();
session_start();
require_once 'dbconnect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
// select logged in users detail
$res = $conn->query("SELECT * FROM users WHERE id=" . $_SESSION['user']);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ThemeToMySQL</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css"/>
    <link rel="stylesheet" href="assets/css/index.css" type="text/css"/>
</head>
<body>

<!-- Navigation Bar-->
<?php include 'header.php'?>

<script>
	var nav = document.getElementById('navbar').getElementsByTagName('ul')[0].children;
	for (i = 0; i < nav.length; i++) {
		nav[i].setAttribute("class",'');
	} 
	document.getElementById("home").setAttribute("class",'active');
</script>


<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
        <h2>Welcome, <?php echo $userRow['username']; ?></h2>
        <div class="info">
			<p>This interface was created for importing and converting T-patterns extracted from <a href='http://patternvision.com/products/theme/'>Theme</a> into a MySQL database where the list of T-patterns are re-organized based on their global uniqueness. 
As a result, every T-pattern is represented only once even if they occur in several datafiles, and all of these different instances (detected in a particular datafile) refer to their unique structure described by the corresponding <i>patstring</i> attribute. </p>

<a href='figures/filter_patterns.jpg'><img style='width:600px;margin:20px' src='figures/filter_patterns.jpg'></a>

<p>Technically, two <a href='figures/db_modell.jpg'>relational tables</a> are created: the first contains every unique T-pattern while the second is to store their locations where they are detected. The imported projects can be explored using pre-constructed database queries. At first, unique T-patterns can be queried using various filters. 
In the resulting table, besides the usual attributes of patterns (<i>N_actors</i>, <i>N_switches</i> etc.) one can check how many physical instances a specific T-pattern has and in how many datafiles. These datafiles and the exact locations can also be queried by clicking at the respective number in the selected record:</p>

<a href='figures/query.png'><img style='width:600px;black;margin:20px' src='figures/query.png'></a>

<p>Every resulting table (or the whole project) can be exported in CSV format. However, the most beneficial feature of the interface is that T-patterns can also be converted and downloaded in EAF format using dump export or by searching and selecting specific types of T-patterns assicoated with the files that containing them,
then just clicking on the <button>Convert to EAF</button> button.
The resulting EAF annotation format  makes it possible to explore and link T-patterns to the original source media of observation using the <a href='https://tla.mpi.nl/tools/tla-tools/elan/'>ELAN</a> annotation tool.
In the EAF files, every unique T-pattern is represented as a unique annotation tier displaying the pattern's occurrences as time intervals in the timeline of the media file:</p>

<a href='figures/elan.jpg'><img style='width:800px;black;margin:20px' src='figures/elan.jpg'></a>

<p>One can also merge the exported EAF files with the original annotations (<i>Merge Transcriptions...</i> in <i>File</i> menu).
It can be usefull if some of the annotation data (e.g: speech transcriptions) was not part of the input of T-pattern detection but we would like to check what is happening on these tiers in parellel with the resulting patterns.</p>


<h2>How to import a Theme project?</h2>
<br>
<p>Please, just follow these steps:</p>

<ol>
<li>Export your project from Theme using this function:  <i>Analysis</i>-><i>Generate Statistical Tables</i>-><i>Generate Tables...</i>
<a href='figures/export1.png'><img style='width:800px;black;margin:20px' src='figures/export1.png'></a>

<li>Select All Files in Project:<br>
<a href='figures/export2.png'><img style='width:200px;black;margin:20px' src='figures/export2.png'></a>
<li>Apply these settings:<br>
<a href='figures/export3.png'><img style='width:300px;black;margin:20px' src='figures/export3.png'></a>
<li>After the exportaiton is completed, you can find two <i>.txt</i> files in the folder of your Theme project: <b>patstring_[your project name].txt</b> and <b>patdur_[your project name].txt</b>. <span style="color:red">Be carefull!</span> 
There is a bug in ThemeEdu: the <i>dataname</i> property of the first pattern is mistakenly located in the headline of the table (as columnname) which must be corrected manually by moving it to the first record and renaming the first column as "dataname":

<a href='figures/correct.png'><img style='width:800px;black;margin:20px' src='figures/correct.png'></a>

<li>Finally, upload the exported txt files <a href="https://altnyelv.unideb.hu/ThemeToMySQL/moduls/createProject.php">here</a></li>
</ol>

</div>
</div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>
