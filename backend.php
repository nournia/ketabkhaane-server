<?php require('begin.php') ?>
<?php
	function updateLicense($id) {
		$days = 100;
		mysql_query("update reghaabats set license = sha1(adddate(now(), $days)) where id = $id");
		$row = mysql_fetch_row(mysql_query("select license from reghaabats where id = $id"));
		return $row[0];
	}

	if (!isset($_POST['command']))
		$_POST['command'] = '';

	// Register
	if ($_POST['command'] == 'register') {
		mysql_query('insert into reghaabats (title) values ("")');
		$id = mysql_insert_id();
		$license = updateLicense($id);
		echo 'ok - '. $id .' - '. $license;
	}

	// Receive
	function parseRow($data) {
		$content = strpos($data, '|');
		$values = explode(',', substr($data, 0, $content-1));
		$values[] = substr($data, $content+1);
		return $values;
	}

	if ($_POST['command'] == 'receive' && isset($_POST['id']) && isset($_POST['key'])) {
		$reghaabat_id = $_POST['id'];
		// todo: check for valid key

		$logs = explode('|-|', $_POST['logs']);
		if (count($logs) == $_POST['count']) {
			foreach($logs as $row) {
				$row = parseRow($row);
				$row[5] = mysql_real_escape_string($row[5]);
				mysql_query("insert into logs values({$reghaabat_id}, '{$row[0]}', '{$row[1]}', {$row[2]}, {$row[3]}, '{$row[4]}', '{$row[5]}')");
			}

			echo 'ok - '. $_POST['synced_at'];
		} else
			echo 'error - '. count($logs) .' rows was sent but '. $_POST['count'] .' was received';
	}

?>
<?php require('end.php') ?>