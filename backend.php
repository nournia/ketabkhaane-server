<?php require('begin.php') ?>
<?php
	function updateLicense($id) {
		$days = 100;
		mysql_query("update reghaabats set license = sha1(adddate(now(), $days)) where id = $id");
		$row = mysql_fetch_row(mysql_query("select license from reghaabats where id = $id"));
		return $row[0];
	}
	function raiseError($msg) {
		echo 'error - '. $msg;
		require('end.php');
		die;
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
		// check for valid client
		$reghaabat_id = mysql_real_escape_string($_POST['id']);
		$result = mysql_query("select license from reghaabats where id = $reghaabat_id");
		if ($result) {
			$row = mysql_fetch_row($result);
			$license = $row[0];

			if(sha1($reghaabat_id .'|x|'. $license) == $_POST['key'])
				$valid_user = true;
		}

		if (!isset($valid_user))
			raiseError('not a valid client');

		// insert data into db
		$logs = explode('|-|', $_POST['logs']);

		$query = 'insert into logs values ';
		$first = true;
		if (count($logs) == $_POST['count']) {
			foreach($logs as $row) {
				$row = parseRow($row);
				if (!$first) $query .= ','; else $first = false;
				if ($row[5] != '') $text = "'". str_replace("'", '"', $row[5]) ."'"; else $text = 'null';
				if ($row[3] != '') $user = $row[3]; else $user = 'null';
				$query .= "($reghaabat_id,'{$row[0]}','{$row[1]}',{$row[2]},$text,$user,'{$row[4]}')";
			}

			if (mysql_query($query)) {
				$synced_at = $_POST['synced_at'];
				mysql_query("update reghaabats set synced_at = '$synced_at' where id = $reghaabat_id");
				echo 'ok - '. $synced_at;
			} else
				raiseError(mysql_error());
		} else
			raiseError(count($logs) .' rows was sent but '. $_POST['count'] .' was received');
	}
?>
<?php require('end.php') ?>