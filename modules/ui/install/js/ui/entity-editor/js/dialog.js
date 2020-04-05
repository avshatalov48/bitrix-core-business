BX.namespace("BX.UI");

if(typeof BX.UI.DialogButtonType === "undefined")
{
	BX.UI.DialogButtonType =
		{
			undefined: 0,
			accept: 1,
			cancel: 2,

			names: { accept: "accept", cancel: "cancel" }
		};
}

if(typeof BX.UI.EditorDialogButton === "undefined")
{
	BX.UI.EditorDialogButton = function()
	{
		this._id = "";
		this._type = BX.UI.DialogButtonType.undefined;
		this._settings = {};
		this._dialog = null;
		this._keyPressHandler = BX.delegate(this.onKeyPress, this);
	};
	BX.UI.EditorDialogButton.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};

			this._type = BX.prop.getInteger(this._settings, "type", BX.UI.DialogButtonType.undefined);
			this._dialog = BX.prop.get(this._settings, "dialog", null);
		},
		bind: function()
		{
			if(this._type === BX.UI.DialogButtonType.accept)
			{
				BX.bind(document, "keydown", this._keyPressHandler);
			}
		},
		unbind: function()
		{
			if(this._type === BX.UI.DialogButtonType.accept)
			{
				BX.unbind(document, "keydown", this._keyPressHandler);
			}
		},
		onKeyPress: function(e)
		{
			if(this._type !== BX.UI.DialogButtonType.accept)
			{
				return;
			}

			e = e || window.event;
			if (e.keyCode === 13)
			{
				//Enter key
				this.onClick(e);
			}
		},
		getId: function()
		{
			return this._id;
		},
		getDialog: function()
		{
			return this._dialog;
		},
		prepareContent: function()
		{
			if(this._type === BX.UI.DialogButtonType.accept)
			{
				return (
					new BX.UI.SaveButton(
						{
							text : BX.prop.getString(this._settings, "text", this._id),
							events: { click: BX.delegate(this.onClick, this) }
						}
					)
				);
			}
			else if(this._type === BX.UI.DialogButtonType.cancel)
			{
				return (
					new BX.UI.CancelButton(
						{
							text : BX.prop.getString(this._settings, "text", this._id),
							events: { click: BX.delegate(this.onClick, this) }
						}
					)
				);
			}
			else
			{
				return (
					new BX.UI.Button(
						{
							text : BX.prop.getString(this._settings, "text", this._id),
							events: { click: BX.delegate(this.onClick, this) }
						}
					)
				);
			}
		},
		onClick: function(e)
		{
			var callback = BX.prop.getFunction(this._settings, "callback", null);
			if(callback)
			{
				callback(this);
			}
		}
	};
	BX.UI.EditorDialogButton.create = function(id, settings)
	{
		var self = new BX.UI.EditorDialogButton();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof BX.UI.EditorAuxiliaryDialog === "undefined")
{
	BX.UI.EditorAuxiliaryDialog = function()
	{
		this._id = "";
		this._settings = {};

		this._popup = null;
		this._buttons = null;
	};
	BX.UI.EditorAuxiliaryDialog.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(4);
			this._settings = settings ? settings : {};
		},
		getSetting: function(name, defaultValue)
		{
			return BX.prop.get(this._settings, name, defaultValue);
		},
		getId: function()
		{
			return this._id;
		},
		open: function()
		{
			this._popup = new BX.PopupWindow(
				this._id,
				BX.prop.getElementNode(this._settings, "anchor", null),
				{
					autoHide: false,
					draggable: false,
					offsetLeft: 0,
					offsetTop: 0,
					zIndex: BX.prop.getInteger(this._settings, "zIndex", 0),
					bindOptions: { forceBindPosition: true },
					titleBar: BX.prop.getString(this._settings, "title", "No title"),
					content: BX.prop.getString(this._settings, "content", ""),
					buttons: this.prepareButtons(),
					events:
						{
							onPopupShow: BX.delegate(this.onPopupShow, this),
							onPopupClose: BX.delegate(this.onPopupClose, this),
							onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
						}
				}
			);
			this._popup.show();

			window.setTimeout(function()
			{
				this._popup.setClosingByEsc(true);
			}.bind(this), 150);
		},
		close: function()
		{
			if(this._popup)
			{
				this._popup.close();
			}
		},
		isOpen: function()
		{
			return this._popup && this._popup.isShown();
		},
		prepareButtons: function()
		{
			var results = [];

			this._buttons = [];
			var data = BX.prop.getArray(this._settings, "buttons", []);
			for(var i = 0, length = data.length; i < length; i++)
			{
				var buttonData = data[i];
				buttonData["dialog"] = this;
				var button = BX.UI.EditorDialogButton.create(
					BX.prop.getString(buttonData, "id", ""),
					buttonData
				);
				this._buttons.push(button);
				results.push(button.prepareContent());
			}

			return results;
		},
		bind: function()
		{
			for(var i = 0, length = this._buttons.length; i < length; i++)
			{
				this._buttons[i].bind();
			}
		},
		unbind: function()
		{
			for(var i = 0, length = this._buttons.length; i < length; i++)
			{
				this._buttons[i].unbind();
			}
		},
		onPopupShow: function()
		{
			this.bind();
		},
		onPopupClose: function()
		{
			this.unbind();

			if(this._popup)
			{
				this._popup.destroy();
			}
		},
		onPopupDestroy: function()
		{
			if(this._popup)
			{
				this._popup = null;
			}
			delete BX.UI.EditorAuxiliaryDialog.items[this.getId()];
		}
	};
	BX.UI.EditorAuxiliaryDialog.items = {};
	BX.UI.EditorAuxiliaryDialog.isItemOpened = function(id)
	{
		return this.items.hasOwnProperty(id) && this.items[id].isOpen();
	};
	BX.UI.EditorAuxiliaryDialog.hasOpenItems = function()
	{
		for(var key in this.items)
		{
			if(!this.items.hasOwnProperty(key))
			{
				continue;
			}

			if(this.items[key].isOpen())
			{
				return true;
			}
		}
		return false;
	};
	BX.UI.EditorAuxiliaryDialog.create = function(id, settings)
	{
		var self = new BX.UI.EditorAuxiliaryDialog();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}

