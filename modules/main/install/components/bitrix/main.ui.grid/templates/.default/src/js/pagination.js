(function() {
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
		init(parent)
		{
			this.parent = parent;
		},

		getParent()
		{
			return this.parent;
		},

		getContainer()
		{
			if (!this.container)
			{
				this.container = BX.Grid.Utils.getByClass(this.getParent().getContainer(), this.getParent().settings.get('classPagination'), true);
			}

			return this.container;
		},

		getLinks()
		{
			const self = this;
			const result = BX.Grid.Utils.getByTag(this.getContainer(), 'a');

			this.links = [];

			if (result)
			{
				this.links = result.map((current) => {
					return new BX.Grid.Element(current, self.getParent());
				});
			}

			return this.links;
		},

		getLink(node)
		{
			let result = null;
			let filter;

			if (BX.type.isDomNode(node))
			{
				filter = this.getLinks().filter((current) => {
					return node === current.getNode();
				});

				if (filter.length > 0)
				{
					result = filter[0];
				}
			}

			return result;
		},
	};
})();
