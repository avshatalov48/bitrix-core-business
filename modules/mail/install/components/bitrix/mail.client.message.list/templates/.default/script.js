;(function ()
{
	BX.namespace('BX.Mail.Client.Message.List');
	BX.Mail.Client.Message.List = function (options)
	{
		this.gridId = options.gridId;
		this.mailboxId = options.mailboxId;
		this.canMarkSpam = options.canMarkSpam;
		this.canDelete = options.canDelete;
		this.moveBtnMailIdPrefix = options.moveBtnMailIdPrefix;
		this.connectedMailboxesLicenseInfo = options.connectedMailboxesLicenseInfo;
		this.ERROR_CODE_CAN_NOT_DELETE = options.ERROR_CODE_CAN_NOT_DELETE;
		this.ERROR_CODE_CAN_NOT_MARK_SPAM = options.ERROR_CODE_CAN_NOT_MARK_SPAM;
		this.disabledClassName = 'js-disabled';
		this.userInterfaceManager = new BX.Mail.Client.Message.List.UserInterfaceManager(options);
		this.userInterfaceManager.reloadGrid = this.reloadGrid.bind(this);
		this.userInterfaceManager.resetGridSelection = this.resetGridSelection.bind(this);
		this.userInterfaceManager.isSelectedRowsHaveClass = this.isSelectedRowsHaveClass.bind(this);
		this.userInterfaceManager.getGridInstance = this.getGridInstance.bind(this);
		this.addEventHandlers();

		BX.Mail.Client.Message.List[options.id] = this;
	};
	BX.Mail.Client.Message.List.prototype = {
		addEventHandlers: function ()
		{
			// todo delete this hack
			// it is here to prevent grid's title changing after filter apply
			BX.ajax.UpdatePageData = (function() {});
		},
		showLicensePopup: function (code)
		{
			B24.licenseInfoPopup.show(
				code,
				BX.message('MAIL_MAILBOX_LICENSE_CONNECTED_MAILBOXES_LIMIT_TITLE'),
				this.connectedMailboxesLicenseInfo
			);
		},
		onCrmClick: function (id)
		{
			this.resetGridSelection();
			var selectedIds = this.getGridInstance().getRows().getSelectedIds();
			var row = this.getGridInstance().getRows().getById(id ? id : selectedIds[0]);
			if (!(row && row.node))
			{
				return;
			}
			var addToCrm = this.userInterfaceManager.isAddToCrmActionAvailable(row.node);
			var messageIdNode = row.node.querySelector('[data-message-id]');
			if (!(messageIdNode.dataset && messageIdNode.dataset.messageId))
			{
				return;
			}
			if (addToCrm)
			{
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
							messageId: messageIdNode.dataset.messageId
						}
					}
				).then(function (id, json)
				{
					this.isAddingToCrmInProgress[id] = false;
					if (json.data && json.data.length > 0)
					{
						this.notify(BX.message('MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM'));
						this.userInterfaceManager.onBindingCreated();
					}
					else
					{
						this.notify(BX.message('MAIL_MESSAGE_LIST_NOTIFY_NOT_ADDED_TO_CRM'));
					}
				}.bind(this, id));
			}
			else
			{
				BX.ajax.runComponentAction(
					'bitrix:mail.client',
					'removeCrmActivity',
					{
						mode: 'ajax',
						data: {
							messageId: messageIdNode.dataset.messageId
						}
					}
				).then(function (messageIdNode)
				{
					this.userInterfaceManager.onCrmBindingDeleted(messageIdNode.dataset.messageId);
					this.notify(BX.message('MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM'));
				}.bind(this, messageIdNode));
			}
		},
		onViewClick: function (id)
		{
			if (id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0)
			{
				return;
			}
			// @TODO: path
			BX.SidePanel.Instance.open("/mail/message/" + id, {
				width: 1080
			});
		},
		onDeleteClick: function (id)
		{
			if (id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0)
			{
				return;
			}
			if (!this.canDelete)
			{
				this.showSettingsSlider();
				return;
			}
			var options = {
				onSuccess: function ()
				{
					this.reloadGrid({});
				}
			};
			if (id !== undefined)
			{
				options.ids = [id];
			}
			if (this.userInterfaceManager.isCurrentFolderTrash)
			{
				var confirmPopup = this.getConfirmDeletePopup(options);
				confirmPopup.show();
			}
			else
			{
				this.runAction('delete', options);
			}
		},
		onMoveToFolderClick: function (event)
		{
			var folderOptions = event.currentTarget.dataset;
			var id = null;
			var popupSubmenu = BX.findParent(event.currentTarget, {className: 'popup-window'});
			if (popupSubmenu && popupSubmenu.id)
			{
				id = popupSubmenu.id.match(new RegExp(this.moveBtnMailIdPrefix + '.*'));
				if (id !== null && Array.isArray(id))
				{
					id = id[0].substr(this.moveBtnMailIdPrefix.length);
				}
			}
			var isDisabled = JSON.parse(folderOptions.isDisabled);
			var folderPath = folderOptions.folderPath;
			if ((id === null && this.getGridInstance().getRows().getSelectedIds().length === 0) || isDisabled)
			{
				return;
			}
			var multiSelectedIds = this.getGridInstance().getRows().getSelectedIds();
			var resultIds = multiSelectedIds.length ? multiSelectedIds : (id ? [id] : []);
			resultIds = this.filterRowsByClassName(this.disabledClassName, resultIds, true);
			if (!resultIds.length)
			{
				return;
			}
			this.resetGridSelection();
			this.runAction('moveToFolder', {ids: resultIds, params: {folder: folderPath}});
		},
		onReadClick: function (id)
		{
			if (id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0)
			{
				return;
			}
			var actionName = this.isSelectedRowsHaveClass('mail-msg-list-cell-unseen', id) ? 'markAsSeen' : 'markAsUnseen';
			var resultIds = this.filterRowsByClassName('mail-msg-list-cell-unseen', id, actionName !== 'markAsSeen');
			resultIds = this.filterRowsByClassName(this.disabledClassName, resultIds, true);
			if (!resultIds.length)
			{
				return;
			}
			this.userInterfaceManager.onMessagesRead(resultIds, {action: actionName});
			this.resetGridSelection();
			if (actionName === 'markAsSeen')
			{
				this.userInterfaceManager.updateUnreadCounters(-resultIds.length);
			}
			else
			{
				this.userInterfaceManager.updateUnreadCounters(resultIds.length);
			}

			this.runAction(actionName, {
				ids: resultIds,
				keepRows: true,
				successParams: actionName,
				onSuccess: false
			});
		},
		onSpamClick: function (id)
		{
			if (id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0)
			{
				return;
			}
			if (!this.canMarkSpam)
			{
				this.showSettingsSlider();
				return;
			}
			var actionName = this.isSelectedRowsHaveClass('js-spam', id) ? 'restoreFromSpam' : 'markAsSpam';
			var resultIds = this.filterRowsByClassName('js-spam', id, actionName !== 'restoreFromSpam');
			resultIds = this.filterRowsByClassName(this.disabledClassName, resultIds, true);
			if (!resultIds.length)
			{
				return;
			}
			var options = {
				onSuccess: function ()
				{
					this.reloadGrid({});
				}
			};
			if (id !== undefined)
			{
				options.ids = [id];
			}
			this.runAction(actionName, options);
		},
		getConfirmDeletePopup: function (options)
		{
			if (!this.popupConfirm)
			{
				var buttons = [
					new BX.PopupWindowButton({
						text: BX.message("MAIL_MESSAGE_LIST_CONFIRM_CANCEL_BTN"),
						className: "popup-window-button-cancel",
						events: {
							click: BX.delegate(function ()
							{
								this.popupConfirm.close();
							}, this)
						}
					}),
					new BX.PopupWindowButton({
						text: BX.message("MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN"),
						className: "popup-window-button-decline",
						events: {
							click: BX.delegate(function ()
							{
								this.runAction('delete', options);
								this.popupConfirm.close();
							}, this)
						}
					})];
				this.popupConfirm = new BX.PopupWindow('bx-mail-message-list-popup-delete-confirm', null, {
					zIndex: 1000,
					autoHide: true,
					buttons: buttons,
					closeByEsc: true,
					titleBar: {
						content: BX.create('div', {
							html: '<span class="popup-window-titlebar-text">' + BX.message("MAIL_MESSAGE_LIST_CONFIRM_TITLE") + '</span>'
						})
					},
					events: {
						onPopupClose: function ()
						{
							this.destroy()
						},
						onPopupDestroy: BX.delegate(function ()
						{
							this.popupConfirm = null
						}, this)
					},
					content: BX.create("div", {
						html: BX.message('MAIL_MESSAGE_LIST_CONFIRM_DELETE')
					})
				});
				this.popupConfirm.selectedIds = options.ids;
			}
			return this.popupConfirm;
		},
		resetGridSelection: function ()
		{
			this.getGridInstance().getRows().unselectAll();
			// todo there is no other way to hide panel for now
			// please delete this line below
			document.querySelector('#pagetitle').click();
		},
		isSelectedRowsHaveClass: function (className, id)
		{
			var selectedIds = this.getGridInstance().getRows().getSelectedIds();
			var ids = selectedIds.length ? selectedIds : (id ? [id] : []);
			var resultIds = [];
			for (var i = 0; i < ids.length; i++)
			{
				var row = this.getGridInstance().getRows().getById(ids[i]);
				if (row && row.node)
				{
					var columns = row.node.getElementsByClassName(className);
					if (columns && columns.length)
					{
						return true;
					}
				}
			}
			return false;
		},
		filterRowsByClassName: function (className, ids, isReversed)
		{
			var resIds = [];
			if (Array.isArray(ids))
			{
				resIds = ids;
			}
			else
			{
				var selectedIds = this.getGridInstance().getRows().getSelectedIds();
				resIds = selectedIds.length ? selectedIds : (ids ? [ids] : []);
			}
			var resultIds = [];
			for (var i = resIds.length - 1; i >= 0; i--)
			{
				var row = this.getGridInstance().getRows().getById(resIds[i]);
				if (row && row.node)
				{
					var columns = row.node.getElementsByClassName(className);
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
		},
		notify: function (text)
		{
			BX.UI.Notification.Center.notify({
				autoHideDelay: 2000,
				content: text ? text : BX.message('MAIL_MESSAGE_LIST_NOTIFY_SUCCESS')
			});
		},
		runAction: function (actionName, options)
		{
			options = options ? options : {};
			var selectedIds = this.getGridInstance().getRows().getSelectedIds();

			if (options.ids)
			{
				selectedIds = options.ids;
			}
			if (!selectedIds.length)
			{
				return;
			}
			if (!options.keepRows)
			{
				this.getGridInstance().tableFade();
			}
			var data = {ids: selectedIds};
			if (options.params)
			{
				var optionsKeys = Object.keys(Object(options.params));
				for (var nextIndex = 0, len = optionsKeys.length; nextIndex < len; nextIndex++)
				{
					var nextKey = optionsKeys[nextIndex];
					var desc = Object.getOwnPropertyDescriptor(options.params, nextKey);
					if (desc !== undefined && desc.enumerable)
					{
						data[nextKey] = options.params[nextKey];
					}
				}
			}
			BX.ajax.runComponentAction('bitrix:mail.client', actionName, {
				mode: 'ajax',
				data: data
			}).then(
				function (response)
				{
					if (options.onSuccess === false)
					{
						return;
					}
					if (options.onSuccess && typeof(options.onSuccess) === "function")
					{
						options.onSuccess.bind(this, selectedIds, options.successParams)();
						return;
					}
					this.onSuccessRequest(response, actionName);
				}.bind(this),
				function (response)
				{
					options.onError && typeof(options.onError) === "function" ?
						options.onError().bind(this, response) :
						this.onErrorRequest(response)
				}.bind(this)
			);
		},
		onErrorRequest: function (response)
		{
			options = {};
			this.checkErrorRights(response.errors);
			options.errorMessage = response.errors[0].message;
			this.reloadGrid(options)
		},
		checkErrorRights: function (errors)
		{
			for (var i = 0; i < errors.length; i++)
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
		},
		onSuccessRequest: function (response, action)
		{
			this.notify();
			this.reloadGrid({});
		},
		reloadGrid: function (options)
		{
			var gridInstance = this.getGridInstance();
			if (gridInstance)
			{
				options.apply_filter = 'Y';
				gridInstance.reloadTable('POST', options);
			}
		},
		showSettingsSlider: function ()
		{
			// @TODO: path
			var url = BX.util.add_url_param("/mail/config/edit", {
				id: this.mailboxId + '#mail-cfg-dirs'
			});
			BX.SidePanel.Instance.open(url, {
				width: 760,
				cacheable: false,
				allowChangeHistory: false
			});
			this.canDelete = true;
			this.canMarkSpam = true;
		},
		onDisabledGroupActionClick: function ()
		{
		},
		onUnreadCounterClick: function ()
		{
			this.userInterfaceManager.onUnreadCounterClick();
		},
		onDirsMenuItemClick: function (el)
		{
			if (BX.data(el, 'is-disabled') == 'true')
			{
				return;
			}

			var filter = this.userInterfaceManager.getFilterInstance();

			var filterApi = filter.getApi();
			filterApi.setFields({
				'DIR': BX.data(el, 'folder-path')
			});
			filterApi.apply();

			this.userInterfaceManager.onMailboxMenuClick();
		},
		getGridInstance: function ()
		{
			return BX.Main.gridManager.getById(this.gridId).instance;
		}
	};
})();