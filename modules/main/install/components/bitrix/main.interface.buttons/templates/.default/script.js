BX.namespace('BX.Main');

if (typeof(BX.Main.interfaceButtons) === 'undefined')
{
	/**
	 * @param {object} params parameters
	 * @property {string} containerId @required
	 * @property {string} theme
	 * @property {object} classes
	 * @property {string} classes.item Class for list item
	 * @property {string} classes.itemSublink Class for sublink (ex. Add link)
	 * @property {string} classes.itemText Class for item text and submenu item text
	 * @property {string} classes.itemCounter Class for list item counter and submenu item counter
	 * @property {string} classes.itemIcon Class for list item icon and submenu item icon
	 * @property {string} classes.itemMore Class for more button
	 * @property {string} classes.itemOver Class for hovered item
	 * @property {string} classes.itemActive Class for active item
	 * @property {string} classes.itemDisabled Class for disabled elements
	 * @property {string} classes.itemLocked Class for locked item. Added for list and submenu item
	 * @property {string} classes.menuShown Class for open menu
	 * @property {string} classes.onDrag Class added for container on dragstart event and removed on drag end event
	 * @property {string} classes.dropzone Class for dropzone in submenu
	 * @property {string} classes.separator Class for submenu separator before disabled items
	 * @property {string} classes.submenuItem Class for submenu item
	 * @property {string} classes.submenu Class for submenu container
	 * @property {string} classes.secret Class for hidden alias items (set display: none; for items)
	 * @property {object} messages Messages object. Contains localization strings
	 * @property {string} messages.MIB_DROPZONE_TEXT Dropzone text
	 * @property {string} messages.MIB_LICENSE_BUY_BUTTON License window Buy button text
	 * @property {string} messages.MIB_LICENSE_TRIAL_BUTTON License window Trial button text
	 * @property {string} messages.MIB_LICENSE_WINDOW_HEADER_TEXT License window header text
	 * @property {string} messages.MIB_LICENSE_WINDOW_TEXT License window content text
	 * @property {string} messages.MIB_LICENSE_WINDOW_TRIAL_SUCCESS_TEXT Trial success text
	 * @property {object} licenseWindow Settings for license window
	 * @property {string} licenseWindow.isFullDemoExists Y|N
	 * @property {string} licenseWindow.hostname Hostname for license window ajax calls
	 * @property {string} licenseWindow.ajaxUrl Ajax handler url
	 * @property {string} licenseWindow.licenseAllPath
	 * @property {string} licenseWindow.licenseDemoPath
	 * @property {string} licenseWindow.featureGroupName
	 * @property {string} licenseWindow.ajaxActionsUrl
	 * @param {HTMLElement} container
	 */
	BX.Main.interfaceButtons = function(container, params)
	{
		/**
		 * Sets default values
		 */
		this.classItem = 'main-buttons-item';
		this.classItemSublink = 'main-buttons-item-sublink';
		this.classItemText = 'main-buttons-item-text';
		this.classItemCounter = 'main-buttons-item-counter';
		this.classItemIcon = 'main-buttons-item-icon';
		this.classItemMore = 'main-buttons-item-more';
		this.classOnDrag = 'main-buttons-drag';
		this.classDropzone = 'main-buttons-submenu-dropzone';
		this.classSeparator = 'main-buttons-submenu-delimiter';
		this.classHiddenLabel = 'main-buttons-hidden-label';
		this.classSubmenuItem = 'main-buttons-submenu-item';
		this.classItemDisabled = 'main-buttons-disabled';
		this.classItemOver = '--over';
		this.classMenuShown = '--menu-shown';
		this.classItemActive = 'main-buttons-item-active';
		this.classSubmenu = 'main-buttons-submenu';
		this.classSecret = 'secret';
		this.classItemLocked = '--locked';
		this.classExtraItemLink = '';
		this.classExtraItemText = '';
		this.classExtraItemIcon = '';
		this.classExtraItemCounter = '';
		this.submenuIdPrefix = 'main_buttons_popup_';
		this.childMenuIdPrefix = 'main_buttons_popup_child_';
		this.submenuWindowIdPrefix = 'menu-popup-';
		this.classSettingMenuItem = 'main-buttons-submenu-setting';
		this.classEditState = 'main-buttons-edit';
		this.classEditItemButton = 'main-buttons-item-edit-button';
		this.classDragItemButton = 'main-buttons-item-drag-button';
		this.classSettingsApplyButton = 'main-buttons-submenu-settings-apply';
		this.classSettingsResetButton = 'main-buttons-submenu-settings-reset';
		this.classSetHome = 'main-buttons-set-home';
		this.classSetHide = 'main-buttons-set-hide';
		this.classManage = 'main-buttons-manage';
		this.classContainer = 'main-buttons';
		this.classSubmenuNoHiddenItem = 'main-buttons-submenu-item-no-hidden';
		this.classDefaultSubmenuItem = 'menu-popup-item';
		this.classDefaultSubmenuDelimimeter = 'popup-window-delimiter-section';
		this.classInner = 'main-buttons-inner-container';
		this.listContainer = null;
		this.dragItem = null;
		this.overItem = null;
		this.moreButton = null;
		this.messages = null;
		this.licenseParams = null;
		this.ajaxSettings = null;
		this.enableItemMouseEnter = true;
		this.menuShowTimeout = null;
		this.isMoreMenuShown = false;
		this.onDragStarted = false;
		this.isSettingsEnabled = true;
		this.containerId = params.containerId;
		this.isEditEnabledState = false;
		this.theme = BX.Type.isStringFilled(params.theme) ? params.theme : 'default';
		this.maxItemLength =
			BX.Type.isNumber(params.maxItemLength) && params.maxItemLength > 6 ? params.maxItemLength : 20
		;
		this.tmp = {};
		this.itemData = new WeakMap();

		this.handleMoreMenuItemMouseEnter = this.handleMoreMenuItemMouseEnter.bind(this);

		this.init(container, params);

		/**
		 * Public methods and properties
		 */
		return {
			addMenuItem: this.addMenuItem.bind(this),
			deleteMenuItem: this.deleteMenuItem.bind(this),
			updateMenuItemText: this.updateMenuItemText.bind(this),

			getItemById: this.getItemById.bind(this),
			getAllItems: this.getAllItems.bind(this),
			getHiddenItems: this.getHiddenItems.bind(this),
			getVisibleItems: this.getVisibleItems.bind(this),
			getDisabledItems: this.getDisabledItems.bind(this),
			getMoreButton: this.getMoreButton.bind(this),
			adjustMoreButtonPosition: this.adjustMoreButtonPosition.bind(this),
			getItemData: this.getItemData.bind(this),

			// Compatible methods
			getSubmenu: this.getMoreMenu.bind(this),
			showSubmenu: this.showMoreMenu.bind(this),
			closeSubmenu: this.closeMoreMenu.bind(this),
			refreshSubmenu: this.refreshMoreMenu.bind(this),

			getMoreMenu: this.getMoreMenu.bind(this),
			showMoreMenu: this.showMoreMenu.bind(this),
			closeMoreMenu: this.closeMoreMenu.bind(this),
			refreshMoreMenu: this.refreshMoreMenu.bind(this),

			getCurrentSettings: this.getCurrentSettings.bind(this),
			saveSettings: this.saveSettings.bind(this),
			updateCounter: this.updateCounter.bind(this),
			getActive: this.getActive.bind(this),
			isDisabled: this.isDisabled.bind(this),
			isVisibleItem: this.isVisibleItem.bind(this),
			isEditEnabled: this.isEditEnabled.bind(this),
			isActiveInMoreMenu: this.isActiveInMoreMenu.bind(this),
			isSettingsEnabled: this.isSettingsEnabled,
			classes:
			{
				item: this.classItem,
				itemText: this.classItemText,
				itemCounter: this.classItemCounter,
				itemIcon: this.classItemIcon,
				itemDisabled: this.classItemDisabled,
				itemOver: this.classItemOver,
				itemActive: this.classItemActive,
				itemLocked: this.classItemLocked,
				menuShown: this.classMenuShown,
				submenu: this.classSubmenu,
				submenuItem: this.classSubmenuItem,
				containerOnDrag: this.classOnDrag,
				classSettingMenuItem: this.classSettingMenuItem
			},
			itemsContainer: this.listContainer,
			itemsContainerId: this.listContainer.id
		};
	};

	//noinspection JSUnusedGlobalSymbols,JSUnusedGlobalSymbols
	BX.Main.interfaceButtons.prototype =
	{
		init: function(container, params)
		{
			this.listContainer = BX(this.getId());

			if (!BX.Type.isPlainObject(params))
			{
				throw 'BX.MainButtons: params is not Object';
			}

			if (!('containerId' in params) || !BX.Type.isStringFilled(params.containerId))
			{
				throw 'BX.MainButtons: containerId not set in params';
			}

			if (!BX.Type.isDomNode(this.listContainer))
			{
				throw 'BX.MainButtons: #' + params.containerId + ' is not dom node';
			}

			if (('classes' in params) && BX.Type.isPlainObject(params.classes))
			{
				this.setCustomClasses(params.classes);
			}

			if (('messages' in params) && BX.Type.isPlainObject(params.messages))
			{
				this.setMessages(params.messages);
			}

			if (('licenseWindow' in params) && BX.Type.isPlainObject(params.licenseWindow))
			{
				this.setLicenseWindowParams(params.licenseWindow);
			}

			if ('disableSettings' in params && params.disableSettings === "true")
			{
				this.isSettingsEnabled = false;
				this.visibleControlMoreButton();
			}

			this.initSaving(params.ajaxSettings);

			this.moreButton = this.getMoreButton();

			this.listChildItems = {};

			this.initItems();

			this.adjustMoreButtonPosition();
			this.bindEventsOnMoreButton();
			this.bindOnResize();

			BX.Event.bind(this.getContainer(), 'click', BX.delegate(this._onDocumentClick, this));
			BX.addCustomEvent("onPullEvent-main", BX.delegate(this._onPush, this));

			this.updateMoreButtonCounter();

			if (this.isActiveInMoreMenu())
			{
				this.activateItem(this.moreButton);
			}

			const homeItem = this.getHomeItem();
			if (homeItem)
			{
				const { url: firstPageLink } = homeItem;
				this.lastHomeLink = firstPageLink;
			}

			const showChildButtons = Array.from(this.container.querySelectorAll('.main-buttons-item-child-button'));
			showChildButtons.forEach(function(button) {
				const realChildButton = button.closest('.main-buttons-item-child');
				if (realChildButton.dataset.isOpened)
				{
					this.realChildButton = realChildButton;
					const clonedChildButton = realChildButton.closest('.main-buttons-item-child-button-cloned')
					if (clonedChildButton)
					{
						this.clonedChildButton = clonedChildButton;
					}
				}

				BX.Event.bind(button, 'click', this.onShowChildButtonClick.bind(this));

			}, this);
		},

		calculateChildListWidth: function()
		{
			if (this.realChildButton)
			{
				const buttons = this.realChildButton
					.querySelectorAll('.main-buttons-item-child-list-inner .main-buttons-item');

				const offset = 10;
				return Array.from(buttons).reduce(function(acc, button) {
					const width = BX.Text.toNumber(BX.Dom.style(button, 'width'));
					const marginLeft = BX.Text.toNumber(BX.Dom.style(button, 'margin-left'));
					const marginRight = BX.Text.toNumber(BX.Dom.style(button, 'margin-right'));

					return acc + width + marginLeft + marginRight;
				}, offset);
			}
			return 0;
		},

		onShowChildButtonClick: function(event)
		{
			event.preventDefault();

			if (!this.realChildButton)
			{
				this.realChildButton = event.currentTarget.closest('.main-buttons-item-child');
			}

			const childListContainer = this.realChildButton.querySelector('.main-buttons-item-child-list');

			this.enableItemMouseEnter = false;
			setTimeout(() => {
				this.enableItemMouseEnter = true;
			}, 200);

			const childIds = BX.Dom.attr(this.realChildButton, 'data-child-items');
			const isOpened = BX.Dom.attr(this.realChildButton, 'data-is-opened');
			let expandedParentIds = {};
			if (isOpened)
			{
				BX.Dom.attr(this.realChildButton, 'data-is-opened', null);

				childIds.forEach(function(childId) {
					const button = this.getContainer().querySelector('[data-id="'+childId+'"]');
					BX.Dom.style(button, 'display', null);
					if (childId.hasOwnProperty('PARENT_ITEM_ID'))
					{
						expandedParentIds[childId['PARENT_ITEM_ID']] = 'N';
					}
				}, this);

				if (this.clonedChildButton)
				{
					BX.Dom.remove(this.clonedChildButton);
				}

				BX.Dom.style(childListContainer, {
					overflow: null,
					'max-width': null,
				});

				expandedParentIds = JSON.stringify(expandedParentIds);
				this.saveOptions('expanded_lists', expandedParentIds);
			}
			else
			{
				BX.Dom.attr(this.realChildButton, 'data-is-opened', true);
				BX.Dom.style(childListContainer, 'max-width', this.calculateChildListWidth() + 'px');

				this.cloneChildButton(this.realChildButton);

				childIds.forEach((childId) => {
					const button = this.getContainer().querySelector('[data-id="'+childId+'"]');
					BX.Dom.insertBefore(button, this.realChildButton);
					BX.Dom.style(button, 'display', 'inline-block');
					if (childId.hasOwnProperty('PARENT_ITEM_ID'))
					{
						expandedParentIds[childId['PARENT_ITEM_ID']] = 'Y';
					}
				});

				setTimeout(() => {
					BX.Dom.style(childListContainer, 'overflow', 'unset');
				}, 200);

				expandedParentIds = JSON.stringify(expandedParentIds);
				this.saveOptions('expanded_lists', expandedParentIds);
			}

			setTimeout(() => {
				this._onResizeHandler();
			}, 200);
		},

		cloneChildButton: function(realChildButton)
		{
			this.clonedChildButton = BX.Runtime.clone(realChildButton);

			const childList = this.clonedChildButton.querySelector('.main-buttons-item-child-list');
			if (childList)
			{
				BX.Dom.remove(childList);
			}

			BX.Dom.addClass(this.clonedChildButton, 'main-buttons-item-child-button-cloned');
			BX.Dom.style(this.clonedChildButton, 'transition', 'none');
			BX.Dom.insertBefore(this.clonedChildButton, realChildButton);
			BX.Event.bind(this.clonedChildButton, 'click', this.onShowChildButtonClick.bind(this));

			setTimeout(() => {
				BX.Dom.style(this.clonedChildButton, 'transition', null);
			}, 0);
		},

		_onDocumentClick: function(event)
		{
			if (this.isDragButton(event.target))
			{
				event.preventDefault();
				event.stopPropagation();
			}

			let item = this.getItem(event);
			if (BX.Type.isDomNode(item))
			{
				if (this.isSettings(item))
				{
					this.enableEdit();
					return false;
				}

				if (this.isApplySettingsButton(item))
				{
					event.preventDefault();
					event.stopPropagation();
					this.disableEdit();

					return false;
				}

				if (this.isResetSettingsButton(item))
				{
					this.resetSettings();
					return false;
				}

				if (this.isEditButton(event.target))
				{
					this.handleEditButtonClick(event);

					return false;
				}

				if (this.isSetHide(item))
				{
					const visibleItems = this.getVisibleItems();
					const visibleItemsLength = BX.Type.isArray(visibleItems) ? visibleItems.length : null;
					const id = this.editItemData.ID.replace(this.listContainer.id + '_', '');
					let currentItem = this.getItemById(id);
					const currentAlias = this.getItemAlias(currentItem);

					currentItem = this.isVisibleItem(currentItem) ? currentItem : currentAlias;

					if (this.isDisabled(currentAlias))
					{
						this.enableItem(currentAlias);

					}
					else if (!this.isDisabled(currentAlias) && visibleItemsLength > 2)
					{
						this.disableItem(currentAlias);
					}

					if (visibleItemsLength === 1)
					{
						BX.onCustomEvent(window, 'BX.Main.InterfaceButtons:onHideLastVisibleItem', [currentItem, this]);
					}

					this.refreshMoreMenu();
					this.saveSettings();

					this.adjustMoreButtonPosition();

					if (this.isEditEnabled())
					{
						this.enableEdit();
					}

					this.editMenu.popupWindow.close();

					return false;
				}

				if (this.isSetHome(item))
				{
					const id = this.editItemData.ID.replace(this.listContainer.id + '_', '');
					const currentItem = this.getItemById(id);
					const currentAlias = this.getItemAlias(currentItem);

					if (this.isDisabled(currentAlias))
					{
						this.enableItem(currentAlias);
					}

					this.listContainer.insertBefore(currentItem, BX.firstChild(this.listContainer));

					this.adjustMoreButtonPosition();
					this.refreshMoreMenu();
					this.saveSettings();

					if (this.isEditEnabled())
					{
						this.enableEdit();
					}

					this.editMenu.popupWindow.close();

					return false;
				}

				if (!this.isDragButton(event.target) && !this.isEditButton(event.target))
				{
					const itemData = this.getItemData(item);
					let dataOnClick = itemData['ON_CLICK']

					if (this.isSublink(event.target))
					{
						dataOnClick = BX.Type.isPlainObject(itemData['SUB_LINK']) ? itemData['SUB_LINK']['ON_CLICK'] : '';
					}

					if (BX.Type.isStringFilled(dataOnClick))
					{
						event.preventDefault();
						this.execScript(dataOnClick, event);
					}
				}
			}

			if (this.isEditEnabled() && this.getMoreMenu())
			{
				this.getMoreMenu().getPopupWindow().setAutoHide(false);
			}
		},

		/**
		 * @return {boolean}
		 */
		isActiveInMoreMenu: function()
		{
			const hiddenItems = this.getHiddenItems();
			const disabledItems = this.getDisabledItems();
			const items = hiddenItems.concat(disabledItems);

			return items.some(function(current) {
				const itemData = this.getItemData(current);

				return itemData['IS_ACTIVE'] === true;
			}, this);
		},

		_onPush: function (command, params)
		{
			if (command === "user_counter" && params && BX.message("SITE_ID") in params)
			{
				const counters = params[BX.message("SITE_ID")];
				for (const counterId in counters)
				{
					if (counters.hasOwnProperty(counterId))
					{
						this.updateCounter(counterId, counters[counterId]);
					}
				}
			}
		},

		/**
		 * Gets active element
		 * @return {?HTMLElement}
		 */
		getActive: function()
		{
			let items = this.getAllItemsData();
			let result = null;
			let rootActiveItem = null;

			while (BX.Type.isArrayFilled(items))
			{
				const item = items.shift();
				if (item['IS_ACTIVE'] === true)
				{
					if (rootActiveItem === null)
					{
						rootActiveItem = item;
					}

					result = item;
					items = BX.Type.isArrayFilled(item['ITEMS']) ? [...item['ITEMS']] : null;
				}
			}

			if (result !== null && rootActiveItem !== null)
			{
				const node = BX(rootActiveItem.ID);
				if (BX.Type.isDomNode(node))
				{
					result.NODE = node;
				}
				else
				{
					result.NODE = null;
				}
			}

			return result;
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isSetHome: function(item)
		{
			return BX.Dom.hasClass(item, this.classSetHome);
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isSetHide: function(item)
		{
			return BX.Dom.hasClass(item, this.classSetHide);
		},

		/**
		 * @return {?HTMLElement}
		 */
		getSettingsButton: function()
		{
			return BX.Buttons.Utils.getByClass(this.getMoreMenuContainer(), this.classSettingMenuItem);
		},

		/**
		 * @return {?HTMLElement}
		 */
		getSettingsApplyButton: function()
		{
			return BX.Buttons.Utils.getByClass(this.getMoreMenuContainer(), this.classSettingsApplyButton);
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isApplySettingsButton: function(item)
		{
			return BX.Dom.hasClass(item, this.classSettingsApplyButton);
		},

		enableEdit: function()
		{
			const menu = this.getMoreMenu();
			if (menu)
			{
				const popup = menu.getPopupWindow();
				popup.setAutoHide(false);

				BX.Dom.addClass(popup.getPopupContainer(), this.classEditState);
			}

			BX.Dom.addClass(this.listContainer, this.classEditState);

			this.isEditEnabledState = true;
		},

		disableEdit: function()
		{
			const menu = this.getMoreMenu();
			if (menu)
			{
				const popup = menu.getPopupWindow();
				popup.setAutoHide(true);
				BX.Dom.removeClass(popup.getPopupContainer(), this.classEditState);
			}

			BX.Dom.removeClass(this.listContainer, this.classEditState);

			this.isEditEnabledState = false;

			this.destroyItemEditMenu();
		},

		/**
		 * @return {boolean}
		 */
		isEditEnabled: function()
		{
			return this.isEditEnabledState;
		},

		/**
		 * @param {object} itemData
		 * @param {HTMLElement} node
		 */
		showItemEditMenu: function(itemData, node)
		{
			if (BX.Type.isPlainObject(itemData) && 'ID' in itemData)
			{
				const menuId = [this.listContainer.id, '_edit_item'].join('');
				let menu = BX.Main.MenuManager.getMenuById(menuId);
				if (menu)
				{
					BX.Main.MenuManager.destroy(menuId);
				}

				menu = this.createItemEditMenu(itemData, menuId, node);
				menu.popupWindow.show();
			}
		},

		destroyItemEditMenu: function()
		{
			const menuId = [this.listContainer.id, '_edit_item'].join('');
			const menu = BX.Main.MenuManager.getMenuById(menuId);
			if (menu)
			{
				BX.Main.MenuManager.destroy(menuId);
			}
		},

		/**
		 * @return {?HTMLElement}
		 */
		getContainer: function()
		{
			if (!BX.Type.isDomNode(this.container))
			{
				this.container = BX(this.containerId).parentNode.parentNode;
			}

			return this.container;
		},

		/**
		 * @return {?BX.PopupMenu}
		 */
		getItemEditMenu: function()
		{
			return BX.Main.MenuManager.getMenuById([this.listContainer.id, '_edit_item'].join(''));
		},

		/**
		 * @param {object} itemData
		 * @param {string} menuId
		 * @param {HTMLElement} node BX.PopupMenu bindElement
		 * @return {?BX.PopupMenu}
		 */
		createItemEditMenu: function(itemData, menuId, node)
		{
			const menuItems = [
				{
					text: this.message('MIB_SET_HOME'),
					className: 'main-buttons-set-home menu-popup-no-icon'
				}
			];

			const id = itemData['ID'].replace(this.listContainer.id + '_', '');
			const currentItem = this.getItemById(id);

			if (this.isDisabled(currentItem))
			{
				menuItems.push({
					text: this.message('MIB_SET_SHOW'),
					className: 'main-buttons-set-hide menu-popup-no-icon'
				});
			}
			else
			{
				menuItems.push({
					text: this.message('MIB_SET_HIDE'),
					className: 'main-buttons-set-hide menu-popup-no-icon'
				});
			}

			if (itemData['IS_PINNED'])
			{
				const parentItem = this.getParentItem(itemData['ID']);
				menuItems.push({
					text: this.message('MIB_UNPIN_ITEM').replace('#NAME#', parentItem ? parentItem['TEXT'] : ''),
					onclick: (event, menuItem) => {
						this.handleItemUnpin(itemData, currentItem);
						menuItem.getMenuWindow().close();
					}
				});
			}

			const nodeRect = BX.pos(node);
			const menuParams = {
				menuId: menuId,
				anchor: node,
				menuItems: menuItems,
				settings: {
					autoHide: true,
					offsetTop: 0,
					offsetLeft: (nodeRect.width / 2),
					zIndex: 20,
					angle: {
						position: 'top',
						offset: (nodeRect.width / 2)
					}
				}
			};

			const menu = BX.Main.MenuManager.create(
				menuParams.menuId,
				menuParams.anchor,
				menuParams.menuItems,
				menuParams.settings
			);

			if (this.isVisibleItem(currentItem))
			{
				itemData.NODE = currentItem;
			}
			else
			{
				itemData.NODE = this.getItemAlias(currentItem);
			}

			this.editItemData = itemData;

			if ('menuItems' in menu && BX.Type.isArray(menu.menuItems))
			{
				menu.menuItems.forEach(function(current) {
					BX.Event.bind(current.layout.item, 'click', BX.delegate(this._onDocumentClick, this));
				}, this);
			}

			BX.onCustomEvent(window, 'BX.Main.InterfaceButtons:onBeforeCreateEditMenu', [menu, itemData, this]);

			this.editMenu = menu;

			return menu;
		},

		prepareMenuItemData: function(data)
		{
			const itemMenuData = {
				CLASS: "",
				CLASS_SUBMENU_ITEM: "",
				COUNTER: 0,
				COUNTER_ID: data.counterId,
				DATA_ID: data.dataId,
				HAS_CHILD: false,
				HAS_MENU: false,
				HTML: "",
				ID: data.id,
				IS_ACTIVE: false,
				IS_DISABLED: "false",
				IS_LOCKED: false,
				IS_PASSIVE: false,
				MAX_COUNTER_SIZE: 99,
				NODE: BX.Tag.render`<div id="${data.id}" class="main-buttons-item"></div>`,
				ON_CLICK: data.onClick,
				SUB_LINK: false,
				SUPER_TITLE: false,
				TEXT: data.text,
				TITLE: "",
				URL: data.url,
			};

			return itemMenuData;
		},

		addMenuItem: function(itemData)
		{
			const settings = this.getCurrentSettings();
			const settingsKeys = Object.keys(settings);
			const menuItemData = this.prepareMenuItemData(itemData);
			const item = this.createRootItem(menuItemData);

			const afterNode = this.getItemById(settingsKeys[settingsKeys.length - 1]);
			BX.Dom.insertAfter(item, afterNode);
			this.initItems();
		},

		deleteMenuItem: function(itemElement)
		{
			this.itemData.delete(itemElement);
			BX.Dom.remove(itemElement);
		},

		updateMenuItemText: function(itemElement, itemText)
		{
			if (!itemElement || !itemText)
			{
				return;
			}

			const itemData = this.getItemData(itemElement);
			itemData.TEXT = itemText;
			const item = this.getItemById(itemData.ID);
			const classItemText = 'main-buttons-item-text-box';
			const elementText = BX.Buttons.Utils.getByClass(item, classItemText);
			elementText.innerText = itemText;
		},

		getHomeItem: function()
		{
			const visibleItems = this.getVisibleItems();
			const firstVisibleItem = BX.Type.isArray(visibleItems) && visibleItems.length > 0 ? visibleItems[0] : null;
			if (!firstVisibleItem)
			{
				return null;
			}

			const itemData = this.getItemData(firstVisibleItem);
			const url = this.normalizeUrl(itemData['URL']);

			if (this.canBeHomed(url, itemData))
			{
				return { itemData, url, firstVisibleItem };
			}

			if (BX.Type.isArrayFilled(itemData['ITEMS']))
			{
				for (let i = 0; i < itemData['ITEMS'].length; i++)
				{
					const subItem = itemData['ITEMS'][i];
					if (subItem['IS_PINNED'] || subItem['IS_DISBANDED'] || subItem['IS_DELIMITER'])
					{
						continue;
					}

					const url = this.normalizeUrl(subItem['URL']);
					if (this.canBeHomed(url, subItem))
					{
						return { itemData: subItem, url, firstVisibleItem };
					}
				}
			}

			return null;
		},

		normalizeUrl: function(url)
		{
			if (!BX.Type.isStringFilled(url))
			{
				return '';
			}

			if (url.charAt(0) === '?')
			{
				const a = document.createElement('a');
				a.href = url;
				url = a.pathname + a.search;
			}

			return url;
		},

		canBeHomed: function(itemLink, itemData)
		{
			if (!BX.Type.isStringFilled(itemLink) || BX.Type.isStringFilled(itemData['ON_CLICK']))
			{
				return false;
			}

			if (BX.Reflection.getClass('BX.SidePanel.Instance'))
			{
				const rule = BX.SidePanel.Instance.getUrlRule(itemLink);
				if (rule)
				{
					return false;
				}
			}

			const event = new BX.Event.BaseEvent({ data: { itemLink, itemData }});
			BX.Event.EventEmitter.emit('BX.Main.InterfaceButtons:onBeforeFirstItemChange', event);

			return !event.isDefaultPrevented();
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isEditButton: function(item)
		{
			return BX.Dom.hasClass(item, this.classEditItemButton);
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isDragButton: function(item)
		{
			return BX.Dom.hasClass(item, this.classDragItemButton);
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isResetSettingsButton: function(item)
		{
			return BX.Dom.hasClass(item, this.classSettingsResetButton);
		},

		/**
		 * Calculate container height
		 * @return {number} Container height in pixels
		 */
		getContainerHeight: function()
		{
			const heights = this.getAllItems().map(function(current) {
				const currentStyle = getComputedStyle(current);

				return (
					BX.height(current) +
					parseInt(currentStyle.marginTop) +
					parseInt(currentStyle.marginBottom)
				);
			});

			return Math.max.apply(Math, heights);
		},

		/**
		 * Sets license window params in this.licenseParams
		 * @param {object} params Params object
		 */
		setLicenseWindowParams: function(params)
		{
			this.licenseParams = params || {};
		},

		/**
		 * Gets message by id
		 * @method message
		 * @private
		 * @param  {string} messageId
		 * @return {string}
		 */
		message: function(messageId)
		{
			let result;

			try
			{
				result = this.messages[messageId];
			}
			catch (error)
			{
				result = '';
			}

			return result;
		},

		/**
		 * Sets custom classes
		 * @param  {object} classes
		 * @return {undefined}
		 */
		setCustomClasses: function(classes)
		{
			if (!BX.Type.isPlainObject(classes))
			{
				return;
			}

			this.classItem = (classes.item || this.classItem);
			this.classItemSublink = (classes.itemSublink || this.classItemSublink);
			this.classItemText = (classes.itemText || this.classItemText);
			this.classItemCounter = (classes.itemCounter || this.classItemCounter);
			this.classItemIcon = (classes.itemIcon || this.classItemIcon);
			this.classItemMore = (classes.itemMore || this.classItemMore);
			this.classItemOver = (classes.itemOver || this.classItemOver);
			this.classMenuShown = (classes.menuShown || this.classMenuShown);
			this.classItemActive = (classes.itemActive || this.classItemActive);
			this.classItemDisabled = (classes.itemDisabled || this.classItemDisabled);
			this.classOnDrag = (classes.onDrag || this.classOnDrag);
			this.classDropzone = (classes.dropzone || this.classDropzone);
			this.classSeparator = (classes.separator || this.classSeparator);
			this.classSubmenuItem = (classes.submenuItem || this.classSubmenuItem);
			this.classSubmenu = (classes.submenu || this.classSubmenu);
			this.classSecret = (classes.secret || this.classSecret);
			this.classItemLocked = (classes.itemLocked || this.classItemLocked);
			this.classExtraItemLink = (classes.extraItemLink || this.classExtraItemLink);
			this.classExtraItemText = (classes.extraItemText || this.classExtraItemText);
			this.classExtraItemIcon = (classes.extraItemIcon || this.classExtraItemIcon);
			this.classExtraItemCounter = (classes.extraItemCounter || this.classExtraItemCounter);
		},

		/**
		 * Sets messages
		 * @param {object} messages Messages object
		 */
		setMessages: function(messages)
		{
			if (!BX.Type.isPlainObject(messages))
			{
				return;
			}

			this.messages = messages;
		},

		/**
		 * Makes full item id
		 * @private
		 * @method makeFullItemId
		 * @param  {string} itemId
		 * @return {*}
		 */
		makeFullItemId: function(itemId)
		{
			if (!BX.Type.isStringFilled(itemId))
			{
				return;
			}

			return [this.listContainer.id, itemId.replace('-', '_')].join('_');
		},

		/**
		 * Gets listContainer child by id
		 * @public
		 * @method getItemById
		 * @param  {string} itemId
		 * @return {object} dom node
		 */
		getItemById: function(itemId)
		{
			let resultItem = null;
			if (BX.Type.isStringFilled(itemId))
			{
				const realId = itemId.startsWith(this.listContainer.id) ? itemId : this.makeFullItemId(itemId);
				resultItem = BX.Buttons.Utils.getBySelector(this.listContainer, '#'+realId.replaceAll(':', '\\:'));
			}

			return resultItem;
		},

		/**
		 * Finds counter object
		 * @private
		 * @method getItemCounterObject
		 * @param  {HTMLElement} item
		 * @return {?HTMLElement} Counter dom node
		 */
		getItemCounterObject: function(item)
		{
			let result = null;

			if (BX.Type.isDomNode(item))
			{
				result = BX.Buttons.Utils.getByClass(item, this.classItemCounter);
			}

			return result;
		},

		/**
		 * Updates menu item counter
		 * @param {string} id - menu item id
		 * @param {*} value - counter value
		 */
		updateCounter: function(id, value)
		{
			if (id.indexOf('crm') === 0 && value < 0)
			{
				//HACK: Skip of CRM counter reset
				return;
			}

			this.updateItemsByCounterId(this.getAllItemsData(), id, value);
			this.updateMoreButtonCounter();
		},

		/**
		 * @private
		 * @param subItems
		 * @param counterId
		 * @param counterValue
		 * @param rootPath
		 */
		updateItemsByCounterId: function(subItems, counterId, counterValue, rootPath = [])
		{
			for (let i = 0; i < subItems.length; i++)
			{
				const subItem = subItems[i];
				if (subItem['COUNTER_ID'] === counterId)
				{
					subItem['COUNTER'] = Number(counterValue);
					this.setCounterValueById(counterId, subItem['COUNTER']);

					for (let index = rootPath.length - 1; index >= 0; index--)
					{
						const rootItem = rootPath[index];

						rootItem['COUNTER'] = rootItem['ITEMS'].reduce((currentValue, subItem) => {
							const isPinned = subItem['IS_PINNED'] === true;
							const counter = BX.Type.isNumber(subItem['COUNTER']) && !isPinned ? subItem['COUNTER'] : 0;

							return currentValue + counter;
						}, 0);

						this.setCounterValueById(rootItem['COUNTER_ID'], rootItem['COUNTER']);
					}
				}

				if (subItem['ITEMS'])
				{
					this.updateItemsByCounterId(subItem['ITEMS'], counterId, counterValue, [...rootPath, subItem]);
				}
			}
		},

		recalculateItemsCounters: function(items, rootPath = [])
		{
			let counterValue = 0;

			for (let i = 0; i < items.length; i++)
			{
				const item = items[i];

				if (item['ITEMS'])
				{
					item['COUNTER'] = this.recalculateItemsCounters(item['ITEMS'], [...rootPath, item]);
				}

				const isPinned = item['IS_PINNED'] === true;
				counterValue += BX.Type.isNumber(item['COUNTER']) && !isPinned ? item['COUNTER'] : 0;
			}

			for (let index = rootPath.length - 1; index >= 0; index--)
			{
				const rootItem = rootPath[index];

				rootItem['COUNTER'] = rootItem['ITEMS'].reduce((currentValue, subItem) => {
					const isPinned = subItem['IS_PINNED'] === true;
					const counter = BX.Type.isNumber(subItem['COUNTER']) && !isPinned ? subItem['COUNTER'] : 0;

					return currentValue + counter;
				}, 0);

				this.setCounterValueById(rootItem['COUNTER_ID'], rootItem['COUNTER']);
			}

			return counterValue;
		},

		/**
		 * @private
		 * @param counterId
		 * @param counterValue
		 */
		setCounterValueById: function(counterId, counterValue)
		{
			if (!BX.Type.isStringFilled(counterId))
			{
				return;
			}

			const counterText = counterValue > 99 ? '99+' : (counterValue > 0 ? counterValue : '');
			const elements = document.querySelectorAll(`[data-mib-counter-id="${counterId}"]`);
			Array.from(elements).forEach(element => {
				element.textContent = counterText;
			});
		},

		/**
		 * Sets counter of more button
		 * @param {*} value
		 */
		setMoreButtonCounter: function(value)
		{
			const counter = this.getItemCounterObject(this.moreButton);
			counter.textContent = value > 99 ? '99+' : (value > 0 ? value : '');
		},

		/**
		 * Binds on click on more button
		 * @method bindOnClickOnMoreButton
		 * @private
		 * @return {undefined}
		 */
		bindEventsOnMoreButton: function()
		{
			BX.Event.bind(this.moreButton, 'click', this.handleMoreButtonClick.bind(this));
			BX.Event.bind(this.moreButton, 'mouseenter', this.handleMoreButtonMouseEnter.bind(this));
			BX.Event.bind(this.moreButton, 'mouseleave', this.handleMoreButtonMouseLeave.bind(this));
		},

		/**
		 * Binds on container/window resize
		 * @method bindOnResize
		 * @private
		 */
		bindOnResize: function()
		{
			const onResize = BX.Runtime.throttle(this._onResizeHandler, 100, this);
			const resizeObserver = new ResizeObserver(onResize);
			resizeObserver.observe(this.listContainer.closest('.main-buttons'));
			BX.Event.bind(window, 'resize', onResize);
		},

		/**
		 * Gets buttons list container id
		 * @return {string}
		 */
		getId: function()
		{
			return BX.Buttons.Utils.getByClass(this.getContainer(), this.classInner).id;
		},

		/**
		 * Gets all items
		 * @public
		 * @return {HTMLElement[]}
		 */
		getAllItems: function()
		{
			return BX.Buttons.Utils.getByClass(this.listContainer, this.classItem, true);
		},

		getAllItemsData: function()
		{
			return this.getAllItems().map(item => this.getItemData(item));
		},

		/**
		 * Gets only visible items
		 * @public
		 * @return {HTMLElement[]}
		 */
		getVisibleItems: function()
		{
			const allItems = this.getAllItems();
			let visibleItems = [];

			if (allItems && allItems.length)
			{
				visibleItems = allItems.filter((current) => {
					return this.isVisibleItem(current) && !this.isDisabled(current);
				});
			}

			return visibleItems;
		},

		/**
		 * Gets only hidden items
		 * @public
		 * @method getHiddenItems
		 * @return {HTMLElement[]}
		 */
		getHiddenItems: function()
		{
			const allItems = this.getAllItems();
			let hiddenItems = [];

			if (allItems && allItems.length)
			{
				hiddenItems = allItems.filter((current) => {
					return !this.isVisibleItem(current) && !this.isDisabled(current);
				});
			}

			return hiddenItems;
		},

		/**
		 * Gets only disabled items,
		 * as showed after separator in popup menu
		 * @public
		 * @method getDisabledItems
		 * @return {HTMLElement[]}
		 */
		getDisabledItems: function()
		{
			return this.getAllItems().filter((current) => {
				return this.isDisabled(current);
			});
		},

		/**
		 * Gets more button
		 * @public
		 * @returns {?HTMLElement} More button element
		 */
		getMoreButton: function()
		{
			const elements = this.getContainer().getElementsByClassName(this.classItemMore);

			return elements[0] || null;
		},

		/**
		 * Gets last visible item
		 * @private
		 * @method getLastVisibleItem
		 * @return {object} last visible item object
		 */
		getLastVisibleItem: function()
		{
			const visibleItems = this.getVisibleItems();
			let lastVisibleItem = null;

			if (BX.Type.isArray(visibleItems) && visibleItems.length)
			{
				lastVisibleItem = visibleItems[visibleItems.length - 1];
			}

			if (!BX.Type.isDomNode(lastVisibleItem))
			{
				lastVisibleItem = null;
			}

			return lastVisibleItem;
		},

		/**
		 * Gets last disabled item
		 * @private
		 * @method getLastVisibleItem
		 * @return {object} last visible item object
		 */
		getLastDisabledItem: function()
		{
			const visibleItems = this.getDisabledItems();
			let lastDisabledItem = null;

			if (BX.Type.isArray(visibleItems) && visibleItems.length)
			{
				lastDisabledItem = visibleItems[visibleItems.length - 1];
			}

			if (!BX.Type.isDomNode(lastDisabledItem))
			{
				lastDisabledItem = null;
			}

			return lastDisabledItem;
		},

		/**
		 * Moves "more button" in the end of the list
		 * @public
		 * @method adjustMoreButtonPosition
		 * @return {undefined}
		 */
		adjustMoreButtonPosition: function()
		{
			this.updateMoreButtonCounter();

			this.getHiddenItems().forEach((item) => {
				BX.Dom.removeClass(item, '--hidden');
			});

			this.getHiddenItems().forEach((item) => {
				BX.Dom.addClass(item, '--hidden');
			});

			if (this.getMoreMenu())
			{
				this.getMoreMenu().getPopupWindow().adjustPosition();
			}
		},

		/**
		 * Gets submenu id
		 * @private
		 * @method getSubmenuId
		 * @param  {boolean} [isFull] Set true if your need to get id for popup window
		 * @return {string} id
		 */
		getMoreMenuId: function(isFull)
		{
			let id = '';

			if (BX.Type.isDomNode(this.listContainer) && BX.Type.isStringFilled(this.listContainer.id))
			{
				id = this.submenuIdPrefix + this.listContainer.id;
			}

			if (isFull)
			{
				id = this.submenuWindowIdPrefix + id;
			}

			return id;
		},

		getChildMenuId: function()
		{
			let id = '';

			if (BX.Type.isDomNode(this.listContainer) && BX.Type.isStringFilled(this.listContainer.id))
			{
				id = this.childMenuIdPrefix + this.listContainer.id;
			}

			return id;
		},

		/**
		 * Gets submenu item content
		 * @private
		 * @method getMoreMenuItemText
		 * @param  {HTMLElement} item
		 * @param pinRootItem
		 * @return {?string}
		 */
		getMenuItemText: function(item, pinRootItem = null)
		{
			const itemData = BX.Type.isElementNode(item) ? this.getItemData(item) : item;

			return BX.Tag.render`
				<span class="main-buttons-menu-popup-item">${[
					BX.Tag.render`<span class="${this.classItemIcon}"></span>`,
					this.createItemText(itemData),
					this.createItemCounter(itemData),
					pinRootItem && this.isEditEnabled() ? this.createItemPin(itemData, pinRootItem) : ''
				]}</span>
			`;
		},

		createRootItem: function(options)
		{
			let itemClass = this.classItem;

			itemClass += BX.Type.isStringFilled(options["CLASS"]) ? ' ' + options["CLASS"] : '';
			if (options['IS_PASSIVE'])
			{
				itemClass += ' --passive';
			}
			else if (options['IS_ACTIVE'])
			{
				if (BX.Type.isStringFilled(this.classItemActive))
				{
					itemClass += ' ' + this.classItemActive;
				}
				else
				{
					itemClass += ' main-buttons-item-active';
				}
			}

			if (options['HAS_MENU'])
			{
				itemClass += ' --has-menu';
			}

			if (options['IS_LOCKED'])
			{
				itemClass += ' --locked';
			}

			const div = BX.Tag.render`
				<div
					id="${options['ID']}"
					class="${itemClass}"
					data-disabled="${options['IS_DISABLED']}"
					data-class="${options['CLASS_SUBMENU_ITEM']}"
					data-id="${options['DATA_ID']}"
					data-top-menu-id="${this.getId()}"
					title=""
				>${[
					this.createItemLink(options, true),
					BX.Type.isPlainObject(options['SUB_LINK']) ? this.createItemSubLink(options['SUB_LINK']) : ''
				]}</div>
			`;

			this.setItemData(div, options);

			return div;
		},

		createItemLink: function(options, rootItemContext = false)
		{
			options = BX.Type.isPlainObject(options) ? options : {};

			let container;
			const classes = ['main-buttons-item-link', this.classExtraItemLink].join(' ').trim();
			if (BX.Type.isStringFilled(options['URL']))
			{
				container = BX.Tag.render`<a class="${classes}" href="${BX.Text.encode(options['URL'])}"></a>`;
			}
			else
			{
				container = BX.Tag.render`<span class="${classes}"></span>`;
			}

			BX.Dom.append(this.createItemIcon(options), container);
			BX.Dom.append(this.createItemText(options, rootItemContext), container);
			BX.Dom.append(this.createItemCounter(options), container);

			return container;
		},

		createItemSubLink: function(options)
		{
			options = BX.Type.isPlainObject(options) ? options : {};

			const className = BX.Type.isStringFilled(options['CLASS']) ? ' ' + options['CLASS'] : '';
			const url = BX.Type.isStringFilled(options['URL']) ? BX.Text.encode(options['URL']) : '';

			return BX.Tag.render`
				<a class="${this.classItemSublink}${className}" href="${url}"></a>
			`;
		},

		createItemIcon: function(options)
		{
			const classes = [this.classItemIcon, this.classExtraItemIcon].join(' ').trim();

			return BX.Tag.render`<span class="${classes}"></span>`;
		},

		createItemText: function(options, rootItemContext = false)
		{
			options = BX.Type.isPlainObject(options) ? options : {};

			const classes = [this.classItemText, this.classExtraItemText].join(' ').trim();
			let itemText = BX.Type.isStringFilled(options['TEXT']) ? options['TEXT'] : '';
			if (rootItemContext && itemText.length > this.maxItemLength)
			{
				itemText = itemText.substring(0, this.maxItemLength - 3) + '...';
			}

			let superTitle = '';
			if (BX.Type.isPlainObject(options['SUPER_TITLE']))
			{
				let { 'TEXT': text, 'CLASS': className, 'COLOR': color } = options['SUPER_TITLE'];
				className = BX.Type.isStringFilled(className) ? ` ${className}` : '';
				const style = BX.Type.isStringFilled(color) ? ` style="color:${color}"` : '';

				superTitle = BX.Tag.render`
					<span class="main-buttons-item-super-title${className}"${style}>${text}</span>
				`;
			}

			return BX.Tag.render`
				<span class="${classes}">${[
					BX.Tag.render`<span 
						class="main-buttons-item-drag-button"
						onclick="${this.handleDragButtonClick.bind(this)}" 
						data-slider-ignore-autobinding="true"
					></span>`,
					superTitle,
					BX.Tag.render`
						<span class="main-buttons-item-text-title">
							<span class="main-buttons-item-text-box">${
								BX.Text.encode(itemText)
							}<span class="main-buttons-item-menu-arrow"></span></span>
						</span>
					`,
					BX.Tag.render`<span 
						class="main-buttons-item-edit-button"
						onclick="${this.handleEditButtonClick.bind(this)}" 
						data-slider-ignore-autobinding="true"
					></span>`,
					BX.Tag.render`<span class="main-buttons-item-text-marker"></span>`,
				]}</span>
			`;
		},

		createItemCounter: function(options)
		{
			options = BX.Type.isPlainObject(options) ? options : {};

			const classes = [this.classItemCounter, this.classExtraItemCounter].join(' ').trim();

			let counter = '';
			const maxCounterSize = BX.Type.isNumber(options['MAX_COUNTER_SIZE']) ? options['MAX_COUNTER_SIZE'] : 99;
			if (BX.Type.isNumber(options['COUNTER']) && options['COUNTER'] > 0)
			{
				counter = options['COUNTER'] > maxCounterSize ? `${maxCounterSize}+` : options['COUNTER'];
			}

			const counterId = BX.Type.isStringFilled(options['COUNTER_ID']) ? options['COUNTER_ID'] : '';

			return BX.Tag.render`<span data-mib-counter-id="${counterId}" class="${classes}">${counter}</span>`;
		},

		createItemPin: function(itemData, rootNode)
		{
			return BX.Tag.render`
				<span class="main-buttons-item-pin" 
					data-slider-ignore-autobinding="true"
					onclick="${this.handleItemPin.bind(this, itemData, rootNode)}"
					onmouseenter="${this.handleItemPinEnter.bind(this)}"
					onmouseleave="${this.handleItemPinLeave.bind(this)}"
				></span>
			`;
		},

		/**
		 * @param {HTMLElement} item
		 * @return {string}
		 */
		getLockedClass: function(item)
		{
			let result = '';
			if (BX.Type.isDomNode(item) && this.isLocked(item))
			{
				result = this.classItemLocked;
			}

			return result;
		},

		/**
		 * Gets More Menu items
		 * @private
		 * @method getMoreMenuItems
		 * @return {HTMLElement[]}
		 */
		getMoreMenuItems: function()
		{
			const allItems = this.getAllItems();
			const hiddenItems = this.getHiddenItems();
			const disabledItems = this.getDisabledItems();
			const result = [];

			if (allItems.length)
			{
				allItems.forEach(current => {
					if (hiddenItems.indexOf(current) === -1 && disabledItems.indexOf(current) === -1)
					{
						const itemData = this.getItemData(current);
						result.push({
							id: itemData['DATA_ID'],
							html: this.getMenuItemText(current),
							href: itemData['URL'],
							onclick: itemData['ON_CLICK'],
							title: current.getAttribute('title'),
							className: [
								this.classSubmenuItem,
								this.getIconClass(current),
								this.classSecret,
								this.getAliasLink(current),
								this.getLockedClass(current)
							].join(' '),
							items: this.getMoreMenuSubItems(current),
							events: {
								onMouseEnter: this.handleMoreMenuItemMouseEnter
							}
						});
					}
				});
			}

			if (hiddenItems.length)
			{
				hiddenItems.forEach(current => {
					const itemData = this.getItemData(current);
					const className = [
						this.classSubmenuItem,
						this.getIconClass(current),
						this.getAliasLink(current),
						this.getLockedClass(current)
					];

					if (itemData['IS_ACTIVE'] === true)
					{
						className.push(this.classItemActive);
					}

					result.push({
						id: itemData['DATA_ID'],
						html: this.getMenuItemText(current),
						href: itemData['URL'],
						onclick: itemData['ON_CLICK'],
						title: current.getAttribute('title'),
						className: className.join(' '),
						items: this.getMoreMenuSubItems(current),
						events: {
							onMouseEnter: this.handleMoreMenuItemMouseEnter
						}
					});
				});
			}

			if (this.isSettingsEnabled)
			{
				result.push({
					delimiter: true,
					html: '<span>' + this.message('MIB_MANAGE') + '</span>',
					className: [
						this.classSeparator,
						this.classSubmenuItem,
						this.classManage
					].join(' ')
				});

				result.push({
					html: this.message('MIB_SETTING_MENU_ITEM'),
					className: [
						this.classSettingMenuItem,
						this.classSubmenuItem
					].join(' ')
				});

				const btnClasses = [
					'ui-btn',
					this.theme === 'default' ? 'ui-btn-sm' : 'ui-btn-xs',
					'ui-btn-success-light',
					'ui-btn-no-caps',
					'ui-btn-round',
					'ui-btn-icon-main-buttons-apply',
				];

				result.push({
					html: `
					<span class="${btnClasses.join(' ')}">
						<span class="ui-btn-text">${this.message('MIB_APPLY_SETTING_MENU_ITEM')}</span>
					</span>`,
					className: [
						this.classSettingsApplyButton,
						this.classSubmenuItem
					].join(' ')
				});

				result.push({
					html: this.message('MIB_RESET_SETTINGS'),
					className: [this.classSettingsResetButton, this.classSubmenuItem].join(' ')
				});

				result.push({
					delimiter: true,
					html: '<span>' + this.message('MIB_HIDDEN') + '</span>',
					className: [
						this.classSeparator,
						this.classSubmenuItem,
						this.classHiddenLabel
					].join(' ')
				});

				if (!disabledItems.length)
				{
					result.push({
						html: '<span>'+this.message('MIB_NO_HIDDEN')+'</span>',
						className: [
							this.classSubmenuItem,
							this.classSubmenuNoHiddenItem
						].join(' ')
					});
				}

				if (disabledItems.length)
				{
					disabledItems.forEach(current => {
						const itemData = this.getItemData(current);
						const className = [
							this.classSubmenuItem,
							this.classItemDisabled,
							this.getIconClass(current),
							this.getAliasLink(current),
							this.getLockedClass(current)
						];

						if (itemData['IS_ACTIVE'] === true)
						{
							className.push(this.classItemActive);
						}

						result.push({
							id: itemData['DATA_ID'],
							html: this.getMenuItemText(current),
							href: itemData['URL'],
							onclick: itemData['ON_CLICK'],
							title: current.getAttribute('title'),
							className: className.join(' '),
							items: this.getMoreMenuSubItems(current),
							events: {
								onMouseEnter: this.handleMoreMenuItemMouseEnter
							}
						});
					});
				}
			}

			return result;
		},

		getMenuItems: function(item)
		{
			return this.createMenuItems(this.getItemData(item), item);
		},

		getMoreMenuSubItems: function(item)
		{
			return this.createMenuItems(this.getItemData(item), null);
		},

		createMenuItems: function(itemData, pinRootItem = null)
		{
			if (!BX.Type.isArrayFilled(itemData['ITEMS']))
			{
				return [];
			}

			const items = itemData['ITEMS'];
			const result = [];
			for (let i = 0; i < items.length; i++)
			{
				const item = items[i];
				if (item['IS_PINNED'] || item['IS_DISBANDED'])
				{
					continue;
				}

				const delimiter = item['IS_DELIMITER'] === true;
				if (delimiter)
				{
					const firstItem = result.length === 0;
					const prevItem = result[result.length - 1];
					if (firstItem || (prevItem && prevItem['delimiter'] === true))
					{
						continue;
					}
				}

				const className = ['menu-popup-no-icon', 'main-buttons-menu-item'];
				if (item['IS_ACTIVE'] === true)
				{
					className.push('main-buttons-menu-item-active');
				}

				const locked = BX.Text.toBoolean(item['IS_LOCKED']);
				if (locked)
				{
					className.push(this.classItemLocked);
				}

				if (this.isEditEnabled())
				{
					className.push(this.classEditState);
				}

				let menuItem;
				if (delimiter)
				{
					menuItem = {
						delimiter: true,
						className: className.join(' '),
						text: item['TEXT'],
					};
				}
				else
				{
					menuItem = {
						html: this.getMenuItemText(item, pinRootItem),
						href: item['URL'],
						onclick: item['ON_CLICK'],
						title: item['TITLE'],
						className: className.join(' '),
					};
				}

				const ajaxMode = item.hasOwnProperty("AJAX_OPTIONS");
				if (ajaxMode)
				{
					menuItem.cacheable = true;
					menuItem.events = this._getEvents(item['AJAX_OPTIONS']);
					menuItem.items = [
						{
							id: 'loading',
							text: this.message('MIB_MAIN_BUTTONS_LOADING')
						}
					];
				}
				else if (BX.Type.isArrayFilled(item['ITEMS']) && !this.isEditEnabled())
				{
					const subItems = this.createMenuItems(item, pinRootItem);
					if (subItems.length)
					{
						menuItem.items = subItems;
					}
				}

				result.push(menuItem);
			}

			if (result.length && result[result.length - 1]['delimiter'] === true)
			{
				result.pop();
			}

			return result;
		},

		_setAjaxMode: function(items)
		{
			for (let itemId in items)
			{
				if (!items.hasOwnProperty(itemId))
				{
					continue;
				}

				if (items[itemId].hasOwnProperty("ajaxOptions"))
				{
					items[itemId].cacheable = true;
					items[itemId].events = this._getEvents(items[itemId]["ajaxOptions"]);
					items[itemId].items = [
						{
							id: "loading",
							text: this.message("MIB_MAIN_BUTTONS_LOADING")
						}
					];
				}
			}
		},

		_getEvents: function(ajaxOptions)
		{
			return {
				onSubMenuShow: () => {
					if (this.subMenuLoaded)
					{
						return;
					}

					const submenu = this.getSubMenu();
					submenu.removeMenuItem("loading");
					const loadingItem = submenu.getMenuItem('loading');

					this.getSubItems(ajaxOptions)
						.then(items => {
							this._setAjaxMode(items);
							this.subMenuLoaded = true;
							this.addSubMenu(items);
							this.showSubMenu();
						})
						.catch(text => {
							if (loadingItem)
							{
								loadingItem.getLayout().text.innerText = text;
							}
						})
					;
				}
			};
		},

		getSubItems: function(ajaxOptions)
		{
			return new Promise(function(resolve, reject) {
				if (this.progress)
				{
					reject(this.message("MIB_MAIN_BUTTONS_LOADING"));
					return;
				}

				if (ajaxOptions.mode === "component")
				{
					this.progress = true;
					BX.ajax.runComponentAction(ajaxOptions.component, ajaxOptions.action, {
						mode: ajaxOptions.componentMode,
						signedParameters: (ajaxOptions.signedParameters ? ajaxOptions.signedParameters : {}),
						data: ajaxOptions.data,
					})
					.then(response => {
						this.progress = false;
						resolve(response.data);
					});
				}
				else
				{
					this.progress = true;
					BX.ajax.runAction(ajaxOptions.action, {
						data: ajaxOptions.data,
					})
					.then(response => {
						this.progress = false;
						resolve(response.data);
					});
				}
			});
		},

		/**
		 * Gets BX.PopupMenu.show arguments
		 * @private
		 * @method getMoreMenuArgs
		 * @return {*[]} Arguments
		 */
		getMoreMenuArgs: function()
		{
			const menuId = this.getMoreMenuId();
			const moreButton = this.moreButton;
			const menuItems = this.getMoreMenuItems();

			const commonParams = {
				autoHide: false,
				compatibleMode: false,
				maxHeight: 800,
				offsetTop: 4,
				cacheable: false,
				bindOptions: {
					position: 'bottom',
					forceTop: true,
				},
				events: {
					onClose: this.handleMoreMenuClose.bind(this),
					onDestroy: this.handleMoreMenuClose.bind(this),
					onFirstShow: this.handleMoreMenuFirstShow.bind(this),
					onShow: this.handleMoreMenuShow.bind(this),
				},
				subMenuOptions: {
					events: {
						onFirstShow: this.handleMoreMenuFirstShow.bind(this),
					},
				},
			};

			let params = null;
			if (this.theme === 'default')
			{
				const maxWidth = 350;
				const activeItemMargin = 25;
				params = {
					className: 'main-buttons-menu-popup main-buttons-more-menu-popup',
					offsetLeft: -activeItemMargin,
					minWidth: 240,
					maxWidth,
					subMenuOptions: {
						className: 'main-buttons-menu-popup main-buttons-more-menu-popup --sub-menu',
						minWidth: 150,
						maxWidth,
					},
					events: {
						onBeforeAdjustPosition: this.handleAdjustPosition.bind(this, moreButton),
					},
				};
			}
			else if (this.theme === 'air')
			{
				const maxWidth = 350;
				params = {
					className: 'main-buttons-default-menu-popup main-buttons-more-menu-popup',
					offsetLeft: -10,
					offsetTop: -5,
					minWidth: 240,
					maxWidth,
					subMenuOptions: {
						className: 'main-buttons-default-menu-popup main-buttons-more-menu-popup --sub-menu',
					},
				};
			}
			else
			{
				const moreButtonTitle = this.moreButton.querySelector('.main-buttons-item-text-title');
				const targetNodeWidth = moreButtonTitle.offsetWidth;
				const popupWidth = 250;
				const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2) + BX.Main.Popup.getOption('angleLeftOffset');
				const angleShift = BX.Main.Popup.getOption('angleLeftOffset') - BX.Main.Popup.getOption('angleMinTop');
				const angleOffset = popupWidth / 2 - angleShift;

				params = {
					className: 'main-buttons-default-menu-popup main-buttons-more-menu-popup',
					offsetLeft,
					minWidth: popupWidth,
					maxWidth: popupWidth,
					angle: {
						position: 'top',
						offset: angleOffset,
					},
					subMenuOptions: {
						className: 'main-buttons-default-menu-popup main-buttons-more-menu-popup --sub-menu',
					},
				};
			}

			params = BX.Runtime.merge(commonParams, params);

			if (this.isEditEnabled())
			{
				params.className += ' ' + this.classEditState;
			}

			return [menuId, moreButton, menuItems, params];
		},

		getChildMenuArgs: function(item)
		{
			const commonParams = {
				autoHide: false,
				compatibleMode: false,
				offsetTop: 4,
				cacheable: false,
				maxHeight: 800,
				bindOptions: {
					position: 'bottom',
					forceTop: true,
				},
				events: {
					onFirstShow: this._onChildMenuFirstShow.bind(this),
					onShow: this._onChildMenuShow.bind(this, item),
					onClose: this._onChildMenuClose.bind(this, item),
					onDestroy: this._onChildMenuClose.bind(this, item),
				},
				subMenuOptions: {
					minWidth: null,
					events: {
						onFirstShow: this._onChildMenuFirstShow.bind(this),
					},
				},
			};

			let params = null;
			if (this.theme === 'default')
			{
				const activeItemMargin = 25;

				params = {
					className: 'main-buttons-menu-popup',
					maxWidth: 350,
					minWidth: item.offsetWidth + activeItemMargin * 2 + 30,
					offsetLeft: -activeItemMargin,
					events: {
						onBeforeAdjustPosition: this.handleAdjustPosition.bind(this, item),
					},
					subMenuOptions: {
						className: 'main-buttons-menu-popup --sub-menu',
					},
				};
			}
			else if (this.theme === 'air')
			{
				params = {
					className: 'main-buttons-default-menu-popup',
					offsetTop: -5,
					offsetLeft: -10,
					maxWidth: 350,
					minWidth: 200,
					subMenuOptions: {
						className: 'main-buttons-default-menu-popup --sub-menu',
					},
				};
			}
			else
			{
				const maxWidth = 250;
				params = {
					className: 'main-buttons-default-menu-popup',
					maxWidth,
					minWidth: Math.min(item.offsetWidth + 25 * 2 + 30, maxWidth),
					subMenuOptions: {
						className: 'main-buttons-default-menu-popup --sub-menu',
					},
				};
			}

			return BX.Runtime.merge(commonParams, params);
		},

		centerPopupArrow(popup, item)
		{
			const targetNodeWidth = item.offsetWidth;
			const popupWidth = popup.getPopupContainer().offsetWidth;
			const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
			const angleShift = BX.Main.Popup.getOption('angleLeftOffset') - BX.Main.Popup.getOption('angleMinTop');

			popup.setAngle({ offset: popupWidth / 2 - angleShift });
			popup.setOffset({ offsetLeft: offsetLeft + BX.Main.Popup.getOption('angleLeftOffset') });
		},

		/**
		 * Controls the visibility of more button
		 */
		visibleControlMoreButton: function()
		{
			const hiddenItems = this.getHiddenItems();
			if (!hiddenItems.length)
			{
				this.getMoreButton().style.display = 'none';
			}
			else
			{
				this.getMoreButton().style.display = '';
			}
		},

		/**
		 * Creates submenu
		 * @return {BX.PopupMenu}
		 */
		createMoreMenu: function()
		{
			const menu = BX.Main.MenuManager.create(...this.getMoreMenuArgs());
			if (this.isSettingsEnabled)
			{
				this.dragAndDropInitInSubmenu();
			}

			menu.getMenuItems().forEach(function(menuItem) {
				const container = menuItem.getLayout().item;
				BX.Event.bind(container, 'click', BX.delegate(this._onDocumentClick, this));
			}, this);

			return menu;
		},

		createChildMenu: function(item)
		{
			const menuItems = this.getMenuItems(item);
			if (menuItems.length)
			{
				const menu = BX.Main.MenuManager.create(
					this.getChildMenuId(),
					item,
					menuItems,
					this.getChildMenuArgs(item),
				);

				if (!this.isEditEnabled() && this.isSettingsEnabled)
				{
					const handleDragStart = () => {
						this.showMoreMenu();
						this.enableEdit();

						this.destroyChildMenu();
						this.showChildMenu(item);
					};

					menu.getMenuItems().forEach((menuItem) => {
						const container = menuItem.getLayout().item;
						container.draggable = true;

						BX.Event.bind(container, 'dragstart', handleDragStart);
					});
				}

				return menu;
			}

			return null;
		},

		/**
		 * Shows More Menu
		 * @public
		 * @method showMoreMenu
		 * @return {undefined}
		 */
		showMoreMenu: function()
		{
			clearTimeout(this.submenuLeaveTimeout);

			if (!this.isEditEnabled())
			{
				this.closeChildMenu();
			}

			let submenu = this.getMoreMenu();
			if (submenu !== null)
			{
				submenu.getPopupWindow().show();
			}
			else
			{
				this.destroyMoreMenu();
				submenu = this.createMoreMenu();
				submenu.getPopupWindow().show();
			}

			this.setMoreMenuShown(true);
			this.activateItem(this.moreButton);

			if (this.isEditEnabled())
			{
				submenu.getPopupWindow().setAutoHide(false);
			}
		},

		showChildMenu: function(item)
		{
			clearTimeout(this.childMenuLeaveTimeout);

			if (!this.isEditEnabled())
			{
				this.closeMoreMenu();
			}

			if (!this.isVisibleItem(item))
			{
				return;
			}

			const currentMenu = BX.Main.MenuManager.getMenuById(this.getChildMenuId());
			if (currentMenu && currentMenu.bindElement === item)
			{
				currentMenu.getPopupWindow().show();
				this.destroyItemEditMenu();
			}
			else
			{
				this.destroyChildMenu(item);
				const childMenu = this.createChildMenu(item);
				if (childMenu)
				{
					childMenu.getPopupWindow().show();
					this.destroyItemEditMenu();
				}
			}
		},

		/**
		 * Closes submenu
		 * @public
		 * @method closeMoreMenu
		 * @return {undefined}
		 */
		closeMoreMenu: function()
		{
			const submenu = this.getMoreMenu();
			if (submenu === null)
			{
				return;
			}

			submenu.getPopupWindow().close();
			if (!this.isActiveInMoreMenu())
			{
				this.deactivateItem(this.moreButton);
			}

			this.setMoreMenuShown(false);
		},

		closeChildMenu: function()
		{
			const childMenu = this.getChildMenu();

			if (childMenu === null)
			{
				return;
			}

			this.closePinHint();
			childMenu.close();
		},

		/**
		 * Gets current More Menu
		 * @public
		 * @method getMoreMenu
		 * @return {BX.Main.Menu}
		 */
		getMoreMenu: function()
		{
			return BX.Main.MenuManager.getMenuById(this.getMoreMenuId());
		},

		/**
		 * Gets current Sub Menu
		 * @public
		 * @method getMoreMenu
		 * @return {BX.Main.Menu}
		 */
		getChildMenu: function()
		{
			return BX.Main.MenuManager.getMenuById(this.getChildMenuId());
		},

		/**
		 * Destroys More Menu
		 * @private
		 * @method destroySubmenu
		 * @return {undefined}
		 */
		destroyMoreMenu: function()
		{
			BX.Main.MenuManager.destroy(this.getMoreMenuId());
		},

		destroyChildMenu: function()
		{
			BX.Main.MenuManager.destroy(this.getChildMenuId());
		},

		/**
		 * Refreshes submenu
		 * @public
		 * @method refreshMoreMenu
		 * @return {undefined}
		 */
		refreshMoreMenu: function()
		{
			const submenu = this.getMoreMenu();
			if (submenu === null)
			{
				return;
			}

			const args = this.getMoreMenuArgs();
			if (BX.Type.isArray(args))
			{
				this.destroyMoreMenu();
				this.createMoreMenu();
				this.showMoreMenu();
			}
		},

		/**
		 * Sets value this.isSubmenuShown
		 * @private
		 * @method setMoreMenuShown
		 * @param {boolean} value
		 */
		setMoreMenuShown: function(value)
		{
			this.isSubmenuShown = false;
			if (BX.type.isBoolean(value))
			{
				this.isSubmenuShown = value;
			}

			if (this.isSubmenuShown)
			{
				BX.Dom.addClass(this.moreButton, this.classMenuShown);
			}
			else
			{
				BX.Dom.removeClass(this.moreButton, this.classMenuShown);
			}
		},

		/**
		 * Adds class active for item
		 * @private
		 * @method activateItem
		 * @param  {object} item
		 * @return {undefined}
		 */
		activateItem: function(item)
		{
			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			if (!BX.Dom.hasClass(item, this.classItemActive))
			{
				BX.Dom.addClass(item, this.classItemActive);
			}
		},

		/**
		 * Removes class active for item
		 * @private
		 * @method deactivateItem
		 * @param  {object} item
		 * @return {undefined}
		 */
		deactivateItem: function(item)
		{
			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			if (BX.Dom.hasClass(item, this.classItemActive))
			{
				BX.Dom.removeClass(item, this.classItemActive);
			}
		},

		/**
		 * Gets current component settings
		 * @public
		 * @method getCurrentSettings
		 * @return {object}
		 */
		getCurrentSettings: function()
		{
			const settings = {};

			this.getAllItems().forEach((current, index) => {
				settings[current.id] = {
					sort: index,
					isDisabled: this.isDisabled(current),
					isPinned: this.isPinned(current),
				};
			});

			return settings;
		},

		initSaving: function(ajaxSettings)
		{
			this.sendOptions = this.sendOptions.bind(this);
			this.optionsToSave = [];
			this.debouncedSendOptions = BX.debounce(this.sendOptions, 5000);
			if (BX.Type.isPlainObject(ajaxSettings))
			{
				this.ajaxSettings = {
					componentName: ajaxSettings.componentName,
					signedParams: ajaxSettings.signedParams,
				};
			}
		},

		/**
		 * Sends settings to the server
		 * @private
		 * @method sendSettings
		 * @return {undefined}
		 */
		sendOptions: function()
		{
			if (this.optionsToSave.length <= 0)
			{
				return;
			}

			const dataToSend = {};
			this.optionsToSave.forEach(function(item){
				dataToSend[item.name] = item.value;
			});
			this.optionsToSave = [];
			window.removeEventListener("beforeunload", this.sendOptions);
			BX.removeCustomEvent("SidePanel.Slider:onClose", this.sendOptions);

			return BX.ajax.runComponentAction(
				this.ajaxSettings.componentName,
				'save',
				{
					mode: 'class',
					signedParameters: this.ajaxSettings.signedParams,
					data: {
						options: dataToSend
					}
				}
			);
		},

		/**
		 * Collects settings into storage and then sends
		 * @private
		 * @method saveOptions
		 * @return {undefined}
		 * @param name
		 * @param value
		 */
		saveOptions: function(name, value)
		{
			if (this.ajaxSettings)
			{
				if (this.optionsToSave.length <= 0)
				{
					window.addEventListener("beforeunload", this.sendOptions);
					BX.addCustomEvent("SidePanel.Slider:onClose", this.sendOptions);
				}
				this.optionsToSave.push({'name': name, 'value': value});
				this.debouncedSendOptions();
			}
			else if (this.listContainer.id)
			{
				BX.userOptions.save('ui', this.listContainer.id, name, value);
			}
		},

		/**
		 * Saves current component settings
		 * @public
		 * @method saveSettings
		 * @return {undefined}
		 */
		saveSettings: function()
		{
			const settings = this.getCurrentSettings();
			const paramName = 'settings';

			if (!BX.Type.isPlainObject(settings))
			{
				return;
			}

			if (BX.Type.isDomNode(this.listContainer) && 'id' in this.listContainer)
			{
				this.saveOptions(paramName, JSON.stringify(settings));
				const homeItem = this.getHomeItem();
				if (homeItem)
				{
					const { itemData, url: firstPageLink, firstVisibleItem } = homeItem;
					if (itemData)
					{
						if (this.lastHomeLink !== firstPageLink)
						{
							this.saveOptions('firstPageLink', firstPageLink);
							this.sendOptions(); // force send
							BX.onCustomEvent(
								'BX.Main.InterfaceButtons:onFirstItemChange',
								[firstPageLink, firstVisibleItem],
							);
						}

						this.lastHomeLink = firstPageLink;
					}
				}
			}
		},

		resetSettings: function()
		{
			let button = null;
			const confirmPopup = BX.PopupWindowManager.create(
				this.listContainer.id + "_reset_popup",
				null,
				{
					content: this.message('MIB_RESET_ALERT'),
					autoHide: false,
					overlay: true,
					closeByEsc : true,
					closeIcon : true,
					draggable : { restrict : true},
					titleBar: this.message("MIB_RESET_SETTINGS"),
					buttons: [
						(button = new BX.PopupWindowButton({
							text: this.message("MIB_RESET_BUTTON"),
							className: 'popup-window-button-create',
							events: {
								click: () => {
									if (BX.Dom.hasClass(button.buttonNode, "popup-window-button-wait"))
									{
										return;
									}

									BX.Dom.addClass(button.buttonNode, "popup-window-button-wait");

									this.handleResetSettings(error => {
										if (error)
										{
											BX.Dom.removeClass(button.buttonNode, "popup-window-button-wait");
											confirmPopup.setContent(error);
										}
										else
										{
											this.saveOptions('settings', JSON.stringify({}));
											this.saveOptions('firstPageLink', '');
											this.sendOptions()
												.then(function() {
													window.location.reload();
												})
												.catch(function() {
													window.location.reload();
												})
											;
										}
									});
								}
							}
						})),
						new BX.PopupWindowButtonLink({
							text: this.message("MIB_CANCEL_BUTTON"),
							className: "popup-window-button-link-cancel",
							events: {
								click: function() {
									this.popupWindow.close();
								}
							}
						})
					]
				}
			);

			confirmPopup.show();
		},

		/**
		 * @callback cb
		 */
		handleResetSettings: function(cb)
		{
			const promises = [];
			BX.onCustomEvent("BX.Main.InterfaceButtons:onBeforeResetMenu", [promises, this]);

			let promise = new BX.Promise();
			const firstPromise = promise;

			for (let i = 0; i < promises.length; i++)
			{
				promise = promise.then(promises[i]);
			}

			promise.then(
				function(result) {
					cb(null, result);
				},
				function(reason) {
					cb(reason, null);
				}
			);

			firstPromise.fulfill();
		},

		/**
		 * Moves alias buttons
		 * @private
		 * @method moveButtonAlias
		 * @param  {HTMLElement} item
		 * @param insertAfter
		 * @return {undefined}
		 */
		moveButtonAlias: function(item, insertAfter)
		{
			if (!item || !this.dragItem)
			{
				return;
			}

			const aliasDragItem = this.getItemAlias(this.dragItem);
			const aliasItem = this.getItemAlias(item);

			if (this.isListItem(aliasDragItem))
			{
				if (aliasItem)
				{
					if (insertAfter)
					{
						BX.Dom.insertAfter(aliasDragItem, aliasItem);
					}
					else
					{
						this.listContainer.insertBefore(aliasDragItem, aliasItem);
					}
				}
				else
				{
					this.listContainer.appendChild(aliasDragItem);
				}
			}

			if (this.getMoreMenu())
			{
				this.getMoreMenu().getPopupWindow().adjustPosition();
			}
		},

		/**
		 * Moves drag item before item, or appendChild to container
		 * @private
		 * @method moveButton
		 * @param  {HTMLElement} item
		 * @param insertAfter
		 * @return {*}
		 */
		moveButton: function(item, insertAfter)
		{
			if (!BX.Type.isDomNode(item) || !BX.Type.isDomNode(this.dragItem))
			{
				return;
			}

			if (this.isListItem(item))
			{
				if (this.isDisabled(this.dragItem))
				{
					this.dragItem.dataset.disabled = 'false';
				}

				if (BX.Type.isDomNode(item))
				{
					if (insertAfter)
					{
						BX.Dom.insertAfter(this.dragItem, item);
					}
					else
					{
						this.listContainer.insertBefore(this.dragItem, item);
					}
				}
				else
				{
					this.listContainer.appendChild(this.dragItem);
				}
			}

			if (this.isSubmenuItem(item))
			{
				if (insertAfter)
				{
					BX.Dom.insertAfter(this.dragItem, item);
				}
				else
				{
					this.getMoreMenuContainer().insertBefore(this.dragItem, item);
				}
			}
		},

		/**
		 * Gets submenu container
		 * @private
		 * @method getSubmenuContainer
		 * @return {object}
		 */
		getMoreMenuContainer: function()
		{
			const submenu = this.getMoreMenu();
			let result = null;

			if (submenu !== null)
			{
				result = submenu.itemsContainer;
			}

			return result;
		},

		/**
		 * Gets next element with className
		 * @param {?HTMLElement} item
		 * @param {string} className
		 * @returns {?HTMLElement}
		 */
		findNextSiblingByClass: function(item, className)
		{
			//noinspection UnnecessaryLocalVariableJS
			const sourceItem = item;
			for (; !!item; item = item.nextElementSibling)
			{
				if (className)
				{
					if (BX.Dom.hasClass(item, className) && item !== sourceItem)
					{
						return item;
					}
				}
				else
				{
					return null;
				}
			}

			return null;
		},

		/**
		 * Finds children item by className
		 * @private
		 * @method findChildrenByClassName
		 * @param  {HTMLElement} item
		 * @param  {string} className
		 * @return {?HTMLElement}
		 */
		findChildrenByClassName: function(item, className)
		{
			let result = null;
			if (BX.Type.isDomNode(item) && BX.Type.isStringFilled(className))
			{
				result = BX.Buttons.Utils.getByClass(item, className);
			}

			return result;
		},

		/**
		 * Initialise Drag And Drop
		 * @private
		 * @method dragAndDropInit
		 * @return {undefined}
		 */
		initItems: function()
		{
			this.getAllItems().forEach((current) => {
				this.initItem(current);
			});
		},

		initItem: function(item)
		{
			if (this.isSettingsEnabled)
			{
				item.setAttribute('draggable', 'true');
				item.setAttribute('tabindex', '-1');

				BX.Event.bind(item, 'dragstart', BX.delegate(this._onDragStart, this));
				BX.Event.bind(item, 'dragend', BX.delegate(this._onDragEnd, this));
				BX.Event.bind(item, 'dragenter', BX.delegate(this._onDragEnter, this));
				BX.Event.bind(item, 'dragover', BX.delegate(this._onDragOver, this));
				BX.Event.bind(item, 'dragleave', BX.delegate(this._onDragLeave, this));
				BX.Event.bind(item, 'drop', BX.delegate(this._onDrop, this));

				item.dataset.link = 'item-' + BX.Text.getRandom().toLowerCase();
			}

			BX.Event.bind(item, 'click', this._handleItemClick.bind(this));
			BX.Event.bind(item, 'mouseenter', this.handleItemMouseEnter.bind(this));
			BX.Event.bind(item, 'mouseleave', this.handleItemMouseLeave.bind(this));
		},

		/**
		 * Initialise Drag And Drop for submenu items
		 * @private
		 * @method dragAndDropInitInSubmenu
		 * @return {undefined}
		 */
		dragAndDropInitInSubmenu: function()
		{
			const submenu = this.getMoreMenu();
			if (!submenu)
			{
				return;
			}

			const submenuItems = submenu.menuItems;

			submenuItems.forEach((current) => {
				if (
					this.isSeparator(current.layout.item)
					|| this.isSettings(current.layout.item)
					|| this.isApplySettingsButton(current.layout.item)
					|| this.isResetSettingsButton(current.layout.item)
				)
				{
					current.layout.item.draggable = false;
				}
				else
				{
					current.layout.item.draggable = true;
					current.layout.item.dataset.sortable = true;

					BX.Event.bind(current.layout.item, 'dragstart', BX.delegate(this._onDragStart, this));
					BX.Event.bind(current.layout.item, 'dragenter', BX.delegate(this._onDragEnter, this));
					BX.Event.bind(current.layout.item, 'dragover', BX.delegate(this._onDragOver, this));
					BX.Event.bind(current.layout.item, 'dragleave', BX.delegate(this._onDragLeave, this));
					BX.Event.bind(current.layout.item, 'dragend', BX.delegate(this._onDragEnd, this));
					BX.Event.bind(current.layout.item, 'drop', BX.delegate(this._onDrop, this));
				}

				if (
					BX.Dom.hasClass(current.layout.item, this.classHiddenLabel)
					|| BX.Dom.hasClass(current.layout.item, this.classManage)
				)
				{
					BX.Event.bind(current.layout.item, 'dragover', BX.delegate(this._onDragOver, this));
				}
			});
		},

		/**
		 * Gets drag and drop event target element
		 * @private
		 * @method getItem
		 * @param  {object} eventOrItem
		 * @return {?HTMLElement}
		 */
		getItem: function(eventOrItem)
		{
			if (!BX.Type.isDomNode(eventOrItem))
			{
				if ((!eventOrItem || !BX.Type.isDomNode(eventOrItem.target)))
				{
					return null;
				}
			}
			else
			{
				eventOrItem = {target: eventOrItem};
			}

			let item = eventOrItem.target.closest('.' + this.classItem);
			if (!BX.Type.isDomNode(item))
			{
				item = eventOrItem.target.closest(
					'.' + this.classDefaultSubmenuItem + ', .' + this.classDefaultSubmenuDelimimeter
				);
			}

			return item;
		},

		getItemData: function(item)
		{
			if (!BX.Type.isDomNode(item))
			{
				return {};
			}

			const result = this.itemData.get(item);
			if (result)
			{
				return result;
			}

			let data;
			try
			{
				data = JSON.parse(item.dataset.item);
			}
			catch(err)
			{
				data = {};
			}

			this.setItemData(item, data);

			return data;
		},

		setItemData(item, data)
		{
			if (BX.Type.isElementNode(item) && BX.Type.isPlainObject(data))
			{
				data.NODE = item;

				this.itemData.set(item, data);
			}
		},

		/**
		 * Sets default opacity style
		 * @private
		 * @method setOpacity
		 * @param {object} item
		 */
		setOpacity: function(item)
		{
			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			BX.style(item, 'opacity', 0.5);
		},

		/**
		 * Unset opacity style
		 * @private
		 * @method unsetOpacity
		 * @param  {object} item
		 * @return {undefined}
		 */
		unsetOpacity: function(item)
		{
			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			BX.style(item, 'opacity', '1');
		},

		/**
		 * Sets drag styles
		 * @private
		 * @method setDragStyles
		 */
		setDragStyles: function()
		{
			BX.Dom.addClass(this.listContainer, this.classOnDrag);
			BX.Dom.addClass(BX(this.getMoreMenuId(true)), this.classOnDrag);

			this.setOpacity(this.dragItem);
		},

		/**
		 * Unset drag styles
		 * @private
		 * @method unsetDragStyles
		 * @return {undefined}
		 */
		unsetDragStyles: function()
		{
			const submenu = this.getMoreMenu();
			this.getAllItems().forEach((current) => {
				this.unsetOpacity(current);
				BX.Dom.removeClass(current, this.classItemOver);
			});

			if (submenu && BX.Type.isArray(submenu.menuItems) && submenu.menuItems.length)
			{
				submenu.menuItems.forEach((current) => {
					this.unsetOpacity(current);
					BX.Dom.removeClass(current.layout.item, this.classItemOver);
				});
			}

			BX.Dom.removeClass(this.listContainer, this.classOnDrag);
			BX.Dom.removeClass(BX(this.getMoreMenuId(true)), this.classOnDrag);
		},

		/**
		 * Gets icon class
		 * @private
		 * @method getIconClass
		 * @param  {object} item
		 * @return {string} className
		 */
		getIconClass: function(item)
		{
			let result = '';
			if (BX.Type.isDomNode(item) &&
				('dataset' in item) &&
				('class' in item.dataset) &&
				(BX.Type.isStringFilled(item.dataset.class)))
			{
				result = item.dataset.class;
			}

			return result;
		},

		/**
		 * Disables the element
		 * @private
		 * @method disableItem
		 * @param  {HTMLElement} item
		 * @return {undefined}
		 */
		disableItem: function(item)
		{
			const alias = this.getItemAlias(item);
			if (item && ('dataset' in item))
			{
				item.dataset.disabled = 'true';
				if (alias)
				{
					alias.dataset.disabled = 'true';
				}
			}
		},

		/**
		 * Disables the element
		 * @private
		 * @method enableItem
		 * @param  {HTMLElement} item
		 * @return {undefined}
		 */
		enableItem: function(item)
		{
			let alias;

			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			if (this.isSubmenuItem(item))
			{
				BX.Dom.removeClass(item, this.classItemDisabled);
				alias = this.getItemAlias(item);

				if (BX.Type.isDomNode(alias))
				{
					alias.dataset.disabled = 'false';
				}
			}
		},

		/**
		 * Gets alias link
		 * @private
		 * @method getAliasLink
		 * @param  {object} item
		 * @return {string}
		 */
		getAliasLink: function(item)
		{
			return this.dataValue(item, 'link') || '';
		},

		/**
		 * Gets item alias
		 * @private
		 * @method getItemAlias
		 * @param  {HTMLElement} item
		 * @return {?HTMLElement}
		 */
		getItemAlias: function(item)
		{
			let result = null;

			if (!BX.Type.isDomNode(item))
			{
				return result;
			}

			const allItems = this.getAllItems();
			const isSubmenuItem = this.isSubmenuItem(item);
			const isListItem = this.isListItem(item);

			if (!isSubmenuItem && !isListItem)
			{
				return result;
			}

			if (isSubmenuItem)
			{
				allItems.forEach(function(current) {
					BX.Dom.hasClass(item, this.getAliasLink(current)) && (result = current);
				}, this);
			}

			if (isListItem)
			{
				result = BX.Buttons.Utils.getByClass(document, this.getAliasLink(item));
			}

			return result;
		},

		/**
		 * @param {?HTMLElement} item
		 */
		hideItem: function(item)
		{
			!!item && BX.Dom.addClass(item, this.classSecret);
		},

		/**
		 * @param {?HTMLElement} item
		 */
		showItem: function(item)
		{
			!!item && BX.Dom.removeClass(item, this.classSecret);
		},

		/**
		 * Replaces drag item
		 * @private
		 * @method fakeDragItem
		 * @return {undefined}
		 */
		fakeDragItem: function()
		{
			if (!BX.Type.isDomNode(this.dragItem) || !BX.Type.isDomNode(this.overItem))
			{
				return;
			}

			let fakeDragItem = null;
			if (this.isDragToSubmenu())
			{
				fakeDragItem = this.getItemAlias(this.dragItem);
				if (fakeDragItem !== this.dragItem)
				{
					this.listContainer.appendChild(this.dragItem);
					this.dragItem = fakeDragItem;
					this.showItem(this.dragItem);
					this.adjustMoreButtonPosition();
					this.updateMoreMenuItems();
					this.tmp.moved = false;
					this.tmp.movetToSubmenu = true;
					this.setOpacity(this.dragItem);
				}
			}

			if (this.isDragToList() && !this.tmp.movetToSubmenu)
			{
				fakeDragItem = this.getItemAlias(this.dragItem);
				if (fakeDragItem !== this.dragItem)
				{
					this.hideItem(this.dragItem);
					this.dragItem = fakeDragItem;
					this.adjustMoreButtonPosition();
					this.updateMoreMenuItems();
					this.setOpacity(this.dragItem);
				}
			}

			this.tmp.movetToSubmenu = false;
		},

		/**
		 * Updates submenu items relative to hidden items
		 * @private
		 * @method updateSubmenuItems
		 * @return {undefined}
		 */
		updateMoreMenuItems: function()
		{
			const submenu = this.getMoreMenu();
			if (submenu === null)
			{
				return;
			}

			const submenuItems = submenu.menuItems;
			if (!BX.Type.isArray(submenuItems) || !submenuItems.length)
			{
				return;
			}

			const hiddenItems = this.getHiddenItems();
			const disabledItems = this.getDisabledItems();
			const items = disabledItems.concat(hiddenItems);

			submenuItems.forEach(current => {
				const some = [].some.call(items, someEl => {
					return (
						BX.Dom.hasClass(current.layout.item, this.dataValue(someEl, 'link')) ||
						this.isDisabled(current.layout.item) ||
						this.isSeparator(current.layout.item) ||
						this.isDropzone(current.layout.item)
					);
				});

				if (
					some
					|| (
						this.isSettings(current.layout.item)
						|| this.isApplySettingsButton(current.layout.item)
						|| this.isResetSettingsButton(current.layout.item)
						|| this.isNotHiddenItem(current.layout.item)
						|| this.isSeparator(current.layout.item)
						|| current.layout.item === this.dragItem
					)
					&& !this.isMoreButton(current.layout.item)
				)
				{
					this.showItem(current.layout.item);
				}
				else
				{
					this.hideItem(current.layout.item);
				}
			});
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isNotHiddenItem: function(item)
		{
			return BX.Dom.hasClass(item, this.classSubmenuNoHiddenItem);
		},

		/**
		 * @return {?HTMLElement}
		 */
		getNotHidden: function()
		{
			return BX.Buttons.Utils.getByClass(this.getMoreMenuContainer(), this.classSubmenuNoHiddenItem);
		},

		/**
		 * Sets styles for hovered item
		 * @private
		 * @method setOverStyles
		 * @param {object} item
		 */
		setOverStyles: function(item)
		{
			if (BX.Type.isDomNode(item) && !BX.Dom.hasClass(item, this.classItemOver))
			{
				BX.Dom.addClass(item, this.classItemOver);
			}
		},

		/**
		 * Unset styles for hovered item
		 * @private
		 * @method unsetOverStyles
		 * @param  {object} item
		 * @return {undefined}
		 */
		unsetOverStyles: function(item)
		{
			if (BX.Type.isDomNode(item) && BX.Dom.hasClass(item, this.classItemOver))
			{
				BX.Dom.removeClass(item, this.classItemOver);
			}
		},

		/**
		 * Gets value data attribute
		 * @private
		 * @method dataValue
		 * @param  {object} item
		 * @param  {string} key
		 * @return {string}
		 */
		dataValue: function(item, key)
		{
			let result = '';
			if (BX.Type.isDomNode(item))
			{
				const tmpResult = BX.data(item, key);
				if (typeof(tmpResult) !== 'undefined')
				{
					result = tmpResult;
				}
			}

			return result;
		},

		/**
		 * Executes script
		 * @private
		 * @method execScript
		 * @param  {string} script
		 */
		/*jshint -W061 */
		execScript: function(script, event)
		{
			if (BX.Type.isStringFilled(script))
			{
				const fn = new Function('event', script);
				fn(event);
				//eval('(function(event) {' + script + '})();');
			}
		},

		/**
		 * Shows license window
		 * @return {undefined}
		 */
		showLicenseWindow: function()
		{
			if (!B24.licenseInfoPopup)
			{
				return;
			}

			const popup = B24.licenseInfoPopup;

			popup.init({
				B24_LICENSE_BUTTON_TEXT: this.message('MIB_LICENSE_BUY_BUTTON'),
				B24_TRIAL_BUTTON_TEXT: this.message('MIB_LICENSE_TRIAL_BUTTON'),
				IS_FULL_DEMO_EXISTS: this.licenseParams.isFullDemoExists,
				HOST_NAME: this.licenseParams.hostname,
				AJAX_URL: this.licenseParams.ajaxUrl,
				LICENSE_ALL_PATH: this.licenseParams.licenseAllPath,
				LICENSE_DEMO_PATH: this.licenseParams.licenseDemoPath,
				FEATURE_GROUP_NAME: this.licenseParams.featureGroupName,
				AJAX_ACTIONS_URL: this.licenseParams.ajaxActionsUrl,
				B24_FEATURE_TRIAL_SUCCESS_TEXT: this.message('MIB_LICENSE_WINDOW_TRIAL_SUCCESS_TEXT')
			});

			popup.show(
				'main-buttons',
				this.message('MIB_LICENSE_WINDOW_HEADER_TEXT'),
				this.message('MIB_LICENSE_WINDOW_TEXT')
			);

		},

		/**
		 * mouse click event handler
		 * @private
		 * @method handleItemClick
		 * @param  {object} event ondragstart event object
		 * @return {undefined}
		 */
		_handleItemClick: function(event)
		{
			if (!this.isEditEnabled())
			{
				const item = this.getItem(event);
				this.showChildMenu(item);
			}
		},

		handleEditButtonClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			let item = this.getItem(event);
			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			if (this.isSubmenuItem(item))
			{
				item = this.getItemAlias(item);
			}

			const itemData = this.getItemData(item);
			const menu = this.getItemEditMenu();
			if (menu && menu.popupWindow.isShown() && this.lastEditNode === item)
			{
				menu.popupWindow.close();
			}
			else
			{
				this.showItemEditMenu(itemData, event.target);
			}

			this.lastEditNode = item;
		},

		handleDragButtonClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
		},

		handleItemPinEnter: function(event)
		{
			const subMenu = this.getChildMenu();
			if (subMenu)
			{
				this.showPinHint(event.currentTarget);
			}
		},

		showPinHint: function(target)
		{
			const height = 48;
			const hint = BX.Main.PopupManager.create({
				id: 'main-buttons-pin-hint',
				closeByEsc: true,
				padding: 15,
				className: 'main-buttons-pin-hint-popup',
				height: height,
				cacheable: false,
				autoHide: true,
				bindOptions: {
					forceBindPosition: true
				},
				content: this.message('MIB_PIN_HINT'),
				darkMode: true,
				events: {
					onAfterShow: function(event) {
						const popup = event.getTarget();

						const targetPosition = BX.Dom.getPosition(target);
						const popupPosition = BX.Dom.getPosition(popup.getPopupContainer());

						if (popupPosition.left < targetPosition.left + targetPosition.width)
						{
							popup.setAngle({ position: 'top', offset: 0 });
							popup.setOffset({ offsetLeft: 20, offsetTop: 5 });
							popup.adjustPosition();
						}
					}
				}
			});

			hint.setAngle({
				position: 'left',
				offset: 0,
			});
			hint.setOffset({
				offsetLeft: target.offsetWidth + 5,
				offsetTop: -target.offsetHeight / 2 - height / 2 - 2,
			});
			hint.setBindElement(target);
			hint.show();
			hint.adjustPosition();
		},

		closePinHint: function()
		{
			const hint = BX.Main.PopupManager.getPopupById('main-buttons-pin-hint');
			if (hint)
			{
				hint.close();
			}
		},

		handleItemPinLeave: function(event)
		{
			this.closePinHint();
		},

		handleItemPin: function(itemData, rootNode, event)
		{
			event.stopPropagation();
			event.preventDefault();

			const newItem = this.createRootItem(itemData);

			BX.Dom.addClass(newItem, 'main-buttons-item-insert-animation');
			BX.Dom.insertBefore(newItem, rootNode);
			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					BX.Dom.style(newItem, {
						width: newItem.scrollWidth + 'px',
						opacity: 1,
					});
				});
			});

			const finalize = () => {
				BX.Dom.removeClass(newItem, 'main-buttons-item-insert-animation');
				BX.Dom.style(newItem, 'width', null);
				BX.Dom.style(newItem, 'opacity', null);

				const rootItemData = this.getItemData(rootNode);
				this.recalculateItemsCounters([rootItemData]);

				this.adjustMoreButtonPosition();
				this.showMoreMenu();
				this.updateMoreButtonCounter();
				this.showChildMenu(rootNode);
				this.saveSettings();
			};

			setTimeout(finalize, 300);

			this.initItem(newItem);
			this.pinItem(itemData);

			this.destroyMoreMenu();
			this.destroyChildMenu();
		},

		handleItemUnpin: function(itemData, rootNode)
		{
			BX.Dom.style(rootNode, { width: rootNode.offsetWidth + 'px' });
			BX.Dom.addClass(rootNode, 'main-buttons-item-insert-animation');
			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					BX.Dom.style(
						rootNode,
						{
							width: 0,
							margin: 0,
							opacity: 0,
						}
					);
				});
			});

			const finalize = () => {
				BX.Dom.remove(rootNode);
				this.adjustMoreButtonPosition();
				this.showMoreMenu();
				this.updateMoreButtonCounter();
				this.saveSettings();
			};

			setTimeout(finalize, 300);

			this.pinItem(itemData, false);

			const roots = [];
			let rootItemId = '';
			const ids = itemData['ID'].split(':');
			ids.forEach(id => {
				rootItemId += `${rootItemId === '' ? '' : ':'}${id}`;
				const rootItem = this.getItemById(rootItemId);
				if (rootItem)
				{
					roots.push(this.getItemData(rootItem));
				}
			});

			this.destroyMoreMenu();
			this.recalculateItemsCounters(roots);
		},

		pinItem: function(itemData, flag = true)
		{
			const itemId = itemData['ID']
			const ids = itemId.split(':');

			let rootItemId = '';
			ids.forEach((id, index) => {
				rootItemId += `${rootItemId === '' ? '' : ':'}${id}`;
				const rootItem = this.getItemById(rootItemId);
				if (!rootItem)
				{
					return;
				}

				const rootItemData = this.getItemData(rootItem);

				let subItems = rootItemData['ITEMS'];
				let subItemId = rootItemId;
				const subItemIds = ids.slice(index + 1);
				const parentItems = [rootItemData];

				while (BX.Type.isArrayFilled(subItemIds) && BX.Type.isArrayFilled(subItems))
				{
					subItemId = subItemId + ':' + subItemIds.shift();
					for (let i = 0; i < subItems.length; i++)
					{
						const subItem = subItems[i];
						if (subItem['ID'] === itemId)
						{
							subItem['IS_PINNED'] = flag;
							for (let index = parentItems.length - 1; index >= 0; index--)
							{
								const parentItem = parentItems[index];
								const firstLevel = index === 0;

								if (flag)
								{
									const hasVisibleSubItems = parentItem['ITEMS'].some((item) => {
										const isPinned = item['IS_PINNED'] === true;
										const isDisbanded = item['IS_DISBANDED'] === true;
										const isDelimiter = item['IS_DELIMITER'] === true;

										return !isPinned && !isDisbanded && !isDelimiter;
									});

									if (!hasVisibleSubItems)
									{
										parentItem['IS_DISBANDED'] = true;

										if (firstLevel)
										{
											rootItem.dataset.disbanded = true;
										}
									}
								}
								else
								{
									if (firstLevel)
									{
										rootItem.dataset.disbanded = false;
									}

									parentItem['IS_DISBANDED'] = false;
								}

								const hasActiveSubItems = parentItem['ITEMS'].some((item) => {
									return (
										item['IS_ACTIVE'] === true
										&& item['IS_PINNED'] !== true
										&& item['IS_DELIMITER'] !== true
									);
								});

								if (hasActiveSubItems)
								{
									parentItem['IS_ACTIVE'] = true;
									if (firstLevel)
									{
										this.activateItem(rootItem);
									}
								}
								else
								{
									parentItem['IS_ACTIVE'] = false;
									if (firstLevel)
									{
										this.deactivateItem(rootItem);
									}
								}
							}

							return; // next forEach
						}
						else if (subItem['ID'] === subItemId)
						{
							subItems = subItem['ITEMS'];
							subItemId = subItem['ID'];
							parentItems.push(subItem);

							break;
						}
					}
				}
			});
		},

		getParentItem: function(itemId)
		{
			const ids = itemId.split(':');
			let rootItemId = '';
			for (let index = 0; index < ids.length; index++)
			{
				rootItemId += `${rootItemId === '' ? '' : ':'}${ids[index]}`;
				const rootItem = this.getItemById(rootItemId);
				if (!rootItem)
				{
					continue;
				}

				const rootItemData = this.getItemData(rootItem);

				let subItems = rootItemData['ITEMS'];
				let subItemId = rootItemId;
				const subItemIds = ids.slice(index + 1);
				let parentItem = null;

				while (BX.Type.isArrayFilled(subItemIds) && BX.Type.isArrayFilled(subItems))
				{
					subItemId = subItemId + ':' + subItemIds.shift();
					for (let i = 0; i < subItems.length; i++)
					{
						const subItem = subItems[i];
						if (subItem['ID'] === itemId)
						{
							return parentItem === null ? rootItemData : parentItem;
						}
						else if (subItem['ID'] === subItemId)
						{
							subItems = subItem['ITEMS'];
							subItemId = subItem['ID'];
							parentItem = subItem;

							break;
						}
					}
				}
			}

			return null;
		},

		/**
		 * dragstart event handler
		 * @private
		 * @method _onDragStart
		 * @param  {object} event ondragstart event object
		 * @return {undefined}
		 */
		_onDragStart: function(event)
		{
			const visibleItems = this.getVisibleItems();
			const visibleItemsLength = BX.Type.isArray(visibleItems) ? visibleItems.length : null;
			this.dragItem = this.getItem(event);

			if (!BX.Type.isDomNode(this.dragItem))
			{
				return;
			}

			if (visibleItemsLength === 1 && this.isListItem(this.dragItem))
			{
				event.preventDefault();
				BX.onCustomEvent(window, 'BX.Main.InterfaceButtons:onHideLastVisibleItem', [this.dragItem, this]);
				return;
			}

			if (
				this.isMoreButton(this.dragItem)
				|| this.isSeparator(this.dragItem)
				|| this.isNotHiddenItem(this.dragItem)
				|| BX.Dom.attr(this.dragItem, 'data-parent-item-id')
				|| BX.Dom.attr(this.dragItem, 'data-has-child')
			)
			{
				event.preventDefault();
				return;
			}

			this.onDragStarted = true;

			this.closeChildMenu();
			this.destroyItemEditMenu();

			if (this.isListItem(this.dragItem))
			{
				this.showMoreMenu();
			}

			this.setDragStyles();

			if (!this.isEditEnabled())
			{
				this.enableEdit();
			}
		},

		/**
		 * dragend event handler
		 * @private
		 * @method _onDragEnd
		 * @param  {object} event dragend event object
		 * @return {undefined}
		 */
		_onDragEnd: function(event)
		{
			event.preventDefault();
			const item = this.getItem(event);

			this.onDragStarted = false;

			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			this.unsetDragStyles();
			this.refreshMoreMenu();
			if (!this.isEditEnabled())
			{
				this.closeMoreMenu();
			}

			const nextVisible = BX.findNextSibling(this.dragItem, (node) => {
				return this.isVisibleItem(node);
			});

			const prevVisible = BX.findPreviousSibling(this.dragItem, (node) => {
				return this.isVisibleItem(node);
			});

			if (
				BX.Dom.hasClass(prevVisible, this.classHiddenLabel)
				|| (this.isDisabled(prevVisible) && this.isSubmenuItem(prevVisible))
				|| (this.isDisabled(nextVisible) && this.isSubmenuItem(nextVisible))
			)
			{
				this.disableItem(this.dragItem);
				this.refreshMoreMenu();
			}

			if (this.isEditEnabled())
			{
				this.enableEdit();
			}
			else
			{
				this.disableEdit();
			}

			this.updateMoreButtonCounter();

			this.saveSettings();
			this.dragItem = null;
			this.overItem = null;
			this.tmp.moved = false;
		},

		updateMoreButtonCounter: function()
		{
			let hiddenItems = this.getHiddenItems();
			const disabledItems = this.getDisabledItems();
			hiddenItems = hiddenItems.concat(disabledItems);
			let sumCount = 0;

			if (BX.Type.isArray(hiddenItems))
			{
				hiddenItems.forEach((current) => {
					const item = this.getItemData(current);
					const counter = BX.Type.isNumber(item['COUNTER']) && item['COUNTER'] > 0 ? item['COUNTER'] : 0;
					sumCount += counter;
				});
			}

			if (BX.Type.isNumber(sumCount))
			{
				this.setMoreButtonCounter(sumCount);
			}
		},

		/**
		 * dragenter event handler
		 * @private
		 * @method _onDragEnter
		 * @param  {object} event dragenter event object
		 * @return {undefined}
		 */
		_onDragEnter: function(event)
		{
			const item = this.getItem(event);
			if (BX.Type.isDomNode(item) && this.isNotHiddenItem(item))
			{
				this.setOverStyles(item);
			}
		},

		/**
		 * dragover event handler
		 * @private
		 * @method _onDragOver
		 * @param  {object} event dragover event object
		 * @return {undefined}
		 */
		_onDragOver: function(event)
		{
			event.preventDefault();

			this.overItem = this.getItem(event);

			if (
				!BX.Type.isDomNode(this.overItem)
				|| !BX.Type.isDomNode(this.dragItem)
				|| this.overItem === this.dragItem
				|| this.isNotHiddenItem(this.overItem)
				|| BX.Dom.attr(this.overItem, 'data-parent-item-id')
				|| BX.Dom.attr(this.overItem, 'data-has-child')
			)
			{
				return;
			}

			this.fakeDragItem();

			const isNext = this.isNext(event);
			const isGoodPosition = this.isGoodPosition(event);

			if (isNext && isGoodPosition)
			{
				let nextSiblingItem;
				let insertAfter = false;
				if (this.isListItem(this.overItem))
				{
					nextSiblingItem = this.findNextSiblingByClass(this.overItem, this.classItem);
					if (nextSiblingItem === null && this.getLastVisibleItem() === this.overItem)
					{
						nextSiblingItem = this.overItem;
						insertAfter = true;
					}
				}
				else
				{
					nextSiblingItem = this.findNextSiblingByClass(this.overItem, this.classSubmenuItem);
					if (
						this.isSettings(nextSiblingItem)
						|| this.isApplySettingsButton(nextSiblingItem)
						|| this.isResetSettingsButton(nextSiblingItem)
					)
					{
						return;
					}

					if (nextSiblingItem === null && this.getItemAlias(this.getLastDisabledItem()) === this.overItem)
					{
						nextSiblingItem = this.overItem;
						insertAfter = true;
					}
				}

				if (BX.Type.isDomNode(nextSiblingItem))
				{
					this.moveButton(nextSiblingItem, insertAfter);
					this.moveButtonAlias(nextSiblingItem, insertAfter);
					this.adjustMoreButtonPosition();
					this.updateMoreMenuItems();
				}
			}
			else if (!isNext && isGoodPosition && !BX.Dom.hasClass(this.overItem, this.classHiddenLabel))
			{
				this.moveButton(this.overItem);
				this.moveButtonAlias(this.overItem);
				this.adjustMoreButtonPosition();
				this.updateMoreMenuItems();
			}
		},

		/**
		 * dragleave event handler
		 * @private
		 * @method _onDragLeave
		 * @param  {object} event dragleave event object
		 * @return {undefined}
		 */
		_onDragLeave: function(event)
		{
			const item = this.getItem(event);
			if (BX.Type.isDomNode(item))
			{
				this.unsetOverStyles(event.target);
			}
		},

		/**
		 * drop event handler
		 * @private
		 * @method _onDrop
		 * @param  {object} event drop event object
		 * @return {undefined}
		 */
		_onDrop: function(event)
		{
			const item = this.getItem(event);
			if (!BX.Type.isDomNode(item))
			{
				return;
			}

			if (this.isNotHiddenItem(item) || this.isDisabled(item))
			{
				this.disableItem(this.dragItem);
				this.adjustMoreButtonPosition();
			}

			const aliasDragItem = this.getItemAlias(this.dragItem);
			if (this.isListItem(aliasDragItem))
			{
				aliasDragItem.dataset.disabled = 'false';
			}

			this.unsetDragStyles();

			event.preventDefault();
		},

		handleMoreMenuFirstShow: function(event)
		{
			const popup = event.getTarget();

			BX.Event.bind(popup.getPopupContainer(), 'mouseenter', this.handleMoreMenuMouseEnter.bind(this));
			BX.Event.bind(popup.getPopupContainer(), 'mouseleave', this.handleMoreMenuMouseLeave.bind(this));
		},

		handleMoreMenuMouseEnter: function()
		{
			clearTimeout(this.submenuLeaveTimeout);
		},

		handleMoreMenuMouseLeave: function()
		{
			this.tryCloseMoreMenuOnTimeout();
		},

		tryCloseMoreMenuOnTimeout: function()
		{
			clearTimeout(this.submenuLeaveTimeout);

			if (!this.isEditEnabled())
			{
				this.submenuLeaveTimeout = setTimeout(() => {
					this.closeMoreMenu();
				}, 500);
			}
		},

		handleMoreMenuShow: function(event)
		{
			BX.Event.EventEmitter.emit('BX.Main.InterfaceButtons:onMenuShow');
			BX.Event.EventEmitter.emit(this, 'BX.Main.InterfaceButtons:onMoreMenuShow', {event});
			setTimeout(() => {
				if (!this.isEditEnabled())
				{
					event.getTarget().setAutoHide(true);
				}
			}, 500);
		},

		handleMoreMenuClose: function()
		{
			BX.Event.EventEmitter.emit(this, 'BX.Main.InterfaceButtons:onMoreMenuClose');
			this.setMoreMenuShown(false);

			if (this.isEditEnabled())
			{
				this.activateItem(this.moreButton);
			}
			else
			{
				if (!this.isActiveInMoreMenu())
				{
					this.deactivateItem(this.moreButton);
				}
			}

			this.destroyItemEditMenu();
		},

		_onChildMenuFirstShow: function(event)
		{
			const popup = event.getTarget();

			BX.Event.bind(popup.getPopupContainer(), 'mouseenter', this._onChildMenuMouseEnter.bind(this));
			BX.Event.bind(popup.getPopupContainer(), 'mouseleave', this._onChildMenuMouseLeave.bind(this));
		},

		_onChildMenuMouseEnter: function()
		{
			clearTimeout(this.childMenuLeaveTimeout);
		},

		_onChildMenuMouseLeave: function()
		{
			this.tryCloseChildMenuOnTimeout();
		},

		tryCloseChildMenuOnTimeout: function()
		{
			if (this.isEditEnabled())
			{
				return;
			}

			clearTimeout(this.childMenuLeaveTimeout);

			this.childMenuLeaveTimeout = setTimeout(() => {
				this.closeChildMenu();
			}, 500);
		},

		_onChildMenuShow: function(item, event)
		{
			BX.Dom.addClass(item, this.classMenuShown);
			BX.Event.EventEmitter.emit('BX.Main.InterfaceButtons:onMenuShow');
			BX.Event.EventEmitter.emit(this, 'BX.Main.InterfaceButtons:onSubMenuShow', {item, event});

			if (this.theme !== 'default' && this.theme !== 'air')
			{
				this.centerPopupArrow(event.getTarget(), item);
			}

			setTimeout(() => {
				event.getTarget().setAutoHide(true);
			}, 500);
		},

		_onChildMenuClose: function(item)
		{
			BX.Event.EventEmitter.emit(this, 'BX.Main.InterfaceButtons:onSubMenuClose');
			BX.Dom.removeClass(item, this.classMenuShown);
			this.closePinHint();
		},

		handleAdjustPosition: function(item, event)
		{
			const activeItemMargin = 25;
			const position = BX.Dom.getPosition(item);
			const popup = event.getTarget();
			if (event.left < (position.left - activeItemMargin))
			{
				const popupWidth = popup.getPopupContainer().offsetWidth;
				const left = position.right - popupWidth + activeItemMargin;
				if (left > 0)
				{
					event.left = left;
					BX.Dom.addClass(popup.getPopupContainer(), '--left-handed');
				}
			}
			else
			{
				BX.Dom.removeClass(popup.getPopupContainer(), '--left-handed');
			}
		},

		/**
		 * resize window event handler
		 * @private
		 * @method _onResizeHandler
		 * @return {object} window resize event object
		 */
		_onResizeHandler: function()
		{
			this.adjustMoreButtonPosition();
			this.updateMoreMenuItems();
			this.closeChildMenu();

			if (!this.isSettingsEnabled)
			{
				this.visibleControlMoreButton();
			}
		},

		handleMoreButtonClick: function(event)
		{
			event.preventDefault();
			this.showMoreMenu();
		},

		handleMoreButtonMouseEnter: function(event)
		{
			if (this.enableItemMouseEnter)
			{
				clearTimeout(this.menuShowTimeout);
				this.menuShowTimeout = setTimeout(() => {
					this.showMoreMenu();
				}, 100);
			}

			clearTimeout(this.submenuLeaveTimeout);
		},

		handleMoreButtonMouseLeave: function()
		{
			clearTimeout(this.menuShowTimeout);
			this.tryCloseMoreMenuOnTimeout();
		},

		handleItemMouseEnter: function(event)
		{
			if (!this.enableItemMouseEnter)
			{
				return;
			}

			if (this.onDragStarted)
			{
				return;
			}

			const item = this.getItem(event);

			clearTimeout(this.childMenuLeaveTimeout);
			clearTimeout(this.menuShowTimeout);

			this.menuShowTimeout = setTimeout(() => {
				this.showChildMenu(item);
			}, 100);

			BX.Dom.addClass(item, this.classItemOver);
		},

		handleItemMouseLeave: function(event)
		{
			clearTimeout(this.menuShowTimeout);

			if (!this.enableItemMouseEnter)
			{
				return;
			}

			if (this.onDragStarted)
			{
				return;
			}

			const item = this.getItem(event);
			BX.Dom.removeClass(item, this.classItemOver);

			this.tryCloseChildMenuOnTimeout();
		},

		handleMoreMenuItemMouseEnter: function(event)
		{
			if (this.isEditEnabled())
			{
				event.preventDefault();
			}
		},

		/**
		 * @return {?HTMLElement}
		 */
		getSettingsResetButton: function()
		{
			return BX.Buttons.Utils.getByClass(this.getMoreMenuContainer(), this.classSettingsResetButton);
		},

		/**
		 * Checks whether the item is disabled
		 * @public
		 * @method isDisabled
		 * @param  {object} item
		 * @return {boolean}
		 */
		isDisabled: function(item)
		{
			let result = false;

			if (BX.Type.isDomNode(item))
			{
				result = (
					this.dataValue(item, 'disabled') === 'true' || BX.Dom.hasClass(item, this.classItemDisabled)
				);
			}

			return result;
		},

		isPinned: function(item)
		{
			let result = false;

			if (BX.Type.isDomNode(item))
			{
				result = this.getItemData(item)['IS_PINNED'] === true;
			}

			return result;
		},

		/**
		 * @param {HTMLElement} item
		 * @return {boolean}
		 */
		isSettings: function(item)
		{
			let result = false;

			if (BX.Type.isDomNode(item))
			{
				result = BX.Dom.hasClass(item, this.classSettingMenuItem);
			}

			return result;
		},

		/**
		 * Checks whether the item is locked
		 * @private
		 * @method isLocked
		 * @param  {object}  item
		 * @return {boolean}
		 */
		isLocked: function(item)
		{
			let result = false;

			if (BX.Type.isDomNode(item))
			{
				result = (
					this.dataValue(item, 'locked') === 'true' ||
					BX.Dom.hasClass(item, this.classItemLocked)
				);
			}

			return result;
		},

		/**
		 * Checks whether the item is dropzone
		 * @private
		 * @method isOvered
		 * @param  {object} item
		 * @return {boolean}
		 */
		isDropzone: function(item)
		{
			return BX.Dom.hasClass(item, this.classDropzone);
		},

		/**
		 * Checks whether the hovered item is next
		 * @private
		 * @method isNext
		 * @param  {object} event dragover event object
		 * @return {boolean}
		 */
		isNext: function(event)
		{
			const dragItemRect = this.dragItem.getBoundingClientRect();
			const overItemRect = this.overItem.getBoundingClientRect();
			const styles = getComputedStyle(this.dragItem);
			const dragItemMarginRight = parseInt(styles.marginRight.replace('px', ''));
			let result = null;

			if (this.isListItem(this.overItem))
			{
				result = (
					event.clientX > (overItemRect.left - dragItemMarginRight) && event.clientX > dragItemRect.right
				);
			}

			if (this.isSubmenuItem(this.overItem))
			{
				result = (
					event.clientY > dragItemRect.top
				);
			}

			return result;
		},

		/**
		 * Checks whether it is possible to move the item
		 * @private
		 * @method isGoodPosition
		 * @param  {object} event dragover event object
		 * @return {boolean}
		 */
		isGoodPosition: function(event)
		{
			const overItem = this.overItem;
			if (!BX.Type.isDomNode(overItem))
			{
				return false;
			}

			let result;
			const overItemRect = overItem.getBoundingClientRect();
			if (this.isListItem(overItem))
			{
				result = (
					(this.isNext(event) && (event.clientX >= (overItemRect.left + (overItemRect.width / 2)))) ||
					(!this.isNext(event) && (event.clientX <= (overItemRect.left + (overItemRect.width / 2))))
				);
			}

			if (this.isSubmenuItem(overItem))
			{
				result = (
					(this.isNext(event) && (event.clientY >= (overItemRect.top + (overItemRect.height / 2)))) ||
					(!this.isNext(event) && (event.clientY <= (overItemRect.top + (overItemRect.height / 2))))
				);
			}

			return result;
		},

		/**
		 * Checks whether the item is a submenu item
		 * @private
		 * @method isSubmenuItem
		 * @param  {object} item
		 * @return {boolean}
		 */
		isSubmenuItem: function(item)
		{
			return BX.Dom.hasClass(item, this.classSubmenuItem);
		},

		/**
		 * Checks whether the item is visible
		 * @public
		 * @method isVisibleItem
		 * @param  {object}  item
		 * @return {boolean}
		 */
		isVisibleItem: function(item)
		{
			if (!BX.Type.isDomNode(item))
			{
				return false;
			}

			return item.offsetTop === 0 && !BX.Dom.hasClass(item, '--hidden');
		},

		/**
		 * Checks whether the item is more button
		 * @private
		 * @method isMoreButton
		 * @param  {object} item
		 * @return {boolean}
		 */
		isMoreButton: function(item)
		{
			let result = false;
			if (BX.Type.isDomNode(item) && BX.Dom.hasClass(item, this.classItemMore))
			{
				result = true;
			}

			return result;
		},

		/**
		 * Checks whether the item is list item
		 * @private
		 * @method isListItem
		 * @param  {object} item
		 * @return {boolean}
		 */
		isListItem: function(item)
		{
			let result = false;

			if (BX.Type.isDomNode(item) && BX.Dom.hasClass(item, this.classItem))
			{
				result = true;
			}

			return result;
		},

		/**
		 * Checks whether the item is sublink
		 * @private
		 * @method isSublink
		 * @param  {object}  item
		 * @return {boolean}
		 */
		isSublink: function(item)
		{
			let result = false;
			if (BX.Type.isDomNode(item))
			{
				result = BX.Dom.hasClass(item, this.classItemSublink);
			}

			return result;
		},

		/**
		 * Checks whether the item is separator
		 * @private
		 * @method isSeparator
		 * @param  {object}  item
		 * @return {boolean}
		 */
		isSeparator: function(item)
		{
			let result = false;
			if (BX.Type.isDomNode(item))
			{
				result = BX.Dom.hasClass(item, this.classSeparator);
			}

			return result;
		},

		/**
		 * Checks that the element is dragged into the submenu
		 * @return {boolean}
		 */
		isDragToSubmenu: function()
		{
			return !this.isSubmenuItem(this.dragItem) && this.isSubmenuItem(this.overItem);
		},

		/**
		 * Checks that the element is dragged into the list
		 * @return {boolean}
		 */
		isDragToList: function()
		{
			return this.isSubmenuItem(this.dragItem) && !this.isSubmenuItem(this.overItem);
		}
	};
}


if (typeof(BX.Main.interfaceButtonsManager) === 'undefined')
{
	BX.Main.interfaceButtonsManager =
	{
		data: {},

		init: function(params)
		{
			let container = null;

			if (!BX.Type.isPlainObject(params) || !('containerId' in params))
			{
				throw 'BX.Main.interfaceButtonsManager: containerId not set in params Object';
			}

			container = BX(params.containerId);

			if (BX.Type.isDomNode(container))
			{
				this.data[params.containerId] = new BX.Main.interfaceButtons(container, params);
			}
			else
			{
				BX(BX.delegate(function() {
					container = BX(params.containerId);

					if (!BX.Type.isDomNode(container))
					{
						throw 'BX.Main.interfaceButtonsManager: container is not dom node';
					}

					this.data[params.containerId] = new BX.Main.interfaceButtons(container, params);
				}, this));
			}
		},

		getById: function(containerId)
		{
			let result = null;

			if (BX.type.isString(containerId) && BX.Type.isStringFilled(containerId))
			{
				try
				{
					result = this.data[containerId];
				}
				catch (e) {}
			}

			return result;
		},

		getObjects: function() {
			return this.data;
		}
	};
}
