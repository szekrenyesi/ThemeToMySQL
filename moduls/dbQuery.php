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
<script>
var backcontp;
var backcontl;

function replaceRes(backto){
	document.getElementsByTagName("BODY")[0].style.cursor = "wait";
	document.getElementById('queryw').innerHTML = '';
	if (backto == 'patterns'){
		document.getElementById('queryw').replaceWith(backcontp);
	} else {
		document.getElementById('queryw').replaceWith(backcontl);
	}
	document.getElementsByTagName("BODY")[0].style.cursor = "";
}


function sendQuery(prid,prname,purl,target,setloc,patstring,stype,back,dataname) {

	if (prid == null){
		var prid = document.getElementById('prid').value;
		var prname = document.getElementById('prname').value;
		var purl = document.getElementById('purl').value;
		var target = document.getElementById('seltar').value;
		var setloc =  document.getElementById('setloc').value;
		var patstring = document.getElementById('patstring').value;
		var stype =  document.getElementById('stype').value;
		var plength = document.getElementById('plength').value;
		var plevel = document.getElementById('plevel').value;
		var nactors = document.getElementById('nactors').value;
		var nswitches = document.getElementById('nswitches').value;
		var minfile = document.getElementById('minfile').value;
		var minocc = document.getElementById('minocc').value;
		var orderby = document.getElementById('orb').value;
		var d = document.getElementById('desc').value;
		var dataname = document.getElementById('dataname').value;
	}

	if (back){
		if (back == 'datafiles'){
			backcontl = document.getElementById('queryw').cloneNode(true);
		} 
		if (back == 'patterns'){
			backcontp = document.getElementById('queryw').cloneNode(true);
		}
	}


	document.getElementById('queryw').innerHTML = '<div class="console">Please wait... <div id="loader"></div></div>'
	document.getElementById('queryw').style.cursor = 'wait';
		
	if (window.XMLHttpRequest) {
	    // code for IE7+, Firefox, Chrome, Opera, Safari
	    xmlhttp = new XMLHttpRequest();
	} else {
	    // code for IE6, IE5
	    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
	    document.getElementById('queryw').style.cursor = '';
	    if (this.readyState == 4 && this.status == 200) {
		document.getElementById("queryw").innerHTML = this.responseText;
	    }
	};
	xmlhttp.open("GET","db_query.php?projectid="+prid+"&prname="+prname+"&purl="+purl+"&target="+target+"&setloc="+setloc+
		"&patstring="+patstring+"&stype="+stype+"&plength="+plength+"&plevel="+plevel+"&nactors="+nactors+"&nswitches="+
		nswitches+"&minfile="+minfile+"&minocc="+minocc+"&orderby="+orderby+"&d="+d+"&back="+back+"&dataname="+dataname,true);
	xmlhttp.send();
}

function convertEAF(that,prid,prname,patstring,stype,dataname,id){
	that.innerHTML = 'Converting...';
	that.setAttribute('onclick','alert("Please wait!")');

	if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            document.getElementById('queryw').style.cursor = '';
	    if (this.readyState == 4 && this.status == 200) {
		    downlink = document.createElement('a');
		    downlink.setAttribute('href',this.responseText);
		    downlink.setAttribute('style','color:black');
		    text = document.createTextNode('Download EAF');
		    downlink.appendChild(text);
		    cell = that.parentElement;
		    cell.innerHTML = '';
		    cell.style.backgroundColor = 'lime';
		    cell.appendChild(downlink);
		    cell.scrollIntoView(false);
            }
        };
	xmlhttp.open("GET","convert_eaf.php?projectid="+prid+"&prname="+prname+"&patstring="+patstring+"&stype="+stype+"&dataname="+dataname+"&id="+id,true);
	xmlhttp.send();

}
</script>



<div class="container">
    <!-- Jumbotron-->
    <div class="jumbotron">
		<h2>Search in database</h2>
		<div id="queryw" class="info">
		<script>
			function selectTarget(){
				target = document.getElementById('seltar').value;
				if (target == 'locations'){
					document.getElementById('fnum').style.display = 'none';
					document.getElementById('tnum').style.display = 'none';
					document.getElementById('orderby').style.display = 'none';
					document.getElementById('disploc').style.display = '';
				} else {
					document.getElementById('fnum').style.display = '';
					document.getElementById('tnum').style.display = '';
					document.getElementById('orderby').style.display = '';
					document.getElementById('disploc').style.display = 'none';
				}
			}
		</script>
		<form form id="myForm" onsubmit="sendQuery()">
			<table class='create'>
			<!--<tr> <td>Target table:</td> 
			<td>
				<select id='seltar' name='target' onchange="selectTarget(null,null,null,null)">
					<option value='patterns'>Unique patterns</option>
					<option value='locations'>Pattern locations</option>
				</select>
			</td></tr>-->
			<tr id='disploc' style='display:none'><td>Display:</td>
			<td>
                                <select id='setloc' name='target' onchange="selectTarget(null,null,null,null)">
                                        <option value='datafiles'>datafiles only</option>
                                        <option value='all'>datafiles with locations</option>
                                </select>
                        </td></tr>
			<tr><td>String in pattern:</td> 
			<td><input id="patstring" name='patstring' type="text"><br>
			<select id='stype' name='stype'>
				<option value='substr'>substring match</option>
				<option value='exactm'>exact match</option>
				<option value='regexp'>regular expression</option>
			</select>
			<tr><td>Length:</td><td><input id="plength" name='plength' type=number></td></tr>
			<tr><td>Level:</td><td><input id="plevel" name='plevel' type=number></td></tr>
			<tr><td>N_actors:</td><td><input id="nactors" name='nactors' type=number></td></tr>
			<tr><td>N_switches:</td><td><input id="nswitches" name='nswitches' type=number></td></tr>
			<tr id='fnum'><td>Min. number of files where the<br> pattern occurs:</td><td><input id="minfile" name='minfile' type=number></td></tr>
			<tr id='tnum'><td>Min. Occurence:</td><td><input id="minocc" name='minocc' type=number></td></tr>
			<tr id='orderby'><td>Order by</td><td>
				<select id="orb" name="orderby">
					<option value='id'>ID</option>
					<option value='length'>Length</option>
					<option value='n_actors'>N_actors</option>
					<option value='n_switches'>N_switches</option>
					<option value='datafiles'>Number_of_files</option>
					<option value='instances'>Total_number</option>
				</select>
				<select id="desc" name="d">
					<option value=''>ASC</option>
					<option value='DESC'>DESC</option>
				</select>
					
			</td></tr>
			</table>
			<br>
			<input type='hidden' name='conditions' value=true>
			<input type="hidden" id='prid' name="projectid" value="<?php echo $_POST['projectid'];?>">
			<input type="hidden" id='prname' name="prname" value="<?php echo $_POST['prname'];?>">
			<input type="hidden" id='purl' name="purl" value="<?php echo $_POST['purl'];?>">
			<input type="hidden" id='dataname' name="dataname" value=''>
			<input type="hidden" id="seltar" name="target" value='patterns'>
                        <input class="btn btn-primary btn-lg" type="submit" value="Start search">
		</form>
	</div>
</div>

</div>

</body>
</html>
