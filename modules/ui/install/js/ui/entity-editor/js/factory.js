BX.namespace("BX.UI");

if(typeof BX.UI.EntityEditorValidatorFactory === "undefined")
{
	BX.UI.EntityEditorValidatorFactory =
	{
		create: function(type, settings)
		{
			if(type === "person")
			{
				return BX.UI.EntityPersonValidator.create(settings);
			}

			return null;
		}
	}
}

if(typeof BX.UI.EntityEditorControlFactory === "undefined")
{
	BX.UI.EntityEditorControlFactory =
	{
		initialized: false,
		methods: {},

		isInitialized: function()
		{
			return this.initialized;
		},
		initialize: function()
		{
			if(this.initialized)
			{
				return;
			}

			var eventArgs = { methods: {} };
			BX.onCustomEvent(
				window,
				"BX.UI.EntityEditorControlFactory:onInitialize",
				[ this, eventArgs ]
			);

			for(var name in eventArgs.methods)
			{
				if(eventArgs.methods.hasOwnProperty(name))
				{
					this.registerFactoryMethod(name, eventArgs.methods[name]);
				}
			}

			this.initialized = true;
		},
		registerFactoryMethod: function(name, method)
		{
			if(BX.type.isFunction(method))
			{
				this.methods[name] = method;
			}
		},
		create: function(type, controlId, settings)
		{
			if(!this.initialized)
			{
				this.initialize();
			}

			if(type === "column")
			{
				return BX.UI.EntityEditorColumn.create(controlId, settings);
			}
			else if(type === "section")
			{
				return BX.UI.EntityEditorSection.create(controlId, settings);
			}
			else if(type === "text")
			{
				return BX.UI.EntityEditorText.create(controlId, settings);
			}
			else if(type === "multitext")
			{
				return BX.UI.EntityEditorMultiText.create(controlId, settings);
			}
			else if(type === "textarea")
			{
				return BX.UI.EntityEditorTextarea.create(controlId, settings);
			}
			else if(type === "number")
			{
				return BX.UI.EntityEditorNumber.create(controlId, settings);
			}
			else if(type === "multinumber")
			{
				return BX.UI.EntityEditorMultiNumber.create(controlId, settings);
			}
			else if(type === "datetime")
			{
				return BX.UI.EntityEditorDatetime.create(controlId, settings);
			}
			else if(type === "multidatetime")
			{
				return BX.UI.EntityEditorMultiDatetime.create(controlId, settings);
			}
			else if(type === "boolean")
			{
				return BX.UI.EntityEditorBoolean.create(controlId, settings);
			}
			else if(type === "list")
			{
				return BX.UI.EntityEditorList.create(controlId, settings);
			}
			else if(type === "multilist")
			{
				return BX.UI.EntityEditorMultiList.create(controlId, settings);
			}
			else if(type === "html")
			{
				return BX.UI.EntityEditorHtml.create(controlId, settings);
			}
			else if(type === "link")
			{
				return BX.UI.EntityEditorLink.create(controlId, settings);
			}
			else if(type === "image")
			{
				return BX.UI.EntityEditorImage.create(controlId, settings);
			}
			else if(type === "custom")
			{
				return BX.UI.EntityEditorCustom.create(controlId, settings);
			}
			else if(type === "money")
			{
				return BX.UI.EntityEditorMoney.create(controlId, settings);
			}
			else if(type === "user")
			{
				return BX.UI.EntityEditorUser.create(controlId, settings);
			}
			else if(type === "included_area")
			{
				return BX.UI.EntityEditorIncludedArea.create(controlId, settings);
			}

			for(var name in this.methods)
			{
				if(!this.methods.hasOwnProperty(name))
				{
					continue;
				}

				var control = this.methods[name](type, controlId, settings);
				if(control)
				{
					return control;
				}
			}

			return null;
		}
	};
}

if (typeof BX.UI.EntityEditorControllerFactory === 'undefined')
{
	BX.UI.EntityEditorControllerFactory =
		{
			methods: null,

			create: function(type, controllerId, settings)
			{
				if (this.methods === null)
				{
					this.registerEventFactories();
				}

				return this.findEventController(type, controllerId, settings);
			},

			registerEventFactories: function()
			{
				var eventArgs = {methods: {}};
				BX.onCustomEvent(
					window,
					'BX.UI.EntityEditorControllerFactory:onInitialize',
					[this, eventArgs]
				);

				this.methods = {};

				for (var name in eventArgs.methods)
				{
					if (eventArgs.methods.hasOwnProperty(name))
					{
						this.registerEventFactory(name, eventArgs.methods[name]);
					}
				}
			},

			registerEventFactory: function(name, method)
			{
				if (BX.type.isFunction(method))
				{
					this.methods[name] = method;
				}
			},

			findEventController: function(type, controllerId, settings)
			{
				for (var name in this.methods)
				{
					if (!this.methods.hasOwnProperty(name))
					{
						continue;
					}

					var controller = this.methods[name](type, controllerId, settings);
					if (controller)
					{
						return controller;
					}
				}
				
				return null;
			}
		};
}

if(typeof BX.UI.EntityEditorModelFactory === "undefined")
{
	BX.UI.EntityEditorModelFactory =
	{
		initialized: false,
		methods: {},

		isInitialized: function()
		{
			return this.initialized;
		},
		initialize: function()
		{
			if(this.initialized)
			{
				return;
			}

			var eventArgs = { methods: {} };
			BX.onCustomEvent(
				window,
				"BX.UI.EntityEditorModelFactory:onInitialize",
				[ this, eventArgs ]
			);

			for(var name in eventArgs.methods)
			{
				if(eventArgs.methods.hasOwnProperty(name))
				{
					this.registerFactoryMethod(name, eventArgs.methods[name]);
				}
			}

			this.initialized = true;
		},
		registerFactoryMethod: function(name, method)
		{
			if(BX.type.isFunction(method))
			{
				this.methods[name] = method;
			}
		},
		create: function(entityTypeName, id, settings)
		{
			if(!this.initialized)
			{
				this.initialize();
			}

			var model = null;
			if(BX.type.isFunction(this.methods[entityTypeName]))
			{
				model = this.methods[entityTypeName](entityTypeName, id, settings);
			}
			if(!model)
			{
				model =  BX.UI.EntityModel.create(id, settings);
			}
			return model;
		}
	};
}