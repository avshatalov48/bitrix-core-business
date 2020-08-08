;(function ()
{
	'use strict';

	BX.namespace('BX.Rest.MarketPartners.TileGrid.Item');

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

	BX.Rest.MarketPartners.TileGrid.Item = function(options) {

		BX.TileGrid.Item.apply(this, arguments);

		this.title = options.title;
		this.description = options.description;
		this.icon = options.icon;
		this.link = options.link;
		this.payment = options.price;
		this.infoHelperCode = options.infoHelperCode;

		this.layout = {
			wrapper: null,
			title: null,
			description: null,
			icon: null
		}
	};

	BX.Rest.MarketPartners.TileGrid.Item.prototype = {

		__proto__: BX.TileGrid.Item.prototype,
		constructor: BX.TileGrid.Item,

		getContent: function()
		{
			if (!this.layout.wrapper)
			{
				this.layout.wrapper = BX.create('div', {
					props: {
						className: 'rest-market-partners-wrapper',
					},
					children: [
						this.getIconNode(),
						BX.create('div', {
							props: {
								className: 'rest-market-partners-content'
							},
							children: [
								this.getTitle(),
								this.getDescription(),
								this.getLinkNode()
							]
						})
					]
				})
			}

			return this.layout.wrapper;
		},

		getIconNode: function()
		{
			if(!this.layout.icon)
			{
				this.layout.icon = BX.create('div', {
					props: {
						className: 'rest-market-partners-icon'
					},
					children: [
						BX.create('img', {
							props: {
								className: 'rest-market-partners-icon-image',
								src: this.icon
							}
						})
					]
				})
			}

			return this.layout.icon;
		},

		getTitle: function()
		{
			if(!this.layout.title)
			{
				this.layout.title = BX.create('div', {
					props: {
						className: 'rest-market-partners-title'
					},
					text: this.title
				})
			}

			return this.layout.title;
		},

		getDescription: function()
		{
			if(!this.layout.description)
			{
				this.layout.description = BX.create('div', {
					props: {
						className: 'rest-market-partners-description'
					},
					text: this.description
				})
			}

			return this.layout.description;
		},

		getLinkNode: function()
		{
			var action = BX.create('a', {
				props: {
					className: 'ui-btn ui-btn-sm ui-btn-primary ui-btn-round',
					href: this.link
				},
				text: BX.message('REST_MARKETPLACE_CATEGORY_INSTALL_LINK_NAME')
			});

			if(this.infoHelperCode !== false)
			{
				action = BX.create('span', {
					props: {
						className: 'ui-btn ui-btn-sm ui-btn-primary ui-btn-round',
					},
					events: {
						click: BX.delegate(function(){
							top.BX.UI.InfoHelper.show(this.infoHelperCode);
						},
							this)
					},
					text: BX.message('REST_MARKETPLACE_CATEGORY_INSTALL_LINK_NAME')
				});
			}

			return BX.create('div', {
				props: {
					className: 'rest-market-partner-link-wrapper'
				},
				children: [
					action,
					this.payment ? BX.create('span', {
						props: {
							className: 'rest-market-partners-price'
						},
						text: this.payment
					}) : null
				]
			})
		},

		clipDescription: function()
		{
			if(!this.layout.description)
			{
				return;
			}
			BX.cleanNode(this.layout.description);
			var descriptionWrapper = BX.create("span", {
				text: this.description
			});

			this.layout.description.appendChild(descriptionWrapper);

			var nodeHeight = this.layout.description.offsetHeight;
			var text = this.description;

			var a = 0;

			while (nodeHeight <= descriptionWrapper.offsetHeight && text.length > a)
			{
				a = a + 2;
				descriptionWrapper.innerText = text.slice(0, -a) + '...';
			}
		},

		clipTitle: function()
		{
			if(!this.layout.title)
			{
				return;
			}
			BX.cleanNode(this.layout.title);
			var wrapper = BX.create("span", {
				text: this.title
			});

			this.layout.title.appendChild(wrapper);

			var nodeHeight = this.layout.title.offsetHeight;
			var text = this.title;

			var a = 0;
			while (nodeHeight <= wrapper.offsetHeight && text.length > a)
			{
				a = a + 2;
				wrapper.innerText = text.slice(0, -a) + '...';
			}
		},

		afterRender: function()
		{
			if(this.description)
			{
				this.clipDescription()
			}

			if(this.title)
			{
				this.clipTitle();

			}
		}
	};
})();