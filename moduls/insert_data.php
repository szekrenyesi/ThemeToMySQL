<?php

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
    $temp_array = array();
    $i = 0;
    $key_array = array();
   
    foreach($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
} 

function proc_file($filename){
	
	$header = NULL;
	$data = array();
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

function insertData($patstrpath,$patdurpath,$dbname){
	
	$newline = "<br>";
	
	echo "Processing patstr.txt...${newline}";
	
	flush();

	$patstr = proc_file($patstrpath);

	echo "Fixing dataname...${newline}";
	flush();

	foreach($patstr as $key => $pattern){
			if ($pattern['DATANAME'] != "undefined"){
					$realdata = $pattern['DATANAME'];
			} else {
				$patdur[$key]['DATANAME'] = $realdata;
			}
	}

	echo "Creating unique patterns...${newline}";
	flush();

	$unique = unique_multidim_array($patstr,'patstring'); 
	$unique = removeElement($unique, "patstring", 'patstring');

	echo "Processing patdur.txt...${newline}";
	flush();

	$patdur = proc_file($patdurpath);

	echo "Fixing dataname...${newline}";
	flush();

	foreach($patdur as $key => $pattern){
			if ($pattern['dataname'] != "undefined"){
					$realdata = $pattern['dataname'];
			} else {
				$patdur[$key]['dataname'] = $realdata;
			}
	}

	echo "Searching related patstring...${newline}";
	flush();

	foreach($patdur as $key => $pattern){
			$search_id = $pattern['id'];
			$search_data = $pattern['dataname'];
			$found_string = search_string($patstr, 'id', 'DATANAME', 'patstring', $search_id, $search_data );
			$patdur[$key]['id'] = $found_string;
	}

	echo "Reindexing patterns...${newline}";
	flush();

	foreach($patdur as $key => $pattern){
			$search_id = $pattern['id'];
			$found_key = search_key($unique, 'patstring', $search_id);
			$patdur[$key]['id'] = $found_key;
	}

	$conn = connect_db($dbname);
	
	echo "Inserting unique patterns: <span id='up'></span>${newline}";
	flush();

	foreach($unique as $key => $value){
		$sql = "INSERT INTO unique_patterns VALUES (${key},${value['Length']},${value['Level']},${value['N_actors']},${value['N_switches']},'" . 
		$value['patstring'] . "');";
		if (mysqli_query($conn, $sql)) {
			echo "<script>document.getElementById('up').innerHTML = '${key}';</script>";
			flush();
		} else {
			echo "Error insterting data: " . mysqli_error($conn) . "${newline}";
		}
	}
	
	echo "Inserting pattern locations: <span id='pl'></span>${newline}";
	flush();
	
	foreach($patdur as $key => $value){
		$sql = "INSERT INTO pattern_locations VALUES (${key},'" . $value['dataname'] . "',${value['id']},${value['sample']},${value['starttime']},
		${value['endtime']},${value['duration']});";
		if (mysqli_query($conn, $sql)) {
			echo "<script>document.getElementById('pl').innerHTML = '${key}';</script>";
			flush();
		} else {
			echo "Error insterting data: " . mysqli_error($conn) . "${newline}";
			print_r($value);
			exit;
		}
	}
}
?>
