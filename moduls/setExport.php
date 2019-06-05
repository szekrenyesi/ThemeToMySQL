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
		<h2>Exporting patterns</h2>
		<div class='info'>
		<h3><i>Console log:</i></h3>
			<div class="consolelog">
			<?php
				$user_id = $_SESSION['user'];
				$pname = $_POST['prname'];
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
				$project = $_POST['projectid'];
				echo "Start exporting...<br>";
				ob_end_flush();
				flush();
				export_eaf($project,$dir);
				echo "Archiving files...<br>";
				flush();
				
				$rootPath = realpath($dir);

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
			?>
			</div>
	</div>
</div>

</div>

</body>
</html>
