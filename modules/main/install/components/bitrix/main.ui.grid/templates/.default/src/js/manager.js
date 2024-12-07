(function() {
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

		push(id, instance)
		{
			if (BX.type.isNotEmptyString(id) && instance)
			{
				const object = {
					id,
					instance,
					old: null,
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

		getById(id)
		{
			const result = this.data.filter((current) => {
				return (current.id === id) || (current.id.replace('main_grid_', '') === id);
			});

			return result.length === 1 ? result[0] : null;
		},

		getInstanceById(id)
		{
			const result = this.getById(id);

			return BX.type.isPlainObject(result) ? result.instance : null;
		},

		reload(id, url)
		{
			const instance = this.getInstanceById(id);
			if (instance)
			{
				instance.reload(url);
			}
		},

		getDataIndex(id)
		{
			let result = null;
			this.data.forEach((item, index) => {
				if (item.id === id)
				{
					result = index;
				}
			});

			return result;
		},

		destroy(id)
		{
			if (BX.type.isNotEmptyString(id))
			{
				const grid = this.getInstanceById(id);

				if (grid instanceof BX.Main.grid)
				{
					grid.destroy();
					const index = this.getDataIndex(id);

					if (index !== null)
					{
						delete this.data[index];
					}
				}
			}
		},
	};
})();
