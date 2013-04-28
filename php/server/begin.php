<?php
require('config.php');

$db = 'reghaabat';
if (isset($_ENV['OPENSHIFT_MYSQL_DB_HOST'])) {
	$hostname = $_ENV['OPENSHIFT_MYSQL_DB_HOST'];
	$port = $_ENV['OPENSHIFT_MYSQL_DB_PORT'];
	$username = $_ENV['OPENSHIFT_MYSQL_DB_USERNAME'];
	$password = $_ENV['OPENSHIFT_MYSQL_DB_PASSWORD'];
} else {
	$hostname = 'localhost';
	$port = '3306';
	$username = 'root';
	$password = '';
}

// connect db
$connection = mysql_connect("$hostname:$port", $username, $password);
$selected = mysql_select_db($db, $connection);
if (! $selected)
	die('Db Connection Error.');
