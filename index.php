<?
error_reporting(E_ALL);

require_once "FiasParser.class.php";
require_once "settings.php";


$dbObjects = array();

$p = new FiasParser($token, $key);

foreach ($includeCity as $cityId) {
	echo "Grab city info. cityId = ".$cityId . "\n";
	$city = $p->cityInfo($cityId);
	if ($city) {
		$dbObjects = array_merge($dbObjects, $p->toArray($city->result[0]));
	}
}

$tmpObj = $dbObjects; // специально, т.к. мы добавляем в
foreach ($tmpObj as $o) {
	if ($o['contentType'] == 'city') {
		$cityId = $o['id'];
		echo "Add streets for cityId = ".$cityId . "\n";
		$streets = $p->streetList($cityId, 999);

		if ($streets->result) {
			echo "Find ".count($streets->result)." streets" . "\n";
			foreach ($streets->result  as $street) {
				$r = (array)$street;
				$r['parentId'] = $cityId;
				$dbObjects[] = $r;
			}
		}

	}
}



// save to database
if ($dbObjects) {
	echo "Generate query for ".count($dbObjects) . " objects";
	echo "memory is ".(memory_get_usage()/1024/1024). "Mb\n";
	foreach ($dbObjects as $object) {
		$sql[] = sprintf("INSERT INTO %s (id,name,zip,type,typeShort,okato,contentType,parentId) VALUES (%s,'%s','%s','%s','%s','%s','%s',%s);",
			$sqlTable,
			$object['id'],
			$object['name'],
			$object['zip'],
			$object['type'],
			$object['typeShort'],
			$object['okato'],
			$object['contentType'],
			$object['parentId'] ? $object['parentId'] : 'NULL'
		);
	}
}

echo "Insert data into table ".$sqlTable . "\n";
$dbLink = mysql_connect($sqlHost,$sqlUser,$sqlPass);
mysql_select_db($sqlDb);
mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
mysql_query("TRUNCATE TABLE `".$sqlTable."`");
foreach ($sql as $s) {
	mysql_query($s);
}
