(function() {

"use strict";

BX.namespace("BX.UI");

/**
 * @interface
 * @constructor
 */
BX.UI.IButton = function()
{

};

BX.UI.IButton.prototype =
{
	render: function()
	{
		throw new Error("Must be implemented by subclass");
	}
};

/**
 *
 * @param {object} options
 * @constructor
 */
BX.UI.BaseButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	this.button = null;
	this.text = "";
	this.props = {};
	this.events = {};
	this.handleEvent = this.handleEvent.bind(this);
	this.tag = this.isEnumValue(options.tag, BX.UI.Button.Tag) ? options.tag : BX.UI.Button.Tag.BUTTON;
	this.baseClass = BX.type.isNotEmptyString(options.baseClass) ? options.baseClass : "ui-btn";

	this.setText(options.text);
	this.setProps(options.props);
	this.setDataSet(options.dataset);
	this.setClass(options.className);

	this.bindEvent("click", options.onclick);
	this.bindEvents(options.events);
};

BX.UI.BaseButton.prototype =
{
	__proto__: BX.UI.IButton.prototype,
	constructor: BX.UI.BaseButton,

	/**
	 * @public
	 * @return {Element}
	 */
	render: function()
	{
		return this.getContainer();
	},

	/**
	 * @public
	 * @param {Element} node
	 * @return {?Element}
	 */
	renderTo: function(node)
	{
		if (BX.type.isDomNode(node))
		{
			return node.appendChild(this.getContainer());
		}

		return null;
	},

	/**
	 * @public
	 * @return {Element}
	 */
	getContainer: function()
	{
		if (this.button !== null)
		{
			return this.button;
		}

		switch (this.getTag())
		{
			case BX.UI.Button.Tag.BUTTON:
			default:
				this.button = BX.create("button", {
					props: {
						className: this.getBaseClass()
					}
				});
				break;

			case BX.UI.Button.Tag.INPUT:
				this.button = BX.create("input", {
					props: {
						className: this.getBaseClass()
					},
					attrs: {
						type: "button"
					}
				});
				break;

			case BX.UI.Button.Tag.LINK:
				this.button = BX.create("a", {
					props: {
						className: this.getBaseClass(),
						href: ""
					}
				});
				break;

			case BX.UI.Button.Tag.SUBMIT:
				this.button = BX.create("input", {
					props: {
						className: this.getBaseClass()
					},
					attrs: {
						type: "submit"
					}
				});
				break;
		}

		return this.button;
	},

	/**
	 * @protected
	 * @return {string}
	 */
	getBaseClass: function()
	{
		return this.baseClass;
	},

	/**
	 * @public
	 * @param {string} text
	 * @return {BX.UI.BaseButton}
	 */
	setText: function(text)
	{
		if (BX.type.isString(text))
		{
			this.text = text;

			if (this.isInputType())
			{
				this.getContainer().value = text;
			}
			else
			{
				this.getContainer().textContent = text;
			}
		}

		return this;
	},

	/**
	 * @public
	 * @return {string}
	 */
	getText: function()
	{
		return this.text;
	},

	/**
	 * @public
	 * @return {BX.UI.Button.Tag}
	 */
	getTag: function()
	{
		return this.tag;
	},

	/**
	 * @public
	 * @param {object.<string, string>} props
	 * @return {BX.UI.BaseButton}
	 */
	setProps: function(props)
	{
		if (!BX.type.isPlainObject(props))
		{
			return this;
		}

		for (var propName in props)
		{
			var propValue = props[propName];
			if (propValue === null)
			{
				this.getContainer().removeAttribute(propName);
				delete this.props[propName];
			}
			else
			{
				this.getContainer().setAttribute(propName, propValue);
				this.props[propName] = propValue;
			}
		}

		return this;
	},

	/**
	 * @public
	 * @return {object.<string, string>}
	 */
	getProps: function()
	{
		return this.props;
	},

	/**
	 * @public
	 * @param {object.<string, string>} props
	 * @return {BX.UI.BaseButton}
	 */
	setDataSet: function(props)
	{
		if (!BX.type.isPlainObject(props))
		{
			return this;
		}

		for (var propName in props)
		{
			var propValue = props[propName];
			if (propValue === null)
			{
				delete this.getDataSet()[propName];
			}
			else
			{
				this.getDataSet()[propName] = propValue;
			}
		}

		return this;
	},

	/**
	 * @public
	 * @return {DOMStringMap}
	 */
	getDataSet: function()
	{
		return this.getContainer().dataset;
	},

	/**
	 * @public
	 * @param {string} className
	 * @return {BX.UI.BaseButton}
	 */
	setClass: function(className)
	{
		if (BX.type.isNotEmptyString(className))
		{
			BX.addClass(this.getContainer(), className);
		}

		return this;
	},

	/**
	 * @public
	 * @param {string} className
	 * @return {BX.UI.BaseButton}
	 */
	removeClass: function(className)
	{
		if (BX.type.isNotEmptyString(className))
		{
			BX.removeClass(this.getContainer(), className);
		}

		return this;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.BaseButton}
	 */
	setDisabled: function(flag)
	{
		if (flag === false)
		{
			this.setProps({ disabled: null });
		}
		else
		{
			this.setProps({ disabled: true });
		}

		return this;
	},

	/**
	 *
	 * @return {boolean}
	 */
	isDisabled: function()
	{
		return this.getProps().disabled === true;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isInputType: function()
	{
		return this.getTag() === BX.UI.Button.Tag.SUBMIT || this.getTag() === BX.UI.Button.Tag.INPUT;
	},

	/**
	 * @public
	 * @param {object.<string, function>} events
	 * @return {BX.UI.BaseButton}
	 */
	bindEvents: function(events)
	{
		if (BX.type.isPlainObject(events))
		{
			for (var eventName in events)
			{
				var fn = events[eventName];
				this.bindEvent(eventName, fn);
			}
		}

		return this;
	},

	/**
	 * @public
	 * @param {string[]} events
	 * @return {BX.UI.BaseButton}
	 */
	unbindEvents: function(events)
	{
		if (BX.type.isArray(events))
		{
			events.forEach(function(eventName) {
				this.unbindEvent(eventName);
			}, this);
		}

		return this;
	},

	/**
	 * @public
	 * @param {string} eventName
	 * @param {function} fn
	 * @return {BX.UI.BaseButton}
	 */
	bindEvent: function(eventName, fn)
	{
		if (BX.type.isNotEmptyString(eventName) && BX.type.isFunction(fn))
		{
			this.unbindEvent(eventName);
			this.events[eventName] = fn;
			this.getContainer().addEventListener(eventName, this.handleEvent);
		}

		return this;
	},

	/**
	 * @public
	 * @param {string} eventName
	 * @return {BX.UI.BaseButton}
	 */
	unbindEvent: function(eventName)
	{
		if (this.events[eventName])
		{
			delete this.events[eventName];
			this.getContainer().removeEventListener(eventName, this.handleEvent);
		}

		return this;
	},

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handleEvent: function(event)
	{
		var eventName = event.type;
		if (this.events[eventName])
		{
			var fn = this.events[eventName];
			fn.call(this, this, event);
		}
	},

	/**
	 * @protected
	 */
	isEnumValue: function(value, enumeration)
	{
		for (var code in enumeration)
		{
			if (enumeration[code] === value)
			{
				return true;
			}
		}

		return false;
	}
};

/**
 *
 * @param {object} [options]
 * @extends BX.UI.BaseButton
 * @constructor
 */
BX.UI.Button = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.BaseButton.call(this, options);

	this.size = null;
	this.color = null;
	this.icon = null;
	this.state = null;
	this.id = null;
	this.context = null;
	
	this.menuWindow = null;
	this.handleMenuClick = this.handleMenuClick.bind(this);
	this.handleMenuClose = this.handleMenuClose.bind(this);

	this.setSize(options.size);
	this.setColor(options.color);
	this.setIcon(options.icon);
	this.setState(options.state);
	this.setId(options.id);
	this.setMenu(options.menu);
	this.setContext(options.context);

	options.noCaps && this.setNoCaps();
	options.round && this.setRound();

	if (options.dropdown || (this.getMenuWindow() && options.dropdown !== false))
	{
		this.setDropdown();
	}
};

/**
 * @readonly
 * @enum {string}
 */
BX.UI.Button.Size = {
	LARGE: "ui-btn-lg",
	MEDIUM: "ui-btn-md",
	SMALL: "ui-btn-sm",
	EXTRA_SMALL: "ui-btn-xs"
};

/**
 * @readonly
 * @enum {string}
 */
BX.UI.Button.Color = {
	DANGER: "ui-btn-danger",
	DANGER_DARK: "ui-btn-danger-dark",
	DANGER_LIGHT: "ui-btn-danger-light",
	SUCCESS: "ui-btn-success",
	SUCCESS_LIGHT: "ui-btn-success-light",
	PRIMARY_DARK: "ui-btn-primary-dark",
	PRIMARY: "ui-btn-primary",
	SECONDARY: "ui-btn-secondary",
	LINK: "ui-btn-link",
	LIGHT: "ui-btn-light",
	LIGHT_BORDER: "ui-btn-light-border"
};

/**
 * @readonly
 * @enum {string}
 */
BX.UI.Button.State = {
	HOVER: "ui-btn-hover",
	ACTIVE: "ui-btn-active",
	DISABLED: "ui-btn-disabled",
	CLOCKING: "ui-btn-clock",
	WAITING: "ui-btn-wait"
};

/**
 * @readonly
 * @enum {string}
 */
BX.UI.Button.Icon = {
	UNFOLLOW: "ui-btn-icon-unfollow",
	FOLLOW: "ui-btn-icon-follow",
	ADD: "ui-btn-icon-add",
	STOP: "ui-btn-icon-stop",
	START: "ui-btn-icon-start",
	ADD_FOLDER: "ui-btn-icon-add-folder",
	PAUSE: "ui-btn-icon-pause",
	SETTING: "ui-btn-icon-setting",
	TASK: "ui-btn-icon-task",
	INFO: "ui-btn-icon-info",
	SEARCH: "ui-btn-icon-search",
	PRINT: "ui-btn-icon-print",
	LIST: "ui-btn-icon-list",
	BUSINESS: "ui-btn-icon-business",
	BUSINESS_CONFIRM: "ui-btn-icon-business-confirm",
	BUSINESS_WARNING: "ui-btn-icon-business-warning",
	CAMERA: "ui-btn-icon-camera",
	PHONE_UP: "ui-btn-icon-phone-up",
	PHONE_DOWN: "ui-btn-icon-phone-down",
	BACK: "ui-btn-icon-back"
};

/**
 * @readonly
 * @enum {string}
 */
BX.UI.Button.Tag = {
	BUTTON: 0,
	LINK: 1,
	SUBMIT: 2,
	INPUT: 3
};

/**
 * @readonly
 * @enum {string}
 */
BX.UI.Button.Style = {
	NO_CAPS: "ui-btn-no-caps",
	ROUND: "ui-btn-round",
	DROPDOWN: "ui-btn-dropdown",
};

BX.UI.Button.prototype =
{
	__proto__: BX.UI.BaseButton.prototype,
	constructor: BX.UI.Button,

	/**
	 * @public
	 * @param {BX.UI.Button.Size|null} size
	 * @return {BX.UI.Button}
	 */
	setSize: function(size)
	{
		return this.setProperty("size", size, BX.UI.Button.Size);
	},

	/**
	 * @public
	 * @return {?BX.UI.Button.Size}
	 */
	getSize: function()
	{
		return this.size;
	},

	/**
	 * @public
	 * @param {BX.UI.Button.Color|null} color
	 * @return {BX.UI.Button}
	 */
	setColor: function(color)
	{
		return this.setProperty("color", color, BX.UI.Button.Color);
	},

	/**
	 * @public
	 * @return {?BX.UI.Button.Size}
	 */
	getColor: function()
	{
		return this.color;
	},

	/**
	 * @public
	 * @param {BX.UI.Button.Icon} icon
	 * @return {BX.UI.Button}
	 */
	setIcon: function(icon)
	{
		this.setProperty("icon", icon, BX.UI.Button.Icon);

		if (this.isInputType() && this.getIcon() !== null)
		{
			throw "Input type button cannot have an icon.";
		}

		return this;
	},

	/**
	 * @public
	 * @return {?BX.UI.Button.Icon}
	 */
	getIcon: function()
	{
		return this.icon;
	},

	/**
	 * @public
	 * @param {BX.UI.Button.State|null} state
	 * @return {BX.UI.Button}
	 */
	setState: function(state)
	{
		return this.setProperty("state", state, BX.UI.Button.State);
	},

	/**
	 * @public
	 * @return {BX.UI.Button.State}
	 */
	getState: function()
	{
		return this.state;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setNoCaps: function(flag)
	{
		if (flag === false)
		{
			BX.removeClass(this.getContainer(), BX.UI.Button.Style.NO_CAPS);
		}
		else
		{
			BX.addClass(this.getContainer(), BX.UI.Button.Style.NO_CAPS);
		}

		return this;
	},

	/**
	 *
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setRound: function(flag)
	{
		if (flag === false)
		{
			BX.removeClass(this.getContainer(), BX.UI.Button.Style.ROUND);
		}
		else
		{
			BX.addClass(this.getContainer(), BX.UI.Button.Style.ROUND);
		}

		return this;
	},

	/**
	 *
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setDropdown: function(flag)
	{
		if (flag === false)
		{
			BX.removeClass(this.getContainer(), BX.UI.Button.Style.DROPDOWN);
		}
		else
		{
			BX.addClass(this.getContainer(), BX.UI.Button.Style.DROPDOWN);
		}

		return this;
	},

	/**
	 * @protected
	 * @param {object|false} options
	 */
	setMenu: function(options)
	{
		if (BX.type.isPlainObject(options) && BX.type.isArray(options.items) &&  options.items.length > 0)
		{
			this.setMenu(false);

			this.menuWindow = new BX.PopupMenuWindow(
				"ui-btn-menu-" + BX.util.getRandomString().toLowerCase(),
				this.getMenuBindElement(),
				options.items,
				options
			);

			BX.addCustomEvent(this.menuWindow.getPopupWindow(), "onPopupClose", this.handleMenuClose);
			this.getMenuClickElement().addEventListener("click", this.handleMenuClick);
		}
		else if (options === false && this.menuWindow !== null)
		{
			this.menuWindow.close();

			BX.removeCustomEvent(this.menuWindow.getPopupWindow(), "onPopupClose", this.handleMenuClose);
			this.getMenuClickElement().removeEventListener("click", this.handleMenuClick);

			this.menuWindow.destroy();
			this.menuWindow = null;
		}
	},

	/**
	 * @public
	 * @return {Element}
	 */
	getMenuBindElement: function()
	{
		return this.getContainer();
	},

	/**
	 * @public
	 * @return {Element}
	 */
	getMenuClickElement: function()
	{
		return this.getContainer();
	},

	/**
	 * @protected
	 * @param event
	 */
	handleMenuClick: function(event)
	{
		this.getMenuWindow().show();
		this.setActive(this.getMenuWindow().getPopupWindow().isShown());
	},

	/**
	 * @protected
	 */
	handleMenuClose: function()
	{
		this.setActive(false);
	},

	/**
	 * @public
	 * @return {BX.PopupMenuWindow}
	 */
	getMenuWindow: function()
	{
		return this.menuWindow;
	},

	/**
	 * @public
	 * @param {string|null} id
	 * @return {BX.UI.Button}
	 */
	setId: function(id)
	{
		if (BX.type.isNotEmptyString(id) || id === null)
		{
			this.id = id;
		}

		return this;
	},

	/**
	 * @public
	 * @return {?string}
	 */
	getId: function()
	{
		return this.id;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 */
	setActive: function(flag)
	{
		return this.setState(flag === false ? null : BX.UI.Button.State.ACTIVE);
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isActive: function()
	{
		return this.getState() === BX.UI.Button.State.ACTIVE;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 */
	setHovered: function(flag)
	{
		return this.setState(flag === false ? null : BX.UI.Button.State.HOVER);
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isHover: function()
	{
		return this.getState() === BX.UI.Button.State.HOVER;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setDisabled: function(flag)
	{
		this.setState(flag === false ? null : BX.UI.Button.State.DISABLED);
		BX.UI.BaseButton.prototype.setDisabled.call(this, flag);

		return this;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isDisabled: function()
	{
		return this.getState() === BX.UI.Button.State.DISABLED;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setWaiting: function(flag)
	{
		if (flag === false)
		{
			this.setState(null);
			this.setProps({ disabled: null });
		}
		else
		{
			this.setState(BX.UI.Button.State.WAITING);
			this.setProps({ disabled: true });
		}

		return this;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isWaiting: function()
	{
		return this.getState() === BX.UI.Button.State.WAITING;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 */
	setClocking: function(flag)
	{
		if (flag === false)
		{
			this.setState(null);
			this.setProps({ disabled: null });
		}
		else
		{
			this.setState(BX.UI.Button.State.CLOCKING);
			this.setProps({ disabled: true });
		}

		return this;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isClocking: function()
	{
		return this.getState() === BX.UI.Button.State.CLOCKING;
	},

	/**
	 * @protected
	 */
	setProperty: function(property, value, enumaration)
	{
		if (this.isEnumValue(value, enumaration))
		{
			BX.removeClass(this.getContainer(), this[property]);
			BX.addClass(this.getContainer(), value);
			this[property] = value;
		}
		else if (value === null)
		{
			BX.removeClass(this.getContainer(), this[property]);
			this[property] = null;
		}

		return this;
	},

	/**
	 * @public
	 * @param {*} context
	 */
	setContext: function(context)
	{
		if (context !== undefined)
		{
			this.context = context;
		}
	},

	/**
	 *
	 * @return {*}
	 */
	getContext: function()
	{
		return this.context;
	}
};

/**
 *
 * @param options
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.SaveButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.Button.call(this, options);

	this.setText(BX.message("UI_BUTTONS_SAVE_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.SaveButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.SaveButton
};

/**
 *
 * @param options
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.CreateButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.Button.call(this, options);

	this.setText(BX.message("UI_BUTTONS_CREATE_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.CreateButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.CreateButton
};

/**
 *
 * @param options
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.AddButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.Button.call(this, options);

	this.setText(BX.message("UI_BUTTONS_ADD_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.AddButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.AddButton
};

/**
 *
 * @param options
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.SendButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.Button.call(this, options);

	this.setText(BX.message("UI_BUTTONS_SEND_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.SendButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.SendButton
};

/**
 *
 * @param options
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.ApplyButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.Button.call(this, options);

	this.setText(BX.message("UI_BUTTONS_APPLY_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.LIGHT_BORDER);
	this.setColor(options.color);
};

BX.UI.ApplyButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.ApplyButton
};

/**
 *
 * @param options
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.CancelButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.Button.call(this, options);

	this.setText(BX.message("UI_BUTTONS_CANCEL_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.LINK);
	this.setColor(options.color);
};

BX.UI.CancelButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.CancelButton
};

/**
 *
 * @param options
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.CloseButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.Button.call(this, options);

	this.setText(BX.message("UI_BUTTONS_CLOSE_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.LINK);
	this.setColor(options.color);
};

BX.UI.CloseButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.CloseButton
};

/**
 *
 * @param {object} [options]
 * @extends {BX.UI.Button}
 * @constructor
 */
BX.UI.SplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};
	delete options.tag;
	delete options.round;

	var mainOptions = BX.type.isPlainObject(options.mainButton) ? options.mainButton : {};
	var menuOptions = BX.type.isPlainObject(options.menuButton) ? options.menuButton : {};
	mainOptions.buttonType = BX.UI.SplitSubButton.Type.MAIN;
	menuOptions.buttonType = BX.UI.SplitSubButton.Type.MENU;

	this.mainButton = new BX.UI.SplitSubButton(mainOptions);
	this.menuButton = new BX.UI.SplitSubButton(menuOptions);
	this.mainButton.setSplitButton(this);
	this.menuButton.setSplitButton(this);

	this.menuTarget = BX.UI.SplitSubButton.Type.MAIN;
	if (options.menuTarget === BX.UI.SplitSubButton.Type.MENU)
	{
		this.menuTarget = BX.UI.SplitSubButton.Type.MENU;
	}

	BX.UI.Button.call(this, options);
};

BX.UI.SplitButton.State = {
	HOVER: "ui-btn-hover",
	MAIN_HOVER: "ui-btn-main-hover",
	MENU_HOVER: "ui-btn-menu-hover",
	ACTIVE: "ui-btn-active",
	MAIN_ACTIVE: "ui-btn-main-active",
	MENU_ACTIVE: "ui-btn-menu-active",
	DISABLED: "ui-btn-disabled",
	MAIN_DISABLED: "ui-btn-main-disabled",
	MENU_DISABLED: "ui-btn-menu-disabled",
	CLOCKING: "ui-btn-clock",
	WAITING: "ui-btn-wait",
};

BX.UI.SplitButton.prototype =
{
	__proto__: BX.UI.Button.prototype,
	constructor: BX.UI.SplitButton,

	/**
	 * @public
	 * @return {Element}
	 */
	getContainer: function()
	{
		if (this.button === null)
		{
			this.button = BX.create("div", {
				props: {
					className: "ui-btn-split"
				},
				children: [
					this.getMainButton().getContainer(),
					this.getMenuButton().getContainer()
				]
			});
		}

		return this.button;
	},

	/**
	 * @public
	 * @return {BX.UI.SplitSubButton}
	 */
	getMainButton: function()
	{
		return this.mainButton;
	},

	/**
	 * @public
	 * @return {BX.UI.SplitSubButton}
	 */
	getMenuButton: function()
	{
		return this.menuButton;
	},

	/**
	 * @public
	 * @param {string} text
	 * @return {BX.UI.SplitButton}
	 */
	setText: function(text)
	{
		if (BX.type.isString(text))
		{
			this.getMainButton().setText(text);
		}

		return this;
	},

	/**
	 * @public
	 * @param {BX.UI.SplitButton.State|null} state
	 * @return {BX.UI.Button}
	 */
	setState: function(state)
	{
		return this.setProperty("state", state, BX.UI.SplitButton.State);
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setDisabled: function(flag)
	{
		this.setState(flag === false ? null : BX.UI.Button.State.DISABLED);
		this.getMainButton().setDisabled(flag);
		this.getMenuButton().setDisabled(flag);

		return this;
	},

	/**
	 * @protected
	 * @return {Element}
	 */
	getMenuBindElement: function()
	{
		if (this.getMenuTarget() === BX.UI.SplitSubButton.Type.MENU)
		{
			return this.getMenuButton().getContainer();
		}
		else
		{
			return this.getContainer();
		}
	},

	/**
	 * @protected
	 * @param event
	 */
	handleMenuClick: function(event)
	{
		this.getMenuWindow().show();

		var isActive = this.getMenuWindow().getPopupWindow().isShown();
		this.getMenuButton().setActive(isActive);
	},

	/**
	 * @protected
	 * @param event
	 */
	handleMenuClose: function(event)
	{
		this.getMenuButton().setActive(false);
	},

	/**
	 * @protected
	 * @return {Element}
	 */
	getMenuClickElement: function()
	{
		return this.getMenuButton().getContainer();
	},

	/**
	 * @public
	 * @return {BX.UI.SplitSubButton.Type}
	 */
	getMenuTarget: function()
	{
		return this.menuTarget;
	},

	/**
	 *
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setRound: function(flag)
	{
		throw new Error("BX.UI.SplitButton can't be round.");
	}
};

/**
 *
 * @param {object} options
 * @extends {BX.UI.BaseButton}
 * @constructor
 */
BX.UI.SplitSubButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	this.splitButton = null;
	this.buttonType =
		options.buttonType === BX.UI.SplitSubButton.Type.MAIN
			? BX.UI.SplitSubButton.Type.MAIN
			: BX.UI.SplitSubButton.Type.MENU
	;

	options.baseClass = this.buttonType;
	BX.UI.BaseButton.call(this, options);

	if (this.isInputType())
	{
		throw "Split button cannot be an input tag.";
	}
};

BX.UI.SplitSubButton.Type = {
	MAIN: "ui-btn-main",
	MENU: "ui-btn-menu"
};

BX.UI.SplitSubButton.prototype =
{
	__proto__: BX.UI.BaseButton.prototype,
	constructor: BX.UI.SplitSubButton,

	/**
	 * @param {BX.UI.SplitButton} button
	 */
	setSplitButton: function(button)
	{
		this.splitButton = button;
	},

	/**
	 * @public
	 * @return {BX.UI.SplitButton}
	 */
	getSplitButton: function()
	{
		return this.splitButton;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isMainButton: function()
	{
		return this.buttonType === BX.UI.SplitSubButton.Type.MAIN;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isMenuButton: function()
	{
		return this.buttonType === BX.UI.SplitSubButton.Type.MENU;
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 */
	setActive: function(flag)
	{
		this.toggleState(
			flag,
			BX.UI.SplitButton.State.ACTIVE,
			BX.UI.SplitButton.State.MAIN_ACTIVE,
			BX.UI.SplitButton.State.MENU_ACTIVE
		);

		return this;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isActive: function()
	{
		var state = this.getSplitButton().getState();
		if (state === BX.UI.SplitButton.State.ACTIVE)
		{
			return true;
		}

		if (this.isMainButton())
		{
			return state === BX.UI.SplitButton.State.MAIN_ACTIVE;
		}
		else
		{
			return state === BX.UI.SplitButton.State.MENU_ACTIVE;
		}
	},

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {BX.UI.Button}
	 */
	setDisabled: function(flag)
	{
		this.toggleState(
			flag,
			BX.UI.SplitButton.State.DISABLED,
			BX.UI.SplitButton.State.MAIN_DISABLED,
			BX.UI.SplitButton.State.MENU_DISABLED
		);

		BX.UI.BaseButton.prototype.setDisabled.call(this, flag);

		return this;
	},

	/**
	 * @public
	 * @param {boolean} flag
	 * @return {BX.UI.SplitSubButton}
	 */
	setHovered: function(flag)
	{
		this.toggleState(
			flag,
			BX.UI.SplitButton.State.HOVER,
			BX.UI.SplitButton.State.MAIN_HOVER,
			BX.UI.SplitButton.State.MENU_HOVER
		);

		return this;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isHovered: function()
	{
		var state = this.getSplitButton().getState();
		if (state === BX.UI.SplitButton.State.HOVER)
		{
			return true;
		}

		if (this.isMainButton())
		{
			return state === BX.UI.SplitButton.State.MAIN_HOVER;
		}
		else
		{
			return state === BX.UI.SplitButton.State.MENU_HOVER;
		}
	},

	/**
	 * @private
	 * @param flag
	 * @param globalState
	 * @param mainState
	 * @param menuState
	 */
	toggleState: function(flag, globalState, mainState, menuState)
	{
		var state = this.getSplitButton().getState();
		if (flag === false)
		{
			if (state === globalState)
			{
				this.getSplitButton().setState(this.isMainButton() ? menuState : mainState);
			}
			else
			{
				this.getSplitButton().setState(null);
			}
		}
		else
		{
			if (state === mainState && this.isMenuButton())
			{
				this.getSplitButton().setState(globalState);
			}
			else if (state === menuState && this.isMainButton())
			{
				this.getSplitButton().setState(globalState);
			}
			else if (state !== globalState)
			{
				this.getSplitButton().setState(this.isMainButton() ? mainState : menuState);
			}
		}
	},
};

/**
*
* @param options
* @extends {BX.UI.SplitButton}
* @constructor
*/
BX.UI.SaveSplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.SplitButton.call(this, options);

	this.setText(BX.message("UI_BUTTONS_SAVE_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.SaveSplitButton.prototype =
{
	__proto__: BX.UI.SplitButton.prototype,
	constructor: BX.UI.SaveSplitButton
};

/**
*
* @param options
* @extends {BX.UI.SplitButton}
* @constructor
*/
BX.UI.CreateSplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.SplitButton.call(this, options);

	this.setText(BX.message("UI_BUTTONS_CREATE_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.CreateSplitButton.prototype =
{
	__proto__: BX.UI.SplitButton.prototype,
	constructor: BX.UI.CreateSplitButton
};

/**
 *
 * @param options
 * @extends {BX.UI.SplitButton}
 * @constructor
 */
BX.UI.AddSplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.SplitButton.call(this, options);

	this.setText(BX.message("UI_BUTTONS_ADD_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.AddSplitButton.prototype =
{
	__proto__: BX.UI.SplitButton.prototype,
	constructor: BX.UI.AddSplitButton
};

/**
 *
 * @param options
 * @extends {BX.UI.SplitButton}
 * @constructor
 */
BX.UI.SendSplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.SplitButton.call(this, options);

	this.setText(BX.message("UI_BUTTONS_SEND_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.SUCCESS);
	this.setColor(options.color);
};

BX.UI.SendSplitButton.prototype =
{
	__proto__: BX.UI.SplitButton.prototype,
	constructor: BX.UI.SendSplitButton
};

/**
 *
 * @param options
 * @extends {BX.UI.SplitButton}
 * @constructor
 */
BX.UI.ApplySplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.SplitButton.call(this, options);

	this.setText(BX.message("UI_BUTTONS_APPLY_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.LIGHT_BORDER);
	this.setColor(options.color);
};

BX.UI.ApplySplitButton.prototype =
{
	__proto__: BX.UI.SplitButton.prototype,
	constructor: BX.UI.ApplySplitButton
};

/**
 *
 * @param options
 * @extends {BX.UI.SplitButton}
 * @constructor
 */
BX.UI.CancelSplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.SplitButton.call(this, options);

	this.setText(BX.message("UI_BUTTONS_CANCEL_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.LINK);
	this.setColor(options.color);
};

BX.UI.CancelSplitButton.prototype =
{
	__proto__: BX.UI.SplitButton.prototype,
	constructor: BX.UI.CancelSplitButton
};

/**
 *
 * @param options
 * @extends {BX.UI.SplitButton}
 * @constructor
 */
BX.UI.CloseSplitButton = function(options)
{
	options = BX.type.isPlainObject(options) ? options : {};

	BX.UI.SplitButton.call(this, options);

	this.setText(BX.message("UI_BUTTONS_CLOSE_BTN_TEXT"));
	this.setText(options.text);

	this.setColor(BX.UI.Button.Color.LINK);
	this.setColor(options.color);
};

BX.UI.CloseSplitButton.prototype =
{
	__proto__: BX.UI.SplitButton.prototype,
	constructor: BX.UI.CloseSplitButton
};

})();