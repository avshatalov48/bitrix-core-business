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

		this.currentFavoriteId = BX.type.isNumber(options.currentFavoriteId) ? options.currentFavoriteId : 0;
		this.initPagetitleStar();

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
			return;
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

		initPagetitleStar: function ()
		{
			var starContNode = BX('uiToolbarStar');
			if (!starContNode)
			{
				return false;
			}

			if (this.currentFavoriteId)
			{
				BX.addClass(starContNode, "ui-toolbar-star-active");
			}

			BX.bind(starContNode, 'click', function ()
			{
				BX.adminFav.titleLinkClick(starContNode, this.currentFavoriteId);
				if (BX.hasClass(starContNode, 'ui-toolbar-star-active'))
				{
					BX.removeClass(starContNode, "ui-toolbar-star-active");

				}
				else
				{
					BX.addClass(starContNode, "ui-toolbar-star-active");
				}
				setTimeout(function()
				{
					BX.removeClass(starContNode, "adm-fav-link-active");
				}, 300);

			}.bind(this));

			return true;
		}
	};
})();
