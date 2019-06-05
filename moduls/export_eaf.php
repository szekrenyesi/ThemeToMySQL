<?php 

function export_eaf($project,$path,$sql,$log){
	include 'connect.php';

	$conn = connect_db($project);

	$allsql = "SELECT dataname,pattern,starttime,endtime,duration,patstring 
	FROM pattern_locations INNER JOIN unique_patterns 
	ON unique_patterns.id = pattern_locations.pattern 
	ORDER BY dataname,pattern,starttime;";

	$dataname = '';
	$aid = 1;
	$atier = '';
	$tslotid = 1;
	$newline = '<br>';
	$xml = "empty";
	$fileid = 0;
	if ($log == 'yes'){
		echo "Exported files: <span id='counter'>0</span><br>";
		flush();
	}
	if ($result=mysqli_query($conn,$sql)) {
		if (mysqli_num_rows($result) < 1){
			if ($log == 'yes'){
				echo "<script>document.getElementById('counter').innerHTML = 'no patterns found'</script>";
			}
			exit;
		}
		while ($obj=mysqli_fetch_object($result)){
			if ($obj->duration > 1){
				if ($dataname != $obj->dataname){
					if ($xml != "empty"){
						#$xml->asXml('eaf/export/' . $dataname . '.eaf');
						$dom = new DomDocument('1.0');
						#$dom = dom_import_simplexml($xml)->ownerDocument;
						$dom->loadXML($xml->asXML());
						$dom->preserveWhiteSpace = false;
						$dom->formatOutput = true;
						$dom->save($path . $dataname . '.eaf');
						$fileid += 1;
						if ($log == 'yes'){
							echo "<script>document.getElementById('counter').innerHTML = 
								'$fileid'</script>";
							flush();
						}
					}
					$date = date(DATE_ATOM, mktime(0, 0, 0, 7, 1, 2000));
					$xmldata="<?xml version='1.0' encoding='UTF-8'?>
					<ANNOTATION_DOCUMENT AUTHOR='' DATE='${date}' FORMAT='2.8' VERSION='2.8' 
					xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' 
					xsi:noNamespaceSchemaLocation='http://www.mpi.nl/tools/elan/EAFv2.8.xsd'>
					<HEADER MEDIA_FILE='' TIME_UNITS='milliseconds'>
					</HEADER>
					<TIME_ORDER>
					</TIME_ORDER>
					<LINGUISTIC_TYPE GRAPHIC_REFERENCES='false' LINGUISTIC_TYPE_ID='Praat' TIME_ALIGNABLE='true'/>
					<CONSTRAINT DESCRIPTION='' STEREOTYPE='Time_Subdivision'/>
					<CONSTRAINT DESCRIPTION='' STEREOTYPE='Symbolic_Subdivision'/>
					<CONSTRAINT DESCRIPTION='' STEREOTYPE='Symbolic_Association'/>
					<CONSTRAINT DESCRIPTION='' STEREOTYPE='Included_In'/>
					</ANNOTATION_DOCUMENT>";
					$xml=simplexml_load_string($xmldata) or die("Error: Cannot create object");
					$dataname = $obj->dataname;
				}
				if ($atier != $obj->pattern){
					$atier = "Pattern_" . $obj->pattern;
					$tier = $xml->addChild('TIER');
					$tier->addAttribute('LINGUISTIC_TYPE_REF','Praat');
					$tier->addAttribute('TIER_ID',$atier);
				}
				$ann = $tier->addChild('ANNOTATION');
				$anndata = $ann->addChild('ALIGNABLE_ANNOTATION');
				
				$tslot = $xml->TIME_ORDER->addchild('TIME_SLOT');
				$startid = 'ts' . $tslotid;
				$tslot->addAttribute('TIME_SLOT_ID',$startid);
				$tslot->addAttribute('TIME_VALUE',$obj->starttime);
				$tslotid += 1;
				$tslot = $xml->TIME_ORDER->addchild('TIME_SLOT');
				$endid = 'ts' . $tslotid;
				$tslot->addAttribute('TIME_SLOT_ID',$endid);
				$tslot->addAttribute('TIME_VALUE',$obj->endtime);
				$tslotid += 1;
				
				$annid = 'a' . $aid;
				$anndata->addAttribute('ANNOTATION_ID',$annid);
				$anndata->addAttribute('TIME_SLOT_REF1', $startid);
				$anndata->addAttribute('TIME_SLOT_REF2', $endid);
				$anndata->addChild('ANNOTATION_VALUE',$obj->patstring);
				$aid += 1;
			}
			unset($obj);
		}
                $dom = new DomDocument('1.0');
                $dom->loadXML($xml->asXML());
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput = true;
                $dom->save($path . $dataname . '.eaf');
		$fileid += 1;
		if ($log == 'yes'){
			echo "<script>document.getElementById('counter').innerHTML = '$fileid'</script>";
        	        flush();
		}
		mysqli_free_result($result);
	}
}
?>
