;(function() {
	'use strict';

	BX.namespace('BX.Main');

	/**
	 * Works with grid instances
	 * @type {{data: Array, push: BX.Main.gridManager.push, getById: BX.Main.gridManager.getById}}
	 */

	if (BX.Main.gridManager)
	{
		return;
	}

	BX.Main.gridManager = {
		data: [],

		push: function(id, instance)
		{
			if (BX.type.isNotEmptyString(id) && instance)
			{
				var object = {
					id: id,
					instance: instance,
					old: null
				};

				if (this.getById(id) === null)
				{
					this.data.push(object);
				}
				else
				{
					this.data[0] = object;
				}
			}
		},

		getById: function(id)
		{
			var result = this.data.filter(function(current) {
				return (current.id === id) || (current.id.replace('main_grid_', '') === id);
			});

			return result.length === 1 ? result[0] : null;
		},

		getInstanceById: function(id)
		{
			var result = this.getById(id);
			return BX.type.isPlainObject(result) ? result["instance"] : null;
		},

		reload: function(id, url)
		{
			var instance = this.getInstanceById(id);
			if(instance)
			{
				instance.reload(url);
			}
		},

		getDataIndex: function(id)
		{
			var result = null;
			this.data.forEach(function(item, index) {
				if (item.id === id)
				{
					result = index;
				}
			});

			return result;
		},

		destroy: function(id)
		{
			if (BX.type.isNotEmptyString(id))
			{
				var grid = this.getInstanceById(id);

				if (grid instanceof BX.Main.grid)
				{
					grid.destroy();
					var index = this.getDataIndex(id);

					if (index !== null)
					{
						delete this.data[index];
					}
				}
			}
		}
	};
})();