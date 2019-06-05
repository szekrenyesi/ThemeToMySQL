<?php
ob_start();
session_start();
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
<!DOCTYPE html>

<?php include 'header.php'?>

<script>
	var nav = document.getElementById('navbar').getElementsByTagName('ul')[0].children;
	for (i = 0; i < nav.length; i++) {
		nav[i].setAttribute("class",'');
	}
	document.getElementById("projects").setAttribute("class",'active');
</script>


<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
		<h2>Exporting patterns (EAF)</h2>
		<div class='info'>
		<?php if(isset($_GET['conditions'])):?>
		<h3><i>Console log:</i></h3>
			<div class="consolelog">
			<?php
				echo "Start exporting...<br>";
                                ob_end_flush();
                                flush();
				$user_id = $_SESSION['user'];
				$pname = $_GET['prname'];
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
				ob_end_flush();
				flush();
				$conditions = 'duration > 1';
				if (isset($_GET['plength']) ){
					if ($_GET['plength'] != ""){
						$plength = $_GET['plength'];
						$conditions = $conditions . " AND p.length = $plength";
					}
					if ($_GET['plevel'] != ""){
						if ($conditions != ""){
							$conditions = $conditions . " AND p.level = " . $_GET['plevel'];
						} else {
							$conditions = "p.level = " . $_GET['plevel'];
						}
					}
					if ($_GET['nactors'] != ""){
						if ($conditions != ""){
							$conditions = $conditions . " AND p.n_actors = " . $_GET['nactors'];
						} else {
							$conditions = "p.n_actors = " . $_GET['nactors'];
						}

					}
					if ($_GET['nswitches'] != ""){
						if ($conditions != ""){
							$conditions = $conditions . " AND p.level = " . $_GET['nswitches'];
						} else {
							$conditions = "p.n_switches = " . $_GET['nswitches'];
						}

					}
				}
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
				#echo $sql;
				export_eaf($project,$dir,$sql,'yes');

				$rootPath = realpath($dir);
                                $filesindir = glob($dir . '/*.eaf');
                                $countFile = 0;
                                if ($filesindir != false){
                                    $countFile = count($filesindir);
                                }
                                if ($countFile == 0){
                                        die("Error: failed to create EAF files for this project!");
                                }

				echo "Archiving files...<br>";
				flush();
				
				$zip = new ZipArchive();
				$zip->open("../data/${user_id}/projects/${pname}/eaf/pattern_in_eaf.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);
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
				echo "All done! <br><br>";
				echo "</div>";
				echo "<h3><i>Results:</i></h3>";
				echo "<h4 class='download'>
				<a href='../data/${user_id}/projects/${pname}/eaf/pattern_in_eaf.zip'>pattern_in_eaf.zip</a></h4>";
				echo "<br><a class='btn btn-primary btn-lg' href='" . $_GET['purl'] . "'>
				Back to project</a>";
			?>
			</div>
	</div>
	<?php else:?>
		<script>
                        function showFilt(){
                                document.getElementById('filter').style.display = 'none';
                                document.getElementById('filters').style.display = '';
                        }
                </script>
		<form form id="myForm" method="GET" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
			<button id="filter" type="button" onclick="showFilt()">Filter patterns &#x21f2;</button><br>
                        <table style="display:none" id="filters" class='create'>
                        <tr><td>String in pattern:</td>
			<td><input name='patstring' type="text"><br>
			<select id='stype' name='stype'>
                                <option value='substr'>substring match</option>
                                <option value='exactm'>exact match</option>
                                <option value='regexp'>regular expression</option>
                        </select></td>
                        <tr><td>Length:</td><td><input name='plength' type=number></td></tr>
                        <tr><td>Level:</td><td><input name='plevel' type=number></td></tr>
                        <tr><td>N_actors:</td><td><input name='nactors' type=number></td></tr>
                        <tr><td>N_switches:</td><td><input name='nswitches' type=number></td></tr>
                        </table>
                        <br>
                        <input type='hidden' name='conditions' value='true'>
                        <input type="hidden" id='prid' name="projectid" value="<?php echo $_POST['projectid'];?>">
			<input type="hidden" id='prname' name="prname" value="<?php echo $_POST['prname'];?>">
			<input type="hidden" id="dataname" name="dataname" value="">
			<input type="hidden" id='purl' name="purl" value="<?php echo $_POST['purl'];?>">
                        <input class="btn btn-primary btn-lg" type="submit" value="Start export">
                </form>

        <?php endif;?>
</div>

</div>

</body>
</html>
