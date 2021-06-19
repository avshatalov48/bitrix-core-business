;(function() {
	"use strict";

	/**
	 * @namespace BX.Main
	 */
	BX.namespace('BX.Main');

	/**
	 *
	 * @param {object} options
	 * @extends {BX.TileGrid.Grid}
	 * @constructor
	 */
	BX.Main.TileGrid = function(options)
	{
		BX.TileGrid.Grid.apply(this, arguments);

		this.navigation = options.navigation;
		this.isLoadingNextPortion = false;

		this.init(options);
		BX.onCustomEvent(
			window,
			"BX.TileGrid.Grid:initialized",
			[this]
		);
	};

	BX.Main.TileGrid.prototype =
	{
		__proto__: BX.TileGrid.Grid.prototype,
		constructor: BX.Main.TileGrid,

		bindEvents: function()
		{
			BX.TileGrid.Grid.prototype.bindEvents.call(this);

			window.addEventListener('scroll', BX.throttle(this.handleScroll.bind(this), 100));
		},

		init: function(parameters)
		{
			this.userOptions = new BX.Main.TileGrid.UserOptions(this, parameters.userOptions, parameters.userOptionsActions, parameters.userOptionsHandlerUrl);
		},

		handleScroll: function ()
		{
			if(this.needToShowNextPortion())
			{
				this.showNextPortion();
			}
		},

		handleBackspace: function ()
		{
			window.history.back();
		},

		reload: function(url, data)
		{
			if (!BX.type.isString(url))
			{
				url = document.location.toString();
			}

			data = data || {};
			if (BX.message.SITE_ID)
			{
				data.SITE_ID = BX.message.SITE_ID;
			}
			data.sessid = BX.bitrix_sessid();

			var eventArgs =
				{
					gridId: this.getId(),
					url: url,
					data: data
				};

			BX.onCustomEvent(
				window,
				"BX.TileGrid.Grid:beforeReload",
				[this, eventArgs]
			);

			url = eventArgs.url;
			data = eventArgs.data;


			var promise = BX.ajax.promise({
				url: BX.util.add_url_param(url, {
					grid_id: this.getId(),
					internal: true
				}),
				data: data,
				method: 'POST',
				dataType: 'json'
			});

			this.setHeightContainer();
			this.setFadeContainer();

			if(!this.loader)
				this.getLoader();

			this.showLoader();

			promise.then(function(response)
			{
				this.navigation = response.data.navigation;
				this.redraw(response.data.tileGrid.items);
				this.loader.destroy();
				this.unSetFadeContainer();
				this.unSetHeightContainer()
			}.bind(this));

			return promise;
		},

		/**
		 * @return {BX.Main.TileGrid.UserOptions}
		 */
		getUserOptions: function()
		{
			return this.userOptions;
		},

		prepareSortUrl: function(header)
		{
			var url = window.location.toString();

			if ('sort_by' in header)
			{
				url = BX.util.add_url_param(url, {by: header.sort_by});
			}

			if ('sort_order' in header)
			{
				url = BX.util.add_url_param(url, {order: header.sort_order});
			}

			return url;
		},

		sortByColumn: function(column)
		{
			var header = column;
			header.sort_url = this.prepareSortUrl(column);

			this.getUserOptions().setSort(header.sort_by, header.sort_order, function() {
				this.reload(header.sort_url, {
					grid_action: 'sort'
				});
			}.bind(this));
		},

		needToShowNextPortion: function()
		{
			if (!this.navigation.hasNextPage)
			{
				return false;
			}

			var containerHeight = BX.pos(this.container).height;
			var windowHeight = document.body.offsetHeight;
			var offsetBottomBorder = containerHeight - windowHeight + this.container.getBoundingClientRect().top;
			var countOfRows = parseInt(this.navigation.pageSize / this.countItemsPerRow, 10);
			var itemHeight = BX.pos(this.items[0].layout.container).height;

			if (itemHeight * Math.max(1, parseInt(countOfRows * 0.5, 10)) >= offsetBottomBorder)
			{
				return true;
			}

			return false;
		},

		showNextPortion: function ()
		{
			if (!this.navigation.hasNextPage || this.isLoadingNextPortion)
			{
				return;
			}

			this.isLoadingNextPortion = true;
			var promise = BX.ajax.promise({
				url: BX.util.add_url_param(this.navigation.urlNextPage, {
					grid_id: this.getId(),
					internal: true
				}),
				method: 'POST',
				dataType: 'json'
			});

			promise.then(function(response) {
				this.isLoadingNextPortion = false;

				response.data.tileGrid.items.forEach(function (item) {
					this.appendItem(item);
					this.navigation =  response.data.navigation;
				}, this);
			}.bind(this)).catch(function(){
				this.isLoadingNextPortion = false;
			}.bind(this));
		}
	};
})();