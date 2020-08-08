BX.namespace("BX.UI");

//region EDITOR FIELD SINGLE EDIT CONTROLLER
if(typeof BX.UI.EditorFieldSingleEditController === "undefined")
{
	BX.UI.EditorFieldSingleEditController = function()
	{
		this._id = "";
		this._settings = null;
		this._field = null;
		this._wrapper = null;

		this._fieldWrapperHandler = BX.delegate(this.onFieldWrapperClick, this);
		this._documentHandler = BX.delegate(this.onDocumentClick, this);
		this._documentTimeoutHandle = 0;

		this._isInitialized = false;
		this._isActive = false;
	};
	BX.UI.EditorFieldSingleEditController.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
				this._settings = settings ? settings : {};

				this._time = (new Date()).toString();

				this._field = BX.prop.get(this._settings, "field");
				if(!(this._field instanceof BX.UI.EntityEditorField))
				{
					throw "EditorFieldSingleEditController: The 'field' param must be EntityEditorField.";
				}

				this._wrapper = this._field.getWrapper();
				if(!BX.type.isElementNode(this._wrapper))
				{
					throw "EditorFieldSingleEditController: Could not find the wrapper element.";
				}

				window.setTimeout(BX.delegate(this.bind, this), 100);
				this._isActive = this._isInitialized = true;
			},
			isActive: function()
			{
				return this._isActive;
			},
			setActive: function(active)
			{
				this._isActive = !!active;
			},
			setActiveDelayed: function(active, delay)
			{
				if(typeof(delay) === "undefined")
				{
					delay = 0;
				}

				window.setTimeout(
					BX.delegate(function(){ this.setActive(active); }, this),
					delay
				);
			},
			release: function()
			{
				this._isActive = this._isInitialized = false;
				this.unbind();
			},
			bind: function()
			{
				if(this._isInitialized)
				{
					BX.bind(this._wrapper, "click", this._fieldWrapperHandler);
					BX.bind(document, "click", this._documentHandler);
				}
			},
			unbind: function()
			{
				BX.unbind(this._wrapper, "click", this._fieldWrapperHandler);
				BX.unbind(document, "click", this._documentHandler);
			},
			saveControl: function()
			{
				if(!this._isActive)
				{
					return;
				}

				var editor = this._field.getEditor();
				if(editor)
				{
					editor.switchControlMode(this._field, BX.UI.EntityEditorMode.view, BX.UI.EntityEditorModeOptions.none);
					//Is not supported by the all controls
					//editor.saveControl(this._field);
				}

				this._isActive = false;
			},
			onFieldWrapperClick: function(e)
			{
				//The call of "preventDefault" is not allowed because of the checkbox controls
				BX.eventCancelBubble(e);
			},
			onDocumentClick: function(e)
			{
				if(this._documentTimeoutHandle > 0)
				{
					window.clearTimeout(this._documentTimeoutHandle);
					this._documentTimeoutHandle = 0;
				}

				this._documentTimeoutHandle = window.setTimeout(
					BX.delegate(this.saveControl, this),
					400
				);
			}
		};
	BX.UI.EditorFieldSingleEditController.create = function(id, settings)
	{
		var self = new BX.UI.EditorFieldSingleEditController();
		self.initialize(id, settings);
		return self;
	}
}
//endregion

