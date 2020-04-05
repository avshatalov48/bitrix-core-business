;(function(window) {

"use strict";

if (BX.PopupWindowManager)
{
	return;
}

BX.PopupWindowManager =
{
	_popups : [],
	_currentPopup : null,

	create : function(popupId, bindElement, params)
	{
		var id = popupId;
		if (BX.type.isPlainObject(popupId) && !bindElement && !params)
		{
			id = popupId.id;
			if (!BX.type.isNotEmptyString(id))
			{
				throw new Error("BX.PopupWindowManager: 'id' parameter is required.")
			}
		}

		var popupWindow = this.getPopupById(id);
		if (popupWindow === null)
		{
			popupWindow = new BX.PopupWindow(popupId, bindElement, params);
			BX.addCustomEvent(popupWindow, "onPopupShow", BX.delegate(this.onPopupShow, this));
			BX.addCustomEvent(popupWindow, "onPopupClose", BX.delegate(this.onPopupClose, this));
		}

		return popupWindow;
	},

	onPopupWindowIsInitialized : function(uniquePopupId, popupWindow)
	{
		BX.addCustomEvent(popupWindow, "onPopupDestroy", BX.delegate(this.onPopupDestroy, this));
		this._popups.push(popupWindow);
	},

	onPopupShow : function(popupWindow)
	{
		if (this._currentPopup !== null)
		{
			this._currentPopup.close();
		}

		this._currentPopup = popupWindow;
	},

	onPopupClose : function(popupWindow)
	{
		this._currentPopup = null;
	},

	onPopupDestroy : function(popupWindow)
	{
		var index;
		if ((index = this._getPopupIndex(popupWindow.uniquePopupId)) !== -1)
		{
			this._popups = BX.util.deleteFromArray(this._popups, index);
		}
	},

	getCurrentPopup : function()
	{
		return this._currentPopup;
	},

	isPopupExists : function(uniquePopupId)
	{
		return this._getPopupIndex(uniquePopupId) !== -1
	},

	isAnyPopupShown : function()
	{
		for(var i = 0, length = this._popups.length; i < length; i++)
		{
			if(this._popups[i].isShown())
			{
				return true;
			}
		}
		return false;
	},

	getPopupById: function(id)
	{
		for (var i = 0; i < this._popups.length; i++)
		{
			if (this._popups[i].getId() === id)
			{
				return this._popups[i];
			}
		}

		return null;
	},

	_getPopupIndex : function(uniquePopupId)
	{
		var index = -1;

		for (var i = 0; i < this._popups.length; i++)
		{
			if (this._popups[i].uniquePopupId === uniquePopupId)
			{
				return i;
			}
		}

		return index;
	},

	getMaxZIndex : function()
	{
		var zIndex = 0, ii;
		for (ii = 0; ii < this._popups.length; ii++)
		{
			zIndex = Math.max(zIndex, this._popups[ii].params.zIndex);
		}
		return zIndex;
	}
};
BX.addCustomEvent("onPopupWindowIsInitialized", BX.proxy(BX.PopupWindowManager.onPopupWindowIsInitialized, BX.PopupWindowManager));
/**
 *
 * @param {string|object} uniquePopupId
 * @param {Element|object|null} [bindElement]
 * @param [params]
 * @constructor
 */
BX.PopupWindow = function(uniquePopupId, bindElement, params)
{
	this.compatibleMode = params && BX.type.isBoolean(params.compatibleMode) ? params.compatibleMode : true;
	if (BX.type.isPlainObject(uniquePopupId) && !bindElement && !params)
	{
		params = uniquePopupId;
		uniquePopupId = params.id;
		bindElement = params.bindElement;
		this.compatibleMode = false;
	}

	params = params || {};
	this.params = params;

	if (!BX.type.isNotEmptyString(uniquePopupId))
	{
		uniquePopupId = "popup-window-" + BX.util.getRandomString().toLowerCase();
	}

	BX.onCustomEvent("onPopupWindowInit", [uniquePopupId, bindElement, params]);

	this.uniquePopupId = uniquePopupId;
	this.params.zIndex = BX.type.isNumber(params.zIndex) ? parseInt(params.zIndex) : 0;
	this.params.zIndexAbsolute = BX.type.isNumber(params.zIndexAbsolute) ? parseInt(params.zIndexAbsolute) : 0;
	this.buttons = params.buttons && BX.type.isArray(params.buttons) ? params.buttons : [];
	this.offsetTop = BX.PopupWindow.getOption("offsetTop");
	this.offsetLeft = BX.PopupWindow.getOption("offsetLeft");
	this.firstShow = false;
	this.bordersWidth = 20;
	this.bindElementPos = null;
	this.closeIcon = null;
	this.resizeIcon = null;
	this.angle = null;
	this.overlay = null;
	this.titleBar = null;
	this.bindOptions = typeof(params.bindOptions) === "object" ? params.bindOptions : {};
	this.autoHide = params.autoHide === true;
	this.autoHideHandler = BX.type.isFunction(params.autoHideHandler) ? params.autoHideHandler : null;
	this.handleAutoHide = this.handleAutoHide.bind(this);
	this.handleOverlayClick = this.handleOverlayClick.bind(this);
	this.isAutoHideBinded = false;
	this.closeByEsc = params.closeByEsc === true;
	this.isCloseByEscBinded = false;

	this.cacheable = true;
	this.destroyed = false;

	this.width = null;
	this.height = null;
	this.minWidth = null;
	this.minHeight = null;
	this.maxWidth = null;
	this.maxHeight = null;

	this.padding = null;
	this.contentPadding = null;
	this.background = null;
	this.contentBackground = null;

	this.appendContainer = document.body;

	this.dragOptions = {
		cursor: "",
		callback: BX.DoNothing,
		eventName: ""
	};

	this.dragged = false;
	this.dragPageX = 0;
	this.dragPageY = 0;

	this.animationShowClassName = null;
	this.animationCloseClassName = null;
	this.animationCloseEventType = null;

	this.handleDocumentKeyUp = this.handleDocumentKeyUp.bind(this);
	this.handleResizeWindow = this.handleResizeWindow.bind(this);

	if (params.events)
	{
		for (var eventName in params.events)
		{
			BX.addCustomEvent(this, eventName, params.events[eventName]);
		}
	}

	var popupClassName = "popup-window";

	if (params.contentColor && BX.type.isNotEmptyString(params.contentColor))
	{
		popupClassName += " popup-window-content-" + params.contentColor;
	}

	if (params.titleBar)
	{
		popupClassName += " popup-window-with-titlebar";
	}

	if (params.className && BX.type.isNotEmptyString(params.className))
	{
		popupClassName += " " + params.className;
	}

	if (params.darkMode)
	{
		popupClassName += ' popup-window-dark';
	}

	this.popupContainer = document.createElement("div");
	var titleBarID = "popup-window-titlebar-" + uniquePopupId;

	if (params.titleBar)
	{
		this.titleBar = BX.create("div", {
			props : {
				className: "popup-window-titlebar",
				id: titleBarID
			}
		});
	}

	if (params.closeIcon)
	{
		this.closeIcon = BX.create("span", {
			props : { className: "popup-window-close-icon" + (params.titleBar ? " popup-window-titlebar-close-icon" : "") },
			style : (typeof(params.closeIcon) === "object" ? params.closeIcon : {} ),
			events : { click : BX.proxy(this.handleCloseIconClick, this) }
		});

		if (BX.browser.IsIE())
		{
			BX.adjust(this.closeIcon, { attrs: { hidefocus: "true" } });
		}
	}

	this.contentContainer = BX.create("div",{
		props:{
			id: "popup-window-content-" +  uniquePopupId,
			className: "popup-window-content"
		}
	});

	BX.adjust(this.popupContainer, {
		props : {
			id : uniquePopupId,
			className : popupClassName
		},
		style : {
			zIndex: this.getZindex(),
			position: "absolute",
			display: "none",
			top: "0px",
			left: "0px"
		},
		children : [this.titleBar, this.contentContainer, this.closeIcon]
	});

	this.appendContainer.appendChild(this.popupContainer);

	this.buttonsContainer = null;

	if (params.angle)
	{
		this.setAngle(params.angle);
	}

	if (params.overlay)
	{
		this.setOverlay(params.overlay);
	}

	this.setOffset(params);
	this.setBindElement(bindElement);
	this.setTitleBar(params.titleBar);
	this.setContent(params.content);
	this.setButtons(params.buttons);
	this.setWidth(params.width);
	this.setHeight(params.height);
	this.setMinWidth(params.minWidth);
	this.setMinHeight(params.minHeight);
	this.setMaxWidth(params.maxWidth);
	this.setMaxHeight(params.maxHeight);
	this.setResizeMode(params.resizable);
	this.setPadding(params.padding);
	this.setContentPadding(params.contentPadding);
	this.setBackground(params.background);
	this.setContentBackground(params.contentBackground);
	this.setAnimation(params.animation);
	this.setCacheable(params.cacheable);

	// Compatibility
	if (params.contentNoPaddings)
	{
		this.setContentPadding(0);
	}
	if (params.noAllPaddings)
	{
		this.setPadding(0);
		this.setContentPadding(0);
	}

	if (params.bindOnResize !== false)
	{
		BX.bind(window, "resize", this.handleResizeWindow);
	}

	BX.onCustomEvent("onPopupWindowIsInitialized", [uniquePopupId, this]);
};

BX.PopupWindow.prototype.getId = function()
{
	return this.uniquePopupId;
};

BX.PopupWindow.prototype.isCompatibleMode = function()
{
	return this.compatibleMode;
};

/**
 *
 * @param {Element|string} content
 */
BX.PopupWindow.prototype.setContent = function(content)
{
	if (!this.contentContainer || !content)
	{
		return;
	}

	if (BX.type.isElementNode(content))
	{
		BX.cleanNode(this.contentContainer);

		var hasParent = BX.type.isDomNode(content.parentNode);
		this.contentContainer.appendChild(content);
		if (this.isCompatibleMode() || hasParent)
		{
			content.style.display = "block";
		}
	}
	else if (BX.type.isString(content))
	{
		this.contentContainer.innerHTML = content;
	}
	else
	{
		this.contentContainer.innerHTML = "&nbsp;";
	}
};

/**
 *
 * @param {BX.PopupWindowButton[]} buttons
 */
BX.PopupWindow.prototype.setButtons = function(buttons)
{
	this.buttons = buttons && BX.type.isArray(buttons) ? buttons : [];

	if (this.buttonsContainer)
	{
		BX.remove(this.buttonsContainer);
	}

	if (this.buttons.length > 0 && this.contentContainer)
	{
		var newButtons = [];
		for (var i = 0; i < this.buttons.length; i++)
		{
			var button = this.buttons[i];
			if (button instanceof BX.PopupWindowButton)
			{
				button.popupWindow = this;
				newButtons.push(button.render());
			}
			else if (button instanceof BX.UI.Button)
			{
				button.setContext(this);
				newButtons.push(button.render());
			}
		}

		this.buttonsContainer = this.contentContainer.parentNode.appendChild(
			BX.create("div",{
				props : { className : "popup-window-buttons" },
				children : newButtons
			})
		);
	}
};

/**
 *
 * @returns {BX.PopupWindowButton[]}
 */
BX.PopupWindow.prototype.getButtons = function()
{
	return this.buttons;
};

/**
 *
 * @param {string} id
 * @returns {BX.PopupWindowButton}
 */
BX.PopupWindow.prototype.getButton = function(id)
{
	for (var i = 0; i < this.buttons.length; i++)
	{
		var button = this.buttons[i];
		if (button.getId() === id)
		{
			return button;
		}
	}

	return null;
};

/**
 *
 * @param {Element|Event|object} bindElement
 */
BX.PopupWindow.prototype.setBindElement = function(bindElement)
{
	if (bindElement === null)
	{
		this.bindElement = null;
	}
	else if (typeof(bindElement) === "object")
	{
		if (BX.type.isDomNode(bindElement) || (BX.type.isNumber(bindElement.top) && BX.type.isNumber(bindElement.left)))
		{
			this.bindElement = bindElement;
		}
		else if (BX.type.isNumber(bindElement.clientX) && BX.type.isNumber(bindElement.clientY))
		{
			BX.fixEventPageXY(bindElement);
			this.bindElement = { left : bindElement.pageX, top : bindElement.pageY, bottom : bindElement.pageY };
		}
	}
};

/**
 *
 * @param bindElement
 * @returns {object} position
 */
BX.PopupWindow.prototype.getBindElementPos = function(bindElement)
{
	if (BX.type.isDomNode(bindElement))
	{
		return BX.pos(bindElement, false);
	}
	else if (bindElement && typeof(bindElement) === "object")
	{
		if (!BX.type.isNumber(bindElement.bottom))
		{
			bindElement.bottom = bindElement.top;
		}

		return bindElement;
	}
	else
	{
		var windowSize =  BX.GetWindowInnerSize();
		var windowScroll = BX.GetWindowScrollPos();
		var popupWidth = this.getPopupContainer().offsetWidth;
		var popupHeight = this.getPopupContainer().offsetHeight;

		this.bindOptions.forceTop = true;

		return {
			left : windowSize.innerWidth/2 - popupWidth/2 + windowScroll.scrollLeft,
			top : windowSize.innerHeight/2 - popupHeight/2 + windowScroll.scrollTop,
			bottom : windowSize.innerHeight/2 - popupHeight/2 + windowScroll.scrollTop,

			//for optimisation purposes
			windowSize : windowSize,
			windowScroll : windowScroll,
			popupWidth : popupWidth,
			popupHeight : popupHeight
		};
	}
};

/**
 *
 * @param {Object|bool} params
 * @param {Number} [params.offset = 0]
 * @param {string} [params.position = "top"]
 */
BX.PopupWindow.prototype.setAngle = function(params)
{
	if (params === false)
	{
		if (this.angle !== null)
		{
			BX.remove(this.angle.element);
		}

		this.angle = null;
		return;
	}

	var className = "popup-window-angly";
	if (this.angle === null)
	{
		var position = this.bindOptions.position && this.bindOptions.position === "top" ? "bottom" : "top";
		var angleMinLeft = BX.PopupWindow.getOption(position === "top" ? "angleMinTop" : "angleMinBottom");
		var defaultOffset = BX.type.isNumber(params.offset) ? params.offset : 0;

		var angleLeftOffset = BX.PopupWindow.getOption("angleLeftOffset", null);
		if (defaultOffset > 0 && BX.type.isNumber(angleLeftOffset))
		{
			defaultOffset += angleLeftOffset - BX.PopupWindow.defaultOptions.angleLeftOffset;
		}

		this.angle = {
			element : BX.create("div", { props : { className: className + " " + className +"-" + position }}),
			position : position,
			offset : 0,
			defaultOffset : Math.max(defaultOffset, angleMinLeft)
			//Math.max(BX.type.isNumber(params.offset) ? params.offset : 0, angleMinLeft)
		};

		this.getPopupContainer().appendChild(this.angle.element);
	}

	if (typeof(params) === "object" && params.position && BX.util.in_array(params.position, ["top", "right", "bottom", "left", "hide"]))
	{
		BX.removeClass(this.angle.element, className + "-" +  this.angle.position);
		BX.addClass(this.angle.element, className + "-" +  params.position);
		this.angle.position = params.position;
	}

	if (typeof(params) === "object" && BX.type.isNumber(params.offset))
	{
		var offset = params.offset;
		var minOffset, maxOffset;
		if (this.angle.position === "top")
		{
			minOffset = BX.PopupWindow.getOption("angleMinTop");
			maxOffset = this.getPopupContainer().offsetWidth - BX.PopupWindow.getOption("angleMaxTop");
			maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

			this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
			this.angle.element.style.left = this.angle.offset + "px";
			this.angle.element.style.marginLeft = 0;
			this.angle.element.style.removeProperty("top");
		}
		else if (this.angle.position === "bottom")
		{
			minOffset = BX.PopupWindow.getOption("angleMinBottom");
			maxOffset = this.getPopupContainer().offsetWidth - BX.PopupWindow.getOption("angleMaxBottom");
			maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

			this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
			this.angle.element.style.marginLeft = this.angle.offset + "px";
			this.angle.element.style.left = 0;
			this.angle.element.style.removeProperty("top");
		}
		else if (this.angle.position === "right")
		{
			minOffset = BX.PopupWindow.getOption("angleMinRight");
			maxOffset = this.getPopupContainer().offsetHeight - BX.PopupWindow.getOption("angleMaxRight");
			maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

			this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
			this.angle.element.style.top = this.angle.offset + "px";
			this.angle.element.style.removeProperty("left");
			this.angle.element.style.removeProperty("margin-left");
		}
		else if (this.angle.position === "left")
		{
			minOffset = BX.PopupWindow.getOption("angleMinLeft");
			maxOffset = this.getPopupContainer().offsetHeight - BX.PopupWindow.getOption("angleMaxLeft");
			maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;

			this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
			this.angle.element.style.top = this.angle.offset + "px";
			this.angle.element.style.removeProperty("left");
			this.angle.element.style.removeProperty("margin-left");
		}
	}
};

/**
 *
 * @returns {Number|null}
 */
BX.PopupWindow.prototype.getWidth = function()
{
	return this.width;
};

/**
 *
 * @param {Number} width
 */
BX.PopupWindow.prototype.setWidth = function(width)
{
	this.setWidthProperty("width", width);
};

/**
 *
 * @returns {Number|null}
 */
BX.PopupWindow.prototype.getHeight = function()
{
	return this.height;
};

/**
 *
 * @param {Number} height
 */
BX.PopupWindow.prototype.setHeight = function(height)
{
	this.setHeightProperty("height", height);
};

/**
 *
 * @returns {Number|null}
 */
BX.PopupWindow.prototype.getMinWidth = function()
{
	return this.minWidth;
};

BX.PopupWindow.prototype.setMinWidth = function(width)
{
	this.setWidthProperty("minWidth", width);
};

/**
 *
 * @returns {Number|null}
 */
BX.PopupWindow.prototype.getMinHeight = function()
{
	return this.minHeight;
};

BX.PopupWindow.prototype.setMinHeight = function(height)
{
	this.setHeightProperty("minHeight", height);
};

/**
 *
 * @returns {Number|null}
 */
BX.PopupWindow.prototype.getMaxWidth = function()
{
	return this.maxWidth;
};

BX.PopupWindow.prototype.setMaxWidth = function(width)
{
	this.setWidthProperty("maxWidth", width);
};

/**
 *
 * @returns {Number|null}
 */
BX.PopupWindow.prototype.getMaxHeight = function()
{
	return this.maxHeight;
};

BX.PopupWindow.prototype.setMaxHeight = function(height)
{
	this.setHeightProperty("maxHeight", height);
};

BX.PopupWindow.prototype.setWidthProperty = function(property, width)
{
	var props = ["width", "minWidth", "maxWidth"];
	if (props.indexOf(property) === -1)
	{
		return;
	}

	if (BX.type.isNumber(width) && width >= 0)
	{
		this[property] = width;
		this.getResizableContainer().style[property] = width + "px";
		this.getContentContainer().style.overflowX = "auto";
		this.getPopupContainer().classList.add("popup-window-fixed-width");

		if (this.getTitleContainer() && BX.browser.IsIE11())
		{
			this.getTitleContainer().style[property] = width + "px";
		}
	}
	else if (width === null || width === false)
	{
		this[property] = null;
		this.getResizableContainer().style.removeProperty(BX.util.getCssName(property));

		var hasOtherProps = props.some(function(prop) {
			return this.getResizableContainer().style.getPropertyValue(BX.util.getCssName(prop)) !== ""
		}, this);

		if (!hasOtherProps)
		{
			this.getContentContainer().style.removeProperty("overflow-x");
			this.getPopupContainer().classList.remove("popup-window-fixed-width");
		}

		if (this.getTitleContainer() && BX.browser.IsIE11())
		{
			this.getTitleContainer().style.removeProperty(BX.util.getCssName(property));
		}
	}
};

BX.PopupWindow.prototype.setHeightProperty = function(property, height)
{
	var props = ["height", "minHeight", "maxHeight"];
	if (props.indexOf(property) === -1)
	{
		return;
	}

	if (BX.type.isNumber(height) && height >= 0)
	{
		this[property] = height;
		this.getResizableContainer().style[property] = height + "px";
		this.getContentContainer().style.overflowY = "auto";
		this.getPopupContainer().classList.add("popup-window-fixed-height");
	}
	else if (height === null || height === false)
	{
		this[property] = null;
		this.getResizableContainer().style.removeProperty(BX.util.getCssName(property));

		var hasOtherProps = props.some(function(prop) {
			return this.getResizableContainer().style.getPropertyValue(BX.util.getCssName(prop)) !== ""
		}, this);

		if (!hasOtherProps)
		{
			this.getContentContainer().style.removeProperty("overflow-y");
			this.getPopupContainer().classList.remove("popup-window-fixed-height");
		}
	}
};

BX.PopupWindow.prototype.setPadding = function(padding) 
{
	if (BX.type.isNumber(padding) && padding >= 0)
	{
		this.padding = padding;
		this.getPopupContainer().style.padding = padding + "px";
	}
	else if (padding === null)
	{
		this.padding = null;
		this.getPopupContainer().style.removeProperty("padding");
	}
};

BX.PopupWindow.prototype.getPadding = function()
{
	return this.padding;
};

BX.PopupWindow.prototype.setContentPadding = function(padding)
{
	if (BX.type.isNumber(padding) && padding >= 0)
	{
		this.contentPadding = padding;
		this.getContentContainer().style.padding = padding + "px";
	}
	else if (padding === null)
	{
		this.contentPadding = null;
		this.getContentContainer().style.removeProperty("padding");
	}
};

BX.PopupWindow.prototype.getContentPadding = function()
{
	return this.contentPadding;
};

BX.PopupWindow.prototype.setBackground = function(background)
{
	if (BX.type.isNotEmptyString(background))
	{
		this.background = background;
		this.getPopupContainer().style.background = background;
	}
	else if (background === null)
	{
		this.background = null;
		this.getPopupContainer().style.removeProperty("background");
	}
};

BX.PopupWindow.prototype.getBackground = function()
{
	return this.background;
};

BX.PopupWindow.prototype.setContentBackground = function(background)
{
	if (BX.type.isNotEmptyString(background))
	{
		this.contentBackground = background;
		this.getContentContainer().style.background = background;
	}
	else if (background === null)
	{
		this.contentBackground = null;
		this.getContentContainer().style.removeProperty("background");
	}
};

BX.PopupWindow.prototype.getContentBackground = function()
{
	return this.contentBackground;
};

BX.PopupWindow.prototype.isDestroyed = function()
{
	return this.destroyed;
};

BX.PopupWindow.prototype.setCacheable = function(cacheable)
{
	this.cacheable = cacheable !== false;
};

BX.PopupWindow.prototype.isCacheable = function()
{
	return this.cacheable;
};

/**
 *
 * @param {object|boolean} options
 * @param {Number} [options.minWidth = 0] deprecated
 * @param {Number} [options.minHeight = 0] deprecated
 */
BX.PopupWindow.prototype.setResizeMode = function(options)
{
	if (options === true || BX.type.isPlainObject(options))
	{
		if (!this.resizeIcon)
		{
			this.resizeIcon = BX.create("div", {
				props: {
					className: "popup-window-resize"
				},
				events: {
					mousedown: BX.proxy(this.onResizeMouseDown, this)
				}
			});

			this.getPopupContainer().appendChild(this.resizeIcon);
		}

		//Compatibility
		this.setMinWidth(options.minWidth);
		this.setMinHeight(options.minHeight);
	}
	else if (options === false && this.resizeIcon)
	{
		BX.remove(this.resizeIcon);
		this.resizeIcon = null;
	}
};

/**
 *
 * @return {HTMLElement}
 */
BX.PopupWindow.prototype.getPopupContainer = function()
{
	return this.popupContainer;
};

BX.PopupWindow.prototype.getContentContainer = function()
{
	return this.contentContainer;
};

BX.PopupWindow.prototype.getResizableContainer = function()
{
	return BX.browser.IsIE11() ? this.getContentContainer() : this.getPopupContainer();
};

BX.PopupWindow.prototype.getTitleContainer = function()
{
	return this.titleBar;
};

BX.PopupWindow.prototype.onTitleMouseDown = function(event)
{
	this._startDrag(
		event,
		{
			cursor: "move",
			callback: BX.proxy(this.move, this),
			eventName: "Drag"
		}
	);
};

BX.PopupWindow.prototype.onResizeMouseDown = function(event)
{
	this._startDrag(
		event,
		{
			cursor: "nwse-resize",
			eventName: "Resize",
			callback: BX.proxy(this._resize, this)
		}
	);

	this.resizeContentPos = BX.pos(this.getResizableContainer());
	this.resizeContentOffset = this.resizeContentPos.left - BX.pos(this.getPopupContainer()).left;
};

BX.PopupWindow.prototype._resize = function(offsetX, offsetY, pageX, pageY)
{
	var width = pageX - this.resizeContentPos.left;
	var height = pageY - this.resizeContentPos.top;

	var scrollWidth = BX.GetWindowScrollSize().scrollWidth;
	if (this.resizeContentPos.left + width + this.resizeContentOffset >= scrollWidth)
	{
		width = scrollWidth - this.resizeContentPos.left - this.resizeContentOffset;
	}

	width = Math.max(width, this.getMinWidth());
	height = Math.max(height, this.getMinHeight());

	if (this.getMaxWidth() !== null)
	{
		width = Math.min(width, this.getMaxWidth());
	}

	if (this.getMaxHeight() !== null)
	{
		height = Math.min(height, this.getMaxHeight());
	}

	this.setWidth(width);
	this.setHeight(height);
};

BX.PopupWindow.prototype.isTopAngle = function()
{
	return this.angle !== null && this.angle.position === "top";
};

BX.PopupWindow.prototype.isBottomAngle = function()
{
	return this.angle !== null && this.angle.position === "bottom";
};

BX.PopupWindow.prototype.isTopOrBottomAngle = function()
{
	return this.angle !== null && BX.util.in_array(this.angle.position, ["top", "bottom"]);
};

BX.PopupWindow.prototype.getAngleHeight = function()
{
	return (this.isTopOrBottomAngle() ? BX.PopupWindow.getOption("angleTopOffset") : 0);
};

/**
 *
 * @param {object} params
 * @param {Number} [params.offsetLeft]
 * @param {Number} [params.offsetTop]
 */
BX.PopupWindow.prototype.setOffset = function(params)
{
	if (!BX.type.isPlainObject(params))
	{
		return;
	}

	if (BX.type.isNumber(params.offsetLeft))
	{
		this.offsetLeft = params.offsetLeft + BX.PopupWindow.getOption("offsetLeft");
	}

	if (BX.type.isNumber(params.offsetTop))
	{
		this.offsetTop = params.offsetTop + BX.PopupWindow.getOption("offsetTop");
	}
};

/**
 *
 * @param {object|string} params
 * @param {Element} [params.content]
 */
BX.PopupWindow.prototype.setTitleBar = function(params)
{
	if (!this.titleBar)
	{
		return;
	}

	if (typeof(params) === "object" && BX.type.isDomNode(params.content))
	{
		this.titleBar.innerHTML = "";
		this.titleBar.appendChild(params.content);
	}
	else if (typeof(params) === "string")
	{
		this.titleBar.innerHTML = "";
		this.titleBar.appendChild(
			BX.create("span", {
				props : {
					className: "popup-window-titlebar-text"
				},
				text : params
			})
		);
	}

	if (this.params.draggable)
	{
		this.titleBar.style.cursor = "move";
		BX.bind(this.titleBar, "mousedown", BX.proxy(this.onTitleMouseDown, this));
	}
};

/**
 *
 * @param {bool} enable
 */
BX.PopupWindow.prototype.setClosingByEsc = function(enable)
{
	enable = BX.type.isBoolean(enable) ? enable : true;
	if (enable)
	{
		this.closeByEsc = true;
		this.bindClosingByEsc();
	}
	else
	{
		this.closeByEsc = false;
		this.unbindClosingByEsc();
	}
};

/**
 * @private
 */
BX.PopupWindow.prototype.bindClosingByEsc = function()
{
	if (this.closeByEsc && !this.isCloseByEscBinded)
	{
		BX.bind(document, "keyup", this.handleDocumentKeyUp);
		this.isCloseByEscBinded = true;
	}
};

/**
 * @private
 */
BX.PopupWindow.prototype.unbindClosingByEsc = function()
{
	if (this.isCloseByEscBinded)
	{
		BX.unbind(document, "keyup", this.handleDocumentKeyUp);
		this.isCloseByEscBinded = false;
	}
};

/**
 * @public
 * @param {bool} enable
 */
BX.PopupWindow.prototype.setAutoHide = function(enable)
{
	enable = BX.type.isBoolean(enable) ? enable : true;
	if (enable)
	{
		this.autoHide = true;
		this.bindAutoHide();
	}
	else
	{
		this.autoHide = false;
		this.unbindAutoHide();
	}
};

/**
 * @private
 */
BX.PopupWindow.prototype.bindAutoHide = function()
{
	if (this.autoHide && !this.isAutoHideBinded && this.isShown())
	{
		this.isAutoHideBinded = true;

		if (this.isCompatibleMode())
		{
			BX.bind(this.getPopupContainer(), "click", this.cancelBubble);
		}

		if (this.overlay && this.overlay.element)
		{
			BX.bind(this.overlay.element, "click", this.handleOverlayClick);
		}
		else
		{
			if (this.isCompatibleMode())
			{
				BX.bind(document, "click", this.handleAutoHide);
			}
			else
			{
				document.addEventListener("click", this.handleAutoHide, true);
			}
		}
	}
};

/**
 * @private
 */
BX.PopupWindow.prototype.unbindAutoHide = function()
{
	if (this.isAutoHideBinded)
	{
		this.isAutoHideBinded = false;

		if (this.isCompatibleMode())
		{
			BX.unbind(this.getPopupContainer(), "click", this.cancelBubble);
		}

		if (this.overlay && this.overlay.element)
		{
			BX.unbind(this.overlay.element, "click", this.handleOverlayClick);
		}
		else
		{
			if (this.isCompatibleMode())
			{
				BX.unbind(document, "click", this.handleAutoHide);
			}
			else
			{
				document.removeEventListener("click", this.handleAutoHide, true);
			}
		}
	}
};

/**
 * @private
 * @param event
 */
BX.PopupWindow.prototype.handleAutoHide = function(event)
{
	if (this.isDestroyed())
	{
		return;
	}

	if (this.autoHideHandler !== null)
	{
		if (this.autoHideHandler(event))
		{
			this._tryCloseByEvent(event);
		}
	}
	else if (event.target !== this.getPopupContainer() && !this.getPopupContainer().contains(event.target))
	{
		this._tryCloseByEvent(event);
	}
};

BX.PopupWindow.prototype._tryCloseByEvent = function(event)
{
	if (this.isCompatibleMode())
	{
		this.tryCloseByEvent(event);
	}
	else
	{
		setTimeout(BX.proxy(this.tryCloseByEvent, this), 0, event);
	}
};

/**
 *
 * @param {MouseEvent} event
 * @private
 */
BX.PopupWindow.prototype.handleOverlayClick = function(event)
{
	this.tryCloseByEvent(event);
	event.stopPropagation();
};

/**
 *
 * @param {object} params
 * @param {Number} [params.opacity]
 * @param {string} [params.backgroundColor]
 */
BX.PopupWindow.prototype.setOverlay = function(params)
{
	if (this.overlay === null)
	{
		this.overlay = {
			element : BX.create("div", {
				props : {
					className: "popup-window-overlay", id : "popup-window-overlay-" + this.uniquePopupId
				}
			})
		};

		this.adjustOverlayZindex();
		this.resizeOverlay();

		this.appendContainer.appendChild(this.overlay.element);
	}

	if (params && params.hasOwnProperty('opacity') && BX.type.isNumber(params.opacity) && params.opacity >= 0 && params.opacity <= 100)
	{
		if (BX.browser.IsIE() && !BX.browser.IsIE9())
		{
			this.overlay.element.style.filter =  "alpha(opacity=" + params.opacity +")";
		}
		else
		{
			this.overlay.element.style.filter = "none";
			this.overlay.element.style.opacity = parseFloat(params.opacity/100).toPrecision(3);
		}
	}

	if (params && params.backgroundColor)
	{
		this.overlay.element.style.backgroundColor = params.backgroundColor;
	}
};

BX.PopupWindow.prototype.removeOverlay = function()
{
	if (this.overlay !== null && this.overlay.element !== null)
	{
		BX.remove(this.overlay.element);
	}

	if (this.overlayTimeout)
	{
		clearInterval(this.overlayTimeout);
		this.overlayTimeout = null;
	}

	this.overlay = null;
};

BX.PopupWindow.prototype.hideOverlay = function()
{
	if (this.overlay !== null && this.overlay.element !== null)
	{
		if (this.overlayTimeout)
		{
			clearInterval(this.overlayTimeout);
			this.overlayTimeout = null;
		}

		this.overlay.element.style.display = "none";
	}
};

BX.PopupWindow.prototype.showOverlay = function()
{
	if (this.overlay !== null && this.overlay.element !== null)
	{
		this.overlay.element.style.display = "block";

		var popupHeight = this.getPopupContainer().offsetHeight;
		this.overlayTimeout = setInterval(function() {
			if (popupHeight !== this.getPopupContainer().offsetHeight)
			{
				this.resizeOverlay();
				popupHeight = this.getPopupContainer().offsetHeight;
			}

		}.bind(this), 1000);
	}
};

BX.PopupWindow.prototype.resizeOverlay = function()
{
	if (this.overlay !== null && this.overlay.element !== null)
	{
		var windowSize = BX.GetWindowScrollSize();
		var scrollHeight = Math.max(
			document.body.scrollHeight, document.documentElement.scrollHeight,
			document.body.offsetHeight, document.documentElement.offsetHeight,
			document.body.clientHeight, document.documentElement.clientHeight
		);

		this.overlay.element.style.width = windowSize.scrollWidth + "px";
		this.overlay.element.style.height = scrollHeight + "px";
	}
};

BX.PopupWindow.prototype.getZindex = function()
{
	if (this.overlay !== null)
	{
		return (this.params.zIndexAbsolute > 0 ? this.params.zIndexAbsolute : BX.PopupWindow.getOption("popupOverlayZindex") + this.params.zIndex);
	}
	else
	{
		return (this.params.zIndexAbsolute > 0 ? this.params.zIndexAbsolute : BX.PopupWindow.getOption("popupZindex") + this.params.zIndex);
	}
};

BX.PopupWindow.prototype.adjustOverlayZindex = function()
{
	if (this.overlay !== null && this.overlay.element !== null)
	{
		this.overlay.element.style.zIndex = parseInt(this.getPopupContainer().style.zIndex) - 1;
	}
};

BX.PopupWindow.prototype.show = function()
{
	if (this.isShown() || this.isDestroyed())
	{
		return;
	}

	if (!this.firstShow)
	{
		BX.onCustomEvent(this, "onPopupFirstShow", [this]);
		this.firstShow = true;
	}

	BX.onCustomEvent(this, "onPopupShow", [this]);

	this.showOverlay();
	this.getPopupContainer().style.display = "block";
	this.adjustPosition();

	this.animateOpening(function() {

		if (this.isDestroyed())
		{
			return;
		}

		BX.removeClass(this.getPopupContainer(), this.animationShowClassName);
		BX.onCustomEvent(this, "onAfterPopupShow", [this]);
	}.bind(this));


	this.bindClosingByEsc();

	if (this.isCompatibleMode())
	{
		setTimeout(function() {
			this.bindAutoHide();
		}.bind(this), 100);
	}
	else
	{
		this.bindAutoHide();
	}
};

/**
 * @private
 * @param {Function} callback
 */
BX.PopupWindow.prototype.animateOpening = function(callback)
{
	BX.removeClass(this.getPopupContainer(), this.animationCloseClassName);

	if (this.animationShowClassName !== null)
	{
		BX.addClass(this.getPopupContainer(), this.animationShowClassName);

		if (this.animationCloseEventType !== null)
		{
			var eventName = this.animationCloseEventType + "end";
			this.getPopupContainer().addEventListener(eventName, function handleTransitionEnd() {
				this.removeEventListener(eventName, handleTransitionEnd);
				callback();
			});
		}
		else
		{
			callback();
		}
	}
	else
	{
		callback();
	}
};

/**
 * @private
 * @param {Function} callback
 */
BX.PopupWindow.prototype.animateClosing = function(callback)
{
	BX.removeClass(this.getPopupContainer(), this.animationShowClassName);

	if (this.animationCloseClassName !== null)
	{
		BX.addClass(this.getPopupContainer(), this.animationCloseClassName);

		if (this.animationCloseEventType !== null)
		{
			var eventName = this.animationCloseEventType + "end";
			this.getPopupContainer().addEventListener(eventName, function handleTransitionEnd() {
				this.removeEventListener(eventName, handleTransitionEnd);
				callback();
			});
		}
		else
		{
			callback();
		}
	}
	else
	{
		callback();
	}
};

/**
 * @public
 * @return {boolean}
 */
BX.PopupWindow.prototype.isShown = function()
{
   return !this.isDestroyed() && this.getPopupContainer().style.display === "block";
};

/**
 * @private
 * @param event
 */
BX.PopupWindow.prototype.cancelBubble = function(event)
{
	event = event || window.event;

	if (event.stopPropagation)
	{
		event.stopPropagation();
	}
	else
	{
		event.cancelBubble = true;
	}
};

/**
 * @
 * @return {void}
 */
BX.PopupWindow.prototype.close = function()
{
	if (this.isDestroyed() || !this.isShown())
	{
		return;
	}

	BX.onCustomEvent(this, "onPopupClose", [this]);

	if (this.isDestroyed())
	{
		return;
	}

	this.animateClosing(function() {

		if (this.isDestroyed())
		{
			return;
		}

		this.hideOverlay();

		this.getPopupContainer().style.display = "none";

		BX.removeClass(this.getPopupContainer(), this.animationCloseClassName);

		this.unbindClosingByEsc();

		if (this.isCompatibleMode())
		{
			setTimeout(function() {
				this.unbindAutoHide();
			}.bind(this), 0);
		}
		else
		{
			this.unbindAutoHide();
		}

		BX.onCustomEvent(this, "onPopupAfterClose", [this]);

		if (!this.isCacheable())
		{
			this.destroy();
		}

	}.bind(this));
};

BX.PopupWindow.prototype.toggle = function()
{
	this.isShown() ? this.close() : this.show();
};

/**
 * @private
 * @param {MouseEvent} event
 */
BX.PopupWindow.prototype.tryCloseByEvent = function(event)
{
	if (this.isLeftButton(event))
	{
		this.close();
	}
};

BX.PopupWindow.prototype.setAnimation = function(options)
{
	if (BX.type.isPlainObject(options))
	{
		this.animationShowClassName = BX.type.isNotEmptyString(options.showClassName) ? options.showClassName : null;
		this.animationCloseClassName = BX.type.isNotEmptyString(options.closeClassName) ? options.closeClassName : null;
		this.animationCloseEventType =
			BX.util.in_array(options.closeAnimationType, ["animation", "transition"])
				? options.closeAnimationType
				: null
		;
	}
	else if (BX.type.isNotEmptyString(options))
	{
		var animationName = options;

		if (animationName === "fading")
		{
			this.animationShowClassName = "popup-window-show-animation-opacity";
			this.animationCloseClassName = "popup-window-close-animation-opacity";
			this.animationCloseEventType = 'animation';
		}
		else if (animationName === "fading-slide")
		{
			this.animationShowClassName = "popup-window-show-animation-opacity-transform";
			this.animationCloseClassName = "popup-window-close-animation-opacity";
			this.animationCloseEventType = "animation";
		}
		else if (animationName === "scale")
		{
			this.animationShowClassName = "popup-window-show-animation-scale";
			this.animationCloseClassName = "popup-window-close-animation-opacity";
			this.animationCloseEventType = "animation";
		}
	}
	else if (options === false || options === null)
	{
		this.animationShowClassName = null;
		this.animationCloseClassName = null;
		this.animationCloseEventType = null;
	}
};

/**
 *
 * @param {MouseEvent} event
 * @private
 */
BX.PopupWindow.prototype.handleCloseIconClick = function(event)
{
	this.tryCloseByEvent(event);
	event.stopPropagation();
};

/**
 * @private
 * @param {MouseEvent} event
 */
BX.PopupWindow.prototype.isLeftButton = function(event)
{
	return event.button === 0;
};

BX.PopupWindow.prototype.handleDocumentKeyUp = function(event)
{
	event = event || window.event;
	if (event.keyCode === 27)
	{
		_checkEscPressed(this.getZindex(), BX.proxy(this.close, this));
	}
};

BX.PopupWindow.prototype.destroy = function()
{
	BX.onCustomEvent(this, "onPopupDestroy", [this]);

	this.destroyed = true;

	this.unbindClosingByEsc();

	if (this.isCompatibleMode())
	{
		setTimeout(function() {
			this.unbindAutoHide();
		}.bind(this), 0);
	}
	else
	{
		this.unbindAutoHide();
	}

	BX.unbindAll(this);
	BX.unbind(document, "mousemove", BX.proxy(this._moveDrag, this));
	BX.unbind(document, "mouseup", BX.proxy(this._stopDrag, this));
	BX.unbind(window, "resize", this.handleResizeWindow);

	BX.remove(this.popupContainer);
	this.removeOverlay();

	this.popupContainer = null;
	this.contentContainer = null;
	this.closeIcon = null;
	this.titleBar = null;
	this.buttonsContainer = null;
	this.angle = null;
	this.resizeIcon = null;
};

BX.PopupWindow.prototype.enterFullScreen = function()
{
	if (BX.PopupWindow.fullscreenStatus)
	{
		if (document.cancelFullScreen)
		{
			document.cancelFullScreen();
		}
		else if (document.mozCancelFullScreen)
		{
			document.mozCancelFullScreen();
		}
		else if (document.webkitCancelFullScreen)
		{
			document.webkitCancelFullScreen();
		}
	}
	else
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
		{
			this.contentContainer.webkitRequestFullScreen(this.contentContainer.ALLOW_KEYBOARD_INPUT);
			BX.bind(window, "webkitfullscreenchange", this.fullscreenBind = BX.proxy(this.eventFullScreen, this));
		}
		else if (BX.browser.IsFirefox())
		{
			this.contentContainer.mozRequestFullScreen(this.contentContainer.ALLOW_KEYBOARD_INPUT);
			BX.bind(window, "mozfullscreenchange", this.fullscreenBind = BX.proxy(this.eventFullScreen, this));
		}
	}
};

BX.PopupWindow.prototype.eventFullScreen = function(event)
{
	if (BX.PopupWindow.fullscreenStatus)
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
		{
			BX.unbind(window, "webkitfullscreenchange", this.fullscreenBind);
		}
		else if (BX.browser.IsFirefox())
		{
			BX.unbind(window, "mozfullscreenchange", this.fullscreenBind);
		}

		BX.removeClass(this.contentContainer, "popup-window-fullscreen", [this.contentContainer]);

		BX.PopupWindow.fullscreenStatus = false;
		BX.onCustomEvent(this, "onPopupFullscreenLeave");
		this.adjustPosition();
	}
	else
	{
		BX.addClass(this.contentContainer, "popup-window-fullscreen");
		BX.PopupWindow.fullscreenStatus = true;
		BX.onCustomEvent(this, "onPopupFullscreenEnter", [this.contentContainer]);
		this.adjustPosition();
	}
};

