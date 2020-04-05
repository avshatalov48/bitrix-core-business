var CrmWebFormList = function(params)
{
	this.init = function(params)
	{
		this.context = BX(params.context);
		this.canEdit = params.canEdit;
		this.nodeHead = this.context.querySelector('.intranet-button-list-header');
		this.headHideClass = 'crm-webform-title-close';
		this.formAttribute = 'data-bx-crm-webform-item';
		this.formAttributeIsSystem = 'data-bx-crm-webform-item-is-system';
		this.forms = [];

		this.mess = params.mess || {};

		var formNodeList = this.context.querySelectorAll('[' + this.formAttribute + ']');
		for(var i = 0; i < formNodeList.length; i++)
		{
			var formNode = formNodeList.item(i);
			var formId = formNode.getAttribute(this.formAttribute);
			var isSystem = formNode.getAttribute(this.formAttributeIsSystem) == 'Y';
			this.initForm({
				'caller': this,
				'id': formId,
				'node': formNode,
				'isSystem': isSystem,
				'detailPageUrlTemplate': params.detailPageUrlTemplate,
				'actionRequestUrl': params.actionRequestUrl,
				'remoteData': !!params.remoteData[formId] ? params.remoteData[formId] : {},
				'localData': !!params.localData[formId] ? params.localData[formId] : {}
			});
		}
	};

	this.onBeforeDeleteForm = function(form)
	{
		var list = this.forms.filter(function(item){
			return item.isSystem == false;
		});
		if(list.length > 1)
		{
			return;
		}

		BX.addClass(this.nodeHead, this.headHideClass)
	};

	this.onAfterDeleteForm = function(form)
	{
		var index = BX.util.array_search(form, this.forms);
		if(index > -1)
		{
			delete this.forms[index];
		}
	};

	this.onRevertDeleteForm = function(form)
	{
		BX.removeClass(this.nodeHead, this.headHideClass)
	};

	this.initForm = function(params)
	{
		var form = new CrmWebFormListItem(params);
		this.forms.push(form);
	};

	this.init(params);
};

