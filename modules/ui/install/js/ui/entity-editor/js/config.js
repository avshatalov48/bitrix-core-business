BX.namespace("BX.UI");

if(typeof BX.UI.EntityConfigType === "undefined")
{
	BX.UI.EntityConfigType = {
		COLUMN: "column",
		SECTION: "section",
		INCLUDED_AREA: "included_area",
		FIELD: "field"
	}
}

if(typeof BX.UI.EntityConfigFactory === "undefined")
{
	BX.UI.EntityConfigFactory =
	{
		createByType: function(type, settings)
		{
			var config;

			if (type === BX.UI.EntityConfigType.COLUMN)
			{
				config = BX.UI.EntityConfigColumn.create(settings);
			}
			else if (type === BX.UI.EntityConfigType.SECTION)
			{
				config = BX.UI.EntityConfigSection.create(settings);
			}
			else if (type === BX.UI.EntityConfigType.INCLUDED_AREA)
			{
				config = BX.UI.EntityConfigIncludedArea.create(settings);
			}
			else
			{
				config = BX.UI.EntityConfigField.create(settings);
			}

			return config;
		}
	};
}

if(typeof BX.UI.EntityConfig === "undefined")
{
	BX.UI.EntityConfig = function()
	{
		this._id = "";
		this._settings = {};
		this._scope = BX.UI.EntityConfigScope.undefined;
		this._userScopes = null;
		this._userScopeId = null;
		this._enableScopeToggle = true;

		this._canUpdatePersonalConfiguration = true;
		this._canUpdateCommonConfiguration = false;

		this._data = {};
		this._items = [];
		this._options = {};
		this._signedParams = null;

		this._isChanged = false;

		this.categoryName = '';
		this.moduleId = '';
	};
	BX.UI.EntityConfig.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};
			this._scope = BX.prop.getString(this._settings, "scope", BX.UI.EntityConfigScope.personal);
			this._userScopes = BX.prop.getObject(this._settings, "userScopes", null);
			this._userScopeId = BX.prop.getString(this._settings, "userScopeId", null);
			this.moduleId = BX.prop.getString(this._settings, "moduleId", null);
			this._enableScopeToggle = BX.prop.getBoolean(this._settings, "enableScopeToggle", true);

			this._canUpdatePersonalConfiguration = BX.prop.getBoolean(this._settings, "canUpdatePersonalConfiguration", true);
			this._canUpdateCommonConfiguration = BX.prop.getBoolean(this._settings, "canUpdateCommonConfiguration", false);
			this._signedParams = BX.prop.getString(this._settings, "signedParams", '');

			this._data = BX.prop.getArray(this._settings, "data", []);

			this._items = [];
			for(var i = 0, length = this._data.length; i < length; i++)
			{
				var item = this._data[i];
				var type = BX.prop.getString(item, "type", "");
				var config = BX.UI.EntityConfigFactory.createByType(type, {data: item});
				this._items.push(config);
			}

			this._options = BX.prop.getObject(this._settings, "options", {});

			this.categoryName = BX.prop.getString(this._settings, 'categoryName', 'ui.form.editor')
		},
		findItemByName: function(name)
		{
			for(var i = 0, length = this._items.length; i < length; i++)
			{
				var item = this._items[i];
				if(item.getName() === name)
				{
					return item;
				}
			}
			return null;
		},
		findItemIndexByName: function(name)
		{
			for(var i = 0, length = this._items.length; i < length; i++)
			{
				var item = this._items[i];
				if(item.getName() === name)
				{
					return i;
				}
			}
			return -1;
		},
		toJSON: function()
		{
			var result = [];
			for(var i = 0, length = this._items.length; i < length; i++)
			{
				result.push(this._items[i].toJSON());
			}
			return result;
		},
		addSchemeElementAt: function(schemeElement, index)
		{
			var item = BX.UI.EntityConfigFactory.createByType(schemeElement.getType(), {
				data: schemeElement.createConfigItem()
			});

			if(index >= 0 && index < this._items.length)
			{
				this._items.splice(index, 0, item);
			}
			else
			{
				this._items.push(item);
			}

			this._isChanged = true;
		},
		moveSchemeElement: function(schemeElement, index)
		{
			var qty = this._items.length;
			var lastIndex = qty - 1;
			if(index < 0  || index > qty)
			{
				index = lastIndex;
			}

			var currentIndex = this.findItemIndexByName(schemeElement.getName());
			if(currentIndex < 0 || currentIndex === index)
			{
				return;
			}

			var item = this._items[currentIndex];
			this._items.splice(currentIndex, 1);

			qty--;

			if(index < qty)
			{
				this._items.splice(index, 0, item);
			}
			else
			{
				this._items.push(item);
			}

			this._isChanged = true;
		},
		updateSchemeElement: function(schemeElement)
		{
			var index;
			var parentElement = schemeElement.getParent();
			if(parentElement && parentElement.getType() === 'section')
			{
				var parentItem = this.findItemByName(parentElement.getName());
				if(parentItem)
				{
					index = parentItem.findFieldIndexByName(schemeElement.getName());
					if(index >= 0)
					{
						var config = BX.UI.EntityConfigFactory.createByType(BX.UI.EntityConfigType.FIELD, {
							data: schemeElement.createConfigItem()
						});
						parentItem.setField(config, index);
						this._isChanged = true;
					}
				}
			}
			else
			{
				index = this.findItemIndexByName(schemeElement.getName());
				if(index >= 0)
				{
					this._items[index] = BX.UI.EntityConfigFactory.createByType(schemeElement.getType(), {
						data: schemeElement.createConfigItem()
					});
					this._isChanged = true;
				}
			}

		},
		removeSchemeElement: function(schemeElement)
		{
			var index = this.findItemIndexByName(schemeElement.getName());
			if(index < 0)
			{
				return;
			}

			this._items.splice(index, 1);
			this._isChanged = true;
		},
		isChangeable: function()
		{
			if(
				this._scope === BX.UI.EntityConfigScope.common
				|| this._scope === BX.UI.EntityConfigScope.custom
			)
			{
				return this._canUpdateCommonConfiguration;
			}
			else if(this._scope === BX.UI.EntityConfigScope.personal)
			{
				return this._canUpdatePersonalConfiguration;
			}

			return false;
		},
		isCanChangeCommonConfiguration: function()
		{
			return this._canUpdateCommonConfiguration;
		},
		isChanged: function()
		{
			return this._isChanged;
		},
		isScopeToggleEnabled: function()
		{
			return this._enableScopeToggle;
		},
		getScope: function()
		{
			return this._scope;
		},
		setScope: function(scope, userScopeId, moduleId)
		{
			var promise = new BX.Promise();
			if(
				!this._enableScopeToggle
				||
				(this._scope === scope && scope !== BX.UI.EntityConfigScope.custom)
				||
				(this._scope === scope && this._userScopeId === userScopeId)
			)
			{
				window.setTimeout(
					function(){ promise.fulfill(); },
					0
				);
				return promise;
			}

			this._scope = scope;
			this._userScopeId = userScopeId;
			this.moduleId = moduleId;

			//Scope is changed - data collections are invalid.
			this._data = [];
			this._items = [];

			BX.ajax.runComponentAction('bitrix:ui.form.config', 'setScope', {
				'data': {
					categoryName: this.categoryName,
					moduleId: this.moduleId,
					guid: this._id,
					scope: this._scope,
					userScopeId: (this._userScopeId || 0)
				}
			}).then(function (response) {
				promise.fulfill();
			});

			return promise;
		},
		registerField: function(scheme)
		{
			var parentScheme = scheme.getParent();
			if(!parentScheme)
			{
				return;
			}

			var section = this.findItemByName(parentScheme.getName());
			if(!section)
			{
				return;
			}

			section.addField(
				BX.UI.EntityConfigField.create({ data: scheme.createConfigItem() })
			);
			this.save();
		},
		unregisterField: function(scheme)
		{
			var parentScheme = scheme.getParent();
			if(!parentScheme)
			{
				return;
			}

			var section = this.findItemByName(parentScheme.getName());
			if(!section)
			{
				return;
			}

			var field = section.findFieldByName(scheme.getName());
			if(!field)
			{
				return;
			}

			section.removeFieldByIndex(field.getIndex());
			this.save();
		},
		save: function(forAllUsers, enableOptions)
		{
			forAllUsers = !!forAllUsers;
			enableOptions = !!enableOptions;

			var promise = new BX.Promise();
			if(!this._isChanged && !forAllUsers)
			{
				window.setTimeout(
					function(){ promise.fulfill(); },
					0
				);

				return promise;
			}

			var data = { guid: this._id, config: this.toJSON(), params: { scope: this._scope }, categoryName: this.categoryName };
			if(enableOptions)
			{
				data["params"]["options"] = this._options;
			}

			if(forAllUsers)
			{
				data["params"]["forAllUsers"] = "Y";
				data["params"]["delete"] = "Y";
			}

			if (this._scope === BX.UI.EntityConfigScope.custom)
			{
				data['params']['userScopeId'] = this._userScopeId;
			}
			data['signedConfigParams'] = this._signedParams;

			BX.ajax.runComponentAction(
				"bitrix:ui.form",
				"saveConfiguration",
				{ mode: "ajax", data: data }
			).then(function(){ promise.fulfill(); });

			this._isChanged = false;

			return promise;
		},
		reset: function(forAllUsers)
		{
			var data = { guid: this._id, params: { scope: this._scope }, categoryName: this.categoryName };
			if(forAllUsers)
			{
				data["params"]["forAllUsers"] = "Y";
			}
			data['signedConfigParams'] = this._signedParams;

			var promise = new BX.Promise();

			BX.ajax.runComponentAction(
				"bitrix:ui.form",
				"resetConfiguration",
				{ mode: "ajax", data: data }
			).then(function(){ promise.fulfill(); });

			return promise;
		},
		forceCommonScopeForAll: function()
		{
			var promise = new BX.Promise();

			BX.ajax.runComponentAction(
				"bitrix:ui.form",
				"forceCommonScopeForAll",
				{ mode: "ajax", data: { guid: this._id, categoryName: this.categoryName, signedConfigParams: this._signedParams } }
			).then(function(){ promise.fulfill(); });

			return promise;
		},
		getOption: function(name, defaultValue)
		{
			return BX.prop.getString(this._options, name, defaultValue);
		},
		setOption: function(name, value)
		{
			if(typeof(value) === "undefined" || value === null)
			{
				return;
			}

			if(BX.prop.getString(this._options, name, null) === value)
			{
				return;
			}

			this._options[name] = value;

			if(this._scope === BX.UI.EntityConfigScope.common)
			{
				BX.userOptions.save(
					this.categoryName,
					this._id.toLowerCase() + "_common_opts",
					name,
					value,
					true
				);
			}
			else if(this._scope === BX.UI.EntityConfigScope.custom)
			{
				BX.userOptions.save(
					this.categoryName,
					this._id.toLowerCase() + "_custom_opts_" + this._userScopeId,
					name,
					value,
					true
				);
			}
			else
			{
				BX.userOptions.save(
					"crm.entity.editor",
					this._id + "_opts",
					name,
					value,
					false
				);
			}
		}
	};
	BX.UI.EntityConfig.create = function(id, settings)
	{
		var self = new BX.UI.EntityConfig();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EntityConfigItem === "undefined")
{
	BX.UI.EntityConfigItem = function()
	{
		this._settings = {};
		this._data = {};
		this._name = "";
		this._title = "";
	};

	BX.UI.EntityConfigItem.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};
			this._data = BX.prop.getObject(this._settings, "data", []);
			this._name = BX.prop.getString(this._data, "name", "");
			this._title = BX.prop.getString(this._data, "title", "");

			this.doInitialize();
		},
		doInitialize: function()
		{
		},
		getType: function()
		{
			return "";
		},
		getName: function()
		{
			return this._name;
		},
		getTitle: function()
		{
			return this._title;
		},
		toJSON: function()
		{
			return {};
		}
	};
}

