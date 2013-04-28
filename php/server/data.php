<?php require('begin.php') ?>
<?php
	header('Cache-Control: max-age='. $cache);
	header('Expires: '. gmdate('D, d M Y H:i:s', time() + $cache) .' GMT');
	ob_start('ob_gzhandler');
?>
<?php
function getResults($query) {
	$result = mysql_query($query);
	$data = array();
	if ($result)
		while($row = mysql_fetch_row($result))
			$data[] = $row;
	return $data;
}
function response($data) {
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	require('end.php'); die;
}
function arg($name) {
	if (!isset($_GET[$name]))
		response(array('error' => 'Invalid Arguments'));
	return $_GET[$name];
}

$mode = arg('m'); $library_id = arg('i'); $operation = arg('o');

if ($mode == 'objects' && $operation == 'list') {
	$objects = getResults("
		select objects.title, authors.title as author, publications.title as publication, objects.type_id, belongs.branch_id, belongs.cnt - ifnull(_borrowed.cnt, 0) > 0 as cnt from objects
		inner join belongs on objects.id = belongs.object_id
		left join authors on objects.author_id = authors.id
		left join publications on objects.publication_id = publications.id
		left join _borrowed on objects.id = _borrowed.object_id and belongs.library_id = _borrowed.library_id
		where belongs.library_id = $library_id
	");

	$branches = getResults("
		select branches.id, if(branches.title != '', concat(roots.title , ' - ', branches.title), roots.title) from branches
		inner join (select distinct branch_id from belongs where library_id = $library_id) as _belongs on branches.id = _belongs.branch_id
		inner join roots on branches.root_id = roots.id
		order by branches.id
	");

	response(array('branches' => $branches, 'objects' => $objects));
}
else if ($mode == 'matches' && $operation == 'list') {
	$matches = getResults("
		select matches.id, matches.title, ageclasses.title, ifnull(types.title, categories.title) as kind, if(matches.category_id is null, trim(left(matches.content, 5)), '-') as answers_ratio from matches
		left join objects on matches.object_id = objects.id
		left join types on objects.type_id = types.id
		left join ageclasses on matches.ageclass = ageclasses.id
		left join categories on matches.category_id = categories.id
		where matches.id div 100000 != $library_id
	");

	response(array('matches' => $matches, 'operation' => $operation));
}
else if ($mode == 'matches' && $operation == 'items') {
	$items = arg('q');
	$objects = array(); $authors = array(); $publications = array(); $contents = array(); $files = array();

	// matches
	$matches = getResults("select * from matches where id in ($items)");
	foreach ($matches as $match) {
		if ($match[4]) $objects[] = $match[4];
		if ($match[6]) $contents[] = $match[6];
	}

	// files
	foreach ($contents as $content) {
		preg_match_all('/src="([^"]+)"/', $content, $cases);
		foreach($cases[1] as $case) {
			$filename = explode('.', $case);
			$files[] = $filename[0];
		}
	}
	if ($files)
		$files = getResults('select * from files where id in ('. join(',', $files) .')');

	// objects, authors and publications
	if ($objects) {
		$objects = getResults('select * from objects where id in ('. join(',', $objects) .')');
		foreach ($objects as $object) {
			if ($object[1]) $authors[] = $object[1];
			if ($object[2]) $publications[] = $object[2];
		}

		if ($authors)
			$authors = getResults('select * from authors where id in ('. join(',', $authors) .')');

		if ($publications)
			$publications = getResults('select * from publications where id in ('. join(',', $publications) .')');
	}

	response(array('matches' => $matches, 'files' => $files, 'objects' => $objects, 'authors' => $authors, 'publications' => $publications, 'operation' => $operation));
}
else if ($mode == 'users' && $operation == 'login') {
	$nationalId = arg('i'); $password = arg('p');
	$users = getResults("select * from users where national_id = $nationalId and upassword = '$password'");
	if (count($users) == 1)
		response(array('user' => $users, 'operation' => $operation));

	response(array('user' => array(), 'operation' => $operation));
}
