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
	var nav = document.getElementById('navbar').getElementsByTagName('ul')[0].children;
	for (i = 0; i < nav.length; i++) {
		nav[i].setAttribute("class",'');
	} 
	document.getElementById("create").setAttribute("class",'active');
</script>


<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
		<?php if (isset($_POST['pname'])): ?>
			<h2>Creating project... Please wait!</h2>
	                <div class="console">
                        <?php
                                include 'connect.php';
                                include 'create_db.php';
				include 'upload.php';
				$config = parse_ini_file('../conf/config.ini');
				$maxp = $config['max_project'];
                                $user_id = $_SESSION['user'];
                                $pname = $_POST['pname'];
                                $conn = connect_db('users');
                                $sql = "SELECT COUNT(*) as num FROM projects WHERE userid = " . $user_id . ";";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                if ($row['num'] >= $maxp){
                                        die("Error: you have the maximum number ($maxp) of projects. Please, delete one of your projects!<br>");
                                }
                                $sql = "SELECT * FROM projects WHERE pr_name = '${pname}' AND userid = " . $user_id . ";";
                                $result = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($result) != 0) {
                                        mysqli_close($conn);
                                        die("Error: Project <b>${pname}</b> already exist.<br>");
                                }
                                else {
                                        echo "Initializing project...<br>";
                                        ob_end_flush();
                                        flush();
					$dir = "../data/${user_id}/projects/${pname}/";
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
                                                die("Failed to create folder: ${structure}<br>");
                                        }
					$uploadOk = uploadFile($dir,'patstr');
					if ($uploadOk != 1){
							die("Failed to upload patstr!<br>");
					}
					$uploadOk = uploadFile($dir,'patdur');
					if ($uploadOk != 1){
							die("Failed to upload patdur!<br>");
					}
                                        $prid = $_SESSION['user'] . "_" . $pname;
					$created = date("Y-m-d H:i:s");
                                        $sql = "INSERT INTO projects VALUES ('${prid}','${pname}',". $user_id . ",'${created}',0);";
                                        $result = mysqli_query($conn, $sql);
                                        if (!$result){
                                                        die("Failed to initialize project!<br>");
                                        }
                                }
                                create_project($prid);

                                $patstrpath = $dir . "patstr.txt";
                                $patdurpath = $dir . "patdur.txt";

                                $cmd = "php process_patterns.php $patstrpath $patdurpath $user_id $pname";
				$logfile = "../data/$user_id/projects/$pname/log.txt";
				$pidfile = "../data/$user_id/projects/$pname/pid";
				exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $logfile, $pidfile));
				echo "<h2><a href='seeProject.php?projectid=$prid&prname=$pname&created=$created&processed=0'>
				<button class='btn btn-primary btn-lg'>Check Progess</button></a></h2>";
                        ?>
		        </div>

		<?php else: ?>
		<?php
			include 'connect.php';
			$maxp = $config['max_project'];
			$user_id = $_SESSION['user'];
			$max_upload = $config['max_file_size'];
			$maxup = $max_upload / 1000000;
                        $conn = connect_db('users');
                        $sql = "SELECT COUNT(*) as num FROM projects WHERE userid = " . $user_id . ";";
                        $result = mysqli_query($conn, $sql);
                        $row = mysqli_fetch_assoc($result);
                        if ($row['num'] >= $maxp){
                        	echo "<p style='color:red'>Warning: you already have the maximum number ($maxp) of projects!</p>";
                       }
		?>
		<h2>Import project</h2>
		<form id="myForm" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" enctype="multipart/form-data">
			<input type="hidden" value="myForm" name="<?php echo ini_get("session.upload_progress.name"); ?>">
			<table class="create" style="boder: 1px solid black">
				<tr>
				<td width="30%">Project name: </td>
				<td><input size="50" type=text name="pname" pattern="[a-zA-Z0-9]+" required></td>
				</tr>
				<tr>
				<td colspan=>Upload patdur.txt here: <br>
				<span style='color:silver;font-style:italic'> Maximum <?php echo $maxup?> Mb is allowed</span></td>
				<td><input type="file" name="patdur" id="patdur" required></td>
				</tr>
				<tr>
				<td colspan=>Upload patstr.txt here: <br>
				<span style='color:silver;font-style:italic'> Maximum <?php echo $maxup?> Mb is allowed</span></td>
				<td><input type="file" name="patstr" id="patstr" required></td>
				</tr>
				<!--<tr><td colspan=2 style="text-align:;color:red">File size limit: 1 Mb</td></tr>-->
				
			</table>
			<br>
			<input class="btn btn-primary btn-lg" type="submit" value="Start import">
		</form>
		<?php endif; ?>
		<h4 id='uptext' style="display:none">Uploading process:</h4>
		 <div id="bar_blank">
                   <div id="bar_color"></div>
                </div>
                <div id="status"></div>
		<iframe id="hidden_iframe" name="hidden_iframe" src="about:blank"></iframe>
		<script type="text/javascript" src="upload_prog.js"></script>
	</div>

</div>

</body>
</html>
