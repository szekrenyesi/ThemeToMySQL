<?php

include 'connect.php';

class step
{
    public $stage;
    public $log;
    public $max;
    public $current;
}

$log = new stdClass;


$log->step[] = new step();
$log->step[0]->stage = 'Step1';
$log->step[0]->log = 'Processing patstr.txt';
$log->step[0]->max = 100;
$log->step[0]->current = 0;
$log->step[] = new step();
$log->step[1]->stage = 'Step2';
$log->step[1]->log = 'Reindexing patterns';
$log->step[1]->max = 100;
$log->step[1]->current = 0;
$log->step[] = new step();
$log->step[2]->stage = 'Step3';
$log->step[2]->log = 'Processing patdur.txt';
$log->step[2]->max = 100;
$log->step[2]->current = 0;
$log->step[] = new step();
$log->step[3]->stage = 'Step4';
$log->step[3]->log = 'Searching related patstring';
$log->step[3]->max = 100;
$log->step[3]->current = 0;
$log->step[] = new step();
$log->step[4]->stage = 'Step5';
$log->step[4]->log = 'Creating unique patterns';
$log->step[4]->max = 100;
$log->step[4]->current = 0;
$log->step[] = new step();
$log->step[5]->stage = 'Step6';
$log->step[5]->log = 'Inserting unique patterns';
$log->step[5]->max = 100;
$log->step[5]->current = 0;
$log->step[] = new step();
$log->step[6]->stage = 'Step7';
$log->step[6]->log = 'Inserting pattern locations';
$log->step[6]->max = 100;
$log->step[6]->current = 0;


$patstrpath = $argv[1];
$patdurpath = $argv[2];
$userid = $argv[3];
$pname = $argv[4];
$dbname = $userid . "_" . $pname;

$logfile = fopen("../data/$userid/projects/$pname/progress.json", "w") or die("Unable to open file!");

$newline = "\n";


function search_key($array, $field, $value)
{
   foreach($array as $key => $product)
   {
      if ( $product[$field] == $value )
         return $key;
   }
   return false;
}

function search_string($array, $field1, $field2, $field3, $value1, $value2)
{
   foreach($array as $product)
   {
      if ( $product[$field1] == $value1 && $product[$field2] == $value2){
         return $product[$field3];
      }
   }
   return false;
}

function unique_multidim_array($array, $key) {
    global $log;
    global $logfile;
    global $userid;
    global $pname;
    $temp_array = array();
    $i = 0;
    $u = 0;
    $key_array = array();
    $length = count($array);
    $onep = $length / 100;
    $prev = 0;
  
    foreach($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$u] = $val;
	    $u++;
        }
        $i++;
	$act = round($i / $onep);
	if ($act > $prev){
		$log->step[4]->current = $act;
		$json = json_encode($log);
		$logfile = fopen("../data/$userid/projects/$pname/progress.json", "w") or die("Unable to open file!");
		fwrite($logfile,$json);
		$prev = $act;
	}
    }
    unset($key_array);
    unset($array);
    return $temp_array;
} 

function proc_file($filename,$step){
	global $log;
	global $logfile;
	global $userid;
	global $pname;
	$header = NULL;
	$data = array();
	$linecount = 0;
	$handle = fopen($filename, "r");
	while(!feof($handle)){
	  $line = fgets($handle);
	  $linecount++;
	}	
	fclose($handle);
	$onep = $linecount / 100;
	$prev = 0;
	$i=0;

	if (($handle = fopen($filename, 'r')) !== FALSE)
	{
		while (($row = fgetcsv($handle, filesize($filename), "\t")) !== FALSE)
		{
			if(!$header){
				$header = $row;
			}
			else {
				$data[] = array_combine($header, $row);
			}
			$i++;
			$act = round($i / $onep);
			if ($act > $prev){
				$log->step[$step]->current = $act;
				$json = json_encode($log);
				$logfile = fopen("../data/$userid/projects/$pname/progress.json", "w") or die("Unable to open file!");
				fwrite($logfile,$json);
				$prev = $act;
			}
		}
		fclose($handle);
		
	}
	return $data;
}

function removeElement($array, $key, $value){
     foreach($array as $subKey => $subArray){
          if($subArray[$key] == $value){
               unset($array[$subKey]);
          }
     }
     return $array;
}

echo "Processing patstr.txt...${newline}";

$patstr = proc_file($patstrpath,0);

echo "Fixing dataname...${newline}";

