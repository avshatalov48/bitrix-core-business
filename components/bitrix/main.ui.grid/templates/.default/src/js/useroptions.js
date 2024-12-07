(function() {
	'use strict';

	BX.namespace('BX.Grid');

	/**
	 * BX.Grid.UserOptions
	 * @param {BX.Main.grid} parent
	 * @param {Object} userOptions
	 * @param {Object} userOptionsActions
	 * @param {String} url
	 * @constructor
	 */
	BX.Grid.UserOptions = function(parent, userOptions, userOptionsActions, url)
	{
		this.options = null;
		this.actions = null;
		this.parent = null;
		this.url = null;
		this.init(parent, userOptions, userOptionsActions, url);
	};

	BX.Grid.UserOptions.prototype = {
		init(parent, userOptions, userOptionsActions, url)
		{
			this.url = url;
			this.parent = parent;

			try
			{
				this.options = eval(userOptions);
			}
			catch
			{
				console.warn('BX.Grid.UserOptions.init: Failed parse user options json string');
			}

			try
			{
				this.actions = eval(userOptionsActions);
			}
			catch
			{
				console.warn('BX.Grid.UserOptions.init: Failed parse user options actions json string');
			}
		},

		getCurrentViewName()
		{
			const options = this.getOptions();

			return 'current_view' in options ? options.current_view : null;
		},

		getViewsList()
		{
			const options = this.getOptions();

			return 'views' in options ? options.views : {};
		},

		getCurrentOptions()
		{
			const name = this.getCurrentViewName();
			const views = this.getViewsList();
			let result = null;

			if (name in views)
			{
				result = views[name];
			}

			if (!BX.type.isPlainObject(result))
			{
				result = {};
			}

			return result;
		},

		getUrl(action)
		{
			return BX.util.add_url_param(this.url, {
				GRID_ID: this.parent.getContainerId(),
				bxajaxid: this.parent.getAjaxId(),
				action,
			});
		},

		getOptions()
		{
			return this.options || {};
		},

		getActions()
		{
			return this.actions;
		},

		getAction(name)
		{
			let action = null;

			try
			{
				action = this.getActions()[name];
			}
			catch
			{
				action = null;
			}

			return action;
		},

		update(newOptions)
		{
			this.options = newOptions;
		},

		setColumns(columns, callback)
		{
			const options = this.getCurrentOptions();

			if (BX.type.isPlainObject(options))
			{
				options.columns = columns.join(',');

				this.save(this.getAction('GRID_SET_COLUMNS'), { columns: options.columns }, callback);
			}

			return this;
		},

		setColumnsNames(columns, callback)
		{
			const options = { view_id: 'default' };

			if (BX.type.isPlainObject(options))
			{
				options.custom_names = columns;

				this.save(this.getAction('SET_CUSTOM_NAMES'), options, callback);
			}

			return this;
		},

		setColumnSizes(sizes, expand)
		{
			this.save(this.getAction('GRID_SET_COLUMN_SIZES'), { sizes, expand });
		},

		reset(forAll, callback)
		{
			let data = {};

			if (forAll)
			{
				data = {
					view_id: 'default',
					set_default_settings: 'Y',
					delete_user_settings: 'Y',
					view_settings: this.getCurrentOptions(),
				};
			}

			this.save(this.getAction('GRID_RESET'), data, callback);
		},

		setSort(by, order, callback)
		{
			if (by && order)
			{
				this.save(this.getAction('GRID_SET_SORT'), { by, order }, callback);
			}

			return this;
		},

		setPageSize(pageSize, callback)
		{
			if (BX.type.isNumber(parseInt(pageSize)))
			{
				this.save(this.getAction('GRID_SET_PAGE_SIZE'), { pageSize }, callback);
			}
		},

		setExpandedRows(ids, callback)
		{
			BX.type.isArray(ids) && this.save(this.getAction('GRID_SET_EXPANDED_ROWS'), { ids }, callback);
		},

		setCollapsedGroups(ids, callback)
		{
			BX.type.isArray(ids) && this.save(this.getAction('GRID_SET_COLLAPSED_GROUPS'), { ids }, callback);
		},

		resetExpandedRows()
		{
			this.save(this.getAction('GRID_RESET_EXPANDED_ROWS'), {});
		},

		saveForAll(callback)
		{
			this.save(
				this.getAction('GRID_SAVE_SETTINGS'),
				{
					view_id: 'default',
					set_default_settings: 'Y',
					delete_user_settings: 'Y',
					view_settings: this.getCurrentOptions(),
				},
				callback,
			);
		},

		batch(data, callback)
		{
			this.save(this.getAction('GRID_SAVE_BATH'), { bath: data }, callback);
		},

		save(action, data, callback)
		{
			const self = this;
			BX.ajax.post(
				this.getUrl(action),
				data,
				(res) => {
					try
					{
						res = JSON.parse(res);
						if (!res.error)
						{
							self.update(res);
							if (BX.type.isFunction(callback))
							{
								callback(res);
							}

							BX.onCustomEvent(self.parent.getContainer(), 'Grid::optionsChanged', [self.parent]);
						}
					}
					catch
					{}
				},
			);
		},
	};
})();
