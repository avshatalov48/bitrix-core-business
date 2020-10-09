;(function () {
	'use strict';

	BX.namespace('BX.RestIntegrationList');

	BX.RestIntegrationList.Start = {
		open: function ()
		{
			if (!!this.url)
			{
				BX.SidePanel.Instance.open(this.url, {cacheable: false});
			}
			else
			{
				if (this.integrationCode)
				{
					if (!!this.loadProcess)
					{
						BX.UI.Notification.Center.notify({
							content: BX.message('REST_INTEGRATION_LIST_OPEN_PROCESS')
						});
						return false;
					}
					else
					{
						this.loadProcess = true;
					}

					BX.ajax.runComponentAction(
						'bitrix:rest.integration.edit',
						'getNewIntegrationUrl',
						{
							mode: 'class',
							data: {
								code: this.integrationCode
							}
						}
					).then(
						function (response)
						{
							if (!!response.data && !!response.data.url)
							{
								BX.SidePanel.Instance.open(
									response.data.url,
									{
										cacheable: false,
										requestMethod: 'post',
										requestParams: {
											'NEW_OPEN': 'Y',
										},
										events:
										{
											onLoad: function(event)
											{
												this.loadProcess = false;
											}.bind(this)
										}
									}
								);
							}
							else
							{
								var errorMessage = BX.message('REST_INTEGRATION_LIST_ERROR_OPEN_URL');
								if (!!response.data.error && response.data.error.length > 0)
								{
									errorMessage = response.data.error;
								}
								BX.UI.Notification.Center.notify({
									content: errorMessage
								});
								this.loadProcess = false;
							}
						}.bind(this)
					).catch(
						function (response)
						{
							BX.UI.Notification.Center.notify({
								content: BX.message('REST_INTEGRATION_LIST_ERROR_OPEN_URL')
							});
							this.loadProcess = false;
						}.bind(this)
					);
				}
			}
		}
	};

	/**
	 *
	 * @param options
	 * @extends {BX.TileGrid.Item}
	 * @constructor
	 */
	BX.RestIntegrationList.Start.TileGridItem = function (options) {
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
		this.events = {click: BX.RestIntegrationList.Start.open.bind(this)};
	};

	BX.RestIntegrationList.Start.TileGridItem.prototype =
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