BX.namespace("BX.UI");

if(typeof BX.UI.EntityValidator === "undefined")
{
	BX.UI.EntityValidator = function()
	{
		this._settings = {};
		this._editor = null;
		this._data = null;
	};
	BX.UI.EntityValidator.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};
			this._editor = BX.prop.get(this._settings, "editor", null);
			this._data = BX.prop.getObject(this._settings, "data", {});

			this.doInitialize();
		},
		doInitialize: function()
		{
		},
		release: function()
		{
		},
		getData: function()
		{
			return this._data;
		},
		getDataStringParam: function(name, defaultValue)
		{
			return BX.prop.getString(this._data, name, defaultValue);
		},
		getErrorMessage: function()
		{
			return BX.prop.getString(this._settings, "message", "");
		},
		validate: function(result)
		{
			return true;
		},
		processControlChange: function(control)
		{
		}
	};
}

if(typeof BX.UI.EntityPersonValidator === "undefined")
{
	BX.UI.EntityPersonValidator = function()
	{
		BX.UI.EntityPersonValidator.superclass.constructor.apply(this);
	};

	BX.extend(BX.UI.EntityPersonValidator, BX.UI.EntityValidator);

	BX.UI.EntityPersonValidator.prototype.doInitialize = function()
	{
		this._nameField = this._editor.getControlById(
			this.getDataStringParam("nameField", "")
		);
		if(this._nameField)
		{
			this._nameField.addValidator(this);
		}

		this._lastNameField = this._editor.getControlById(
			this.getDataStringParam("lastNameField", "")
		);
		if(this._lastNameField)
		{
			this._lastNameField.addValidator(this);
		}
	};
	BX.UI.EntityPersonValidator.prototype.release = function()
	{
		if(this._nameField)
		{
			this._nameField.removeValidator(this);
		}

		if(this._lastNameField)
		{
			this._lastNameField.removeValidator(this);
		}
	};
	BX.UI.EntityPersonValidator.prototype.validate = function(result)
	{
		var isNameActive = this._nameField.isActive();
		var isLastNameActive = this._lastNameField.isActive();

		if(!isNameActive && !isLastNameActive)
		{
			return true;
		}

		var name = isNameActive ? this._nameField.getRuntimeValue() : this._nameField.getValue();
		var lastName = isLastNameActive ? this._lastNameField.getRuntimeValue() : this._lastNameField.getValue();

		if(name !== "" || lastName !== "")
		{
			return true;
		}

		if(name === "" && isNameActive)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this._nameField }));
			this._nameField.showError(this.getErrorMessage());
		}

		if(lastName === "" && isLastNameActive)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this._lastNameField }));
			this._lastNameField.showError(this.getErrorMessage());
		}

		return false;
	};
	BX.UI.EntityPersonValidator.prototype.processFieldChange = function(field)
	{
		if(field !== this._nameField && field !== this._lastNameField)
		{
			return;
		}

		if(this._nameField)
		{
			this._nameField.clearError();
		}

		if(this._lastNameField)
		{
			this._lastNameField.clearError();
		}
	};
	BX.UI.EntityPersonValidator.create = function(settings)
	{
		var self = new BX.UI.EntityPersonValidator();
		self.initialize(settings);
		return self;
	};
}

if(typeof BX.UI.EntityValidationError === "undefined")
{
	BX.UI.EntityValidationError = function()
	{
		this._settings = {};
		this._field = null;
		this._message = "";
	};
	BX.UI.EntityValidationError.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};
			this._field = BX.prop.get(this._settings, "field", null);
			this._message = BX.prop.getString(this._settings, "message", "");
		},
		getField: function()
		{
			return this._field;
		},
		getMessage: function()
		{
			return this._message;
		}
	};
	BX.UI.EntityValidationError.create = function(settings)
	{
		var self = new BX.UI.EntityValidationError();
		self.initialize(settings);
		return self;
	};
}

