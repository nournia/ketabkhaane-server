<?php require('begin.php') ?>
<?php
$tables = array('libraries', 'ageclasses', 'categories', 'open_categories', 'types', 'accounts', 'roots', 'branches', 'users', 'authors', 'publications', 'objects', 'matches', 'questions', 'files', 'logs', 'answers', 'borrows', 'open_scores', 'permissions', 'supports', 'transactions');

if (isset($_GET['rebuild'])) {

	// drop tables
	$commands = array();
	foreach ($tables as $table)
		$commands[] = 'drop table if exists '. $table;

	$sqlFile = 'server.sql';
	$sql = fread(fopen($sqlFile, 'r'),filesize($sqlFile));

	$table_conf = ' engine=MyISAM collate=utf8_general_ci';
	foreach (explode(';', $sql) as $command) {
		if (strpos($command, 'CREATE') !== FALSE)
			$command .= $table_conf;
		else if (strpos($command, 'INSERT') !== FALSE)
			$command = $command;
		else
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
	echo 'Logs Directory: '. $logsDir;
	if (!file_exists($logsDir)) {
		mkdir($logsDir);
		if (file_exists($logsDir))
			echo ' +created';
	}
}

if (isset($_GET['env'])) {
	print_r(getenv("VCAP_SERVICES"));
}
?>
<?php require('end.php') ?>
