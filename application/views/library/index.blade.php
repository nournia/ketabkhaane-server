@section('content')
<div class="header">
	<img id="logo" src="{{ $library->image ? "files/". $library->image : "" }}">
	<h2>{{ $library->title }}</h2>
</div>

<div class="alert loading">
	<p>در حال دریافت فهرست کتاب‌ها</p>	
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
	var libraryId = {{ $library->id }};

	function updateFilters() {
		itemsView.setFilters($('#branch').val(), $('#query').val());
	}
	$('#query').keyup(updateFilters);
	$('#branch').change(updateFilters);

	$.ajax({
		url: 'data/object_list/'+ libraryId,
		dataType: 'json',
		success: function(data) {

			// fill branch select
			selector = $('#branch');
			selector.empty();
			selector.append('<option value="0">همه</option>');
			_.each(data['branches'], function(item) {
				selector.append('<option value="'+ item[0] +'">'+ item[1] +'</option>');
			});

			// fill items collection
			items = [];
			_.each(data['objects'], function(item) {
				items.push({title: item[0], author: item[1], publication: item[2], type: item[3], branch: Number(item[4]), state: item[5]});
			});
			window.itemsView = new ItemsView(items);

			// render
			selector.val(0).change();
			$('.alert').hide();
			$('#object-browser').fadeIn();
		},
		error: function() {
			$('.alert').removeClass('loading').addClass('alert-error');
			$('.alert p').text('خطا در دریافت داده‌ها');
		}
	});
});
</script>
@endsection

@include('main')
