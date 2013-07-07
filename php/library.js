
var Item = Backbone.Model.extend({
	text: function() {
		return this.get('title') +' '+ this.get('author') +' '+ this.get('publication');
	}
});

var Items = Backbone.Collection.extend({
	model: Item,

	getRows: function(branch, query) {
		if (!query) {
			if (branch == 0)
				return this.toArray();
			return this.where({branch: Number(branch)})
		}

		return _(this.filter(function(item) {
			return item.text().indexOf(query) >= 0;
		})).toArray();
	},
});

var ItemView = Backbone.View.extend({
	template: _.template('<tr class="<%= state == 0 ? "warning" : "" %>"><td class="<%= type == 0 ? "book" : "cd" %>"></td><td><%= title %></td><td><%= author %></td><td><%= publication %></td></tr>'),
	render: function () {
		data = this.model.toJSON();
		this.setElement(this.template(data));
		return this;
	}
});

var ItemsView = Backbone.View.extend({
	el: $('#items'),

	initialize: function(data) {
		var that = this;
		this.collection = new Items();
		this.collection.add(data);
		this.query = '';
		this.branch = '';
		this.show = 100;
	},
	setFilters: function(branch, query) {
		if (query.length < 3)
			query = '';
		if (branch != this.branch || query != this.query) {
			this.branch = branch;
			this.query = query;
			this.show = 100;
			this.render();
		}
	},
	render: function() {
		items = this.collection.getRows(this.branch, this.query);

		var tbody = this.$el.find('tbody');
		tbody.empty();
		_.each(_.first(items, this.show), function (item) {
			var itemView = new ItemView({model: item});
			tbody.append(itemView.render().el);
		});
		tbody.find('tr.warning').tooltip({placement: 'left', title: 'امانت داده شده', container: 'body'});

		this.$el.find('#continue').remove();
		if (items.length > this.show) {
			this.$el.append('<button id="continue" class="btn btn-small" rel="'+ this.show +'">ادامه فهرست</button>');
			this.$el.find('#continue').click(function() {
				itemsView.show = Number($(this).attr('rel')) + 100;
				itemsView.render();
			});
		}
	}
});
