;(function () {
	'use strict';

	BX.namespace('BX.Rest.MarketDirections.TileGrid.Item');

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

	BX.Rest.MarketDirections.TileGrid.Item = function (options) {

		BX.TileGrid.Item.apply(this, arguments);

		this.title = options.title;
		this.description = options.description;
		this.icon = options.icon;
		this.color = options.color;
		this.link = options.link;

		this.layout = {
			wrapper: null,
			title: null,
			description: null,
			icon: null
		}

	};

	BX.Rest.MarketDirections.TileGrid.Item.prototype = {

		__proto__: BX.TileGrid.Item.prototype,
		constructor: BX.TileGrid.Item,

		getContent: function () {
			if (!this.layout.wrapper)
			{
				this.layout.wrapper = BX.create('div', {
					props: {
						className: 'rest-market-directions-wrapper',
					},
					children: [
						this.getColorLine(),
						this.getIconNode(),
						BX.create('div', {
							props: {
								className: 'rest-market-directions-content'
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

		getLinkNode: function () {
			if (!this.link)
			{
				return
			}

			return BX.create('div', {
				props: {
					className: 'rest-market-directions-link-wrapper'
				},
				children: [
					BX.create('a', {
						props: {
							className: 'rest-market-directions-link',
							href: this.link
						},
						text: BX.message('REST_CONFIGURATION_SECTION_LINK_NAME')
					})
				]
			})
		},

		getIconNode: function () {
			if (!this.layout.icon)
			{
				this.layout.icon = BX.create('div', {
					props: {
						className: 'rest-market-directions-icon'
					},
					children: [
						BX.create('div', {
							props: {
								className: 'rest-market-directions-round'
							},
							style: {
								backgroundColor: this.color
							}
						}),
						this.icon ?
							BX.create('div', {
								props: {
									className: 'rest-market-directions-icon-image'
								},
								style: {
									backgroundImage: 'url(' + this.icon + ')'
								}
							}) : null
					]
				})
			}

			return this.layout.icon;
		},

		getColorLine: function () {
			if (!this.color)
			{
				return
			}

			return BX.create('div', {
				props: {
					className: 'rest-market-directions-color-line'
				},
				style: {
					backgroundColor: this.color
				}
			})
		},

		getTitle: function () {
			if (!this.layout.title)
			{
				this.layout.title = BX.create('div', {
					props: {
						className: 'rest-market-directions-title'
					},
					text: this.title
				})
			}

			return this.layout.title;
		},

		getDescription: function () {
			if (!this.layout.description)
			{
				this.layout.description = BX.create('div', {
					props: {
						className: 'rest-market-directions-description'
					},
					text: this.description
				})
			}

			return this.layout.description;
		},

		clipDescription: function () {
			if (!this.layout.description)
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

		afterRender: function () {
			this.clipDescription()
		}
	};
})();