<?php
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
			<p>This interface was created for importing T-patterns into a MySQL database. After the database is created, you can group those patterns (with their locations) 
			which are unique to your whole project. One can also download pattern locations in EAF format which makes possible to explore and link patterns
			to media files using the sofware environment of ELAN annotation tool.</p>
			<p>For creating a new project, all you need is to export and upload <i>patstr.txt</i> and <i>patdur.txt</i> files <a href="createForm.php">here.</a></p>
        </div>
    </div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>