if(typeof BX.UI.EntityValidationResult === "undefined")
{
	BX.UI.EntityValidationResult = function()
	{
		this._settings = {};
		this._errors = [];
	};
	BX.UI.EntityValidationResult.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};
		},
		getStatus: function()
		{
			return this._errors.length === 0;
		},
		addError: function(error)
		{
			this._errors.push(error);
		},
		getErrors: function()
		{
			return this._errors;
		},
		addResult: function(result)
		{
			var errors = result.getErrors();
			for(var i = 0, length = errors.length; i < length; i++)
			{
				this._errors.push(errors[i]);
			}
		},
		getTopmostField: function()
		{
			var field = null;
			var top = null;
			for(var i = 0, length = this._errors.length; i < length; i++)
			{
				var currentField = this._errors[i].getField();
				if(!field)
				{
					field = currentField;
					top = currentField.getPosition()["top"];
					continue;

				}
				var pos = currentField.getPosition();
				if(!pos)
				{
					continue;
				}

				var currentFieldTop = currentField.getPosition()["top"];
				if(currentFieldTop < top)
				{
					field = currentField;
					top = currentFieldTop;
				}
			}

			return field;
		}
	};
	BX.UI.EntityValidationResult.create = function(settings)
	{
		var self = new BX.UI.EntityValidationResult();
		self.initialize(settings);
		return self;
	};
}
if(typeof BX.UI.EntityAsyncValidator === "undefined")
{
	BX.UI.EntityAsyncValidator = function()
	{
		this.promisesList = [];
		this.isValid = true;
	};
	BX.UI.EntityAsyncValidator.prototype =
		{
			addResult: function(validationResult)
			{
				if (validationResult instanceof Promise || validationResult instanceof BX.Promise)
				{
					this.promisesList.push(validationResult);
				}
				else
				{
					this.isValid = (this.isValid && validationResult);
				}
			},
			validate: function()
			{
				if (this.promisesList.length)
				{
					return Promise.all(this.promisesList);
				}

				return this.isValid;
			}
		};
	BX.UI.EntityAsyncValidator.create = function()
	{
		return new BX.UI.EntityAsyncValidator();
	};
}

if(typeof BX.UI.EntityTrackingSourceValidator === "undefined")
{
	BX.UI.EntityTrackingSourceValidator = function()
	{
		BX.UI.EntityTrackingSourceValidator.superclass.constructor.apply(this);
	};

	BX.extend(BX.UI.EntityTrackingSourceValidator, BX.UI.EntityValidator);

	BX.UI.EntityTrackingSourceValidator.prototype.doInitialize = function()
	{
		this._trackingSourceField = this._editor.getControlById(
			this.getDataStringParam("fieldName", "")
		);
		if(this._trackingSourceField)
		{
			this._trackingSourceField.addValidator(this);
		}
	};
	BX.UI.EntityTrackingSourceValidator.prototype.release = function()
	{
		if(this._trackingSourceField)
		{
			this._trackingSourceField.removeValidator(this);
		}
	};
	BX.UI.EntityTrackingSourceValidator.prototype.validate = function(result)
	{
		var isActive = this._trackingSourceField.isActive();
		var value = "";
		var select;

		if(!isActive
			|| !(this._trackingSourceField.isRequired()
				|| this._trackingSourceField.isRequiredByAttribute()))
		{
			return true;
		}

		if (isActive)
		{
			if (this._trackingSourceField.hasOwnProperty("_innerWrapper")
				&& BX.Type.isDomNode(this._trackingSourceField["_innerWrapper"]))
			{
				select = this._trackingSourceField["_innerWrapper"].querySelector(
					"select[name=" + this._trackingSourceField.getId().replace(/["\\]/g, '\\$&') + "]"
				);
				if (select)
				{
					value = select.value;
				}
			}
		}
		else
		{
			value = this._trackingSourceField.getValue();
		}

		if(value !== "")
		{
			return true;
		}

		if(value === "" && isActive)
		{
			result.addError(BX.UI.EntityValidationError.create({ field: this._trackingSourceField }));
			this._trackingSourceField.showError(this._trackingSourceField.getMessage("requiredFieldError"));
		}

		return false;
	};
	BX.UI.EntityTrackingSourceValidator.prototype.processFieldChange = function(field)
	{
		if(field !== this._trackingSourceField)
		{
			return;
		}

		if(this._trackingSourceField)
		{
			this._trackingSourceField.clearError();
		}
	};
	BX.UI.EntityTrackingSourceValidator.create = function(settings)
	{
		var self = new BX.UI.EntityTrackingSourceValidator();
		self.initialize(settings);
		return self;
	};
}
