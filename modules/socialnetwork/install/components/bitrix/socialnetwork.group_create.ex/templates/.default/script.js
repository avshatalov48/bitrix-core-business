function BXSwitchProject(isChecked)
{
	BX.BXGCE.recalcFormPartProject(isChecked);
}

function BXSwitchNotVisible(isChecked)
{
	if (
		BX("GROUP_OPENED")
		&& BX('GROUP_OPENED').type == 'checkbox'
	)
	{
		if (isChecked)
		{
			BX("GROUP_OPENED").disabled = false;
		}
		else
		{
			BX("GROUP_OPENED").disabled = true;
			BX("GROUP_OPENED").checked = false;
		}
	}
}

function BXSwitchExtranet(isChecked, useAnimation)
{
	if (BX("INVITE_EXTRANET_block"))
	{
		if (isChecked)
		{
			BX('INVITE_EXTRANET_block_container').style.display = 'block';
		}

		BX.BXGCE.showHideBlock({
			container: BX('INVITE_EXTRANET_block_container'),
			block: BX('INVITE_EXTRANET_block'),
			show: isChecked,
			duration: (useAnimation ? 1000 : 0),
			callback: {
				complete: function() {
					if (isChecked)
					{
						BX.removeClass(BX('INVITE_EXTRANET_block_container'), 'invisible');
					}
					else
					{
						BX('INVITE_EXTRANET_block_container').style.display = 'none';
						BX.addClass(BX('INVITE_EXTRANET_block_container'), 'invisible');
					}
				}
			}
		});
	}

	if (BX('GROUP_OPENED'))
	{
		if (!isChecked)
		{
			if (BX('GROUP_OPENED').type == 'checkbox')
			{
				BX("GROUP_OPENED").disabled = false;
			}
		}
		else
		{
			if (BX('GROUP_OPENED').type == 'checkbox')
			{
				BX("GROUP_OPENED").disabled = true;
				BX("GROUP_OPENED").checked = false;
			}
			else
			{
				BX("GROUP_OPENED").value = 'N';
			}
		}
	}

	if (BX('GROUP_VISIBLE'))
	{
		if (!isChecked)
		{
			if (BX('GROUP_VISIBLE').type == 'checkbox')
			{
				BX("GROUP_VISIBLE").disabled = false;
			}
		}
		else
		{
			if (BX('GROUP_VISIBLE').type == 'checkbox')
			{
				BX("GROUP_VISIBLE").disabled = true;
				BX("GROUP_VISIBLE").checked = false;
			}
			else
			{
				BX("GROUP_VISIBLE").value = 'N';
			}
		}
	}

	if (
		BX("GROUP_INITIATE_PERMS")
		&& BX("GROUP_INITIATE_PERMS_OPTION_E")
		&& BX("GROUP_INITIATE_PERMS_OPTION_K")
	)
	{
		if (isChecked)
		{
			BX("GROUP_INITIATE_PERMS_OPTION_E").selected = true;
		}
		else
		{
			BX("GROUP_INITIATE_PERMS_OPTION_K").selected = true;
		}
	}

	if (
		BX("GROUP_INITIATE_PERMS_PROJECT")
		&& BX("GROUP_INITIATE_PERMS_OPTION_PROJECT_E")
		&& BX("GROUP_INITIATE_PERMS_OPTION_PROJECT_K")
	)
	{
		if (isChecked)
		{
			BX("GROUP_INITIATE_PERMS_OPTION_PROJECT_E").selected = true;
		}
		else
		{
			BX("GROUP_INITIATE_PERMS_OPTION_PROJECT_K").selected = true;
		}
	}
	
	if (BX("USERS_employee_section_extranet"))
	{
		BX("USERS_employee_section_extranet").style.display = (isChecked ? "inline-block" : "none");
	}
}

