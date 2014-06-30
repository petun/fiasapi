<?

require_once "FiasParser.class.php";
error_reporting(E_ALL);

// app params
$token = '53b12622fca9169d6d0f2393';
$key = 'ae5f1e9548785b0ba8cdc8a1f8f533a3119072e3';


$sqlTable  = 'fias';
$sqlHost  = 'localhost';
$sqlUser  = 'root';
$sqlPass  = '';
$sqlDb  = 'fias';

$regionId = '5200000000000';

$includeCity = array(
	5200000700000,
	5200000701200,
	
);

$dbObjects = array();

$p = new FiasParser($token, $key);
echo "Get city list from regionId = ".$regionId . "\n";
$data = $p->cityList($regionId, 999);


if ($data->result) {
	foreach ($data->result as $object) {
		// добавляем только те что нужно
		if (in_array($object->id, $includeCity) || true) {
			echo "Add object ".$object->id. " name = ".$object->name . "\n";
			$dbObjects = array_merge($dbObjects, $p->toArray($object));
		}
	}
}


$tmpObj = $dbObjects; // специально, т.к. мы добавляем в
foreach ($tmpObj as $o) {
	if ($o['contentType'] == 'city' && false) {
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
mysql_query("TRUNCATE TABLE `".$sqlTable."`");
foreach ($sql as $s) {
	mysql_query($s);
}