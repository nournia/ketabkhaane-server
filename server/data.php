<?php ob_start('ob_gzhandler'); ?>
<?php require('begin.php') ?>
<?php
	function getResults($query) {
		$result = mysql_query($query);
		$data = array();
		while($row = mysql_fetch_row($result))
			$data[] = $row;
		return $data;
	}

	$library_id = 1;
	$objects = getResults("
		select objects.title, authors.title as author, publications.title as publication, objects.type_id, belongs.branch_id, belongs.cnt - ifnull(_borrowed.cnt, 0) > 0 as cnt from objects
		inner join belongs on objects.id = belongs.object_id
		left join authors on objects.author_id = authors.id
		left join publications on objects.publication_id = publications.id
		left join _borrowed on objects.id = _borrowed.object_id and belongs.library_id = _borrowed.library_id
		where belongs.library_id = $library_id
	");

	$branches = getResults("
		select branches.id, roots.title, branches.title from branches
		inner join (select distinct branch_id from belongs where library_id = $library_id) as _belongs on branches.id = _belongs.branch_id
		inner join roots on branches.root_id = roots.id
		order by branches.id
	");

	$data = array('branches' => $branches, 'objects' => $objects);
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
?>
<?php require('end.php') ?>
