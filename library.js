
var Item = Backbone.Model.extend({
	text: function() {
		return this.get('title') +' '+ this.get('author') +' '+ this.get('publication');
	}
});

var Items = Backbone.Collection.extend({
	model: Item,

	query: function(text) {
		if (!text)
			return this.first(20);

		return _(this.filter(function(item) {
			return item.text().indexOf(text) >= 0;
		})).first(20);
	}
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

	initialize: function() {
		var that = this;
		this.collection = new Items();
		this.query = '';

		$.ajax({
			url: 'server/data.php',
			dataType: 'json',
			success: function(data){
				_.each(data, function(item) {
					item = {title: item[0], author: item[1], publication: item[2], type: item[3], state: item[4]};
					that.collection.add(item);
				});

				that.render();
			},
			error: function() {
				console.log('Data Error');
			}
		});
	},
	setQuery: function(query) {
		this.query = query;
		this.render();
	},
	render: function() {
		var tbody = this.$el.find('tbody');
		tbody.empty();

		_.each(this.collection.query(this.query), function (item) {
			var itemView = new ItemView({model: item});
			tbody.append(itemView.render().el);
		});
	}
});

var AppView = Backbone.View.extend({
	initialize: function() {
		this.itemsView = new ItemsView();
		$('#query').bind('keyup', function() {
			app.itemsView.setQuery($(this).val());
		});
	}
});

$(document).ready(function() {
	window.app = new AppView();
});
