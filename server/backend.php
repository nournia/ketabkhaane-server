<?php require('begin.php') ?>
<?php
	$event_tables = array('answers', 'borrows', 'open_scores', 'permissions', 'supports', 'belongs', 'transactions');
	$entity_tables = array('roots', 'branches', 'users', 'authors', 'publications', 'objects', 'matches', 'files');

	function updateLicense($id) {
		$days = 100;
		mysql_query("update libraries set license = sha1(adddate(now(), $days)) where id = $id");
		$row = mysql_fetch_row(mysql_query("select license from libraries where id = $id"));
		return $row[0];
	}
	function parseRow($data) {
		$content = strpos($data, '|');
		$values = explode(',', substr($data, 0, $content-1));
		$values[] = substr($data, $content+1);
		return $values;
	}
	function response($state, $data) {
		$data['command'] = $_POST['command'];
		$data['state'] = $state;
		echo json_encode($data);
		require('end.php'); die;
	}
	function returnError($message) {
		response('error', array('message' => $message));
	}
	function returnData($data) {
		response('ok', $data);
	}

	// check arguments
	if (!(isset($_POST['command']) && isset($_POST['id']) && isset($_POST['key']))) {
		require('end.php'); die;		
	}
	$library_id = mysql_real_escape_string($_POST['id']);
		
	// authentication
	if ($_POST['command'] == 'register') {
		mysql_query('insert into libraries (title) values ("")');
		$id = mysql_insert_id();
		$license = updateLicense($id);
		response('ok', array('id' => $id, 'license' => $license));
	}
	else  {
		$result = mysql_query("select license from libraries where id = $library_id");
		if ($result) {
			$row = mysql_fetch_row($result);
			$license = $row[0];

			if(sha1($library_id .'|x|'. $license) == $_POST['key'])
				$valid_user = true;
		}

		if (!isset($valid_user))
			returnError('Authentication Failed.');
	}

	// query data
	if ($_POST['command'] == 'query') {
		if ($_POST['query'] == 'synced_at') {
			$row = mysql_fetch_row(mysql_query("select synced_at from libraries where id = $library_id"));
			returnData(array('synced_at' => $row[0]));
		}
	}

	// store
	if ($_POST['command'] == 'store') {

		// extract records
		$logs = explode('|-|', $_POST['xlogs']);

		// data validation
		if (count($logs) != $_POST['count'])
			returnError($_POST['count'] .' rows was sent but '.  count($logs) .' was received');

		// write logs to file
		$logFile = fopen($filesDir . $library_id . '.log', 'a');

		// insert data into db in groups
		$command = ''; $table = ''; $values = array();

		function storeData() {
			global $library_id, $command, $table, $values, $event_tables, $entity_tables;

			if (count($values) > 0 && (in_array($table, $event_tables) || in_array($table, $entity_tables))) {
				$query = '';
				$values = join(',', $values);

				if ($command == 'insert')
					$query = "insert ignore into $table values $values";
				else if ($command == 'update')
					$query = "replace into $table values $values";
				else if ($command == 'delete')
					$query = "delete from $table where id in ($values)". (in_array($table, $event_tables) ? " and library_id = $library_id" : '');
				else
					returnData('Invalid Db Command');

				if (!mysql_query($query))
					returnError(mysql_error());
			}
		}

		foreach ($logs as $row) {
			// store row before parsing it
			fwrite($logFile, $row. "\n");

			// set new value
			$row = parseRow($row);
			if ($row[1] == 'delete')
				$value = $row[2];
			else if (in_array($row[0], $event_tables))
				$value = "($library_id,{$row[2]},{$row[5]})";
			else
				$value = "({$row[2]},{$row[5]})";

			// store values
			if ($table == $row[0] && $command == $row[1] && count($values) < 100)
				$values[] = $value;
			else {
				storeData();

				$command = $row[1];
				$table = $row[0];
				$values = array($value);
			}
		}
		storeData();

		// copy files into directory
		if (count($_FILES) > 0) {
			foreach ($_FILES as $file)
				move_uploaded_file($file['tmp_name'], $filesDir . $file['name']);
		}

		// update reghaabat synced_at
		$synced_at = $_POST['synced_at'];
		mysql_query("update libraries set synced_at = '$synced_at' where id = $library_id");
		returnData(array('synced_at' => $synced_at, 'count' => count($logs)));
	}

	returnError('Invalid Command.');
