<?php
function connectDatabase() {
	$services_json = json_decode(getenv("VCAP_SERVICES"),true);
	$mysql_config = $services_json["mysql-5.1"][0]["credentials"];

	$username = $mysql_config["username"];
	$password = $mysql_config["password"];
	$hostname = $mysql_config["hostname"];
	$port = $mysql_config["port"];
	$db = $mysql_config["name"];

	$link = mysql_connect("$hostname:$port", $username, $password);
	$db_selected = mysql_select_db($db, $link);
	if (! $db_selected) die("Db Connection Error.");
}

function disconnectDatabase() {
	global $connection;
	if (isset($connection))
		mysql_close($connection);
}
?>

<?php connectDatabase(); ?>