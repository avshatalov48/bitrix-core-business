;(function() {
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
		init: function(parent, userOptions, userOptionsActions, url)
		{
			this.url = url;
			this.parent = parent;

			try {
				this.options = eval(userOptions);
			} catch(err) {
				console.warn('BX.Grid.UserOptions.init: Failed parse user options json string');
			}

			try {
				this.actions = eval(userOptionsActions);
			} catch(err) {
				console.warn('BX.Grid.UserOptions.init: Failed parse user options actions json string');
			}
		},

		getCurrentViewName: function()
		{
			var options = this.getOptions();

			return 'current_view' in options ? options.current_view : null;
		},

		getViewsList: function()
		{
			var options = this.getOptions();

			return 'views' in options ? options.views : {};
		},

		getCurrentOptions: function()
		{
			var name = this.getCurrentViewName();
			var views = this.getViewsList();
			var result = null;

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

		getUrl: function(action)
		{
			return BX.util.add_url_param(this.url, {
				GRID_ID: this.parent.getContainerId(),
				bxajaxid: this.parent.getAjaxId(),
				action: action
			});
		},

		getOptions: function()
		{
			return this.options || {};
		},

		getActions: function()
		{
			return this.actions;
		},

		getAction: function(name)
		{
			var action = null;

			try {
				action = this.getActions()[name];
			} catch (err) {
				action = null;
			}

			return action;
		},

		update: function(newOptions)
		{
			this.options = newOptions;
		},

		setColumns: function(columns, callback)
		{
			var options = this.getCurrentOptions();

			if (BX.type.isPlainObject(options))
			{
				options.columns = columns.join(',');

				this.save(this.getAction('GRID_SET_COLUMNS'), {columns: options.columns}, callback);
			}

			return this;
		},

		setColumnsNames: function(columns, callback)
		{
			var options = {view_id: 'default'};

			if (BX.type.isPlainObject(options))
			{
				options.custom_names = columns;

				this.save(this.getAction('SET_CUSTOM_NAMES'), options, callback);
			}

			return this;
		},

		setColumnSizes: function(sizes, expand)
		{
			this.save(this.getAction('GRID_SET_COLUMN_SIZES'), {sizes: sizes, expand: expand});
		},

		reset: function(forAll, callback)
		{
			var data = {};

			if (!!forAll)
			{
				data = {
					view_id: 'default',
					set_default_settings: 'Y',
					delete_user_settings: 'Y',
					view_settings: this.getCurrentOptions()
				};
			}

			this.save(this.getAction('GRID_RESET'), data, callback);
		},

		setSort: function(by, order, callback)
		{
			if (by && order)
			{
				this.save(this.getAction('GRID_SET_SORT'), {by: by, order: order}, callback);
			}

			return this;
		},

		setPageSize: function(pageSize, callback)
		{
			if (BX.type.isNumber(parseInt(pageSize)))
			{
				this.save(this.getAction('GRID_SET_PAGE_SIZE'), {pageSize: pageSize}, callback);
			}
		},

		setExpandedRows: function(ids, callback)
		{
			BX.type.isArray(ids) && this.save(this.getAction('GRID_SET_EXPANDED_ROWS'), {ids: ids}, callback);
		},

		setCollapsedGroups: function(ids, callback)
		{
			BX.type.isArray(ids) && this.save(this.getAction('GRID_SET_COLLAPSED_GROUPS'), {ids: ids}, callback);
		},

		resetExpandedRows: function()
		{
			this.save(this.getAction('GRID_RESET_EXPANDED_ROWS'), {});
		},

		saveForAll: function(callback)
		{
			this.save(
				this.getAction('GRID_SAVE_SETTINGS'),
				{
					view_id: 'default',
					set_default_settings: 'Y',
					delete_user_settings: 'Y',
					view_settings: this.getCurrentOptions()
				},
				callback
			);
		},

		batch: function(data, callback)
		{
			this.save(this.getAction('GRID_SAVE_BATH'), {bath: data}, callback);
		},

		save: function(action, data, callback)
		{
			var self = this;
			BX.ajax.post(
				this.getUrl(action),
				data,
				function(res)
				{
					try {
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
					} catch (err) {}
				}
			);
		}
	};
})();