if(typeof BX.UI.EntityConfigColumn === "undefined")
{
	BX.UI.EntityConfigColumn = function()
	{
		BX.UI.EntityConfigColumn.superclass.constructor.apply(this);
		this._sections = [];
	};
	BX.extend(BX.UI.EntityConfigColumn, BX.UI.EntityConfigItem);

	BX.UI.EntityConfigColumn.prototype.doInitialize = function()
	{
		var elements = BX.prop.getArray(this._data, "elements", []);

		for (var i = 0, length = elements.length; i < length; i++)
		{
			if (elements[i].type === "section" || elements[i].type === "included_area")
			{
				var config = BX.UI.EntityConfigFactory.createByType(elements[i].type, {data: elements[i]});
				this.addSection(config);
			}
		}
	};
	BX.UI.EntityConfigColumn.prototype.getType = function()
	{
		return BX.UI.EntityConfigType.COLUMN;
	};
	BX.UI.EntityConfigColumn.prototype.getSections = function()
	{
		return this._sections;
	};
	BX.UI.EntityConfigColumn.prototype.findSectionByName = function(name)
	{
		var index = this.findSectionIndexByName(name);

		return index >= 0 ? this._sections[index] : null;
	};
	BX.UI.EntityConfigColumn.prototype.findSectionIndexByName = function(name)
	{
		for(var i = 0, length = this._sections.length; i < length; i++)
		{
			var section = this._sections[i];
			if(section.getName() === name)
			{
				return i;
			}
		}

		return -1;
	};
	BX.UI.EntityConfigColumn.prototype.findFieldByName = function(name)
	{
		var index = this.findFieldIndexByName(name);

		return index >= 0 ? this._sections[index] : null;
	};
	BX.UI.EntityConfigColumn.prototype.findFieldIndexByName = function(name)
	{
		for (var i = 0, length = this._sections.length; i < length; i++)
		{
			var field = this._sections[i];

			if (field.getName() === name)
			{
				return i;
			}
		}

		return -1;
	};
	BX.UI.EntityConfigColumn.prototype.addSection = function(section)
	{
		this._sections.push(section);
	};
	BX.UI.EntityConfigColumn.prototype.setSection = function(section, index)
	{
		this._sections[index] = section;
	};
	BX.UI.EntityConfigColumn.prototype.removeSectionByIndex = function(index)
	{
		if (index < 0 || index >= this._sections.length)
		{
			return false;
		}

		this._sections.splice(index, 1);

		return true;
	};
	BX.UI.EntityConfigColumn.prototype.toJSON = function()
	{
		var result = {
			name: this._name,
			type: this.getType(),
			data: BX.prop.getObject(this._data, "data", {}),
			elements: []
		};

		for (var i = 0, length = this._sections.length; i < length; i++)
		{
			result.elements.push(this._sections[i].toJSON());
		}

		return result;
	};
	BX.UI.EntityConfigColumn.create = function(settings)
	{
		var self = new BX.UI.EntityConfigColumn();
		self.initialize(settings);
		return self;
	};
}