//region EDITOR FIELD VIEW CONTROLLER
if(typeof BX.UI.EditorFieldViewController === "undefined")
{
	BX.UI.EditorFieldViewController = function()
	{
		this._id = "";
		this._settings = null;
		this._field = null;
		this._wrapper = null;

		this._timeoutHandle = 0;
		this._time = 0;
		this._pos = { x: 0, y: 0 };

		this._mouseDownHandler = BX.delegate(this.onMouseDown, this);
		this._mouseUpHandler = BX.delegate(this.onMouseUp, this);

		this._isInitialized = false;
		this._isActive = false;
	};
	BX.UI.EditorFieldViewController.prototype =
		{
			initialize: function (id, settings)
			{
				this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
				this._settings = settings ? settings : {};

				this._field = BX.prop.get(this._settings, "field");
				if (!(this._field instanceof BX.UI.EntityEditorField)) {
					throw "EditorFieldViewController: The 'field' param must be EntityEditorField.";
				}

				this._wrapper = BX.prop.getElementNode(this._settings, "wrapper");
				if (!BX.type.isElementNode(this._wrapper)) {
					throw "EditorFieldSingleEditController: Could not find the wrapper element.";
				}

				window.setTimeout(BX.delegate(this.bind, this), 100);
				this._isActive = this._isInitialized = true;
			},
			release: function()
			{
				this._isActive = this._isInitialized = false;
				this.unbind();
			},
			bind: function()
			{
				if(this._isInitialized)
				{
					BX.bind(this._wrapper, "mousedown", this._mouseDownHandler);
					BX.bind(this._wrapper, "mouseup", this._mouseUpHandler);
				}
			},
			unbind: function()
			{
				BX.unbind(this._wrapper, "mousedown", this._mouseDownHandler);
				BX.unbind(this._wrapper, "mouseup", this._mouseUpHandler);
			},
			onMouseDown: function(e)
			{
				if(this._timeoutHandle > 0)
				{
					window.clearTimeout(this._timeoutHandle);
					this._timeoutHandle = 0;
				}

				if(!this.isHandleableEvent(e))
				{
					return;
				}

				this._time = new Date().valueOf();
				this._pos = { x: e.clientX, y: e.clientY };
			},
			onMouseUp: function(e)
			{
				if(this._timeoutHandle > 0)
				{
					window.clearTimeout(this._timeoutHandle);
					this._timeoutHandle = 0;
				}

				if(!this.isHandleableEvent(e))
				{
					return;
				}

				if((new Date().valueOf() - this._time) < 400 || Math.abs(this._pos.x - e.clientX) < 2)
				{
					this._timeoutHandle = window.setTimeout(
						BX.delegate(this.switchTo, this),
						0
					);
				}

				this._time = 0;
			},
			isHandleableEvent: function(e)
			{
				var node = BX.getEventTarget(e);
				if(node.tagName === "A")
				{
					return false;
				}

				if(node.getAttribute("data-editor-control-type") === "button")
				{
					return false;
				}

				return !BX.findParent(node, { tagName: "a" }, this._wrapper);
			},
			switchTo: function()
			{
				this._field.switchToSingleEditMode();
			}
		};
	BX.UI.EditorFieldViewController.create = function(id, settings)
	{
		var self = new BX.UI.EditorFieldViewController();
		self.initialize(id, settings);
		return self;
	}
}

if (typeof BX.UI.EntityEditorController === 'undefined')
{
	BX.UI.EntityEditorController = function()
	{
		this._id = '';
		this._settings = {};

		this._editor = null;
		this._model = null;
		this._config = null;

		this._isChanged = false;
	};

	BX.UI.EntityEditorController.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._editor = BX.prop.get(this._settings, 'editor', null);
			this._model = BX.prop.get(this._settings, 'model', null);
			this._config = BX.prop.getObject(this._settings, 'config', {});

			this.doInitialize();
		},

		doInitialize: function()
		{
		},

		getConfig: function()
		{
			return this._config;
		},

		getConfigStringParam: function(name, defaultValue)
		{
			return BX.prop.getString(this._config, name, defaultValue);
		},

		isChanged: function()
		{
			return this._isChanged;
		},

		markAsChanged: function()
		{
			if (this._isChanged)
			{
				return;
			}

			this._isChanged = true;
			if (this._editor)
			{
				this._editor.processControllerChange(this);
			}
		},

		rollback: function()
		{
		},

		innerCancel: function()
		{
		},

		onBeforeSubmit: function()
		{
		},

		onAfterSave: function()
		{
			if (this._isChanged)
			{
				this._isChanged = false;
			}
		},

		onBeforeSaveControl: function(data)
		{
			return data;
		}
	};
}
//endregion