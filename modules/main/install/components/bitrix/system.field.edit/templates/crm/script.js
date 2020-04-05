BX.CrmEntitySelector = (function ()
{
	var CrmEntitySelector = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.jsObject = parameters.jsObject;
		this.fieldUid = parameters.fieldUid;
		this.fieldName = parameters.fieldName;
		this.usePrefix = parameters.usePrefix;
		this.listPrefix = parameters.listPrefix;
		this.multiple = parameters.multiple;
		this.listElement = parameters.listElement;
		this.listEntityType = parameters.listEntityType;
		this.pluralCreation = Boolean(parameters.pluralCreation);
		this.listEntityCreateUrl = parameters.listEntityCreateUrl;
		this.currentEntityType = parameters.currentEntityType;
		this.context = parameters.context;

		this.initialize();
	};

	CrmEntitySelector.prototype.initialize = function()
	{
		this.popupObject = null;
		this.popupId = 'crm-'+this.randomString+'-popup';
		this.popupBindElement = null;
		this.popupContent = '';
		this.externalRequestData = null;
		this.externalEventHandler = null;

		BX.addCustomEvent('onCrmSelectedItem', BX.proxy(this.setSelectedElement, this));
		BX.addCustomEvent('onCrmUnSelectedItem', BX.proxy(this.unsetSelectedElement, this));
	};

	CrmEntitySelector.prototype.createNewEntity = function(event)
	{
		if(this.pluralCreation)
		{
			event = event || window.event;
			this.popupBindElement = event.currentTarget;
			this.createPopup();
		}
		else
		{
			this.performExternalRequest();
		}
	};

	CrmEntitySelector.prototype.performExternalRequest = function(entityType)
	{
		if(this.popupObject)
		{
			this.popupObject.popupWindow.close();
		}

		if(entityType)
		{
			this.setCurrentEntityType(entityType);
		}

		var url = BX.util.add_url_param(this.getCreateUrl(), {
			external_context: this.context
		});

		if(!this.externalRequestData)
		{
			this.externalRequestData = {};
		}

		this.externalRequestData[this.context] = {context: this.context, wnd: window.open(url)};

		if(!this.externalEventHandler)
		{
			this.externalEventHandler = BX.delegate(this.onExternalEvent, this);
			BX.addCustomEvent(window, 'onLocalStorageSet', this.externalEventHandler);
		}
	};

	CrmEntitySelector.prototype.onExternalEvent = function(params)
	{
		var key = BX.type.isNotEmptyString(params['key']) ? params['key'] : '';
		var value = BX.type.isPlainObject(params['value']) ? params['value'] : {};
		var typeName = BX.type.isNotEmptyString(value['entityTypeName']) ? value['entityTypeName'] : '';
		var context = BX.type.isNotEmptyString(value['context']) ? value['context'] : '';

		if(key === 'onCrmEntityCreate' && typeName === this.currentEntityType.toUpperCase()
			&& this.externalRequestData && BX.type.isPlainObject(this.externalRequestData[context]))
		{
			var isCanceled = BX.type.isBoolean(value['isCanceled']) ? value['isCanceled'] : false;
			if(!isCanceled && BX.type.isPlainObject(value['entityInfo']))
			{
				if(this.multiple != 'Y')
				{
					for(var k = 0; k < this.listElement.length; k++)
					{
						this.listElement[k]['selected'] = 'N';
					}
				}
				value["entityInfo"]['selected'] = 'Y';
				var entityInfo = value["entityInfo"];
				if(this.usePrefix == 'Y')
				{
					var entityType = entityInfo['type'].toUpperCase();
					entityInfo['id'] = this.listPrefix[entityType]+'_'+entityInfo['id'];
				}
				this.listElement.push(entityInfo);
				BX[''+this.jsObject+''].initWidgetEntitySelection();
			}

			if(this.externalRequestData[context]['wnd'])
			{
				this.externalRequestData[context]['wnd'].close();
			}

			delete this.externalRequestData[context];
		}
	};

	CrmEntitySelector.prototype.createPopup = function()
	{
		var popupItems = [];
		for(var k = 0; k < this.listEntityType.length; k++)
		{
			popupItems.push({
				text : BX.message('CRM_CES_CREATE_'+this.listEntityType[k].toUpperCase()),
				onclick : 'BX["'+this.jsObject+'"].performExternalRequest("'+this.listEntityType[k]+'");'
			});
		}
		if(!BX.PopupMenu.getMenuById(this.popupId))
		{
			var buttonRect = this.popupBindElement.getBoundingClientRect();
			this.popupObject = BX.PopupMenu.create(
				this.popupId,
				this.popupBindElement,
				popupItems,
				{
					closeByEsc : true,
					angle: true,
					offsetLeft: buttonRect.width/2
				}
			);
		}
		if(this.popupObject)
		{
			this.popupObject.popupWindow.show();
		}
	};

	CrmEntitySelector.prototype.setCurrentEntityType = function(currentEntityType)
	{
		this.currentEntityType = currentEntityType;
	};

	CrmEntitySelector.prototype.getCreateUrl = function()
	{
		if(this.listEntityCreateUrl.hasOwnProperty(this.currentEntityType)) 
		{
			return this.listEntityCreateUrl[this.currentEntityType];
		}
		else 
		{
			return '';
		}
	};

	CrmEntitySelector.prototype.setSelectedElement = function(itemInfo)
	{
		for (var k in this.listElement)
		{
			if (itemInfo.id === this.listElement[k].id)
			{
				this.listElement[k].selected = 'Y';
			}
		}
	};

	CrmEntitySelector.prototype.unsetSelectedElement = function(itemInfo)
	{
		for (var k in this.listElement)
		{
			if (itemInfo.id === this.listElement[k].id)
			{
				this.listElement[k].selected = 'N';
			}
		}
	};

	CrmEntitySelector.prototype.initWidgetEntitySelection = function()
	{
		BX.loadCSS('/bitrix/js/crm/css/crm.css');

		if(typeof(CRM) == 'undefined')
		{
			BX.loadScript('/bitrix/js/crm/crm.js', BX[''+this.jsObject+''].initWidgetEntitySelection());
			return;
		}

		CRM.Set(
			BX('crm-'+this.fieldUid+'-open'),
			this.fieldName,
			'',
			this.listElement,
			(this.usePrefix === 'Y'),
			(this.multiple === 'Y'),
			this.listEntityType,
			{
				'lead': BX.message('CRM_FF_LEAD'),
				'contact': BX.message('CRM_FF_CONTACT'),
				'company': BX.message('CRM_FF_COMPANY'),
				'deal': BX.message('CRM_FF_DEAL'),
				'quote': BX.message('CRM_FF_QUOTE'),
				'order': BX.message('CRM_FF_ORDER'),
				'ok': BX.message('CRM_FF_OK'),
				'cancel': BX.message('CRM_FF_CANCEL'),
				'close': BX.message('CRM_FF_CLOSE'),
				'wait': BX.message('CRM_FF_SEARCH'),
				'noresult': BX.message('CRM_FF_NO_RESULT'),
				'add': BX.message('CRM_FF_CHOISE'),
				'edit': BX.message('CRM_FF_CHANGE'),
				'search': BX.message('CRM_FF_SEARCH'),
				'last': BX.message('CRM_FF_LAST')
			}
		);
	};

	return CrmEntitySelector;
})();