
var Item = Backbone.Model.extend({
	text: function() {
		return this.get('title') +' '+ this.get('author') +' '+ this.get('publication');
	}
});

var Items = Backbone.Collection.extend({
	model: Item,

	getRows: function(branch, query) {
		if (!query)
			return _(this.filter(function(item) {
				return item.get('branch') == branch;
			}));

		return _(this.filter(function(item) {
			return item.text().indexOf(query) >= 0;
		}));
	},
});

var ItemView = Backbone.View.extend({
	template: _.template('<tr class="<%= state == 0 ? "error" : "" %>"><td class="<%= type == 0 ? "book" : "cd" %>"></td><td><%= title %></td><td><%= author %></td><td><%= publication %></td></tr>'),
	render: function () {
		data = this.model.toJSON();
		this.setElement(this.template(data));
		return this;
	}
});

var ItemsView = Backbone.View.extend({
	el: $('#items'),

	initialize: function(options) {
		var that = this;
		this.collection = new Items();
		this.libraryId = options['libraryId'];
		this.query = '';
		this.branch = '';

		$.ajax({
			url: 'server/data.php?m=objects&o=list&i='+ this.libraryId,
			dataType: 'json',
			success: function(data){

				// fill branch select
				selector = $('#branch');
				selector.empty();
				_.each(data['branches'], function(item) {
					selector.append('<option value="'+ item[0] +'">'+ item[1] +' - '+ item[2] +'</option>');
				});

				// fill items collection
				_.each(data['objects'], function(item) {
					item = {title: item[0], author: item[1], publication: item[2], type: item[3], branch: item[4], state: item[5]};
					that.collection.add(item);
				});

				// render
				selector.val(111).change();
				$('.alert').hide();
				$('#object-browser').fadeIn();
			},
			error: function() {
				console.log('Data Error');
			}
		});
	},
	setFilters: function(branch, query) {
		if (query.length < 3)
			query = '';
		if (branch != this.branch || query != this.query) {
			this.branch = branch;
			this.query = query;
			this.render();
		}
	},
	render: function() {
		var tbody = this.$el.find('tbody');
		tbody.empty();

		items = this.collection.getRows(this.branch, this.query).first(1000);
		_.each(items, function (item) {
			var itemView = new ItemView({model: item});
			tbody.append(itemView.render().el);
		});

		tbody.find('tr.error').tooltip({placement: 'left', title: 'امانت داده شده'});
	}
});
