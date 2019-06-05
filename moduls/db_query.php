<?php
ob_start();
session_start();
set_time_limit(300);
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
<div class="consolelog">
	<form id="newsearch" method="POST" action="dbQuery.php">
		<input type="hidden" id='prid' name="projectid" value="<?php echo $_GET['projectid'];?>">
		<input type="hidden" id='prname' name="prname" value="<?php echo $_GET['prname'];?>">
        	<input style="text-align:center" class="btn btn-primary btn-lg" type="submit" value="New search">
	</form>
	<?php
		include 'connect.php';
		$user_id = $_SESSION['user'];
		$pname = $_GET['prname'];
		$purl = $_GET['purl'];
		$project = $_GET['projectid'];
		$conditions = "";
		if ($_GET['plength'] != "" && $_GET['plength'] != 'undefined'){
			$plength = $_GET['plength'];
			$conditions = "p.length = $plength";
		}
		if ($_GET['plevel'] != "" && $_GET['plevel'] != 'undefined' ){
			if ($conditions != ""){
				$conditions = $conditions . " AND p.level = " . $_GET['plevel'];
			} else {
				$conditions = "p.level = " . $_GET['plevel'];
			}
		}
		if ($_GET['nactors'] != ""  && $_GET['nactors'] != 'undefined' ){
			if ($conditions != ""){
				$conditions = $conditions . " AND p.n_actors = " . $_GET['nactors'];
			} else {
				$conditions = "p.n_actors = " . $_GET['nactors'];
			}

		}
		if ($_GET['nswitches'] != ""  && $_GET['nswitches'] != 'undefined' ){
			if ($conditions != ""){
				$conditions = $conditions . " AND p.level = " . $_GET['nswitches'];
			} else {
				$conditions = "p.n_switches = " . $_GET['nswitches'];
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
		if ($_GET['minfile'] != ""  && $_GET['minfile'] != 'undefined' ){
			if ($conditions != ""){
				$conditions = $conditions . " AND fnum >= " . $_GET['minfile'];	
			} else {
				$conditions = "fnum >= " . $_GET['minfile'];
			}
		}
		if ($_GET['minocc'] != ""  && $_GET['minocc'] != 'undefined' ){
			if ($conditions != ""){
				 $conditions = $conditions . " AND tnum >= " . $_GET['minocc'];
			} else {
				$conditions = "tnum >= " . $_GET['minocc'];
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
		$conn = connect_db($project);
		if ($_GET['target'] == "patterns"){
			$sql = "SELECT p.id as id,p.length as length,p.Level as Level,p.N_actors as N_actors,
			p.N_switches as N_switches,p.patstring as patstring,fnum as datafiles,tnum as 
			instances
			FROM unique_patterns AS p
			INNER JOIN
			(SELECT unique_patterns.id,COUNT(DISTINCT dataname) AS fnum FROM pattern_locations 
			INNER JOIN unique_patterns
			ON pattern_locations.pattern = unique_patterns.id
			GROUP BY unique_patterns.id) AS a
			ON a.id = p.id
			INNER JOIN
			(SELECT unique_patterns.id,COUNT(dataname) AS tnum FROM pattern_locations 
			INNER JOIN unique_patterns
			ON pattern_locations.pattern = unique_patterns.id
			GROUP BY unique_patterns.id) AS b
			ON b.id = p.id $conditions ORDER BY " . $_GET['orderby'] . " " . $_GET['d'] . ";";
		}
		else {
			if ($_GET['setloc'] == 'all'){
				$sql = "SELECT l.dataname as dataname,p.patstring as patstring,starttime,endtime,duration
				FROM pattern_locations as l INNER JOIN unique_patterns as p
				ON l.pattern = p.id $conditions;";
			}
			if ($_GET['setloc'] == 'datafiles'){
				$sql = "SELECT l.dataname as dataname,p.patstring as patstring,COUNT(patstring) as instances
					FROM pattern_locations as l INNER JOIN unique_patterns as p
					ON l.pattern = p.id $conditions GROUP BY patstring,dataname ORDER BY dataname;";
			}
		}

		ob_end_flush();
		flush();
		$result = mysqli_query($conn,$sql);
		if (!$result){
			echo "<h3 style='color:red'>MySQL Error: " . mysqli_error($conn) . "</h3>";
		}
		else {
			$fh = fopen("../data/${user_id}/projects/${pname}/query_results.csv", 'w+') or die("can't open file");
			$pre = "";
			if ($_GET['target'] != 'patterns' ){
                                $csvname = "pattern_locations_" . $_GET['setloc'] . ".csv";
                        } else {
                                $csvname = 'patterns.csv';
                        }
			echo "<span style='float:right'><a id='csvlink' 
				href='../data/${user_id}/projects/${pname}/$csvname'>Download results as CSV</a></span><br>";
			if ($_GET['back'] != 'undefined'){
				$backto = $_GET['back'];
				echo "<br><span style='float:right'><a id='backto'" .
					'href="javascript:replaceRes(' . "'$backto'" . ');"><< Back to ' . $backto . '</a></span>';
			}
			echo "<table class='sqlres' border=1>";
			echo "<caption>Number of results: " . mysqli_num_rows($result) . "</caption>";
			echo "<tr>";
			$result = mysqli_query($conn,$sql);
			while ($meta =  mysqli_fetch_field($result)) {
				echo "<th>$meta->name</th>";
				$pre = $pre . $meta->name . "\t";
			}
			if ($_GET['target'] == "locations" && $_GET['setloc'] == 'datafiles'){
				echo "<th style='width:120px'>Action</th>";
			}
			if ($_GET['target'] == "patterns"){
				echo "<th style='width:120px'>Action</th>";
			}
			echo "</tr>";
			fwrite($fh,$pre . "\n" );
			fclose($fh);
			$data = "";
			$fh = fopen("../data/${user_id}/projects/${pname}/$csvname", 'a+') or die("can't open file");
			while($row = mysqli_fetch_assoc($result)){
				echo "<tr>";
				$i=0;
				foreach ($row as $cell){
					$data = $data . $cell . "\t";
					echo "<td>";
					$disp = false;
					if ($_GET['target'] == "patterns" && $i == 6){
						echo "<a href='javascript:sendQuery(" . '"' . $project . '","' . $pname . '","' . $purl
							. '","locations","datafiles","' . $row['patstring'] . '","exactm","patterns")' 
							. "'>" . "$cell</a>";
						$disp = true;
					} 
					if ($_GET['target'] == "locations" && $_GET['setloc'] == 'datafiles' && $i == 2){
						echo "<a href='javascript:sendQuery(" . '"' . $project . '","' . $pname . '","' . $purl
							. '","locations","all","'. $row['patstring'] . '","exactm","datafiles","' .
							$row['dataname'] . '")' . "'>" . "$cell</a>";
						$disp = true;
					}
					if ($_GET['target'] == "patterns" && $i == 7){
						echo "<a href='javascript:sendQuery(" . '"' . $project . '","' . $pname . '","' . $purl
                                                        . '","locations","all","'. $row['patstring'] . '","exactm","patterns")'
                                                        . "'>" . "$cell</a>";
                                                $disp = true;
					}
					if ($disp == false){
						echo $cell;
					}
					echo "</td>";
					if ($_GET['target'] == "locations" && $_GET['setloc'] == 'datafiles' && $i == 2){
						echo "<td>";
						echo '<button onclick="convertEAF(this,' . "'$project','$pname','" . 
							$row['patstring'] . "','exactm','" . 
							$row['dataname'] .  "','" . $row['dataname'] . "')" . '">Convert to EAF</button>';
						echo "</td>";
					}
					if ($_GET['target'] == "patterns" && $i == 7){
                                                echo "<td>";
                                                echo '<button  onclick="convertEAF(this,' . "'$project','$pname','" .
                                                        $row['patstring'] . "','exactm','','" .
                                                        $row['id'] . "')" . '">Convert to EAF</button>';
                                                echo "</td>";
                                        }


					$i++;
				}
				$data = $data . "\n";
				echo "</tr>";
			}
			echo "</table>";
			fwrite($fh,$data);
			fclose($fh);
		}
	?>
</div>
