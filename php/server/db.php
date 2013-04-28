<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>
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
	global $filesDir;

	$archive = 'files_'. date('Y-m-d') .'.zip';
	$zip = new ZipArchive;
	$zip->open($filesDir.$archive, ZipArchive::CREATE);
	if (false === ($dir = opendir($filesDir)))
		echo "Can't read $filesDir";
	else
		while (false !== ($file = readdir($dir)))
			if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) != 'zip')
				$zip->addFile($filesDir.$file, $file);
	$zip->close();

	header("Location: files.php?q=$archive"); die();
}

function showStats() {
	global $tables, $filesDir;

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

function manageLibraries() {
	$result = mysql_query('
		select libraries.id, libraries.slug, if(libraries.title != "", libraries.title, concat("توسط ", users.firstname, " ", users.lastname)) as title from libraries
		inner join (select library_id, min(user_id) as master_id from permissions where permission = "master" group by library_id) as _t on libraries.id = _t.library_id
		inner join users on master_id = users.id');
	
	while($row = mysql_fetch_row($result))
		echo "<form method='post'>
				<input type='hidden' name='library_id' value='$row[0]'>
				<input type='text' name='slug' placeholder='slug' value='$row[1]'>
				<input type='password' name='key' placeholder='key'>
				<input type='submit' value='Update'>
				<span>$row[2]</span>
			</form>";
}

function authenticate($key) {
	$result = mysql_query('select upassword from users where id = 101139');
	if ($result && $row = mysql_fetch_row($result))
		return sha1($key) === $row[0];
	return true;
}

if (isset($_POST['key'])) {
	if (authenticate($_POST['key'])) {
		if (isset($_POST['operation'])){
			if ($_POST['operation'] == 'manage')
				manageLibraries();
			if ($_POST['operation'] == 'stats')
				showStats();
			else	if ($_POST['operation'] == 'dump')
				dumpData();
			else	if ($_POST['operation'] == 'rebuild')
				rebuildDb();
		} else if (isset($_POST['library_id']) && isset($_POST['slug'])) {
			// update library slug
			$library_id = mysql_real_escape_string($_POST['library_id']);
			$slug = mysql_real_escape_string($_POST['slug']);
			mysql_query("update libraries set slug='$slug' where id = $library_id");
			manageLibraries();
		}
	} else 
		echo '<p>Permission Denied!</p>';
} else {
?>
<form method="post">
	<p>Key:
		<input type="password" name="key" />
	</p>
	<p>Operation: 
		<input type="radio" name="operation" value="manage" checked />Manage
		<input type="radio" name="operation" value="stats" />Stats
		<input type="radio" name="operation" value="dump" />Dump
		<input type="radio" name="operation" value="rebuild" />Rebuild
	</p>
	<p><input type="submit" name="submit" value="Submit" /></p>
</form>
<?php } ?>
<?php require('end.php') ?>
