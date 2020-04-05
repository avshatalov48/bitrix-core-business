BX.namespace("BX.UI");

if(typeof BX.UI.EntityEditorToolPanel === "undefined")
{
	BX.UI.EntityEditorToolPanel = function()
	{
		this._id = "";
		this._settings = {};
		this._container = null;
		this._wrapper = null;
		this._editor = null;
		this._isVisible = false;
		this._isLocked = false;
		this._hasLayout = false;
		this._keyPressHandler = BX.delegate(this.onKeyPress, this);
	};

	BX.UI.EntityEditorToolPanel.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._container = BX.prop.getElementNode(this._settings, "container", null);
			this._editor = BX.prop.get(this._settings, "editor", null);
			this._isVisible = BX.prop.getBoolean(this._settings, "visible", false);
		},
		getId: function()
		{
			return this._id;
		},
		getContainer: function()
		{
			return this._container;
		},
		setContainer: function (container)
		{
			this._container = container;
		},
		isVisible: function()
		{
			return this._isVisible;
		},
		setVisible: function(visible)
		{
			visible = !!visible;
			if(this._isVisible === visible)
			{
				return;
			}

			this._isVisible = visible;
			this.adjustLayout();
		},
		isLocked: function()
		{
			return this._isLocked;
		},
		setLocked: function(locked)
		{
			locked = !!locked;
			if(this._isLocked === locked)
			{
				return;
			}

			this._isLocked = locked;

			if(locked)
			{
				BX.addClass(this._editButton, "ui-btn-clock");
			}
			else
			{
				BX.removeClass(this._editButton, "ui-btn-clock");
			}
		},
		disableSaveButton: function()
		{
			if(!this._editButton)
			{
				return;
			}

			this._editButton.disabled = true;
			BX.addClass(this._editButton, 'ui-btn-disabled');
		},
		enableSaveButton: function()
		{
			if(!this._editButton)
			{
				return;
			}

			this._editButton.disabled = false;
			BX.removeClass(this._editButton, 'ui-btn-disabled');
		},
		isSaveButtonEnabled: function()
		{
			return this._editButton && !this._editButton.disabled;
		},
		layout: function()
		{
			this._editButton = BX.create("button",
				{
					props: { className: "ui-btn ui-btn-success", title: "[Ctrl+Enter]" },
					text: BX.message("UI_ENTITY_EDITOR_SAVE"),
					events: { click: BX.delegate(this.onSaveButtonClick, this) }
				}
			);

			this._cancelButton = BX.create("a",
				{
					props:  { className: "ui-btn ui-btn-link", title: "[Esc]" },
					text: BX.message("UI_ENTITY_EDITOR_CANCEL"),
					attrs:  { href: "#" },
					events: { click: BX.delegate(this.onCancelButtonClick, this) }
				}
			);

			this._errorContainer = BX.create("DIV", { props: { className: "ui-entity-section-control-error-block" } });
			this._errorContainer.style.maxHeight = "0";

			this._wrapper = BX.create("DIV",
				{
					props: { className: "ui-entity-wrap" },
					children :
						[
							BX.create("DIV",
								{
									props: { className: "ui-entity-section ui-entity-section-control" },
									children : [ this._editButton, this._cancelButton, this._errorContainer ]
								}
							)
						]
				}
			);

			this._container.appendChild(this._wrapper);

			this._hasLayout = true;
			this.adjustLayout();
		},
		adjustLayout: function()
		{
			if(!this._hasLayout)
			{
				return;
			}

			if(!this._isVisible)
			{
				BX.removeClass(this._wrapper, "crm-section-control-active");
				BX.unbind(document, "keydown", this._keyPressHandler);
			}
			else
			{
				BX.addClass(this._wrapper, "crm-section-control-active");
				BX.bind(document, "keydown", this._keyPressHandler);
			}
		},
		getPosition: function()
		{
			return this._hasLayout ? BX.pos(this._wrapper) : null;
		}
	};
	BX.UI.EntityEditorToolPanel.prototype.onSaveButtonClick = function(e)
	{
		if(!this._isLocked)
		{
			this._editor.saveChanged();
		}
	};
	BX.UI.EntityEditorToolPanel.prototype.onCancelButtonClick = function(e)
	{
		if(!this._isLocked)
		{
			this._editor.cancel();
		}
		return BX.eventReturnFalse(e);
	};
	BX.UI.EntityEditorToolPanel.prototype.onKeyPress = function(e)
	{
		if(!this._isVisible)
		{
			return;
		}

		if(BX.type.isFunction(BX.PopupWindowManager.isAnyPopupShown) && BX.PopupWindowManager.isAnyPopupShown())
		{
			return;
		}

		e = e || window.event;
		if (e.keyCode == 27)
		{
			//Esc pressed
			this._editor.cancel();
			BX.eventCancelBubble(e);
		}
		else if (e.keyCode == 13 && e.ctrlKey)
		{
			//Ctrl+Enter pressed
			this._editor.saveChanged();
			BX.eventCancelBubble(e);
		}
	};
	BX.UI.EntityEditorToolPanel.prototype.addError = function(error)
	{
		this._errorContainer.appendChild(
			BX.create("DIV",
				{
					attrs: { className: "ui-entity-section-control-error-text" },
					html: error
				}
			)
		);

		this._errorContainer.style.maxHeight = "";
	};
	BX.UI.EntityEditorToolPanel.prototype.clearErrors = function()
	{
		this._errorContainer.innerHTML = "";
		this._errorContainer.style.maxHeight = "0";
	};
	BX.UI.EntityEditorToolPanel.prototype.getMessage = function(name)
	{
		var m = BX.UI.EntityEditorToolPanel.messages;
		return m.hasOwnProperty(name) ? m[name] : name;
	};
	if(typeof(BX.UI.EntityEditorToolPanel.messages) === "undefined")
	{
		BX.UI.EntityEditorToolPanel.messages = {};
	}
	BX.UI.EntityEditorToolPanel.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorToolPanel();
		self.initialize(id, settings);
		return self;
	};
}