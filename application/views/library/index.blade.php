@section('content')
<div class="container">
<div class="header">
	<img id="logo" src="{{ $library->image ? "files/". $library->image : "" }}">
	<h2>{{ $library->title }}</h2>
</div>

<h3>نمودار ماهانه امانت کتاب‌ها</h3>
<div id ="stats-loading" class="alert loading">
	<p>در حال دریافت آمار امانت</p>	
</div>
<style type="text/css">
	#stats {
		height: 400px;
		margin-bottom: 50px;
	}


.node rect {
  cursor: pointer;
  fill: #fff;
  fill-opacity: .5;
  stroke: #3182bd;
  stroke-width: 1.5px;
}

.node text {
  font: 10px sans-serif;
  pointer-events: none;
}

#legend {
	float: left;
	margin-left: -180px;
}

.nvtooltip {
  position: absolute;
  background-color: rgba(255,255,255,1);
  padding: 10px;
  border: 1px solid #ddd;
  z-index: 10000;

  /*font-family: Arial;*/
  /*font-size: 13px;*/

  transition: opacity 500ms linear;
  -moz-transition: opacity 500ms linear;
  -webkit-transition: opacity 500ms linear;

  transition-delay: 500ms;
  -moz-transition-delay: 500ms;
  -webkit-transition-delay: 500ms;

  -moz-box-shadow: 4px 4px 8px rgba(0,0,0,.5);
  -webkit-box-shadow: 4px 4px 8px rgba(0,0,0,.5);
  box-shadow: 4px 4px 8px rgba(0,0,0,.5);

  -moz-border-radius: 10px;
  border-radius: 10px;

  pointer-events: none;

  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

.nvtooltip h4 {
  margin: 0;
  padding: 0;
  text-align: center;
}

.nvtooltip p {
  margin: 0;
  padding: 0;
  text-align: center;
}

.nvtooltip span {
  display: inline-block;
  margin: 2px 0;
}

.nvtooltip-pending-removal {
  position: absolute;
  pointer-events: none;
}




</style>
<div id="stats">
	<svg id="legend"></svg>
	<svg id="chart" style="direction: ltr"></svg>
</div>

<h3>فهرست کتاب‌ها</h3>
<div id="objects-loading" class="alert loading">
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
</div>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/underscore.js"></script>
<script type="text/javascript" src="js/backbone.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript" src="library.js"></script>

<script type="text/javascript" src="js/d3.js"></script>
<script type="text/javascript" src="js/nv.d3.js"></script>
<script type="text/javascript" src="js/jdate.js"></script>
<script type="text/javascript" src="branches.js"></script>


<script type="text/javascript">
$(document).ready(function() {
	var libraryId = {{ $library->id }};

	function updateFilters() {
		itemsView.setFilters($('#branch').val(), $('#query').val());
	}
	$('#query').keyup(updateFilters);
	$('#branch').change(updateFilters);

	// Object List
	$.ajax({
		url: 'data/object_list/'+ libraryId,
		dataType: 'json',
		success: function(data) {

	// 		// fill branch select
	// 		selector = $('#branch');
	// 		selector.empty();
	// 		selector.append('<option value="0">همه</option>');
	// 		_.each(data['branches'], function(item) {
	// 			selector.append('<option value="'+ item[0] +'">'+ item[1] +'</option>');
	// 		});

	// 		// fill items collection
	// 		items = [];
	// 		_.each(data['objects'], function(item) {
	// 			items.push({title: item[0], author: item[1], publication: item[2], type: item[3], branch: item[4], state: item[5]});
	// 		});
	// 		window.itemsView = new ItemsView(items);

	// 		// render
	// 		selector.val(0).change();
			$('#objects-loading').hide();
			$('#object-browser').fadeIn();
		},
	// 	error: function() {
	// 		$('#objects-loading').removeClass('loading').addClass('alert-error');
	// 		$('#objects-loading p').text('خطا در دریافت داده‌ها');
	// 	}
	});

	// Branch Stats
	$.ajax({
		url: 'data/branch_stats/'+ libraryId +'/5',
		dataType: 'json',
		success: function(data) {
			objects = {};

			dates = {};
			_.each(data['dates'], function(list, date) {
				date = JDate(date).toString('MMMM yy');
				
				_.each(list, function(branch) {
					if (! dates[date])
						dates[date] = {};
					if (! dates[date][branch])
						dates[date][branch] = 0;
					dates[date][branch] += 1;
				})		
			});
			branches = {}; tree = {name: 'همه', children: []};
			_.each(data['branches'], function(item) {
				branches[item[0]] = item[1];

				var root;
				parts = item[1].split(' - ');
				_.each(tree['children'], function(branch) {
					if (branch['name'] == parts[0]) {
						root = branch;
						root['values'].push(item[0]);
						return false;
					}
				});
				if (!root) {
					root = {name: parts[0], _children: [], values: []};
					tree['children'].push(root);
				}
				if (parts[1])
					root['_children'].push({name: parts[1], value: item[0]})
			});

			$('#stats-loading').hide();
			drawBranches(dates, branches, tree);
		},
		error: function() {
			$('#stats-loading').removeClass('loading').addClass('alert-error');
			$('#stats-loading p').text('خطا در دریافت داده‌ها');
		}
	});
});

mixpanel.track('Library', {library: "{{ $library->title }}"});
</script>
@endsection

@include('main')
