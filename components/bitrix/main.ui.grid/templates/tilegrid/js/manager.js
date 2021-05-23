;(function() {
	'use strict';

	BX.namespace('BX.Main');

	/**
	 * Works with tileGrid instances
	 * @type {{data: Array, push: BX.Main.tileGridManager.push, getById: BX.Main.tileGridManager.getById}}
	 */

	if (BX.Main.tileGridManager)
	{
		return;
	}

	BX.Main.tileGridManager = {
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
				return (current.id === id);
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
				var tileGrid = this.getInstanceById(id);

				if (tileGrid instanceof BX.Main.TileGrid)
				{
					tileGrid.destroy();
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