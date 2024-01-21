BX.namespace("BX.UI");

if(typeof(BX.UI.Form) === "undefined")
{
	BX.UI.Form = function()
	{
		this._id = "";
		this._settings = null;
		this._elementNode = null;
		this._formData = null;
	};
	BX.UI.Form.prototype =
	{
		initialize: function(id, setting)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : "";
			this._settings = BX.type.isPlainObject(setting) ? setting : {};
			this._elementNode = BX.prop.getElementNode(this._settings, "elementNode", null);
			this._formData = BX.prop.getObject(this._settings, "formData", null);
			if(!this._elementNode && !this._formData)
			{
				throw "BX.UI.Form: Could not find 'elementNode' or 'formData' parameter in settings.";
			}

			this.doInitialize();
		},
		doInitialize: function()
		{
		},
		getId: function()
		{
			return this._id;
		},
		getElementNode: function()
		{
			return this._elementNode;
		},
		submit: function(options)
		{
			if(!BX.type.isPlainObject(options))
			{
				options = {};
			}

			var eventArgs = { cancel: false, options: options };
			BX.onCustomEvent(this, "onBeforeSubmit", [this, eventArgs]);
			if(eventArgs["cancel"])
			{
				return false;
			}

			this.doSubmit(options);
			BX.onCustomEvent(this, "onAfterSubmit", [this, { options: options }]);
			return true;
		},
		doSubmit: function(options)
		{
		}
	};
}

if(typeof(BX.UI.AjaxForm) === "undefined")
{
	BX.UI.AjaxForm = function()
	{
		BX.UI.AjaxForm.superclass.constructor.apply(this);
		this._config = null;
	};
	BX.extend(BX.UI.AjaxForm, BX.UI.Form);
	BX.UI.AjaxForm.prototype.doInitialize = function()
	{
		this._config = BX.prop.getObject(this._settings, "config", null);
		if(!this._config)
		{
			throw "BX.UI.AjaxForm: Could not find 'config' parameter in settings.";
		}

		if(BX.prop.getString(this._config, "url", "") === "")
		{
			throw "BX.UI.AjaxForm: Could not find 'url' parameter in config";
		}

		if(BX.prop.getString(this._config, "method", "") === "")
		{
			this._config["method"] = "POST";
		}

		if(BX.prop.getString(this._config, "dataType", "") === "")
		{
			this._config["dataType"] = "json";
		}
	};
	BX.UI.AjaxForm.prototype.getUrl = function()
	{
		return BX.prop.getString(this._config, "url", "");
	};
	BX.UI.AjaxForm.prototype.setUrl = function(url)
	{
		this._config["url"] = url;
	};
	BX.UI.AjaxForm.prototype.addUrlParams = function(params)
	{
		if(BX.type.isPlainObject(params) && Object.keys(params).length > 0)
		{
			this._config["url"] = BX.util.add_url_param(BX.prop.getString(this._config, "url", ""), params);
		}
	};

	BX.UI.AjaxForm.prototype.doSubmit = function(options)
	{
		if (!this._elementNode)
		{
			var config = BX.clone(this._config);
			if (!config.data)
			{
				config.data = {};
			}
			config.data = BX.merge(config.data, this._formData);
			BX.ajax(config);
		}
		else
		{
			BX.ajax.submitAjax(this._elementNode, this._config);
		}
	};
	BX.UI.AjaxForm.create = function(id, settings)
	{
		var self = new BX.UI.AjaxForm();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof(BX.UI.ComponentAjax) === "undefined")
{
	BX.UI.ComponentAjax = function()
	{
		BX.UI.ComponentAjax.superclass.constructor.apply(this);
		this._className = "";
		this._actionName = "";
		this._signedParameters = null;
		this._callbacks = null;
		this._getParameters = {};
	};
	BX.extend(BX.UI.ComponentAjax, BX.UI.Form);
	BX.UI.ComponentAjax.prototype.doInitialize = function()
	{
		this._className = BX.prop.getString(this._settings, "className", "");
		this._actionName = BX.prop.getString(this._settings, "actionName", "");
		this._signedParameters = BX.prop.getString(this._settings, "signedParameters", null);
		this._callbacks = BX.prop.getObject(this._settings, "callbacks", {});

	};
	BX.UI.ComponentAjax.prototype.addUrlParams = function(params)
	{
		if(BX.type.isPlainObject(params) && Object.keys(params).length > 0)
		{
			this._getParameters = BX.merge(this._getParameters, params);
		}
	};
	BX.UI.ComponentAjax.prototype._prepareFormData = function(data, formData, parent)
	{
		for (var dataElement in data)
		{
			var dataElementParent = (parent !== '') ? parent + '[' + dataElement + ']' : dataElement;

			if (BX.type.isPlainObject(data[dataElement]) || BX.type.isArray(data[dataElement]))
			{
				this._prepareFormData(data[dataElement], formData, dataElementParent);
			}
			else
			{
				formData.append(dataElementParent, data[dataElement]);
			}
		}
	};
	BX.UI.ComponentAjax.prototype.makeFormData = function(data)
	{
		var formData = new FormData();

		this._prepareFormData(data, formData, '');

		return formData;
	};
	BX.UI.ComponentAjax.prototype.doSubmit = function(options)
	{
		var formData = this._elementNode
			? BX.ajax.prepareForm(this._elementNode)
			: {data : BX.clone(this._formData), filesCount : 0}
		;

		if (BX.type.isPlainObject(options.data))
		{
			for (var i in options.data)
			{
				if (options.data.hasOwnProperty(i))
				{
					formData.data[i] = options.data[i];
				}
			}
		}

		var resultData = formData.filesCount > 0 ? this.makeFormData(formData) : formData;

		BX.ajax.runComponentAction(
			this._className,
			this._actionName,
			{
				mode: "class",
				signedParameters: this._signedParameters,
				data: resultData,
				getParameters: this._getParameters
			}
		).then(
			function(response)
			{
				var callback = BX.prop.getFunction(this._callbacks, "onSuccess", null);
				if(callback)
				{
					BX.onCustomEvent(
						window,
						"BX.UI.EntityEditorAjax:onSubmit",
						[ response["data"]["ENTITY_DATA"], response ]
					);
					callback(response["data"]);
				}
			}.bind(this)
		).catch(
			function(response)
			{
				var callback = BX.prop.getFunction(this._callbacks, "onFailure", null);
				if(!callback)
				{
					return;
				}

				var messages = [];
				var errors = response["errors"];
				for(var i = 0, length = errors.length; i < length; i++)
				{
					messages.push(errors[i]["message"]);
				}
				BX.onCustomEvent(
					window,
					"BX.UI.EntityEditorAjax:onSubmitFailure",
					[ response["errors"] ]
				);
				callback({ "ERRORS": messages });
			}.bind(this)
		);
	};
	BX.UI.ComponentAjax.create = function(id, settings)
	{
		var self = new BX.UI.ComponentAjax();
		self.initialize(id, settings);
		return self;
	};
}