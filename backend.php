<?php require('begin.php') ?>
<?php
	function updateLicense($id) {
		$days = 100;
		mysql_query("update reghaabats set license = sha1(adddate(now(), $days)) where id = $id");
		$row = mysql_fetch_row(mysql_query("select license from reghaabats where id = $id"));
		return $row[0];
	}
	function showMessage($type, $msg) {
		echo "$type - $msg";
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
		showMessage('registered', $id .' - '. $license);
	}

	// Store
	function parseRow($data) {
		$content = strpos($data, '|');
		$values = explode(',', substr($data, 0, $content-1));
		$values[] = substr($data, $content+1);
		return $values;
	}

	if ($_POST['command'] == 'store' && isset($_POST['id']) && isset($_POST['key'])) {
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
			showMessage('error', 'not a valid client');

		// extract records
		$logs = explode('|-|', $_POST['logs']);

		if (count($logs) != $_POST['count'])
			showMessage('error', count($logs) .' rows was sent but '. $_POST['count'] .' was received');

		// insert data into db
		$query = 'insert into logs values ';
		$first = true;
		foreach($logs as $row) {
			$row = parseRow($row);
			if (!$first) $query .= ','; else $first = false;
			if ($row[5] != '') $text = "'". str_replace("'", '"', $row[5]) ."'"; else $text = 'null';
			if ($row[3] != '') $user = $row[3]; else $user = 'null';
			$query .= "($reghaabat_id,'{$row[0]}','{$row[1]}',{$row[2]},$text,$user,'{$row[4]}')";
		}
		if (!mysql_query($query))
			showMessage('error', mysql_error());

		// copy files into directory
		if (count($_FILES) > 0) {
			foreach ($_FILES as $file)
				move_uploaded_file($file['tmp_name'], $fileDir . $reghaabat_id .'-'. $file['name']);
		}

		// update reghaabat synced_at
		$synced_at = $_POST['synced_at'];
		mysql_query("update reghaabats set synced_at = '$synced_at' where id = $reghaabat_id");
		showMessage('ok', $synced_at);
	}
?>
<?php require('end.php') ?>