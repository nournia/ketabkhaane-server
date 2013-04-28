<html lang="fa">
<head>
	<meta charset="utf-8" />
	<title>کتابخان‌ها</title>
	<link href="style/bootstrap.css" rel="stylesheet">
	<link href="style/main.css" rel="stylesheet">
	<link href="style/library.css" rel="stylesheet">
</head>
<body>
<div class="container">
	<div class="header">
		<img id="logo" src="style/images/reghaabat.jpg">
		<h2>کتاب‌خانه‌ها</h2>
	</div>
	<div class="list">
	<ul>
<?php
	require('server/begin.php');
	$result = mysql_query("select title, slug, image from libraries where slug != '' and title != ''");
	if (!$result) echo mysql_error();
	while($row = mysql_fetch_row($result))
		echo "<li class='well well-small'><img class='tiny' src='server/files.php?q={$row[2]}'><a href='library.php?{$row[1]}'>{$row[0]}</a></li>";

	require('server/end.php');
 ?>
	</ul>
	</div>
</body>
</html>