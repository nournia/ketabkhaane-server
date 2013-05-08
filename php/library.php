<?php require('begin.php') ?>
<?php
	$args = array_keys($_GET);
	if (isset($args[0])) {
		$result = mysql_query("select id, title, image, synced_at from libraries where slug = '". mysql_real_escape_string($args[0]) ."'");
		if ($result && $row = mysql_fetch_row($result))
			$data = $row;
	}
?>
<?php if (! isset($data)) { ?>
	<p></p>
	<div class="alert alert-error">
		<p>صفحه مورد نظر پیدا نشد.</p>	
	</div>
<?php die; }; ?>
	<div class="header">
		<img id="logo" src="<?php echo 'server/files.php?q='. $data[2] ?>">
		<h2><?php echo $data[1] ?></h2>
	</div>
	<div class="alert">
		<p id="loading">در حال دریافت فهرست کتاب‌ها</p>	
	</div>
	<div id="object-browser">
		<div class="well well-small form-search">
			<div class="branch">
				<span>گروه: </span><select id="branch"></select>
			</div>
			<div class="title input-prepend">
				<input id="query" type="text" class="search-query span2">
				<button class="btn">جستجو</button>
			</div>
		</div>
		<div id="items">
			<table class="table table-striped">
				<thead>
					<tr><th style="width: 4%"></th><th>عنوان</th><th style="width: 25%">نویسنده</th><th style="width: 25%">ناشر</th></tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/underscore.js"></script>
<script type="text/javascript" src="js/backbone.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript" src="library.js"></script>

<script type="text/javascript">
$(document).ready(function() {
	window.itemsView = new ItemsView({libraryId: <?php echo $data[0] ?>});
	
	function updateFilters() {
		itemsView.setFilters($('#branch').val(), $('#query').val());
	}
	$('#query').keyup(updateFilters);
	$('#branch').change(updateFilters);
});
</script>
<?php require('end.php') ?>
