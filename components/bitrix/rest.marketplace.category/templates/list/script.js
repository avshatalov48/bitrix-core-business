'use strict';

BX.namespace("BX.Rest.Markeplace.Category");

BX.Rest.Markeplace.Category = {
	init: function (params)
	{
		this.signedParameters = params.signedParameters || {};
		this.filterId = params.filterId;

		this.leftMenuItems = BX.findChildren(BX("mp-left-menu"), {attribute : {"bx-role" : "mp-left-menu-item"}}, true);

		if (BX('mp-top-menu'))
		{
			var items = BX.findChildren(BX("mp-top-menu"), {tagName : "A"}, true), i;
			for (i = 0; i <= items.length; i++)
			{
				items[i].href = BX.util.add_url_param(items[i].href, {"IFRAME" : "Y"});
			}
		}
		this.initEvents();
		BX.Rest.Markeplace.Category.Page = this;
	},

	initEvents: function()
	{
		BX.addCustomEvent('BX.Main.Filter:apply', this.onApplyFilter.bind(this));
		BX.addCustomEvent('BX.Main.Filter:clickMPMenu', this.clickMPMenu.bind(this));
	},
	clickMPMenu : function(nodeMenu)
	{
		var Filter = BX.Main.filterManager.getById(this.filterId);
		if (!(Filter instanceof BX.Main.Filter))
		{
			return;
		}

		var category = nodeMenu.getAttribute("bx-mp-left-menu-item");
		var FilterApi = Filter.getApi();
		FilterApi.setFields({ CATEGORY : category});
		Filter.__marketplaceFilter = {
			filterMode : nodeMenu.getAttribute("bx-filter-mode"),
			filterValue : nodeMenu.getAttribute("bx-filter-value")
		};

		FilterApi.apply();
	},
	onApplyFilter: function (id, data, ctx, promise, params)
	{
		if (id !== this.filterId)
		{
			return;
		}

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
		this.reloadPage(ctx.__marketplaceFilter, promise);
		delete ctx.__marketplaceFilter;
	},
	reloadPage : function(filter, promise)
	{
		var loader = new BX.Loader({
			target: BX("mp-category-block"),
			offset: {top: "150px"}
		});

		loader.show();

		BX.ajax.runComponentAction(
			"bitrix:rest.marketplace.category",
			"getPage",
			{
				mode: "class",
				data: filter,
				signedParameters: this.signedParameters
			}).then(
			function(data)
			{
				var ob = BX.processHTML(data.data, false);
				BX("mp-category-block").innerHTML = ob.HTML;
				setTimeout(BX.ajax.processScripts, 500, ob.SCRIPT);
				loader.hide();
				if (promise)
				{
					promise.fulfill();
				}
			},
			function()
			{
				loader.hide();
				if (promise)
				{
					promise.reject();
				}
			}
		);
	}
};

