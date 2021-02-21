;(function () {
	'use strict';

	BX.namespace('BX.rest.integration.list');

	BX.rest.integration.list = {};

	/**
	 *
	 * @param options
	 * @extends {BX.TileGrid.Item}
	 * @constructor
	 */
	BX.rest.integration.list.TileGridItem = function (options) {
		BX.TileGrid.Item.apply(this, arguments);

		this.title = options.title;
		this.description = options.description;
		this.image = options.image;
		this.iconClass = options.iconClass;
		this.iconColor = options.iconColor;
		this.iconIBgColor = options.iconIBgColor;
		this.iconIClass = options.iconIClass;
		this.className = options.className;
		this.selected = options.selected;
		this.url = !!options.url ? options.url : false;
		this.integrationCode = !!options.integrationCode ? options.integrationCode : false;
		this.loadProcess = false;
		this.events = {
			click: function ()
			{
				BX.rest.integration.open(this.integrationCode, this.url);
			}.bind(this)
		};
	};

	BX.rest.integration.list.TileGridItem.prototype =
		{
			__proto__: BX.TileGrid.Item.prototype,
			constructor: BX.TileGrid.Item,

			getContent: function () {
				return BX.create('div', {
					props: {
						className: 'rest-integration-tile-item' + ' ' + (this.className ? this.className : '') + ' ' + (this.selected ? this.selected : '')
					},
					children: [
						BX.create('div', {
							props: {
								className: 'rest-integration-tile-head'
							},
							children: [
								BX.create('div', {
									props: {
										className: 'rest-integration-tile-img ' + (this.iconClass ? this.iconClass : '')
									},
									style: {
										backgroundImage: this.image ? 'url("' + this.image + '")' : null,
										color: this.iconColor ? this.iconColor : null
									},
									children: [
										BX.create('i', {
											style: {
												"background-color": this.iconIBgColor ? this.iconIBgColor : null
											},
											props: {
												className: this.iconIClass ? this.iconIClass : ''
											}
										}
									)]
								}),
								BX.create('div', {
									props: {
										className: 'rest-integration-tile-title'
									},
									text: this.title ? this.title : ""
								})
							]
						}),
						BX.create('div', {
							props: {
								className: 'rest-integration-tile-description'
							},
							text: this.description ? this.description : ''
						})
					],
					events: this.events
				})
			}
		};
})();