function BXGCESubmitForm(e)
{
	if (BX('EXTRANET_INVITE_ACTION'))
	{
		BX('EXTRANET_INVITE_ACTION').value = BX.BXGCE.lastAction;
	}

	var actionURL = BX("sonet_group_create_popup_form").action;

	if (actionURL)
	{
		if (
			BX('SONET_GROUP_ID')
			&& parseInt(BX('SONET_GROUP_ID').value) <= 0
		)
		{
			actionURL += (actionURL.indexOf('?') >= 0 ? '&' : '?') + 'action=createGroup&groupType=' + BX.BXGCE.selectedTypeCode;
		}

		BX.BXGCE.disableSubmitButton(true);

		BX.ajax.submitAjax(
			document.forms.sonet_group_create_popup_form,
			{
				url: actionURL,
				method: 'POST',
				dataType: 'json',
				onsuccess: function(obResponsedata)
				{
					if (
						typeof obResponsedata["ERROR"] != 'undefined'
						&& obResponsedata["ERROR"].length > 0
					)
					{
						BX.BXGCE.showError(
							(
								typeof obResponsedata["WARNING"] != 'undefined'
								&& obResponsedata["WARNING"].length > 0
									? obResponsedata["WARNING"] + '<br>'
									: ''
							) + obResponsedata["ERROR"]
						);

						if (
							typeof BX.SocNetLogDestination.obItems != 'undefined'
							&& typeof obResponsedata["USERS_ID"] != 'undefined'
							&& BX.type.isArray(obResponsedata["USERS_ID"])
						)
						{
							var selectedUsersOld = false;
							var selectedUsers = [];
							var strUserCodeTmp = false;
							var j = 0;

							for (j = 0; j < obResponsedata["USERS_ID"].length; j++)
							{
								selectedUsers['U' + obResponsedata['USERS_ID'][j]] = 'users';
							}

							if (BX.BXGCE.arUserSelector.length > 0)
							{
								for (var i = 0; i < BX.BXGCE.arUserSelector.length; i++)
								{
									selectedUsersOld = BX.findChildren(BX('sonet_group_create_popup_users_item_post_' + BX.BXGCE.arUserSelector[i]), { className: "feed-add-post-destination-users" }, true);
									if (selectedUsersOld)
									{
										for (j = 0; j < selectedUsersOld.length; j++)
										{
											strUserCodeTmp = selectedUsersOld[j].getAttribute('data-id');
											if (
												strUserCodeTmp
												&& strUserCodeTmp.length > 0
											)
											{
												BX.SocNetLogDestination.deleteItem(strUserCodeTmp, 'users', BX.BXGCE.arUserSelector[i]);
											}
										}
									}

									BX.SocNetLogDestination.obItemsSelected[BX.BXGCE.arUserSelector[i]] = selectedUsers;
									BX.SocNetLogDestination.reInit(BX.BXGCE.arUserSelector[i]);
								}
							}
						}

						BX.BXGCE.disableSubmitButton(false);
					}
					else if (obResponsedata["MESSAGE"] == 'SUCCESS')
					{
						if (window === top.window) // not frame
						{
							if (
								typeof obResponsedata["URL"] !== 'undefined'
								&& obResponsedata["URL"].length > 0
							)
							{
								top.location.href = obResponsedata["URL"];
							}
						}
						else // frame
						{
							if (typeof obResponsedata.ACTION != 'undefined')
							{
								var eventData = false;

								if (
									BX.util.in_array(obResponsedata.ACTION, ['create', 'edit'])
									&& typeof obResponsedata.GROUP != 'undefined'
								)
								{
									eventData = {
										code: (obResponsedata.ACTION == 'create' ? 'afterCreate' : 'afterEdit'),
										data: {
											group: obResponsedata.GROUP
										}
									};
								}
								else if (BX.util.in_array(obResponsedata.ACTION, ['invite']))
								{
									eventData = {
										code: 'afterInvite',
										data: {}
									};
								}

								if (eventData)
								{
									window.top.BX.SidePanel.Instance.postMessageAll(window, 'sonetGroupEvent', eventData);
									BX.SidePanel.Instance.close();

									if (
										obResponsedata.ACTION == 'create'
										&& BX.type.isNotEmptyString(obResponsedata.URL)
										&& (
											!BX.type.isNotEmptyString(BX.BXGCE.config.refresh)
											|| BX.BXGCE.config.refresh == 'Y'
										)
									)
									{
										top.window.location.href = obResponsedata.URL;
									}
								}
								else
								{
									BX.SocialnetworkUICommon.reload();

									window.top.BX.onCustomEvent("BX.Bitrix24.PageSlider:close", [false]);
									window.top.BX.onCustomEvent("SidePanel.Slider:onClose", [false]);
									window.top.BX.onCustomEvent('onSonetIframeCancelClick');
								}
							}
						}
					}
				},
				onfailure: function(errorData) {
					BX.BXGCE.disableSubmitButton(false);
					BX.BXGCE.showError(BX.message('SONET_GCE_T_AJAX_ERROR'));
				}
			}
		);
	}

	e.preventDefault();
}

function __deleteExtranetEmail(item)
{
	var flag = false;

	if (!item || !BX.type.isDomNode(item))
		item = this;

	if (item)
	{
		BX(item).parentNode.parentNode.removeChild(BX(item).parentNode);
		var num = parseInt(BX(item).parentNode.id.substring(36));
		top.BXExtranetMailList[num-1] = '';

		BX('EMAILS').value = '';
		for(var i=0; i<top.BXExtranetMailList.length; i++)
		{
			if (top.BXExtranetMailList[i].length > 0)
			{
				if (flag)
					BX('EMAILS').value += ', ';

				BX('EMAILS').value += top.BXExtranetMailList[i];
				flag = true;
			}
		}
	}
}

