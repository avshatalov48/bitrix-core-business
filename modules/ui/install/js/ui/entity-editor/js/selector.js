BX.namespace("BX.UI");

if(typeof(BX.UI.SelectorMenuItem) === "undefined")
{
	BX.UI.SelectorMenuItem = function()
	{
		this._parent = null;
		this._settings = {};
		this._emitter = null;
	};
	BX.UI.SelectorMenuItem.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings;
			this._emitter = new BX.Event.EventEmitter();
			this._emitter.setEventNamespace('BX.UI.SelectorMenuItem');

			var listener = BX.prop.getFunction(BX.prop.getObject(this._settings, "events", {}), "select", null);
			if(listener)
			{
				this.addOnSelectListener(listener);
			}
		},
		getValue: function()
		{
			return BX.prop.getString(this._settings, "value", "");
		},
		getText: function()
		{
			var text = BX.prop.getString(this._settings, "text", "");
			return BX.type.isNotEmptyString(text) ? text : this.getValue();
		},
		isEnabled: function()
		{
			return BX.prop.getBoolean(this._settings, "enabled", true);
		},
		isDefault: function()
		{
			return BX.prop.getBoolean(this._settings, "default", false);
		},
		createMenuItem: function(encode)
		{
			if(BX.prop.getBoolean(this._settings, "delimiter", false))
			{
				return { delimiter: true };
			}

			encode = !!encode;
			var text = this.getText();
			if(!!encode)
			{
				text = BX.util.htmlspecialchars(text);
			}

			return(
				{
					text: text,
					onclick: function(){
						this._emitter.emit("select", { item: this });
					}.bind(this),
					className: BX.prop.getString(this._settings, "className", "")
				}
			);
		},
		addOnSelectListener: function(listener)
		{
			this._emitter.subscribe("select", listener);
		},
		removeOnSelectListener: function(listener)
		{
			this._emitter.unsubscribe("select", listener);
		}
	};
	BX.UI.SelectorMenuItem.create = function(settings)
	{
		var self = new BX.UI.SelectorMenuItem();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.UI.SelectorMenu) === "undefined")
{
	BX.UI.SelectorMenu = function()
	{
		this._id = "";
		this._settings = {};
		this._items = [];
		this._encodeItems = true;
		this._emitter = null;
		this._popup = null;
		this._isOpened = false;
		this._itemSelectHandler = BX.delegate(this.onItemSelect, this);
	};
	BX.UI.SelectorMenu.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : ("ui_selector_menu_" + Math.random().toString().substring(2));
			this._settings = settings ? settings : {};

			this._encodeItems = !!this.getSetting("encodeItems", true);
			var itemData = this.getSetting("items");
			itemData = BX.type.isArray(itemData) ? itemData : [];
			this._items = [];
			for(var i = 0; i < itemData.length; i++)
			{
				var item = BX.UI.SelectorMenuItem.create(itemData[i]);
				item.addOnSelectListener(this._itemSelectHandler);
				this._items.push(item);
			}

			this._emitter = new BX.Event.EventEmitter();
			this._emitter.setEventNamespace('BX.UI.SelectorMenu');
		},
		getId: function()
		{
			return this._id;
		},
		getSetting: function (name, defaultval)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
		},
		getItems: function()
		{
			return this._items;
		},
		setupItems: function(data)
		{
			this._items = [];
			for(var i = 0; i < data.length; i++)
			{
				var item = BX.UI.SelectorMenuItem.create(data[i]);
				item.addOnSelectListener(this._itemSelectHandler);
				this._items.push(item);
			}
		},
		isOpened: function()
		{
			return this._isOpened;
		},
		open: function(anchor)
		{
			if(this._isOpened)
			{
				return;
			}

			var menuItems = [];
			for(var i = 0; i < this._items.length; i++)
			{
				var item = this._items[i];
				if(item.isEnabled())
				{
					menuItems.push(item.createMenuItem(this._encodeItems));
				}
			}

			BX.PopupMenu.show(
				this._id,
				anchor,
				menuItems,
				{
					closeByEsc: true,
					offsetTop: 0,
					offsetLeft: 0,
					events:
						{
							onPopupShow: BX.delegate(this.onPopupShow, this),
							onPopupClose: BX.delegate(this.onPopupClose, this),
							onPopupDestroy: BX.delegate(this.onPopupDestroy, this)
						}
				}
			);
			this._popup = BX.PopupMenu.currentItem;
		},
		close: function()
		{
			if (this._popup && this._popup.popupWindow)
			{
				this._popup.popupWindow.close();
			}
		},
		addOnSelectListener: function(listener)
		{
			this._emitter.subscribe("select", listener);
		},
		removeOnSelectListener: function(listener)
		{
			this._emitter.unsubscribe("select", listener);
		},
		onItemSelect: function(event)
		{
			this.close();
			this._emitter.emit("select", { menu: this, item: event.data["item"] });
		},
		onPopupShow: function()
		{
			this._isOpened = true;
		},
		onPopupClose: function()
		{
			if(this._popup)
			{
				if(this._popup.popupWindow)
				{
					this._popup.popupWindow.destroy();
				}
			}
		},
		onPopupDestroy: function()
		{
			this._isOpened = false;
			this._popup = null;

			if(typeof(BX.PopupMenu.Data[this._id]) !== "undefined")
			{
				delete(BX.PopupMenu.Data[this._id]);
			}
		}
	};
	BX.UI.SelectorMenu.create = function(id, settings)
	{
		var self = new BX.UI.SelectorMenu();
		self.initialize(id, settings);
		return self;
	};
}