function CrmWebFormListItem(params)
{
	this.caller = params.caller;
	this.id = params.id;
	this.node = params.node;
	this.isSystem = params.isSystem;
	this.actionRequestUrl = params.actionRequestUrl;
	this.detailPageUrlTemplate = params.detailPageUrlTemplate;
	this.remoteData = params.remoteData;
	this.localData = params.localData;

	this.nodeDelete = this.node.querySelector('.copy-to-buffer-button');
	this.nodeCopyToClipboard = this.node.querySelector('.copy-to-clipboard-node');
	this.nodeCopyToClipboardButton = this.node.querySelector('.copy-to-clipboard-button');

	this.nodeDelete = this.node.querySelector('[data-bx-crm-webform-item-delete]');
	this.nodeSettings = this.node.querySelector('[data-bx-crm-webform-item-settings]');
	this.nodeViewSettings = this.node.querySelector('[data-bx-crm-webform-item-view-settings]');
	this.nodeView = this.node.querySelector('[data-bx-crm-webform-item-view]');
	this.isActiveControlLocked = false;

	this.popupSettings = null;
	this.popupViewSettings = null;

	this.activeController = new CrmWebFormListItemActiveDateController({caller: this});
	this.bindControls(params);
}
CrmWebFormListItem.prototype =
{
	showErrorPopup: function (data)
	{
		data = data || {};
		var text = data.text || this.caller.mess.errorAction;
		var popup = BX.PopupWindowManager.create(
			'crm_webform_list_error',
			null,
			{
				autoHide: true,
				lightShadow: true,
				closeByEsc: true,
				overlay: {backgroundColor: 'black', opacity: 500}
			}
		);
		popup.setButtons([
			new BX.PopupWindowButton({
				text: this.caller.mess.dlgBtnClose,
				events: {click: function(){this.popupWindow.close();}}
			})
		]);
		popup.setContent('<span class="crm-webform-edit-warning-popup-alert">' + text + '</span>');
		popup.show();
	},
	showConfirmPopup: function (data)
	{
		data = data || {};
		var text = data.text || this.caller.mess.confirmAction;
		var popup = BX.PopupWindowManager.create(
			'crm_webform_list_confirm',
			null,
			{
				autoHide: true,
				lightShadow: true,
				closeByEsc: true,
				overlay: {backgroundColor: 'black', opacity: 500}
			}
		);
		popup.setButtons([
			new BX.PopupWindowButton({
				text: this.caller.mess.dlgBtnApply,
				className: "popup-window-button-accept",
				events: {click: function(){this.popupWindow.close(); data.action.apply(this, [])}}
			}),
			new BX.PopupWindowButton({
				text: this.caller.mess.dlgBtnCancel,
				events: {click: function(){this.popupWindow.close();}}
			})
		]);
		popup.setContent('<span class="crm-webform-edit-warning-popup-confirm">' + text + '</span>');
		popup.show();
	},
	changeActive: function (event, doNotSend)
	{
		if(!this.caller.canEdit)
			return;

		doNotSend = doNotSend || false;

		if(this.isActiveControlLocked)
			return;

		var needDeactivate = this.activeController.isActive(),
			action = needDeactivate ? 'deactivate' : 'activate',
			reqData = {};

		if(action == 'activate')
		{
			reqData = {
				'REMOTE_DATA': this.remoteData,
				'LOCAL_DATA': this.localData
			};
		}
		else
		{
			reqData = {
				'BUTTON_ID': this.id
			};
		}


		if(needDeactivate)
			this.activeController.deactivate();
		else
			this.activeController.activate();

		if(doNotSend)
			return;

		this.isActiveControlLocked = true;
		this.sendActionRequest(
			action,
			reqData,
			function(data)
			{
				this.isActiveControlLocked = false;

				if(action == 'activate')
				{
					if(!!data.LOCAL_DATA)
						this.localData = data.LOCAL_DATA;

				}
				else if(action == 'deactivate')
				{
					this.localData = {};
				}
			},
			function(data)
			{
				data = data || {'error': true, 'text': ''};
				this.isActiveControlLocked = false;
				this.activeController.revert();
				this.showErrorPopup(data);
			}
		);
	},

	redirectToDetailPage: function (formId)
	{
		window.location = this.detailPageUrlTemplate.replace('#id#', formId).replace('#form_id#', formId);
	},

	resetCounters: function ()
	{
		this.sendActionRequest('reset_counters', {}, function(){
			window.location.reload();
		});
	},
	copy: function ()
	{
		this.sendActionRequest('copy', {}, function(data){
			this.redirectToDetailPage(data.copiedId);
		});
	},
	delete: function ()
	{
		this.showConfirmPopup({
			text: this.caller.mess.deleteConfirmation,
			action: BX.proxy(function(){

				var deleteClassName = 'crm-webform-row-close';
				BX.addClass(this.node, deleteClassName);
				this.caller.onBeforeDeleteForm(this);

				this.sendActionRequest(
					'delete',
					{},
					function(data){
						this.caller.onAfterDeleteForm(this);
					},
					function(data){
						BX.removeClass(this.node, deleteClassName);
						this.caller.onRevertDeleteForm(this);
						this.showErrorPopup(data);
					}
				);

			}, this)
		});
	},
	sendActionRequest: function (action, data, callbackSuccess, callbackFailure)
	{
		callbackSuccess = callbackSuccess || null;
		callbackFailure = callbackFailure || BX.proxy(this.showErrorPopup, this);

		BX.ajax({
			url: this.actionRequestUrl,
			method: 'POST',
			data: {
				'action': action,
				'data': data,
				'sessid': BX.bitrix_sessid()
			},
			timeout: 30,
			dataType: 'json',
			processData: true,
			onsuccess: BX.proxy(function(data){
				data = data || {};
				if(data.error)
				{
					callbackFailure.apply(this, [data]);
				}
				else if(callbackSuccess)
				{
					callbackSuccess.apply(this, [data]);
				}
			}, this),
			onfailure: BX.proxy(function(){
				var data = {'error': true, 'text': ''};
					callbackFailure.apply(this, [data]);
			}, this)
		});
	},
	bindControls: function ()
	{
		BX.clipboard.bindCopyClick(this.nodeCopyToClipboardButton, {text: this.nodeCopyToClipboard});
		BX.bind(this.nodeDelete, 'click', BX.proxy(this.delete, this));
		BX.bind(this.activeController.nodeActiveControl, 'click', BX.proxy(this.changeActive, this));
		BX.bind(this.nodeSettings, 'click', BX.proxy(this.showSettings, this));
		BX.bind(this.nodeViewSettings, 'click', BX.proxy(this.showViewSettings, this));
	},
	changeClass: function (node, className, isAdd)
	{
		isAdd = isAdd || false;
		if(!node)
		{
			return;
		}

		if(isAdd)
		{
			BX.addClass(node, className);
		}
		else
		{
			BX.removeClass(node, className);
		}
	},
	styleDisplay: function (node, isShow, displayValue)
	{
		isShow = isShow || false;
		displayValue = displayValue || '';
		if(!node)
		{
			return;
		}

		node.style.display = isShow ? displayValue : 'none';
	},
	createPopup: function(popupId, button, items, params)
	{
		params = params || {};
		return BX.PopupMenu.create(
			popupId,
			button,
			items,
			{
				autoHide: true,
				offsetLeft: params.offsetLeft ? params.offsetLeft : -21,
				offsetTop: params.offsetTop ? params.offsetTop : -3,
				angle:
				{
					position: "top",
					offset: 42
				},
				events:
				{
					onPopupClose : BX.delegate(this.onPopupClose, this)
				}
			}
		);
	},
	closePopup: function(popup)
	{
		if(popup && popup.popupWindow)
		{
			popup.popupWindow.close();
		}
	},
	onPopupClose: function()
	{

	}
};

