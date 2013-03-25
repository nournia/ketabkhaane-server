<?php require('begin.php') ?>
<?php
	function updateLicense($id) {
		$days = 100;
		mysql_query("update reghaabats set license = sha1(adddate(now(), $days)) where id = $id");
		$row = mysql_fetch_row(mysql_query("select license from reghaabats where id = $id"));
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
	$reghaabat_id = mysql_real_escape_string($_POST['id']);
		
	// authentication
	if ($_POST['command'] == 'register') {
		mysql_query('insert into reghaabats (title) values ("")');
		$id = mysql_insert_id();
		$license = updateLicense($id);
		response('ok', array('id' => $id, 'license' => $license));
	}
	else  {
		$result = mysql_query("select license from reghaabats where id = $reghaabat_id");
		if ($result) {
			$row = mysql_fetch_row($result);
			$license = $row[0];

			if(sha1($reghaabat_id .'|x|'. $license) == $_POST['key'])
				$valid_user = true;
		}

		if (!isset($valid_user))
			returnError('Authentication Failed.');
	}

	// query data
	if ($_POST['command'] == 'query') {
		if ($_POST['query'] == 'synced_at') {
			$row = mysql_fetch_row(mysql_query("select synced_at from reghaabats where id = $reghaabat_id"));
			returnData(array('synced_at' => $row[0]));
		}
	}

	// store
	if ($_POST['command'] == 'store') {
		// extract records
		$logs = explode('|-|', $_POST['logs']);

		// data validation
		if (count($logs) != $_POST['count'])
			returnError(count($logs) .' rows was sent but '. $_POST['count'] .' was received');

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
			returnError(mysql_error());

		// copy files into directory
		if (count($_FILES) > 0) {
			foreach ($_FILES as $file)
				move_uploaded_file($file['tmp_name'], $fileDir . $reghaabat_id .'-'. $file['name']);
		}

		// update reghaabat synced_at
		$synced_at = $_POST['synced_at'];
		mysql_query("update reghaabats set synced_at = '$synced_at' where id = $reghaabat_id");
		returnData(array('synced_at' => $synced_at, 'count' => count($logs)));
	}

	returnError('Invalid Command.');
