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
		this._customButtons = {};
		this._buttonsOrder = {
			VIEW: [],
			EDIT: [BX.UI.EntityEditorActionIds.defaultActionId, BX.UI.EntityEditorActionIds.cancelActionId],
		};
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
			var customButtons = BX.prop.getArray(this._settings, 'customButtons', []);
			for (var i = 0, length = customButtons.length; i < length; i++)
			{
				var customButtonProps = customButtons[i];
				this._customButtons[customButtonProps.ID] = this.createCustomButton(customButtonProps);
			}

			var buttonsOrder = BX.prop.getObject(this._settings, 'buttonsOrder', {});
			var editButtonsOrder = BX.prop.getArray(buttonsOrder, 'EDIT', []);
			var viewButtonsOrder = BX.prop.getArray(buttonsOrder, 'VIEW', []);
			if (editButtonsOrder.length > 0 || viewButtonsOrder.length > 0)
			{
				this._buttonsOrder = buttonsOrder;
			}

			this.attachToEditorEvents();
		},
		attachToEditorEvents: function()
		{
			BX.addCustomEvent(this._editor.eventsNamespace + ':onControlModeChange', BX.delegate(function(editor, additionalData) {
				if (editor !== this._editor || !additionalData.control)
				{
					return;
				}
				var control = additionalData.control;
				if(control.getMode() === BX.UI.EntityEditorMode.edit)
				{
					this.showEditModeButtons();
				}
				else
				{
					this.showViewModeButtons();
				}
			}, this));
			BX.addCustomEvent(this._editor.eventsNamespace + ':onControlChange', BX.delegate(function(editor) {
				if (editor !== this._editor)
				{
					return;
				}
				this.showEditModeButtons();
			}, this));
			BX.addCustomEvent(this._editor.eventsNamespace + ':onControllerChange', BX.delegate(function(editor) {
				if (editor !== this._editor)
				{
					return;
				}
				this.showEditModeButtons();
			}, this));
			BX.addCustomEvent(this._editor.eventsNamespace + ':onSwitchToViewMode', BX.delegate(function(editor) {
				if (editor !== this._editor)
				{
					return;
				}
				this.showViewModeButtons();
			}, this));
			BX.addCustomEvent(this._editor.eventsNamespace + ':onNothingChanged', BX.delegate(function(editor) {
				if (editor !== this._editor)
				{
					return;
				}
				this.showViewModeButtons();
			}, this));
		},
		showEditModeButtons: function ()
		{
			var editButtonsOrder = BX.prop.getArray(this._buttonsOrder, 'EDIT', []);
			if (editButtonsOrder.length > 0)
			{
				if (this._viewModeSectionControl)
				{
					BX.hide(this._viewModeSectionControl);
				}
				if (this._editModeSectionControl)
				{
					BX.show(this._editModeSectionControl);
				}
			}
		},
		showViewModeButtons: function ()
		{
			var viewButtonsOrder = BX.prop.getArray(this._buttonsOrder, 'VIEW', []);
			if (viewButtonsOrder.length > 0)
			{
				if (this._editModeSectionControl)
				{
					BX.hide(this._editModeSectionControl);
				}
				if (this._viewModeSectionControl)
				{
					BX.show(this._viewModeSectionControl);
				}
			}
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

			var activeButton = this._editButton;
			if (this._clickedButton)
			{
				activeButton = this._clickedButton;
			}
			if (activeButton)
			{
				if(locked)
				{
					BX.addClass(activeButton, "ui-btn-clock");
				}
				else
				{
					BX.removeClass(activeButton, "ui-btn-clock");
				}
			}
		},
		disableSaveButton: function()
		{
			if(!this._editButton)
			{
				return;
			}

			var buttonsToDisable = [this._editButton];

			var _this = this;
			Object.keys(this._customButtons).forEach(function (button) {
				if (_this._buttonsOrder.EDIT.includes(button))
				{
					buttonsToDisable.push(_this._customButtons[button]);
				}
			});

			buttonsToDisable.forEach(function (button) {
				button.disabled = true;
				BX.addClass(button, 'ui-btn-disabled');
			});

			BX.onCustomEvent(window, "onEntityEditorToolbarSaveButtonDisabled", [this]);
		},
		enableSaveButton: function()
		{
			if(!this._editButton)
			{
				return;
			}

			var buttonsToEnable = [this._editButton];

			var _this = this;
			Object.keys(this._customButtons).forEach(function (button) {
				if (_this._buttonsOrder.EDIT.includes(button))
				{
					buttonsToEnable.push(_this._customButtons[button]);
				}
			});

			buttonsToEnable.forEach(function (button) {
				button.disabled = false;
				BX.removeClass(button, 'ui-btn-disabled');
			});

			BX.onCustomEvent(window, "onEntityEditorToolbarSaveButtonEnabled", [this]);
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

			var sectionControlChildren = [];

			var editModeButtonsOrder = BX.prop.getArray(this._buttonsOrder, 'EDIT', []);
			var viewModeButtonsOrder = BX.prop.getArray(this._buttonsOrder, 'VIEW', []);
			if (editModeButtonsOrder.length > 0 || viewModeButtonsOrder.length > 0)
			{
				var editModeSectionControlChildren = [];
				var viewModeSectionControlChildren = [];
				for (var i = 0, length = editModeButtonsOrder.length; i < length; i++)
				{
					if (editModeButtonsOrder[i] === BX.UI.EntityEditorActionIds.defaultActionId)
					{
						editModeSectionControlChildren.push(this._editButton);
					}
					else if (editModeButtonsOrder[i] === BX.UI.EntityEditorActionIds.cancelActionId)
					{
						editModeSectionControlChildren.push(this._cancelButton);
					}
					else if (this._customButtons[editModeButtonsOrder[i]])
					{
						editModeSectionControlChildren.push(this._customButtons[editModeButtonsOrder[i]]);
					}
				}
				for (var i = 0, length = viewModeButtonsOrder.length; i < length; i++)
				{
					if (viewModeButtonsOrder[i] === BX.UI.EntityEditorActionIds.defaultActionId)
					{
						viewModeSectionControlChildren.push(this._editButton);
					}
					else if (viewModeButtonsOrder[i] === BX.UI.EntityEditorActionIds.cancelActionId)
					{
						viewModeSectionControlChildren.push(this._cancelButton);
					}
					else if (this._customButtons[viewModeButtonsOrder[i]])
					{
						viewModeSectionControlChildren.push(this._customButtons[viewModeButtonsOrder[i]]);
					}
				}
				this._editModeSectionControl = BX.create("DIV",
					{
						props: { className: "ui-entity-section ui-entity-section-control-edit-mode" },
						children : editModeSectionControlChildren,
					}
				);
				this._viewModeSectionControl = BX.create("DIV",
					{
						props: { className: "ui-entity-section ui-entity-section-control-view-mode" },
						children : viewModeSectionControlChildren,
					}
				);
				if (this._editor.getMode() === BX.UI.EntityEditorMode.edit)
				{
					this.showEditModeButtons();
				}
				else
				{
					this.showViewModeButtons();
				}
				sectionControlChildren = [ this._editModeSectionControl, this._viewModeSectionControl, this._errorContainer ];
			}
			else
			{
				sectionControlChildren = [ this._editButton, this._cancelButton, this._errorContainer ];
			}

			this._wrapper = BX.create("DIV",
				{
					props: { className: "ui-entity-wrap" },
					children :
						[
							BX.create("DIV",
								{
									props: { className: "ui-entity-section ui-entity-section-control" },
									children : sectionControlChildren
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
		createCustomButton: function(buttonProps)
		{
			var actionId = buttonProps.ACTION_ID;
			var className = "ui-btn";
			if (buttonProps.CLASS)
			{
				className += " " + buttonProps.CLASS;
			}
			return BX.create("button",
				{
					props: { className: className, id: "ui-entity-section-control-" + buttonProps.ID },
					text: BX.util.htmlspecialchars(buttonProps.TEXT),
					events: { click: BX.delegate(this.onCustomButtonClick, this) },
					dataset: {
						actionId: actionId,
					}
				}
			);
		},
		getPosition: function()
		{
			return this._hasLayout ? BX.pos(this._wrapper) : null;
		}
	};
	BX.UI.EntityEditorToolPanel.prototype.onSaveButtonClick = function(e)
	{
		this._clickedButton = e.target;
		if(!this._isLocked)
		{
			this._editor.saveChanged();
		}
	};
	BX.UI.EntityEditorToolPanel.prototype.onCustomButtonClick = function(e)
	{
		this._clickedButton = e.target;
		if(!this._isLocked)
		{
			this._editor.performAction(this._clickedButton.dataset.actionId);
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
		if (BX.Type.isStringFilled(error))
		{
			error = error
				.replace(/(<br(( *)\/)?>)/gi, '<br>')
				.split('<br>')
				.map(function(line) {
					return BX.Text.encode(line);
				})
				.join('<br>');
		}
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