(function(){

if (!!BX.BXGCE)
{
	return;
}

BX.BXGCE = {
	config: {
		refresh: 'Y'
	},
	groupId: null,
	userSelector: '',
	lastAction: 'invite',
	arUserSelector: [],
	formSteps: 2,
	animationList: {},
	selectedTypeCode: false
};

BX.BXGCE.init = function(params) {

	if (typeof (params) != 'undefined')
	{
		if (typeof (params.groupId) != 'undefined')
		{
			this.groupId = parseInt(params.groupId);
		}

		if (typeof (params.config) != 'undefined')
		{
			this.config = params.config;
		}
	}

	var
		i = null,
		cnt = null;

	if (BX('sonet_group_create_form_step_1'))
	{
		var tiles = BX.findChildren(BX('sonet_group_create_form_step_1'), {
			className : "social-group-tile-item"
		}, true);
		for (i = 0, cnt = tiles.length; i < cnt; i++ )
		{
			BX.bind(tiles[i], "click", BX.delegate(function(e) {
				var node = e.currentTarget;

				var typeCode = this.selectedTypeCode = node.getAttribute('bx-type');

				if (BX.type.isNotEmptyString(typeCode))
				{
					this.showStep({
						step: 2
					});

					if (BX('GROUP_NAME_input'))
					{
						BX('GROUP_NAME_input').focus();
					}

					this.recalcForm({
						type: typeCode
					});
				}
				e.preventDefault();
			}, this));

		}
	}

	if (BX('additional-block-features'))
	{
		var editButtonsList = BX.findChildren(BX('additional-block-features'), {
			className : "social-group-create-form-pencil"
		}, true);
		for (i = 0, cnt = editButtonsList.length; i < cnt; i++ )
		{
			BX.bind(editButtonsList[i], "click", BX.delegate(function(e) {
				var node = e.currentTarget;
				var featureNode = BX.findParent(node, {
					className: 'social-group-create-form-field-list-item'
				}, BX('additional-block-features'));
				if (featureNode)
				{
					BX.addClass(featureNode, 'custom-value');
				}
				var inputNode = BX.findChild(featureNode, {
					className: 'social-group-create-form-field-input-text'
				}, true);
				var textNode = BX.findChild(featureNode, {
					className: 'social-group-create-form-field-list-label'
				}, true);
				if (
					inputNode
					&& textNode
				)
				{
					inputNode.value = textNode.innerText;
				}

				e.preventDefault();
			}, this));
		}

		var cancelButtonsList = BX.findChildren(BX('additional-block-features'), {
			className : "social-group-create-form-field-cancel"
		}, true);
		for (i = 0, cnt = cancelButtonsList.length; i < cnt; i++ )
		{
			BX.bind(cancelButtonsList[i], "click", BX.delegate(function(e) {
				var node = e.currentTarget;
				var featureNode = BX.findParent(node, {
					className: 'social-group-create-form-field-list-item'
				}, BX('additional-block-features'));
				if (featureNode)
				{
					BX.removeClass(featureNode, 'custom-value');
				}

				var inputNode = BX.findChild(featureNode, {
					className: 'social-group-create-form-field-input-text'
				}, true);
				if (inputNode)
				{
					inputNode.value = '';
				}

				e.preventDefault();
			}, this));
		}
	}

	if (BX('GROUP_NAME_input'))
	{
		BX('GROUP_NAME_input').focus();
	}

	BX.bind(BX('sonet_group_create_popup_form_button_step_2_back'), 'click', BX.delegate(function(e) {
		this.showStep({
			step: 1
		});

		return e.preventDefault();
	}, this));

	BX.bind(BX("sonet_group_create_popup_form_button_submit"), "click", BXGCESubmitForm);

		window.top.BX.onCustomEvent("SidePanel.Slider:onClose", [false]);
	BX.bind(BX("sonet_group_create_popup_form_button_step_2_cancel"), "click", function(e) {
		window.top.BX.onCustomEvent("BX.Bitrix24.PageSlider:close", [false]);
		window.top.BX.onCustomEvent("SidePanel.Slider:onClose", [false]);
		window.top.BX.onCustomEvent('onSonetIframeCancelClick');

		return e.preventDefault();
	});

	if (BX.SidePanel.Instance.getTopSlider())
	{
		BX.addCustomEvent(
			BX.SidePanel.Instance.getTopSlider().getWindow(),
			"SidePanel.Slider:onClose",
			function (event)
			{
				setTimeout(function(){ BX.SidePanel.Instance.destroy(event.getSlider().getUrl()) }, 500);
			}
		);
	}

	BX.bind(BX("GROUP_INITIATE_PERMS"), "change", BX.BXGCE.onInitiatePermsChange);
	BX.bind(BX("GROUP_INITIATE_PERMS_PROJECT"), "change", BX.BXGCE.onInitiatePermsChange);

	if (
		BX('GROUP_MODERATORS_switch')
		&& BX('GROUP_MODERATORS_PROJECT_switch')
	)
	{
		var func = BX.delegate(function() {
			var show = BX.hasClass(BX('GROUP_MODERATORS_block_container'), 'invisible');
			if (show)
			{
				BX('GROUP_MODERATORS_block_container').style.display = 'block';
			}

			this.showHideBlock({
				container: BX('GROUP_MODERATORS_block_container'),
				block: BX('GROUP_MODERATORS_block'),
				show: show,
				duration: 500,
				callback: {
					complete: function() {
						if (!show)
						{
							BX('GROUP_MODERATORS_block_container').style.display = 'none';
						}
						BX.toggleClass(BX('GROUP_MODERATORS_block_container'), 'invisible');
					}
				}
			});
		}, this);

		BX.bind(BX('GROUP_MODERATORS_switch'), 'click', func);
		BX.bind(BX('GROUP_MODERATORS_PROJECT_switch'), 'click', func);
	}

	if (
		BX('IS_EXTRANET_GROUP')
		&& BX('IS_EXTRANET_GROUP').type == 'checkbox'
	)
	{
		BX.bind(BX('IS_EXTRANET_GROUP'), 'click', function() {
			BXSwitchExtranet(BX('IS_EXTRANET_GROUP').checked, true);
		});
	}

	if (
		BX('GROUP_VISIBLE')
		&& BX('GROUP_VISIBLE').type == 'checkbox'
	)
	{
		BX.bind(BX('GROUP_VISIBLE'), 'click', function() {
			BXSwitchNotVisible(BX('GROUP_VISIBLE').checked)
		});
	}

	if (BX('switch_additional'))
	{
		BX.bind(BX('switch_additional'), 'click', BX.delegate(function(e) {

			var blockId = BX.getEventTarget(e).getAttribute('bx-block-id');
			if (BX.type.isNotEmptyString(blockId))
			{
				if (!BX.hasClass(BX('switch_additional'), 'opened'))
				{
					this.onToggleAdditionalBlock({
						callback: BX.delegate(function() {
							this.highlightAdditionalBlock(blockId);
						}, this)
					});
				}
				else
				{
					this.highlightAdditionalBlock(blockId);
				}
			}
			else
			{
				this.onToggleAdditionalBlock();
			}
		}, this));
	}

	if (
		BX.type.isNotEmptyString(params.avatarUploaderId)
		&& BX('GROUP_IMAGE_ID_block')
		&& typeof BX.UploaderManager != 'undefined'
	)
	{
		setTimeout(function() { // not async
			var uploaderInstance = BX.UploaderManager.getById(params.avatarUploaderId);
			if (uploaderInstance)
			{
				BX.addCustomEvent(uploaderInstance, "onQueueIsChanged", function(uploaderInstance, action, fileId, file) {
					if (action == 'add')
					{
						BX.addClass(BX('GROUP_IMAGE_ID_block'), 'social-group-create-link-upload-set');
					}
					else if (action == 'delete')
					{
						BX.removeClass(BX('GROUP_IMAGE_ID_block'), 'social-group-create-link-upload-set');
					}
				});
			}
		}, 0);
	}
};

BX.BXGCE.onToggleAdditionalBlock = function(params) {
	BX.toggleClass(BX('switch_additional'), 'opened');

	var show = BX.hasClass(BX('block_additional'), 'invisible');

	if (show)
	{
		BX('block_additional').style.display = 'block';
	}

	this.showHideBlock({
		container: BX('block_additional'),
		block: BX('block_additional_inner'),
		show: show,
		duration: 1000,
		callback: {
			complete: function()
			{
				BX.toggleClass(BX('block_additional'), 'invisible');

				if (
					typeof params != 'undefined'
					&& typeof params.callback == 'function'
				)
				{
					if (!show)
					{
						BX('block_additional').style.display = 'none';
					}
					params.callback();
				}
			}
		}
	});
};

BX.BXGCE.showHideBlock = function(params) {

	if (typeof params == 'undefined')
	{
		return false;
	}

	var containerNode = (typeof params.container != 'undefined' ? BX(params.container) : false);
	var blockNode = (typeof params.block != 'undefined' ? BX(params.block) : false);
	var show = !!params.show;

	if (
		!containerNode
		|| !blockNode
	)
	{
		return false;
	}

	if (
		typeof this.animationList[blockNode.id] != 'undefined'
		&& this.animationList[blockNode.id] != null
	)
	{
		return false;
	}

	this.animationList[blockNode.id] = null;

	var maxHeight = parseInt(blockNode.offsetHeight);
	var duration = (typeof params.duration != 'undefined' && parseInt(params.duration) > 0 ? parseInt(params.duration) : 0);

	if (show)
	{
		containerNode.style.display = 'block';
	}

	if (duration > 0)
	{
		if (BX.type.isNotEmptyString(blockNode.id))
		{
			this.animationList[blockNode.id] = true;
		}

		BX.delegate((new BX["easing"]({
			duration : duration,
			start : {
				height: (show ? 0 : maxHeight),
				opacity: (show ? 0 : 100)
			},
			finish : {
				height: (show ? maxHeight : 0),
				opacity: (show ? 100 : 0)
			},
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step : function(state){
				containerNode.style.maxHeight = state.height + "px";
				containerNode.style.opacity = state.opacity / 100;
			},
			complete : BX.delegate(function(){
				if (BX.type.isNotEmptyString(blockNode.id))
				{
					this.animationList[blockNode.id] = null;
				}

				if (
					typeof params.callback != 'undefined'
					&& typeof params.callback.complete == 'function'
				)
				{
					containerNode.style.maxHeight = '';
					containerNode.style.opacity = '';
					params.callback.complete();
				}
			}, this)
		})).animate(), this);
	}
	else
	{
		params.callback.complete();
	}

	return true;
};

BX.BXGCE.highlightAdditionalBlock = function(blockId) {
	var node = BX('additional-block-' + blockId);

	if (node)
	{
		var highlightClassName = 'item-highlight';
		var windowScroll = BX.GetWindowScrollPos();

		BX.addClass(node, highlightClassName);

		setTimeout(function(){
			var position = BX.pos(node);

			(new BX.easing({
				duration : 500,
				start : {
					scroll: windowScroll.scrollTop
				},
				finish : {
					scroll: position.top
				},
				transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
				step : function(state){
					window.scrollTo(0, state.scroll);
				},
				complete: function() {}
			})).animate();
		}, 600);

		setTimeout(function(){
			BX.removeClass(node, highlightClassName);
		}, 3000);
	}
};

BX.BXGCE.onInitiatePermsChange = function() {
	var targetPrefix = (this.id == 'GROUP_INITIATE_PERMS' ? 'GROUP_INITIATE_PERMS_OPTION_PROJECT_' : 'GROUP_INITIATE_PERMS_OPTION_');
	if (BX(targetPrefix + this.options[this.selectedIndex].value))
	{
		BX(targetPrefix + this.options[this.selectedIndex].value).selected = true;
	}
};

BX.BXGCE.showStep = function (params) {
	var step = (
		typeof params != 'undefined'
		&& typeof params.step != 'undefined'
			? parseInt(params.step)
			: 1
	);

	for (var j = 1; j <= this.formSteps; j++)
	{
		if (BX('sonet_group_create_form_step_' + j))
		{
			BX('sonet_group_create_form_step_' + j).style.display = (j == step ? 'block' : 'none');
		}
	}
};

BX.BXGCE.recalcFormPartProjectBlock = function(blockId, isChecked)
{
	if (BX(blockId))
	{
		if (isChecked)
		{
			BX.addClass(BX(blockId), 'sgcp-switch-project');
		}
		else
		{
			BX.removeClass(blockId, 'sgcp-switch-project');
		}
	}
};

BX.BXGCE.recalcFormPartProject = function (isChecked) {
	isChecked = !!isChecked;

	if (BX('GROUP_PROJECT'))
	{
		this.setCheckedValue(BX('GROUP_PROJECT'), isChecked);
	}

	BX.BXGCE.recalcFormPartProjectBlock('IS_PROJECT_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_VISIBLE_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_OPENED_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_CLOSED_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_EXTRANET_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_OWNER_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_ADD_DEPT_HINT_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_MODERATORS_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_MODERATORS_SWITCH_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_TYPE_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_SUBJECT_ID_LABEL_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_INVITE_PERMS_block', isChecked);
	BX.BXGCE.recalcFormPartProjectBlock('GROUP_INVITE_PERMS_LABEL_block', isChecked);

	if (
		BX('sonet_group_create_popup_form_button_submit')
		&& BX('sonet_group_create_popup_form_button_submit').getAttribute('bx-action-type') == "create"
	)
	{
		BX('sonet_group_create_popup_form_button_submit').innerHTML = BX.message(isChecked ? 'SONET_GCE_T_DO_CREATE_PROJECT' : 'SONET_GCE_T_DO_CREATE');
	}

	if (BX('GROUP_NAME_input'))
	{
		BX('GROUP_NAME_input').placeholder = BX.message(isChecked ? 'SONET_GCE_T_NAME2_PROJECT' : 'SONET_GCE_T_NAME2');
	}

	if (BX('pagetitle-slider'))
	{
		BX('pagetitle-slider').innerHTML = BX.message(
			this.groupId > 0
				? (isChecked ? 'SONET_GCE_T_TITLE_EDIT_PROJECT' : 'SONET_GCE_T_TITLE_EDIT')
				: (isChecked ? 'SONET_GCE_T_TITLE_CREATE_PROJECT' : 'SONET_GCE_T_TITLE_CREATE')
		);
	}
};

BX.BXGCE.recalcForm = function (params) {
	var type = (
		typeof params != 'undefined'
		&& typeof params.type != 'undefined'
			? params.type
			: false
	);

	if (
		!type
		|| typeof this.types[type] == 'undefined'
	)
	{
		return;
	}

	this.recalcFormPartProject(this.types[type].PROJECT == 'Y');

	if (BX('GROUP_OPENED'))
	{
		this.setCheckedValue(BX('GROUP_OPENED'), (this.types[type].OPENED == 'Y'));
	}

	if (BX('GROUP_VISIBLE'))
	{
		this.setCheckedValue(BX('GROUP_VISIBLE'), (this.types[type].VISIBLE == 'Y'));
	}

	if (BX('IS_EXTRANET_GROUP'))
	{
		this.setCheckedValue(BX('IS_EXTRANET_GROUP'), (this.types[type].EXTERNAL == 'Y'));
	}

	this.recalcFormDependencies();
};

BX.BXGCE.recalcFormDependencies = function ()
{
	if (BX("IS_EXTRANET_GROUP"))
	{
		BXSwitchExtranet(this.getCheckedValue(BX("IS_EXTRANET_GROUP")), false);
	}

	if (
		BX("GROUP_VISIBLE")
		&& BX("GROUP_OPENED")
	)
	{
		var checked = this.getCheckedValue(BX('GROUP_VISIBLE'));
		if (!checked)
		{
			this.setCheckedValue(BX('GROUP_OPENED'), false);
		}
	}
};

BX.BXGCE.setSelector = function(selectorName)
{
	BX.BXGCE.userSelector = selectorName;
};

BX.BXGCE.disableBackspace = function(event)
{
	if (
		BX.SocNetLogDestination.backspaceDisable 
		|| BX.SocNetLogDestination.backspaceDisable != null
	)
	{
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
	}

	BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(event) {
		if (event.keyCode == 8)
		{
			event.preventDefault();
			return false;
		}
	});
	setTimeout(function(){
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
		BX.SocNetLogDestination.backspaceDisable = null;
	}, 5000);
};

BX.BXGCE.selectCallback = function(params)
{
	if (
		typeof params == 'undefined'
		|| !BX.type.isNotEmptyString(params.name)
		|| typeof params.item == 'undefined'
		|| !BX.type.isNotEmptyString(params.type)
	)
	{
		return;
	}

	var
		name = params.name,
		type = params.type,
		item = params.item;

	var single = (
		typeof BX.BXGCESelectorManager.controls[name] != 'undefined'
		&& typeof BX.BXGCESelectorManager.controls[name].single != 'undefined'
		&& !!BX.BXGCESelectorManager.controls[name].single
	);

	var controlName = (
		typeof BX.BXGCESelectorManager.controls[name] != 'undefined'
		&& typeof BX.BXGCESelectorManager.controls[name].controlName != 'undefined'
		&& BX.BXGCESelectorManager.controls[name].controlName
			? BX.BXGCESelectorManager.controls[name].controlName
			: 'USER_CODES[]'
	);

	if(!BX.findChild(BX('sonet_group_create_popup_users_item_post_' + name), { attr : { 'data-id' : item.id }}, false, false))
	{
		if (single)
		{
			BX.cleanNode(BX('sonet_group_create_popup_users_item_post_' + name));
			BX('sonet_group_create_popup_users_input_post_' + name).style.display = 'none';
		}

		BX('sonet_group_create_popup_users_item_post_' + name).appendChild(
			BX.create("span", { 
				attrs : { 
					'data-id' : item.id 
				}, 
				props : { 
					className : "feed-add-post-destination feed-add-post-destination-" + type
				}, 
				children: [
					BX.create("input", { 
						attrs : { 
							type : 'hidden',
							name : controlName,
							value : item.id
						}
					}),
					BX.create("span", { 
						props : { 
							className : "feed-add-post-destination-text"
						}, 
						html : item.name
					}),
					BX.create("span", { 
						props : { 
							className : "feed-add-post-del-but"
						}, 
						events : {
							click : function(e){
								BX.SocNetLogDestination.deleteItem(item.id, type, name);
								e.preventDefault();
							}, 
							mouseover : function(){
								BX.addClass(this.parentNode, 'feed-add-post-destination-hover');
							}, 
							mouseout : function(){
								BX.removeClass(this.parentNode, 'feed-add-post-destination-hover');
							}
						}
					})
				]
			})
		);

		BX.BXGCE.showDepartmentHint(name);
	}

	BX('sonet_group_create_popup_users_input_post_' + name).value = '';

	if (!single)
	{
		BX.SocNetLogDestination.BXfpSetLinkName({
			formName: name,
			tagInputName: 'sonet_group_create_popup_users_tag_post_' + name,
			tagLink1: BX.message('SONET_GCE_T_DEST_LINK_1'),
			tagLink2: BX.message('SONET_GCE_T_DEST_LINK_2')
		});
	}
	else
	{
		BX.style(BX('sonet_group_create_popup_users_tag_post_' + name), 'display', 'none');
		BX.SocNetLogDestination.closeDialog();
	}
};

BX.BXGCE.selectCallbackOld = function(item, type, search, bUndeleted, name)
{
	BX.BXGCE.selectCallback({
		name: name,
		type: type,
		item: item
	});
};

BX.BXGCE.showDepartmentHint = function(name)
{
	if (
		!BX.type.isPlainObject(BX.SocNetLogDestination.obItemsSelected[name])
		|| !BX('GROUP_ADD_DEPT_HINT_block')
	)
	{
		return false;
	}

	var departmentFound = false;
	for (var itemId in BX.SocNetLogDestination.obItemsSelected[name])
	{
		if (!BX.SocNetLogDestination.obItemsSelected[name].hasOwnProperty(itemId))
		{
			continue;
		}

		if (itemId.match(/DR\d+/))
		{
			departmentFound = true;
			break;
		}
	}

	if (departmentFound)
	{
		BX.addClass(BX('GROUP_ADD_DEPT_HINT_block'), 'visible');
	}
	else
	{
		BX.removeClass(BX('GROUP_ADD_DEPT_HINT_block'), 'visible');
	}

	return departmentFound;
};

BX.BXGCE.unSelectCallback = function(params)
{
	if (
		typeof params == 'undefined'
		|| !BX.type.isNotEmptyString(params.name)
		|| typeof params.item == 'undefined'
	)
	{
		return;
	}

	var
		name = params.name,
		item = params.item;

	var single = (
		typeof BX.BXGCESelectorManager.controls[name] != 'undefined'
		&& typeof BX.BXGCESelectorManager.controls[name].single != 'undefined'
		&& !!BX.BXGCESelectorManager.controls[name].single
	);

	var tagLinkText1 = (
		typeof BX.BXGCESelectorManager.controls[name] != 'undefined'
		&& typeof BX.BXGCESelectorManager.controls[name].tagLinkText1 != 'undefined'
		&& BX.BXGCESelectorManager.controls[name].tagLinkText1.length > 0
			? BX.BXGCESelectorManager.controls[name].tagLinkText1
			: BX.message('SONET_GCE_T_DEST_LINK_1')
	);

	var tagLinkText2 = (
		typeof BX.BXGCESelectorManager.controls[name] != 'undefined'
		&& typeof BX.BXGCESelectorManager.controls[name].tagLinkText2 != 'undefined'
		&& BX.BXGCESelectorManager.controls[name].tagLinkText2.length > 0
			? BX.BXGCESelectorManager.controls[name].tagLinkText2
			: BX.message('SONET_GCE_T_DEST_LINK_2')
	);

	BX.delegate(BX.SocNetLogDestination.BXfpUnSelectCallback, {
		formName: name,
		inputContainerName: 'sonet_group_create_popup_users_item_post_' + name,
		inputName: 'sonet_group_create_popup_users_input_post_' + name,
		tagInputName: 'sonet_group_create_popup_users_tag_post_' + name,
		tagLink1: tagLinkText1,
		tagLink2: tagLinkText2
	})(item);

	if (!single)
	{
		BX.SocNetLogDestination.BXfpSetLinkName({
			formName: name,
			tagInputName: 'sonet_group_create_popup_users_tag_post_' + name,
			tagLink1: BX.message('SONET_GCE_T_DEST_LINK_1'),
			tagLink2: BX.message('SONET_GCE_T_DEST_LINK_2')
		});
	}
	else
	{
		BX.style(BX('sonet_group_create_popup_users_tag_post_' + name), 'display', 'inline-block');
	}
};

BX.BXGCE.unSelectCallbackOld = function(item, type, search, name)
{
	BX.SocNetLogDestination.BXfpUnSelectCallback.apply(this, [item, type, search, name]);
	BX.BXGCE.showDepartmentHint(name);
};

BX.BXGCE.openDialogCallback = function(params)
{
	if (
		typeof params == 'undefined'
		|| !BX.type.isNotEmptyString(params.name)
	)
	{
		return;
	}

	var name = params.name;

	BX.PopupWindow.setOptions({
		'popupZindex': 2100
	});

	var single = (
		typeof BX.BXGCESelectorManager.controls[name] != 'undefined'
		&& typeof BX.BXGCESelectorManager.controls[name].single != 'undefined'
		&& !!BX.BXGCESelectorManager.controls[name].single
	);

	if (single)
	{
		BX('sonet_group_create_popup_users_input_post_' + name).style.display = 'inline-block';
	}

	BX.SocNetLogDestination.BXfpOpenDialogCallback.apply(this, arguments);
};

BX.BXGCE.openDialogCallbackOld = function(name)
{
	BX.BXGCE.openDialogCallback.apply(this, [ {
		name: name
	} ]);
};

BX.BXGCE.closeDialogCallback = function(params)
{
	var name = (typeof params != 'undefined' && BX.type.isNotEmptyString(params.name) ? params.name : '');
	BX.SocNetLogDestination.BXfpCloseDialogCallback.apply(this, [ name ]);
};

BX.BXGCE.bindActionLink = function(oBlock)
{
	if (
		oBlock === undefined
		|| oBlock == null
	)
	{
		return;
	}

	BX.bind(oBlock, "click", function(e)
	{
		BX.PopupMenu.destroy('invite-dialog-usertype-popup');

		var arItems = [
			{
				text : BX.message('SONET_GCE_T_DEST_EXTRANET_SELECTOR_INVITE'),
				id : 'sonet_group_create_popup_action_invite',
				className : 'menu-popup-no-icon',
				onclick: function() { BX.BXGCE.onActionSelect('invite'); }
			},
			{
				text : BX.message('SONET_GCE_T_DEST_EXTRANET_SELECTOR_ADD'),
				id : 'sonet_group_create_popup_action_add',
				className : 'menu-popup-no-icon',
				onclick: function() { BX.BXGCE.onActionSelect('add'); }
			}
		];

		var arParams = {
			offsetLeft: -14,
			offsetTop: 4,
			zIndex: 1200,
			lightShadow: false,
			angle: {position: 'top', offset : 50},
			events : {
				onPopupShow : function(ob)
				{

				}
			}
		};
		BX.PopupMenu.show('sonet_group_create_popup_action_popup', oBlock, arItems, arParams);
	});
};

BX.BXGCE.onActionSelect = function(action)
{
	if (action != 'add')
	{
		action = 'invite';
	}

	BX.BXGCE.lastAction = action;

	BX('sonet_group_create_popup_action_title_link').innerHTML = BX.message('SONET_GCE_T_DEST_EXTRANET_SELECTOR_' + (action == 'invite' ? 'INVITE' : 'ADD'));

	if (action == 'invite')
	{
		BX('sonet_group_create_popup_action_block_invite').style.display = 'block';
		BX('sonet_group_create_popup_action_block_invite_2').style.display = 'block';
		BX('sonet_group_create_popup_action_block_add').style.display = 'none';
	}
	else
	{
		BX('sonet_group_create_popup_action_block_invite').style.display = 'none';
		BX('sonet_group_create_popup_action_block_invite_2').style.display = 'none';
		BX('sonet_group_create_popup_action_block_add').style.display = 'block';
	}
	BX('sonet_group_create_popup_action_block_' + action).style.display = 'block';
	BX('sonet_group_create_popup_action_block_' + (action == 'invite' ? 'add' : 'invite')).style.display = 'none';

	BX.PopupMenu.destroy('sonet_group_create_popup_action_popup');
};

BX.BXGCE.showError = function(errorText)
{
	if (BX('sonet_group_create_error_block'))
	{
		BX('sonet_group_create_error_block').innerHTML = errorText;
		BX.removeClass(BX('sonet_group_create_error_block'), 'sonet-ui-form-error-block-invisible');
	}
};

BX.BXGCE.showMessage = function()
{
};

BX.BXGCE.disableSubmitButton = function(bDisable)
{
	bDisable = !!bDisable;
	
	var oButton = BX("sonet_group_create_popup_form_button_submit");
	if (oButton)
	{
		if (bDisable)
		{
			BX.SocialnetworkUICommon.showButtonWait(oButton);
			BX.unbind(oButton, "click", BXGCESubmitForm);
		}
		else
		{
			BX.SocialnetworkUICommon.hideButtonWait(oButton);
			BX.bind(oButton, "click", BXGCESubmitForm);
		}
	}
};

BX.BXGCE.getCheckedValue = function(node)
{
	var result = false;

	if (!BX(node))
	{
		return result;
	}

	if (node.type == 'hidden')
	{
		result = (node.value == 'Y');
	}
	else if (node.type == 'checkbox')
	{
		result = node.checked;
	}

	return result;
};

BX.BXGCE.setCheckedValue = function(node, value)
{
	if (!BX(node))
	{
		return;
	}

	value = !!value;

	if (node.type == 'checkbox')
	{
		node.checked = value;
	}
	else
	{
		node.value = (value ? 'Y' : 'N');
	}
};

BX.BXGCETagsForm = function(params)
{
	this.popup = null;
	this.addNewLink = null;
	this.hiddenField = null;
	this.popupContent = null;

	this.init(params);
};

BX.BXGCETagsForm.prototype.init = function(params)
{
	this.addNewLink = BX(params.addNewLinkId);
	this.tagsContainer = BX(params.containerNodeId);
	this.hiddenField = BX(params.hiddenFieldId);
	this.popupContent = BX(params.popupContentNodeId);
	this.popupInput = BX.findChild(this.popupContent, { tag : "input" });

	var tags = BX.findChildren(this.tagsContainer, {
		className : "js-id-tdp-mem-sel-is-item-delete"
	}, true);
	for (var i = 0, cnt = tags.length; i < cnt; i++ )
	{
		BX.bind(tags[i], "click", BX.proxy(this.onTagDelete, {
			obj : this,
			tagBox : tags[i].parentNode.parentNode,
			tagValue : tags[i].parentNode.parentNode.getAttribute("data-tag")
		}));
	}

	BX.bind(this.addNewLink, "click", BX.proxy(this.onAddNewClick, this));
};

BX.BXGCETagsForm.prototype.onTagDelete = function()
{
	BX.remove(this.tagBox);
	this.obj.hiddenField.value = this.obj.hiddenField.value.replace(this.tagValue + ',', '').replace('  ', ' ');
};

BX.BXGCETagsForm.prototype.show = function()
{
	if (this.popup === null)
	{
		this.popup = new BX.PopupWindow("bx-group-tag-popup", this.addNewLink, {
			content : this.popupContent,
			lightShadow : false,
			offsetTop: 8,
			offsetLeft: 10,
			autoHide: true,
			angle : true,
			closeByEsc: true,
			zIndex: -840,
			buttons: [
				new BX.PopupWindowButton({
					text : BX.message("SONET_GCE_T_TAG_ADD"),
					events : {
						click : BX.proxy(this.onTagAdd, this)
					}
				})
			]
		});

		BX.bind(this.popupInput, "keydown", BX.proxy(this.onKeyPress, this));
		BX.bind(this.popupInput, "keyup", BX.proxy(this.onKeyPress, this));
	}

	this.popup.show();
	BX.focus(this.popupInput);
};

BX.BXGCETagsForm.prototype.addTag = function(tagStr)
{
	var tags = BX.type.isNotEmptyString(tagStr) ? tagStr.split(",") : this.popupInput.value.split(",");
	var result = [];
	for (var i = 0; i < tags.length; i++ )
	{
		var tag = BX.util.trim(tags[i]);
		if(tag.length > 0)
		{
			var allTags = this.hiddenField.value.split(",");
			if(!BX.util.in_array(tag, allTags))
			{
				var newTagDelete = null;

				var newTag = BX.create("span", {
					children : [
						BX.create("span", {
							props: {
								className: "js-id-tdp-mem-sel-is-item social-group-create-form-field-item"
							},
							children: [
								BX.create("a", {
									props: {
										className: "social-group-create-form-field-item-text"
									},
									text: tag
								}),
								(newTagDelete = BX.create("span", {
									props: {
										className: "js-id-tdp-mem-sel-is-item-delete social-group-create-form-field-item-delete"
									}
								}))
							]
						})
					],
					attrs: {
						'data-tag': tag
					},
					props: {
						className: "js-id-tdp-mem-sel-is-items social-group-create-sliders-h-invisible"
					}
				});

				this.tagsContainer.insertBefore(newTag, this.addNewLink);

				BX.bind(newTagDelete, "click", BX.proxy(this.onTagDelete, {
					obj : this,
					tagBox : newTag,
					tagValue : tag
				}));

				this.hiddenField.value += tag + ',';

				result.push(tag);
			}
		}
	}

	return result;
};

BX.BXGCETagsForm.prototype.onTagAdd = function()
{
	this.addTag();
	this.popupInput.value = "";
	this.popup.close();
};

BX.BXGCETagsForm.prototype.onAddNewClick = function(event)
{
	event = event || window.event;
	this.show();
	event.preventDefault();
};

BX.BXGCETagsForm.prototype.onKeyPress = function(event)
{
	event = event || window.event;
	var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null));
	if (key == 13)
	{
		setTimeout(BX.proxy(this.onTagAdd, this), 0);
	}
};

