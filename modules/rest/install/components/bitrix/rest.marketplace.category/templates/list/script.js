'use strict';

BX.namespace("BX.Rest.Markeplace.Category");

BX.Rest.Markeplace.Category = {
	init: function (params)
	{
		if (typeof params === "object" && params)
		{
			this.ajaxPath = params.ajaxPath || "";
			this.pageCount = Number(params.pageCount) || "";
			this.currentPage = Number(params.currentPage) || "";
			this.filterId = params.filterId || "";
		}

		this.leftMenuItems = BX.findChildren(BX("mp-left-menu"), {attribute : {"bx-role" : "mp-left-menu-item"}}, true);

		if (BX.type.isDomNode(BX("mp-more-button")))
		{
			BX.bind(BX("mp-more-button"), "click", function () {
				this.loadPage();
			}.bind(this));
		}
		if (BX('mp-top-menu'))
		{
			var items = BX.findChildren(BX("mp-top-menu"), {tagName : "A"}, true), i;
			for (i = 0; i <= items.length; i++)
			{
				items[i].href = BX.util.add_url_param(items[i].href, {"IFRAME" : "Y"});
			}
		}
	},

	initEvents: function()
	{
		BX.addCustomEvent('BX.Main.Filter:apply', BX.proxy(this.onApplyFilter, this));
		BX.addCustomEvent('BX.Main.Filter:clickMPMenu', BX.proxy(this.clickMPMenu, this));
	},

	clickMPMenu : function(nodeMenu) {
		var Filter = BX.Main.filterManager.getById(this.filterId);

		if (!!Filter && (Filter instanceof BX.Main.Filter)) {
			var category = nodeMenu.getAttribute("bx-mp-left-menu-item"),
				FilterApi = Filter.getApi();
			FilterApi.setFields({'CATEGORY': nodeMenu.getAttribute("bx-mp-left-menu-item")});
			Filter.__ = nodeMenu.getAttribute("bx-mp-left-menu-item");
			FilterApi.apply();
		}
	},
	onApplyFilter: function (id, data, ctx, promise, params)
	{
		if (id !== BX.Rest.Markeplace.Category.filterId)
			return;

		if (this.leftMenuItems && this.leftMenuItems.length > 0)
		{
			var item, activeItem = ctx.getFilterFieldsValues()["CATEGORY"], i;
			for (i = 0; i < this.leftMenuItems.length; i++)
			{
				item = this.leftMenuItems[i];
				if (item.getAttribute("bx-mp-left-menu-item") != activeItem)
					BX.removeClass(item.parentNode, "ui-sidepanel-menu-active");
				else if (!BX.hasClass(item.parentNode, "ui-sidepanel-menu-active"))
					BX.addClass(item.parentNode, "ui-sidepanel-menu-active");
			}
		}
		params.autoResolve = false;

		var loader = new BX.Loader({
			target: BX("mp-category-block-list"),
			offset: {top: "150px"}
		});
		loader.show();
		BX.ajax({
			method: 'POST',
			dataType: 'html',
			url: BX.Rest.Markeplace.Category.ajaxPath,
			data: {
				action: "setFilter",
				sessid: BX.bitrix_sessid()
			},
			onsuccess: BX.proxy(function (html) {
				BX("mp-category-block-list").innerHTML = html;
				loader.hide();

				promise.fulfill();
			}, this),
			onfailure: function () {
				promise.reject();
			}

		});
	},

	loadPage: function ()
	{
		if (this.pageCount <= this.currentPage)
			return;

		BX.addClass(BX("mp-more-button"), "ui-btn-clock");

		var url = this.ajaxPath;
		url += ((this.ajaxPath.indexOf("?") === -1) ? "?" : "&") + "nav-apps=page-" + ++this.currentPage;

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: url,
			data: {
				action: "loadPage",
				sessid: BX.bitrix_sessid()
			},
			onsuccess: BX.proxy(function (json)
			{
				for (var item in json)
				{
					if(json.hasOwnProperty(item))
						window.gridTile.appendItem(json[item]);
				}

				BX.removeClass(BX("mp-more-button"), "ui-btn-clock");
				if (this.pageCount === this.currentPage)
				{
					BX.remove(BX("mp-more-button"));
				}
			}, this)
		});
	}
};