/**
 *
 * @param {object} [bindOptions]
 * @param {bool} [bindOptions.forceBindPosition]
 * @param {bool} [bindOptions.forceLeft]
 * @param {bool} [bindOptions.forceTop]
 * @param {string} [bindOptions.position = "bottom"]
 */
BX.PopupWindow.prototype.adjustPosition = function(bindOptions)
{
	if (bindOptions && typeof(bindOptions) === "object")
	{
		this.bindOptions = bindOptions;
	}

	var bindElementPos = this.getBindElementPos(this.bindElement);

	if (
		!this.bindOptions.forceBindPosition &&
		this.bindElementPos !== null &&
		bindElementPos.top === this.bindElementPos.top &&
		bindElementPos.left === this.bindElementPos.left
	)
	{
		return;
	}

	this.bindElementPos = bindElementPos;

	var windowSize = bindElementPos.windowSize ? bindElementPos.windowSize : BX.GetWindowInnerSize();
	var windowScroll = bindElementPos.windowScroll ? bindElementPos.windowScroll : BX.GetWindowScrollPos();
	var popupWidth = bindElementPos.popupWidth ? bindElementPos.popupWidth : this.popupContainer.offsetWidth;
	var popupHeight = bindElementPos.popupHeight ? bindElementPos.popupHeight : this.popupContainer.offsetHeight;

	var angleTopOffset = BX.PopupWindow.getOption("angleTopOffset");

	var left = this.bindElementPos.left + this.offsetLeft -
				(this.isTopOrBottomAngle() ? BX.PopupWindow.getOption("angleLeftOffset") : 0);

	if (
		!this.bindOptions.forceLeft &&
		(left + popupWidth + this.bordersWidth) >= (windowSize.innerWidth + windowScroll.scrollLeft) &&
		(windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth) > 0)
	{
		var bindLeft = left;
		left = windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth;
		if (this.isTopOrBottomAngle())
		{
			this.setAngle({ offset : bindLeft - left + this.angle.defaultOffset});
		}
	}
	else if (this.isTopOrBottomAngle())
	{
		this.setAngle({ offset : this.angle.defaultOffset + (left < 0 ? left : 0) });
	}

	if (left < 0)
	{
		left = 0;
	}

	var top = 0;

	if (this.bindOptions.position && this.bindOptions.position === "top")
	{

		top = this.bindElementPos.top - popupHeight - this.offsetTop - (this.isBottomAngle() ? angleTopOffset : 0);
		if (top < 0 || (!this.bindOptions.forceTop && top < windowScroll.scrollTop))
		{
			top = this.bindElementPos.bottom + this.offsetTop;
			if (this.angle !== null)
			{
				top += angleTopOffset;
				this.setAngle({ position: "top"});
			}
		}
		else if (this.isTopAngle())
		{
			top = top - angleTopOffset + BX.PopupWindow.getOption("positionTopXOffset");
			this.setAngle({ position: "bottom"});
		}
		else
		{
			top += BX.PopupWindow.getOption("positionTopXOffset");
		}
	}
	else
	{

		top = this.bindElementPos.bottom + this.offsetTop + this.getAngleHeight();

		if (
			!this.bindOptions.forceTop &&
			(top + popupHeight) > (windowSize.innerHeight + windowScroll.scrollTop) &&
			(this.bindElementPos.top - popupHeight - this.getAngleHeight()) >= 0) //Can we place the PopupWindow above the bindElement?
		{
			//The PopupWindow doesn't place below the bindElement. We should place it above.
			top = this.bindElementPos.top - popupHeight;

			if (this.isTopOrBottomAngle())
			{
				top -= angleTopOffset;
				this.setAngle({ position: "bottom"});
			}

			top += BX.PopupWindow.getOption("positionTopXOffset");

		}
		else if (this.isBottomAngle())
		{
			top += angleTopOffset;
			this.setAngle({ position: "top"});
		}
	}

	if (top < 0)
	{
		top = 0;
	}

	BX.adjust(this.popupContainer, { style: {
		top: top + "px",
		left: left + "px",
		zIndex: this.getZindex()
	}});

	this.adjustOverlayZindex();
};

