;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var removeClass = BX.Landing.Utils.removeClass;
	var addClass = BX.Landing.Utils.addClass;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;
	var bind = BX.Landing.Utils.bind;
	var makeFilterablePopupMenu = BX.Landing.Utils.makeFilterablePopupMenu;
	var makeSelectablePopupMenu = BX.Landing.Utils.makeSelectablePopupMenu;
	var style = BX.Landing.Utils.style;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;

	/**
	 * Implements preview panel interface
	 *
	 * @extends {BX.Landing.UI.Panel.BasePanel}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Panel.Top = function(id, data)
	{
		BX.Landing.UI.Panel.BasePanel.apply(this, arguments);

		this.layout = document.querySelector(".landing-ui-panel-top");
		this.siteButton = this.layout.querySelector(".landing-ui-panel-top-chain-link-site");
		this.pageButton = this.layout.querySelector(".landing-ui-panel-top-chain-link-page");
		this.undoButton = this.layout.querySelector(".landing-ui-panel-top-history-undo");
		this.redoButton = this.layout.querySelector(".landing-ui-panel-top-history-redo");
		this.desktopButton = this.layout.querySelector(".landing-ui-button-desktop");
		this.tabletButton = this.layout.querySelector(".landing-ui-button-tablet");
		this.mobileButton = this.layout.querySelector(".landing-ui-button-mobile");
		this.iframeWrapper = document.querySelector(".landing-ui-view-iframe-wrapper");
		this.iframe = document.querySelector(".landing-ui-view");

		this.lastActive = this.desktopButton;
		this.loader = null;

		this.onDesktopSizeChange = this.onDesktopSizeChange.bind(this);
		this.onTabletSizeChange = this.onTabletSizeChange.bind(this);
		this.onMobileSizeChange = this.onMobileSizeChange.bind(this);
		this.onIframeClick = this.onIframeClick.bind(this);
		this.onSiteButtonClick = this.onSiteButtonClick.bind(this);
		this.onPageButtonClick = this.onPageButtonClick.bind(this);
		this.onUndo = this.onUndo.bind(this);
		this.onRedo = this.onRedo.bind(this);
		this.onKeyDown = this.onKeyDown.bind(this);
		this.adjustHistoryButtonsState = this.adjustHistoryButtonsState.bind(this);

		bind(this.desktopButton, "click", this.onDesktopSizeChange);
		bind(this.tabletButton, "click", this.onTabletSizeChange);
		bind(this.mobileButton, "click", this.onMobileSizeChange);
		bind(this.iframe.contentDocument, "click", this.onIframeClick);
		bind(this.undoButton, "click", this.onUndo);
		bind(this.redoButton, "click", this.onRedo);
		bind(document, "keydown", this.onKeyDown);

		onCustomEvent(document, "iframe:keydown", this.onKeyDown);
		onCustomEvent(window, "BX.Landing.History:init", this.adjustHistoryButtonsState);
		onCustomEvent(window, "BX.Landing.History:update", this.adjustHistoryButtonsState);

		var sitesCount = parseInt(BX.Landing.Main.getInstance().options.sites_count);
		var pagesCount = parseInt(BX.Landing.Main.getInstance().options.pages_count);

		if (sitesCount > 1 && this.siteButton)
		{
			bind(this.siteButton, "click", this.onSiteButtonClick);
		}

		if (pagesCount > 1 && this.pageButton)
		{
			bind(this.pageButton, "click", this.onPageButtonClick);
		}

		// Force history init
		var rootWindow = BX.Landing.PageObject.getRootWindow();
		var topHistory = rootWindow.BX.getClass('BX.Landing.History');
		if (topHistory)
		{
			rootWindow.BX.Landing.History.instance = null;
		}

		BX.Landing.History.getInstance();
	};


	BX.Landing.UI.Panel.Top.instance = null;


	/**
	 * Gets instance of BX.Landing.UI.Panel.Top
	 * @return {BX.Landing.UI.Panel.Top}
	 */
	BX.Landing.UI.Panel.Top.getInstance = function()
	{
		var rootWindow = BX.Landing.PageObject.getRootWindow();

		if (!rootWindow.BX.Landing.UI.Panel.Top.instance)
		{
			rootWindow.BX.Landing.UI.Panel.Top.instance = new BX.Landing.UI.Panel.Top("top_panel");
		}

		return rootWindow.BX.Landing.UI.Panel.Top.instance;
	};


	BX.Landing.UI.Panel.Top.prototype = {
		constructor: BX.Landing.UI.Panel.Top,
		__proto__: BX.Landing.UI.Panel.BasePanel.prototype,
		superclass: BX.Landing.UI.Panel.BasePanel.prototype,


		/**
		 * Handles keydown event
		 * @param {KeyboardEvent} event
		 */
		onKeyDown: function(event)
		{
			var key = event.keyCode || event.which;

			if (key === 90 && (window.navigator.userAgent.match(/win/i) ? event.ctrlKey : event.metaKey))
			{
				if (event.shiftKey)
				{
					event.preventDefault();
					this.onRedo();
				}
				else
				{
					event.preventDefault();
					this.onUndo();
				}
			}
		},


		/**
		 * Handles undo event
		 */
		onUndo: function()
		{
			if (
				BX.Landing.History.getInstance().canUndo()
				&& !this.undoButton.hasAttribute('data-disabled')
			)
			{
				this.getLoader().show(this.undoButton);
				addClass(this.undoButton, "landing-ui-onload");
				BX.Landing.History.getInstance().undo()
					.then(function() {
						this.getLoader().hide();
						removeClass(this.undoButton, "landing-ui-onload");
					}.bind(this));
			}
			else
			{
				this.getLoader().hide();
				removeClass(this.undoButton, "landing-ui-onload");
			}
		},


		/**
		 * Handles redo event
		 */
		onRedo: function()
		{
			if (
				BX.Landing.History.getInstance().canRedo()
				&& !this.redoButton.hasAttribute('data-disabled')
			)
			{
				this.getLoader().show(this.redoButton);
				addClass(this.redoButton, "landing-ui-onload");
				BX.Landing.History.getInstance().redo()
					.then(function() {
						this.getLoader().hide();
						removeClass(this.redoButton, "landing-ui-onload");
					}.bind(this));
			}
			else
			{
				this.getLoader().hide();
				removeClass(this.redoButton, "landing-ui-onload");
			}
		},


		/**
		 * Gets loader
		 * @return {BX.Loader}
		 */
		getLoader: function()
		{
			if (this.loader === null)
			{
				this.loader = new BX.Loader({size: 23, offset: {top: "3px", left: "1px"}});
				void style(this.loader.layout.querySelector(".main-ui-loader-svg-circle"), {
					"stroke-width": "4px"
				});
				void style(this.loader.layout.querySelector(".main-ui-loader-svg"), {
					"margin-top": "-3px"
				});
			}

			return this.loader;
		},


		/**
		 * Adjusts undo/redo buttons state. If need disables or enables button
		 * @param {BX.Landing.History} history
		 */
		adjustHistoryButtonsState: function(history)
		{
			if (history.canUndo())
			{
				this.undoButton.classList.remove("landing-ui-disabled");
				this.undoButton.removeAttribute('data-disabled');
			}
			else
			{
				this.undoButton.classList.add("landing-ui-disabled");
			}

			if (history.canRedo())
			{
				this.redoButton.classList.remove("landing-ui-disabled");
				this.redoButton.removeAttribute('data-disabled');
			}
			else
			{
				this.redoButton.classList.add("landing-ui-disabled");
			}
		},

		disableHistory: function()
		{
			this.undoButton.classList.add("landing-ui-disabled");
			this.undoButton.setAttribute('data-disabled', '');
			this.redoButton.classList.add("landing-ui-disabled");
			this.redoButton.setAttribute('data-disabled', '');
		},

		enableHistory: function()
		{
			this.adjustHistoryButtonsState(BX.Landing.History.getInstance());
		},

		disableDevices: function()
		{
			this.desktopButton.classList.add("landing-ui-disabled");
			this.tabletButton.classList.add("landing-ui-disabled");
			this.mobileButton.classList.add("landing-ui-disabled");
		},

		enableDevices: function()
		{
			this.desktopButton.classList.remove("landing-ui-disabled");
			this.tabletButton.classList.remove("landing-ui-disabled");
			this.mobileButton.classList.remove("landing-ui-disabled");
		},

		/**
		 * Handles desktop size change event
		 */
		onDesktopSizeChange: function()
		{
			this.lastActive.classList.remove("active");
			this.lastActive = this.desktopButton;
			this.desktopButton.classList.add("active");

			BX.DOM.write(function() {
				this.iframeWrapper.style.width = null;
			}.bind(this));

			this.iframeWrapper.dataset.postfix = "";
			BX.Landing.Main.getInstance().enableControls();
			BX.Landing.Main.getInstance().setNoTouchDevice();
		},


		/**
		 * Handles tablet size change event
		 */
		onTabletSizeChange: function()
		{
			this.lastActive.classList.remove("active");
			this.lastActive = this.tabletButton;
			this.tabletButton.classList.add("active");

			BX.DOM.write(function() {
				this.iframeWrapper.style.width = "990px";
			}.bind(this));

			this.iframeWrapper.dataset.postfix = "--md";
			BX.Landing.Main.getInstance().disableControls();
			BX.Landing.Main.getInstance().setTouchDevice();
		},


		/**
		 * Handles mobile size change event
		 */
		onMobileSizeChange: function()
		{
			this.lastActive.classList.remove("active");
			this.lastActive = this.mobileButton;
			this.mobileButton.classList.add("active");

			BX.DOM.write(function() {
				this.iframeWrapper.style.width = "375px";
			}.bind(this));

			this.iframeWrapper.dataset.postfix = "--md";
			BX.Landing.Main.getInstance().disableControls();
			BX.Landing.Main.getInstance().setTouchDevice();
		},


		onSiteButtonClick: function(event)
		{
			event.preventDefault();

			if (!this.siteMenu)
			{
				var loader = new BX.Loader({size: 40});
				this.siteMenu = new BX.PopupMenuWindow({
					id: "site_list_menu",
					bindElement: this.siteButton,
					events: {
						onPopupClose: function() {
							this.siteButton.classList.remove("landing-ui-active");
							this.siteButton.blur();
						}.bind(this)
					},
					menuShowDelay: 0,
					offsetTop: 9
				});

				this.siteMenu.popupWindow.contentContainer.style.minHeight = "60px";
				this.siteMenu.popupWindow.contentContainer.style.minWidth = "160px";
				loader.show(this.siteMenu.popupWindow.contentContainer);

				var options = {
					siteId: BX.Landing.Main.getInstance().options.site_id,
					landingId: BX.Landing.Main.getInstance().id,
					filter: {
						'=TYPE': BX.Landing.Main.getInstance().options.params.type,
						'SPECIAL': 'N'
					}
				};

				BX.Landing.Backend.getInstance()
					.getSites(options)
					.then(function(sites) {
						return new Promise(function(resolve) {
							setTimeout(resolve.bind(null, sites), 300);
						});
					})
					.then(function(sites) {
						makeFilterablePopupMenu(this.siteMenu);
						makeSelectablePopupMenu(this.siteMenu);

						sites.forEach(function(site) {
							this.siteMenu.addMenuItem({
								id: site.ID,
								text: encodeDataValue(site.TITLE),
								items: (function() {
									var items = [];
									var editMask = BX.Landing.Main.getInstance().options.params.sef_url.site_edit;
									var showMask = BX.Landing.Main.getInstance().options.params.sef_url.site_show;

									items.push({
										text: BX.Landing.Loc.getMessage("LANDING_ENTITIES_MENU_PAGES_LIST"),
										href: showMask.replace("#site_show#", site.ID)
									});

									items.push({
										text: BX.Landing.Loc.getMessage("LANDING_ENTITIES_MENU_EDIT"),
										href: editMask.replace("#site_edit#", site.ID)
									});

									return items;
								})()
							});
						}, this);
						loader.hide();
					}.bind(this));
			}

			this.siteButton.classList.add("landing-ui-active");
			this.siteMenu.show();
		},


		/**
		 * Handles page button click event
		 * @param {MouseEvent} event
		 */
		onPageButtonClick: function(event)
		{
			event.preventDefault();

			if (!this.pageMenu)
			{
				var loader = new BX.Loader({size: 40});
				this.pageMenu = new BX.PopupMenuWindow({
					id: "page_list_menu",
					bindElement: this.pageButton,
					events: {
						onPopupClose: function() {
							this.pageButton.classList.remove("landing-ui-active");
							this.pageButton.blur();
						}.bind(this)
					},
					menuShowDelay: 0,
					offsetTop: 9
				});

				this.pageMenu.popupWindow.contentContainer.style.minHeight = "60px";
				this.pageMenu.popupWindow.contentContainer.style.minWidth = "160px";
				loader.show(this.pageMenu.popupWindow.contentContainer);

				var options = {
					siteId: BX.Landing.Main.getInstance().options.site_id,
					landingId: BX.Landing.Main.getInstance().id,
					filter: {
						'=TYPE': BX.Landing.Main.getInstance().options.params.type
					}
				};

				BX.Landing.Backend.getInstance()
					.getLandings({siteId: options.siteId})
					.then(function(landings) {
						return new Promise(function(resolve) {
							setTimeout(resolve.bind(null, landings), 300);
						});
					})
					.then(function(landings) {
						makeFilterablePopupMenu(this.pageMenu);
						makeSelectablePopupMenu(this.pageMenu);

						landings.forEach(function(landing) {
							if ((landing.FOLDER_ID === null || parseInt(landing.FOLDER_ID) === 0) && !landing.IS_AREA)
							{
								this.pageMenu.addMenuItem({
									id: landing.ID,
									text: encodeDataValue(landing.TITLE),
									items: (function() {
										var items = [];
										var editMask = BX.Landing.Main.getInstance().options.params.sef_url.landing_edit;
										var viewMask = BX.Landing.Main.getInstance().options.params.sef_url.landing_view;

										if (landing.FOLDER === "Y")
										{
											var siteShowMask = BX.Landing.Main.getInstance().options.params.sef_url.site_show;
											items.push({
												text: BX.Landing.Loc.getMessage("LANDING_ENTITIES_MENU_PAGES_LIST"),
												href: BX.Landing.Utils.addQueryParams(
													siteShowMask.replace("#site_show#", landing.SITE_ID),
													{
														folderId: landing.ID
													}
												)
											});
										}

										items.push({
											text: BX.Landing.Loc.getMessage("LANDING_ENTITIES_MENU_PAGES_EDIT"),
											href: viewMask.replace("#site_show#", landing.SITE_ID).replace("#landing_edit#", landing.ID)
										});

										items.push({
											text: BX.Landing.Loc.getMessage("LANDING_ENTITIES_MENU_PAGES_SETTINGS"),
											href: editMask.replace("#site_show#", landing.SITE_ID).replace("#landing_edit#", landing.ID)
										});

										return items;
									})()
								});
							}
						}, this);
						requestAnimationFrame(function() {
							loader.hide();
						});
					}.bind(this));
			}

			this.pageButton.classList.add("landing-ui-active");
			this.pageMenu.show();
		},


		/**
		 * Handles frame click event
		 */
		onIframeClick: function()
		{
			if (this.siteMenu)
			{
				this.siteMenu.close();
			}

			if (this.pageMenu)
			{
				this.pageMenu.close();
			}
		},

		getFormNameLayout: function()
		{
			return this.layout.querySelector('.landing-ui-panel-top-form-name');
		},

		setFormName: function(text)
		{
			if (BX.Type.isString(text))
			{
				var formNameLayout = this.getFormNameLayout();
				if (BX.Type.isDomNode(formNameLayout))
				{
					formNameLayout.firstElementChild.textContent = text;
					formNameLayout.firstElementChild.setAttribute('title', text);
				}
			}
		},
	};

})();