BX.namespace('BX.Rest.Marketplace.TileGrid');

/**
 *
 * @param options
 * @extends {BX.TileGrid.Item}
 * @constructor
 */
BX.Rest.Marketplace.TileGrid.Item = function(options)
{
	BX.TileGrid.Item.apply(this, arguments);

	this.title = options.NAME;
	this.developer = options.PARTNER_NAME;
	this.image = options.ICON;
	this.layout = {
		container: null,
		image: null,
		labels: null,
		title: null,
		clipTitle: null,
		company: null,
		controls: null,
		buttonAction: null,
		price: null
	};
	this.currency = options.currency;
	this.period = options.period;
	this.payment = options.PRICE;
	this.rate = options.rate;
	this.action = BX.message("MARKETPLACE_SHOW_APP");//options.action;
	this.installed = options.INSTALLED === "Y";
	this.url = options.URL;
	this.promo = options.PROMO === "Y";
	this.labels = options.LABELS;
	this.recommended = options.recommended;
	this.top = options.top;
	this.infoHelperCode = false;
	if('INFO_HELPER_CODE' in options)
	{
		this.infoHelperCode = options['INFO_HELPER_CODE'];
	}
};

BX.Rest.Marketplace.TileGrid.Item.prototype =
{
	__proto__: BX.TileGrid.Item.prototype,
	constructor: BX.TileGrid.Item,

	getContent: function()
	{
		if(this.layout.container)
			return this.layout.container;

		this.layout.container = BX.create('div', {
			props: {
				className: 'mp-item'
			},
			children: [
				this.getLabels(),
				this.getImage(),
				BX.create('div', {
					props: {
						className: 'mp-item-content'
					},
					children: [
						this.getTitle(),
						this.getDeveloper(),
						this.getControls()
					]
				}),
				this.getStatus()
			]
		});

		return this.layout.container;
	},

	getStatus: function()
	{
		if(this.layout.status)
			return this.layout.status;

		this.layout.status = BX.create('div', {
			props: {
				className: 'mp-item-status'
			},
			children: [
				this.getStatusPromo(),
				//this.getStatusRecommended(),
				this.getStatusInstalled(),
				this.getStatusTop()
			]
		});

		return this.layout.status;
	},

	getStatusInstalled: function()
	{
		if(!this.installed)
			return;

		return BX.create('div', {
			props: {
				className: 'mp-item-status-item mp-item-status-item-installed'
			},
			text: BX.message("MARKETPLACE_INSTALLED").toUpperCase()
		});
	},

	getStatusPromo: function()
	{
		if(!this.promo)
			return;

		return BX.create('div', {
			props: {
				className: 'mp-item-status-item mp-item-status-item-sale'
			},
			text: BX.message("MARKETPLACE_SALE").toUpperCase()
		});
	},

	getStatusRecommended: function()
	{
		if(!this.recommended)
			return;

		return BX.create('div', {
			props: {
				className: 'mp-item-status-item mp-item-status-item-recommended'
			},
			text: 'Recommended'
		});
	},

	getStatusTop: function()
	{
		if(!this.top)
			return;

		return BX.create('div', {
			props: {
				className: 'mp-item-status-item mp-item-status-item-top'
			}
		});
	},

	getImage: function()
	{
		if(this.layout.image)
			return this.layout.image;

		this.layout.image = BX.create('div', {
			props: {
				className: 'mp-item-image'
			},
			style: {
				backgroundImage: this.image ? 'url("' + this.image + '")' : null,
			}
		});

		if(!this.layout.image.hasAttribute('style'))
			this.layout.image.style.backgroundSize = 'auto';

		return this.layout.image;
	},

	getLabels: function()
	{
		if (this.layout.labels !== null)
			return this.layout.labels;
		this.layout.labels = "";
		if (BX.type.isArray(this.labels))
		{
			var i, j, res = [], color;
			for (j = 0; j < Math.min(this.labels.length, 5); j++)
			{
				i = this.labels[j];
				i["COLOR"] = BX.type.isNotEmptyString(i["COLOR"]) ? i["COLOR"] : "";
				res.push(BX.create('div', {
					props: {
						className: 'mp-badge-ribbon-box' + (i["COLOR"] !== "" && i["COLOR"].substring(0, 1) !== "#" ? (" mp-badge-ribbon-box-" + i["COLOR"]) : "")
					},
					children: [
						BX.create('span', {
							props: {
								className: 'mp-badge-ribbon-item',
							},
							style : (i["COLOR"].substring(0, 1) === "#" ? {backgroundColor : i["COLOR"]} : {}),
							children: [
								BX.create('textNode', {
									text: i["TEXT"]}),
								BX.create('span', {
									props: {
										className: 'mp-badge-ribbon-item-after'
									},
									style : (i["COLOR"].substring(0, 1) === "#" ? {borderColor : [i["COLOR"], 'transparent', i["COLOR"], i["COLOR"]].join(' ')} : {})
								})
							]
						})
					]
				}));
			}
			if (res.length > 0)
			{
				this.layout.labels = BX.create('div', {
					props: {
						className: 'mp-badge-ribbon-wrap'
					},
					children: res
				});
			}
		}
		return this.layout.labels;
	},

	getTitle: function()
	{
		if(this.layout.title)
			return this.layout.title;

		this.layout.title = BX.create('div', {
			props: {
				className: 'mp-item-title'
			},
			children: [
				this.layout.clipTitle = BX.create('span', {
					text: this.title
				})
			],
			events: {
				click: function ()
				{
					if(this.infoHelperCode !== false)
					{
						top.BX.UI.InfoHelper.show(this.infoHelperCode);
					}
					else
					{
						BX.SidePanel.Instance.open(this.url);
					}
				}.bind(this)
			}
		});

		return this.layout.title;
	},

	getDeveloper: function()
	{
		if(this.layout.developer)
			return this.layout.developer;

		this.layout.developer = BX.create('div', {
			props: {
				className: 'mp-item-developer'
			},
			text: this.developer
		});

		return this.layout.developer;
	},

	getControls: function()
	{
		if(this.layout.controls)
			return this.layout.controls;

		var action = null;

		if(this.currency)
			action = this.currency + '/' + this.period;

		this.layout.controls = BX.create('div', {
			props: {
				className: 'mp-item-controls'
			},
			children: [
				this.layout.buttonAction = BX.create('div', {
					props: {
						className: 'ui-btn ui-btn-xs ui-btn-light-border ui-btn-round'
					},
					text: this.action,
					events: {
						mouseenter: function()
						{
							BX.removeClass(this.layout.buttonAction, 'ui-btn ui-btn-xs ui-btn-light-border ui-btn-round');
							BX.addClass(this.layout.buttonAction, 'ui-btn ui-btn-xs ui-btn-primary ui-btn-hover ui-btn-round')
						}.bind(this),
						mouseleave: function()
						{
							BX.removeClass(this.layout.buttonAction, 'ui-btn ui-btn-xs ui-btn-primary ui-btn-hover ui-btn-round');
							BX.addClass(this.layout.buttonAction, 'ui-btn ui-btn-xs ui-btn-light-border ui-btn-round');
						}.bind(this),
						click: function ()
						{
							if(this.infoHelperCode !== false)
							{
								top.BX.UI.InfoHelper.show(this.infoHelperCode);
							}
							else
							{
								BX.SidePanel.Instance.open(this.url);
							}
						}.bind(this)
					}
				}),
				this.layout.price = BX.create('div', {
					props: {
						className: 'mp-item-controls-rate'
					},
					children: [
						BX.create('span', {
							text: this.payment
						}),
						BX.create('span', {
							props: {
								className: 'mp-item-controls-rate-currency'
							},
							html: action
						})
					]
				})
			]
		});

		return this.layout.controls;
	},

	afterRender: function()
	{
		this.clipTitle();
	},

	clipTitle: function()
	{
		for(var i = this.layout.title.offsetHeight, a = 0; i < this.layout.clipTitle.offsetHeight; a++)
		{
			a++;
			this.layout.clipTitle.innerHTML = this.title.slice(0, -a) + '...';
		}
	}
};