if(typeof BX.UI.EntityConfigSection === "undefined")
{
	BX.UI.EntityConfigSection = function()
	{
		BX.UI.EntityConfigSection.superclass.constructor.apply(this);
		this._fields = [];
	};
	BX.extend(BX.UI.EntityConfigSection, BX.UI.EntityConfigItem);

	BX.UI.EntityConfigSection.prototype.doInitialize = function()
	{
		this._fields = [];
		var elements = BX.prop.getArray(this._data, "elements", []);
		for(var i = 0, length = elements.length; i < length; i++)
		{
			var field = BX.UI.EntityConfigField.create({ data: elements[i] });
			field.setIndex(i);
			this._fields.push(field);
		}
	};
	BX.UI.EntityConfigSection.prototype.getType = function()
	{
		return BX.UI.EntityConfigType.SECTION;
	};
	BX.UI.EntityConfigSection.prototype.getFields = function()
	{
		return this._fields;
	};
	BX.UI.EntityConfigSection.prototype.findFieldByName = function(name)
	{
		var index = this.findFieldIndexByName(name);
		return index >= 0 ? this._fields[index] : null;
	};
	BX.UI.EntityConfigSection.prototype.findFieldIndexByName = function(name)
	{
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			var field = this._fields[i];
			if(field.getName() === name)
			{
				return i;
			}
		}
		return -1;
	};
	BX.UI.EntityConfigSection.prototype.addField = function(field)
	{
		this._fields.push(field);
	};
	BX.UI.EntityConfigSection.prototype.setField = function(field, index)
	{
		this._fields[index] = field;
	};
	BX.UI.EntityConfigSection.prototype.removeFieldByIndex = function(index)
	{
		var length = this._fields.length;
		if(index < 0 || index >= length)
		{
			return false;
		}

		this._fields.splice(index, 1);
		return true;
	};
	BX.UI.EntityConfigSection.prototype.toJSON = function()
	{
		var result = {
			name: this._name,
			title: this._title,
			type: this.getType(),
			data: BX.prop.getObject(this._data, "data", {}),
			elements: []
		};
		for(var i = 0, length = this._fields.length; i < length; i++)
		{
			result.elements.push(this._fields[i].toJSON());
		}
		return result;
	};
	BX.UI.EntityConfigSection.create = function(settings)
	{
		var self = new BX.UI.EntityConfigSection();
		self.initialize(settings);
		return self;
	};
}