BX.PopupWindow.prototype.handleResizeWindow = function(event)
{
	if (this.isShown())
	{
		this.adjustPosition();
		if (this.overlay !== null)
		{
			this.resizeOverlay();
		}
	}
};

BX.PopupWindow.prototype.move = function(offsetX, offsetY, pageX, pageY)
{
	var left = parseInt(this.popupContainer.style.left) + offsetX;
	var top = parseInt(this.popupContainer.style.top) + offsetY;

	if (typeof(this.params.draggable) === "object" && this.params.draggable.restrict)
	{
		//Left side
		if (left < 0)
		{
			left = 0;
		}

		//Right side
		var scrollSize = BX.GetWindowScrollSize();
		var floatWidth = this.popupContainer.offsetWidth;
		var floatHeight = this.popupContainer.offsetHeight;

		if (left > (scrollSize.scrollWidth - floatWidth))
		{
			left = scrollSize.scrollWidth - floatWidth;
		}

		if (top > (scrollSize.scrollHeight - floatHeight))
		{
			top = scrollSize.scrollHeight - floatHeight;
		}

		//Top side
		if (top < 0)
		{
			top = 0;
		}
	}

	this.popupContainer.style.left = left + "px";
	this.popupContainer.style.top = top + "px";
};

BX.PopupWindow.prototype._startDrag = function(event, options)
{
	event = event || window.event;
	BX.fixEventPageXY(event);

	options = options || {};
	if (BX.type.isNotEmptyString(options.cursor))
	{
		this.dragOptions.cursor = options.cursor;
	}

	if (BX.type.isNotEmptyString(options.eventName))
	{
		this.dragOptions.eventName = options.eventName;
	}

	if (BX.type.isFunction(options.callback))
	{
		this.dragOptions.callback = options.callback;
	}

	this.dragPageX = event.pageX;
	this.dragPageY = event.pageY;
	this.dragged = false;

	BX.bind(document, "mousemove", BX.proxy(this._moveDrag, this));
	BX.bind(document, "mouseup", BX.proxy(this._stopDrag, this));

	if (document.body.setCapture)
	{
		document.body.setCapture();
	}

	document.body.ondrag = BX.False;
	document.body.onselectstart = BX.False;
	document.body.style.cursor = this.dragOptions.cursor;
	document.body.style.MozUserSelect = "none";
	this.popupContainer.style.MozUserSelect = "none";

	return BX.PreventDefault(event);
};

