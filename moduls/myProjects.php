<?php
ob_start();
session_start();
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
require_once 'dbconnect.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
// select logged in users detail
$res = $conn->query("SELECT * FROM users WHERE id=" . $_SESSION['user']);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);
$user_id = $_SESSION['user'];

?>
<!DOCTYPE html>

<?php include 'header.php'?>

<script>
	 function seeProject(id,name,created,processed){
		document.getElementById('prid').value = id;
		document.getElementById('prname').value = name;
		document.getElementById('created').value = created;
		document.getElementById('processed').value = processed;
		document.getElementById('seeProject').submit();
	}

	var nav = document.getElementById('navbar').getElementsByTagName('ul')[0].children;
	for (i = 0; i < nav.length; i++) {
		nav[i].setAttribute("class",'');
	} 
	document.getElementById("projects").setAttribute("class",'active');
</script>

<form id="seeProject" method="GET" action="seeProject.php">
	<input type="hidden" id='prid' name="projectid" value="">
	<input type="hidden" id='prname' name="prname" value="">
	<input type="hidden" id='created' name="created" value="">
	<input type="hidden" id='processed' name="processed" value="">
</form>

<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
		<h2>My Projects</h2>
		<div class="info">
		<?php
			include 'connect.php';
			$conn = connect_db('users');
			$sql="SELECT * FROM projects WHERE userid = ${user_id}";
			if ($result = $conn->query($sql)) {
				if ($result->num_rows == 0){
					echo "<h4>You have not created any project yet!</h4>";
				}
				while ($obj = $result->fetch_object()) {
					if ($obj->processed == 1){
						$status = "ready";
					} else {
						$status = "in progress";
					}
					echo "<h4 onclick=" . '"seeProject(' . "'" . $obj->id . "','" . 
					$obj->pr_name . "','" . $obj->created . "',$obj->processed)" . 
					'"' . " class='myproject'>" . $obj->pr_name . " [ <i>$status</i> ]" .
					" <span class='created'>" . $obj->created. "</span></h4>";
				}
			}
		?>
		</div>
	</div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>