if(typeof(BX.UI.ConfirmationDialog) === "undefined")
{
	BX.UI.ConfirmationDialog = function()
	{
		this._id = "";
		this._settings = null;
		this._confirmListener = BX.delegate(this.onConfirm, this);
		this._cancelListener = BX.delegate(this.onCancel, this);
		this._promise = null;

		this._isOpened = false;
	};
	BX.UI.ConfirmationDialog.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : {};
			this._popup = null;
		},
		getId: function()
		{
			return this._id;
		},
		isOpened: function()
		{
			return this._isOpened;
		},
		open: function()
		{
			if(this._isOpened)
			{
				return this._promise;
			}

			this._popup = new BX.PopupWindow(
				this._id,
				null,
				{
					autoHide: false,
					draggable: true,
					bindOptions: { forceBindPosition: false },
					closeByEsc: true,
					closeIcon: { top: "10px", right: "15px" },
					zIndex: 0,
					titleBar: BX.prop.getString(this._settings, "title", "untitled"),
					content: BX.prop.getString(this._settings, "content", "-"),
					className : "crm-text-popup",
					lightShadow : true,
					events:
						{
							onPopupShow: BX.delegate(this.onPopupShow, this),
							onPopupClose: BX.delegate(this.onPopupClose, this),
							onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
						},
					buttons:
						[
							new BX.PopupWindowButton(
								{
									text : BX.message("JS_CORE_WINDOW_CONTINUE"),
									className : "popup-window-button-accept",
									events: { click: this._confirmListener }
								}
							),
							new BX.PopupWindowButtonLink(
								{
									text : BX.message("JS_CORE_WINDOW_CANCEL"),
									className : "popup-window-button-link-cancel",
									events: { click: this._cancelListener }
								}
							)
						]
				}
			);
			this._popup.show();
			return(this._promise = new BX.Promise());
		},
		close: function()
		{
			if(this._popup)
			{
				this._popup.close();
			}
		},
		onConfirm: function()
		{
			if(this._promise)
			{
				this._promise.fulfill({ cancel: false });
				this._promise = null;
			}
			this.close();
		},
		onCancel: function()
		{
			if(this._promise)
			{
				this._promise.fulfill({ cancel: true });
				this._promise = null;
			}
			this.close();
		},
		onPopupShow: function()
		{
			this._isOpened = true;
		},
		onPopupClose: function()
		{
			this._isOpened = false;
			if(this._popup)
			{
				this._popup.destroy();
			}
		},
		onPopupDestroy: function()
		{
			if(this._popup)
			{
				this._popup = null;
			}
		}
	};
	BX.UI.ConfirmationDialog.items = {};
	BX.UI.ConfirmationDialog.get = function(id)
	{
		return this.items.hasOwnProperty(id) ? this.items[id] : null;
	};
	BX.UI.ConfirmationDialog.create = function(id, settings)
	{
		var self = new BX.UI.ConfirmationDialog();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}