BX.PopupWindow.prototype._moveDrag = function(event)
{
	event = event || window.event;
	BX.fixEventPageXY(event);

	if (this.dragPageX === event.pageX && this.dragPageY === event.pageY)
	{
		return;
	}

	this.dragOptions.callback(
		event.pageX - this.dragPageX,
		event.pageY - this.dragPageY,
		event.pageX,
		event.pageY
	);

	this.dragPageX = event.pageX;
	this.dragPageY = event.pageY;

	if (!this.dragged)
	{
		BX.onCustomEvent(this, "onPopup" + this.dragOptions.eventName + "Start", [this]);
		this.dragged = true;
	}

	BX.onCustomEvent(this, "onPopup" + this.dragOptions.eventName, [this]);
};

BX.PopupWindow.prototype._stopDrag = function(event)
{
	if(document.body.releaseCapture)
	{
		document.body.releaseCapture();
	}

	BX.unbind(document, "mousemove", BX.proxy(this._moveDrag, this));
	BX.unbind(document, "mouseup", BX.proxy(this._stopDrag, this));

	//document.onmousedown = null;
	document.body.ondrag = null;
	document.body.onselectstart = null;
	document.body.style.cursor = "";
	document.body.style.MozUserSelect = "";
	this.popupContainer.style.MozUserSelect = "";

	BX.onCustomEvent(this, "onPopup" + this.dragOptions.eventName + "End", [this]);
	this.dragged = false;

	return BX.PreventDefault(event);
};

