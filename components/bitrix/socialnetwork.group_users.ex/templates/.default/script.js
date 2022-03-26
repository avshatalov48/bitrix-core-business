(function(){

	if (!!BX.BXGUE)
	{
		return;
	}

	BX.BXGUE = {
		groupId: null,
		menuPopup: null,
		errorBlock: null,
		ajaxUrl: '/bitrix/components/bitrix/socialnetwork.group_users.ex/ajax.php',
		popupHint: {}
	};

	BX.BXGUE.init = function(params) {
		if (
			typeof (params) == 'undefined'
			|| typeof (params.groupId) == 'undefined'
			|| parseInt(params.groupId) <= 0
		)
		{
			return;
		}

		this.groupId = params.groupId;

		if (typeof (params) != 'undefined')
		{
			if (
				BX.type.isNotEmptyString(params.errorBlockName)
				&& BX(params.errorBlockName)
			)
			{
				this.errorBlock = BX(params.errorBlockName);
			}

			if (typeof (params.styles) != 'undefined')
			{
				if (
					typeof params.styles.memberClass != 'undefined'
					&& typeof params.styles.memberClassOver != 'undefined'
					&& typeof params.styles.memberClassDelete != 'undefined'
				)
				{
					var userBlockArr = BX.findChildren(document, {
						className: params.styles.memberClass
					}, true);
					var
						deleteBlock = null;

					if (userBlockArr)
					{
						for (var i = userBlockArr.length - 1; i >= 0; i--)
						{

							BX.bind(userBlockArr[i], 'mouseover', function(e) {
								BX.addClass(e.currentTarget, params.styles.memberClassOver);
							});

							BX.bind(userBlockArr[i], 'mouseout', function(e) {
								BX.removeClass(e.currentTarget, params.styles.memberClassOver);
							});

							deleteBlock = BX.findChild(userBlockArr[i], {
								className: params.styles.memberClassDelete
							}, true);

							if (deleteBlock)
							{
								BX.bind(deleteBlock, 'click', BX.delegate(function(e) {
									var userBlock = BX.findParent(e.currentTarget, {
										className: params.styles.memberClass
									});

									if (
										userBlock
										&& BX.type.isNotEmptyString(userBlock.getAttribute('bx-action'))
									)
									{
										this.showDeleteButton({
											userBlock: userBlock,
											action: userBlock.getAttribute('bx-action')
										});
									}
								}, this));
							}
						}
					}
				}
			}
		}

		if (BX('sonet-members-actionlink-changeowner'))
		{
			BX.bind(BX('sonet-members-actionlink-changeowner'), 'click', BX.delegate(function(e) {
				BX.onCustomEvent(window, "BX.SonetGroupUsers:openDestDialog", [ { id: 'changeowner' } ]);
				e.preventDefault();
			}, this))
		}

		if (BX('sonet-members-actionlink-addmoderator'))
		{
			BX.bind(BX('sonet-members-actionlink-addmoderator'), 'click', BX.delegate(function(e) {
				BX.onCustomEvent(window, "BX.SonetGroupUsers:openDestDialog", [ { id: 'addmoderator' } ]);
				e.preventDefault();
			}, this))
		}

		this.initHint('sonet-members-auto-subtitle-hint');
	};

	BX.BXGUE.initHint = function(nodeId)
	{
		var node = BX(nodeId);
		if (!node)
		{
			return;
		}

		node.setAttribute('data-id', node);
		BX.bind(node, 'mouseover', BX.proxy(function(){
			var id = BX.proxy_context.getAttribute('data-id');
			var text = BX.proxy_context.getAttribute('data-text');
			this.showHint(id, BX.proxy_context, text);
		}, this));
		BX.bind(node, 'mouseout',  BX.proxy(function(){
			var id = BX.proxy_context.getAttribute('data-id');
			this.hideHint(id);
		}, this));
	};

	BX.BXGUE.showHint = function(id, bind, text)
	{
		if (this.popupHint[id])
		{
			this.popupHint[id].close();
		}

		this.popupHint[id] = new BX.PopupWindow('sonet-members-auto-hint-popup', bind, {
			lightShadow: true,
			autoHide: false,
			darkMode: true,
			offsetLeft: 9,
			offsetTop: -5,
			bindOptions: {position: "top"},
			zIndex: 200,
			events : {
				onPopupClose : function() {this.destroy()}
			},
			content : BX.create("div", { attrs : { style : "padding-right: 5px; width: 250px;" }, html: text})
		});
		this.popupHint[id].setAngle({offset:13, position: 'bottom'});
		this.popupHint[id].show();

		return true;
	};

	BX.BXGUE.hideHint = function(id)
	{
		this.popupHint[id].close();
		this.popupHint[id] = null;
	};

	BX.BXGUE.showDeleteButton = function(params)
	{
		var
			userBlock = null,
			action = null;

		if (
			typeof params.userBlock != 'undefined'
			&& BX(params.userBlock)
		)
		{
			userBlock = params.userBlock;
		}

		if (
			typeof params.action != 'undefined'
			&& BX.type.isNotEmptyString(params.action)
		)
		{
			action = params.action;
		}

		if (
			!userBlock
			|| !action
		)
		{
			return;
		}

		var f = BX.delegate(function(e) {
			var entityId = parseInt(BX.getEventTarget(e).getAttribute('bx-entity-id'));
			if (entityId > 0)
			{
				this.doAction({
					entityId: entityId,
					action: action,
					buttonNode: BX.getEventTarget(e),
					callback: {
						success: BX.delegate(function ()
						{
							userBlock.style.display = 'none';
						})
					}
				});
			}
			else
			{
				this.hideDeleteButton({
					userBlock: userBlock
				});
				window.removeEventListener('click', f, true);
			}
			e.preventDefault();
		}, this);

		if (
			typeof params.userBlock != 'undefined'
			&& BX(params.userBlock)
		)
		{
			BX.addClass(BX(params.userBlock), 'delete');
			window.addEventListener('click', f, true);
		}
	};

	BX.BXGUE.hideDeleteButton = function(params)
	{
		if (
			typeof params.userBlock != 'undefined'
			&& BX(params.userBlock)
		)
		{
			BX.removeClass(BX(params.userBlock), 'delete');
		}
	};

	BX.BXGUE.sendAjax = function(data, params)
	{
		if (data.items.length > 0)
		{
			if (
				typeof params == 'undefined'
				|| typeof params.showWait == 'undefined'
				|| params.showWait
			)
			{
				BX.SocialnetworkUICommon.Waiter.getInstance().show();
			}

			var requestData = {
				ACTION: data.action,
				GROUP_ID: parseInt(BX.message('GUEGroupId')),
				sessid: BX.bitrix_sessid(),
				site: BX.util.urlencode(BX.message('SITE_ID'))
			};

			if (data.action == 'UNCONNECT_DEPT')
			{
				requestData.DEPARTMENT_ID = data.items;
			}
			else
			{
				requestData.USER_ID = data.items;
			}

			BX.ajax({
				url: this.ajaxUrl,
				method: 'POST',
				dataType: 'json',
				data: requestData,
				onsuccess: BX.proxy(function(responseData) {
					if (
						typeof params != 'undefined'
						&& typeof params.callback != 'undefined'
						&& typeof params.callback.success != 'undefined'
					)
					{
						params.callback.success(responseData);
					}
					else
					{
						this.processAJAXResponse(responseData, data.popup);
					}
				}, this),
				onfailure: BX.delegate(function() {
					if (
						typeof params != 'undefined'
						&& typeof params.callback != 'undefined'
						&& typeof params.callback.failure != 'undefined'
					)
					{
						params.callback.failure();
					}
				}, this)
			});
		}
		else
		{
			this.showError(BX.message(data.action == 'UNCONNECT_DEPT' ? 'GUEErrorDepartmentIDNotDefined' : 'GUEErrorUserIDNotDefined'));
		}
	};

	BX.BXGUE.processAJAXResponse = function(data, popup)
	{
		if (
			popup == 'undefined'
			|| popup == null
			|| !popup.isShown()
		)
		{
			return false;
		}

		if (
			typeof data.SUCCESS != "undefined"
			&& data.SUCCESS == "Y"
		)
		{
			popup.close();
			BX.reload();
		}
		else if (data["ERROR"] != "undefined" && data["ERROR"].length > 0)
		{
			if (data["ERROR"].indexOf("USER_ACTION_FAILED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorActionFailedPattern').replace("#ERROR#", data["ERROR"].substr(20)));
				return false;
			}
			else if (data["ERROR"].indexOf("SESSION_ERROR", 0) === 0)
			{
				this.showError(BX.message('GUEErrorSessionWrong'));
				BX.reload();
			}
			else if (data["ERROR"].indexOf("USER_GROUP_NO_PERMS", 0) === 0)
			{
				this.showError(BX.message('GUEErrorNoPerms'));
				return false;
			}
			else if (data["ERROR"].indexOf("USER_ID_NOT_DEFINED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorUserIDNotDefined'));
				return false;
			}
			else if (data["ERROR"].indexOf("DEPARTMENT_ID_NOT_DEFINED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorDepartmentIDNotDefined'));
				return false;
			}
			else if (data["ERROR"].indexOf("GROUP_ID_NOT_DEFINED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorGroupIDNotDefined'));
				return false;
			}
			else if (data["ERROR"].indexOf("CURRENT_USER_NOT_AUTH", 0) === 0)
			{
				this.showError(BX.message('GUEErrorCurrentUserNotAuthorized'));
				return false;
			}
			else if (data["ERROR"].indexOf("SONET_MODULE_NOT_INSTALLED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorModuleNotInstalled'));
				return false;
			}
			else if (data["ERROR"].indexOf("SONET_GUE_T_OWNER_CANT_EXCLUDE_HIMSELF", 0) === 0)
			{
				this.showError(BX.message('GUEErrorOwnerCantExcludeHimself'));
				return false;
			}
			else if (data["ERROR"].indexOf("SONET_GUE_T_CANT_EXCLUDE_AUTO_MEMBER", 0) === 0)
			{
				this.showError(BX.message('GUEErrorCantExcludeAutoMember'));
				return false;
			}
			else if (data["ERROR"].indexOf("DEPARTMENT_ACTION_FAILED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorActionFailedPattern').replace("#ERROR#", data["ERROR"].substr(26)));
				return false;
			}
			else
			{
				this.showError(data["ERROR"]);
				return false;
			}
		}
	};

	BX.BXGUE.showError = function(errorText)
	{
		BX.SocialnetworkUICommon.Waiter.getInstance().hide();
		var errorPopup = new BX.PopupWindow('gue-error' + Math.random(), window, {
			autoHide: true,
			lightShadow: false,
			zIndex: 2,
			content: BX.create('DIV', {props: {'className': 'sonet-members-error-text-block'}, html: errorText}),
			closeByEsc: true,
			closeIcon: true
		});
		errorPopup.show();
	};

	BX.BXGUE.doAction = function(params)
	{
		if (
			typeof params == 'undefined'
			|| typeof params.entityId == 'undefined'
			|| parseInt(params.entityId) <= 0
			|| !BX.type.isNotEmptyString(params.action)
			|| parseInt(this.groupId) <= 0
		)
		{
			return;
		}

		var
			entityId = parseInt(params.entityId),
			ajaxAction = null,
			eventCode = null;

		if (params.action == 'exclude')
		{
			ajaxAction = 'EX';
			eventCode = 'afterUserExclude';
		}
		else if (params.action == 'unban')
		{
			ajaxAction = 'UNBAN';
			eventCode = 'afterUserUnban';
		}
		else if (params.action == 'unconnect')
		{
			ajaxAction = 'UNCONNECT_DEPT';
			eventCode = 'afterDeptUnconnect';
		}
		else if (params.action == 'setowner')
		{
			ajaxAction = 'SETOWNER';
			eventCode = 'afterOwnerSet';
		}
		else if (params.action == 'addmoderator')
		{
			ajaxAction = 'ADDMODERATOR';
			eventCode = 'afterModeratorAdd';
		}
		else if (params.action == 'removemod')
		{
			ajaxAction = 'M2U';
			eventCode = 'afterModeratorRemove';
		}

		if (ajaxAction)
		{
			if (
				typeof params.buttonNode != 'undefined'
				&& BX(params.buttonNode)
			)
			{
				BX.SocialnetworkUICommon.showButtonWait(BX(params.buttonNode));
			}

			this.sendAjax({
					action: ajaxAction,
					items: [ entityId ]
				},
				{
					showWait: (
						params.action == 'setowner'
						|| params.action == 'addmoderator'
					),
					callback: {
						success: BX.delegate(function (responseData) {

							if (
								typeof params.buttonNode != 'undefined'
								&& BX(params.buttonNode)
							)
							{
								BX.SocialnetworkUICommon.hideButtonWait(BX(params.buttonNode));
							}

							if (
								typeof responseData.SUCCESS != "undefined"
								&& responseData.SUCCESS == "Y"
							)
							{
								if (
									eventCode
									&& window !== top.window
								) // frame
								{
									window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', {
										code: eventCode,
										data: {
											entityId: entityId,
											groupId: this.groupId
										}
									});
								}

								if (
									typeof params.callback != 'undefined'
									&& typeof params.callback.success == 'function'
								)
								{
									params.callback.success();
								}

								BX.SocialnetworkUICommon.reload();
							}
							else if (
								typeof responseData.ERROR != "undefined"
								&& BX.type.isNotEmptyString(responseData.ERROR)
								&& this.errorBlock
							)
							{
								var errorMessage = '';
								if (responseData.ERROR.indexOf("USER_ACTION_FAILED", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorActionFailedPattern').replace("#ERROR#", responseData.ERROR.substr(20));
								}
								else if (responseData.ERROR.indexOf("SESSION_ERROR", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorSessionWrong');
								}
								else if (responseData.ERROR.indexOf("USER_GROUP_NO_PERMS", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorNoPerms');
								}
								else if (responseData.ERROR.indexOf("USER_ID_NOT_DEFINED", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorUserIDNotDefined');
								}
								else if (responseData.ERROR.indexOf("DEPARTMENT_ID_NOT_DEFINED", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorDepartmentIDNotDefined');
								}
								else if (responseData.ERROR.indexOf("GROUP_ID_NOT_DEFINED", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorGroupIDNotDefined');
								}
								else if (responseData.ERROR.indexOf("CURRENT_USER_NOT_AUTH", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorCurrentUserNotAuthorized');
								}
								else if (responseData.ERROR.indexOf("SONET_MODULE_NOT_INSTALLED", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorModuleNotInstalled');
								}
								else if (responseData.ERROR.indexOf("SONET_GUE_T_OWNER_CANT_EXCLUDE_HIMSELF", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorOwnerCantExcludeHimself');
								}
								else if (responseData.ERROR.indexOf("SONET_GUE_T_CANT_EXCLUDE_AUTO_MEMBER", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorCantExcludeAutoMember');
								}
								else if (responseData.ERROR.indexOf("DEPARTMENT_ACTION_FAILED", 0) === 0)
								{
									errorMessage = BX.message('GUEErrorActionFailedPattern').replace("#ERROR#", responseData.ERROR.substr(26));
								}
								else
								{
									errorMessage = responseData.ERROR;
								}

								if (
									typeof params.callback != 'undefined'
									&& typeof params.callback.failure == 'function'
								)
								{
									params.callback.failure();
								}

								if (BX.type.isNotEmptyString(errorMessage))
								{
									BX.SocialnetworkUICommon.showError(errorMessage, this.errorBlock);
								}
							}
						}, this),
						failure: BX.delegate(function () {

							if (
								typeof params.buttonNode != 'undefined'
								&& BX(params.buttonNode)
							)
							{
								BX.SocialnetworkUICommon.hideButtonWait(BX(params.buttonNode));
							}

							if (
								typeof params.callback != 'undefined'
								&& typeof params.callback.failure == 'function'
							)
							{
								params.callback.failure();
							}

							if (this.errorBlock)
							{
								BX.SocialnetworkUICommon.showError(BX.message('SONET_EXT_COMMON_AJAX_ERROR'), this.errorBlock);
							}
						}, this)
					}
				});
		}
	};

	BX.BXGUE.showActionWait = function(params)
	{
		if (
			typeof params == 'undefined'
			|| typeof params.node == 'undefined'
			|| !BX(params.node)
			|| !BX.type.isNotEmptyString(params.className)
		)
		{
			return;
		}

		BX.addClass(BX(params.node), params.className);
		BX(params.node).disabled = true;
	};

	BX.BXGUE.hideActionWait = function(params)
	{
		if (
			typeof params == 'undefined'
			|| typeof params.node == 'undefined'
			|| !BX(params.node)
			|| !BX.type.isNotEmptyString(params.className)
		)
		{
			return;
		}

		BX.removeClass(BX(params.node), params.className);
		BX(params.node).disabled = false;
	};

	BX.BXGUEDestinationSelectorManager = {

		data: {
			changeowner: {
				multiple: false,
				containterId: 'sonet-members-container-changeowner',
				value: null
			},
			addmoderator: {
				multiple: true,
				containterId: 'sonet-members-container-addmoderator',
				value: null
			}
		},

		onSelect: function(params)
		{
			if (
				typeof params == 'undefined'
				|| !BX.type.isNotEmptyString(params.selectorId)
				|| !BX.type.isNotEmptyObject(params.item)
			)
			{
				return;
			}

			var
				name = params.selectorId,
				item = params.item;

			if (typeof BX.BXGUEDestinationSelectorManager.data[name] == 'undefined')
			{
				return;
			}

			var multiple = BX.BXGUEDestinationSelectorManager.data[name].multiple;

			if (
				typeof params.state != 'undefined'
				&& params.state == 'init'
			)
			{
				if (!multiple)
				{
					BX.BXGUEDestinationSelectorManager.data[name].value = item.id;
				}
				else
				{
					if (BX.BXGUEDestinationSelectorManager.data[name].value === null)
					{
						BX.BXGUEDestinationSelectorManager.data[name].value = [];
					}
					BX.BXGUEDestinationSelectorManager.data[name].value.push(item.id);
				}

				return;
			}

			if (
				(
					!multiple
					&& item.id != BX.BXGUEDestinationSelectorManager.data[name].value
				)
				|| (
					multiple
					&& !BX.util.in_array(item.id, BX.BXGUEDestinationSelectorManager.data[name].value)
				)
			)
			{
				if (!multiple)
				{
					BX.BXGUEDestinationSelectorManager.data[name].value = item.id;
				}
				else
				{
					BX.BXGUEDestinationSelectorManager.data[name].value.push(item.id);
				}

				var matches = item.id.match(/^U(\d+)/);
				if (matches)
				{
					if (name == 'changeowner')
					{
						BX.BXGUE.doAction({
							entityId: matches[1],
							action: 'setowner',
							entityNode: BX('sonet-members-member-block-owner')
						});
					}
					else if (name == 'addmoderator')
					{
						BX.BXGUE.doAction({
							entityId: matches[1],
							action: 'addmoderator',
							entityNode: BX('sonet-members-member-block-mod-' + matches[1])
						});
					}
				}
			}
		},

		onDialogOpen: function(params)
		{
			if (
				typeof params == 'undefined'
				|| !BX.type.isNotEmptyString(params.selectorId)
			)
			{
				return;
			}

			var item = BX.BXGUEDestinationSelector.items[params.selectorId];
			if(item)
			{
				item.onDialogOpen();
			}
		},

		onDialogClose: function(params)
		{
			if (
				typeof params == 'undefined'
				|| !BX.type.isNotEmptyString(params.selectorId)
			)
			{
				return;
			}

			var item = BX.BXGUEDestinationSelector.items[params.selectorId];
			if (item)
			{
				item.onDialogClose();
			}
		}
	};

	BX.BXGUEDestinationSelector = function ()
	{
		this.id = "";
		this.settings = {};
		this.fieldId = "";
		this.control = null;
		this.inited = null;
	};

	BX.BXGUEDestinationSelector.items = {};

	BX.BXGUEDestinationSelector.create = function(id, settings)
	{
		var self = new BX.BXGUEDestinationSelector(id, settings);
		self.initialize(id, settings);
		this.items[id] = self;
		BX.onCustomEvent(window, 'BX.SonetGroupUsers:create', [ id ]);
		return self;
	};

	BX.BXGUEDestinationSelector.prototype.initialize = function(id, settings)
	{
		this.id = id;
		this.settings = settings ? settings : {};
		this.fieldId = this.getSetting("fieldId", "");
		this.inited = false;
		this.opened = null;

		BX.addCustomEvent(window, "BX.SonetGroupUsers:openDestDialog", BX.delegate(this.onSelectorOpen, this));
		BX.addCustomEvent(window, "BX.Main.SelectorV2:beforeInitDialog", BX.delegate(this.onBeforeInitDialog, this));
	};

	BX.BXGUEDestinationSelector.prototype.getSetting = function(name, defaultval)
	{
		return this.settings.hasOwnProperty(name) ? this.settings[name] : defaultval;
	};

	BX.BXGUEDestinationSelector.prototype.open = function()
	{
		if (!this.inited)
		{
			BX.addCustomEvent(window, "BX.Main.SelectorV2:afterInitDialog", BX.delegate(function(params) {
				if (
					typeof params.id != 'undefined'
					|| params.id != this.id
				)
				{
					return;
				}

				this.opened = true;
			}, this));

			BX.onCustomEvent(window, 'BX.SonetGroupUsers:openInit', [ {
				id: this.id,
				openDialogWhenInit: true,
				containerId: BX.BXGUEDestinationSelectorManager.data[this.id].containterId
			} ]);
		}
		else
		{
			BX.onCustomEvent(window, 'BX.SonetGroupUsers:open', [ {
				id: this.id,
				bindNode: BX(BX.BXGUEDestinationSelectorManager.data[this.id].containterId)
			} ]);

			this.opened = true;
		}
	};

	BX.BXGUEDestinationSelector.prototype.close = function()
	{
	};

	BX.BXGUEDestinationSelector.prototype.onSelectorOpen = function(params)
	{
		var id = (
			typeof params != 'undefined'
			&& typeof params.id != 'undefined'
				? params.id
				: false
		);

		if (
			!id
			|| id != this.id
		)
		{
			return;
		}

		if (!this.opened)
		{
			this.open();
		}
		else
		{
			this.close();
		}
	};

	BX.BXGUEDestinationSelector.prototype.onDialogOpen = function()
	{
		this.opened = true;
	};

	BX.BXGUEDestinationSelector.prototype.onDialogClose = function()
	{
		this.opened = false;
	};

	BX.BXGUEDestinationSelector.prototype.onBeforeInitDialog = function(params)
	{
		if (
			typeof params.id == 'undefined'
			|| params.id != this.id
		)
		{
			return;
		}

		this.inited = true;
	};

})();