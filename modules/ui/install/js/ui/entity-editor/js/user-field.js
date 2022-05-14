BX.namespace("BX.UI");

if(typeof BX.UI.EntityUserFieldType === "undefined")
{
	BX.UI.EntityUserFieldType =
	{
		string: "string",
		integer: "integer",
		double: "double",
		boolean: "boolean",
		money: "money",
		date: "date",
		datetime: "datetime",
		enumeration: "enumeration",
		employee: "employee",
		crm: "crm",
		crmStatus: "crm_status",
		file: "file",
		url: "url"
	};
}

if(typeof BX.UI.EntityUserFieldManager === "undefined")
{
	BX.UI.EntityUserFieldManager = function()
	{
		this._id = "";
		this._settings = {};
		this._entityId = 0;
		this._fieldEntityId = "";
		this._enableSelection = true;
		this._enableCreation = false;
		this._creationSignature = "";
		this._creationPageUrl = "";
		this._activeFields = {};
		this._validationEnabled = true;
		this._validationResult = null;
		this._validationPromise = null;

		this._enableMandatoryControl = true;
		this._config = null;

		this._contextParams = {};
	};
	BX.UI.EntityUserFieldManager.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};
			this._entityId = BX.prop.getInteger(this._settings, "entityId", 0);
			this._fieldEntityId = BX.prop.getString(this._settings, "fieldEntityId", "");
			this._enableSelection = BX.prop.getBoolean(this._settings, "enableSelection", true);
			this._enableCreation = BX.prop.getBoolean(this._settings, "enableCreation", false);
			this._creationSignature = BX.prop.getString(this._settings, "creationSignature", "");
			this._creationPageUrl = BX.prop.getString(this._settings, "creationPageUrl", "");
			this._enableMandatoryControl = BX.prop.getBoolean(this._settings, "enableMandatoryControl", true);
			this._contextParams = BX.prop.getObject(this._settings, "contextParams", {});

			//region Bind EntityEditorControlFactory Method
			if(typeof BX.UI.EntityEditorControlFactory !== "undefined")
			{
				BX.UI.EntityEditorControlFactory.registerFactoryMethod(
					"userField",
					this.createEditorControl.bind(this)
				);
			}
			else
			{
				BX.addCustomEvent(
					"BX.UI.EntityEditorControlFactory:onInitialize",
					function(params, eventArgs)
					{
						eventArgs.methods["userField"] = this.createEditorControl.bind(this);
					}.bind(this)
				);
			}
			//endregion
		},
		isSelectionEnabled: function()
		{
			return this._enableSelection;
		},
		isCreationEnabled: function()
		{
			return this._enableCreation;
		},
		isModificationEnabled: function()
		{
			return this._enableSelection || this._enableCreation;
		},
		isMandatoryControlEnabled: function()
		{
			return this._enableMandatoryControl;
		},
		getDefaultFieldLabel: function(typeId)
		{
			if(typeId === "string")
			{
				return BX.message("UI_ENTITY_EDITOR_UF_STRING_LABEL");
			}
			else if(typeId === "double")
			{
				return BX.message("UI_ENTITY_EDITOR_UF_DOUBLE_LABEL");
			}
			else if(typeId === "money")
			{
				return BX.message("UI_ENTITY_EDITOR_UF_MONEY_LABEL");
			}
			else if(typeId === "datetime")
			{
				return BX.message("UI_ENTITY_EDITOR_UF_DATETIME_LABEL");
			}
			else if(typeId === "enumeration")
			{
				return BX.message("UI_ENTITY_EDITOR_UF_ENUMERATION_LABEL");
			}
			else if(typeId === "file")
			{
				return BX.message("UI_ENTITY_EDITOR_UF_FILE_LABEL");
			}
			return BX.message("UI_ENTITY_EDITOR_UF_LABEL");
		},
		getFieldPrefix: function()
		{
			return BX.prop.getString(this._settings, "fieldPrefix", "");
		},
		getAdditionalTypeList: function()
		{
			return (BX.type.isArray(BX.UI.EntityUserFieldManager["additionalTypeList"])
				? BX.UI.EntityUserFieldManager["additionalTypeList"] : []
			);
		},
		getTypeInfos: function()
		{
			var items = [];
			items.push({ name: "string", title: BX.message("UI_ENTITY_EDITOR_UF_STRING_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_STRING_LEGEND") });
			items.push({ name: "enumeration", title: BX.message("UI_ENTITY_EDITOR_UF_ENUM_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_ENUM_LEGEND") });
			items.push({ name: "datetime", title: BX.message("UI_ENTITY_EDITOR_UF_DATETIME_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_DATETIME_LEGEND") });
			items.push({ name: "address", title: BX.message("UI_ENTITY_EDITOR_UF_ADDRESS_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_ADDRESS_LEGEND") });

			items.push({ name: "url", title: BX.message("UI_ENTITY_EDITOR_UF_URL_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_URL_LEGEND") });
			items.push({ name: "file", title: BX.message("UI_ENTITY_EDITOR_UF_FILE_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_FILE_LEGEND") });
			items.push({ name: "money", title: BX.message("UI_ENTITY_EDITOR_UF_MONEY_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_MONEY_LEGEND") });
			items.push({ name: "boolean", title: BX.message("UI_ENTITY_EDITOR_BOOLEAN_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_BOOLEAN_LEGEND") });
			items.push({ name: "double", title: BX.message("UI_ENTITY_EDITOR_UF_DOUBLE_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_DOUBLE_LEGEND") });

			var additionalList = this.getAdditionalTypeList();
			for(var i = 0; i < additionalList.length; i++)
			{
				items.push({
					name: additionalList[i].USER_TYPE_ID,
					title: additionalList[i].TITLE,
					legend: additionalList[i].LEGEND
				});
			}

			if(this._creationPageUrl)
			{
				items.push({ name: "custom", title: BX.message("UI_ENTITY_EDITOR_UF_CUSTOM_TITLE"), legend: BX.message("UI_ENTITY_EDITOR_UF_CUSTOM_LEGEND") });
			}

			var event = new BX.Event.BaseEvent({
				data: {
					types: items
				}
			});
			BX.Event.EventEmitter.emit('BX.UI.EntityUserFieldManager:getTypes', event);
			if(BX.Type.isArray(event.getData().types))
			{
				items = event.getData().types;
			}

			return items;
		},
		getCreationPageUrl: function()
		{
			return this._creationPageUrl;
		},
		createEditorControl: function (type, controlId, settings)
		{
			if(type === "userField")
			{
				return BX.UI.EntityEditorUserField.create(controlId, settings);
			}
			return null;
		},
		createField: function(fieldData, mode)
		{
			if(!this._enableCreation)
			{
				return;
			}

			var typeId = BX.prop.getString(fieldData, "USER_TYPE_ID", "");
			if(typeId === "")
			{
				typeId = BX.UI.EntityUserFieldType.string;
			}

			if(!BX.type.isNotEmptyString(fieldData["EDIT_FORM_LABEL"]))
			{
				fieldData["EDIT_FORM_LABEL"] = this.getDefaultFieldLabel(typeId);
			}

			if(!BX.type.isNotEmptyString(fieldData["LIST_COLUMN_LABEL"]))
			{
				fieldData["LIST_COLUMN_LABEL"] = fieldData["EDIT_FORM_LABEL"];
			}

			if(!BX.type.isNotEmptyString(fieldData["LIST_FILTER_LABEL"]))
			{
				fieldData["LIST_FILTER_LABEL"] = fieldData["LIST_COLUMN_LABEL"];
			}

			this.addFieldLabel("EDIT_FORM_LABEL", fieldData["EDIT_FORM_LABEL"], fieldData);
			this.addFieldLabel("LIST_COLUMN_LABEL", fieldData["LIST_COLUMN_LABEL"], fieldData);
			this.addFieldLabel("LIST_FILTER_LABEL", fieldData["LIST_FILTER_LABEL"], fieldData);

			var promise = new BX.Promise();
			var onSuccess = function(result)
			{
				promise.fulfill(result);
			};

			if(!BX.type.isNotEmptyString(fieldData["FIELD"]))
			{
				var prefix = this.getFieldPrefix();
				fieldData["FIELD"] = "UF_" + (prefix !== "" ? (prefix + "_") : "") + (new Date()).getTime().toString();
			}

			fieldData["ENTITY_ID"] = this._fieldEntityId;
			fieldData["SIGNATURE"] = this._creationSignature;

			if(!BX.type.isNotEmptyString(fieldData["MULTIPLE"]))
			{
				fieldData["MULTIPLE"] = "N";
			}

			if(!BX.type.isNotEmptyString(fieldData["MANDATORY"]))
			{
				fieldData["MANDATORY"] = "N";
			}

			if(typeId === BX.UI.EntityUserFieldType.file)
			{
				fieldData["SHOW_FILTER"] = "N";
				fieldData["SHOW_IN_LIST"] = "N";
			}
			else
			{
				if(typeId === BX.UI.EntityUserFieldType.employee
					|| typeId === BX.UI.EntityUserFieldType.crm
					|| typeId === BX.UI.EntityUserFieldType.crmStatus
				)
				{
					//Force exact match for 'employee', 'crm' and 'crm_status' types
					fieldData["SHOW_FILTER"] = "I";
				}
				else
				{
					fieldData["SHOW_FILTER"] = "E";
				}
				fieldData["SHOW_IN_LIST"] = "Y";
			}

			if(typeId === BX.UI.EntityUserFieldType.enumeration)
			{
				if(!fieldData.hasOwnProperty("SETTINGS"))
				{
					fieldData["SETTINGS"] = {};
				}
			}

			if(
				typeId === BX.UI.EntityUserFieldType.enumeration
				|| typeId === BX.UI.EntityUserFieldType.crmStatus
			)
			{
				fieldData["SETTINGS"]["DISPLAY"] = (fieldData["SETTINGS"]["DISPLAY"] || "UI");
			}

			if(typeId === BX.UI.EntityUserFieldType.boolean)
			{
				if(!fieldData.hasOwnProperty("SETTINGS"))
				{
					fieldData["SETTINGS"] = {};
				}

				fieldData["SETTINGS"]["LABEL_CHECKBOX"] = fieldData["EDIT_FORM_LABEL"];
			}

			if(typeId === BX.UI.EntityUserFieldType.double)
			{
				if(!fieldData.hasOwnProperty("SETTINGS"))
				{
					fieldData["SETTINGS"] = {};
				}

				fieldData["SETTINGS"]["PRECISION"] = 2;
			}

			fieldData["CONTEXT_PARAMS"] = this._contextParams;

			if(mode === BX.UI.EntityEditorMode.view)
			{
				BX.Main.UF.ViewManager.add({ "FIELDS": [fieldData] }, onSuccess);
			}
			else
			{
				BX.Main.UF.EditManager.add({ "FIELDS": [fieldData] }, onSuccess);
			}

			BX.onCustomEvent(window, "BX.UI.EntityEditor:onUserFieldAdd", [ this, fieldData ]);

			return promise;
		},
		updateField: function(fieldData, mode)
		{
			fieldData["ENTITY_ID"] = this._fieldEntityId;
			fieldData["SIGNATURE"] = this._creationSignature;

			if(BX.type.isNotEmptyString(fieldData["EDIT_FORM_LABEL"]))
			{
				this.addFieldLabel("EDIT_FORM_LABEL", fieldData["EDIT_FORM_LABEL"], fieldData);
			}

			if(BX.type.isNotEmptyString(fieldData["LIST_COLUMN_LABEL"]))
			{
				this.addFieldLabel("LIST_COLUMN_LABEL", fieldData["LIST_COLUMN_LABEL"], fieldData);
			}

			if(BX.type.isNotEmptyString(fieldData["LIST_FILTER_LABEL"]))
			{
				this.addFieldLabel("LIST_FILTER_LABEL", fieldData["LIST_FILTER_LABEL"], fieldData);
			}

			var promise = new BX.Promise();
			var onSuccess = function(result)
			{
				promise.fulfill(result);
			};

			BX.onCustomEvent(window, "BX.UI.EntityEditor:onBeforeUserFieldUpdate", [ this, {
				fields: [fieldData],
				onSuccess: onSuccess
			}]);

			if (fieldData === 'cancelUfUpdate')
			{
				return promise;
			}

			if(mode === BX.UI.EntityEditorMode.view)
			{
				BX.Main.UF.ViewManager.update({ "FIELDS": [fieldData] }, onSuccess);
			}
			else
			{
				BX.Main.UF.EditManager.update({ "FIELDS": [fieldData] }, onSuccess);
			}
			return promise;
		},
		resolveFieldName: function(fieldInfo)
		{
			return BX.prop.getString(fieldInfo, "FIELD", "");
		},
		addFieldLabel: function(name, value, fieldData)
		{
			var languages = BX.prop.getArray(this._settings, "languages", []);
			if(languages.length === 0)
			{
				fieldData[name] = value;
				return;
			}

			fieldData[name] = {};
			for(var i = 0, length = languages.length; i < length; i++)
			{
				var language = languages[i];
				fieldData[name][language["LID"]] = value;
			}
		},
		prepareSchemeElementSettings: function(fieldInfo)
		{
			var name = BX.prop.getString(fieldInfo, "FIELD", "");
			if(name === "")
			{
				return null;
			}

			if(BX.prop.getString(fieldInfo, "USER_TYPE_ID", "") === "")
			{
				fieldInfo["USER_TYPE_ID"] = "string";
			}

			if(BX.prop.getString(fieldInfo, "ENTITY_ID", "") === "")
			{
				fieldInfo["ENTITY_ID"] = this._fieldEntityId;
			}

			if(BX.prop.getInteger(fieldInfo, "ENTITY_VALUE_ID", 0) <= 0)
			{
				fieldInfo["ENTITY_VALUE_ID"] = this._entityId;
			}

			return(
				{
					name: name,
					originalTitle: BX.prop.getString(fieldInfo, "EDIT_FORM_LABEL", name),
					title: BX.prop.getString(fieldInfo, "EDIT_FORM_LABEL", name),
					type: "userField",
					required: BX.prop.getString(fieldInfo, "MANDATORY", "N") === "Y",
					data: { fieldInfo: fieldInfo }
				}
			);
		},
		createSchemeElement: function(fieldInfo)
		{
			return BX.UI.EntitySchemeElement.create(this.prepareSchemeElementSettings(fieldInfo));
		},
		updateSchemeElement: function(element, fieldInfo)
		{
			var settings = this.prepareSchemeElementSettings(fieldInfo);
			settings["title"] = element.getTitle();
			element.mergeSettings(settings);
		},
		registerActiveField: function(field)
		{
			var name = field.getName();
			this._activeFields[name] = field;

			BX.Main.UF.EditManager.registerField(name, field.getFieldInfo(), field.getFieldNode());
		},
		unregisterActiveField: function(field)
		{
			var name = field.getName();
			if(this._activeFields.hasOwnProperty(name))
			{
				delete this._activeFields[name];
			}
			BX.Main.UF.EditManager.unRegisterField(name);
		},
		setValidationEnabled: function(isEnabled)
		{
			this._validationEnabled = !!isEnabled;
		},
		validate: function(result)
		{
			var names = [];
			for(var name in this._activeFields)
			{
				if(this._activeFields.hasOwnProperty(name))
				{
					names.push(name);
				}
			}

			if(this._validationEnabled && names.length > 0)
			{
				this._validationResult = result;
				BX.Main.UF.EditManager.validate(
					names,
					BX.delegate(this.onValidationComplete, this)
				);
			}
			else
			{
				window.setTimeout(
					BX.delegate(
						function()
						{
							if(this._validationPromise)
							{
								this._validationPromise.fulfill();
								this._validationPromise = null;
							}
						},
						this
					),
					0
				);
			}

			this._validationPromise = new BX.Promise();
			return this._validationPromise;
		},
		onValidationComplete: function(results)
		{
			var name;
			//Reset previous messages
			for(name in this._activeFields)
			{
				if(this._activeFields.hasOwnProperty(name))
				{
					this._activeFields[name].clearError();
				}
			}

			//Add new messages
			for(name in results)
			{
				if(!results.hasOwnProperty(name))
				{
					continue;
				}

				if(this._activeFields.hasOwnProperty(name))
				{
					var field = this._activeFields[name];
					field.showError(results[name]);
					this._validationResult.addError(BX.UI.EntityValidationError.create({ field: field }));
				}
			}

			if(this._validationPromise)
			{
				this._validationPromise.fulfill();
			}

			this._validationResult = null;
			this._validationPromise = null;
		}
	};
	BX.UI.EntityUserFieldManager.items = {};
	BX.UI.EntityUserFieldManager.create = function(id, settings)
	{
		var self = new BX.UI.EntityUserFieldManager();
		self.initialize(id, settings);
		this.items[id] = self;
		return self;
	};
	BX.UI.EntityUserFieldManager.getById = function(id)
	{
		return this.items.hasOwnProperty(id) ? this.items[id] : null;
	}
}

