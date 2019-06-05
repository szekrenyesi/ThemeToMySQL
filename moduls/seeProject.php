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
$projectid = $_GET['projectid'];
$prname = $_GET['prname'];
$created = $_GET['created'];
$processed = $_GET['processed'];
include 'connect.php';
?>
<!DOCTYPE html>

<?php include 'header.php'?>

<script>
	function makeAction(action){
		document.getElementById('action').action = action;
		document.getElementById('action').submit();
	}

	var nav = document.getElementById('navbar').getElementsByTagName('ul')[0].children;
	for (i = 0; i < nav.length; i++) {
		nav[i].setAttribute("class",'');
	} 
	document.getElementById("projects").setAttribute("class",'active');
</script>

<form id="action" method="POST" action="">
	<input type="hidden" id='prid' name="projectid" value="<?php echo $projectid;?>">
	<input type="hidden" id='prname' name="prname" value="<?php echo $prname;?>">
	<input type="hidden" id='purl' name='purl' value="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"?>">
</form>

<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
		<h2><?php echo $prname;?></h2>
		<?php if ($processed == 1): ?>
		<div class='info'>
			<h3><i>Project Info:</i></h3>
			<table class="project">
				<tr>
					<td>Project name:</td><td><?php echo $prname;?></td>
				</tr>
				<tr>
					<td>Created on:</td><td><?php echo $created;?></td>
				</tr>
				<?php
					$conn = connect_db($projectid);
					$sql="SELECT COUNT(*) as uc FROM unique_patterns";
					if ($result = $conn->query($sql)) {
						$row = mysqli_fetch_assoc($result);
						echo "<tr><td>Number of unique patterns:</td><td>" . $row['uc'] . "</td></tr>";
					}
					$sql="SELECT COUNT(DISTINCT dataname) as dm FROM pattern_locations";
					if ($result = $conn->query($sql)) {
						$row = mysqli_fetch_assoc($result);
						echo "<tr><td>Number of datafiles:</td><td>" . $row['dm'] . "</td></tr>";
					}
					$sql="SELECT COUNT(*) as pl FROM pattern_locations";
					if ($result = $conn->query($sql)) {
						$row = mysqli_fetch_assoc($result);
						echo "<tr><td>Number of pattern locations:</td><td>" . $row['pl'] . "</td></tr>";
					}
				?>
				</tr>
			</table>
		</div>
		<script>
			function showSure(){	
				var sure = document.getElementById('sure');
				if (sure.style.display == 'none'){
					alert('Be careful! The project will cannot be restored!');
					sure.style.display = '';
				} else {
					
					sure.style.display = 'none';
				}
			}
		</script>
		<div class='info'>
			<h3><i>Actions:</i></h3>
			<h4 class='action' onclick="makeAction('exportCSV.php')">Export patterns in CSV</h4>
			<h4 class='action' onclick="makeAction('exportEAF.php')">Export patterns in EAF</h4>
			<h4 class='action' onclick="makeAction('dbQuery.php')">Search in database</h4>
			<h4 class='action' onclick="showSure()">Remove project</h4>
			<h4 class='action' id="sure" style='display:none'>Are you sure?
			<button onclick="makeAction('removeProject.php')">yes</button> or <button onclick="showSure()">no</button>
			</h4>
		</div>
		<?php else: ?>
			<div class='info' id='log'>
				<div class='action'>
				<h4>Step1</h4>
				<h5>Processing patstr.txt: <progress id='step0' value="0" max="100"></progress></h5>
				</div>
				<div class='action'>
				<h4>Step2</h4>
                                <h5>Reindexing patterns:  <progress id='step1' value="0" max="100"></progress></h5>
				</div>
				<div class='action'>
				<h4>Step3</h5>
                                <h5>Processing patdur.txt:  <progress id='step2' value="0" max="100"></progress></h5>
				</div>
                                <div class='action'>
				<h4>Step4</h4>
                                <h5>Searching related patstring:  <progress id='step3' value="0" max="100"></progress></h5>
				</div>
                                <div class='action'>
				<h4>Step5</h4>
                                <h5>Creating unique patterns:  <progress id='step4' value="0" max="100"></progress></h5>
				</div>
                                <div class='action'>
				<h4>Step6</h4>
                                <h5>Inserting unique patterns:  <progress id='step5' value="0" max="100"></progress></h5>
				</div>
                                <div class='action'>
				<h4>Step7</h4>
                                <h5>Inserting pattern locations:  <progress id='step6' value="0" max="100"></progress></h5>
				</div>
			</div>
			<script>
			    var previous = null;
			    var whendone = '<?php echo "seeProject.php?projectid=$projectid&prname=$prname&created=$created&processed=1"?>';
			    var jsonpath = '<?php echo "../data/$user_id/projects/$prname/" ?>/progress.json';
			    var current = null;
			     $.getJSON(jsonpath, function(json) {
				for (i in json.step) {
					var step = json.step[i];
					document.getElementById('step' + i).value = step.current;
				} 
			    });	
			    setInterval(function() {
				$.getJSON(jsonpath, function(json) {
				    current = JSON.stringify(json);            
				    if (previous && current && previous !== current) {
					for (i in json.step) {
  						var step = json.step[i];
						document.getElementById('step' + i).value = step.current;
						if (i == 6){
							if (step.current == 100){
								window.location.href = whendone;
							}
						}
					} 
					
				    }
				    previous = current;
				});                       
			    }, 1000); 
			</script>
		<?php endif; ?>
	</div>

</div>


</body>
</html>


