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
	document.getElementById("projects").setAttribute("class",'active');
</script>


<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
		<h2>Exporting data (CSV)</h2>
		<div class="info">
		<?php if (ISSET($_POST['conditions'])):?>
			<h3><i>Console log:</i></h3>
			<div class="consolelog">
			<?php
				include 'connect.php';
				echo "Querying data...<br>";
				ob_end_flush();
                                flush();
				$user_id = $_SESSION['user'];
				$pname = $_POST['prname'];
				$project = $_POST['projectid'];
				$conn = connect_db("${project}");
				$conditions = '';
				$conditions2 = '';
				if ($_POST['plength'] != ""){
                                        $plength = $_POST['plength'];
                                        $conditions = "p.length = $plength";
                                }
				if ($_POST['plevel'] != ""){
                                        if ($conditions != ""){
                                                $conditions = $conditions . " AND p.level = " . $_POST['plevel'];
                                        } else {
                                                $conditions = "p.level = " . $_POST['plevel'];
                                        }
                                }
                                if ($_POST['nactors'] != ""){
                                        if ($conditions != ""){
                                                $conditions = $conditions . " AND p.n_actors = " . $_POST['nactors'];
                                        } else {
                                                $conditions = "p.n_actors = " . $_POST['nactors'];
                                        }

                                }
                                if ($_POST['nswitches'] != ""){
                                        if ($conditions != ""){
                                                $conditions = $conditions . " AND p.level = " . $_POST['nswitches'];
                                        } else {
                                                $conditions = "p.n_switches = " . $_POST['nswitches'];
                                        }

				}
				if ($_POST['patstring'] != ""){
                                        $stype =  $_POST['stype'];
                                        $expr= $_POST['patstring'];
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

                                if ($_POST['minfile'] != ""){
                                        if ($conditions != ""){
                                                $conditions2 = $conditions . " AND fnum >= " . $_POST['minfile'];
                                        } else {
                                                $conditions2 = "fnum >= " . $_POST['minfile'];
                                        }
                                }
                                if ($_POST['minocc'] != ""){
                                         if ($conditions2 != ""){
                                                 $conditions = $conditions2 . " AND tnum >= " . $_POST['minocc'];
                                        } else {
						if ($conditions != ""){
                                                         $conditions2 = $conditions . " AND tnum >= " . $_POST['minocc'];
                                                } else {
                                                	$conditions2 = "tnum >= " . $_POST['minocc'];
						}
                                        }

                                }
                                if ($conditions != ""){
                                        $conditions = "WHERE " . $conditions;
                                }
				if ($conditions2 != ""){
					$conditions2 = "WHERE " . $conditions2;
				}
				else {
					$conditions2 = $conditions;
				}

				$fh = fopen("../data/${user_id}/projects/${pname}/unique_patterns.csv", 'w+') or die("can't open file3");
				$preambulum = 'id' . "\t" .  'length' . "\t" . 'Level' . "\t" . 
				"N_actors" . "\t" . "N_switches" . "\t" . "patstring" . "\t" . "N_datafiles" . "\t" . "N_total" . "\n";
				fwrite($fh, $preambulum);
				$sql = "SELECT p.id as id,p.length as length,p.Level as Level,p.N_actors as N_actors,
				p.N_switches as N_switches,p.patstring as patstring,fnum,tnum
				FROM unique_patterns AS p
				INNER JOIN
				(SELECT unique_patterns.id,COUNT(DISTINCT dataname) AS fnum FROM pattern_locations 
				INNER JOIN unique_patterns
				ON pattern_locations.pattern = unique_patterns.id
				GROUP BY unique_patterns.id) AS a
				ON a.id = p.id
				INNER JOIN
				(SELECT unique_patterns.id,COUNT(dataname) AS tnum FROM pattern_locations INNER JOIN unique_patterns
				ON pattern_locations.pattern = unique_patterns.id
				GROUP BY unique_patterns.id) AS b
				ON b.id = p.id $conditions2 ORDER BY " . $_POST['orderby'] . " " . $_POST['d'] . ";";
				echo "Exporting unique patterns...<br>";
				ob_end_flush();
				flush();
				$fh = fopen("../data/${user_id}/projects/${pname}/unique_patterns.csv", 'a+') or die("can't open file3");
				if ($result=mysqli_query($conn,$sql)){
					while ($obj=mysqli_fetch_object($result)){
						$line = $obj->id . "\t" .  $obj->length . "\t" . $obj->Level . "\t" . $obj->N_actors . "\t" . 
						$obj->N_switches . "\t" . $obj->patstring . "\t" . $obj->fnum . "\t" . $obj->tnum . "\n";
						fwrite($fh, $line);
					}
				}
				fclose($fh);
				$fh = fopen("../data/${user_id}/projects/${pname}/pattern_locations.csv", 'w+') or die("can't open file3");
				$preambulum = 'id' . "\t" .  'dataname' . "\t" . 'patternid' . "\t" . "sample" . "\t" . 
				"starttime" . "\t" . "endtime" . "\t" . "duration" . "\n";
				fwrite($fh, $preambulum);
				$sql = "SELECT l.id as id, l.dataname as dataname, sample, patstring as pattern, starttime, 
				endtime, duration FROM pattern_locations as l 
				INNER JOIN unique_patterns as p ON l.pattern = p.id $conditions";
				echo "Exporting pattern locations...<br>";
				flush();
				$fh = fopen("../data/${user_id}/projects/${pname}/pattern_locations.csv", 'a+') or die("can't open file3");
				if ($result=mysqli_query($conn,$sql)){
					while ($obj=mysqli_fetch_object($result)){
						$line = $obj->id . "\t" .  $obj->dataname . "\t" . $obj->pattern . "\t" . 
						$obj->sample . "\t" . $obj->starttime . "\t" . $obj->endtime . "\t" . 
						$obj->duration . "\n";
						fwrite($fh, $line);
					}
				}
				fclose($fh);
				echo "All done!<br><br>";
				echo "</div>";
				echo "<h3><i>Results:</i><h3>";
				echo "<h4 class='download'><a href='../data/${user_id}/projects/${pname}/unique_patterns.csv'>
				unique_patterns.csv</a></h4>";
				echo "<h4 class='download'><a href='../data/${user_id}/projects/${pname}/pattern_locations.csv'>
				pattern_locations.csv</a></h4>";
				echo "<br><a class='btn btn-primary btn-lg' href='" . $_POST['purl'] . "'>
                                Back to project</a>";

				
			?>
			</div>
	<?php else:?>
		<script>
			function showFilt(){
				document.getElementById('filter').style.display = 'none';
				document.getElementById('filters').style.display = '';
			}
		</script>
		<form form id="myForm" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
			<button id="filter" type="button" onclick="showFilt()">Filter patterns &#x21f2;</button><br>
                        <table id="filters" style="display:none" class='create'>
                        <tr><td>String in pattern:</td>
                        <td><input name='patstring' type="text"><br>
                        <select id='stype' name='stype'>
                                <option value='substr'>subtring match</option>
                                <option value='exactm'>exact match</option>
                                <option value='regexp'>reular expression</option>
                        </select></td>
                        <tr><td>Length:</td><td><input name='plength' type=number></td></tr>
                        <tr><td>Level:</td><td><input name='plevel' type=number></td></tr>
                        <tr><td>N_actors:</td><td><input name='nactors' type=number></td></tr>
                        <tr><td>N_switches:</td><td><input name='nswitches' type=number></td></tr>
                        <tr id='fnum'><td>Min. number of files:</td><td><input name='minfile' type=number></td></tr>
                        <tr id='tnum'><td>Min. Occurence:</td><td><input name='minocc' type=number></td></tr>
                        <tr id='orderby'><td>Order by</td><td>
                                <select name="orderby">
                                        <option value='id'>ID</option>
                                        <option value='length'>Length</option>
                                        <option value='n_actors'>N_actors</option>
                                        <option value='n_switches'>N_switches</option>
                                        <option value='number_of_files'>Number_of_files</option>
                                        <option value='total_number'>Total_number</option>
                                </select>
                                <select name="d">
                                        <option value=''>ASC</option>
                                        <option value='DESC'>DESC</option>
                                </select>

                        </td></tr>
                        </table>
                        <br>
                        <input type='hidden' name='conditions' value='true'>
                        <input type="hidden" id='prid' name="projectid" value="<?php echo $_POST['projectid'];?>">
                        <input type="hidden" id='prname' name="prname" value="<?php echo $_POST['prname'];?>">
			<input type="hidden" id='purl' name="purl" value="<?php echo $_POST['purl'];?>">
                        <input class="btn btn-primary btn-lg" type="submit" value="Start export">
                </form>

	<?php endif;?>
	</div>
</div>

</div>

</body>
</html>
