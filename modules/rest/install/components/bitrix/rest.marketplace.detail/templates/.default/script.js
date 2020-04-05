'use strict';

BX.namespace("BX.Rest.Marketplace.Detail");

BX.Rest.Marketplace.Detail =
{
	init: function(params)
	{
		params = typeof params === "object" ? params : {};

		this.ajaxPath = params.ajaxPath || null;
		this.siteId = params.siteId || null;
		this.appName = params.appName || "";
		this.appCode = params.appCode || "";
		this.importUrl = params.importUrl || "";
		this.openImport = params.openImport || false;

		this.description = document.querySelector('[data-role="mp-detail-main-description"]');
		this.descriptionWrapper = document.querySelector('[data-role="mp-detail-main-description-wrapper"]');
		if(this.openImport === true && params.importUrl !== '')
		{
			BX.SidePanel.Instance.open(params.importUrl);
		}
	/*	this.descriptionMore = document.querySelector('[data-role="mp-detail-main-description-more"]');

		BX.bind(this.descriptionMore, 'click', function() {
			this.slideDescription();
		}.bind(this));*/

		if (BX.type.isDomNode(BX("detail_cont")))
		{
			var employeeInstButton = BX("detail_cont").getElementsByClassName("js-employee-install-button");

			if (BX.type.isDomNode(employeeInstButton[0]))
			{
				BX.bind(employeeInstButton[0], "click", BX.proxy(function(){
					this.confirmInstallRequest(BX.proxy_context);
				},this));
			}
		}

		this.initTabs();
	},

	initTabs: function()
	{
		this.slicker = document.querySelector('[data-role="mp-detail-content-menu-border"]');
		this.menuItems = document.querySelectorAll('.mp-detail-content-menu-item');
		this.menuItemActive;
		this.contentItems = document.querySelectorAll('.mp-detail-content-wrapper-item');
		this.contentActiveItem;

		for (var i = 0; i < this.menuItems.length; i++)
		{
			var itemd = this.menuItems[i];
			BX.bind(itemd, 'click', BX.proxy(function()
			{
				this.setActiveItem(BX.proxy_context);
			},this));
		}

		this.setSlickerParam();
	},

	setSlickerParam: function()
	{
		this.menuItemActive = document.querySelector('.mp-detail-content-menu-item-active');
		this.slicker.style.left = this.menuItemActive.offsetLeft + 'px';
		this.slicker.style.width = this.menuItemActive.offsetWidth + 'px';
	},

	setActiveItem: function(item)
	{
		if(BX.hasClass(item, 'mp-detail-content-menu-item-active'))
			return;

		for (var i = 0; i < this.menuItems.length; i++)
		{
			BX.removeClass(this.menuItems[i], 'mp-detail-content-menu-item-active');
		}

		BX.addClass(item, 'mp-detail-content-menu-item-active');

		this.setSlickerParam();
		this.setActiveContainer(item.getAttribute('for'));
	},

	setActiveContainer: function(id)
	{
		this.contentActiveItem = document.getElementById(id);

		for (var i = 0; i < this.contentItems.length; i++)
		{
			BX.removeClass(this.contentItems[i], 'mp-detail-content-wrapper-item-active');
		}

		BX.addClass(this.contentActiveItem, 'mp-detail-content-wrapper-item-active');
	},

	slideDescription: function ()
	{
		if (!this.description.style.maxHeight)
		{
			this.description.style.maxHeight = this.descriptionWrapper.offsetHeight + 'px';
		}
		else
		{
			this.description.style.maxHeight = "";
			//BX.addClass(this.descriptionMore, 'mp-detail-main-description-more-hide');
		}
	},

	confirmInstallRequest: function(element)
	{
		var popup = BX.PopupWindowManager.create('mp_install_confirm_popup', null, {
			content: '<div class="mp_install_confirm"><div class="mp_install_confirm_text">' + BX.message('REST_MP_INSTALL_REQUEST_CONFIRM') + '</div></div>',
			closeByEsc: true,
			closeIcon: {top: '1px', right: '10px'},
			buttons: [
				new BX.PopupWindowButton({
					text: BX.message("REST_MP_APP_INSTALL_REQUEST"),
					className: "popup-window-button-accept",
					events: {
						click: BX.delegate(function()
						{
							popup.close();
							this.sendInstallRequest(element);
						}, this)
					}
				}),
				new BX.PopupWindowButtonLink({
					text: BX.message('JS_CORE_WINDOW_CANCEL'),
					className: "popup-window-button-link-cancel",
					events: {
						click: function()
						{
							this.popupWindow.close()
						}
					}
				})
			]
		});

		popup.show();
	},

	sendInstallRequest: function(element)
	{
		BX.PopupWindowManager.create("mp-detail-block", element, {
			content: BX.message("MARKETPLACE_APP_INSTALL_REQUEST"),
			angle: {offset : 35 },
			offsetTop:8,
			autoHide:true
		}).show();

		BX.ajax({
			method: "POST",
			dataType: "json",
			url: this.ajaxPath,
			data: {
				sessid : BX.bitrix_sessid(),
				site_id : this.siteId,
				action: "sendInstallRequest",
				appName: this.appName,
				appCode: this.appCode
			},
			onsuccess: function()
			{

			},
			onfailure: function()
			{
			}
		});
	}
};

