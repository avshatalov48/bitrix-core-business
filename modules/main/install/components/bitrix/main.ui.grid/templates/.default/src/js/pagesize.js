(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.Pagesize = function(parent)
	{
		this.parent = null;
		this.init(parent);
	};

	BX.Grid.Pagesize.prototype = {
		init(parent)
		{
			this.parent = parent;
			BX.addCustomEvent('Dropdown::change', BX.proxy(this.onChange, this));
		},

		destroy()
		{
			BX.removeCustomEvent('Dropdown::change', BX.proxy(this.onChange, this));
		},

		onChange(id, event, item, dataValue, value)
		{
			const self = this;

			if (id === `${this.parent.getContainerId()}_${this.parent.settings.get('pageSizeId')}` && value >= 0)
			{
				this.parent.tableFade();
				this.parent.getUserOptions().setPageSize(value, () => {
					self.parent.reloadTable();
					BX.onCustomEvent(self.parent.getContainer(), 'Grid::pageSizeChanged', [self.parent]);
				});
			}
		},
	};
})();
