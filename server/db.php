<?php require('begin.php') ?>
<?php
$tables = array('libraries', 'ageclasses', 'categories', 'open_categories', 'types', 'accounts', 'roots', 'branches', 'users', 'authors', 'publications', 'objects', 'matches', 'questions', 'files', 'logs', 'answers', 'borrows', 'open_scores', 'permissions', 'supports', 'transactions');
$views = array('_borrowed');

if (isset($_GET['rebuild'])) {

	// drop tables
	$commands = array();
	foreach ($tables as $table)
		$commands[] = 'drop table if exists '. $table;
	foreach ($views as $view)
		$commands[] = 'drop view if exists '. $view;

	$sqlFile = 'server.sql';
	$sql = fread(fopen($sqlFile, 'r'),filesize($sqlFile));

	$table_conf = ' engine=MyISAM collate=utf8_general_ci';
	foreach (explode(';', $sql) as $command) {
		if (strpos($command, 'CREATE TABLE') !== false)
			$command = $command . $table_conf;
		else if (strpos($command, 'CREATE') === false && strpos($command, 'INSERT') === false)
			continue;

		$commands[] = $command;
	}

	foreach ($commands as $command)
		if (!mysql_query($command))
			echo mysql_error(). '<br>';

	echo 'Database Rebuilt <br>';

	// create files directory
	echo 'Files Directory: '. $filesDir;
	if (!file_exists($filesDir)) {
		mkdir($filesDir);
		if (file_exists($filesDir))
			echo ' +created';
	}
}

if (isset($_GET['stats'])) {
	foreach ($tables as $table) {
		$value = 0;
		$result = mysql_query("select count(id) from $table");
		if ($result) {
			$row = mysql_fetch_row($result);
			$value = $row[0];
		}
		echo "<li>$table: $value</li>";
	}

	// files
	if (file_exists($filesDir)) {
		$handler = opendir($filesDir);
		while ($file = readdir($handler))
			echo $file .'<br>';
		closedir($handler);
	} else {
		echo 'no files directory!' .'<br>';
		echo $filesDir .'<br>';
		echo mkdir($filesDir);
	}
}

if (isset($_GET['env'])) {
	print_r(getenv("VCAP_SERVICES"));
}
?>
<?php require('end.php') ?>
