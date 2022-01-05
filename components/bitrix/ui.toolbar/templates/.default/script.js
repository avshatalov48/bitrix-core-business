;(function () {
	'use strict';

	BX.namespace('BX.UI');

	BX.UI.ToolbarManager =
	{
		toolbars: {},

		/**
		 *
		 * @return {BX.UI.Toolbar}
		 */
		create: function(options)
		{
			var toolbar = new BX.UI.Toolbar(options);

			if (this.get(toolbar.getId()))
			{
				throw new Error("The toolbar instance with the same 'id' already exists.");
			}

			this.toolbars[toolbar.getId()] = toolbar;

			return toolbar;
		},

		/**
		 *
		 * @return {BX.UI.Toolbar|null}
		 */
		getDefaultToolbar: function()
		{
			return this.get('default-toolbar');
		},

		/**
		 *
		 * @return {BX.UI.Toolbar|null}
		 */
		get: function(id)
		{
			return id in this.toolbars ? this.toolbars[id] : null
		}
	};

	BX.UI.Toolbar = function(options)
	{
		options = BX.type.isPlainObject(options) ? options : {};

		this.titleMinWidth = BX.type.isNumber(options.titleMinWidth) ? options.titleMinWidth : 158;
		this.titleMaxWidth = BX.type.isNumber(options.titleMaxWidth) ? options.titleMaxWidth : '';

		this.filterMinWidth = BX.type.isNumber(options.filterMinWidth) ? options.filterMinWidth : 300;
		this.filterMaxWidth = BX.type.isNumber(options.filterMaxWidth) ? options.filterMaxWidth : 748;

		this.id = BX.Type.isStringFilled(options.id) ? options.id : BX.Text.getRandom();
		this.toolbarContainer = options.target;

		if (!BX.Type.isDomNode(this.toolbarContainer))
		{
			throw new Error('BX.UI.Toolbar: "target" parameter is required.');
		}

		this.titleContainer = this.toolbarContainer.querySelector('.ui-toolbar-title-box');
		this.filterContainer = this.toolbarContainer.querySelector('.ui-toolbar-filter-box');
		this.filterButtons = this.toolbarContainer.querySelector('.ui-toolbar-filter-buttons');
		this.rightButtons = this.toolbarContainer.querySelector('.ui-toolbar-right-buttons');
		this.afterTitleButtons = this.toolbarContainer.querySelector('.ui-toolbar-after-title-buttons');

		if (!this.filterContainer)
		{
			this.filterMinWidth = 0;
			this.filterMaxWidth = 0;
		}

		this.buttons = Object.create(null);
		this.buttonIds = BX.Type.isArray(options.buttonIds) ? options.buttonIds : [];

		if (!this.buttonIds.length)
		{
			return
		}

		this.buttonIds.forEach(function(buttonId) {
			var button = BX.UI.ButtonManager.createByUniqId(buttonId);
			if (button)
			{
				button.getContainer().originalWidth = button.getContainer().offsetWidth;

				if (!button.getIcon() && !BX.Type.isStringFilled(button.getDataSet()['toolbarCollapsedIcon']))
				{
					if (button.getColor() === BX.UI.ButtonColor.PRIMARY)
					{
						button.setDataSet({
							'toolbarCollapsedIcon': BX.UI.ButtonIcon.ADD
						});
					}
					else
					{
						console.warn(
							'BX.UI.Toolbar: the button "' + button.getText() + '" ' +
							'doesn\'t have an icon for collapsed mode. ' +
							'Use the "data-toolbar-collapsed-icon" attribute.'
						);
					}
				}

				this.buttons[buttonId] = button;
			}
			else
			{
				console.warn('BX.UI.Toolbar: the button "' + buttonId + '" wasn\'t initialized.');
			}
		}, this);

		this.windowWidth = document.body.offsetWidth;
		this.reduceItemsWidth();

		window.addEventListener('resize', function() {
			if (this.isWindowIncreased())
			{
				this.increaseItemsWidth();
			}
			else
			{
				this.reduceItemsWidth();
			}

		}.bind(this));
	};

	BX.UI.Toolbar.prototype =
	{
		/**
		 *
		 * @return {Map<string, BX.UI.Button>}
		 */
		getButtons: function()
		{
			return this.buttons;
		},

		getButton: function(id)
		{
			return id in this.buttons ? this.buttons[id] : null;
		},

		getId: function()
		{
			return this.id;
		},

		isWindowIncreased: function()
		{
			var previousWindowWidth = this.windowWidth;
			var currentWindowWidth = document.body.offsetWidth;
			this.windowWidth = currentWindowWidth;

			return currentWindowWidth > previousWindowWidth;
		},

		getContainerSize: function()
		{
			return this.toolbarContainer.offsetWidth;
		},

		getInnerTotalWidth: function()
		{
			return this.toolbarContainer.scrollWidth;
		},

		reduceItemsWidth: function()
		{
			if (this.getInnerTotalWidth() <= this.getContainerSize())
			{
				return;
			}

			var buttons = Object.values(this.getButtons());
			for (var i = buttons.length - 1; i >= 0; i--)
			{
				var button = buttons[i];
				if (!button.getIcon() && !BX.Type.isStringFilled(button.getDataSet()['toolbarCollapsedIcon']))
				{
					continue;
				}

				if (button.isCollapsed())
				{
					continue;
				}

				button.setCollapsed(true);

				if (!button.getIcon())
				{
					button.setIcon(button.getDataSet()['toolbarCollapsedIcon']);
				}

				if (this.getInnerTotalWidth() <= this.getContainerSize())
				{
					return;
				}
			}
		},

		increaseItemsWidth: function()
		{
			var buttons = Object.values(this.getButtons());
			for (var i = 0; i < buttons.length; i++)
			{
				var button = buttons[i];
				var item = button.getContainer();
				if (!button.isCollapsed())
				{
					continue;
				}

				var newInnerWidth = (
					this.titleMinWidth +
					this.filterMinWidth +
					(this.afterTitleButtons ? this.afterTitleButtons.offsetWidth : 0) +
					(this.filterButtons ? this.filterButtons.offsetWidth : 0) +
					(this.rightButtons ? this.rightButtons.offsetWidth : 0) +
					(item.originalWidth - item.offsetWidth)
				);

				if (newInnerWidth > this.getContainerSize())
				{
					break;
				}

				button.setCollapsed(false);
				if (button.getIcon() === button.getDataSet()['toolbarCollapsedIcon'])
				{
					button.setIcon(null);
				}
			}
		},
	};

	BX.UI.Toolbar.Star = function()
	{
		this.initialized = false;
		this.currentPageInMenu = false;
		this.starContNode = null;

		BX.ready(function() {
			this.init();
		}.bind(this));
		BX.addCustomEvent('onFrameDataProcessed', function() {
			this.init();
		}.bind(this));
	}

	BX.UI.Toolbar.Star.prototype =
	{
		init: function()
		{
			this.starContNode = document.getElementById('uiToolbarStar');
			if (!this.starContNode)
			{
				return false;
			}

			if (this.initialized)
			{
				return false;
			}
			this.initialized = true;

			var currentFullPath = this.starContNode.getAttribute('data-bx-url');
			if (!BX.type.isNotEmptyString(currentFullPath))
			{
				currentFullPath = document.location.pathname + document.location.search;
			}
			currentFullPath = BX.Uri.removeParam(currentFullPath, [ 'IFRAME', 'IFRAME_TYPE' ]);

			top.BX.addCustomEvent('BX.Bitrix24.LeftMenuClass:onSendMenuItemData', function(params) {
				this.processMenuItemData(params);
			}.bind(this));

			top.BX.addCustomEvent('BX.Bitrix24.LeftMenuClass:onStandardItemChangedSuccess', function(params) {
				this.onStandardItemChangedSuccess(params);
			}.bind(this));

			top.BX.onCustomEvent('UI.Toolbar:onRequestMenuItemData', [{
				currentFullPath: currentFullPath,
				context: window,
			}]);

			return true;
		},

		processMenuItemData: function(params)
		{
			if (
				params.context
				&& params.context !== window
			)
			{
				return;
			}

			this.currentPageInMenu = params.currentPageInMenu;
			if (BX.type.isNotEmptyObject(params.currentPageInMenu))
			{
				BX.addClass(this.starContNode, 'ui-toolbar-star-active');
			}
			this.starContNode.title = BX.message(this.starContNode.classList.contains('ui-toolbar-star-active') ? 'UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU' : 'UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU');

			//default page
			if (
				BX.type.isDomNode(this.currentPageInMenu)
				&& this.currentPageInMenu.getAttribute('data-type') !== 'standard')
			{
				this.starContNode.title = BX.message('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE');
				BX.bind(this.starContNode, 'click', function ()
				{
					this.showMessage(BX.message('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE_DELETE_ERROR'));
				}.bind(this));

				return true;
			}

			//any page
			BX.bind(this.starContNode, 'click', function ()
			{
				var pageTitle = document.getElementById('pagetitle').innerText;
				var pageTitleTemplate = this.starContNode.getAttribute('data-bx-title-template');
				if (BX.type.isNotEmptyString(pageTitleTemplate))
				{
					pageTitle = pageTitleTemplate.replace(/#page_title#/i, pageTitle);
				}

				var pageLink = this.starContNode.getAttribute('data-bx-url');
				if (!BX.type.isNotEmptyString(pageLink))
				{
					pageLink = document.location.pathname + document.location.search;
				}
				pageLink = BX.Uri.removeParam(pageLink, [ 'IFRAME', 'IFRAME_TYPE' ])

				top.BX.onCustomEvent('UI.Toolbar:onStarClick', [{
					isActive: BX.hasClass(this.starContNode, 'ui-toolbar-star-active'),
					context: window,
					pageTitle: pageTitle,
					pageLink: pageLink,
				}]);
			}.bind(this));
		},

		onStandardItemChangedSuccess: function(params)
		{
			if (
				!BX.type.isBoolean(params.isActive)
				|| !this.starContNode
			)
			{
				return;
			}

			if (
				params.context
				&& params.context !== window
			)
			{
				return;
			}

			if (params.isActive)
			{
				this.showMessage(BX.message('UI_TOOLBAR_ITEM_WAS_ADDED_TO_LEFT'));
				this.starContNode.title = BX.message('UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU');
				BX.addClass(this.starContNode, 'ui-toolbar-star-active');
			}
			else
			{
				this.showMessage(BX.message('UI_TOOLBAR_ITEM_WAS_DELETED_FROM_LEFT'));
				this.starContNode.title = BX.message('UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU');
				BX.removeClass(this.starContNode, 'ui-toolbar-star-active');
			}
		},

		showMessage: function (message)
		{
			var popup = BX.PopupWindowManager.create('left-menu-message', this.starContNode, {
				content: message,
				darkMode: true,
				offsetTop: 2,
				offsetLeft: 0,
				angle: true,
				events: {
					onPopupClose: function ()
					{
						if (popup)
						{
							popup.destroy();
							popup = null;
						}
					}
				},
				autoHide: true
			});

			popup.show();

			setTimeout(function ()
			{
				if (popup)
				{
					popup.destroy();
					popup = null;
				}
			}, 3000);
		},
	};

})();
