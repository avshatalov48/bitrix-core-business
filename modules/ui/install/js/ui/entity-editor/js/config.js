BX.namespace("BX.UI");

if(typeof BX.UI.EntityConfig === "undefined")
{
	BX.UI.EntityConfig = function()
	{
		this._id = "";
		this._settings = {};
		this._scope = BX.UI.EntityConfigScope.undefined;
		this._enableScopeToggle = true;

		this._canUpdatePersonalConfiguration = true;
		this._canUpdateCommonConfiguration = false;

		this._data = {};
		this._items = [];
		this._options = {};

		this._isChanged = false;
	};
	BX.UI.EntityConfig.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};
			this._scope = BX.prop.getString(this._settings, "scope", BX.UI.EntityConfigScope.personal);
			this._enableScopeToggle = BX.prop.getBoolean(this._settings, "enableScopeToggle", true);

			this._canUpdatePersonalConfiguration = BX.prop.getBoolean(this._settings, "canUpdatePersonalConfiguration", true);
			this._canUpdateCommonConfiguration = BX.prop.getBoolean(this._settings, "canUpdateCommonConfiguration", false);

			this._data = BX.prop.getArray(this._settings, "data", []);

			this._items = [];
			for(var i = 0, length = this._data.length; i < length; i++)
			{
				var item = this._data[i];
				var type = BX.prop.getString(item, "type", "");
				if(type === "section")
				{
					this._items.push(BX.UI.EntityConfigSection.create({ data: item }));
				}
				else
				{
					this._items.push(BX.UI.EntityConfigField.create({ data: item }));
				}
			}

			this._options = BX.prop.getObject(this._settings, "options", {});
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
			var data = schemeElement.createConfigItem();
			var item = schemeElement.getType() === "section"
				? BX.UI.EntityConfigSection.create({ data: data })
				: BX.UI.EntityConfigField.create({ data: data });

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
			if(parentElement)
			{
				var parentItem = this.findItemByName(parentElement.getName());
				if(parentItem)
				{
					index = parentItem.findFieldIndexByName(schemeElement.getName());
					if(index >= 0)
					{
						parentItem.setField(
							BX.UI.EntityConfigField.create({ data: schemeElement.createConfigItem() }),
							index
						);
						this._isChanged = true;
					}
				}
			}
			else
			{
				index = this.findItemIndexByName(schemeElement.getName());
				if(index >= 0)
				{
					if(schemeElement.getType() === "section")
					{
						this._items[index] = BX.UI.EntityConfigSection.create({ data: schemeElement.createConfigItem() });
					}
					else
					{
						this._items[index] = BX.UI.EntityConfigField.create({ data: schemeElement.createConfigItem() });
					}
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
			if(this._scope === BX.UI.EntityConfigScope.common)
			{
				return this._canUpdateCommonConfiguration;
			}
			else if(this._scope === BX.UI.EntityConfigScope.personal)
			{
				return this._canUpdatePersonalConfiguration;
			}

			return false;
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
		setScope: function(scope)
		{
			var promise = new BX.Promise();
			if(!this._enableScopeToggle || this._scope === scope)
			{
				window.setTimeout(
					function(){ promise.fulfill(); },
					0
				);
				return promise;
			}

			this._scope = scope;

			//Scope is changed - data collections are invalid.
			this._data = [];
			this._items = [];

			BX.ajax.runComponentAction(
				"bitrix:ui.form",
				"setScope",
				{ mode: "ajax", data: { guid: this._id, scope: this._scope } }
			).then(function(){ promise.fulfill(); });

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

			var data = { guid: this._id, config: this.toJSON(), params: { scope: this._scope } };
			if(enableOptions)
			{
				data["params"]["options"] = this._options;
			}

			if(forAllUsers)
			{
				data["params"]["forAllUsers"] = "Y";
				data["params"]["delete"] = "Y";
			}

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
			var data = { guid: this._id, params: { scope: this._scope } };
			if(forAllUsers)
			{
				data["params"]["forAllUsers"] = "Y";
			}

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
				{ mode: "ajax", data: { guid: this._id } }
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

			BX.userOptions.save(
				"ui.entity.editor",
				this._id + "_opts",
				name,
				value,
				false
			);
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
		return "section";
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
		var result = { name: this._name, title: this._title, type: "section", elements: [] };
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

if(typeof BX.UI.EntityConfigField === "undefined")
{
	BX.UI.EntityConfigField = function()
	{
		BX.UI.EntityConfigField.superclass.constructor.apply(this);
		this._index = -1;
		this._optionFlags = 0;

	};
	BX.extend(BX.UI.EntityConfigField, BX.UI.EntityConfigItem);
	BX.UI.EntityConfigField.prototype.doInitialize = function()
	{
		this._optionFlags = BX.prop.getInteger(this._data, "optionFlags", 0);
	};
	BX.UI.EntityConfigField.prototype.toJSON = function()
	{
		var result = { name: this._name };
		if(this._title !== "")
		{
			result["title"] = this._title;
		}
		if(this._optionFlags > 0)
		{
			result["optionFlags"] = this._optionFlags;
		}
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