BX.Rest.Markeplace.Category.Items = {
	init: function (params)
	{
		this.pageCount = Number(params.pageCount);
		this.currentPageNumber = Number(params.currentPageNumber);
		this.filter = params.filter || {};

		if (BX.type.isDomNode(BX("mp-more-button")))
		{
			BX.bind(BX("mp-more-button"), "click", function () { this.loadPage(); }.bind(this));
		}
	},

	loadPage: function ()
	{
		if (this.pageCount <= this.currentPageNumber)
		{
			return;
		}

		BX.addClass(BX("mp-more-button"), "ui-btn-clock");

		BX.ajax.runComponentAction(
			"bitrix:rest.marketplace.category",
			"getNextPage",
			{
				mode: "class",
				data: this.filter,
				navigation : {page : (++this.currentPageNumber)},
				signedParameters: BX.Rest.Markeplace.Category.Page.signedParameters
			}).then(
			function(data)
			{
				for (var item in data.data)
				{
					if (data.data.hasOwnProperty(item))
					{
						window.gridTile.appendItem(data.data[item]);
					}
				}
				BX.removeClass(BX("mp-more-button"), "ui-btn-clock");

				if (this.pageCount === this.currentPageNumber)
				{
					BX.remove(BX("mp-more-button"));
				}
			}.bind(this)
		);
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
	this.shortDesc = options.SHORT_DESC;
	this.secondaryDesc = options.SECONDARY_DESC;
	this.image = options.ICON;
	this.onclick = options.ONCLICK;
	this.feedback = options.FEEDBACK === 'Y';
	this.layout = {
		container: null,
		secondaryDesc: null,
		image: null,
		labels: null,
		title: null,
		clipTitle: null,
		company: null,
		controls: null,
		buttonAction: null,
		price: null,
		feedback: null
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

		if(this.feedback)
		{
			this.getFeedbackContent();
		}
		else
		{
			this.getApplicationContent();
		}

		return this.layout.container;
	},

	getApplicationContent: function()
	{
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
						this.getDesc(),
						this.getControls()
					]
				}),
				this.getStatus()
			]
		});
	},

	getFeedbackContent: function()
	{
		this.layout.container = BX.create('div', {
			props: {
				className: 'mp-item mp-fb-item'
			},
			children: [
				BX.create('div', {
					props: {
						className: 'mp-item-fb-content'
					},
					children: [
						this.getTitle(),
						this.getDesc(),
						this.getSecondaryDesc()
					]
				}),
				BX.create('div', {
					props: {
						className: 'mp-item-aside'
					},
					children: [
						this.getImage(),
						this.getControls()
					]
				}),
			]
		});
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
					if(this.infoHelperCode !== false)
					{
						top.BX.UI.InfoHelper.show(this.infoHelperCode);
					}
					else
					{
						BX.SidePanel.Instance.open(this.url);
					}
				},
				this
			);
		}
	},

	getTitle: function()
	{
		if (this.layout.title)
		{
			return this.layout.title;
		}

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
				click: this.getClickEvent()
			}
		});

		return this.layout.title;
	},

	getDesc: function()
	{
		if(this.layout.desc)
			return this.layout.desc;

		this.layout.desc = BX.create('div', {
			props: {
				className: 'mp-item-developer' + (this.hasSecondDesc() ? '' : ' mp-item-developer-full')
			},
			text: this.shortDesc ? this.shortDesc : this.developer
		});

		return this.layout.desc;
	},

	hasSecondDesc: function()
	{
		return BX.type.isString(this.secondaryDesc) && this.secondaryDesc !== '';
	},

	getSecondaryDesc: function()
	{
		if(this.layout.secondaryDesc)
			return this.layout.secondaryDesc;

		if (this.hasSecondDesc())
		{
			this.layout.secondaryDesc = BX.create('div', {
				props: {
					className: 'mp-item-desc-box'
				},
				children: [
					BX.create('div', {
						props: {
							className: 'mp-item-desc'
						},
						text: this.secondaryDesc
					}),
					BX.create('div', {
						props: {
							className: 'mp-item-desc-icon'
						}
					})
				]
			});
		}

		return this.layout.secondaryDesc;
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
						className: 'ui-btn ui-btn-xs ui-btn-secondary ui-btn-round'
					},
					text: this.action,
					events: {
						click: this.getClickEvent()
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
		this.clipDesc();
	},

	clipDesc: function()
	{
		BX.cleanNode(this.layout.desc);

		this.layout.descriptionWrapper = BX.create("span", {
			text: this.shortDesc ? this.shortDesc : this.developer
		});

		this.layout.desc.appendChild(this.layout.descriptionWrapper);

		var nodeHeight = this.layout.desc.offsetHeight;
		var text = this.shortDesc ? this.shortDesc : this.developer;

		var a = 0;
		while (nodeHeight <= this.layout.descriptionWrapper.offsetHeight && text.length > a)
		{
			a = a + 2;
			this.layout.descriptionWrapper.innerText = text.slice(0, -a) + '...';
		}
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