if(typeof BX.UI.EntityConfigIncludedArea === "undefined")
{
	BX.UI.EntityConfigIncludedArea = function()
	{
		BX.UI.EntityConfigIncludedArea.superclass.constructor.apply(this);
		this._params = {};
	};
	BX.extend(BX.UI.EntityConfigIncludedArea, BX.UI.EntityConfigItem);

	BX.UI.EntityConfigIncludedArea.prototype.doInitialize = function()
	{
		this._params = BX.prop.getObject(this._data, "data", {});
	};
	BX.UI.EntityConfigIncludedArea.prototype.getType = function()
	{
		return BX.UI.EntityConfigType.INCLUDED_AREA;
	};
	BX.UI.EntityConfigIncludedArea.prototype.toJSON = function()
	{
		return {
			name: this._name,
			title: this._title,
			data: this._params,
			type: this.getType()
		};
	};
	BX.UI.EntityConfigIncludedArea.create = function(settings)
	{
		var self = new BX.UI.EntityConfigIncludedArea();
		self.initialize(settings);
		return self;
	};
}

if(typeof BX.UI.EntityConfigField === "undefined")
{
	BX.UI.EntityConfigField = function()
	{
		BX.UI.EntityConfigField.superclass.constructor.apply(this);
		this._index = -1;
		this._optionFlags = 0;
		this._options = {};

	};
	BX.extend(BX.UI.EntityConfigField, BX.UI.EntityConfigItem);
	BX.UI.EntityConfigField.prototype.doInitialize = function()
	{
		this._optionFlags = BX.prop.getInteger(this._data, "optionFlags", 0);
		this._options = BX.prop.getObject(this._data, "options", {});
	};
	BX.UI.EntityConfigField.prototype.toJSON = function()
	{
		var result = { name: this._name };
		if(this._title !== "")
		{
			result["title"] = this._title;
		}

		result["optionFlags"] = this._optionFlags;
		result["options"] = this._options;
		return result;
	};
	BX.UI.EntityConfigField.prototype.getIndex = function()
	{
		return this._index;
	};
	BX.UI.EntityConfigField.prototype.setIndex = function(index)
	{
		this._index = index;
	};
	BX.UI.EntityConfigField.create = function(settings)
	{
		var self = new BX.UI.EntityConfigField();
		self.initialize(settings);
		return self;
	};
}