BX.PopupWindow.options = {};
BX.PopupWindow.defaultOptions = {

	angleLeftOffset : 40, /*left offset for popup about target */

	positionTopXOffset : -11, /* when popup position is 'top' offset distance between popup body and target node */

	angleTopOffset : 10,    /* offset distance between popup body and target node if use angle, sum with positionTopXOffset  */

	popupZindex : 1000,
	popupOverlayZindex : 1100,

	angleMinLeft : 10,
	angleMaxLeft : 30,

	angleMinRight : 10,
	angleMaxRight : 30,

	angleMinBottom : 23, /**/
	angleMaxBottom : 25,

	angleMinTop : 23,
	angleMaxTop : 25,

	offsetLeft : 0,
	offsetTop: 0
};

BX.PopupWindow.setOptions = function(options)
{
	if (!options || typeof(options) !== "object")
	{
		return;
	}

	for (var option in options)
	{
		BX.PopupWindow.options[option] = options[option];
	}
};

BX.PopupWindow.getOption = function(option, defaultValue)
{
	if (typeof(BX.PopupWindow.options[option]) !== "undefined")
	{
		return BX.PopupWindow.options[option];
	}
	else if (typeof(defaultValue) !== "undefined")
	{
		return defaultValue;
	}
	else
	{
		return BX.PopupWindow.defaultOptions[option];
	}
};

/**
 *
 * @param {object} params
 * @param {string} [params.text]
 * @param {string} [params.id]
 * @param {string} [params.className]
 * @param {object} [params.events]
 * @constructor
 */
BX.PopupWindowButton = function(params)
{
	this.popupWindow = null;

	this.params = params || {};

	this.text = this.params.text || "";
	this.id = this.params.id || "";
	this.className = this.params.className || "";
	this.events = this.params.events || {};

	this.contextEvents = {};
	for (var eventName in this.events)
	{
		this.contextEvents[eventName] = BX.proxy(this.events[eventName], this);
	}

	this.buttonNode = BX.create(
		"span",
		{
			props : { className : "popup-window-button" + (this.className.length > 0 ? " " + this.className : ""), id : this.id },
			events : this.contextEvents,
			text : this.text
		}
	);
};

BX.PopupWindowButton.prototype.render = function()
{
	return this.buttonNode;
};

BX.PopupWindowButton.prototype.getId = function()
{
	return this.id;
};

BX.PopupWindowButton.prototype.getName = function()
{
	return this.name;
};

