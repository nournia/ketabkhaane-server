<?php

function updateLicense($id) {
	DB::query('update libraries set license = sha1(if(slug is null, concat("x", id), concat("a", id))) where id = ?', array($id));
	$row = DB::query('select license from libraries where id = ?', array($id));
	return $row[0]->license;
}
function response($state, $data) {
	$data['command'] = Input::get('command');
	$data['state'] = $state;
	return Response::json($data);
}
function returnError($message) {
	return response('error', array('message' => $message));
}
function returnData($data) {
	return response('ok', $data);
}

class Backend_Controller extends Base_Controller {
	
	public function auth()
	{
		$library_id = Input::get('id');
		$row = DB::query('select license from libraries where id = ?', array($library_id));
		if ($row) {
			$license = $row[0]->license;

			if(sha1($library_id .'|x|'. $license) == Input::get('key'))
				return $library_id;
		}

		// returnError('Authentication Failed.');
	}

	public function action_register()
	{
		$id = DB::table('libraries')->insert_get_id(array('title' => ''));
		$license = updateLicense($id);
		return response('ok', array('id' => $id, 'license' => $license));
	}

	public function action_query()
	{
		$library_id = $this->auth();
		
		if (Input::get('query') == 'synced_at') {
			$row = DB::query('select synced_at, slug from libraries where id = ?', array($library_id));
			if ($row[0]->slug && $row[0]->slug[0] == '_') {
				DB::query('update libraries set slug = substr(slug, 2) where id = ?', array($library_id));
				$license = updateLicense($library_id);
				return returnData(array('synced_at' => $row[0]->synced_at, 'license' => $license));
			}
			else
				return returnData(array('synced_at' => $row[0]->synced_at));
		}
	}

	public function action_store()
	{
		$library_id = $this->auth();
		$event_tables = array('answers', 'borrows', 'open_scores', 'permissions', 'supports', 'belongs', 'transactions');
		$entity_tables = array('roots', 'branches', 'users', 'authors', 'publications', 'objects', 'matches', 'files');

		function parseRow($data) {
			$content = strpos($data, '|');
			$values = explode(',', substr($data, 0, $content-1));
			$values[] = substr($data, $content+1);
			return $values;
		}
		function storeData($library_id, $command, $table, $values) {
			$event_tables = array('answers', 'borrows', 'open_scores', 'permissions', 'supports', 'belongs', 'transactions');
			$entity_tables = array('roots', 'branches', 'users', 'authors', 'publications', 'objects', 'matches', 'files');

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

				DB::query($query);
			}
		}

		// extract records
		$logs = explode('|-|', Input::get('xlogs'));

		// data validation
		if (count($logs) != Input::get('count'))
			returnError(Input::get('count') .' rows was sent but '.  count($logs) .' was received');

		// write logs to file
		$logFile = fopen(path('storage') .'files/'.$library_id.'.log', 'a');

		// insert data into db in groups
		$command = ''; $table = ''; $values = array();
		foreach ($logs as $row) {
			// store row before parsing it
			fwrite($logFile, $row. "\n");

			// set new value
			$row = parseRow($row);

			if ($row[0] == 'library') {
				$data = explode(',', str_replace('null', '""', $row[5]));
				DB::query("update libraries set title = {$data[0]}, description = {$data[1]}, started_at = {$data[2]}, image = {$data[3]}, version = {$data[4]} where id = $library_id");
				continue;
			}

			if ($row[1] == 'delete')
				$value = $row[2];
			else if (in_array($row[0], $event_tables))
				$value = "($library_id,{$row[2]},{$row[5]})";
			else
				$value = "({$row[2]},{$row[5]})";

			// store values
			if ($table == $row[0] && $command == $row[1] && count($values) < 50)
				$values[] = $value;
			else {
				storeData($library_id, $command, $table, $values);

				$command = $row[1];
				$table = $row[0];
				$values = array($value);
			}
		}
		storeData($library_id, $command, $table, $values);

		// copy files into directory
		if (count($_FILES) > 0) {
			foreach ($_FILES as $file)
				move_uploaded_file($file['tmp_name'], path('storage') .'files/'.$file['name']);
		}

		// update reghaabat synced_at
		$synced_at = Input::get('synced_at');
		DB::query('update libraries set synced_at = ? where id = ?', array($synced_at, $library_id));
		return returnData(array('synced_at' => $synced_at, 'count' => count($logs)));
	}
}
