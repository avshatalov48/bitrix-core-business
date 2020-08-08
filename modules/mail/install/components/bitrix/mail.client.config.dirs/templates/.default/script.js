;(function ()
{

	BX.namespace('BX.Mail.Client.Config.Dirs');
	BX.Mail.Client.Config.Dirs.Form = function (options)
	{
		this.mailboxId = options.mailboxId;
		this.dirs = options.dirs;
		this.items = {};
		this.maxLevelDirs = options.maxLevelDirs;
		this.container = options.container;
		this.buttonSubmit = options.buttonSubmit;
		this.buttonCancel = options.buttonCancel;
		this.itemRowSelector = options.itemRowSelector;
		this.subMenuSelector = options.subMenuSelector;
		this.itemContentSelector = options.itemContentSelector;
		this.checkboxSelector = options.checkboxSelector;
		this.levelButtonSelector = options.levelButtonSelector;
		this.childCounterContainerSelector = options.childCounterContainerSelector;
		this.syncChildCounterSelector = options.syncChildCounterSelector;
		this.totalChildCounterSelector = options.totalChildCounterSelector;
		this.urlRedirect = options.urlRedirect;
		this.menuDirsTypes = new BX.Mail.Client.Config.Dirs.DirsTypes({
			items: options.menuItemDirsTypes,
			dirs: options.dirs,
			mailboxId: options.mailboxId
		});
		this.selectAllButton = new BX.Mail.Client.Config.Dirs.SelectAllButton({
			container: options.dirs && options.dirs.length > 0 ? options.container : null,
			type: 'prepend',
			className: 'mail-config-dirs-button-select-all',
			id: 'mail-dirs-dropdown-menu',
			updateLabel: false
		});

		this.init();
	};

	BX.Mail.Client.Config.Dirs.Form.prototype = {
		init: function ()
		{
			if (!this.container)
			{
				return;
			}

			this.loadData();
			this.addEvents();
		},

		loadData: function ()
		{
			var list = this.container.querySelectorAll(':scope > ' + this.itemRowSelector);

			for (var i = 0; i < list.length; i++)
			{
				var options = {};

				options.container = list[i];
				options.checkbox = options.container.querySelector(this.itemContentSelector + ' ' + this.checkboxSelector);
				options.id = options.checkbox ? options.checkbox.dataset.id : null;
				options.path = options.checkbox ? options.checkbox.dataset.path : null;
				options.hasChild = options.checkbox ? options.checkbox.dataset.haschild : false;
				options.level = options.checkbox ? parseInt(options.checkbox.dataset.level) : 0;
				options.defaultChecked = options.checkbox ? options.checkbox.checked : null;
				options.root = this;
				options.levelButton = options.container.querySelector(this.itemContentSelector + ' ' + this.levelButtonSelector);

				var item = new BX.Mail.Client.Config.Dirs.DropdownMenuItem(options);

				this.addItem(item);
			}
		},

		addEvents: function ()
		{
			if (this.buttonSubmit)
			{
				this.buttonSubmit.addEventListener('click', this.onSubmitForm.bind(this));
			}

			if (this.buttonCancel)
			{
				this.buttonCancel.addEventListener('click', this.onCancelForm.bind(this));
			}

			if (this.selectAllButton)
			{
				this.selectAllButton.getButton().addEventListener('change', this.onChangeSelectAllButton.bind(this));
			}
		},

		addItem: function (item)
		{
			this.items[item.id] = item;
		},

		onCancelForm: function (e)
		{
			e.preventDefault();

			this.close();
		},

		onSubmitForm: function (e)
		{
			e.preventDefault();

			var list = [];
			for (var i in this.items)
			{
				if (!this.items[i].isModified())
				{
					continue;
				}

				this.items[i].resetDefaultCheckbox();

				var item = {};
				item.path = this.items[i].path;
				item.dirMd5 = this.items[i].id;
				item.value = this.items[i].checkbox.checked ? 1 : 0;

				list.push(item);
			}

			var listTypes = [];
			var itemsTypes = this.menuDirsTypes.getItemsTypes();

			for (var type in itemsTypes)
			{
				if (!itemsTypes.hasOwnProperty(type))
				{
					continue;
				}

				var item = {};
				item.path = itemsTypes[type].path;
				item.dirMd5 = itemsTypes[type].dirMd5;
				item.type = type;

				listTypes.push(item);
			}

			if (!list.length && !listTypes.length)
			{
				this.close();
				return;
			}

			BX.ajax.runComponentAction('bitrix:mail.client.config.dirs', 'save', {
				mode: 'class',
				data: {mailboxId: this.mailboxId, dirs: list, dirsTypes: listTypes}
			}).then(
				function (response)
				{
					this.close();
					top.BX.SidePanel.Instance.postMessage(window, 'mail-mailbox-config-dirs-success', {changed: true});
				}.bind(this),
				function (response)
				{
				}.bind(this)
			);
		},

		close: function ()
		{
			var slider = top.BX.SidePanel.Instance.getSliderByWindow(window);
			if (slider)
			{
				slider.setCacheable(false);
				slider.close();
			}
			else
			{
				window.location.href = this.urlRedirect;
			}
		},

		setUnSelectAllButton: function ()
		{
			if (this.selectAllButton)
			{
				this.selectAllButton.setChecked(false);
			}
		},

		setSelectAllButton: function ()
		{
			if (this.isSelectedAll())
			{
				this.selectAllButton.setChecked(true);
			}
		},

		onChangeSelectAllButton: function (e)
		{
			this.selectAll(e.target.checked);
		},

		selectAll: function (val)
		{
			for (var i in this.items)
			{
				if (!this.items.hasOwnProperty(i))
				{
					continue;
				}

				if (this.items[i].container.offsetHeight == 0)
				{
					continue;
				}

				this.items[i].setCheckboxChecked(val);
			}
		},

		isSelectedAll: function ()
		{
			for (var i in this.items)
			{
				if (!this.items.hasOwnProperty(i))
				{
					continue;
				}

				if (!this.items[i].isSelectedAll())
				{
					return false;
				}
			}

			return true;
		}
	};

	BX.Mail.Client.Config.Dirs.DropdownMenuItem = function (options)
	{
		this.id = options.id;
		this.path = options.path;
		this.container = options.container;
		this.checkbox = options.checkbox;
		this.defaultChecked = options.defaultChecked;
		this.hasChild = options.hasChild;
		this.level = options.level;
		this.subMenuOpen = options.subMenuOpen || false;
		this.subMenu = options.subMenu || null;
		this.parent = options.parent || null;
		this.root = options.root;
		this.levelButton = new BX.Mail.Client.Config.Dirs.LevelButton({
			button: options.levelButton
		});
		this.subItems = {};
		this.selectAllButton = options.hasChild ? new BX.Mail.Client.Config.Dirs.SelectAllButton({
			container: options.container.querySelector(this.root.itemContentSelector),
			className: 'mail-config-dirs-button-select',
			id: options.id,
			updateLabel: true
		}) : null;
		this.childCounter = new BX.Mail.Client.Config.Dirs.ChildCounter({
			container: options.container.querySelector(this.root.childCounterContainerSelector),
			syncCounter: options.container.querySelector(this.root.syncChildCounterSelector),
			totalCounter: options.container.querySelector(this.root.totalChildCounterSelector),
		});

		this.init();
	};

	BX.Mail.Client.Config.Dirs.DropdownMenuItem.prototype = {
		init: function ()
		{
			this.loadData();
			this.addEvents();

			if (!this.hasSyncChildren())
			{
				this.hideSubmenu();
			}
			else
			{
				this.showSubmenu();
			}
		},

		loadData: function ()
		{
			this.loadSubmenu();
			this.loadSubItems();
		},

		reloadData: function (response)
		{
			this.removeSubmenu();
			this.addSubmenu(response.html);
			this.loadData();
			this.showSubmenu();
			this.countParentAllChildCounter();
		},

		loadSubmenu: function ()
		{
			this.subMenuOpen = this.container.querySelectorAll(this.root.itemRowSelector).length > 0;
			this.subMenu = this.container.querySelector(':scope > ' + this.root.subMenuSelector);
		},

		loadSubItems: function ()
		{
			if (!this.subMenu)
			{
				return;
			}

			var list = this.subMenu.querySelectorAll(':scope > ' + this.root.itemRowSelector);

			for (var i = 0; i < list.length; i++)
			{
				var options = {};

				options.container = list[i];
				options.checkbox = options.container.querySelector(this.root.itemContentSelector + ' ' + this.root.checkboxSelector);
				options.id = options.checkbox ? options.checkbox.dataset.id : null;
				options.path = options.checkbox ? options.checkbox.dataset.path : null;
				options.hasChild = options.checkbox ? options.checkbox.dataset.haschild : false;
				options.level = options.checkbox ? parseInt(options.checkbox.dataset.level) : 0;
				options.defaultChecked = options.checkbox ? options.checkbox.checked : null;
				options.root = this.root;
				options.parent = this;
				options.levelButton = options.container.querySelector(this.root.itemContentSelector + ' ' + this.root.levelButtonSelector);

				var item = new BX.Mail.Client.Config.Dirs.DropdownMenuItem(options);

				this.addSubItem(item);
				this.root.addItem(item);
			}
		},

		addEvents: function ()
		{
			this.checkbox.addEventListener('change', this.onChangeCheckbox.bind(this));

			if (this.levelButton && this.levelButton.getButton())
			{
				this.levelButton.getButton().addEventListener('click', this.onClickLevelButton.bind(this));
			}

			if (this.selectAllButton && this.selectAllButton.getButton())
			{
				this.selectAllButton.getButton().addEventListener('click', this.onClickSelectAllButton.bind(this));
			}
		},

		hasSyncChildren: function ()
		{
			if (!this.subMenu)
			{
				return false;
			}

			var items = this.subMenu.querySelectorAll(this.root.itemContentSelector + ' ' + this.root.checkboxSelector);

			for (var i = 0; i < items.length; i++)
			{
				if (items[i].checked)
				{
					return true;
				}
			}

			return false;
		},

		showSubmenu: function ()
		{
			this.subMenuOpen = true;

			if (this.subMenu)
			{
				this.subMenu.style.display = 'block';
			}

			this.updateLevelButton();

			this.updateSelectAllButton(this.isSelectedAll());

			if (this.selectAllButton && this.selectAllButton.label)
			{
				this.selectAllButton.label.style.display = '';
			}
		},

		hideSubmenu: function ()
		{
			this.subMenuOpen = false;

			if (this.subMenu)
			{
				this.subMenu.style.display = 'none';
			}

			this.updateLevelButton();

			this.updateSelectAllButton(this.getCheckboxChecked());

			if (this.selectAllButton && this.selectAllButton.label)
			{
				this.selectAllButton.label.style.setProperty('display', 'none', 'important');
			}
		},

		updateLevelButton: function ()
		{
			if (this.levelButton)
			{
				this.levelButton.update(this.subMenuOpen);
			}
		},

		onClickLevelButton: function (e)
		{
			e.preventDefault();

			this.toggleSubmenu();
		},

		toggleSubmenu: function ()
		{
			if (this.subMenuOpen)
			{
				this.hideSubmenu();

				return;
			}

			if (this.level < this.root.maxLevelDirs)
			{
				if (this.subMenu)
				{
					this.showSubmenu();
				}
				else
				{
					this.getLevelDirs();
				}
			}
		},

		getLevelDirs: function ()
		{
			var item = {};
			item.path = this.path;
			item.dirMd5 = this.id;

			this.levelButton.showLoader();

			BX.ajax.runComponentAction('bitrix:mail.client.config.dirs', 'level', {
				mode: 'class',
				data: {mailboxId: this.root.mailboxId, dir: item}
			}).then(
				function (response)
				{
					this.reloadData(response.data);

					this.levelButton.hideLoader();
				}.bind(this),
				function (response)
				{
					if (response.errors && response.errors.length > 0)
					{
						this.notify(response.errors.map(
							function (item)
							{
								return item.message;
							}
						).join('<br>'), 5000);
					}
					else
					{
						this.notify(BX.message('MAIL_CLIENT_AJAX_ERROR'));
					}

					this.levelButton.hideLoader();
				}.bind(this)
			);
		},

		notify: function (text, delay)
		{
			top.BX.UI.Notification.Center.notify({
				autoHideDelay: delay > 0 ? delay : 2000,
				content: text,
				category: 'mailbox-dirs',
			});
		},

		onChangeCheckbox: function (e)
		{
			var val = e.target.checked;

			this.updateSelectAllButton(val);

			if (!val)
			{
				this.decrementParentSyncChildCounter();
			}
			else
			{
				this.incrementParentSyncChildCounter();
			}
		},

		updateSelectAllButton: function (val)
		{
			if (val)
			{
				this.setParentSelectAllButton();
				this.root.setSelectAllButton();
			}
			else
			{
				this.setParentUnSelectAllButton();
				this.root.setUnSelectAllButton();
			}
		},

		getCheckbox: function ()
		{
			return this.checkbox;
		},

		setCheckboxChecked: function (val)
		{
			if (this.checkbox.checked !== val)
			{
				this.checkbox.click();
			}
		},

		isModified: function ()
		{
			return this.defaultChecked !== this.getCheckboxChecked();
		},

		resetDefaultCheckbox: function ()
		{
			this.defaultChecked = this.getCheckboxChecked();
		},

		getCheckboxChecked: function ()
		{
			return this.checkbox.checked;
		},

		addSubItem: function (item)
		{
			this.subItems[item.id] = item;
		},

		removeSubItems: function ()
		{
			for (var i in this.subItems)
			{
				delete this.subItems[i];
			}
		},

		addSubmenu: function (html)
		{
			if (!html)
			{
				return;
			}

			var menu = document.createElement('div');
			menu.className = this.root.subMenuSelector.replace('.', '');
			menu.innerHTML = html;
			this.container.appendChild(menu);
		},

		removeSubmenu: function ()
		{
			if (this.subMenu && this.subMenu.parentNode)
			{
				this.subMenu.parentNode.removeChild(this.subMenu);
			}

			this.removeSubItems();
		},

		setSelectAllButton: function (val)
		{
			if (!this.selectAllButton)
			{
				return
			}

			this.selectAllButton.setChecked(val);
		},

		setParentSelectAllButton: function ()
		{
			if (this.isSelectedAll())
			{
				this.setSelectAllButton(true);

				if (this.parent)
				{
					this.parent.setParentSelectAllButton();
				}
			}
		},

		setParentUnSelectAllButton: function ()
		{
			this.setSelectAllButton(false);

			if (this.parent)
			{
				this.parent.setParentUnSelectAllButton();
			}
		},

		isSelectedAll: function ()
		{
			if (this.container.offsetHeight == 0)
			{
				return true;
			}

			if (!this.getCheckboxChecked())
			{
				return false;
			}

			for (var i in this.subItems)
			{
				if (!this.subItems[i].isSelectedAll())
				{
					return false;
				}
			}

			return true;
		},

		onClickSelectAllButton: function (e)
		{
			var val = e.target.checked;
			this.selectAll(val);

			if (!val)
			{
				this.setParentUnSelectAllButton();
			}
			else
			{
				this.setParentSelectAllButton();
			}
		},

		selectAll: function (val)
		{
			this.setSelectAllButton(val);
			this.setCheckboxChecked(val);

			for (var i in this.subItems)
			{
				if (!this.subItems.hasOwnProperty(i))
				{
					continue;
				}

				if (this.subItems[i].container.offsetHeight == 0)
				{
					continue;
				}

				this.subItems[i].selectAll(val);
			}
		},

		countParentAllChildCounter: function (calculateDeep)
		{
			if (typeof calculateDeep == 'undefined')
			{
				calculateDeep = true;
			}

			var syncChildCount = this.countSyncChildCounter(calculateDeep);
			var totalChildCount = this.countTotalChildCounter(calculateDeep);

			this.childCounter.setSyncCounter(syncChildCount);
			this.childCounter.setTotalCounter(totalChildCount);

			if (this.parent)
			{
				this.parent.countParentAllChildCounter(false);
			}
		},

		countSyncChildCounter: function (calculateDeep)
		{
			var count = 0;

			for (var i in this.subItems)
			{
				if (!this.subItems.hasOwnProperty(i))
				{
					continue;
				}

				var syncCount = this.subItems[i].getCheckboxChecked() ? 1 : 0;

				count += this.subItems[i].childCounter.getSyncCounter() + syncCount;

				if (calculateDeep)
				{
					count += this.subItems[i].countSyncChildCounter(calculateDeep);
				}
			}

			return count;
		},

		countTotalChildCounter: function (calculateDeep)
		{
			var count = 0;

			for (var i in this.subItems)
			{
				if (!this.subItems.hasOwnProperty(i))
				{
					continue;
				}

				count += this.subItems[i].childCounter.getTotalCounter() + 1;

				if (calculateDeep)
				{
					count += this.subItems[i].countTotalChildCounter(calculateDeep);
				}
			}

			return count;
		},

		decrementParentSyncChildCounter: function (update)
		{
			if (typeof update == 'undefined')
			{
				update = false;
			}

			if (update)
			{
				this.childCounter.decrementSyncCounter();
			}

			if (this.parent)
			{
				this.parent.decrementParentSyncChildCounter(true);
			}
		},

		incrementParentSyncChildCounter: function (update)
		{
			if (typeof update == 'undefined')
			{
				update = false;
			}

			if (update)
			{
				this.childCounter.incrementSyncCounter();
			}

			if (this.parent)
			{
				this.parent.incrementParentSyncChildCounter(true);
			}
		},
	};

	BX.Mail.Client.Config.Dirs.DirsTypes = function (options)
	{
		this.items = options.items;
		this.dirs = options.dirs;
		this.itemsTypes = {};
		this.menuDropdown = null;
		this.cache = {};
		this.mailboxId = options.mailboxId;
		this.dataId = null;
		this.dataType = null;

		this.init();
	};

	BX.Mail.Client.Config.Dirs.DirsTypes.prototype = {
		init: function ()
		{
			this.addEvents();
		},

		addEvents: function ()
		{
			for (var i = 0; i < this.items.length; i++)
			{
				this.items[i].addEventListener('click', this.onClickMenuDropdown.bind(this));
			}

			BX.Event.EventEmitter.subscribe('BX.Main.Menu.Item:onmouseenter', function (event)
			{
				var menuItem = event.target;

				if (!menuItem.dataset || !menuItem.getMenuWindow())
				{
					return;
				}

				var menuWindow = menuItem.getMenuWindow();
				var subMenuItems = menuWindow.getMenuItems();

				var path = menuItem.dataset.path;
				var hash = menuItem.dataset.dirMd5;
				var hasChild = menuItem.dataset.hasChild;

				if (!hasChild)
				{
					return;
				}

				for (var i = 0; i < subMenuItems.length; i++)
				{
					var item = subMenuItems[i];

					if (item.getId() === path)
					{
						var hasSubMenu = item.hasSubMenu();

						if (hasSubMenu)
						{
							item.showSubMenu();
							var subMenu = item.getSubMenu();

							if (subMenu)
							{
								var items = subMenu.getMenuItems();
								var hasLoadingItem = false;

								for (var k = 0; k < items.length; k++)
								{
									var subItem = items[k];

									if (subItem.getId() === 'loading')
									{
										hasLoadingItem = true;
									}
								}
							}

							if (!hasLoadingItem)
							{
								return;
							}
						}

						this.loadLevelMenu(item, hash)
					}
				}
			}.bind(this));
		},

		loadLevelMenu: function (menuItem, hash)
		{
			var menu = this.getCache(menuItem.getId());
			var popup = BX.Main.PopupManager.getPopupById('menu-popup-popup-submenu-' + menuItem.getId());

			if (popup)
			{
				popup.destroy();
			}

			if (menu)
			{
				menuItem.destroySubMenu();
				menuItem.addSubMenu(menu);
				menuItem.showSubMenu();
				return;
			}

			var subItem = {
				'id': 'loading',
				'text': BX.message('MAIL_CLIENT_BUTTON_LOADING'),
				'disabled': true
			};

			menuItem.destroySubMenu();
			menuItem.addSubMenu([subItem]);
			menuItem.showSubMenu();

			BX.ajax.runComponentAction('bitrix:mail.client.config.dirs', 'level', {
				mode: 'class',
				data: {mailboxId: this.mailboxId, dir: {path: menuItem.getId(), dirMd5: hash}}
			}).then(
				function (response)
				{
					var dirs = response.data.dirs;
					var data = this.getDataDropdownMenu(dirs);

					this.setCache(menuItem.getId(), data);

					var popup = BX.Main.PopupManager.getPopupById('popup-submenu-' + menuItem.getId());
					var isShown = menuItem.getMenuWindow().getPopupWindow().isShown();

					if (popup)
					{
						popup.destroy();
					}

					if (isShown)
					{
						menuItem.destroySubMenu();
						menuItem.addSubMenu(data);
						menuItem.showSubMenu();
					}
				}.bind(this),
				function (response)
				{
				}.bind(this)
			);
		},

		getCache: function (key)
		{
			if (!key)
			{
				return;
			}

			return this.cache[key] ? this.cache[key] : null;
		},
		setCache: function (key, value)
		{
			return this.cache[key] = value;
		},

		getItemsTypes: function ()
		{
			return this.itemsTypes;
		},

		setItemsTypes: function (item)
		{
			this.itemsTypes[this.dataType] = {
				path: item.dataset.path,
				dirMd5: item.dataset.dirMd5
			};
		},

		onClickMenuDropdown: function (e)
		{
			e.preventDefault();

			this.dataId = e.currentTarget.getAttribute('data-id');
			this.dataType = e.currentTarget.getAttribute('data-type');

			var data = this.getDataDropdownMenu(this.dirs);

			this.showMenuDropdown(data, e.target);
		},

		getDataDropdownMenu: function (dirs)
		{
			var list = [];

			for (var i = 0; i < dirs.length; i++)
			{
				var hasChild = /(HasChildren)/i.test(dirs[i].FLAGS);
				var item = {};
				item.id = dirs[i].PATH;
				item.text = dirs[i].NAME;
				item.title = dirs[i].FORMATTED_NAME;
				item.className = dirs[i].IS_DISABLED ? 'menu-popup-no-icon mail-dirs-menu-disabled' : '';
				item.dataset = {
					'path': dirs[i].PATH,
					'dirMd5': dirs[i].DIR_MD5,
					'isDisabled': dirs[i].IS_DISABLED,
					'hasChild': hasChild
				};
				item.items = dirs[i].CHILDREN && dirs[i].CHILDREN.length ? this.getDataDropdownMenu(dirs[i].CHILDREN) : (
					hasChild ? [{
						'id': 'loading',
						'text': BX.message('MAIL_CLIENT_BUTTON_LOADING'),
						'disabled': true
					}] : []
				);
				item.onclick = this.onClickMenuDropdownItem.bind(this);

				list.push(item);
			}

			return list;
		},

		onClickMenuDropdownItem: function (e, item)
		{
			if (item.dataset.isDisabled)
			{
				e.preventDefault();
				return;
			}

			this.setItemsTypes(item);

			var element = document.querySelector('[data-id="' + this.dataId + '"]');

			if (element)
			{
				element.innerHTML = item.title;
			}

			this.closeMenuDropdown();
		},

		showMenuDropdown: function (data, node)
		{
			this.menuDropdown = BX.Main.MenuManager.create({
				id: 'mail-client-config-dirs-dropdown-menu',
				autoHide: true,
				closeByEsc: true,
				items: data,
				zIndex: 7001,
				maxHeight: 400,
				maxWidth: 200,
				events: {
					onPopupClose: function ()
					{
						this.removeMenuDropdown();
					}.bind(this)
				}
			});
			this.menuDropdown.popupWindow.setBindElement(node);
			this.menuDropdown.show();
		},

		closeMenuDropdown: function ()
		{
			if (this.menuDropdown)
			{
				this.menuDropdown.close();
			}
		},

		removeMenuDropdown: function ()
		{
			if (this.menuDropdown)
			{
				BX.Main.MenuManager.destroy(this.menuDropdown.id);
			}
		},
	};

	BX.Mail.Client.Config.Dirs.LevelButton = function (options)
	{
		this.button = options.button;
	};

	BX.Mail.Client.Config.Dirs.LevelButton.prototype = {
		update: function (isOpen)
		{
			if (!this.button)
			{
				return;
			}

			if (isOpen)
			{
				this.button.classList.remove('mail-config-dirs-plus-icon');
				this.button.classList.add('mail-config-dirs-minus-icon');
			}
			else
			{
				this.button.classList.remove('mail-config-dirs-minus-icon');
				this.button.classList.add('mail-config-dirs-plus-icon');
			}
		},

		getButton: function ()
		{
			return this.button;
		},

		showLoader: function ()
		{
			if (!this.button)
			{
				return;
			}

			this.button.classList.add('mail-config-dirs-loader-icon');
		},

		hideLoader: function ()
		{
			if (!this.button)
			{
				return;
			}

			this.button.classList.remove('mail-config-dirs-loader-icon');
		}
	};

	BX.Mail.Client.Config.Dirs.SelectAllButton = function (options)
	{
		this.container = options.container;
		this.className = options.className;
		this.id = 'select-all-button-' + options.id;
		this.type = options.type;
		this.updateLabel = options.updateLabel;
		this.button = null;
		this.label = null;

		this.init();
	};

	BX.Mail.Client.Config.Dirs.SelectAllButton.prototype = {
		init: function ()
		{
			this.addButton();
		},

		addButton: function ()
		{
			if (!this.container)
			{
				return;
			}

			var div = document.createElement('div');
			div.className = this.className;

			this.button = document.createElement('input');
			this.button.type = 'checkbox';
			this.button.id = this.id;
			this.button.className = this.className + '-input';

			this.label = document.createElement('label');
			this.label.htmlFor = this.id;
			this.label.className = this.className + '-label';
			this.label.textContent = BX.message('MAIL_CLIENT_CONFIG_DIRS_BTN_SELECT_ALL');

			div.appendChild(this.button);
			div.appendChild(this.label);

			if (this.type === 'prepend')
			{
				this.container.prepend(div);
			}
			else
			{
				this.container.appendChild(div);
			}
		},

		update: function ()
		{
			if (!this.updateLabel || !this.label)
			{
				return;
			}

			if (!this.button.checked)
			{
				this.label.textContent = BX.message('MAIL_CLIENT_CONFIG_DIRS_BTN_SELECT_ALL');
			}
			else
			{
				this.label.textContent = BX.message('MAIL_CLIENT_CONFIG_DIRS_BTN_CANCEL_ALL');
			}
		},

		getButton: function ()
		{
			return this.button;
		},

		setChecked: function (val)
		{
			if (!this.button)
			{
				return;
			}

			this.button.checked = val;
			this.update();
		}
	};

	BX.Mail.Client.Config.Dirs.ChildCounter = function (options)
	{
		this.container = options.container;
		this.syncCounter = options.syncCounter;
		this.totalCounter = options.totalCounter;

		this.init();
	};

	BX.Mail.Client.Config.Dirs.ChildCounter.prototype = {
		init: function ()
		{
		},

		decrementSyncCounter: function ()
		{
			var val = this.getSyncCounter();

			this.setSyncCounter(val - 1);

			this.toggleCounter();
		},

		incrementSyncCounter: function ()
		{
			var val = this.getSyncCounter();

			this.setSyncCounter(val + 1);

			this.toggleCounter();
		},

		setSyncCounter: function (val)
		{
			if (!this.syncCounter)
			{
				return
			}

			this.syncCounter.textContent = val > 0 ? val : 0;
		},

		getSyncCounter: function ()
		{
			if (!this.syncCounter)
			{
				return 0;
			}

			return parseInt(this.syncCounter.textContent);
		},

		setTotalCounter: function (val)
		{
			if (!this.totalCounter)
			{
				return
			}

			this.totalCounter.textContent = val > 0 ? val : 0;
		},

		getTotalCounter: function ()
		{
			if (!this.totalCounter)
			{
				return
			}

			return parseInt(this.totalCounter.textContent);
		},

		toggleCounter: function ()
		{
			var val = this.getSyncCounter();

			if (val > 0)
			{
				this.showCounter();
			}
			else
			{
				this.hideCounter();
			}
		},

		showCounter: function ()
		{
			if (!this.container)
			{
				return;
			}

			this.container.classList.add('show');
		},

		hideCounter: function ()
		{
			if (!this.container)
			{
				return;
			}

			this.container.classList.remove('show');
		},
	}
})();
