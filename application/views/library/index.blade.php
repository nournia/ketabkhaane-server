@section('content')
<div class="header">
	<img id="logo" src="{{ $library->image ? "files/". $library->image : "" }}">
	<h2>{{ $library->title }}</h2>
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
			<input id="query" type="text" class="search-query input-medium">
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
	window.itemsView = new ItemsView({libraryId: {{ $library->id }}});
	
	function updateFilters() {
		itemsView.setFilters($('#branch').val(), $('#query').val());
	}
	$('#query').keyup(updateFilters);
	$('#branch').change(updateFilters);
});
</script>
@endsection

@include('main')
