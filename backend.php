<?php require('begin.php') ?>
<?php
	function updateLicense($id) {
		$days = 100;
		mysql_query("update reghaabats set license = sha1(adddate(now(), $days)) where id = $id");
		return mysql_fetch_row(mysql_query("select license from reghaabats where id = $id"))[0];
	}

	// Register
	if (isset($_POST['register'])) {
		mysql_query('insert into reghaabats (title) values ("")');
		$id = mysql_insert_id();
		$license = updateLicense($id);
		echo $id .','. $license;
	}

	// Receive

?>
<?php require('end.php') ?>