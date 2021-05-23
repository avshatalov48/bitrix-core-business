;(function ()
{
	'use strict';

	BX.namespace('BX.Rest.MarketSite.TileGrid.Item');

	if (!BX.TileGrid)
	{
		return false;
	}

	/**
	 *
	 * @param options
	 * @extends {BX.TileGrid.Item}
	 * @constructor
	 */

	BX.Rest.MarketSite.TileGrid.Item = function(options) {

		BX.TileGrid.Item.apply(this, arguments);

		this.title = options.title;
		this.icon = options.icon;
		this.url = options.url;
		this.onclick = options.onclick;
		this.disabled = options.disabled;

		this.layout = {
			wrapper: null,
			title: null,
			icon: null
		}
	};

	BX.Rest.MarketSite.TileGrid.Item.prototype = {

		__proto__: BX.TileGrid.Item.prototype,
		constructor: BX.TileGrid.Item,

		getContent: function()
		{
			if (!this.layout.wrapper)
			{
				this.layout.wrapper = BX.create('div', {
					props: {
						className: 'rest-market-action-wrapper'+((this.disabled) ? ' rest-market-action-wrapper-disabled':''),
					},
					children: [
						this.getTitle(),
						this.getIconNode()
					]
				})
			}

			return this.layout.wrapper;
		},

		getTitle: function()
		{
			if (!this.layout.title)
			{
				this.layout.title = BX.create('div', {
					props: {
						className: 'rest-market-action-title'
					},
					text: this.title
				})
			}

			return this.layout.title;
		},

		getIconNode: function()
		{
			if (!this.layout.icon)
			{
				this.layout.icon = BX.create('div', {
					props: {
						className: 'rest-market-action-icon'
					},
					children: [
						BX.create('span', {
							props: {
								className: 'rest-market-action-icon-image',
							},
							style: {
								backgroundImage: 'url(' + this.icon + ')'
							},
							events: {
								click: !this.disabled ? this.getClickEvent() : function(){}
							}
						})
					]
				})
			}

			return this.layout.icon;
		},
		getClickEvent: function()
		{
			if (!!this.onclick && this.onclick !== '')
			{
				return new Function('', this.onclick);
			}
			else
			{
				return BX.delegate(
					function () {
						BX.SidePanel.Instance.open(this.url);
					},
					this
				);
			}
		},
	}
})();