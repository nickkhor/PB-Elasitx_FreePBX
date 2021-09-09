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

header("Content-Type: text/xml");

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
$col2disp = array(	// SQL Column , XML SIP Directory Attribute
	array("extension","Telephone")
	, array("name","Name")
	);

$colStr = "";
for ($i=0; $i < count($col2disp); $i++){
	if ($i > 0) $colStr .= ",";
	$colStr .= "{$col2disp[$i][0]}";
}

$sql = "SELECT {$colStr} FROM users ORDER BY extension";
$result = $conn->query($sql);

/*--------XML CONTENT START HERE --------*/
$xml_content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml_content .= "<YealinkIPPhoneDirectory>\n";

if ($result->num_rows > 0) {
	// output data of each row
	while($row = $result->fetch_assoc()) {
		$xml_content .= "\t<DirectoryEntry>\n";
		
		//Output for each column/directory map
		for ($i=0; $i < count($col2disp); $i++) {
			$xml_content .= "\t\t<{$col2disp[$i][1]}>";
			$xml_content .= "{$row[$col2disp[$i][0]]}";
			$xml_content .= "</{$col2disp[$i][1]}>\n";
		}
		$xml_content .= "\t</DirectoryEntry>\n";
	}
}
$xml_content .= "</YealinkIPPhoneDirectory>\n";

echo $xml_content;
/*--------XML CONTENT END HERE --------*/






?>

