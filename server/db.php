<?php require('begin.php') ?>
<?php
$tables = array('libraries', 'ageclasses', 'categories', 'open_categories', 'types', 'accounts', 'roots', 'branches', 'users', 'authors', 'publications', 'objects', 'matches', 'files', 'logs', 'answers', 'borrows', 'open_scores', 'permissions', 'supports', 'belongs', 'transactions');
$views = array('_borrowed');

function rebuildDb() {
	global $tables, $views, $filesDir;

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

function dumpData() {
	global $tables, $views, $filesDir;

}

function showStats() {
	global $tables, $views, $filesDir;

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

if (isset($_POST['key']) && isset($_POST['operation'])) {
	$auth = false;
	$row = mysql_fetch_row(mysql_query('select upassword from users where id = 101139'));
	if ($row)
		$auth = sha1($_POST['key']) === $row[0];
	else 
		$auth = true;

	if ($auth) {
		if ($_POST['operation'] == 'stats')
			showStats();
		else	if ($_POST['operation'] == 'dump')
			dumpData();
		else	if ($_POST['operation'] == 'rebuild')
			rebuildDb();
	} else
		echo '<p>Permission Denied!</p>';
}
?>
<form method="post">
	<p>Key:
		<input type="password" name="key" />
	</p>
	<p>Operation: 
		<input type="radio" name="operation" value="stats"checked />Stats
		<input type="radio" name="operation" value="dump" />Dump
		<input type="radio" name="operation" value="rebuild" />Rebuild
	</p>
	<p><input type="submit" name="submit" value="Submit" /></p>
</form>
<?php require('end.php') ?>
