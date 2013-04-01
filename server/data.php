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
	$objects = getResults("select objects.title, authors.title as author, publications.title as publication, objects.type_id, objects.branch_id, objects.cnt - ifnull(_borrowed.cnt, 0) > 0 as cnt from objects
		left join authors on objects.author_id = authors.id and objects.library_id = authors.library_id
		left join publications on objects.publication_id = publications.id and objects.library_id = publications.library_id
		left join _borrowed on objects.id = _borrowed.object_id and objects.library_id = _borrowed.library_id
		where objects.library_id = $library_id");

	$branches = getResults("select branches.id, roots.title, branches.title from branches
		inner join roots on branches.root_id = roots.id and branches.library_id = roots.library_id
		where branches.library_id = $library_id order by branches.id");

	$data = array('branches' => $branches, 'objects' => $objects);
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
?>
<?php require('end.php') ?>
