<?php
function connectDatabase($settings) {
	$env = json_decode($settings, true);
	$config = $env["mysql-5.1"][0]["credentials"];

	$link = mysql_connect("{$config['hostname']}:{$config["port"]}", $config["username"], $config["password"]);
	$db_selected = mysql_select_db($config["name"], $link);
	if (! $db_selected) die("Db Connection Error.");
}

function disconnectDatabase() {
	global $connection;
	if (isset($connection))
		mysql_close($connection);
}

// connect db
$settings = getenv("VCAP_SERVICES");
if (empty($settings))
	$settings = '{"mysql-5.1": [{"credentials": {"hostname": "localhost", "name": "reghaabat-db", "port": "3306", "username": "root", "password": ""}}]}';

connectDatabase($settings);
$filesDir = 'files/';
?>