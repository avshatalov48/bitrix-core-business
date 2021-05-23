BX.namespace('BX.Lists');
BX.Lists.ListsElementAttachedCrm = (function ()
{
	var ListsElementAttachedCrm = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.jsObject = parameters.jsObject;
		this.entityId = parameters.entityId;
		this.entityType = parameters.entityType;
		this.singleMode = Boolean(parameters.singleMode);
		this.iblockId = parameters.iblockId;
		this.gridPrefixId = parameters.gridPrefixId;
		this.listElementTemplateUrl = parameters.listElementTemplateUrl;
		this.backEndUrl = parameters.backEndUrl;
		this.fieldsForSetValue = parameters.fieldsForSetValue;

		this.init();
	};

	ListsElementAttachedCrm.prototype.init = function ()
	{
		this.gridId = this.gridPrefixId + this.iblockId;
		this.externalContext = 'creatingElementFromCrm';
		this.externalRequestData = null;
		this.externalEventHandler = null;

		BX.addCustomEvent('Grid::beforeRequest', BX.delegate(function (gridObject, eventArgs) {
			this.setGridRequestParams(gridObject, eventArgs);
		}, this));

		if(this.singleMode)
		{
			BX.bind(BX('leac-button-add-element-'+this.iblockId), 'click', BX.delegate(this.addElement, this));
		}
	};

	ListsElementAttachedCrm.prototype.setGridRequestParams = function (gridObject, eventArgs)
	{
		if(eventArgs.gridId != this.gridId) return;

		if(eventArgs.url == '') eventArgs.url = this.backEndUrl;

		eventArgs.url = BX.util.add_url_param(eventArgs.url, {
			gridId: eventArgs.gridId,
			entityId: this.entityId,
			entityType: this.entityType
		});
	};

	ListsElementAttachedCrm.prototype.showElement = function (gridId, elementId, url)
	{
		var gridObject;

		window.open(url);

		gridObject = BX.Main.gridManager.getById(gridId);
		if(gridObject.hasOwnProperty('instance'))
		{
			var rowObject = gridObject.instance.getRows().getById(elementId);
			if(rowObject) rowObject.closeActionsMenu();
		}
	};

	ListsElementAttachedCrm.prototype.unBind = function (gridId, elementId)
	{
		BX.Lists.modalWindow({
			modalId: 'bx-lists-migrate-list',
			title: BX.message('LEACT_DELETE_POPUP_TITLE'),
			contentClassName: '',
			draggable: true,
			contentStyle: {
				width: '400px',
				padding: '20px 20px 20px 20px'
			},
			events: {
				onPopupClose : function() {
					this.destroy();
				}
			},
			content: BX.message("LEACT_TOOLBAR_ELEMENT_DELETE_WARNING"),
			buttons: [
				BX.create('span', {
					text : BX.message("LEACT_DELETE_POPUP_ACCEPT_BUTTON"),
					props: {
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function() {
							var reloadParams = {}, gridObject;
							reloadParams['action_button_'+gridId] = 'delete';
							reloadParams['ID'] = [elementId];

							gridObject = BX.Main.gridManager.getById(gridId);
							if(gridObject.hasOwnProperty('instance'))
							{
								gridObject.instance.reloadTable('POST', reloadParams);
								var rowObject = gridObject.instance.getRows().getById(elementId);
								if(rowObject) rowObject.closeActionsMenu();
							}
							BX.PopupWindowManager.getCurrentPopup().close();
						}, this)
					}
				}),
				BX.create('span', {
					text : BX.message("LEACT_DELETE_POPUP_CANCEL_BUTTON"),
					props: {
						className: 'popup-window-button popup-window-button-link popup-window-button-link-cancel'
					},
					events : {
						click : BX.delegate(function() {
							BX.PopupWindowManager.getCurrentPopup().close();
						}, this)
					}
				})
			]
		});
	};

	ListsElementAttachedCrm.prototype.addElement = function ()
	{
		var url = this.listElementTemplateUrl[this.iblockId];
		url = url.replace('#section_id#', 0).replace('#element_id#', 0);
		var urlParams = {external_context: this.externalContext};
		for(var p in this.fieldsForSetValue)
		{
			urlParams['fieldId'] = p;
			urlParams['defaultValue'] = this.fieldsForSetValue[p].defaultValue;
		}
		url = BX.util.add_url_param(url, urlParams);

		this.performExternalRequest(url);
	};

	ListsElementAttachedCrm.prototype.editElement = function (elementId)
	{
		var url = this.listElementTemplateUrl[this.iblockId];
		url = url.replace('#section_id#', 0).replace('#element_id#', elementId);
		url = BX.util.add_url_param(url, {external_context: this.externalContext});

		this.performExternalRequest(url);
	};

	ListsElementAttachedCrm.prototype.performExternalRequest = function(url)
	{
		if(!this.externalRequestData)
		{
			this.externalRequestData = {};
		}

		this.externalRequestData[this.externalContext] = { context: this.externalContext, wnd: window.open(url) };

		if(!this.externalEventHandler)
		{
			this.externalEventHandler = BX.delegate(this.onExternalEvent, this);
			BX.addCustomEvent(window, 'onLocalStorageSet', this.externalEventHandler);
		}

		BX.localStorage.set('externalValue_'+this.iblockId, this.fieldsForSetValue, 10);
	};

	ListsElementAttachedCrm.prototype.onExternalEvent = function(params)
	{
		var key = BX.type.isNotEmptyString(params['key']) ? params['key'] : '';
		var value = BX.type.isPlainObject(params['value']) ? params['value'] : {};

		var context = BX.type.isNotEmptyString(value['context']) ? value['context'] : '';

		if(key === 'onElementCreate' && this.externalRequestData
			&& BX.type.isPlainObject(this.externalRequestData[context]))
		{
			var isCanceled = BX.type.isBoolean(value['isCanceled']) ? value['isCanceled'] : false;
			if(!isCanceled && BX.type.isPlainObject(value['elementInfo']))
			{
				if(this.iblockId == value['elementInfo']['iblockId'])
				{
					BX.Main.gridManager.getById(this.gridId).instance.reload();
				}
			}

			if(this.externalRequestData[context]['wnd'])
			{
				this.externalRequestData[context]['wnd'].close();
			}

			delete this.externalRequestData[context];
		}
	};

	return ListsElementAttachedCrm;
})();