function CrmWebFormListItemActiveDateController(params)
{
	this.caller = params.caller;

	this.nodeActiveControl = this.caller.node.querySelector('[data-bx-crm-webform-item-active]');
	this.nodeDate = this.caller.node.querySelector('[data-bx-crm-webform-item-active-date]');

	this.nodeDateNowActivated = this.caller.node.querySelector('[data-bx-crm-webform-item-active-date-now-a]');
	this.nodeDateNowDeActivated = this.caller.node.querySelector('[data-bx-crm-webform-item-active-date-now-d]');

	this.classDateNow = 'user-container-show-now';
	this.classDateNowState = 'user-container-show-now-deact';
	this.classOn = 'intranet-button-list-on';
	this.classOff = 'intranet-button-list-off';

	this.isNowShowedCounter = 0;
	this.isRevert = false;
}
CrmWebFormListItemActiveDateController.prototype =
{
	isActive: function ()
	{
		return BX.hasClass(this.nodeActiveControl, this.classOn);
	},
	revert: function ()
	{
		this.isRevert = true;
		this.toggle();

		if(this.isNowShowedCounter < 2)
		{
			this.isNowShowedCounter = 0;
		}
		this.isRevert = false;
	},
	toggle: function ()
	{
		if(this.isActive())
		{
			this.deactivate();
		}
		else
		{
			this.activate();
		}
	},
	activate: function ()
	{
		BX.addClass(this.nodeActiveControl, this.classOn);
		BX.removeClass(this.nodeActiveControl, this.classOff);
		this.actualizeDate();
	},
	deactivate: function ()
	{
		BX.removeClass(this.nodeActiveControl, this.classOn);
		BX.addClass(this.nodeActiveControl, this.classOff);
		this.actualizeDate();
	},
	actualizeDate: function ()
	{
		this.caller.changeClass(this.nodeDate, this.classDateNowState, !this.isActive());
		this.nodeDate.style.display = '';
		var isNow = (!this.isRevert || this.isNowShowedCounter > 1);
		this.caller.changeClass(this.nodeDate, this.classDateNow, isNow);
		this.isNowShowedCounter++;
	}
};