BX.namespace("BX.UI");

if(typeof BX.UI.EntityModel === "undefined")
{
	BX.UI.EntityModel = function()
	{
		this._id = "";
		this._settings = {};
		this._isIdentifiable = true;
		this._data = null;
		this._initData = null;
		this._lockedFields = null;
		this._changeNotifier = null;
		this._lockNotifier = null;
		this.eventsNamespace = 'UI.EntityModel';
	};
	BX.UI.EntityModel.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};
			this._isIdentifiable = BX.prop.getBoolean(this._settings, "isIdentifiable", true);
			this._data = BX.prop.getObject(this._settings, "data", {});
			this._initData = BX.clone(this._data);
			this._lockedFields = {};

			this.doInitialize();
		},
		doInitialize: function()
		{
		},
		getEventArguments: function()
		{
			return { entityTypeName: this.getEntityTypeName(), entityId: this.getEntityId() };
		},
		getEntityTypeName: function()
		{
			return '';
		},
		isIdentifiable: function()
		{
			return this._isIdentifiable;
		},
		getEntityId: function()
		{
			return BX.prop.getInteger(this._data, "ID", 0);
		},
		hasField: function(name)
		{
			return this._data.hasOwnProperty(name);
		},
		getField: function(name, defaultValue)
		{
			if(defaultValue === undefined)
			{
				defaultValue = null;
			}
			return BX.prop.get(this._data, name, defaultValue);
		},
		getStringField: function(name, defaultValue)
		{
			if(defaultValue === undefined)
			{
				defaultValue = null;
			}
			return BX.prop.getString(this._data, name, defaultValue);
		},
		getIntegerField: function(name, defaultValue)
		{
			if(defaultValue === undefined)
			{
				defaultValue = null;
			}
			return BX.prop.getInteger(this._data, name, defaultValue);
		},
		getNumberField: function(name, defaultValue)
		{
			if(defaultValue === undefined)
			{
				defaultValue = null;
			}
			return BX.prop.getNumber(this._data, name, defaultValue);
		},
		getArrayField: function(name, defaultValue)
		{
			if(defaultValue === undefined)
			{
				defaultValue = null;
			}
			return BX.prop.getArray(this._data, name, defaultValue);
		},
		registerNewField: function(name, value)
		{
			//update data
			this._data[name] = value;
			//update initialization data because of rollback.
			this._initData[name] = value;
		},
		setField: function(name, value, options)
		{
			if(this._data.hasOwnProperty(name) && this._data[name] === value)
			{
				return;
			}

			this._data[name] = value;

			if(!BX.type.isPlainObject(options))
			{
				options = {};
			}

			if(BX.prop.getBoolean(options, "enableNotification", true))
			{
				var eventArgs = this.getEventArguments();
				eventArgs.fieldName = name;
				BX.onCustomEvent(
					window,
					this.eventsNamespace + ".Change",
					[ this, eventArgs ]
				);

				if(this._changeNotifier && BX.type.isFunction(this._changeNotifier.notify))
				{
					this._changeNotifier.notify(
						[
							{
								name: name,
								originator: BX.prop.get(options, "originator", null)
							}
						]
					);
				}
			}
		},
		getData: function()
		{
			return this._data;
		},
		setData: function(data, options)
		{
			this._data = BX.type.isPlainObject(data) ? data : {};
			this._initData = BX.clone(this._data);

			if(BX.prop.getBoolean(options, "enableNotification", true))
			{
				var eventArgs = this.getEventArguments();
				eventArgs.forAll = true;
				BX.onCustomEvent(
					window,
					this.eventsNamespace + ".Change",
					[ this, eventArgs ]
				);

				if(this._changeNotifier && BX.type.isFunction(this._changeNotifier.notify))
				{
					this._changeNotifier.notify(
						[
							{
								forAll: true,
								originator: BX.prop.get(options, "originator", null)
							}
						]
					);
				}
			}
		},
		updateData: function(data, options)
		{
			if(!BX.type.isPlainObject(data))
			{
				return;
			}

			this._data = BX.mergeEx(this._data, data);
			if(BX.prop.getBoolean(options, "enableNotification", true))
			{
				var eventArgs = this.getEventArguments();
				eventArgs.forAll = true;

				BX.onCustomEvent(
					window,
					this.eventsNamespace + ".Change",
					[ this, eventArgs ]
				);

				if(this._changeNotifier && BX.type.isFunction(this._changeNotifier.notify))
				{
					this._changeNotifier.notify(
						[
							{
								forAll: true,
								originator: BX.prop.get(options, "originator", null)
							}
						]
					);
				}
			}
		},
		updateDataObject: function(name, data, options)
		{
			if(!this._data.hasOwnProperty(name))
			{
				this._data[name] = data;
			}
			else
			{
				this._data[name] = BX.mergeEx(this._data[name], data);
			}

			if(BX.prop.getBoolean(options, "enableNotification", true))
			{
				var eventArgs = this.getEventArguments();
				eventArgs.forAll = true;

				BX.onCustomEvent(
					window,
					this.eventsNamespace + ".Change",
					[ this, eventArgs ]
				);

				if(this._changeNotifier && BX.type.isFunction(this._changeNotifier.notify))
				{
					this._changeNotifier.notify(
						[
							{
								forAll: true,
								originator: BX.prop.get(options, "originator", null)
							}
						]
					);
				}
			}
		},
		getInitFieldValue: function(name, defaultValue)
		{
			if(defaultValue === undefined)
			{
				defaultValue = null;
			}
			return BX.prop.get(this._initData, name, defaultValue);
		},
		setInitFieldValue: function(name, value)
		{
			if(this._initData.hasOwnProperty(name) && this._initData[name] === value)
			{
				return;
			}
			this._initData[name] = value;
		},
		getSchemeField: function(schemeElement, name, defaultValue)
		{
			return this.getField(schemeElement.getDataStringParam(name, ""), defaultValue);
		},
		setSchemeField: function(schemeElement, name, value)
		{
			var fieldName = schemeElement.getDataStringParam(name, "");
			if(fieldName !== "")
			{
				this.setField(fieldName, value);
			}
		},
		getMappedField: function(map, name, defaultValue)
		{
			var fieldName = BX.prop.getString(map, name, "");
			return fieldName !== "" ? this.getField(fieldName, defaultValue) : defaultValue;
		},
		setMappedField: function(map, name, value)
		{
			var fieldName = BX.prop.getString(map, name, "");
			if(fieldName !== "")
			{
				this.setField(fieldName, value);
			}
		},
		save: function()
		{
		},
		rollback: function()
		{
			this._data = BX.clone(this._initData);
		},
		lockField: function(fieldName)
		{
			if(this._lockedFields.hasOwnProperty(fieldName))
			{
				return;
			}

			this._lockedFields[fieldName] = true;
			if(this._lockNotifier && BX.type.isFunction(this._lockNotifier.notify))
			{
				this._lockNotifier.notify([ { name: fieldName, isLocked: true } ]);
			}
		},
		unlockField: function(fieldName)
		{
			if(!this._lockedFields.hasOwnProperty(fieldName))
			{
				return;
			}

			delete this._lockedFields[fieldName];
			if(this._lockNotifier && BX.type.isFunction(this._lockNotifier.notify))
			{
				this._lockNotifier.notify([ { name: fieldName, isLocked: false } ]);
			}
		},
		isFieldLocked: function(fieldName)
		{
			return this._lockedFields.hasOwnProperty(fieldName);
		},
		isCaptionEditable: function()
		{
			return false;
		},
		getCaption: function()
		{
			return "";
		},
		setCaption: function(caption)
		{
		},
		prepareCaptionData: function(data)
		{
		},
		addChangeListener: function(listener)
		{
			if(this._changeNotifier && BX.type.isFunction(this._changeNotifier.addListener))
			{
				this._changeNotifier.addListener(listener);
			}
		},
		removeChangeListener: function(listener)
		{
			if(this._changeNotifier && BX.type.isFunction(this._changeNotifier.removeListener))
			{
				this._changeNotifier.removeListener(listener);
			}
		},
		addLockListener: function(listener)
		{
			if(this._lockNotifier && BX.type.isFunction(this._lockNotifier.addListener))
			{
				this._lockNotifier.addListener(listener);
			}
		},
		removeLockListener: function(listener)
		{
			if(this._lockNotifier && BX.type.isFunction(this._lockNotifier.removeListener))
			{
				this._lockNotifier.removeListener(listener);
			}
		}
	};
	BX.UI.EntityModel.create = function(id, settings)
	{
		var self = new BX.UI.EntityModel();
		self.initialize(id, settings);
		return self;
	};
}