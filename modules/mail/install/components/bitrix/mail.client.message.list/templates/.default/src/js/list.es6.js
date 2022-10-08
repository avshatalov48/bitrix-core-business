import { EventEmitter } from "main.core.events";
import { Loc } from 'main.core';

export class List
{
	constructor(options)
	{
		this.mailReadAllButton = options.mailReadAllButton;
		this.gridId = options.gridId;
		this.mailboxId = options.mailboxId;
		this.canMarkSpam = options.canMarkSpam;
		this.canDelete = options.canDelete;
		this.ERROR_CODE_CAN_NOT_DELETE = options.ERROR_CODE_CAN_NOT_DELETE;
		this.ERROR_CODE_CAN_NOT_MARK_SPAM = options.ERROR_CODE_CAN_NOT_MARK_SPAM;
		this.disabledClassName = 'js-disabled';
		this.userInterfaceManager = new BX.Mail.Client.Message.List.UserInterfaceManager(options);
		this.userInterfaceManager.resetGridSelection = this.resetGridSelection.bind(this);
		this.userInterfaceManager.isSelectedRowsHaveClass = this.isSelectedRowsHaveClass.bind(this);
		this.userInterfaceManager.getGridInstance = this.getGridInstance.bind(this);
		this.userInterfaceManager.updateCountersFromBackend = this.updateCountersFromBackend.bind(this);
		this.cache = {};
		this.addEventHandlers();

		BX.Mail.Client.Message.List[options.id] = this;
	};

