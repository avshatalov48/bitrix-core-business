;(function ()
{
	BX.namespace('BX.Mail.Client.Message.List.UserInterfaceManager');
	BX.Mail.Client.Message.List.UserInterfaceManager = function (options)
	{
		this.gridId = options.gridId;
		this.mailboxId = options.mailboxId;
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
		this.ENTITY_TYPE_IM_CHAT = options.ENTITY_TYPE_IM_CHAT;
		this.ENTITY_TYPE_CALENDAR_EVENT = options.ENTITY_TYPE_CALENDAR_EVENT;
		this.settingsMenu = options.settingsMenu;
		this.readActionBtnRole = 'read-action';
		this.notReadActionBtnRole = 'not-read-action';
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

						if(this.getCurrentFolder() !== '')
						{
							BX.Mail.Home.Counters.updateCounters([
								{
									name: this.getCurrentFolder(),
									lower: true,
									count: 1,
								},
							]);
						}

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
			var bindingWrapper = document.querySelector('.js-bind-' + messageId+'.mail-binding-crm.mail-ui-active');
			if (!bindingWrapper)
			{
				return;
			}
			bindingWrapper.deactivation();
			this.updateGridByUnbindFilter();
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

			targetNode.firstChild.onclick = function() {
				BX.Mail.Home.Grid.resetGridSelection();
			}

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
				var bindingWrapper = container.querySelector('.mail-binding-crm.mail-ui-active');

				if (bindingWrapper)
				{
					return false;
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
			if (typeof top.B24 === "object" && typeof top.B24.updateCounters === "function")
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
						var oldMessage = row.node.getElementsByClassName('mail-msg-list-cell-old');

						if (!(oldMessage && oldMessage.length) )
						{
							row.node.setAttribute("unseen", "true");
							row.node.cells[2].classList.add('mail-msg-list-cell-unseen');
							row.node.cells[3].classList.add('mail-msg-list-cell-unseen');
							row.node.cells[4].classList.add('mail-msg-list-cell-unseen');
						}
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

			var crmBtn = BX.Mail.Home.Grid.getPanel().getItemById('addToCrm');
			var notCrmBtn = BX.Mail.Home.Grid.getPanel().getItemById('excludeFromCrm');

			if (!crmBtn || !notCrmBtn)
			{
				return;
			}

			if (showAddToCrm)
			{
				notCrmBtn.hide();
				crmBtn.showAsInlineBlock();
			}
			else
			{
				notCrmBtn.showAsInlineBlock();
				crmBtn.hide();
			}
		},
		updateSpamBtn: function ()
		{
			var notSpamBtn = BX.Mail.Home.Grid.getPanel().getItemById('notSpam');
			var spamBtn = BX.Mail.Home.Grid.getPanel().getItemById('spam');

			if (this.isCurrentFolderSpam)
			{
				notSpamBtn.showAsInlineBlock();
				spamBtn.hide();
			}
			else
			{
				spamBtn.showAsInlineBlock();
				notSpamBtn.hide();
			}
		},
		updateSeenBtn: function ()
		{
			var actionName = this.isSelectedRowsHaveClass('mail-msg-list-cell-unseen') ? 'markAsSeen' : 'markAsUnseen';
			var selectedIds = this.getGridInstance().getRows().getSelectedIds();
			var oldMessagesNumber = this.isSelectedRowsHaveClass('mail-msg-list-cell-old');

			var notReadBtn = BX.Mail.Home.Grid.getPanel().getItemById('notRead');
			var readBtn = BX.Mail.Home.Grid.getPanel().getItemById('read');

			if (!notReadBtn || !readBtn)
			{
				return;
			}

			if(selectedIds.length === oldMessagesNumber)
			{
				readBtn.hide();
				notReadBtn.hide();
			}
			else
			{

				if (actionName === 'markAsSeen')
				{
					notReadBtn.hide();
					readBtn.showAsInlineBlock();
				}
				else
				{
					notReadBtn.showAsInlineBlock();
					readBtn.hide();
				}
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

			var notReadBtn = BX.Mail.Home.Grid.getPanel().getItemById('notRead');
			var readBtn = BX.Mail.Home.Grid.getPanel().getItemById('read');
			var notSpamBtn = BX.Mail.Home.Grid.getPanel().getItemById('notSpam');
			var spamBtn = BX.Mail.Home.Grid.getPanel().getItemById('spam');
			var crmBtn = BX.Mail.Home.Grid.getPanel().getItemById('addToCrm');
			var notCrmBtn = BX.Mail.Home.Grid.getPanel().getItemById('excludeFromCrm');

			if (typeof notReadBtn !== 'undefined')
			{
				notReadBtn.hide();
			}
			if (typeof notSpamBtn !== 'undefined')
			{
				notSpamBtn.hide();
			}
			if (typeof readBtn !== 'undefined')
			{
				readBtn.showAsInlineBlock();
			}

			if (typeof spamBtn !== 'undefined')
			{
				spamBtn.showAsInlineBlock();
			}

			if (typeof crmBtn !== 'undefined')
			{
				crmBtn.showAsInlineBlock();
			}

			if (typeof notCrmBtn !== 'undefined')
			{
				notCrmBtn.hide();
			}

			BX.style(readBtn.layout.container,'display','inline-block');

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
			return filter.getFilterFieldsValues()['DIR'];
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
