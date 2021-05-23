;(function ()
{
	'use strict';

	BX.namespace('BX.Rest.MarketSite.TileGrid.Item');

	if(!BX.TileGrid)
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
		this.link = options.link;
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
						className: 'rest-market-site-wrapper'+((this.disabled)?' rest-market-site-wrapper-disabled':''),
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
						className: 'rest-market-site-title'
					},
					text: this.title
				})
			}

			return this.layout.title;
		},

		getIconNode: function()
		{
			if(!this.layout.icon)
			{
				this.layout.icon = BX.create('div', {
					props: {
						className: 'rest-market-site-icon'
					},
					children: [
						BX.create('a', {
							props: {
								className: 'rest-market-site-icon-image',
								href: this.link
							},
							style: {
								backgroundImage: 'url(' + this.icon + ')'
							}
						})
					]
				})
			}

			return this.layout.icon;
		}
	}
})();