foreach($patstr as $key => $pattern){
		if ($pattern['dataname'] != "dataname"){
			$realdata = $pattern['dataname'];

		} else {
			$patstr[$key]['dataname'] = $realdata;
		}
}

echo "Reindexing patterns...${newline}";

$patstr2 = array();

$length = count($patstr);
$onep = $length / 100;
$prev = 0;
$i=0;

foreach($patstr as $pattern){
	$dataname = $pattern['dataname'];
	$pid = $pattern['id'];
	$newkey = $dataname . '_' . $pid;
	$patstring = $pattern['patstring'];
	$patstr2[$newkey]['patstring'] = $patstring;
	$i++;
	$act = round($i / $onep);
	if ($act > $prev){
		$log->step[1]->current = $act;
		$json = json_encode($log);
		$logfile = fopen("../data/$userid/projects/$pname/progress.json", "w") or die("Unable to open file!");
		fwrite($logfile,$json);
		$prev = $act;
	}
}

echo "Processing patdur.txt...${newline}";

$patdur = proc_file($patdurpath,2);

echo "Fixing dataname...${newline}";

foreach($patdur as $key => $pattern){
		if ($pattern['dataname'] != "dataname"){
				$realdata = $pattern['dataname'];
		} else {
			$patdur[$key]['dataname'] = $realdata;
		}
}

echo "Searching related patstring...${newline}";

$patdur_length = count($patdur);
$onep = $patdur_length / 100;
$prev = 0;
$i=0;

foreach($patdur as $key => $pattern){
		$search_id = $pattern['id'];
		$search_data = $pattern['dataname'];
		$search = $search_data . "_" . $search_id;
		//$found_string = search_string($patstr, 'id', 'DATANAME', 'patstring', $search_id, $search_data );
		//$patdur[$key]['id'] = $found_string;
		$patdur[$key]['id'] = $patstr2[$search]['patstring'];
		$i++;
		$act = round($i / $onep);
		if ($act > $prev){
			$log->step[3]->current = $act;
	                $json = json_encode($log);
			$logfile = fopen("../data/$userid/projects/$pname/progress.json", "w") or die("Unable to open file!");
        	        fwrite($logfile,$json);
			$prev = $act;
		}
}

unset($patstr2);

echo "Creating unique patterns...${newline}";

$unique = unique_multidim_array($patstr,'patstring');
$unique = removeElement($unique, "patstring", 'patstring');

unset($patstr);

$conn = connect_db($dbname);

echo "Inserting unique patterns...${newline}";

$unique_length = count($unique);
$onep = $unique_length / 100;
$prev = 0;
$i=0;

foreach($unique as $key => $value){
	$sql = "INSERT INTO unique_patterns VALUES (${key},${value['Length']},${value['Level']},${value['N_actors']},${value['N_switches']},'" . 
	$value['patstring'] . "');";
	if (mysqli_query($conn, $sql)) {
		$i++;
		$act = round($i / $onep);
		if ($act > $prev){
			$log->step[5]->current = $act;
	                $json = json_encode($log);
			$logfile = fopen("../data/$userid/projects/$pname/progress.json", "w") or die("Unable to open file!");
        	        fwrite($logfile,$json);
			$prev = $act;
		}
	} else {
		echo "Error insterting data: " . mysqli_error($conn) . "${newline}";
	}
}

unset($unique);

echo "Inserting pattern locations...${newline}";

$onep = $patdur_length / 100;
$prev = 0;
$i=0;
foreach($patdur as $key => $value){
	$sql = "INSERT INTO pattern_locations VALUES (${key},'" . $value['dataname'] . "',
	(SELECT id FROM unique_patterns WHERE patstring = '${value['id']}'),
	${value['sample']},${value['starttime']},
	${value['endtime']},${value['duration']});";
	if (mysqli_query($conn, $sql)) {
		$i++;
		$act = round($i / $onep);
		if ($act > $prev){
			$log->step[6]->current = $act;
	                $json = json_encode($log);
			$logfile = fopen("../data/$userid/projects/$pname/progress.json", "w") or die("Unable to open file!");
        	        fwrite($logfile,$json);
			$prev = $act;
		}
	} else {
		echo "Error insterting data: " . mysqli_error($conn) . "${newline}";
		print_r($value);
		exit;
	}
}
unset($patdur);

mysqli_close($conn);

$conn = connect_db('users');

$sql = "UPDATE projects SET processed=1 WHERE id='${dbname}'";
if (mysqli_query($conn, $sql)) {
	echo "\nProject successfuly processed\n";
} else {
	echo "\nError finishing project.\n";
}

mysqli_close($conn);

?>