if(typeof BX.UI.EntityUserFieldLayoutLoader === "undefined")
{
	BX.UI.EntityUserFieldLayoutLoader = function()
	{
		this._id = "";
		this._settings = {};
		this._mode = BX.UI.EntityEditorMode.view;
		this._enableBatchMode = true;
		this._owner = null;
		this._items = [];
	};
	BX.UI.EntityUserFieldLayoutLoader.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};
			this._mode = BX.prop.getInteger(this._settings, "mode", BX.UI.EntityEditorMode.view);
			this._enableBatchMode = BX.prop.getBoolean(this._settings, "enableBatchMode", true);
			this._owner = BX.prop.get(this._settings, "owner", null);
		},
		getId: function()
		{
			return this._id;
		},
		getOwner: function()
		{
			return this._owner;
		},
		addItem: function(item)
		{
			this._items.push(item);
		},
		run: function()
		{
			if(!this._enableBatchMode)
			{
				this.startRequest();
			}
		},
		runBatch: function()
		{
			if(this._enableBatchMode)
			{
				this.startRequest();
			}
		},
		startRequest: function()
		{
			if(this._items.length === 0)
			{
				return;
			}

			var fields = [];
			for(var i = 0, length = this._items.length; i < length; i++)
			{
				if(BX.prop.getString(this._items[i], "name", "") !== "")
				{
					fields.push(BX.prop.getObject(this._items[i], "field", {}));
				}
			}

			if(fields.length === 0)
			{
				return;
			}

			var data = { "FIELDS": fields, "FORM": this._id, "CONTEXT": "UI_EDITOR" };

			if(this._mode === BX.UI.EntityEditorMode.view)
			{
				BX.Main.UF.Manager.getView(data, BX.delegate(this.onRequestComplete, this));
			}
			else
			{
				BX.Main.UF.Manager.getEdit(data, BX.delegate(this.onRequestComplete, this));
			}
		},
		onRequestComplete: function(result)
		{
			for(var i = 0, length = this._items.length; i < length; i++)
			{
				var item = this._items[i];
				var name = BX.prop.getString(item, "name", "");
				var callback = BX.prop.getFunction(item, "callback", null);
				if(name !== "" && callback !== null)
				{
					callback(BX.prop.getObject(result, name, {}));
				}
			}
		}
	};
	BX.UI.EntityUserFieldLayoutLoader.create = function(id, settings)
	{
		var self = new BX.UI.EntityUserFieldLayoutLoader();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorUserField === "undefined")
{
	/**
	 * @extends BX.UI.EntityEditorField
	 * @constructor
	 */
	BX.UI.EntityEditorUserField = function()
	{
		BX.UI.EntityEditorUserField.superclass.constructor.apply(this);
		this._innerWrapper = null;

		this._isLoaded = false;
		this._focusOnLoad = false;

		this.isClickBinded = false;
	};

	BX.extend(BX.UI.EntityEditorUserField, BX.UI.EntityEditorField);
	BX.UI.EntityEditorUserField.prototype.doInitialize = function()
	{
		BX.UI.EntityEditorUserField.superclass.doInitialize.apply(this);
		this._manager = this._editor.getUserFieldManager();
	};
	BX.UI.EntityEditorUserField.prototype.getModeSwitchType = function(mode)
	{
		var result = BX.UI.EntityEditorModeSwitchType.common;
		if(mode === BX.UI.EntityEditorMode.edit)
		{
			result |= BX.UI.EntityEditorModeSwitchType.button|BX.UI.EntityEditorModeSwitchType.content;
		}
		return result;
	};
	BX.UI.EntityEditorUserField.prototype.getContentWrapper = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorUserField.prototype.getFieldInfo = function()
	{
		return this._schemeElement.getDataParam("fieldInfo", {});
	};
	BX.UI.EntityEditorUserField.prototype.getFieldType = function()
	{
		return BX.prop.getString(this.getFieldInfo(), "USER_TYPE_ID", "");
	};
	BX.UI.EntityEditorUserField.prototype.getFieldSettings = function()
	{
		return BX.prop.getObject(this.getFieldInfo(), "SETTINGS", {});
	};
	BX.UI.EntityEditorUserField.prototype.isMultiple = function()
	{
		return BX.prop.getString(this.getFieldInfo(), "MULTIPLE", "N") === "Y";
	};
	BX.UI.EntityEditorUserField.prototype.getEntityValueId = function()
	{
		return BX.prop.getString(this.getFieldInfo(), "ENTITY_VALUE_ID", "");
	};
	BX.UI.EntityEditorUserField.prototype.getFieldValue = function()
	{
		var fieldData = this.getValue();
		var value = BX.prop.getArray(fieldData, "VALUE", null);
		if(value === null)
		{
			value = BX.prop.getString(fieldData, "VALUE", "");
		}
		return value;
	};
	BX.UI.EntityEditorUserField.prototype.getFieldSignature = function()
	{
		return BX.prop.getString(this.getValue(), "SIGNATURE", "");
	};
	BX.UI.EntityEditorUserField.prototype.isTitleEnabled = function()
	{
		var info = this.getFieldInfo();
		var typeName = BX.prop.getString(info, "USER_TYPE_ID", "");

		if(typeName !== 'boolean')
		{
			return true;
		}

		//Disable title for checkboxes only.
		return BX.prop.getString(BX.prop.getObject(info, "SETTINGS", {}), "DISPLAY", "") !== "CHECKBOX";
	};
	BX.UI.EntityEditorUserField.prototype.getFieldNode = function()
	{
		return this._innerWrapper;
	};
	BX.UI.EntityEditorUserField.prototype.checkIfNotEmpty = function(value)
	{
		if(BX.prop.getBoolean(value, "IS_EMPTY", false))
		{
			return false;
		}

		var fieldValue;
		if(this.getFieldType() === BX.UI.EntityUserFieldType.boolean)
		{
			fieldValue = BX.prop.getString(value, "VALUE", "");
			return fieldValue !== "";
		}

		fieldValue = BX.prop.getArray(value, "VALUE", null);
		if(fieldValue === null)
		{
			fieldValue = BX.prop.getString(value, "VALUE", "");
		}
		return BX.type.isArray(fieldValue) ? fieldValue.length > 0 : fieldValue !== "";
	};
	BX.UI.EntityEditorUserField.prototype.getValue = function(defaultValue)
	{
		if(defaultValue === undefined)
		{
			defaultValue = null;
		}

		if(!this._model)
		{
			return defaultValue;
		}

		return this._model.getField(this.getName(), defaultValue);
	};
	BX.UI.EntityEditorUserField.prototype.hasContentToDisplay = function()
	{
		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			return true;
		}
		// if rest field marked as "show always", it always has value and should be shown:
		if (this.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways)
			&& this.getFieldType().indexOf('rest_') === 0)
		{
			return true;
		}
		return this.checkIfNotEmpty(this.getValue());
	};
	BX.UI.EntityEditorUserField.prototype.layout = function(options)
	{
		if(this._hasLayout)
		{
			return;
		}

		var name = this.getName();
		var title = this.getTitle();

		var fieldInfo = this.getFieldInfo();
		var fieldData = this.getValue();

		var signature = BX.prop.getString(fieldData, "SIGNATURE", "");

		this.ensureWrapperCreated();
		this.adjustWrapper();

		if(!this.isNeedToDisplay())
		{
			this.registerLayout(options);
			this._hasLayout = true;
			return;
		}

		var fieldType = this.getFieldType();
		if(fieldType === BX.UI.EntityUserFieldType.string)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-custom-text");
		}
		else if(fieldType === BX.UI.EntityUserFieldType.integer || fieldType === BX.UI.EntityUserFieldType.double)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-custom-number");
		}
		else if(fieldType === BX.UI.EntityUserFieldType.money)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-custom-money");
		}
		else if(fieldType === BX.UI.EntityUserFieldType.date || fieldType === BX.UI.EntityUserFieldType.datetime)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-custom-date");
		}
		else if(fieldType === BX.UI.EntityUserFieldType.boolean)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-field-custom-checkbox");
		}
		else if(fieldType === BX.UI.EntityUserFieldType.enumeration)
		{
			BX.addClass(
				this._wrapper,
				this.isMultiple()
					? "ui-entity-editor-content-block-field-custom-multiselect"
					: "ui-entity-editor-content-block-field-custom-select"
			);
		}
		else if(fieldType === BX.UI.EntityUserFieldType.file)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-custom-file");
		}
		else if(fieldType === BX.UI.EntityUserFieldType.url)
		{
			BX.addClass(this._wrapper, "ui-entity-editor-content-block-field-custom-link");
		}

		this._innerWrapper = null;

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			if(this.isTitleEnabled())
			{
				this._wrapper.appendChild(this.createTitleNode(title));
			}

			this._innerWrapper = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block" }
				}
			);
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			this._innerWrapper = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block" }
				}
			);
		}
		this._wrapper.appendChild(this._innerWrapper);

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		//It is strongly required to append wrapper to container before "setupContentHtml" will be called otherwise user field initialization will fail.
		this.registerLayout(options);

		if(this.hasContentToDisplay())
		{
			var html = BX.prop.getString(options, "html", "");
			if(html === "")
			{
				//Try get preloaded HTML
				html = BX.prop.getString(
					BX.prop.getObject(fieldData, "HTML", {}),
					BX.UI.EntityEditorMode.getName(this._mode).toUpperCase(),
					""
				);

			}
			if(html !== "")
			{
				this.setupContentHtml(html);
				this._hasLayout = true;
			}
			else
			{
				this._isLoaded = false;

				var loader = null;
				//Ignore group loader for single edit mode
				if(!this.isInSingleEditMode())
				{
					loader = BX.prop.get(options, "userFieldLoader", null);
				}

				if(!loader)
				{
					loader = BX.UI.EntityUserFieldLayoutLoader.create(
						this._id,
						{ mode: this._mode, enableBatchMode: false }
					);
				}

				var fieldParams = BX.clone(fieldInfo);
				fieldParams["SIGNATURE"] = signature;
				if(fieldType === BX.UI.EntityUserFieldType.file && BX.type.isObject(fieldParams["ADDITIONAL"]))
				{
					var ownerToken = BX.prop.getString(
						BX.prop.getObject(fieldData, "EXTRAS", {}),
						"OWNER_TOKEN",
						""
					);
					if(ownerToken !== "")
					{
						fieldParams["ADDITIONAL"]["URL_TEMPLATE"] += "&owner_token=" + encodeURIComponent(ownerToken);
					}
				}
				if(this.checkIfNotEmpty(fieldData))
				{
					var value = BX.prop.getArray(fieldData, "VALUE", null);
					if(value === null)
					{
						value = BX.prop.getString(fieldData, "VALUE", "");
					}
					fieldParams["VALUE"] = value;
				}

				this.adjustFieldParams(fieldParams, true);
				loader.addItem(
					{
						name: name,
						field: fieldParams,
						callback: BX.delegate(this.onLayoutLoaded, this)
					}
				);
				loader.run();
			}
		}
		else
		{
			this._innerWrapper.appendChild(document.createTextNode(BX.message("UI_ENTITY_EDITOR_FIELD_EMPTY")));
			this._hasLayout = true;
		}
	};
	BX.UI.EntityEditorUserField.prototype.doRegisterLayout = function()
	{
	};
	BX.UI.EntityEditorUserField.prototype.adjustFieldParams = function(fieldParams, isLayoutContext)
	{
		var fieldType = this.getFieldType();
		if(fieldType === BX.UI.EntityUserFieldType.boolean)
		{
			//HACK: Overriding original label for boolean field
			if(!BX.type.isPlainObject(fieldParams["SETTINGS"]))
			{
				fieldParams["SETTINGS"] = {};
			}
			fieldParams["SETTINGS"]["LABEL_CHECKBOX"] = this.getTitle();
		}

		//HACK: We have to assign fake ENTITY_VALUE_ID for render predefined value of new entity
		if(isLayoutContext
			&& typeof fieldParams["VALUE"] !== "undefined"
			&& this._mode === BX.UI.EntityEditorMode.edit
			&& BX.prop.getInteger(fieldParams, "ENTITY_VALUE_ID") <= 0
		)
		{
			fieldParams["ENTITY_VALUE_ID"] = 1;
		}

	};
	BX.UI.EntityEditorUserField.prototype.validate = function()
	{
		return true;
	};
	BX.UI.EntityEditorUserField.prototype.save = function()
	{
	};
	BX.UI.EntityEditorUserField.prototype.focus = function()
	{
		if(this._mode !== BX.UI.EntityEditorMode.edit)
		{
			return;
		}

		if(this._isLoaded)
		{
			this.doFocus();
		}
		else
		{
			this._focusOnLoad = true;
		}
	};
	BX.UI.EntityEditorUserField.prototype.doFocus = function()
	{
		BX.Main.UF.Factory.focus(this.getName());
	};
	BX.UI.EntityEditorUserField.prototype.setupContentHtml = function(html)
	{
		if(this._innerWrapper)
		{
			//console.log("setupContentHtml: %s->%s->%s", this._editor.getId(), this._id, BX.UI.EntityEditorMode.getName(this._mode));

			BX.html(this._innerWrapper, html).then(
				function()
				{
					this.onLayoutSuccess();

					this._isLoaded = true;
					if(this._focusOnLoad === true)
					{
						this.doFocus();
						this._focusOnLoad = false;
					}
				}.bind(this)
			);
		}
	};
	BX.UI.EntityEditorUserField.prototype.doSetActive = function()
	{
		//We can't call this._manager.registerActiveField. We have to wait field layout load(see onLayoutSuccess)
		if(!this._isActive)
		{
			this._manager.unregisterActiveField(this);
		}
	};
	BX.UI.EntityEditorUserField.prototype.rollback = function()
	{
		this._manager.unregisterActiveField(this);
	};
	BX.UI.EntityEditorUserField.prototype.onLayoutSuccess = function()
	{
		if(this._isActive)
		{
			this._manager.registerActiveField(this);
		}

		//Add Change Listener after timeout for prevent markAsChanged call in process of field initialization.
		window.setTimeout(
			function(){
				BX.bindDelegate(
					this._innerWrapper,
					"bxchange",
					{ tag: [ "input", "select", "textarea" ] },
					this._changeHandler
				);
			}.bind(this),
			200
		);

		//HACK: Try to resolve employee change button
		var fieldType = this.getFieldType();
		if(fieldType === BX.UI.EntityUserFieldType.employee)
		{
			var button = this._innerWrapper.querySelector('.feed-add-destination-link');
			if(button && this.isClickBinded)
			{
				BX.bind(button, "click", BX.delegate(this.onEmployeeSelectorOpen, this));
				this.isClickBinded = true;
			}
		}

		//HACK: Mark empty boolean field as changed because of default value
		if(fieldType === BX.UI.EntityUserFieldType.boolean)
		{
			if(this._mode === BX.UI.EntityEditorMode.edit && !this.checkIfNotEmpty(this.getValue()))
			{
				this.markAsChanged();
			}
		}

		//Field content is added successfully. Layout is ready.
		if(!this._hasLayout)
		{
			this._hasLayout = true;
		}

		this.addExternalEventsHandlers();
	};
	BX.UI.EntityEditorUserField.prototype.doClearLayout = function(options)
	{
		this._innerWrapper = null;
		this.removeExternalEventsHandlers();
	};
	BX.UI.EntityEditorUserField.prototype.addExternalEventsHandlers = function()
	{
		this.removeExternalEventsHandlers();
		// Handler could be called by UF to trigger _changeHandler in complicated cases
		BX.addCustomEvent(window, "onUIEntityEditorUserFieldExternalChanged", BX.proxy(this.userFieldExternalChangedHandler, this));
		BX.addCustomEvent(window, "onUIEntityEditorUserFieldSetValidator", BX.proxy(this.userFieldSetValidatorHandler, this));
	};
	BX.UI.EntityEditorUserField.prototype.removeExternalEventsHandlers = function()
	{
		BX.removeCustomEvent(window, "onUIEntityEditorUserFieldExternalChanged", BX.proxy(this.userFieldExternalChangedHandler, this));
		BX.removeCustomEvent(window, "onUIEntityEditorUserFieldSetValidator", BX.proxy(this.userFieldSetValidatorHandler, this));
	};
	BX.UI.EntityEditorUserField.prototype.userFieldExternalChangedHandler = function(fieldId)
	{
		if (fieldId == this._id && BX.type.isFunction(this._changeHandler))
		{
			this._changeHandler();
		}
	};
	BX.UI.EntityEditorUserField.prototype.release = function()
	{
		this.removeExternalEventsHandlers();
	};
	BX.UI.EntityEditorUserField.prototype.userFieldSetValidatorHandler = function(fieldId, callback)
	{
		if (fieldId == this._id && BX.type.isFunction(callback))
		{
			this.validate = callback;
		}
	};
	BX.UI.EntityEditorUserField.prototype.onLayoutLoaded = function(result)
	{
		var html = BX.prop.getString(result, "HTML", "");
		if(html !== "")
		{
			this.setupContentHtml(html);
			this._hasLayout = true;
			this.raiseLayoutEvent();
		}
	};
	BX.UI.EntityEditorUserField.prototype.onEmployeeSelectorOpen = function(e)
	{
		var button = BX.getEventTarget(e);
		if(!button)
		{
			return;
		}

		//HACK: Try to resolve UserFieldEmployee object
		var match = button.id.match(/^add_user_([a-z_0-9-]+)/i);
		if(BX.type.isArray(match) && match.length > 1)
		{
			var selector = BX.Intranet.UserFieldEmployee.instance(match[1]);
			if(selector)
			{
				BX.addCustomEvent(selector, 'onUpdateValue', this._changeHandler);
			}
		}
	};
	BX.UI.EntityEditorUserField.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorUserField();
		self.initialize(id, settings);
		return self;
	}
}

