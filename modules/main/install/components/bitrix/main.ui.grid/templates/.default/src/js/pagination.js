;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.Pagination
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.Pagination = function(parent)
	{
		this.parent = null;
		this.container = null;
		this.links = null;
		this.init(parent);
	};

	BX.Grid.Pagination.prototype = {
		init: function(parent)
		{
			this.parent = parent;
		},

		getParent: function()
		{
			return this.parent;
		},

		getContainer: function()
		{
			if (!this.container)
			{
				this.container = BX.Grid.Utils.getByClass(this.getParent().getContainer(), this.getParent().settings.get('classPagination'), true);
			}

			return this.container;
		},

		getLinks: function()
		{
			var self = this;
			var result = BX.Grid.Utils.getByTag(this.getContainer(), 'a');

			this.links = [];

			if (result)
			{
				this.links = result.map(function(current) {
					return new BX.Grid.Element(current, self.getParent());
				});
			}

			return this.links;
		},

		getLink: function(node)
		{
			var result = null;
			var filter;

			if (BX.type.isDomNode(node))
			{
				filter = this.getLinks().filter(function(current) {
					return node === current.getNode();
				});

				if (filter.length)
				{
					result = filter[0];
				}
			}

			return result;
		}
	};
})();