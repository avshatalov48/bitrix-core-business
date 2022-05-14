BX.namespace("BX.UI");

if(typeof BX.UI.EntityScheme === "undefined")
{
	BX.UI.EntityScheme = function()
	{
		this._id = "";
		this._settings = {};
		this._elements = null;
		this._availableElements = null;
	};
	BX.UI.EntityScheme.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._elements = [];
			this._availableElements = [];

			var i, length;
			var currentData = BX.prop.getArray(this._settings, "current", []);
			for(i = 0, length = currentData.length; i < length; i++)
			{
				this._elements.push(BX.UI.EntitySchemeElement.create(currentData[i]));
			}

			var availableData = BX.prop.getArray(this._settings, "available", []);
			for(i = 0, length = availableData.length; i < length; i++)
			{
				this._availableElements.push(BX.UI.EntitySchemeElement.create(availableData[i]));
			}
		},
		getId: function()
		{
			return this._id;
		},
		getElements: function()
		{
			return ([].concat(this._elements));
		},
		findElementByName: function(name, options)
		{
			var isRecursive = BX.prop.getBoolean(options, "isRecursive", false);

			if (isRecursive)
			{
				return this.findElementByNameRecursive(this._elements, name);
			}

			for(var i = 0, length = this._elements.length; i < length; i++)
			{
				var element = this._elements[i];
				if(element.getName() === name)
				{
					return element;
				}
			}

			return null;
		},
		findElementByNameRecursive: function(elements, name)
		{
			if (Array.isArray(elements))
			{
				for(var i = 0, length = elements.length; i < length; i++)
				{
					var element = elements[i];
					if(element.getName() === name)
					{
						return element;
					}

					var result = this.findElementByNameRecursive(element._elements, name);
					if (result)
					{
						return result;
					}
				}
			}
			return null;
		},
		getAvailableElements: function()
		{
			return([].concat(this._availableElements));
		},
		setAvailableElements: function(elements)
		{
			this._availableElements = BX.type.isArray(elements) ? elements : [];
		}
	};
	BX.UI.EntityScheme.create = function(id, settings)
	{
		var self = new BX.UI.EntityScheme();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntitySchemeElement === "undefined")
{
	BX.UI.EntitySchemeElement = function()
	{
		this._settings = {};
		this._name = "";
		this._type = "";
		this._title = "";
		this._hint = "";
		this._originalTitle = "";
		this._optionFlags = 0;
		this._options = {};

		this._isEditable = true;
		this._isShownAlways = false;
		this._isTransferable = true;
		this._isContextMenuEnabled = true;
		this._isRequired = false;
		this._isRequiredConditionally = false;
		this._isRequiredByAttribute = false;
		this._isPhaseDependent = true;
		this._isHeading = false;
		this._isMergeable = true;

		this._visibilityPolicy = BX.UI.EntityEditorVisibilityPolicy.always;
		this._data = null;
		this._elements = null;
		this._parent = null;
	};
	BX.UI.EntitySchemeElement.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};

			this._name = BX.prop.getString(this._settings, "name", "");
			this._type = BX.prop.getString(this._settings, "type", "");

			this._data = BX.prop.getObject(this._settings, "data", {});

			this.prepareAdditionalParameters();

			this._isEditable = BX.prop.getBoolean(this._settings, "editable", true);
			this._isMergeable = BX.prop.getBoolean(this._settings, "mergeable", true);
			this._isShownAlways = BX.prop.getBoolean(this._settings, "showAlways", false);
			this._isTransferable = BX.prop.getBoolean(this._settings, "transferable", true);
			this._isContextMenuEnabled = BX.prop.getBoolean(this._settings, "enabledMenu", true);
			this._isTitleEnabled = BX.prop.getBoolean(this._settings, "enableTitle", true)
				&& this.getDataBooleanParam("enableTitle", true);
			this._isDragEnabled = BX.prop.getBoolean(this._settings, "isDragEnabled", true);
			this._isPhaseDependent = this.getDataBooleanParam("isPhaseDependent", true);
			this._isRequired = BX.prop.getBoolean(this._settings, "required", false);
			this._isRequiredConditionally = BX.prop.getBoolean(this._settings, "requiredConditionally", false);
			this._isRequiredByAttribute = this.getRequiredByAttributeConfiguration();
			this._isHeading = BX.prop.getBoolean(this._settings, "isHeading", false);

			this._visibilityPolicy = BX.UI.EntityEditorVisibilityPolicy.parse(
				BX.prop.getString(
					this._settings,
					"visibilityPolicy",
					""
				)
			);

			//region Titles
			var hint = BX.prop.getString(this._settings, "hint", "");
			var title = BX.prop.getString(this._settings, "title", "");
			var originalTitle = BX.prop.getString(this._settings, "originalTitle", "");

			if(title !== "" && originalTitle === "")
			{
				originalTitle = title;
			}
			else if(originalTitle !== "" && title === "")
			{
				title = originalTitle;
			}

			this._hint = hint;
			this._title = title;
			this._originalTitle = originalTitle;
			//endregion

			this._optionFlags = BX.prop.getInteger(this._settings, "optionFlags", 0);
			this._options = BX.prop.getObject(this._settings, "options", {});

			this._elements = [];
			var elementData = BX.prop.getArray(this._settings, "elements", []);
			for(var i = 0, l = elementData.length; i < l; i++)
			{
				this._elements.push(BX.UI.EntitySchemeElement.create(elementData[i]));
			}
		},
		prepareAdditionalParameters: function()
		{
			var fieldInfo = this._data.fieldInfo || null;
			if (
				fieldInfo
				&& fieldInfo.ADDITIONAL === undefined
				&& fieldInfo.USER_TYPE_ID === BX.UI.EntityUserFieldType.file
				&& BX.UI.EntitySchemeElement.userFieldFileUrlTemplate !== undefined
			)
			{
				var template = BX.UI.EntitySchemeElement.userFieldFileUrlTemplate;
				template = template.replace('#owner_id#', fieldInfo.ENTITY_VALUE_ID)
					.replace('#field_name#', fieldInfo.FIELD);
				fieldInfo.ADDITIONAL = {};
				fieldInfo.ADDITIONAL.URL_TEMPLATE = template;
			}
		},
		mergeSettings: function(settings)
		{
			this.initialize(BX.mergeEx(this._settings, settings));
		},
		getName: function()
		{
			return this._name;
		},
		getType: function()
		{
			return this._type;
		},
		getHint: function()
		{
			return this._hint;
		},
		getTitle: function()
		{
			return this._title;
		},
		setTitle: function(title)
		{
			this._title = this._settings["title"] = title;
		},
		getOriginalTitle: function()
		{
			return this._originalTitle;
		},
		hasCustomizedTitle: function()
		{
			return this._title !== "" && this._title !== this._originalTitle;
		},
		resetOriginalTitle: function()
		{
			this._originalTitle = this._title;
		},
		getOptionFlags: function()
		{
			return this._optionFlags;
		},
		setOptionFlags: function(flags)
		{
			this._optionFlags = this._settings["optionFlags"] = flags;
		},
		areAttributesEnabled: function()
		{
			return BX.prop.getBoolean(this._settings, "enableAttributes", true);
		},
		isEditable: function()
		{
			return this._isEditable;
		},
		isShownAlways: function()
		{
			return this._isShownAlways;
		},
		isTransferable: function()
		{
			return this._isTransferable;
		},
		isRequired: function()
		{
			return this._isRequired;
		},
		isRequiredConditionally: function()
		{
			return this._isRequiredConditionally;
		},
		isRequiredByAttribute: function()
		{
			return this._isRequiredByAttribute;
		},
		isContextMenuEnabled: function()
		{
			return this._isContextMenuEnabled;
		},
		isTitleEnabled: function()
		{
			return this._isTitleEnabled;
		},
		isDragEnabled: function()
		{
			return this._isDragEnabled;
		},
		isHeading: function()
		{
			return this._isHeading;
		},
		isMergeable: function()
		{
			return this._isMergeable;
		},
		needShowTitle: function()
		{
			return BX.prop.getBoolean(this._settings, "showTitle", true);
		},
		isVirtual: function()
		{
			return BX.prop.getBoolean(this._settings, "virtual", false);
		},
		getCreationPlaceholder: function()
		{
			return BX.prop.getString(
				BX.prop.getObject(this._settings, "placeholders", null),
				"creation",
				""
			);
		},
		getChangePlaceholder: function()
		{
			return BX.prop.getString(
				BX.prop.getObject(this._settings, "placeholders", null),
				"change",
				""
			);
		},
		getVisibilityPolicy: function()
		{
			return this._visibilityPolicy;
		},
		getData: function()
		{
			return this._data;
		},
		setData: function(data)
		{
			this._data = data;
		},
		getDataParam: function(name, defaultval)
		{
			return BX.prop.get(this._data, name, defaultval);
		},
		setDataParam: function(name, val)
		{
			this._data[name] = val;
		},
		getDataStringParam: function(name, defaultval)
		{
			return BX.prop.getString(this._data, name, defaultval);
		},
		getDataIntegerParam: function(name, defaultval)
		{
			return BX.prop.getInteger(this._data, name, defaultval);
		},
		getDataBooleanParam: function(name, defaultval)
		{
			return BX.prop.getBoolean(this._data, name, defaultval);
		},
		getDataObjectParam: function(name, defaultval)
		{
			return BX.prop.getObject(this._data, name, defaultval);
		},
		getDataArrayParam: function(name, defaultval)
		{
			return BX.prop.getArray(this._data, name, defaultval);
		},
		getInnerConfig: function()
		{
			var innerConfig = this.getDataObjectParam("innerConfig", {});

			var isInnerConfigValid = (
				BX.Type.isPlainObject(innerConfig)
				&& innerConfig.hasOwnProperty("type")
				&& BX.Type.isStringFilled(innerConfig["type"])
				&& innerConfig.hasOwnProperty("controller")
				&& BX.Type.isStringFilled(innerConfig["controller"])
				&& innerConfig.hasOwnProperty("statusType")
				&& BX.Type.isStringFilled(innerConfig["statusType"])
				&& innerConfig.hasOwnProperty("itemsConfig")
				&& BX.Type.isPlainObject(innerConfig["itemsConfig"])
			);

			return (isInnerConfigValid) ? innerConfig : null;
		},
		getElements: function()
		{
			return this._elements;
		},
		setElements: function(elements)
		{
			this._elements = elements;
		},
		findElementByName: function(name)
		{
			for(var i = 0, length = this._elements.length; i < length; i++)
			{
				var element = this._elements[i];
				if(element.getName() === name)
				{
					return element;
				}
			}
			return null;
		},
		getAffectedFields: function()
		{
			var results = this.getDataArrayParam("affectedFields", []);
			if(results.length === 0)
			{
				results.push(this._name);
			}
			return results;
		},
		getParent: function()
		{
			return this._parent;
		},
		setParent: function(parent)
		{
			this._parent = parent instanceof BX.UI.EntitySchemeElement ? parent : null;
		},
		hasAttributeConfiguration: function(attributeTypeId)
		{
			return !!this.getAttributeConfiguration(attributeTypeId);
		},
		getAttributeConfiguration: function(attributeTypeId)
		{
			var data = this.getData();
			var configs = BX.prop.getArray(data, "attrConfigs", null);
			if(!configs)
			{
				return null;
			}

			for(var i = 0, length = configs.length; i < length; i++)
			{
				var config = configs[i];
				if(BX.prop.getInteger(config, "typeId", BX.UI.EntityFieldAttributeType.undefined) === attributeTypeId)
				{
					return BX.clone(config);
				}
			}
			return null;
		},
		setAttributeConfiguration: function(config)
		{
			var typeId = BX.prop.getInteger(config, "typeId", BX.UI.EntityFieldAttributeType.undefined);
			if(typeof(this._data["attrConfigs"]) === "undefined")
			{
				this._data["attrConfigs"] = [];
			}

			var index = -1;
			for(var i = 0, length = this._data["attrConfigs"].length; i < length; i++)
			{
				if(BX.prop.getInteger(
					this._data["attrConfigs"][i],
					"typeId",
					BX.UI.EntityFieldAttributeType.undefined) === typeId)
				{
					index = i;
					break;
				}
			}

			if(index >= 0)
			{
				this._data["attrConfigs"].splice(index, 1, config);
			}
			else
			{
				this._data["attrConfigs"].push(config);
			}

			this._isRequiredByAttribute = this.getRequiredByAttributeConfiguration();
		},
		removeAttributeConfiguration: function(attributeTypeId)
		{
			if(typeof(this._data["attrConfigs"]) !== "undefined")
			{
				for(var i = 0, length = this._data["attrConfigs"].length; i < length; i++)
				{
					if(BX.prop.getInteger(
						this._data["attrConfigs"][i],
						"typeId",
						BX.UI.EntityFieldAttributeType.undefined) === attributeTypeId)
					{
						this._data["attrConfigs"].splice(i, 1);
						break;
					}
				}
			}

			this._isRequiredByAttribute = this.getRequiredByAttributeConfiguration();
		},
		getRequiredByAttributeConfiguration: function()
		{
			var result = false;
			var resultReady = false;

			if(typeof(this._data["attrConfigs"]) === "undefined")
			{
				resultReady = true;
			}

			if (!resultReady)
			{
				for(var i = 0, length = this._data["attrConfigs"].length; i < length; i++)
				{
					if (BX.prop.getInteger(
						this._data["attrConfigs"][i],
						"typeId",
						BX.UI.EntityFieldAttributeType.undefined
					) === BX.UI.EntityFieldAttributeType.required)
					{
						if (this._isPhaseDependent)
						{
							var config = this._data["attrConfigs"][i];
							if (config.hasOwnProperty("groups") && BX.Type.isArray(config["groups"]))
							{
								for (var j = 0; j <= config["groups"].length; j++)
								{
									if (BX.Type.isPlainObject(config["groups"][j]))
									{
										var group = config["groups"][j];
										if (group.hasOwnProperty("phaseGroupTypeId"))
										{
											if (parseInt(group["phaseGroupTypeId"]) ===
												BX.UI.EntityFieldAttributePhaseGroupType.general)
											{
												result = true;
												resultReady = true;
												break;
											}
										}
									}
								}
							}
						}
						else
						{
							result = true;
							resultReady = true;
						}
						if (resultReady)
						{
							break;
						}
					}
				}
			}

			return result;
		},
		setVisibilityConfiguration: function(config)
		{
			this._data["visibilityConfigs"] = config;
		},
		removeVisibilityConfiguration: function(attributeTypeId)
		{
			this._data["visibilityConfigs"] = {};
		},
		createConfigItem: function()
		{
			var result = { name: this._name };

			if(this._type === "column")
			{
				result["type"] = "column";
				result["data"] = this._data;

				result["elements"] = [];
				for(var i = 0, length = this._elements.length; i < length; i++)
				{
					result["elements"].push(this._elements[i].createConfigItem());
				}
			}
			else if(this._type === "section")
			{
				result["type"] = "section";
				result["data"] = this._data;

				if(this._title !== "")
				{
					result["title"] = this._title;
				}

				result["elements"] = [];
				for(var i = 0, length = this._elements.length; i < length; i++)
				{
					//result["elements"].push({ name: this._elements[i].getName() });
					result["elements"].push(this._elements[i].createConfigItem());
				}
			}
			else if(this._type === "included_area")
			{
				result["type"] = "included_area";
				result["data"] = this._data;

				if(this._title !== "")
				{
					result["title"] = this._title;
				}
			}
			else
			{
				if(this._title !== "" && this._title !== this._originalTitle)
				{
					result["title"] = this._title;
				}

				if(this._optionFlags > 0)
				{
					result["optionFlags"] = this._optionFlags;
				}
				result["options"] = this._options;
			}

			return result;
		},
		clone: function()
		{
			return BX.UI.EntitySchemeElement.create(BX.clone(this._settings));
		}
	};
	BX.UI.EntitySchemeElement.create = function(settings)
	{
		var self = new BX.UI.EntitySchemeElement();
		self.initialize(settings);
		return self;
	}
}