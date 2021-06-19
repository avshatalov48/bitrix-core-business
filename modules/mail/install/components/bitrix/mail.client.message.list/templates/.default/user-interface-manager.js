;(function ()
{
	BX.namespace('BX.Mail.Client.Message.List.UserInterfaceManager');
	BX.Mail.Client.Message.List.UserInterfaceManager = function (options)
	{
		this.gridId = options.gridId;
		this.mailboxId = options.mailboxId;
		this.userHasCrmActivityPermission = options.userHasCrmActivityPermission;
		this.spamDir = options.spamDir;
		this.outcomeDir = options.outcomeDir;
		this.inboxDir = options.inboxDir;
		this.trashDir = options.trashDir;
		this.PATH_TO_USER_TASKS_TASK = options.PATH_TO_USER_TASKS_TASK;
		this.PATH_TO_USER_BLOG_POST = options.PATH_TO_USER_BLOG_POST;
		this.ENTITY_TYPE_NO_BIND = options.ENTITY_TYPE_NO_BIND;
		this.ENTITY_TYPE_CRM_ACTIVITY = options.ENTITY_TYPE_CRM_ACTIVITY;
		this.ENTITY_TYPE_TASKS_TASK = options.ENTITY_TYPE_TASKS_TASK;
		this.ENTITY_TYPE_BLOG_POST = options.ENTITY_TYPE_BLOG_POST;
		this.settingsMenu = options.settingsMenu;
		this.readActionBtnRole = 'read-action';
		this.spamActionBtnRole = 'spam-action';
		this.crmActionBtnRole = 'crm-action';
		this.hideClassName = 'main-ui-hide';
		this.mailboxMenuToggle = document.querySelector('[data-role="mailbox-current-title"]');
		this.settingsToggle = document.querySelector('[data-role="mail-list-settings-menu-popup-toggle"]');
		this.mailboxPopupMenuId = 'mail-msg-list-mailbox-menu';
		this.isCurrentFolderSpam = false;
		this.isCurrentFolderTrash = false;
		this.isCurrentFolderOutcome = false;
		this.setLastDir();
		this.initMailboxes(options.mailboxMenu);
		this.setCurrentFolderFlags(this.getFilterInstance());
		this.addEventHandlers();
		this.updateLeftMenuCounter();
		this.setDefaultBtnTitles();
	};

	BX.Mail.Client.Message.List.UserInterfaceManager.prototype = {
		initMailboxes: function (mailboxMenu)
		{
			this.mailboxMenu = mailboxMenu;

			this.mailboxesUnseen = {};
			for (var i = 0; i < this.mailboxMenu.length; i++)
			{
				if (!(this.mailboxMenu[i] && this.mailboxMenu[i].dataset))
				{
					continue;
				}
				this.mailboxesUnseen[this.mailboxMenu[i].dataset.mailboxId] = this.mailboxMenu[i].dataset.unseen;
			}

			BX.Main.MenuManager.destroy(this.mailboxPopupMenuId);
		},
		addEventHandlers: function ()
		{
			if (this.mailboxMenuToggle)
			{
				BX.bind(this.mailboxMenuToggle, 'click', BX.delegate(this.onMailboxMenuClick, this));
			}
			if (this.settingsToggle)
			{
				BX.bind(this.settingsToggle, 'click', BX.delegate(this.onSettingsToggleClick, this));
			}

			BX.addCustomEvent('BX.Main.Filter:apply', this.onApplyFilter.bind(this));
			BX.addCustomEvent('BX.UI.ActionPanel:hidePanel', this.setDefaultBtnTitles.bind(this));
			BX.addCustomEvent('BX.UI.ActionPanel:showPanel', this.setDefaultBtnOnShow.bind(this));
			BX.addCustomEvent('Grid::updated', function(){
				if(this.getGridInstance().getRows().getSelectedIds().length > 0)
				{
					BX.onCustomEvent(window,'Grid::thereSelectedRows');
				}
			}.bind(this));
			BX.addCustomEvent('Grid::thereSelectedRows', this.handleGridSelectItem.bind(this));
			BX.addCustomEvent('Grid::allRowsSelected', this.handleGridSelectItem.bind(this));
			
			BX.addCustomEvent('mail:openMessageForView',
				function(event)
				{
					var messageId = event['id']
					var row = BX.findParent(document.querySelector('.mail-msg-list-cell-' + messageId), {tagName: 'tr'});
					if (row && row.dataset.id
						&& row.getElementsByClassName('mail-msg-list-cell-unseen').length !== 0)
					{
						this.updateUnreadCounters();

						BX.Mail.Home.Counters.updateCounters([
							{
								name: this.getCurrentFolder(),
								lower: true,
								count: 1,
							},
						]);

						this.onMessagesRead([row.dataset.id], {action: 'markAsSeen'});
					}

				}.bind(this)
			)

			BX.addCustomEvent(
				'SidePanel.Slider:onMessage',
				function (event)
				{
					if (event.getEventId() === 'Mail.Client.MessageCreatedSuccess')
					{
						if (this.isCurrentFolderOutcome)
						{
							BX.Mail.Home.Grid.reloadTable();
						}
					}
				}.bind(this)
			);
			BX.addCustomEvent(
				'onPullEvent-mail',
				this.onBindingCreated.bind(this)
			);

			this.trackActionPanelStyleChange();
			BX.addCustomEvent(window, 'onPopupShow', function (popupWindow)
			{
				if (!(popupWindow.uniquePopupId.indexOf('menu-popup-main-grid-actions-menu-') == 0
					&& popupWindow.getPopupContainer()
					&& popupWindow.bindElement && popupWindow.bindElement.parentElement))
				{
					return;
				}
				this.updateRowMenuSeenBtn(popupWindow);
				this.updateRowMenuSpamBtn(popupWindow);
				this.updateRowMenuCrmBtn(popupWindow);
			}.bind(this));
		},
		onCrmBindingDeleted: function (messageId)
		{
			var bindingWrapper = document.querySelector('.js-bind-' + messageId);
			if (!bindingWrapper)
			{
				return;
			}
			var crmBindType = this.ENTITY_TYPE_CRM_ACTIVITY;
			var crmBind = bindingWrapper.querySelector('[data-type="' + crmBindType + '"]');
			if (!crmBind)
			{
				return;
			}
			crmBind.parentNode.removeChild(crmBind);
			var firstChild = bindingWrapper.firstChild;
			var lastChild = bindingWrapper.lastChild;
			if (firstChild && firstChild.dataset
				&& firstChild.dataset.role === 'comma-separator')
			{
				firstChild.parentNode.removeChild(firstChild);
			}
			if (lastChild && lastChild.dataset
				&& lastChild.dataset.role === 'comma-separator')
			{
				lastChild.parentNode.removeChild(lastChild);
			}
			if (bindingWrapper.childElementCount === 0)
			{
				this.updateGridByUnbindFilter();
			}
		},
		onBindingCreated: function (command, params)
		{
			if ('messageBindingCreated' === command
				&& this.mailboxId == params.mailboxId)
			{
				var bindingWrapper = document.querySelector('.js-bind-' + params.messageId);
				if (bindingWrapper)
				{
					var bindEntityExists = false;
					var bindingsNodes = bindingWrapper.querySelectorAll('[data-type]');
					if (bindingsNodes && bindingsNodes.length > 0)
					{
						for (var i = 0; i < bindingsNodes.length; i++)
						{
							if (bindingsNodes[i].dataset.type === params.entityType)
							{
								bindEntityExists = true;
								break;
							}
						}
					}
					if (!bindEntityExists)
					{
						var bindNode;
						switch (params.entityType)
						{
							case this.ENTITY_TYPE_TASKS_TASK:
								bindNode = BX.create('span', {
									attrs: {
										class: 'mail-badge mail-badge-dark',
									},
									dataset: {
										type: params.entityType
									},
									children: [
										BX.create('a', {
											attrs: {
												href: this.PATH_TO_USER_TASKS_TASK.replace('#action#', 'view').replace('#task_id#', params.entityId),
												class: 'mail-badge-item',
											},
											text: BX.message('MAIL_MESSAGE_LIST_COLUMN_BIND_TASKS_TASK')
										})
									]
								});
								break;
							case this.ENTITY_TYPE_BLOG_POST:
								bindNode = BX.create('span', {
									attrs: {
										class: 'mail-badge mail-badge-dark',
									},
									'dataset': {
										'type': params.entityType
									},
									children: [
										BX.create('a', {
											attrs: {
												class: 'mail-badge-item',
												'href': this.PATH_TO_USER_BLOG_POST.replace('#post_id#', params.entityId),
												'onclick': 'top.BX.SidePanel.Instance.open(this.href, {loader: \'socialnetwork:userblogpost\'}); return false; '
											},
											'text': BX.message('MAIL_MESSAGE_LIST_COLUMN_BIND_BLOG_POST')
										})
									]
								});
								break;
							case this.ENTITY_TYPE_CRM_ACTIVITY:
								if (this.userHasCrmActivityPermission)
								{
									bindNode = BX.create('span', {
										attrs: {
											class: 'mail-badge mail-badge-dark',
										},
										dataset: {
											role: 'crm-binding-link',
											entityId: params.entityId,
											type: params.entityType
										},
										children: [
											BX.create('a', {
												attrs: {
													class: 'mail-badge-item',
													href: params.bindingEntityLink ? params.bindingEntityLink : '#'
												},
												text: BX.message('MAIL_MESSAGE_LIST_COLUMN_BIND_CRM_ACTIVITY')
											})
										]
									});
									break;
								}
								bindNode = BX.create('span', {
									dataset: {type: params.entityType},
									text: BX.message('MAIL_MESSAGE_LIST_COLUMN_BIND_CRM_ACTIVITY')
								});
								break;
							default:
								break;
						}
						if (bindNode)
						{
							bindingWrapper.appendChild(bindNode);
							this.arrangeBindings(bindingWrapper);
							this.updateGridByUnbindFilter();
						}
					}
				}
			}
		},
		arrangeBindings: function(bindingWrapper)
		{
			var crmBind = bindingWrapper.querySelector('[data-type="' + this.ENTITY_TYPE_CRM_ACTIVITY + '"]');
			var taskBind = bindingWrapper.querySelector('[data-type="' + this.ENTITY_TYPE_TASKS_TASK + '"]');
			var postBind = bindingWrapper.querySelector('[data-type="' + this.ENTITY_TYPE_BLOG_POST + '"]');

			if(crmBind !== null && bindingWrapper.firstElementChild !== null)
			{
				bindingWrapper.insertBefore(crmBind, bindingWrapper.firstElementChild);
			}

			if(postBind !== null && taskBind !== null)
			{
				bindingWrapper.insertBefore(taskBind, postBind);
			}
		},
		trackActionPanelStyleChange: function ()
		{
			var targetNode = document.querySelector('.ui-action-panel');
			if (!targetNode)
			{
				return;
			}

			var gridInstance = BX.Main.gridManager.getById(this.gridId).instance;

			var checkbox = BX.create(
				'input',
				{
					'props': {
						'type': 'checkbox',
						'disabled': gridInstance.getRows().getCountDisplayed() == 0,
						title: BX.message('INTERFACE_MAIL_CHECK_ALL'),
					},
					'style': {
						'verticalAlign': 'middle'
					}
				}
			);

			BX.Mail.Home.Grid.setCheckboxNodeForCheckAll(checkbox);

			var container = BX.create(
				'span',
				{
					'style': {
						'display': 'inline-block',
						'height': '100%',
						'paddingLeft': '10px'
					},
					'children': [
						checkbox,
						BX.create(
							'span',
							{
								'style': {
									'display': 'inline-block',
									'height': '100%',
									'verticalAlign': 'middle'
								}
							}
						)
					]
				}
			);

			var getCheckAllCheckboxes = gridInstance.getCheckAllCheckboxes.bind(gridInstance);
			gridInstance.getCheckAllCheckboxes = function ()
			{
				var list = getCheckAllCheckboxes();

				list.push(new BX.Grid.Element(checkbox));

				return list;
			};

			var enableCheckAllCheckboxes = gridInstance.enableCheckAllCheckboxes.bind(gridInstance);
			gridInstance.enableCheckAllCheckboxes = function ()
			{
				setTimeout(
					function ()
					{
						if (gridInstance.getRows().getCountDisplayed() > 0)
						{
							enableCheckAllCheckboxes();
						}
					},
					0
				);
			};

			gridInstance.bindOnCheckAll();

			targetNode.insertBefore(container, targetNode.firstChild);
		},
		getGridHeaderCheckbox: function ()
		{
			if (this.gridHeaderCheckbox === undefined)
			{
				this.gridHeaderCheckbox = document.querySelector('#' + this.gridId + '_table .main-grid-cell-head.main-grid-cell-checkbox');
			}
			return this.gridHeaderCheckbox;
		},
		showElement: function (element, force)
		{
			if (element && force)
			{
				element.style.display = 'block';
			}
			else if (element)
			{
				element.classList.remove(this.hideClassName);
			}
		},
		hideElement: function (element, force)
		{
			if (element && force)
			{
				element.style.display = 'none';
			}
			else if (element)
			{
				element.classList.add(this.hideClassName);
			}
		},
		updateRowMenuSpamBtn: function (popupWindow)
		{
			var notSpamBtn = BX.findParent(popupWindow.getPopupContainer().querySelector('[data-role^="not-spam"]'), {className: 'menu-popup-item'});
			var spamBtn = BX.findParent(popupWindow.getPopupContainer().querySelector('[data-role^="spam"]'), {className: 'menu-popup-item'});
			if (this.isCurrentFolderSpam)
			{
				this.showElement(notSpamBtn, true);
				this.hideElement(spamBtn, true);
			}
			else
			{
				this.showElement(spamBtn, true);
				this.hideElement(notSpamBtn, true);
			}
		},
		updateRowMenuCrmBtn: function (popupWindow)
		{
			var tableRow = BX.findParent(popupWindow.bindElement.parentElement, {className: 'main-grid-row'});
			if (tableRow && tableRow.dataset && tableRow.dataset.id)
			{
				var gridRow = this.getGridInstance().getRows().getById(tableRow.dataset.id);
				if (gridRow)
				{
					gridRow.getActionsMenu();
				}
				var addCrmBtn = BX.findParent(popupWindow.getPopupContainer().querySelector('[data-role^="crm"]'), {className: 'menu-popup-item'});
				var excludeCrmBtn = BX.findParent(popupWindow.getPopupContainer().querySelector('[data-role^="not-crm"]'), {className: 'menu-popup-item'});

				var showAddCrmBtn = this.isAddToCrmActionAvailable(gridRow.node);
				if (showAddCrmBtn)
				{
					this.showElement(addCrmBtn, true);
					this.hideElement(excludeCrmBtn, true);
				}
				else
				{
					this.showElement(excludeCrmBtn, true);
					this.hideElement(addCrmBtn, true);
				}
			}
		},
		isAddToCrmActionAvailable: function (container)
		{
			if (container)
			{
				var bindingWrapper = container.querySelector('[class^="js-bind"]');
				if (bindingWrapper)
				{
					var crmBindings = bindingWrapper.querySelectorAll('[data-role="crm-binding-link"]');
					if (crmBindings && crmBindings.length > 0)
					{
						return false;
					}
				}
			}
			return true;
		},
		updateRowMenuSeenBtn: function (popupWindow)
		{
			var tableRow = BX.findParent(popupWindow.bindElement.parentElement, {className: 'main-grid-row'});
			if (tableRow && tableRow.dataset && tableRow.dataset.id)
			{
				var gridRow = this.getGridInstance().getRows().getById(tableRow.dataset.id);
				if (gridRow)
				{
					gridRow.getActionsMenu();
				}
				var notReadBtn = BX.findParent(popupWindow.getPopupContainer().querySelector('[data-role^="not-read"]'), {className: 'menu-popup-item'});
				var readBtn = BX.findParent(popupWindow.getPopupContainer().querySelector('[data-role^="read"]'), {className: 'menu-popup-item'});

				var actionName = this.isSelectedRowsHaveClass('mail-msg-list-cell-unseen', tableRow.dataset.id) ? 'markAsSeen' : 'markAsUnseen';
				
				if (actionName === 'markAsSeen')
				{
					this.showElement(readBtn, true);
					this.hideElement(notReadBtn, true);
				}
				else
				{
					this.showElement(notReadBtn, true);
					this.hideElement(readBtn, true);
				}
			}
		},
		onMessagesRead: function (selectedIds, params)
		{
			this.changeMessageRead(selectedIds, params);
			this.updateGridByUnseenFilter();
		},
		updateGridByUnseenFilter: function ()
		{
			var filter = this.getFilterInstance();
			if (filter.getFilterFieldsValues() && filter.getFilterFieldsValues()['IS_SEEN'] !== '')
			{
				BX.Mail.Home.Grid.reloadTable();
			}
		},
		updateGridByUnbindFilter: function ()
		{
			var filter = this.getFilterInstance();
			if (filter.getFilterFieldsValues() && filter.getFilterFieldsValues()['BIND'] !== '')
			{
				BX.Mail.Home.Grid.reloadTable();
			}
		},
		onPopupMenuFirstShow: function (popupWindow)
		{
			BX.bind(
				popupWindow.contentContainer,
				'click',
				function ()
				{
					popupWindow.close();
				}
			);
		},
		onSettingsToggleClick: function ()
		{
			var popup = BX.PopupMenu.create(
				'mail-msg-list-settings-menu',
				this.settingsToggle,
				this.settingsMenu,
				{
					events: {
						onPopupFirstShow: this.onPopupMenuFirstShow
					}
				}
			);

			popup.popupWindow.isShown() ? popup.close() : popup.show();
		},
		onMailboxMenuClick: function ()
		{
			var popup = BX.Main.MenuManager.create(
				this.mailboxPopupMenuId,
				this.mailboxMenuToggle,
				this.mailboxMenu,
				{
					events: {
						onPopupFirstShow: this.onPopupMenuFirstShow
					}
				}
			);

			popup.popupWindow.isShown() ? popup.close() : popup.show();
		},
		closeMailboxMenu: function ()
		{
			var popup = BX.Main.MenuManager.getMenuById(this.mailboxPopupMenuId);

			if (popup)
			{
				popup.close();
			}
		},
		updateUnreadCounters: function ()
		{
			this.updateMailboxMenuUnseenCounter();
		},
		updateUnreadMessageMailboxesMarker: function(totalNumberOfUnreadLetters)
		{
			if(totalNumberOfUnreadLetters)
				BX.Mail.Home.unreadMessageMailboxesMarker.classList.remove('mail-hidden-element');
			else
				BX.Mail.Home.unreadMessageMailboxesMarker.classList.add('mail-hidden-element');
		}
		,
		updateTotalUnreadCounters: function (totalNumberOfUnreadMessagesInOtherMailboxes)
		{
			BX.onCustomEvent('BX.Mail.Home:updateAllCounters');

			this.updateUnreadMessageMailboxesMarker(totalNumberOfUnreadMessagesInOtherMailboxes);
			this.setTotalUnseenCounter(totalNumberOfUnreadMessagesInOtherMailboxes);
			this.updateLeftMenuCounter();
		},
		updateLeftMenuCounter: function ()
		{
			var unseen = BX.Mail.Home.mailboxCounters.getTotalCounter();
			if (typeof top.B24 === "object" && typeof top.B24.updateCounters === "function" && unseen > 0)
			{
				top.B24.updateCounters({mail_unseen: unseen});
			}
			if (typeof top.BXIM === "object" && typeof top.BXIM.notify === "object")
			{
				if (typeof top.BXIM.notify.counters === "object")
				{
					top.BXIM.notify.counters.mail_unseen = unseen;
				}
				if (typeof top.BXIM.notify.updateNotifyMailCount === "function")
				{
					top.BXIM.notify.updateNotifyMailCount(unseen);
				}
			}
		},
		updateMailboxUnseenCounter: function (seenNumber)
		{
			this.updateMailboxMenuUnseenCounter(seenNumber);
		},
		changeMessageRead: function (selectedIds, params)
		{
			if (params.action === 'markAsSeen')
			{
				for (var i = 0; i < selectedIds.length; i++)
				{
					var row = this.getGridInstance().getRows().getById(selectedIds[i]);
					if (row && row.node)
					{
						var columns = row.node.getElementsByClassName('mail-msg-list-cell-unseen');
						for (var j = columns.length - 1; j >= 0; j--)
						{
							columns[j].classList.remove('mail-msg-list-cell-unseen');
							row.node.setAttribute("unseen", "false");
						}
					}
				}
			}
			else if (params.action === 'markAsUnseen')
			{
				for (var i = 0; i < selectedIds.length; i++)
				{
					var row = this.getGridInstance().getRows().getById(selectedIds[i]);
					if (row && row.node)
					{
						row.node.setAttribute("unseen", "true");
						row.node.cells[2].classList.add('mail-msg-list-cell-unseen');
						row.node.cells[3].classList.add('mail-msg-list-cell-unseen');
						row.node.cells[4].classList.add('mail-msg-list-cell-unseen');
					}
				}
			}
		},
		handleGridSelectItem: function ()
		{
			this.updateSeenBtn();
			this.updateCrmBtn();
			this.updateSpamBtn();

			var event = document.createEvent("Event");
			event.initEvent("resize", true, true);
			window.dispatchEvent(event);
		},
		disActivateBtn: function (btnRole)
		{
			this.activateBtn(btnRole, false);
		},
		activateBtn: function (activatingBtnRole, show)
		{
			show = show === undefined || show === true;

			this.toggleButton(activatingBtnRole, show);
			this.toggleButton('not-' + activatingBtnRole, !show);
		},
		toggleButton: function(role, show)
		{
			var buttons = document.querySelectorAll('[data-role^="' + role + '"]');

			Array.prototype.slice.call(buttons, 0).forEach(
				function (title)
				{
					var button = BX.findParent(title, {className: "ui-action-panel-item"});
					button = button ? button : BX.findParent(title, {className: 'main-grid-row'});
					show ? this.showElement(button) : this.hideElement(button);
				}.bind(this)
			);
		},
		updateCrmBtn: function ()
		{
			var selectedIds = this.getGridInstance().getRows().getSelectedIds();
			if (selectedIds.length !== 1)
			{
				this.activateBtn(this.crmActionBtnRole);
				return;
			}
			var row = this.getGridInstance().getRows().getById(selectedIds[0]);
			if (!(row && row.node))
			{
				return;
			}
			var showAddToCrm = this.isAddToCrmActionAvailable(row.node);
			if (showAddToCrm)
			{
				this.activateBtn(this.crmActionBtnRole);
			}
			else
			{
				this.disActivateBtn(this.crmActionBtnRole);
			}
		},
		updateSpamBtn: function ()
		{
			if (this.isCurrentFolderSpam)
			{
				this.disActivateBtn(this.spamActionBtnRole);
			}
			else
			{
				this.activateBtn(this.spamActionBtnRole);
			}
		},
		updateSeenBtn: function ()
		{
			var actionName = this.isSelectedRowsHaveClass('mail-msg-list-cell-unseen') ? 'markAsSeen' : 'markAsUnseen';
			if (actionName === 'markAsSeen')
			{
				this.activateBtn(this.readActionBtnRole);
			}
			else
			{
				this.disActivateBtn(this.readActionBtnRole);
			}
		},
		setDefaultBtnOnShow: function (panel)
		{
			panel.items.forEach(function(item) {

			if(item && item instanceof BX.UI.ActionPanel.Item)
			{
				if(this.getCurrentFolder() === '[Gmail]/All Mail' && item['id']==='deleteImmediately')
				{
					item.disable();
					item.layout.container.removeAttribute('onclick');
				}
			}}.bind(this));
		}
		,
		setDefaultBtnTitles: function (panel)
		{
			if(panel && Array.isArray(panel.items))
			{
				panel.items.forEach(function(item) {
					if(item && item instanceof BX.UI.ActionPanel.Item)
					{
						item.layout.container.removeAttribute('onclick');
					}
				});
			}
			
			var popup = BX.Main.MenuManager.getMenuById('ui-action-panel-item-popup-menu');
			popup && popup.close();

			this.toggleButton(this.readActionBtnRole, false);
			this.toggleButton('not-' + this.readActionBtnRole, false);
			this.activateBtn(this.crmActionBtnRole);
			this.updateSpamBtn();

			var event = document.createEvent("Event");
			event.initEvent("resize", true, true);
			window.dispatchEvent(event);
		},
		getRowMenu: function ()
		{
			var selectedIds = this.getGridInstance().getRows().getSelectedIds();
			id = selectedIds.length ? selectedIds[0] : null;
			var row = id ? this.getGridInstance().getRows().getById(id) : null;
			if (row)
			{
				return row.getActionsMenu();
			}
			return null;
		},
		onUnreadCounterClick: function ()
		{
			var filter = this.getFilterInstance();
			var filterApi = filter.getApi();
			filterApi.setFields({
				'DIR': filter.getFilterFieldsValues()['DIR'],
				'IS_SEEN': 'N'
			});
			filterApi.apply();
		},
		getFilterInstance: function ()
		{
			return BX.Main.filterManager.getById(this.gridId);
		},
		onApplyFilter: function (id, data, filterInstance, promise, params)
		{
			if (id !== this.gridId)
			{
				return;
			}
			this.setCurrentFolderFlags(filterInstance);
			this.setDefaultBtnTitles();
		},
		setCurrentFolderFlags: function (filterInstance)
		{
			var presetName = filterInstance.getPreset().getCurrentPresetId();
			this.isCurrentFolderTrash = (this.trashDir !== '' && (
					presetName === 'trash' ||
					(filterInstance.getFilterFieldsValues() && filterInstance.getFilterFieldsValues()['DIR'] === this.trashDir)
				)
			);
			this.isCurrentFolderSpam = (this.spamDir !== '' && (
					presetName === 'spam' ||
					(filterInstance.getFilterFieldsValues() && filterInstance.getFilterFieldsValues()['DIR'] === this.spamDir)
				)
			);
			this.isCurrentFolderOutcome = (this.outcomeDir !== '' && (
					presetName === 'outcome' ||
					(filterInstance.getFilterFieldsValues() && filterInstance.getFilterFieldsValues()['DIR'] === this.outcomeDir)
				)
			);
		},
		getLastDir: function ()
		{
			return this.lastDir;
		},
		setLastDir: function ()
		{
			BX.onCustomEvent(window,'Mail::directoryChanged');
			this.lastDir = this.getCurrentFolder();
		},
		getCurrentFolder: function ()
		{
			var filter = this.getFilterInstance();
			var dir = filter.getFilterFieldsValues()['DIR'];
			var inboxDir = this.inboxDir;
			return dir || inboxDir;
		},
		getCurrentMailboxId: function ()
		{
			var currentMailbox = document.querySelector('[data-role="mailbox-current-title"]');
			return currentMailbox && currentMailbox.dataset && currentMailbox.dataset.mailboxId ? currentMailbox.dataset.mailboxId : null;
		},
		setTotalUnseenCounter: function (count)
		{
			var currentMailboxId = this.getCurrentMailboxId();
			this.mailboxesUnseen[currentMailboxId] = count;
		},
		updateMailboxMenuUnseenCounter: function ()
		{
			var currentMailboxId = this.getCurrentMailboxId();

			if (!currentMailboxId)
			{
				return;
			}

			for (var i = 0; i < this.mailboxMenu.length; i++)
			{
				if (this.mailboxMenu[i] && this.mailboxMenu[i].html && this.mailboxMenu[i].dataset
					&& this.mailboxMenu[i].dataset.mailboxId == currentMailboxId)
				{
					this.mailboxMenu[i] = this.updateMailboxMenuItemUnseenCounter(
						this.mailboxMenu[i],
						BX.Mail.Home.Counters.getTotalCounter()
					);

					BX.Main.MenuManager.destroy(this.mailboxPopupMenuId);
					break;
				}
			}
		},
		updateMailboxMenuItemUnseenCounter: function (mailboxMenu, count)
		{
			mailboxMenu = this.setMailboxTitleMenuUnseenCounter(
				mailboxMenu,
				count
			);

			if (!mailboxMenu.items)
			{
				return mailboxMenu;
			}

			return mailboxMenu;
		},
		setMailboxTitleMenuUnseenCounter: function (mailboxMenu, count)
		{
			var className = this.hideClassName;
			if (count > 0)
			{
				className = '';

				if (typeof mailboxMenu.dataset.path != 'undefined')
				{
					if (mailboxMenu.unseen == 0)
					{
						className += ' mail-msg-list-menu-child-counter';
					}

					if (!mailboxMenu.dataset.isCounted)
					{
						className += ' mail-msg-list-menu-fake-counter';
					}
				}
			}

			var find = /<span class="(main-buttons-item-counter)[\w -]*">[0-9]+<\/span>/g;
			var replace = '<span class="main-buttons-item-counter ' + className + '">' + count + '</span>';

			if (mailboxMenu.html.match(find))
			{
				mailboxMenu.html = mailboxMenu.html.replace(
					find,
					replace
				);
			}
			else
			{
				mailboxMenu.html += '&nbsp;' + replace;
			}

			mailboxMenu.dataset.unseen = count;

			return mailboxMenu;
		},
		isVisible: function (element)
		{
			return element && !element.classList.contains(this.hideClassName);
		}
	};
})();