BX.BXGCESelectorInstance = function(params)
{
	this.single = (
		typeof params != 'undefined'
		&& typeof params.single != 'undefined'
		&& !!params.single
	);

	this.controlName = (
		typeof params != 'undefined'
		&& typeof params.controlName != 'undefined'
			? params.controlName
			: false
	);

	this.tagLinkText1 = (
		typeof params != 'undefined'
		&& typeof params.tagLinkText1 != 'undefined'
			? params.tagLinkText1
			: ''
	);

	this.tagLinkText2 = (
		typeof params != 'undefined'
		&& typeof params.tagLinkText2 != 'undefined'
			? params.tagLinkText2
			: ''
	);
};

BX.BXGCESelectorInstance.prototype.init = function(openParams)
{
	BX.bind(
		BX(openParams.contId),
		'click',
		function() {
			var single = (
				typeof openParams.id != 'undefined'
				&& typeof BX.BXGCESelectorManager.controls[openParams.id] != 'undefined'
				&& typeof BX.BXGCESelectorManager.controls[openParams.id].single != 'undefined'
				&& !!BX.BXGCESelectorManager.controls[openParams.id].single
			);

			if (
				!single
				|| (
					BX('sonet_group_create_popup_users_item_post_' + openParams.id)
					&& BX('sonet_group_create_popup_users_item_post_' + openParams.id).children.length <= 0
				)
			)
			{
				BX.onCustomEvent(window, 'BX.BXGCE:open', [openParams]);
			}
		}
	);
};

BX.BXGCESelectorManager = {
	controls: {}
};

})();