BX.namespace('BX.Rest.Marketplace.DetailImageScroller');

BX.Rest.Marketplace.DetailImageScroller = function(param)
{
	this.param = param;
	this.layout = {
		container: param.target,
		wrapper: param.target.querySelector('.mp-detail-image-scroller-wrapper'),
		earLeft: null,
		earRight: null
	};
	this.earTimer = null;
};

BX.Rest.Marketplace.DetailImageScroller.prototype =
{
	init: function()
	{
		if(!BX.type.isDomNode(this.layout.container))
			return;

		this.layout.container.appendChild(this.getEarLeft());
		this.layout.container.appendChild(this.getEarRight());

		this.bindEvents();
		this.adjustEars();
	},

	bindEvents: function()
	{
		BX.bind(this.layout.wrapper, 'scroll', this.adjustEars.bind(this));
	},

	getEarLeft: function()
	{
		if(this.layout.earLeft)
			return this.layout.earLeft;

		return this.layout.earLeft = BX.create('div', {
			props: {
				className: 'mp-detail-image-scroller-ear mp-detail-image-scroller-ear-left'
			},
			events: {
				mouseenter: this.scrollToLeft.bind(this),
				mouseleave: this.stopAutoScroll.bind(this)
			}
		});
	},

	getEarRight: function()
	{
		if(this.layout.earRight)
			return this.scroller.earRight;

		return this.layout.earRight = BX.create('div', {
			props: {
				className: 'mp-detail-image-scroller-ear mp-detail-image-scroller-ear-right'
			},
			events: {
				mouseenter: this.scrollToRight.bind(this),
				mouseleave: this.stopAutoScroll.bind(this)
			}
		});
	},

	scrollToRight: function()
	{
		this.earTimer = setInterval(function() {
			this.layout.wrapper.scrollLeft += 10;
		}.bind(this), 20);
	},

	scrollToLeft: function()
	{
		this.earTimer = setInterval(function() {
			this.layout.wrapper.scrollLeft -= 10;
		}.bind(this), 20);
	},

	stopAutoScroll: function()
	{
		clearInterval(this.earTimer);
	},

	adjustEars: function()
	{
		var scroller = this.layout.wrapper;
		var scroll = scroller.scrollLeft;

		var isLeftVisible = scroll > 0;
		var isRightVisible = scroller.scrollWidth > (Math.round(scroll + scroller.offsetWidth));

		this.layout.container.classList[isLeftVisible ? 'add' : 'remove']('mp-detail-image-scroller-ear-left-shown');
		this.layout.container.classList[isRightVisible ? 'add' : 'remove']('mp-detail-image-scroller-ear-right-shown');
	}
};




