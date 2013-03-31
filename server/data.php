<?php require('begin.php') ?>
<?php
	$library_id = 1;
	$query = 'select objects.title, authors.title as author, publications.title as publication, objects.type_id, objects.cnt - ifnull(_borrowed.cnt, 0) > 0 as cnt from objects
		left join authors on objects.author_id = authors.id and objects.library_id = authors.library_id
		left join publications on objects.publication_id = publications.id and objects.library_id = publications.library_id
		left join _borrowed on objects.id = _borrowed.object_id and objects.library_id = _borrowed.library_id
		where objects.library_id = '. $library_id;

	$result = mysql_query($query);
	$data = array();
	while($row = mysql_fetch_row($result))
		$data[] = $row;
	echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
?>
<?php require('end.php') ?>