/**
 *
 * @returns {Element}
 */
BX.PopupWindowButton.prototype.getContainer = function()
{
	return this.buttonNode;
};

BX.PopupWindowButton.prototype.setName = function(name)
{
	this.text = name || "";
	if (this.buttonNode)
	{
		BX.cleanNode(this.buttonNode);
		BX.adjust(this.buttonNode, { text : this.text} );
	}
};

BX.PopupWindowButton.prototype.setClassName = function(className)
{
	if (this.buttonNode)
	{
		if (BX.type.isString(this.className) && (this.className !== ""))
		{
			BX.removeClass(this.buttonNode, this.className);
		}

		BX.addClass(this.buttonNode, className)
	}

	this.className = className;
};

BX.PopupWindowButton.prototype.addClassName = function(className)
{
	if (this.buttonNode)
	{
		BX.addClass(this.buttonNode, className);
		this.className = this.buttonNode.className;
	}
};

BX.PopupWindowButton.prototype.removeClassName = function(className)
{
	if (this.buttonNode)
	{
		BX.removeClass(this.buttonNode, className);
		this.className = this.buttonNode.className;
	}
};

BX.PopupWindowButtonLink = function(params)
{
	BX.PopupWindowButtonLink.superclass.constructor.apply(this, arguments);

	this.buttonNode = BX.create(
		"span",
		{
			props : { className : "popup-window-button popup-window-button-link" + (this.className.length > 0 ? " " + this.className : ""), id : this.id },
			text : this.text,
			events : this.contextEvents
		}
	);

};

BX.extend(BX.PopupWindowButtonLink, BX.PopupWindowButton);

BX.PopupWindowCustomButton = function(params)
{
	BX.PopupWindowCustomButton.superclass.constructor.apply(this, arguments);

	this.buttonNode = BX.create(
		"span",
		{
			props : { className :  (this.className.length > 0 ? this.className : ""), id : this.id },
			events : this.contextEvents,
			text : this.text
		}
	);
};

BX.extend(BX.PopupWindowCustomButton, BX.PopupWindowButton);

BX.PopupMenu = {

	Data: {},
	currentItem: null,

	show: function(options)
	{
		if (this.currentItem !== null)
		{
			this.currentItem.popupWindow.close();
		}

		this.currentItem = this.create.apply(this, arguments);
		this.currentItem.popupWindow.show();
	},

	create: function(options)
	{
		var menuId = null;

		//Compatibility
		var bindElement = arguments[1];
		var menuItems = arguments[2];
		var params = arguments[3];

		if (BX.type.isPlainObject(options) && !bindElement && !menuItems && !params)
		{
			menuId = options.id;
			if (!BX.type.isNotEmptyString(menuId))
			{
				throw new Error("BX.PopupMenu.create: 'id' parameter is required.")
			}
		}
		else
		{
			menuId = options;
		}

		if (!this.Data[menuId])
		{
			this.Data[menuId] = new BX.PopupMenuWindow(options, bindElement, menuItems, params);
			BX.addCustomEvent(this.Data[menuId], 'onPopupMenuDestroy', this.onPopupDestroy.bind(this));
		}

		return this.Data[menuId];
	},

	getCurrentMenu : function()
	{
		return this.currentItem;
	},

	getMenuById : function(id)
	{
		return this.Data[id] ? this.Data[id] : null;
	},

	onPopupDestroy: function(popupMenuWindow)
	{
		this.destroy(popupMenuWindow.id);
	},

	destroy : function(id)
	{
		var menu = this.getMenuById(id);
		if (menu)
		{
			if (this.currentItem === menu)
			{
				this.currentItem = null;
			}
			menu.popupWindow.destroy();
			delete this.Data[id];
		}
	}
};

BX.PopupMenuWindow = function(id, bindElement, menuItems, params)
{
	if (BX.type.isPlainObject(id) && !bindElement && !menuItems && !params)
	{
		params = id;
		params.compatibleMode = false;

		id = params.id;
		bindElement = params.bindElement;
		menuItems = params.items;

		if (!BX.type.isNotEmptyString(id))
		{
			id = "menu-popup-" + BX.util.getRandomString().toLowerCase();
		}
	}

	this.id = id;
	this.bindElement = bindElement;

	/**
	 *
	 * @type {BX.PopupMenuItem[]}
	 */
	this.menuItems = [];
	this.itemsContainer = null;
	this.params = params && typeof(params) === "object" ? params : {};
	this.parentMenuWindow = null;
	this.parentMenuItem = null;

	if (menuItems && BX.type.isArray(menuItems))
	{
		for (var i = 0; i < menuItems.length; i++)
		{
			this.addMenuItemInternal(menuItems[i], null);
		}
	}

	this.layout = {
		menuContainer: null,
		itemsContainer: null
	};

	this.popupWindow = this.__createPopup();
};

BX.PopupMenuWindow.prototype.__createPopup = function()
{
	var domItems = [];
	for (var i = 0; i < this.menuItems.length; i++)
	{
		var item = this.menuItems[i];
		var itemLayout = item.getLayout();
		domItems.push(itemLayout.item);
	}

	var defaults = {
		closeByEsc: false,
		angle: false,
		autoHide: true,
		offsetTop: 1,
		offsetLeft: 0,
		animation: "fading"
	};

	var options = BX.mergeEx(defaults, this.params);

	//Override user params
	options.noAllPaddings = true;
	options.darkMode = false;
	options.autoHideHandler = this.handleAutoHide.bind(this);
	options.content = (this.layout.menuContainer = BX.create("div", {
		props : {
			className : "menu-popup"
		},
		children: [
			(this.layout.itemsContainer = this.itemsContainer = BX.create("div", {
				props : {
					className : "menu-popup-items"
				},
				children: domItems
			}))
		]
	}));

	//Make internal event handlers first in the queue.
	options.events = {
		onPopupClose: this.onMenuWindowClose.bind(this),
		onPopupDestroy: this.onMenuWindowDestroy.bind(this)
	};

	var id = options.compatibleMode === false ? this.getId() : "menu-popup-" + this.getId();
	var popupWindow = new BX.PopupWindow(id, this.bindElement, options);

	if (this.params && this.params.events)
	{
		for (var eventName in this.params.events)
		{
			if (this.params.events.hasOwnProperty(eventName))
			{
				BX.addCustomEvent(popupWindow, eventName, this.params.events[eventName]);
			}
		}
	}

	return popupWindow;
};

/**
 *
 * @returns {BX.PopupWindow}
 */
BX.PopupMenuWindow.prototype.getPopupWindow = function()
{
	return this.popupWindow;
};

BX.PopupMenuWindow.prototype.show = function()
{
	BX.onCustomEvent(this, "onPopupMenuShow", [this]);
	this.getPopupWindow().show();
};

BX.PopupMenuWindow.prototype.close = function()
{
	BX.onCustomEvent(this, "onPopupMenuClose", [this]);
	this.getPopupWindow().close();
};

BX.PopupMenuWindow.prototype.destroy = function()
{
	BX.onCustomEvent(this, "onPopupMenuDestroy", [this]);
	this.getPopupWindow().destroy();
};

BX.PopupMenuWindow.prototype.toggle = function()
{
	if (this.getPopupWindow().isShown())
	{
		this.close();
	}
	else
	{
		this.show();
	}
};

BX.PopupMenuWindow.prototype.getId = function() 
{
	return this.id;
};

BX.PopupMenuWindow.prototype.onMenuWindowClose = function()
{
	for (var i = 0; i < this.menuItems.length; i++)
	{
		var item = this.menuItems[i];
		item.closeSubMenu();
	}
};

BX.PopupMenuWindow.prototype.onMenuWindowDestroy = function()
{
	for (var i = 0; i < this.menuItems.length; i++)
	{
		var item = this.menuItems[i];
		item.destroySubMenu();
	}
};

/**
 * @private
 * @param {Event} event
 * @return {boolean}
 */
BX.PopupMenuWindow.prototype.handleAutoHide = function(event)
{
	return !this.containsTarget(event.target);
};

/**
 *
 * @param {Element} target
 * @return {boolean}
 */
BX.PopupMenuWindow.prototype.containsTarget = function(target)
{
	var el = this.getPopupWindow().getPopupContainer();
	if (this.getPopupWindow().isShown() && (target === el || el.contains(target)))
	{
		return true;
	}

	return this.getMenuItems().some(function(/*BX.PopupMenuItem*/item) {

		return item.getSubMenu() && item.getSubMenu().containsTarget(target);

	});
};

/**
 *
 * @param {BX.PopupMenuWindow} parentMenuWindow
 */
BX.PopupMenuWindow.prototype.setParentMenuWindow = function(parentMenuWindow)
{
	if (parentMenuWindow instanceof BX.PopupMenuWindow)
	{
		this.parentMenuWindow = parentMenuWindow;
	}
};

/**
 *
 * @returns {BX.PopupMenuWindow|null}
 */
BX.PopupMenuWindow.prototype.getParentMenuWindow = function()
{
	return this.parentMenuWindow;
};

BX.PopupMenuWindow.prototype.getRootMenuWindow = function()
{
	var root = null;
	var parent = this.getParentMenuWindow();
	while (parent !== null)
	{
		root = parent;
		parent = parent.getParentMenuWindow();
	}

	return root;
};

/**
 *
 * @param {BX.PopupMenuItem} parentMenuItem
 */
BX.PopupMenuWindow.prototype.setParentMenuItem = function(parentMenuItem)
{
	if (parentMenuItem instanceof BX.PopupMenuItem)
	{
		this.parentMenuItem = parentMenuItem;
	}
};

/**
 *
 * @returns {BX.PopupMenuItem}
 */
BX.PopupMenuWindow.prototype.getParentMenuItem = function()
{
	return this.parentMenuItem;
};

/**
 *
 * @param menuItemJson
 * @param targetItemId
 * @returns {BX.PopupMenuItem}
 */
BX.PopupMenuWindow.prototype.addMenuItem = function(menuItemJson, targetItemId)
{
	var menuItem = this.addMenuItemInternal(menuItemJson, targetItemId);
	if (!menuItem)
	{
		return null;
	}

	var itemLayout = menuItem.getLayout();
	var targetItem = this.getMenuItem(targetItemId);
	if (targetItem !== null)
	{
		var targetLayout = targetItem.getLayout();
		this.itemsContainer.insertBefore(itemLayout.item, targetLayout.item);
	}
	else
	{
		this.itemsContainer.appendChild(itemLayout.item);
	}

	return menuItem;
};

/**
 *
 * @param menuItemJson
 * @param targetItemId
 * @returns {BX.PopupMenuItem}
 */
