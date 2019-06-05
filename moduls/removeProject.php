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
</form>

<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
		<h2>Removing project: <?php echo $_POST['prname'];?></h2>
		<div class='console'>
			<?php
				include 'connect.php';
                                $user_id = $_SESSION['user'];
                                $pname = $_POST['prname'];
                                $project = $_POST['projectid'];
                                $conn = connect_db("users");
				$dir = "../data/${user_id}/projects/${pname}";
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
				$sql = "DROP DATABASE $project";
				$result=mysqli_query($conn,$sql);
				if ($result){
					echo "Database was succesfully dropped.<br>";
				} else {
					echo "Error: cannot drop database.<br>";
				}
				$sql = "DELETE FROM projects WHERE id = '${project}'";
				$result=mysqli_query($conn,$sql);
				if ($result){
                                        echo "Project was succesfully removed.<br>";
                                } else {
                                        echo "Error: cannot remove project.<br>";
                                }
			?>	
		</div>
     </div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>