if (typeof BX.UI.EntityEditorUserFieldListItem === "undefined")
{
	BX.UI.EntityEditorUserFieldListItem = function()
	{
		BX.UI.EntityEditorUserFieldListItem.superclass.constructor.apply(this);
	};
	BX.extend(BX.UI.EntityEditorUserFieldListItem, BX.UI.EntityEditorFieldConfiguratorEnumItem);
	BX.UI.EntityEditorUserFieldListItem.prototype.prepareData = function()
	{
		var value = this._labelInput ? BX.util.trim(this._labelInput.value) : "";
		if(value === "")
		{
			return null;
		}

		var data = { "VALUE": value };
		var id = BX.prop.getInteger(this._data, "ID", 0);
		if(id > 0)
		{
			data["ID"] = id;
		}

		var xmlId = BX.prop.getString(this._data, "XML_ID", "");
		if(id > 0)
		{
			data["XML_ID"] = xmlId;
		}

		return data;
	};
	BX.UI.EntityEditorUserFieldListItem.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorUserFieldListItem();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof(BX.UI.UserFieldTypeMenu) === "undefined")
{
	BX.UI.UserFieldTypeMenu = function()
	{
		this._id = null;
		this._settings = {};
		this._items = null;
		this._isOpened = false;

		this._wrapper = null;
		this._innerWrapper = null;

		this._topScrollButton = null;
		this._bottomScrollButton = null;

		this._bottomButtonMouseOverHandler = BX.delegate(this.onBottomButtonMouseOver, this);
		this._bottomButtonMouseOutHandler = BX.delegate(this.onBottomButtonMouseOut, this);

		this._topButtonMouseOverHandler = BX.delegate(this.onTopButtonMouseOver, this);
		this._topButtonMouseOutHandler = BX.delegate(this.onTopButtonMouseOut, this);

		this._scrollHandler = BX.throttle(this.onScroll, 100, this);

		this._enableScrollToBottom = false;
		this._enableScrollToTop = false;

		this._popup = null;
	};

	BX.UI.UserFieldTypeMenu.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._items = [];
			var itemData = BX.prop.getArray(settings, "items");
			for(var i = 0, length = itemData.length; i < length; i++)
			{
				var data = itemData[i];
				data["menu"] = this;
				this._items.push(
					BX.UI.UserFieldTypeMenuItem.create(
						BX.prop.getString(data, "value"),
						data
					)
				);
			}
		},
		getId: function()
		{
			return this._id;
		},
		isOpened: function()
		{
			return this._isOpened;
		},
		open: function(anchor)
		{
			if(this._isOpened)
			{
				return;
			}

			this._popup = new BX.PopupWindow(
				this._id,
				anchor,
				{
					autoHide: true,
					draggable: false,
					offsetLeft: 0,
					offsetTop: 0,
					noAllPaddings: true,
					bindOptions: { forceBindPosition: true },
					closeByEsc: true,
					events:
						{
							onPopupShow: BX.delegate(this.onPopupShow, this),
							onPopupClose: BX.delegate(this.onPopupClose, this),
							onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
						},
					content: this.prepareContent()
				}
			);
			this._popup.show();
		},
		close: function()
		{
			if(!this._isOpened)
			{
				return;
			}

			if(this._popup)
			{
				this._popup.close();
			}
		},
		prepareContent: function()
		{
			this._wrapper = BX.create("div", { props: { className: "ui-entity-editor-popup-create-field-popup" } });

			var scrollIcon = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"42\" height=\"13\" viewBox=\"0 0 42 13\">\n" +
				"  <polyline fill=\"none\" stroke=\"#CACDD1\" stroke-width=\"2\" points=\"274 98 284 78.614 274 59\" transform=\"rotate(90 186 -86.5)\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" +
				"</svg>\n";

			this._topScrollButton = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-popup-create-scroll-control-top" },
					html: scrollIcon
				}
			);
			this._wrapper.appendChild(this._topScrollButton);

			this._bottomScrollButton = BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-popup-create-scroll-control-bottom" },
					html: scrollIcon
				}
			);
			this._wrapper.appendChild(this._bottomScrollButton);

			this._innerWrapper = BX.create("div", { props: { className: "ui-entity-editor-popup-create-field-list" } });
			this._wrapper.appendChild(this._innerWrapper);

			for(var i = 0, length = this._items.length; i < length; i++)
			{
				this._innerWrapper.appendChild(this._items[i].prepareContent());
			}
			return this._wrapper;
		},
		adjust: function()
		{
			var height = this._innerWrapper.offsetHeight;
			var scrollTop = this._innerWrapper.scrollTop;
			var scrollHeight = this._innerWrapper.scrollHeight;

			if(scrollTop === 0)
			{
				BX.addClass(this._topScrollButton, "control-hide");
			}
			else
			{
				BX.removeClass(this._topScrollButton, "control-hide");
			}

			if((scrollTop + height) === scrollHeight)
			{
				BX.addClass(this._bottomScrollButton, "control-hide");
			}
			else
			{
				BX.removeClass(this._bottomScrollButton, "control-hide");
			}
		},
		onItemSelect: function(item)
		{
			var callback = item.getCallback();
			if (!BX.type.isFunction(callback))
			{
				callback = BX.prop.getFunction(this._settings, "callback", null);
			}

			if (callback)
			{
				callback(this, item);
			}
		},
		onPopupShow: function()
		{
			this._isOpened = true;

			BX.bind(this._bottomScrollButton, "mouseover", this._bottomButtonMouseOverHandler);
			BX.bind(this._bottomScrollButton, "mouseout", this._bottomButtonMouseOutHandler);

			BX.bind(this._topScrollButton, "mouseover", this._topButtonMouseOverHandler);
			BX.bind(this._topScrollButton, "mouseout", this._topButtonMouseOutHandler);

			BX.bind(this._innerWrapper, "scroll", this._scrollHandler);

			window.setTimeout(this.adjust.bind(this), 100);
		},
		onPopupClose: function()
		{
			if(this._popup)
			{
				this._popup.destroy();
			}
		},
		onPopupDestroy: function()
		{
			this._isOpened = false;

			BX.unbind(this._bottomScrollButton, "mouseover", this._bottomButtonMouseOverHandler);
			BX.unbind(this._bottomScrollButton, "mouseout", this._bottomButtonMouseOutHandler);

			BX.unbind(this._topScrollButton, "mouseover", this._topButtonMouseOverHandler);
			BX.unbind(this._topScrollButton, "mouseout", this._topButtonMouseOutHandler);

			BX.unbind(this._innerWrapper, "scroll", this._scrollHandler);

			this._wrapper = null;
			this._innerWrapper = null;
			this._topScrollButton = null;
			this._bottomScrollButton = null;

			this._popup = null;
		},
		onBottomButtonMouseOver: function(e)
		{
			if(this._enableScrollToBottom)
			{
				return;
			}

			this._enableScrollToBottom = true;
			this._enableScrollToTop = false;

			(function scroll()
			{
				if(!this._enableScrollToBottom)
				{
					return;
				}

				var el = this._innerWrapper;
				if((el.scrollTop + el.offsetHeight) !== el.scrollHeight)
				{
					el.scrollTop += 3;
				}

				if((el.scrollTop + el.offsetHeight) === el.scrollHeight)
				{
					this._enableScrollToBottom = false;
					//console.log("scrollToBottom: completed");
				}
				else
				{
					window.setTimeout(scroll.bind(this), 20);
				}
			}).bind(this)();
		},
		onBottomButtonMouseOut: function()
		{
			this._enableScrollToBottom = false;
		},
		onTopButtonMouseOver: function(e)
		{
			if(this._enableScrollToTop)
			{
				return;
			}

			this._enableScrollToBottom = false;
			this._enableScrollToTop = true;

			(function scroll()
			{
				if(!this._enableScrollToTop)
				{
					return;
				}

				var el = this._innerWrapper;
				if(el.scrollTop > 0)
				{
					el.scrollTop -= 3;
				}

				if(el.scrollTop === 0)
				{
					this._enableScrollToTop = false;
					//console.log("scrollToTop: completed");
				}
				else
				{
					window.setTimeout(scroll.bind(this), 20);
				}
			}).bind(this)();
		},
		onTopButtonMouseOut: function()
		{
			this._enableScrollToTop = false;
		},
		onScroll: function(e)
		{
			this.adjust();
		}
	};
	BX.UI.UserFieldTypeMenu.create = function(id, settings)
	{
		var self = new BX.UI.UserFieldTypeMenu();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof(BX.UI.UserFieldTypeMenuItem) === "undefined")
{
	BX.UI.UserFieldTypeMenuItem = function()
	{
		this._id = "";
		this._settings = null;
		this._menu = "";
		this._value = "";
		this._text = "";
		this._legend = "";
		this._callback = null;
	};
	BX.UI.UserFieldTypeMenuItem.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};
			this._menu = BX.prop.get(settings, "menu");
			this._value = BX.prop.getString(settings, "value");
			this._text = BX.prop.getString(settings, "text");
			this._legend = BX.prop.getString(settings, "legend");
			this._callback = BX.prop.getFunction(settings, "callback", null);
		},
		getId: function()
		{
			return this._id;
		},
		getValue: function()
		{
			return this._value;
		},
		getText: function()
		{
			return this._text;
		},
		getLegend: function()
		{
			return this._legend;
		},
		getCallback: function()
		{
			return this._callback;
		},
		prepareContent: function()
		{
			var wrapper = BX.create(
				"span",
				{
					props: { className: "ui-entity-editor-popup-create-field-item" },
					events: { click: BX.delegate(this.onClick, this) }
				}
			);

			wrapper.appendChild(
				BX.create(
					"span",
					{
						props: { className: "ui-entity-editor-popup-create-field-item-title" },
						text: this._text
					}
				)
			);

			wrapper.appendChild(
				BX.create(
					"span",
					{
						props: { className: "ui-entity-editor-popup-create-field-item-desc" },
						text: this._legend
					}
				)
			);

			return wrapper;
		},
		onClick: function(e)
		{
			this._menu.onItemSelect(this);
		}
	};
	BX.UI.UserFieldTypeMenuItem.create = function(id, settings)
	{
		var self = new BX.UI.UserFieldTypeMenuItem();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityEditorUserFieldConfigurator === "undefined")
{
	BX.UI.EntityEditorUserFieldConfigurator = function()
	{
		BX.UI.EntityEditorUserFieldConfigurator.superclass.constructor.apply(this);
		this._field = null;
		this._typeId = "";
		this._isLocked = false;

		this._labelInput = null;
		this._saveButton = null;
		this._cancelButton = null;
		this._isTimeEnabledCheckBox = null;
		this._isRequiredCheckBox = null;
		this._isMultipleCheckBox = null;
		this._showAlwaysCheckBox = null;
		this._optionWrapper = null;

		this._enumConfigurator = null;

		this._enableMandatoryControl = true;
		this._mandatoryConfigurator = null;
	};

	BX.extend(BX.UI.EntityEditorUserFieldConfigurator, BX.UI.EntityEditorFieldConfigurator);

	BX.UI.EntityEditorUserFieldConfigurator.prototype.checkField = function()
	{
		if(this._field && !(this._field instanceof BX.UI.EntityEditorUserField))
		{
			throw "EntityEditorUserFieldConfigurator. The 'field' param must be EntityEditorUserField.";
		}
	};
	BX.UI.EntityEditorUserFieldConfigurator.prototype.getDefaultFieldLabel = function()
	{
		var manager = this._editor.getUserFieldManager();
		return manager.getDefaultFieldLabel(this._typeId);
	};

	BX.UI.EntityEditorUserFieldConfigurator.prototype.getInputTitle = function()
	{
		var manager = this._editor.getUserFieldManager();
		return this._field ? this._field.getTitle() : manager.getDefaultFieldLabel(this._typeId);
	};
	BX.UI.EntityEditorUserFieldConfigurator.prototype.getInputContainer = function()
	{
		var title = BX.message("UI_ENTITY_EDITOR_FIELD_TITLE");
		this._labelInput = BX.create("input",
			{
				attrs:
					{
						className: "ui-ctl-element",
						type: "text",
						value: this.getInputTitle()
					}
			}
		);

		return BX.create(
			"div",
			{
				props: { className: "ui-entity-editor-content-block" },
				children: [
					BX.create(
						"div",
						{
							props: { className: "ui-entity-editor-block-title" },
							children: [
								BX.create(
									"span",
									{
										attrs: { className: "ui-entity-editor-block-title-text" },
										text: title
									}
								)
							]
						}
					),
					BX.create(
						"div",
						{
							props: { className: "ui-entity-editor-content-block" },
							children: [
								BX.create(
									"div",
									{
										props: { className: "ui-ctl ui-ctl-textbox ui-ctl-w100" },
										children: [ this._labelInput ]
									}
								)
							]
						}
					)
				]
			}
		);
	};

	BX.UI.EntityEditorUserFieldConfigurator.prototype.layoutInnerConfigurator = function(innerConfig, listItems, nextNode)
	{
		if (
			BX.Type.isPlainObject(innerConfig)
			&& BX.Type.isArray(listItems)
			&& this._enumConfigurator === null
		)
		{
			var enums = [];

			for (var i = 0; i < listItems.length; i++)
			{
				enums.push({
					ID: listItems[i]["VALUE"],
					VALUE: listItems[i]["NAME"],
					XML_ID: ""
				});
			}

			var fieldSettings = this._settings.field._schemeElement._data.fieldInfo.SETTINGS;
			var showDisplaySettings = false;

			if (this._typeId === BX.UI.EntityUserFieldType.enumeration)
			{
				showDisplaySettings = true;
			}

			this._enumConfigurator = BX.UI.EntityEditorEnumConfigurator.create({
				enumInfo: {
					enumItems: enums,
					innerConfig: innerConfig,
				},
				wrapper: this._wrapper,
				nextNode: (BX.Type.isDomNode(nextNode) ? nextNode : null),
				display: (fieldSettings ? fieldSettings.DISPLAY : null),
				showDisplaySettings: showDisplaySettings,
			});
			this._enumConfigurator.layout();
		}
	}

	BX.UI.EntityEditorUserFieldConfigurator.prototype.layoutInternal = function()
	{
		this._wrapper.appendChild(this.getInputContainer());

		if (this._typeId === BX.UI.EntityUserFieldType.enumeration)
		{
			if (this._enumConfigurator === null)
			{
				var fieldInfo = (this._field) ? this._field.getFieldInfo() : {};
				var enums = BX.prop.getArray(fieldInfo, "ENUM", []);
				var settings = BX.prop.getObject(fieldInfo, 'SETTINGS', null);
				this._enumConfigurator = BX.UI.EntityEditorUserFieldEnumConfigurator.create({
					enumInfo: { enumItems: enums },
					wrapper: this._wrapper,
					display: (settings ? settings.DISPLAY : null),
					showDisplaySettings: true,
				});
				this._enumConfigurator.layout();
			}
		}

		var innerConfigNextNode = this.getOptionContainer();
		this._wrapper.appendChild(innerConfigNextNode);
		this._wrapper.appendChild(
			BX.create("hr", { props: { className: "ui-entity-editor-line" } })
		);
		this._wrapper.appendChild(this.getButtonContainer());

		if (this._typeId === BX.UI.EntityUserFieldType.crmStatus)
		{
			var innerConfig = this._field.getInnerConfig();
			if (BX.Type.isPlainObject(innerConfig))
			{
				BX.ajax.runAction(
					"crm.status.getItems",
					{ data: { configData: { innerConfig: innerConfig } } }
				).then(
					function(response) {
						if (
							BX.Type.isObject(response)
							&& response.hasOwnProperty("status")
							&& response.status === "success"
							&& response.hasOwnProperty("data")
							&& BX.Type.isArray(response["data"])
						)
						{
							var enumeration = response["data"];
							var items = [];
							for (var i = 0; i < enumeration.length; i++)
							{
								items.push({
									"NAME": enumeration[i]["VALUE"],
									"VALUE": enumeration[i]["ID"]
								});
							}
							this.layoutInnerConfigurator(innerConfig, items, innerConfigNextNode);
						}
						else
						{
							console.error("Invalid server response.");
						}
					}.bind(this),
					function(response) {
						if (
							BX.Type.isObject(response)
							&& response.hasOwnProperty("status")
							&& response["status"] === "error"
							&& response.hasOwnProperty("errors")
							&& BX.Type.isArray(response["errors"])
							&& response["errors"].length > 0
							&& BX.Type.isPlainObject(response["errors"][0])
							&& response["errors"][0].hasOwnProperty("message")
							&& BX.Type.isString(response["errors"][0]["message"])
						)
						{
							console.error(response["errors"][0]["message"]);
						}
						else
						{
							console.error("Invalid server response.");
						}
					}.bind(this)
				);
			}
		}
	};

	BX.UI.EntityEditorUserFieldConfigurator.prototype.getOptionContainer = function()
	{
		var isNew = (this._field === null);
		this._optionWrapper = BX.create(
			"div",
			{
				props: { className: "ui-entity-editor-content-block" }
			}
		);
		this._wrapper.appendChild(
			BX.create(
				"div",
				{
					props: { className: "ui-entity-editor-content-block ui-entity-editor-content-block-checkbox" },
					children: [ this._optionWrapper ]
				}
			)
		);

		if(isNew && (this._typeId === "datetime" || this._typeId === "date"))
		{
			this._isTimeEnabledCheckBox = this.createOption({ caption: BX.message("UI_ENTITY_EDITOR_UF_ENABLE_TIME") });
		}

		if(this._typeId !== "boolean")
		{
			if(this.getEditor().canChangeCommonConfiguration() && this._enableMandatoryControl)
			{
				if(this._mandatoryConfigurator)
				{
					this._isRequiredCheckBox = this.createOption(
						{
							caption: this._mandatoryConfigurator.getTitle() + ":",
							//labelSettings: { props: { className: "ui-entity-new-field-addiction-label" } },
							containerSettings: { props: { className: "ui-entity-new-field-addiction-flex-row" } },
							elements: this._mandatoryConfigurator.getButton().prepareLayout()
						}
					);

					this._isRequiredCheckBox.checked = (this._field && this._field.isRequired())
						|| this._mandatoryConfigurator.isCustomized();

					this._mandatoryConfigurator.setSwitchCheckBox(this._isRequiredCheckBox);
					this._mandatoryConfigurator.setLabel(this._isRequiredCheckBox.nextSibling);

					this._mandatoryConfigurator.setEnabled(this._isRequiredCheckBox.checked);
					this._mandatoryConfigurator.adjust();
				}
				else
				{
					this._isRequiredCheckBox = this.createOption({ caption: BX.message("UI_ENTITY_EDITOR_UF_REQUIRED_FIELD") });
					this._isRequiredCheckBox.checked = this._field && this._field.isRequired();
				}
			}

			if(isNew)
			{
				this._isMultipleCheckBox = this.createOption({ caption: BX.message("UI_ENTITY_EDITOR_UF_MULTIPLE_FIELD") });
			}
		}

		//region Show Always
		this._showAlwaysCheckBox = this.createOption(
			{ caption: BX.message("UI_ENTITY_EDITOR_SHOW_ALWAYS"), helpCode: "9627471" }
		);
		this._showAlwaysCheckBox.checked = isNew
			? BX.prop.getBoolean(this._settings, "showAlways", true)
			: this._field.checkOptionFlag(BX.UI.EntityEditorControlOptions.showAlways);
		//endregion

		return this._optionWrapper;
	};

	BX.UI.EntityEditorUserFieldConfigurator.prototype.prepareSaveParams = function(e)
	{
		var params = BX.UI.EntityEditorUserFieldConfigurator.superclass.prepareSaveParams.apply(this, arguments);
		if (
			(
				this._typeId === BX.UI.EntityUserFieldType.enumeration
				|| this._typeId === BX.UI.EntityUserFieldType.crmStatus
			)
			&& this._enumConfigurator
		)
		{
			params["innerConfig"] = (this._field) ? this._field.getInnerConfig() : {};
			params["enumeration"] = this._enumConfigurator.prepareSaveParams();
			params['display'] = this._enumConfigurator.getDisplaySelectValue();
		}

		if (this._field)
		{
			if (this._isMultipleCheckBox)
			{
				params["multiple"] = this._isMultipleCheckBox.checked;
			}
		}
		else
		{
			if(this._typeId === "boolean")
			{
				params["multiple"] = false;
			}
			else if(this._isMultipleCheckBox)
			{
				params["multiple"] = this._isMultipleCheckBox.checked;
			}

			if (this._typeId === "datetime")
			{
				params["enableTime"] = this._isTimeEnabledCheckBox.checked;
			}
		}

		return params;
	};

	BX.UI.EntityEditorUserFieldConfigurator.prototype.getIsRequiredCheckBox = function()
	{
		var checkBox = null;
		if(this._typeId !== "boolean")
		{
			if(this._enableMandatoryControl)
			{
				if(this._mandatoryConfigurator)
				{
					checkBox = this.createOption(
						{
							caption: this._mandatoryConfigurator.getTitle() + ":",
							//labelSettings: { props: { className: "ui-entity-new-field-addiction-label" } },
							containerSettings: { style: { alignItems: "center" } },
							elements: this._mandatoryConfigurator.getButton().prepareLayout()
						}
					);

					checkBox.checked = (this._field && this._field.isRequired())
						|| this._mandatoryConfigurator.isCustomized();

					this._mandatoryConfigurator.setSwitchCheckBox(checkBox);
					this._mandatoryConfigurator.setLabel(checkBox.nextSibling);

					this._mandatoryConfigurator.setEnabled(checkBox.checked);
					this._mandatoryConfigurator.adjust();
				}
				else
				{
					checkBox = this.createOption({ caption: BX.message("UI_ENTITY_EDITOR_UF_REQUIRED_FIELD") });
					checkBox.checked = this._field && this._field.isRequired();
				}
			}
		}

		return checkBox;
	};

	BX.UI.EntityEditorUserFieldConfigurator.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorUserFieldConfigurator();
		self.initialize(id, settings);
		return self;
	};

	BX.onCustomEvent(window, "BX.UI.EntityEditorUserFieldConfigurator:onDefine");
}

if (typeof BX.UI.EntityEditorUserFieldEnumConfigurator === "undefined")
{
	BX.UI.EntityEditorUserFieldEnumConfigurator = function()
	{
		BX.UI.EntityEditorUserFieldEnumConfigurator.superclass.constructor.apply(this);
	};
	BX.extend(BX.UI.EntityEditorUserFieldEnumConfigurator, BX.UI.EntityEditorEnumConfigurator);

	BX.UI.EntityEditorUserFieldEnumConfigurator.prototype.createEnumerationItem = function(data)
	{
		var item = BX.UI.EntityEditorUserFieldListItem.create(
			"",
			{
				configurator: this,
				container: this._enumItemContainer,
				data: data
			}
		);

		this._enumItems.push(item);
		item.layout();
		return item;
	};

	BX.UI.EntityEditorUserFieldEnumConfigurator.create = function(settings)
	{
		var self = new BX.UI.EntityEditorUserFieldEnumConfigurator();
		self.initialize(settings);
		return self;
	};
}
