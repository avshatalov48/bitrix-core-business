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
		this.trashDir = options.trashDir;
		this.taskViewUrlTemplate = options.taskViewUrlTemplate;
		this.taskViewUrlIdForReplacement = options.taskViewUrlIdForReplacement;
		this.ENTITY_TYPE_NO_BIND = options.ENTITY_TYPE_NO_BIND;
		this.ENTITY_TYPE_CRM_ACTIVITY = options.ENTITY_TYPE_CRM_ACTIVITY;
		this.ENTITY_TYPE_TASKS_TASK = options.ENTITY_TYPE_TASKS_TASK;
		this.mailboxMenu = options.mailboxMenu;
		this.settingsMenu = options.settingsMenu;
		this.unreadCounterSelector = '[data-role="unreadCounter"]';
		this.emptyCountersTitleSelector = '[data-role="emptyCountersTitle"]';
		this.readActionBtnRole = 'read-action';
		this.spamActionBtnRole = 'spam-action';
		this.crmActionBtnRole = 'crm-action';
		this.hideClassName = 'main-ui-hide';
		this.countersBlock = document.querySelector('#mail-msg-counter-title');
		this.mailboxMenuToggle = document.querySelector('[data-role="mailbox-current-title"]');
		this.settingsToggle = document.querySelector('[data-role="mail-list-settings-menu-popup-toggle"]');
		this.totalUnseen = document.querySelector('[data-role="unseen-total"]');
		this.mailboxesUnseen = {};
		for (var i = 0; i < this.mailboxMenu.length; i++)
		{
			if (!(this.mailboxMenu[i] && this.mailboxMenu[i].dataset))
			{
				continue;
			}
			this.mailboxesUnseen[this.mailboxMenu[i].dataset.mailboxId] = this.mailboxMenu[i].dataset.unseen;
		}
		this.UNREAD_COUNTER_TYPE = 'unread';
		this.isCurrentFolderSpam = false;
		this.isCurrentFolderTrash = false;
		this.isCurrentFolderOutcome = false;
		this.setCurrentFolderFlags(this.getFilterInstance());
		this.addEventHandlers();
		this.updateLeftMenuCounter();
		this.setDefaultBtnTitles();
	};

	BX.Mail.Client.Message.List.UserInterfaceManager.prototype = {
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
			BX.addCustomEvent('Grid::updated', this.setDefaultBtnTitles.bind(this));
			BX.addCustomEvent('Grid::thereSelectedRows', this.handleGridSelectItem.bind(this));
			BX.addCustomEvent('Grid::allRowsSelected', this.handleGridSelectItem.bind(this));

			BX.addCustomEvent(
				'SidePanel.Slider:onMessage',
				function (event)
				{
					if (event.getEventId() === 'mail-message-view')
					{
						var row = BX.findParent(document.querySelector('.mail-msg-list-cell-' + event.getData().id), {tagName: 'tr'});
						if (row && row.dataset.id
							&& row.getElementsByClassName('mail-msg-list-cell-unseen').length !== 0)
						{
							this.updateUnreadCounters(-1);
							this.onMessagesRead([row.dataset.id], {action: 'markAsSeen'});
						}
					}
					else if (event.getEventId() === 'Mail.Client.MessageCreatedSuccess')
					{
						if (this.isCurrentFolderOutcome)
						{
							this.reloadGrid({});
						}
						this.resetGridSelection();
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
				this.resetGridSelection();

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
								bindNode = BX.create('a', {
									attrs: {href: this.taskViewUrlTemplate.replace(new RegExp(this.taskViewUrlIdForReplacement, 'g'), params.entityId)},
									children: [
										BX.create('span', {
											dataset: {type: params.entityType},
											text: BX.message('MAIL_MESSAGE_LIST_COLUMN_BIND_TASKS_TASK')
										})
									]
								});
								break;
							case this.ENTITY_TYPE_CRM_ACTIVITY:
								if (this.userHasCrmActivityPermission)
								{
									bindNode = BX.create('span', {
										dataset: {
											role: 'crm-binding-link',
											entityId: params.entityId,
											type: params.entityType
										},
										children: [
											BX.create('a', {
												attrs: {href: params.bindingEntityLink ? params.bindingEntityLink : '#'},
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
							if (bindingsNodes.length > 0)
							{
								bindingWrapper.appendChild(document.createTextNode(', '));
							}
							bindingWrapper.appendChild(bindNode);

							this.updateGridByUnbindFilter();
						}
					}
				}
			}
		},
		trackActionPanelStyleChange: function ()
		{
			var targetNode = document.querySelector('.ui-action-panel');
			if (!targetNode)
			{
				return;
			}

			var gridHeaderCheckbox = this.getGridHeaderCheckbox();
			if (!gridHeaderCheckbox)
			{
				return;
			}

			var gridInstance = BX.Main.gridManager.getById(this.gridId).instance;

			var gridHeaderCheckboxClone = gridHeaderCheckbox.cloneNode(true);

			var checkboxCloneInput = BX.findChildByClassName(gridHeaderCheckboxClone, 'main-grid-check-all', true);
			checkboxCloneInput.removeAttribute('id');

			BX.bind(
				checkboxCloneInput,
				'change',
				gridInstance._clickOnCheckAll.bind(gridInstance)
				//gridInstance.getRows()[this.checked?'selectAll':'unselectAll']();
			);

			var updateCloneState = function (row, grid)
			{
				if (grid === gridInstance)
				{
					checkboxCloneInput.checked = grid.getRows().getBodyChild().length > 0 && grid.getRows().isAllSelected();
				}
			};

			BX.addCustomEvent('Grid::selectRow', updateCloneState);
			BX.addCustomEvent('Grid::unselectRow', updateCloneState);

			BX.addCustomEvent(
				'BX.UI.ActionPanel:hidePanel',
				function ()
				{
					setTimeout(updateCloneState.bind(null, null, gridInstance), 0);
				}
			);

			BX.addCustomEvent(
				'Grid::disabled',
				function (grid)
				{
					if (grid === gridInstance)
					{
						checkboxCloneInput.checked = false;
						checkboxCloneInput.disabled = true;
					}
				}
			);

			BX.addCustomEvent(
				'Grid::enabled',
				function (grid)
				{
					if (grid === gridInstance)
					{
						checkboxCloneInput.disabled = false;
					}
				}
			);

			targetNode.insertBefore(gridHeaderCheckboxClone, targetNode.firstChild);
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
				this.reloadGrid({});
			}
		},
		updateGridByUnbindFilter: function ()
		{
			var filter = this.getFilterInstance();
			if (filter.getFilterFieldsValues() && filter.getFilterFieldsValues()['BIND'] !== '')
			{
				this.reloadGrid({});
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
			var popup = BX.PopupMenu.create(
				'mail-msg-list-mailbox-menu',
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
		updateUnreadCounters: function (readNumber)
		{
			this.updateCounter(this.UNREAD_COUNTER_TYPE, readNumber);
			this.updateMailboxUnseenCounter(readNumber);
			this.updateTotalUnseenCounter(readNumber);
			this.updateLeftMenuCounter();
		},
		updateLeftMenuCounter: function ()
		{
			var unseen = this.getTotalUnseen();
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
		updateTotalUnseenCounter: function (readNumber)
		{
			this.totalUnseen.textContent = parseInt(this.totalUnseen.textContent) + readNumber;
			if (this.totalUnseen.textContent > 0)
			{
				this.showElement(this.totalUnseen);
			}
			else
			{
				this.hideElement(this.totalUnseen);
			}
		},
		updateMailboxUnseenCounter: function (seenNumber)
		{
			var currentMailbox = document.querySelector('[data-role="mailbox-current-title"]');
			if (currentMailbox && currentMailbox.dataset && currentMailbox.dataset.mailboxId)
			{
				var currentUnseen = this.mailboxesUnseen[currentMailbox.dataset.mailboxId];
				this.mailboxesUnseen[currentMailbox.dataset.mailboxId] = parseInt(currentUnseen) + parseInt(seenNumber);
				for (var i = 0; i < this.mailboxMenu.length; i++)
				{
					if (this.mailboxMenu[i] && this.mailboxMenu[i].text && this.mailboxMenu[i].dataset
						&& this.mailboxMenu[i].dataset.mailboxId == currentMailbox.dataset.mailboxId)
					{
						this.mailboxMenu[i].text = this.mailboxMenu[i].text.replace(
							/[0-9]+<\/span>/g,
							this.mailboxesUnseen[currentMailbox.dataset.mailboxId].toString() + '</span>'
						);
						if (this.mailboxesUnseen[currentMailbox.dataset.mailboxId] > 0)
						{
							this.mailboxMenu[i].text = this.mailboxMenu[i].text.replace(
								/main-ui-hide/g,
								'js-unseen-mailbox'
							);
						}
						else
						{
							this.mailboxMenu[i].text = this.mailboxMenu[i].text.replace(
								/js-unseen-mailbox/g,
								this.hideClassName
							);
						}
						break;
					}
				}
			}
		},
		getTotalUnseen: function ()
		{
			var unseen = this.totalUnseen;
			if (unseen)
			{
				return unseen.textContent;
			}
			return 0;
		},
		updateCounter: function (type, changedNumber)
		{
			var cntNumberSelector = null, cntBlockSelector;
			if (type === this.UNREAD_COUNTER_TYPE)
			{
				cntNumberSelector = '[data-role="unread-counter-number"]';
				cntBlockSelector = this.unreadCounterSelector;
			}

			var counter = document.querySelector(cntNumberSelector);
			if (counter)
			{
				var currentNumber = counter.textContent;
				var newAmount = parseInt(currentNumber) + parseInt(changedNumber);
				if (newAmount > 0)
				{
					counter.textContent = newAmount.toString();
					this.showElement(document.querySelector(cntBlockSelector));
				}
				else
				{
					counter.textContent = '0';
					this.hideElement(document.querySelector(cntBlockSelector));
				}
				this.updateCountersBlock();
			}
		},
		updateCountersBlock: function ()
		{
			var unread = document.querySelector(this.unreadCounterSelector);
			if (this.isVisible(unread))
			{
				this.hideElement(document.querySelector(this.emptyCountersTitleSelector));
			}
			else
			{
				this.showElement(document.querySelector(this.emptyCountersTitleSelector));
			}
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
						row.node.cells[2].classList.add('mail-msg-list-cell-unseen');
						row.node.cells[3].classList.add('mail-msg-list-cell-unseen');
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
			var activeBtnTitles = document.querySelectorAll('[data-role^="' + activatingBtnRole + '"]');
			Array.prototype.slice.call(activeBtnTitles, 0).forEach(function (btnTitle)
			{
				var activeBtn = BX.findParent(btnTitle, {className: "ui-action-panel-item"});
				activeBtn = activeBtn ? activeBtn : BX.findParent(btnTitle, {className: 'main-grid-row'});
				show === undefined || show === true ? this.showElement(activeBtn) : this.hideElement(activeBtn);

			}.bind(this));

			var inactiveBtnTitles = document.querySelectorAll('[data-role^="not-' + activatingBtnRole + '"]');
			Array.prototype.slice.call(inactiveBtnTitles, 0).forEach(function (btnTitle)
			{
				var inactiveBtn = BX.findParent(btnTitle, {className: "ui-action-panel-item"});
				inactiveBtn = inactiveBtn ? inactiveBtn : BX.findParent(btnTitle, {className: 'main-grid-row'});
				show === undefined || show === true ? this.hideElement(inactiveBtn) : this.showElement(inactiveBtn);
			}.bind(this));
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
		setDefaultBtnTitles: function ()
		{
			this.activateBtn(this.readActionBtnRole);
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
		isVisible: function (element)
		{
			return element && !element.classList.contains(this.hideClassName);
		}
	};
})();