	addEventHandlers()
	{
		// todo delete this hack
		// it is here to prevent grid's title changing after filter apply
		BX.ajax.UpdatePageData = (function() {
		});

		EventEmitter.subscribe(
			'onSubMenuShow',
			function(event){
				const menuItem = event.target;
				const container = menuItem.getMenuWindow().getPopupWindow().getPopupContainer();
				let id = null;

				if (container)
				{
					id = BX.data(container, 'grid-row-id');
				}

				BX.data(
					menuItem.getSubMenu().getPopupWindow().getPopupContainer(),
					'grid-row-id',
					menuItem.gridRowId || id,
				);
			},
		);

		EventEmitter.subscribe('Mail::directoryChanged', () =>
		{
			this.resetGridSelection();
		})

		EventEmitter.subscribe('BX.Mail.Home:updatingCounters', (event) => {
			if(event['data']['name'] !== 'mailboxCounters')
			{
				const counters = event['data']['counters'];
				BX.Mail.Home.LeftMenuNode.directoryMenu.setCounters(counters);

				BX.Mail.Home.mailboxCounters.setCounters([
					{
						path: 'unseenCountInCurrentMailbox',
						count: BX.Mail.Home.Counters.getTotalCounter(),
					},
				]);
			}
			else
			{
				this.userInterfaceManager.updateLeftMenuCounter();
			}
		});

		EventEmitter.subscribe('BX.Main.Menu.Item:onmouseenter', function(event) {
			const menuItem = event.target;

			if (!menuItem.dataset || !menuItem.getMenuWindow())
			{
				return;
			}

			const menuWindow = menuItem.getMenuWindow();
			const subMenuItems = menuWindow.getMenuItems();

			const path = menuItem.dataset.path;
			const hash = menuItem.dataset.dirMd5;
			const hasChild = menuItem.dataset.hasChild;

			if (!hasChild)
			{
				return;
			}

			for (let i = 0; i < subMenuItems.length; i++)
			{
				const item = subMenuItems[i];

				if (item.getId() === path)
				{
					const hasSubMenu = item.hasSubMenu();

					if (hasSubMenu)
					{
						item.showSubMenu();
						const subMenu = item.getSubMenu();

						let hasLoadingItem = false;

						if (subMenu)
						{
							const items = subMenu.getMenuItems();

							for (let k = 0; k < items.length; k++)
							{
								const subItem = items[k];

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

					this.loadLevelMenu(item, hash);
				}
			}
		}.bind(this));

		const itemsMenu = document.querySelectorAll('.ical-event-control-menu');

		for (let i = 0; i < itemsMenu.length; i++)
		{
			itemsMenu[i].addEventListener('click', this.showICalMenuDropdown.bind(this));
		}

		BX.bindDelegate(document.body, 'click', { className: 'ical-event-control-button' }, this.onClickICalButton.bind(this));
	}

	loadLevelMenu(menuItem, hash)
	{
		const menu = this.getCache(menuItem.getId());
		const popup = BX.Main.PopupManager.getPopupById('menu-popup-popup-submenu-' + menuItem.getId());

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

		const subItem = {
			'id': 'loading',
			'text': Loc.getMessage('MAIL_CLIENT_BUTTON_LOADING'),
			'disabled': true,
		};

		menuItem.destroySubMenu();
		menuItem.addSubMenu([subItem]);
		menuItem.showSubMenu();

		BX.ajax.runComponentAction('bitrix:mail.client.config.dirs', 'level', {
			mode: 'class',
			data: { mailboxId: this.mailboxId, dir: { path: menuItem.getId(), dirMd5: hash } },
		}).then(
			function(response) {
				const dirs = response.data.dirs;
				const items = [];

				for (let i = 0; i < dirs.length; i++)
				{
					const hasChild = /(HasChildren)/i.test(dirs[i].FLAGS);
					const item = {
						'id': dirs[i].PATH,
						'text': dirs[i].NAME,
						'dataset': {
							'path': dirs[i].PATH,
							'dirMd5': dirs[i].DIR_MD5,
							'isDisabled': dirs[i].IS_DISABLED,
							'hasChild': hasChild,
						},
						items: hasChild ? [{
							id: 'loading',
							'text': Loc.getMessage('MAIL_CLIENT_BUTTON_LOADING'),
							'disabled': true,
						}] : [],
					};

					items.push(item);
				}

				this.setCache(menuItem.getId(), items);

				const popup = BX.Main.PopupManager.getPopupById('menu-popup-popup-submenu-' + menuItem.getId());
				const isShown = menuItem.getMenuWindow().getPopupWindow().isShown();

				if (popup)
				{
					popup.destroy();
				}

				if (isShown)
				{
					menuItem.destroySubMenu();
					menuItem.addSubMenu(items);
					menuItem.showSubMenu();
				}
			}.bind(this),
			function(response) {
			}.bind(this),
		);
	}

	onCrmClick(id)
	{
		const selected = this.getGridInstance().getRows().getSelected();
		const row = id ? this.getGridInstance().getRows().getById(id) : selected[0];
		if (!(row && row.node))
		{
			return;
		}
		const addToCrm = this.userInterfaceManager.isAddToCrmActionAvailable(row.node);
		const messageIdNode = row.node.querySelector('[data-message-id]');
		if (!(messageIdNode.dataset && messageIdNode.dataset.messageId))
		{
			return;
		}

		if(id === undefined)
		{
			this.resetGridSelection();
		}

		if (addToCrm)
		{
			const crmBtnInRow = row.node.querySelector('.mail-binding-crm.mail-ui-not-active');

			if(crmBtnInRow)
			{
				crmBtnInRow.startWait();
			}

			if (typeof this.isAddingToCrmInProgress !== "object")
			{
				this.isAddingToCrmInProgress = {};
			}
			if (this.isAddingToCrmInProgress[id] === true)
			{
				return;
			}
			this.isAddingToCrmInProgress[id] = true;

			BX.ajax.runComponentAction(
				'bitrix:mail.client',
				'createCrmActivity',
				{
					mode: 'ajax',
					data: {
						messageId: messageIdNode.dataset.messageId,
					},
					analyticsLabel: {
						'groupCount': selected.length,
						'bindings': this.getRowsBindings([row]),
					},
				},
			).then(
				function(id) {
					this.isAddingToCrmInProgress[id] = false;
					this.notify(Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM'));
				}.bind(this, id),
				function(json) {

					if(crmBtnInRow)
					{
						crmBtnInRow.stopWait();
					}

					this.isAddingToCrmInProgress[id] = false;
					if (json.errors && json.errors.length > 0)
					{
						this.notify(json.errors.map(
							function(item) {
								return item.message;
							},
						).join('<br>'), 5000);
					}
					else
					{
						this.notify(Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADD_TO_CRM_ERROR'));
					}
				}.bind(this),
			);
		}
		else
		{
			this.userInterfaceManager.onCrmBindingDeleted(messageIdNode.dataset.messageId);
			BX.ajax.runComponentAction(
				'bitrix:mail.client',
				'removeCrmActivity',
				{
					mode: 'ajax',
					data: {
						messageId: messageIdNode.dataset.messageId,
					},
					analyticsLabel: {
						'groupCount': selected.length,
						'bindings': this.getRowsBindings([row]),
					},
				},
			).then(function(messageIdNode) {
				this.notify(Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM'));
			}.bind(this, messageIdNode));
		}

		let selectedIds = this.getGridInstance().getRows().getSelectedIds();
		if(selectedIds.length === 1 && selectedIds[0]===id)
		{
			this.resetGridSelection();
		}
	}

	onViewClick(id)
	{
		if (id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0)
		{
			return;
		}
		// @TODO: path
		BX.SidePanel.Instance.open("/mail/message/" + id, {
			width: 1080,
			loader: 'view-mail-loader',
		});
	}

	onDeleteImmediately(id)
	{
		let additionalOptions =
		{
			'deleteImmediately' : true,
		};
		this.onDeleteClick(id,additionalOptions);
	}

	onDeleteClick(id,additionalOptions)
	{
		const selected = this.getGridInstance().getRows().getSelected();
		if (id === undefined && selected.length === 0)
		{
			return;
		}
		if (!this.canDelete)
		{
			this.showDirsSlider();
			return;
		}
		let options = {
			params: (additionalOptions !== undefined) ? additionalOptions : {},
			keepRows: true,
			analyticsLabel: {
				'groupCount': selected.length,
				'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected),
			},
		};
		let selectedIds;

		if(id === undefined)
		{
			selectedIds = BX.Mail.Home.Grid.getSelectedIds();
		}
		else
		{
			selectedIds = [id];
		}

		selectedIds = this.filterRowsByClassName(this.disabledClassName, selectedIds, true);
		options.ids = selectedIds;

		if (this.userInterfaceManager.isCurrentFolderTrash || (additionalOptions !== undefined && additionalOptions['deleteImmediately']) )
		{
			const confirmPopup = this.getConfirmDeletePopup(options);
			confirmPopup.show();
		}
		else
		{
			BX.Mail.Home.Grid.hideRowByIds(selectedIds);

			const unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', selectedIds).length;

			if(this.getCurrentFolder() !== '')
			{
				BX.Mail.Home.Counters.updateCounters([
					{
						name: this.getCurrentFolder(),
						lower: true,
						count: unseenRowsIdsCount,
					},
				]);
			}

			this.runAction('delete', options,() =>
				BX.Mail.Home.Grid.reloadTable()
			);
			if(id === undefined)
			{
				this.resetGridSelection();
			}
		}
	}

	onMoveToFolderClick(event)
	{
		const folderOptions = event.currentTarget.dataset;
		const toFolderByPath = folderOptions.path;
		const toFolderByName = toFolderByPath;

		if(toFolderByPath === this.getCurrentFolder())
		{
			this.notify(Loc.getMessage('MESSAGES_ALREADY_EXIST_IN_FOLDER'));
			return;
		}

		let id = undefined;
		const popupSubmenu = BX.findParent(event.currentTarget, { className: 'popup-window' });
		if (popupSubmenu)
		{
			id = BX.data(popupSubmenu, 'grid-row-id');
		}
		const isDisabled = JSON.parse(folderOptions.isDisabled);

		if ((id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0) || isDisabled)
		{
			return;
		}
		let selected = this.getGridInstance().getRows().getSelected();
		let idsForMoving = (id ? [id] : this.getGridInstance().getRows().getSelectedIds());
		idsForMoving = this.filterRowsByClassName(this.disabledClassName, idsForMoving, true);
		if (!idsForMoving.length)
		{
			return;
		}

		// to hide the context menu
		BX.onCustomEvent('Grid::updated');

		let selectedIds;

		if(id === undefined)
		{
			selectedIds = BX.Mail.Home.Grid.getSelectedIds();
		}
		else
		{
			selectedIds = [id];
		}

		BX.Mail.Home.Grid.hideRowByIds(selectedIds);

		const unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', selectedIds).length;

		if(this.getCurrentFolder() !== '')
		{
			BX.Mail.Home.Counters.updateCounters([
				{
					name:toFolderByName,
					increase: true,
					count: unseenRowsIdsCount,
				},
				{
					name: this.getCurrentFolder(),
					lower: true,
					count: unseenRowsIdsCount,
				},
			]);
		}

		this.runAction(
			'moveToFolder',
			{
				keepRows: true,
				ids: idsForMoving,
				params: {
					folder: toFolderByPath,
				},
				analyticsLabel: {
					'groupCount': selected.length,
					'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected),
				},
			},
			()=> {
				BX.Mail.Home.Grid.reloadTable();
			},
		);

		if(id === undefined)
		{
			this.resetGridSelection();
		}
	}

	onReadClick(id)
	{
		let selected = [];
		let resultIds = [];

		if(id === undefined)
		{
			selected = this.getGridInstance().getRows().getSelected();
			resultIds = this.getGridInstance().getRows().getSelectedIds();
		}
		else
		{
			let selectedIds = this.getGridInstance().getRows().getSelectedIds();
			if(selectedIds.length === 1 && selectedIds[0]===id)
			{
				/*if the action is non-group, but one cell is selected,
				then the action was performed through the "Action panel"
				and the selection should be reset*/
				selected = this.getGridInstance().getRows().getSelected();
				resultIds = selectedIds;
				id = undefined;
			}
			else
			{
				resultIds = [id];
			}

		}
		if (id === undefined && selected.length === 0)
		{
			return;
		}
		const actionName = 'all' == id || this.isSelectedRowsHaveClass('mail-msg-list-cell-unseen', id) ? 'markAsSeen' : 'markAsUnseen';

		resultIds = this.filterRowsByClassName('mail-msg-list-cell-unseen', resultIds, actionName !== 'markAsSeen');
		resultIds = this.filterRowsByClassName(this.disabledClassName, resultIds, true);

		if (!resultIds.length)
		{
			return;
		}

		const handler = function() {
			this.userInterfaceManager.onMessagesRead(resultIds, { action: actionName });
			const currentFolder = this.getCurrentFolder();

			const oldMessagesCount = actionName !== 'markAsSeen'? this.isSelectedRowsHaveClass('mail-msg-list-cell-old') : 0;
			let countMessages = resultIds.length - oldMessagesCount;

			if(this.getCurrentFolder() !== '')
			{
				if (actionName === 'markAsSeen')
				{
					if ('all' === id)
					{
						countMessages = BX.Mail.Home.Counters.getCounter(currentFolder) - oldMessagesCount;
					}

					BX.Mail.Home.Counters.updateCounters([
						{
							name: currentFolder,
							lower: true,
							count: countMessages,
						},
					]);
				}
				else
				{
					BX.Mail.Home.Counters.updateCounters([
						{
							name: currentFolder,
							increase: true,
							count: countMessages,
						},
					]);
				}
			}

			if(id === undefined) {
				this.resetGridSelection();
			}

			if ('all' == id)
			{
				resultIds['for_all'] = this.mailboxId + '-' + this.userInterfaceManager.getCurrentFolder();
			}

			this.userInterfaceManager.updateUnreadCounters();

			this.runAction(actionName, {
				ids: resultIds,
				keepRows: true,
				successParams: actionName,
				analyticsLabel: {
					'groupCount': selected.length,
					'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected),
				},
				onSuccess: function(){
					this.updateCountersFromBackend();
				}.bind(this),
			});

			return true;
		};
		handler.apply(this);
	}

	onSpamClick(id)
	{
		const selected = this.getGridInstance().getRows().getSelected();
		if (id === undefined && selected.length === 0)
		{
			return;
		}
		if (!this.canMarkSpam)
		{
			this.showDirsSlider();
			return;
		}
		const actionName = this.isSelectedRowsHaveClass('js-spam', id) ? 'restoreFromSpam' : 'markAsSpam';
		let resultIds = this.filterRowsByClassName('js-spam', id, actionName !== 'restoreFromSpam');
		resultIds = this.filterRowsByClassName(this.disabledClassName, resultIds, true);
		if (!resultIds.length)
		{
			return;
		}
		const options = {
			keepRows: true,
			analyticsLabel: {
				'groupCount': selected.length,
				'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected),
			},
		};

		let selectedIds;

		if(id === undefined)
		{
			selectedIds = BX.Mail.Home.Grid.getSelectedIds();
		}
		else
		{
			selectedIds = [id];
		}

		options.ids = selectedIds;

		BX.Mail.Home.Grid.hideRowByIds(selectedIds);

		const unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', selectedIds).length;

		if(this.getCurrentFolder() !== '') {
			if (actionName === 'markAsSpam') {
				BX.Mail.Home.Counters.updateCounters([
					{
						name: this.userInterfaceManager.spamDir,
						increase: true,
						count: unseenRowsIdsCount,
					},
					{
						name: this.getCurrentFolder(),
						lower: true,
						count: unseenRowsIdsCount,
					},
				]);
			} else {
				BX.Mail.Home.Counters.updateCounters([
					{
						name: this.userInterfaceManager.spamDir,
						lower: true,
						count: unseenRowsIdsCount,
					},
					{
						name: this.userInterfaceManager.inboxDir,
						increase: true,
						count: unseenRowsIdsCount,
					},
				]);
			}
		}

		this.runAction(actionName, options,() =>
			BX.Mail.Home.Grid.reloadTable()
		);
		if(id === undefined)
		{
			this.resetGridSelection();
		}
	}

	getConfirmDeletePopup(options)
	{
		return new BX.UI.Dialogs.MessageBox({
			title: Loc.getMessage('MAIL_MESSAGE_LIST_CONFIRM_TITLE'),
			message: Loc.getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE'),
			buttons: [
				new BX.UI.Button({
					color: BX.UI.Button.Color.DANGER,
					text: Loc.getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN'),
					onclick: (function(button) {

						const unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', options.ids).length;

						BX.Mail.Home.Counters.updateCounters([
							{
								name: this.getCurrentFolder(),
								lower: true,
								count: unseenRowsIdsCount,
							},
						]);

						this.runAction('delete', options,() =>
							BX.Mail.Home.Grid.reloadTable()
						);
						button.getContext().close();
						BX.Mail.Home.Grid.hideRowByIds(options.ids);
					}).bind(this),
				}),
				new BX.UI.CancelButton({
					onclick: function(button) {
						button.getContext().close();
					},
				}),
			],
		});
	}

	resetGridSelection()
	{
		BX.onCustomEvent('Mail::resetGridSelection');
		this.getGridInstance().getRows().unselectAll();
		this.getGridInstance().adjustCheckAllCheckboxes();
		BX.Mail.Home.Grid.hidePanel();
	}

	isSelectedRowsHaveClass(className, id)
	{
		let selectedIds;
		if(id === undefined)
		{
			selectedIds = this.getGridInstance().getRows().getSelectedIds();
		}
		else
		{
			selectedIds = [id];
		}
		const ids = selectedIds.length ? selectedIds : (id ? [id] : []);

		let selectedLinesWithClassNumber = 0;

		for (let i = 0; i < ids.length; i++)
		{
			const row = this.getGridInstance().getRows().getById(ids[i]);
			if (row && row.node)
			{
				const columns = row.node.getElementsByClassName(className);
				if (columns && columns.length)
				{
					selectedLinesWithClassNumber++;
				}
			}
		}
		return selectedLinesWithClassNumber;
	}

	filterRowsByClassName(className, ids, isReversed)
	{
		let resIds = [];
		if ('all' == ids)
		{
			resIds = this.getGridInstance().getRows().getBodyChild().map(
				function(current) {
					return current.getId();
				},
			);
		}
		else if (Array.isArray(ids))
		{
			resIds = ids;
		}
		else
		{
			const selectedIds = this.getGridInstance().getRows().getSelectedIds();
			resIds = selectedIds.length ? selectedIds : (ids ? [ids] : []);
		}
		const resultIds = [];
		for (let i = resIds.length - 1; i >= 0; i--)
		{
			const row = this.getGridInstance().getRows().getById(resIds[i]);
			if (row && row.node)
			{
				const columns = row.node.getElementsByClassName(className);
				if (!isReversed && (columns && columns.length))
				{
					resultIds.push(resIds[i]);
				}
				else if (isReversed && !(columns && columns.length))
				{
					resultIds.push(resIds[i]);
				}
			}
		}
		return resultIds;
	}

	notify(text, delay)
	{
		top.BX.UI.Notification.Center.notify({
			autoHideDelay: delay > 0 ? delay : 2000,
			content: text ? text : Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_SUCCESS'),
		});
	}

	updateCountersFromBackend()
	{
		if(this.getCurrentFolder() === '')
		{
			BX.ajax.runComponentAction('bitrix:mail.client.message.list', 'getDirsWithUnseenMailCounters', {
				mode: 'class',
				data: {
					mailboxId: this.mailboxId
				},
			}).then(
				function(response) {
					BX.Mail.Home.Counters.setCounters(response.data);
				}
			);
		}
	}

	runAction(actionName, options, actionOnSuccess)
	{
		options = options ? options : {};

		let selectedIds = [];

		if (options.ids)
		{
			selectedIds = options.ids;
		}
		if (!selectedIds.length && !selectedIds.for_all)
		{
			return;
		}
		if (!options.keepRows)
		{
			this.getGridInstance().tableFade();
		}
		const data = { ids: selectedIds };
		if (options.params)
		{
			const optionsKeys = Object.keys(Object(options.params));
			for (let nextIndex = 0, len = optionsKeys.length; nextIndex < len; nextIndex++)
			{
				const nextKey = optionsKeys[nextIndex];
				const desc = Object.getOwnPropertyDescriptor(options.params, nextKey);
				if (desc !== undefined && desc.enumerable)
				{
					data[nextKey] = options.params[nextKey];
				}
			}
		}

		BX.ajax.runComponentAction('bitrix:mail.client', actionName, {
			mode: 'ajax',
			data: data,
			analyticsLabel: options.analyticsLabel,
		}).then(
			function() {
				if (options.onSuccess === false)
				{
					return;
				}

				this.updateCountersFromBackend();

				if (options.onSuccess && typeof (options.onSuccess) === "function")
				{
					options.onSuccess.bind(this, selectedIds, options.successParams)();
					return;
				}
				if(actionOnSuccess === undefined)
				{
					this.notify();
				}
				else
				{
					actionOnSuccess();
				}
			}.bind(this),
			function(response) {
				BX.Mail.Home.Counters.restoreFromCache();
				BX.Mail.Home.Grid.reloadTable();
				options.onError && typeof (options.onError) === "function" ?
					options.onError().bind(this, response) :
					this.onErrorRequest(response);
			}.bind(this),
		);
	}

	onErrorRequest(response)
	{
		let options = {};
		this.checkErrorRights(response.errors);
		options.errorMessage = response.errors[0].message;
		this.notify(options.errorMessage);
	}

	checkErrorRights(errors)
	{
		for (let i = 0; i < errors.length; i++)
		{
			if (errors[i].code === this.ERROR_CODE_CAN_NOT_DELETE)
			{
				this.canDelete = false;
			}
			if (errors[i].code === this.ERROR_CODE_CAN_NOT_MARK_SPAM)
			{
				this.canMarkSpam = false;
			}
		}
	}

	showDirsSlider()
	{
		const url = BX.util.add_url_param("/mail/config/dirs", {
			mailboxId: this.mailboxId,
		});
		BX.SidePanel.Instance.open(url, {
			width: 640,
			cacheable: false,
			allowChangeHistory: false,
		});
		this.canDelete = true;
		this.canMarkSpam = true;
	}

	onDisabledGroupActionClick()
	{
	}

	getCurrentFolder()
	{
		return this.userInterfaceManager.getCurrentFolder();
	}

	getGridInstance()
	{
		return BX.Main.gridManager.getById(this.gridId).instance;
	}

	getRowsBindings(rows)
	{
		return BX.util.array_unique(Array.prototype.concat.apply(
			[],
			rows.map(
				function(row) {
					if (!row || !row.node)
					{
						return null;
					}

					return Array.prototype.map.call(
						row.node.querySelectorAll('[class^="js-bind-"] [data-type]'),
						function(node) {
							return node.dataset.type;
						},
					);
				},
			),
		));
	}

	getCache(key)
	{
		if (!key)
		{
			return;
		}

		return this.cache[key] ? this.cache[key] : null;
	}

	setCache(key, value)
	{
		return this.cache[key] = value;
	}

	showICalMenuDropdown(event)
	{
		event.stopPropagation();
		event.preventDefault();

		const menu = event.currentTarget.dataset.menu;

		if (!menu)
		{
			return;
		}

		this.iCalMenuDropdown = BX.Main.MenuManager.create({
			id: 'mail-client-message-list-ical-dropdown-menu',
			autoHide: true,
			closeByEsc: true,
			items: JSON.parse(menu),
			zIndex: 7001,
			maxHeight: 400,
			maxWidth: 200,
			angle: {
				position: "top",
				offset: 40,
			},
			events: {
				onPopupClose: function() {
					this.removeICalMenuDropdown();
				}.bind(this),
			},
		});
		this.iCalMenuDropdown.popupWindow.setBindElement(event.currentTarget);
		this.iCalMenuDropdown.show();
	}

	removeICalMenuDropdown()
	{
		if (this.iCalMenuDropdown)
		{
			BX.Main.MenuManager.destroy(this.iCalMenuDropdown.id);
		}
	}

	onClickICalButton(event)
	{
		event.stopPropagation();
		event.preventDefault();

		const messageId = event.target.dataset.messageid || event.target.parentNode.dataset.messageid;
		const action = event.target.dataset.action || event.target.parentNode.dataset.action;
		const button = event.target;

		button.classList.add('ui-btn-wait');
		this.removeICalMenuDropdown();

		this.sendICal(messageId, action)
			.then(function() {
				button.classList.remove('ui-btn-wait');
				this.notify(Loc.getMessage(action === 'cancelled' ? 'MAIL_MESSAGE_ICAL_NOTIFY_REJECT' : 'MAIL_MESSAGE_ICAL_NOTIFY_ACCEPT'));
			}.bind(this))
			.catch(function() {
				button.classList.remove('ui-btn-wait');
				this.notify(Loc.getMessage('MAIL_MESSAGE_ICAL_NOTIFY_ERROR'));
			}.bind(this));
	}

	sendICal(messageId, action)
	{
		return new Promise(function(resolve, reject) {
			BX.ajax.runComponentAction('bitrix:mail.client', 'ical', {
				mode: 'ajax',
				data: { messageId, action },
			}).then(
				function() {
					resolve();
				}.bind(this),
				function() {
					reject();
				}.bind(this),
			);
		});
	}
}
