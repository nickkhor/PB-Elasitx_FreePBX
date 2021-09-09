<?php 
/* 
  +----------------------------------------------------------------------+
  | Made from 99% recycled code, applicable rights                       |
  | Palosanto Solutions S. A., Coalescent Systems Inc, FreePBX, et al.   |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+--------------+
  | Script mod from https://ethertubes.com/grandstream-phone-book-creator-for-elastix/	|
  |	Git Repo https://github.com/nickkhor/PB-Elasitx_FreePBX								|
  |	USE AT YOUR OWN RISK, YOYO!															|
  +-------------------------------------------------------------------------------------+
*/
header('Content-Type:application/json;charset=utf-8');

define("AMP_CONF", "/etc/amportal.conf");

$file = file(AMP_CONF);
if (is_array($file)) {
    foreach ($file as $line) {
        if (preg_match("/^\s*([a-zA-Z0-9_]+)=([a-zA-Z0-9 .&-@=_!<>\"\']+)\s*$/",$line,$matches)) {
            $amp_conf[ $matches[1] ] = $matches[2];
        }
    }
}

require_once('DB.php'); //PEAR must be installed
$db_user = $amp_conf["AMPDBUSER"];
$db_pass = $amp_conf["AMPDBPASS"];
$db_host = $amp_conf["AMPDBHOST"];
$db_name = $amp_conf["AMPDBNAME"];

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

//Colomn to modify, add array if needed
$col2disp = array(	// SQL Column , JSON  Directory Attribute
	array("extension","number")
	, array("name","name")
	);

$colStr = "";
for ($i=0; $i < count($col2disp); $i++){
	if ($i > 0) $colStr .= ",";
	$colStr .= "{$col2disp[$i][0]}";
}

$sql = "SELECT {$colStr} FROM users ORDER BY extension";
$result = $conn->query($sql);

/*--------XML CONTENT START HERE --------*/
$json_content = "{\"contacts\":[\n";

if ($result->num_rows > 0) {
	// output data of each row
	//IF having PHP 5.2 and above, can use json_encode method
	$idx1=0;
	while($row = $result->fetch_assoc()) {
		if ($idx1 > 0) {
			$json_content .= ",";
		}
		
		$json_content .= "{ ";
		$idx2 = 0;
		//Output for each column/directory map
		for ($i=0; $i < count($col2disp); $i++) {
			if ($i > 0) $json_content .= ",";
			$json_content .= "\"{$col2disp[$i][1]}\":\"{$row[$col2disp[$i][0]]}\"";
		}
		
		$json_content .= "}\n";
		$idx1++;
	}
}
$json_content .= "]}\n";

echo $json_content;
/*--------XML CONTENT END HERE --------*/



?>