BX.PopupMenuWindow.prototype.addMenuItemInternal = function(menuItemJson, targetItemId)
{
	if (
		!menuItemJson ||
		(!menuItemJson.delimiter && !BX.type.isNotEmptyString(menuItemJson.text)) ||
		(menuItemJson.id && this.getMenuItem(menuItemJson.id) !== null)
	)
	{
		return null;
	}

	if (BX.type.isNumber(this.params.menuShowDelay))
	{
		menuItemJson.menuShowDelay = this.params.menuShowDelay;
	}

	var menuItem = new BX.PopupMenuItem(menuItemJson);
	menuItem.setMenuWindow(this);

	var position = this.getMenuItemPosition(targetItemId);
	if (position >= 0)
	{
		this.menuItems = BX.util.insertIntoArray(this.menuItems, position, menuItem);
	}
	else
	{
		this.menuItems.push(menuItem);
	}

	return menuItem;
};

BX.PopupMenuWindow.prototype.removeMenuItem = function(itemId)
{
	var item = this.getMenuItem(itemId);
	if (!item)
	{
		return;
	}

	for (var position = 0; position < this.menuItems.length; position++)
	{
		if (this.menuItems[position] === item)
		{
			item.destroySubMenu();
			this.menuItems = BX.util.deleteFromArray(this.menuItems, position);
			break;
		}
	}

	if (!this.menuItems.length)
	{
		var menuWindow = item.getMenuWindow();
		if (menuWindow)
		{
			var parentMenuItem = menuWindow.getParentMenuItem();
			if (parentMenuItem)
			{
				parentMenuItem.destroySubMenu();
			}
			else
			{
				menuWindow.destroy();
			}
		}
	}

	item.layout.item.parentNode.removeChild(item.layout.item);
	item.layout = {
		item: null,
		text: null
	};
};

/**
 *
 * @param itemId
 * @returns {BX.PopupMenuItem}
 */
BX.PopupMenuWindow.prototype.getMenuItem = function(itemId)
{
	for (var i = 0; i < this.menuItems.length; i++)
	{
		if (this.menuItems[i].id && this.menuItems[i].id === itemId)
		{
			return this.menuItems[i];
		}
	}

	return null;
};

/**
 *
 * @returns {BX.PopupMenuItem[]}
 */
BX.PopupMenuWindow.prototype.getMenuItems = function()
{
	return this.menuItems;
};

/**
 *
 * @param itemId
 * @returns {Number}
 */
BX.PopupMenuWindow.prototype.getMenuItemPosition = function(itemId)
{
	if (itemId)
	{
		for (var i = 0; i < this.menuItems.length; i++)
		{
			if (this.menuItems[i].id && this.menuItems[i].id === itemId)
			{
				return i;
			}
		}
	}

	return -1;
};

/**
 *
 * @param {Object} options
 * @param {string} [options.id]
 * @param {string} [options.text]
 * @param {string} [options.title = ""]
 * @param {boolean} [options.disabled = false]
 * @param {string} [options.href = null]
 * @param {string} [options.target = null]
 * @param {string} [options.className = null]
 * @param {boolean} [options.delimiter = false]
 * @param {Number} [options.menuShowDelay = 300]
 * @param {Number} [options.subMenuOffsetX = 4]
 * @param {object} [options.events]
 * @param {object} [options.dataset]
 * @param {function|string} [options.onclick = null]
 * @param {array[]} [options.items = []]
 * @constructor
 */
BX.PopupMenuItem = function(options)
{
	options = options || {};
	this.options = options;

	this.id = options.id || BX.util.getRandomString().toLowerCase();
	this.text = BX.type.isNotEmptyString(options.text) ? options.text : "";
	this.title = BX.type.isNotEmptyString(options.title) ? options.title : "";
	this.delimiter = options.delimiter === true;
	this.href = BX.type.isNotEmptyString(options.href) ? options.href : null;
	this.target = BX.type.isNotEmptyString(options.target) ? options.target : null;
	this.dataset = BX.type.isPlainObject(options.dataset) ? options.dataset : null;
	this.className = BX.type.isNotEmptyString(options.className) ? options.className : null;
	this.menuShowDelay = BX.type.isNumber(options.menuShowDelay) ? options.menuShowDelay : 300;
	this.subMenuOffsetX = BX.type.isNumber(options.subMenuOffsetX) ? options.subMenuOffsetX : 4;
	this._items = BX.type.isArray(options.items) ? options.items : [];
	this.disabled = options.disabled === true;
	this.cacheable = options.cacheable === true;

	/**
	 *
	 * @type {function|string}
	 */
	this.onclick =
		BX.type.isNotEmptyString(options.onclick) || BX.type.isFunction(options.onclick)
			? options.onclick
			: null
	;

	if (BX.type.isPlainObject(options.events))
	{
		for (var eventName in options.events)
		{
			BX.addCustomEvent(this, eventName, options.events[eventName]);
		}
	}

	/**
	 *
	 * @type {BX.PopupMenuWindow}
	 */
	this.menuWindow = null;

	/**
	 *
	 * @type {BX.PopupMenuWindow}
	 */
	this.subMenuWindow = null;

	/**
	 *
	 * @type {{item: Element, text: Element}}
	 */
	this.layout = {
		item: null,
		text: null
	};

	this.getLayout(); //compatibility

	//compatibility
	//now use this.options
	this.events = {};
	this.items = [];
	for (var property in options)
	{
		if (options.hasOwnProperty(property) && typeof(this[property]) === "undefined")
		{
			this[property] = options[property];
		}
	}
};

BX.PopupMenuItem.prototype = {

	getLayout: function()
	{
		if (this.layout.item)
		{
			return this.layout;
		}

		if (this.delimiter)
		{
			if (BX.type.isNotEmptyString(this.text))
			{
				this.layout.item = BX.create("span", {
					props: {
						className: "popup-window-delimiter-section"
					},
					children: [
						(this.layout.text = BX.create("span", {
							props : {
								className: "popup-window-delimiter-text"
							},
							html : this.text
						}))
					]
				});

			}
			else
			{
				this.layout.item = BX.create("span", {
					props: {
						className: "popup-window-delimiter"
					}
				});
			}
		}
		else
		{
			this.layout.item = BX.create(this.href ? "a" : "span", {
				props : {
					className: [
						"menu-popup-item",
						(this.className ? this.className : "menu-popup-no-icon"),
						(this.hasSubMenu() ? "menu-popup-item-submenu" : "")
					].join(" ")
				},

				attrs : {
					title : this.title,
					onclick: BX.type.isString(this.onclick) ? this.onclick : "", // compatibility
					target : this.target ? this.target : ""
				},

				dataset: this.dataset,

				events :
					BX.type.isFunction(this.onclick)
						? { click : BX.delegate(this.onItemClick, this) }
						: null
				,

				children : [
					BX.create("span", { props : { className: "menu-popup-item-icon"} }),
					(this.layout.text = BX.create("span", {
						props : {
							className: "menu-popup-item-text"
						},
						html : this.text
					}))
				]
			});

			if (this.href)
			{
				this.layout.item.href = this.href;
			}

			if (this.isDisabled())
			{
				this.disable();
			}

			BX.bind(this.layout.item, "mouseenter", this.onItemMouseEnter.bind(this));
			BX.bind(this.layout.item, "mouseleave", this.onItemMouseLeave.bind(this));
		}

		return this.layout;
	},

	getContainer: function()
	{
		return this.getLayout().item;
	},

	getTextContainer: function()
	{
		return this.getLayout().text;
	},

	getText: function()
	{
		return this.text;
	},

	setText: function(text)
	{
		if (BX.type.isString(text))
		{
			this.text = text;
			this.getTextContainer().innerHTML = text;
		}
	},

	onItemClick: function(event)
	{
		this.onclick.call(this.menuWindow, event, this); //compatibility
	},

	onItemMouseEnter: function(event)
	{
		if (this.isDisabled())
		{
			return;
		}

		BX.onCustomEvent(this, "onMouseEnter");

		this.clearSubMenuTimeout();

		if (this.hasSubMenu())
		{
			this.subMenuTimeout = setTimeout(function() {
				this.showSubMenu();
			}.bind(this), this.menuShowDelay);
		}
		else
		{
			this.subMenuTimeout = setTimeout(function() {
				this.closeSiblings();
			}.bind(this), this.menuShowDelay);
		}
	},

	onItemMouseLeave: function(event)
	{
		if (this.isDisabled())
		{
			return;
		}

		BX.onCustomEvent(this, "onMouseLeave");

		this.clearSubMenuTimeout();
	},

	clearSubMenuTimeout: function()
	{
		if (this.subMenuTimeout)
		{
			clearTimeout(this.subMenuTimeout);
		}

		this.subMenuTimeout = null;
	},

	hasSubMenu: function()
	{
		return this.subMenuWindow !== null || this._items.length;
	},

	showSubMenu: function()
	{
		this.addSubMenu(this._items);

		if (this.subMenuWindow)
		{
			BX.addClass(this.layout.item, "menu-popup-item-open");

			this.closeSiblings();
			this.closeChildren();

			var popupWindow = this.subMenuWindow.getPopupWindow();
			if (!popupWindow.isShown())
			{
				BX.onCustomEvent(this, "onSubMenuShow");
				popupWindow.show();
			}

			this.adjustSubMenu();
		}
	},

	/**
	 *
	 * @param {object[]} items
	 * @return {BX.PopupMenuWindow|null}
	 */
	addSubMenu: function(items)
	{
		if (this.subMenuWindow !== null || !BX.type.isArray(items) || !items.length)
		{
			return null;
		}

		var rootMenuWindow = this.getMenuWindow().getRootMenuWindow() || this.getMenuWindow();
		var options = rootMenuWindow.params;

		//Override root menu options
		options.autoHide = false;
		options.menuShowDelay = this.menuShowDelay;
		options.cacheable = this.isCacheable();
		options.zIndexAbsolute = this.getMenuWindow().getPopupWindow().getZindex() + 2;
		options.bindOptions = {
			forceTop: true,
			forceLeft: true,
			forceBindPosition: true
		};

		delete options.zIndex;
		delete options.events;
		delete options.angle;
		delete options.overlay;

		this.subMenuWindow = new BX.PopupMenuWindow("popup-submenu-" + this.id, this.layout.item, items, options);
		this.subMenuWindow.setParentMenuWindow(this.getMenuWindow());
		this.subMenuWindow.setParentMenuItem(this);

		BX.addCustomEvent(this.subMenuWindow.getPopupWindow(), "onPopupDestroy", this.handleSubMenuDestroy.bind(this));
		BX.addClass(this.layout.item, "menu-popup-item-submenu");

		return this.subMenuWindow;
	},

	handleSubMenuDestroy: function()
	{
		this.subMenuWindow = null;
	},

	closeSubMenu: function()
	{
		this.clearSubMenuTimeout();

		if (this.subMenuWindow)
		{
			BX.removeClass(this.layout.item, "menu-popup-item-open");

			this.closeChildren();

			var popupWindow = this.subMenuWindow.getPopupWindow();
			if (popupWindow.isShown())
			{
				BX.onCustomEvent(this, "onSubMenuClose");
			}

			this.subMenuWindow.close();
		}
	},

	closeSiblings: function()
	{
		var siblings = this.menuWindow.getMenuItems();
		for (var i = 0; i < siblings.length; i++)
		{
			if (siblings[i] !== this)
			{
				siblings[i].closeSubMenu();
			}
		}
	},

	closeChildren: function()
	{
		if (this.subMenuWindow)
		{
			var children = this.subMenuWindow.getMenuItems();
			for (var i = 0; i < children.length; i++)
			{
				children[i].closeSubMenu();
			}
		}
	},

	destroySubMenu: function()
	{
		if (this.subMenuWindow)
		{
			BX.removeClass(this.layout.item, "menu-popup-item-open menu-popup-item-submenu");
			this.destroyChildren();
			this.subMenuWindow.destroy();

			this.subMenuWindow = null;
			this._items = [];
		}
	},

	destroyChildren: function()
	{
		if (this.subMenuWindow)
		{
			var children = this.subMenuWindow.getMenuItems();
			for (var i = 0; i < children.length; i++)
			{
				children[i].destroySubMenu();
			}
		}
	},

	adjustSubMenu: function()
	{
		if (!this.subMenuWindow || !this.layout.item)
		{
			return;
		}

		var popupWindow = this.subMenuWindow.getPopupWindow();
		var itemRect = this.layout.item.getBoundingClientRect();

		var offsetLeft = itemRect.width + this.subMenuOffsetX;
		var offsetTop = itemRect.height + this.getPopupPadding();
		var angleOffset = itemRect.height / 2 - this.getPopupPadding();
		var anglePosition = "left";

		var popupWidth = popupWindow.getPopupContainer().offsetWidth;
		var popupHeight = popupWindow.getPopupContainer().offsetHeight;
		var popupBottom = itemRect.top + popupHeight;

		var clientWidth = document.documentElement.clientWidth;
		var clientHeight = document.documentElement.clientHeight;

		// let's try to fit a submenu to the browser viewport
		var exceeded = popupBottom - clientHeight;
		if (exceeded > 0)
		{
			var roundOffset = Math.ceil(exceeded / itemRect.height) * itemRect.height;
			if (roundOffset > itemRect.top)
			{
				// it cannot be higher than the browser viewport.
				roundOffset -= Math.ceil((roundOffset - itemRect.top) / itemRect.height) * itemRect.height;
			}

			if (itemRect.bottom > (popupBottom - roundOffset))
			{
				// let's sync bottom boundaries.
				roundOffset -= itemRect.bottom - (popupBottom - roundOffset) + this.getPopupPadding();
			}

			offsetTop += roundOffset;
			angleOffset += roundOffset;
		}


		if ((itemRect.left + offsetLeft + popupWidth) > clientWidth)
		{
			var left = itemRect.left - popupWidth - this.subMenuOffsetX;
			if (left > 0)
			{
				offsetLeft = -popupWidth - this.subMenuOffsetX;
				anglePosition = "right";
			}
		}

		popupWindow.setBindElement(this.layout.item);
		popupWindow.setOffset({ offsetLeft: offsetLeft, offsetTop: -offsetTop });
		popupWindow.setAngle({ position: anglePosition, offset: angleOffset });
		popupWindow.adjustPosition();
	},

	/**
	 *
	 * @returns {Number}
	 */
	getPopupPadding: function() {
		if (!BX.type.isNumber(this.popupPadding))
		{
			if (this.subMenuWindow)
			{
				var menuContainer = this.subMenuWindow.layout.menuContainer;
				this.popupPadding = parseInt(BX.style(menuContainer, "paddingTop"), 10);
			}
			else
			{
				this.popupPadding = 0;
			}
		}

		return this.popupPadding;
	},

	/**
	 *
	 * @returns {BX.PopupMenuWindow|null}
	 */
	getSubMenu: function()
	{
		return this.subMenuWindow;
	},

	getId: function()
	{
		return this.id;
	},

	/**
	 *
	 * @param {BX.PopupMenuWindow} menuWindow
	 */
	setMenuWindow: function(menuWindow)
	{
		this.menuWindow = menuWindow;
	},

	/**
	 *
	 * @returns {BX.PopupMenuWindow}
	 */
	getMenuWindow: function()
	{
		return this.menuWindow;
	},

	getMenuShowDelay: function()
	{
		return this.menuShowDelay;
	},

	enable: function()
	{
		this.disabled = false;
		this.getContainer().classList.remove("menu-popup-item-disabled");
	},

	disable: function()
	{
		this.disabled = true;
		this.closeSubMenu();
		this.getContainer().classList.add("menu-popup-item-disabled");
	},

	/**
	 *
	 * @return {boolean}
	 */
	isDisabled: function()
	{
		return this.disabled;
	},

	setCacheable: function(cacheable)
	{
		this.cacheable = cacheable !== false;
	},

	isCacheable: function()
	{
		return this.cacheable;
	}

};

