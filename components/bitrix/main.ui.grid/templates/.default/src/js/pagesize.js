;(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.Pagesize = function(parent)
	{
		this.parent = null;
		this.init(parent);
	};

	BX.Grid.Pagesize.prototype = {
		init: function(parent)
		{
			this.parent = parent;
			BX.addCustomEvent('Dropdown::change', BX.proxy(this.onChange, this));
		},

		destroy: function()
		{
			BX.removeCustomEvent('Dropdown::change', BX.proxy(this.onChange, this));
		},

		onChange: function(id, event, item, dataValue, value)
		{
			var self = this;

			if (id === this.parent.getContainerId() + '_' + this.parent.settings.get('pageSizeId'))
			{
				if (value >= 0)
				{
					this.parent.tableFade();
					this.parent.getUserOptions().setPageSize(value, function() {
						self.parent.reloadTable();
						BX.onCustomEvent(self.parent.getContainer(), 'Grid::pageSizeChanged', [self.parent]);
					});
				}
			}
		}
	};
})();