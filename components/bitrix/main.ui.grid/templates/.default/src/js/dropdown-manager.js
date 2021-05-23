;(function() {
	'use strict';

	BX.namespace('BX.Main');

	BX.Main.dropdownManager = {
		dropdownClass: 'main-dropdown',
		data: {},
		init: function()
		{
			var self = this;
			var result;
			var onLoadItems;
			var items;

			BX.bind(document, 'click', BX.delegate(function(event) {
				if (BX.hasClass(event.target, this.dropdownClass))
				{
					event.preventDefault();

					result = this.getById(event.target.id);

					if (result && result.dropdown === event.target)
					{
						self.push(event.target.id, this.getById(event.target.id));
					}
					else
					{
						self.push(event.target.id, new BX.Main.dropdown(event.target));
					}
				}
			}, this));

			onLoadItems = BX.Grid.Utils.getByClass(document.body, this.dropdownClass);

			if (BX.type.isArray(onLoadItems))
			{
				onLoadItems.forEach(function(current) {
					result = self.getById(current.id);
					try {
						items = eval(BX.data(current, 'items'));
					} catch (err) {}

					BX.onCustomEvent(window, 'Dropdown::load', [current.id, {}, null, BX.type.isArray(items) && items.length ? items[0] : [], BX.data(current, 'value')]);
				});
			}

		},

		push: function(id, instance)
		{
			this.data[id] = instance;
		},

		getById: function(id)
		{
			return (id in this.data) ? this.data[id] : null;
		}
	};
})();