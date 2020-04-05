(function() {

"use strict";

BX.namespace("BX.UI.Notification");

/**
 *
 * @enum {number}
 */
var State = {
	INIT: 0,
	OPENING: 1,
	OPEN: 2,
	CLOSING: 3,
	CLOSED: 4,
	PAUSED: 5,
	QUEUED: 6
};

BX.UI.Notification.State = State;

/**
 * @typedef {object} BX.UI.Notification.BalloonOptions
 * @property {BX.UI.Notification.Stack} stack
 * @property {string|Element} [content]
 * @property {boolean} [autoHide=true]
 * @property {number} [autoHideDelay=8000]
 * @property {number} [zIndex=3200]
 * @property {boolean} [closeButton=true]
 * @property {string} [category]
 * @property {string} [id]
 * @property {BX.UI.Notification.Action[]} [actions]
 * @property {function} [render]
 * @property {number} [width=400]
 * @property {object} [data]
 * @property {?object.<string, function>} [events]
 */

/**
 *
 * @param {BX.UI.Notification.BalloonOptions} options
 * @constructor
 */
BX.UI.Notification.Balloon = function(options)
{
	options = BX.type.isPlainObject(options) ? options : Object.create(null);

	if (!(options.stack instanceof BX.UI.Notification.Stack))
	{
		throw new Error("BX.UI.Notification.Balloon: 'stack' parameter is required.");
	}

	this.id = BX.type.isNotEmptyString(options.id) ? options.id : BX.util.getRandomString(8).toLowerCase();
	this.stack = options.stack;
	this.state = State.INIT;

	this.container = null;
	this.content = null;
	this.actions = [];
	this.animationClassName = "ui-notification-balloon-animate";
	this.customRender = null;
	this.category = null;

	this.autoHide = true;
	this.autoHideDelay = 8000;
	this.autoHideTimeout = null;

	this.data = {};
	this.zIndex = 3200;
	this.width = 400;

	this.closeButton = null;
	this.closeButtonVisibility = true;

	if (BX.type.isPlainObject(options.events))
	{
		for (var eventName in options.events)
		{
			this.addEvent(eventName, options.events[eventName]);
		}
	}

	this.setOptions(options);
};

BX.UI.Notification.Balloon.prototype =
{
	/**
	 * @public
	 */
	show: function()
	{
		if (this.getState() === State.OPENING)
		{
			return;
		}

		if (this.getState() === State.OPEN)
		{
			this.activateAutoHide();
			return;
		}

		var firstLaunch = false;
		if (!this.getContainer().parentNode)
		{
			firstLaunch = true;
			document.body.appendChild(this.getContainer());
			this.getStack().add(this);
			if (this.getState() === State.QUEUED)
			{
				return;
			}
		}

		var paused = this.getState() === State.PAUSED;
		this.setState(State.OPENING);
		this.adjustPosition();

		this.animateIn(function() {

			this.setState(State.OPEN);

			if (firstLaunch)
			{
				this.fireEvent("onOpen");
			}

			if (!paused)
			{
				this.activateAutoHide();
			}

		}.bind(this));

	},

	/**
	 *
	 * @param {BX.UI.Notification.BalloonOptions} options
	 */
	setOptions: function(options)
	{
		if (!BX.type.isPlainObject(options))
		{
			return;
		}

		this.setContent(options.content);
		this.setWidth(options.width);
		this.setZIndex(options.zIndex);
		this.setData(options.data);
		this.setCloseButtonVisibility(options.closeButton);
		this.setActions(options.actions);
		this.setCategory(options.category);
		this.setAutoHide(options.autoHide);
		this.setCustomRender(options.render);
		this.setAutoHideDelay(options.autoHideDelay);
	},

	/**
	 *
	 * @param {BX.UI.Notification.BalloonOptions} options
	 */
	update: function(options)
	{
		this.setOptions(options);

		BX.cleanNode(this.getContainer());

		this.getContainer().style.zIndex = this.getZIndex();
		this.getContainer().appendChild(this.render());

		this.deactivateAutoHide();
		this.activateAutoHide();
	},

	/**
	 * @public
	 */
	close: function()
	{
		if (this.getState() === State.CLOSING || this.getState() === State.CLOSED)
		{
			return;
		}

		this.setState(State.CLOSING);
		this.deactivateAutoHide();

		this.animateOut(function() {

			if (this.getState() !== State.CLOSING)
			{
				return;
			}

			this.setState(State.CLOSED);

			BX.remove(this.getContainer());
			this.container = null;

			this.fireEvent("onClose");

		}.bind(this));
	},

	/**
	 * @private
	 */
	blink: function()
	{
		var self = this;
		this.animateOut(function() {
			setTimeout(function() {
				self.animateIn(function() {});
			}, 200);
		});
	},

	/**
	 * @public
	 */
	adjustPosition: function()
	{
		if (this.getStack().isNewestOnTop())
		{
			this.getStack().adjustPosition();
		}
		else
		{
			this.getStack().adjustPosition(this);
		}
	},

	/**
	 * @public
	 * @return {string}
	 */
	getId: function()
	{
		return this.id;
	},

	/**
	 * @package
	 * @return {Element}
	 */
	getCloseButton: function()
	{
		if (this.closeButton !== null)
		{
			return this.closeButton;
		}

		this.closeButton = BX.create("div", {
			props: {
				className: "ui-notification-balloon-close-btn"
			},
			events: {
				click: this.handleCloseBtnClick.bind(this)
			}
		});

		return this.closeButton;
	},

	/**
	 *
	 * @param {boolean} visibility
	 */
	setCloseButtonVisibility: function(visibility)
	{
		this.closeButtonVisibility = visibility !== false;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	isCloseButtonVisible: function()
	{
		return this.closeButtonVisibility;
	},

	/**
	 * @public
	 * @return {Element|string}
	 */
	getContent: function()
	{
		return this.content;
	},

	/**
	 * @public
	 * @param {Element|string} content
	 */
	setContent: function(content)
	{
		if (BX.type.isString(content) || BX.type.isDomNode(content))
		{
			this.content = content;
		}
	},

	/**
	 * @public
	 * @return {number|"auto"}
	 */
	getWidth: function()
	{
		return this.width;
	},

	/**
	 * @public
	 * @param {number|"auto"} width
	 */
	setWidth: function(width)
	{
		if (BX.type.isNumber(width) || width === "auto")
		{
			this.width = width;
		}
	},

	/**
	 * @public
	 * @return {number}
	 */
	getZIndex: function()
	{
		return this.zIndex;
	},

	/**
	 * @public
	 * @param {number} zIndex
	 */
	setZIndex: function(zIndex)
	{
		if (BX.type.isNumber(zIndex))
		{
			this.zIndex = zIndex;
		}
	},

	/**
	 * @package
	 * @return {number}
	 */
	getHeight: function()
	{
		return this.getContainer().offsetHeight;
	},

	/**
	 * @public
	 * @return {string|null}
	 */
	getCategory: function()
	{
		return this.category;
	},

	/**
	 * @public
	 * @param {string|null} category
	 */
	setCategory: function(category)
	{
		if (BX.type.isNotEmptyString(category) || category === null)
		{
			this.category = category;
		}
	},

	/**
	 * @public
	 * @param {BX.UI.Notification.Action[]|null} actions
	 */
	setActions: function(actions)
	{
		if (BX.type.isArray(actions))
		{
			this.actions = [];
			actions.forEach(function(action) {
				this.actions.push(new BX.UI.Notification.Action(this, action));
			}, this);
		}
		else if (actions === null)
		{
			this.actions = [];
		}
	},

	/**
	 * @public
	 * @return {BX.UI.Notification.Action[]}
	 */
	getActions: function()
	{
		return this.actions;
	},

	/**
	 * @public
	 * @param {string} id
	 * @return {BX.UI.Notification.Action}
	 */
	getAction: function(id)
	{
		for (var i = 0; i < this.actions.length; i++)
		{
			var action = this.actions[i];
			if (action.getId() === id)
			{
				return action;
			}
		}

		return null;
	},

	/**
	 * @public
	 * @return {Element}
	 */
	getContainer: function()
	{
		if (this.container !== null)
		{
			return this.container;
		}

		this.container = BX.create("div", {
			props: {
				className: "ui-notification-balloon"
			},
			style: {
				zIndex: this.getZIndex()
			},
			children: [
				this.render()
			],
			events: {
				mouseenter: this.handleMouseEnter.bind(this),
				mouseleave: this.handleMouseLeave.bind(this)
			}
		});

		return this.container;
	},

	/**
	 * @protected
	 * @return {Element}
	 */
	render: function()
	{
		if (this.getCustomRender() !== null)
		{
			return this.getCustomRender().apply(this, [this]);
		}

		var actions = this.getActions().map(function(action) {
			return action.getContainer();
		});

		var content = this.getContent();
		var width = this.getWidth();

		return BX.create("div", {
			props: {
				className: "ui-notification-balloon-content"
			},
			style: {
				width: BX.type.isNumber(width) ? (width + "px") : width
			},
			children: [
				BX.create("div", {
					props: {
						className: "ui-notification-balloon-message"
					},
					html: BX.type.isDomNode(content) ? null : content,
					children: BX.type.isDomNode(content) ? [content] : []
				}),
				BX.create("div", {
					props: {
						className: "ui-notification-balloon-actions"
					},
					children: actions
				}),
				this.isCloseButtonVisible() ?  this.getCloseButton(): null
			]
		});
	},

	/**
	 * @public
	 * @param {function} render
	 */
	setCustomRender: function(render)
	{
		if (BX.type.isFunction(render))
		{
			this.customRender = render;
		}
	},

	/**
	 * @public
	 * @return {Function}
	 */
	getCustomRender: function()
	{
		return this.customRender;
	},

	/**
	 * @public
	 * @return {BX.UI.Notification.Stack}
	 */
	getStack: function() 
	{
		return this.stack;
	},

	/**
	 * @package
	 * @param {State} state
	 */
	setState: function(state)
	{
		var code = this.getStateCode(state);
		if (code !== null)
		{
			this.state = state;
		}
	},

	/**
	 * @public
	 * @return {State}
	 */
	getState: function()
	{
		return this.state;
	},

	/**
	 * @public
	 * @param mode
	 * @return {?string}
	 */
	getStateCode: function(mode)
	{
		for (var code in State)
		{
			if (State[code] === mode)
			{
				return code;
			}
		}

		return null;
	},

	/**
	 * @public
	 */
	activateAutoHide: function()
	{
		if (!this.getAutoHide())
		{
			return;
		}

		this.deactivateAutoHide();

		this.autoHideTimeout = setTimeout(function() {
			this.close();
		}.bind(this), this.getAutoHideDelay());
	},

	/**
	 * @public
	 */
	deactivateAutoHide: function()
	{
		clearTimeout(this.autoHideTimeout);
		this.autoHideTimeout = null;
	},

	/**
	 * @public
	 * @param {boolean} autoHide
	 */
	setAutoHide: function(autoHide)
	{
		this.autoHide = autoHide !== false;
	},

	/**
	 * @public
	 * @return {boolean}
	 */
	getAutoHide: function()
	{
		return this.autoHide;
	},

	/**
	 * @public
	 * @param {number} delay
	 */
	setAutoHideDelay: function(delay)
	{
		if (BX.type.isNumber(delay) && delay > 0)
		{
			this.autoHideDelay = delay;
		}
	},

	/**
	 * @public
	 * @return {number}
	 */
	getAutoHideDelay: function()
	{
		return this.autoHideDelay;
	},

	/**
	 * @private
	 * @param {function} callback
	 */
	animateIn: function(callback)
	{
		if (!this.getContainer().classList.contains(this.getAnimationClassName()))
		{
			this.getContainer().addEventListener("transitionend", function handleTransitionEnd() {
				this.removeEventListener("transitionend", handleTransitionEnd);
				callback();
			});

			this.getContainer().classList.add(this.getAnimationClassName());
		}
		else
		{
			callback();
		}
	},

	/**
	 * @private
	 * @param {function} callback
	 */
	animateOut: function(callback)
	{
		if (this.getContainer().classList.contains(this.getAnimationClassName()))
		{
			this.getContainer().addEventListener("transitionend", function handleTransitionEnd() {
				this.removeEventListener("transitionend", handleTransitionEnd);
				callback();
			});

			this.getContainer().classList.remove(this.getAnimationClassName());
		}
		else
		{
			callback();
		}
	},

	/**
	 * @private
	 * @return {string}
	 */
	getAnimationClassName: function()
	{
		return this.animationClassName;
	},

	/**
	 * @private
	 */
	handleCloseBtnClick: function()
	{
		this.close();
	},

	/**
	 * @private
	 */
	handleMouseEnter: function()
	{
		this.fireEvent("onMouseEnter");
		this.deactivateAutoHide();
		this.setState(State.PAUSED);
		this.show();
	},

	/**
	 * @private
	 */
	handleMouseLeave: function()
	{
		this.fireEvent("onMouseLeave");
		this.activateAutoHide();
	},

	/**
	 * @package
	 * @param {string} eventName
	 * @returns {BX.UI.Notification.Event}
	 */
	fireEvent: function(eventName)
	{
		var event = this.getEvent(eventName);
		BX.onCustomEvent(this, event.getFullName(), [event]);

		return event;
	},

	/**
	 * @public
	 * @param {string} eventName
	 * @param {function} fn
	 */
	addEvent: function(eventName, fn)
	{
		if (BX.type.isFunction(fn))
		{
			BX.addCustomEvent(this, BX.UI.Notification.Event.getFullName(eventName), fn);
		}
	},

	/**
	 * @public
	 * @param {string} eventName
	 * @param {function} fn
	 */
	removeEvent: function(eventName, fn)
	{
		if (BX.type.isFunction(fn))
		{
			BX.removeCustomEvent(this, BX.UI.Notification.Event.getFullName(eventName), fn);
		}
	},

	/**
	 * @private
	 * @param {string} eventName
	 * @returns {BX.UI.Notification.Event}
	 */
	getEvent: function(eventName)
	{
		var event = new BX.UI.Notification.Event();
		event.setBalloon(this);
		event.setName(eventName);

		return event;
	},

	/**
	 * @public
	 * @return {object}
	 */
	getData: function()
	{
		return this.data;
	},

	/**
	 * @public
	 * @param {object} data
	 */
	setData: function(data)
	{
		if (BX.type.isPlainObject(data))
		{
			this.data = data;
		}
	}
};

BX.UI.Notification.Event = function()
{
	this.balloon = null;
	this.name = null;
};

/**
 *
 * @param {string} eventName
 * @return {string}
 */
BX.UI.Notification.Event.getFullName = function(eventName)
{
	return "UI.Notification.Balloon:" + eventName;
};

BX.UI.Notification.Event.prototype =
{
	/**
	 * @public
	 * @return {BX.UI.Notification.Balloon}
	 */
	getBalloon: function()
	{
		return this.balloon;
	},

	/**
	 * @public
	 * @param {BX.UI.Notification.Balloon} balloon
	 */
	setBalloon: function(balloon)
	{
		if (balloon instanceof BX.UI.Notification.Balloon)
		{
			this.balloon = balloon;
		}
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getName: function()
	{
		return this.name;
	},

	/**
	 * @public
	 * @param {string} name
	 */
	setName: function(name)
	{
		if (BX.type.isNotEmptyString(name))
		{
			this.name = name;
		}
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getFullName: function()
	{
		return BX.UI.Notification.Event.getFullName(this.getName());
	}
};

/**
 *
 * @param {BX.UI.Notification.Balloon} balloon
 * @param {object} [options]
 * @param {string} [options.id]
 * @param {string} [options.href]
 * @param {string} [options.title]
 * @param {?object.<string, function>} [options.events]
 * @constructor
 */
BX.UI.Notification.Action = function(balloon, options)
{
	options = BX.type.isPlainObject(options) ? options : Object.create(null);

	this.balloon = balloon;
	this.id = BX.type.isNotEmptyString(options.id) ? options.id : BX.util.getRandomString(8).toLowerCase();
	this.container = null;
	this.href = BX.type.isNotEmptyString(options.href) ? options.href : null;
	this.title = BX.type.isNotEmptyString(options.title) ? options.title : null;

	this.events = {};
	if (BX.type.isPlainObject(options.events))
	{
		for (var eventName in options.events)
		{
			var fn = options.events[eventName];
			if (!BX.type.isFunction(fn))
			{
				continue;
			}

			this.events[eventName] = (function(fn, action) {
				return function(event) {
					fn.call(event.target, event, action.getBalloon(), action);
				};
			})(fn, this);
		}
	}
};

BX.UI.Notification.Action.prototype =
{
	/**
	 * @public
	 * @return {BX.UI.Notification.Balloon}
	 */
	getBalloon: function()
	{
		return this.balloon;
	},

	/**
	 * @public
	 * @return {string}
	 */
	getId: function()
	{
		return this.id;
	},

	/**
	 * @public
	 * @return {string}
	 */
	getTitle: function()
	{
		return this.title;
	},

	/**
	 * @public
	 * @return {string}
	 */
	getHref: function()
	{
		return this.href;
	},

	/**
	 * @public
	 * @return {Element}
	 */
	getContainer: function()
	{
		if (this.container === null)
		{
			this.container = BX.create(this.getHref() ?  "a" : "span", {
				props: {
					className: "ui-notification-balloon-action"
				},
				events: this.events,
				text: this.getTitle()
			})
		}

		return this.container;
	}
};

})();