// TODO: copypaste/update/enhance CSS and images from calendar to MAIN CORE
// this.values = [{ID: 1, NAME : '111', DESCRIPTION: '111', URL: 'href://...'}]

window.BXInputPopup = function(params)
{
	this.id = params.id || 'bx-inp-popup-' + Math.round(Math.random() * 1000000);
	this.handler = params.handler || false;
	this.values = params.values || false;
	this.pInput = params.input;
	this.bValues = !!this.values;
	this.defaultValue = params.defaultValue || '';
	this.openTitle = params.openTitle || '';
	this.className = params.className || '';
	this.noMRclassName = params.noMRclassName || 'ec-no-rm';
	this.emptyClassName = params.noMRclassName || 'ec-label';

	var _this = this;
	this.curInd = false;

	if (this.bValues)
	{
		this.pInput.onfocus = this.pInput.onclick = function(e)
		{
			if (this.value == _this.defaultValue)
			{
				this.value = '';
				this.className = _this.className;
			}
			_this.ShowPopup();
			return BX.PreventDefault(e);
		};
		this.pInput.onblur = function()
		{
			if (_this.bShowed)
				setTimeout(function(){_this.ClosePopup(true);}, 200);
			_this.OnChange();
		};
	}
	else
	{
		this.pInput.className = this.noMRclassName;
		this.pInput.onblur = BX.proxy(this.OnChange, this);
	}
}

BXInputPopup.prototype = {
ShowPopup: function()
{
	if (this.bShowed)
		return;

	var _this = this;
	if (!this.oPopup)
	{
		var
			pRow,
			pWnd = BX.create("DIV", {props:{className: "bxecpl-loc-popup " + this.className}});

		for (var i = 0, l = this.values.length; i < l; i++)
		{
			pRow = pWnd.appendChild(BX.create("DIV", {
				props: {id: 'bxecmr_' + i},
				text: this.values[i].NAME,
				events: {
					mouseover: function(){BX.addClass(this, 'bxecplloc-over');},
					mouseout: function(){BX.removeClass(this, 'bxecplloc-over');},
					click: function()
					{
						var ind = this.id.substr('bxecmr_'.length);
						_this.pInput.value = _this.values[ind].NAME;
						_this.curInd = ind;
						_this.OnChange();
						_this.ClosePopup(true);
					}
				}
			}));

			if (this.values[i].DESCRIPTION)
				pRow.title = this.values[i].DESCRIPTION;
			if (this.values[i].CLASS_NAME)
				BX.addClass(pRow, this.values[i].CLASS_NAME);

			if (this.values[i].URL)
				pRow.appendChild(BX.create('A', {props: {href: this.values[i].URL, className: 'bxecplloc-view', target: '_blank', title: this.openTitle}}));
		}

		this.oPopup = new BX.PopupWindow(this.id, this.pInput, {
			autoHide : true,
			offsetTop : 1,
			offsetLeft : 0,
			lightShadow : true,
			closeByEsc : true,
			content : pWnd
		});

		BX.addCustomEvent(this.oPopup, 'onPopupClose', BX.proxy(this.ClosePopup, this));
	}

	this.oPopup.show();
	this.pInput.select();

	this.bShowed = true;
	BX.onCustomEvent(this, 'onInputPopupShow', [this]);
},

ClosePopup: function(bClosePopup)
{
	this.bShowed = false;

	if (this.pInput.value == '')
		this.OnChange();

	BX.onCustomEvent(this, 'onInputPopupClose', [this]);

	if (bClosePopup === true)
		this.oPopup.close();
},

OnChange: function()
{
	var val = this.pInput.value;
	if (this.bValues)
	{
		if (this.pInput.value == '' || this.pInput.value == this.defaultValue)
		{
			this.pInput.value = this.defaultValue;
			this.pInput.className = this.emptyClassName;
			val = '';
		}
		else
		{
			this.pInput.className = '';
		}
	}

	if (isNaN(parseInt(this.curInd)) || this.curInd !==false && val != this.values[this.curInd].NAME)
		this.curInd = false;
	else
		this.curInd = parseInt(this.curInd);

	BX.onCustomEvent(this, 'onInputPopupChanged', [this, this.curInd, val]);
	if (this.handler && typeof this.handler == 'function')
		this.handler({ind: this.curInd, value: val});
},

Set: function(ind, val, bOnChange)
{
	this.curInd = ind;
	if (this.curInd !== false)
		this.pInput.value = this.values[this.curInd].NAME;
	else
		this.pInput.value = val;

	if (bOnChange !== false)
		this.OnChange();
},

Get: function(ind)
{
	var
		id = false;
	if (typeof ind == 'undefined')
		ind = this.curInd;

	if (ind !== false && this.values[ind])
		id = this.values[ind].ID;
	return id;
},

GetIndex: function(id)
{
	for (var i = 0, l = this.values.length; i < l; i++)
		if (this.values[i].ID == id)
			return i;
	return false;
},

Deactivate: function(bDeactivate)
{
	if (this.pInput.value == '' || this.pInput.value == this.defaultValue)
	{
		if (bDeactivate)
		{
			this.pInput.value = '';
			this.pInput.className = this.noMRclassName;
		}
		else if (this.oEC.bUseMR)
		{
			this.pInput.value = this.defaultValue;
			this.pInput.className = this.emptyClassName;
		}
	}
	this.pInput.disabled = bDeactivate;
}
};

/************** utility *************/

var _escCallbackIndex = -1,
	_escCallback = null;

function _checkEscPressed(zIndex, callback)
{
	if(zIndex === false)
	{
		if(_escCallback && _escCallback.length > 0)
		{
			for(var i=0;i<_escCallback.length; i++)
			{
				_escCallback[i]();
			}

			_escCallback = null;
			_escCallbackIndex = -1;
		}
	}
	else
	{
		if(_escCallback === null)
		{
			_escCallback = [];
			_escCallbackIndex = -1;
			BX.defer(_checkEscPressed)(false);
		}

		if(zIndex > _escCallbackIndex)
		{
			_escCallbackIndex = zIndex;
			_escCallback = [callback];
		}
		else if(zIndex == _escCallbackIndex)
		{
			_escCallback.push(callback)
		}
	}
}


})(window);

