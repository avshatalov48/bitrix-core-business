/* eslint-disable */
(function() {

"use strict";

/**
 * @namespace BX.SidePanel
 */
BX.namespace("BX.SidePanel");

/**
 * @class
 * @param {string} url
 * @param {BX.SidePanel.Options} [options]
 * @constructor
 */
BX.SidePanel.Slider = function(url, options)
{
	options = BX.type.isPlainObject(options) ? options : {};
	this.options = options;

	this.contentCallback = BX.type.isFunction(options.contentCallback) ? options.contentCallback : null;
	this.contentCallbackInvoved = false;
	this.contentClassName = BX.type.isNotEmptyString(options.contentClassName) ? options.contentClassName : null;

	this.url = this.contentCallback ? url : this.refineUrl(url);

	this.offset = null;
	this.hideControls = options.hideControls === true;
	this.width = BX.type.isNumber(options.width) ? options.width : null;
	this.cacheable = options.cacheable !== false;
	this.autoFocus = options.autoFocus !== false;
	this.printable = options.printable === true;
	this.allowChangeHistory = options.allowChangeHistory !== false;
	this.allowChangeTitle = BX.type.isBoolean(options.allowChangeTitle) ? options.allowChangeTitle : null;
	this.allowCrossOrigin = options.allowCrossOrigin === true;
	this.data = new BX.SidePanel.Dictionary(BX.type.isPlainObject(options.data) ? options.data : {});

	this.customLeftBoundary = null;
	this.customRightBoundary = null;
	this.setCustomLeftBoundary(options.customLeftBoundary);
	this.setCustomRightBoundary(options.customRightBoundary);

	this.title = null;
	this.setTitle(options.title);
	/**
	 *
	 * @type {HTMLIFrameElement}
	 */
	this.iframe = null;
	this.iframeSrc = null;
	this.iframeId = null;
	this.requestMethod =
		BX.type.isNotEmptyString(options.requestMethod) && options.requestMethod.toLowerCase() === "post"
			? "post"
			: "get"
	;
	this.requestParams = BX.type.isPlainObject(options.requestParams) ? options.requestParams : {};

	this.opened = false;
	this.hidden = false;
	this.destroyed = false;
	this.loaded = false;
	this.loadedCnt = 0;

	this.minimizing = false;
	this.maximizing = false;

	this.handleFrameKeyDown = this.handleFrameKeyDown.bind(this);
	this.handleFrameFocus = this.handleFrameFocus.bind(this);
	this.handleFrameUnload = this.handleFrameUnload.bind(this);
	this.handlePopupInit = this.handlePopupInit.bind(this);
	this.handleCrossOriginWindowMessage = this.handleCrossOriginWindowMessage.bind(this);

	/**
	 *
	 * @type {{overlay: Element, container: Element, loader: Element, content: Element, label: Element, closeBtn: Element}}
	 */
	this.layout = {
		overlay: null,
		container: null,
		loader: null,
		content: null,
		closeBtn: null,
		printBtn: null
	};

	this.cache = new BX.Cache.MemoryCache();

	this.loader =
		BX.type.isNotEmptyString(options.loader) || BX.type.isElementNode(options.loader)
			? options.loader
			: BX.type.isNotEmptyString(options.typeLoader) ? options.typeLoader : "default-loader"
	;

	this.animation = null;
	this.animationDuration = BX.type.isNumber(options.animationDuration) ? options.animationDuration : 200;
	this.startParams = { translateX: 100, opacity: 0, scale: 0 };
	this.endParams = { translateX: 0, opacity: 40, scale: 100 };
	this.currentParams = null;
	this.overlayAnimation = false;
	this.animationName = 'sliding';
	this.animationOptions = {};

	this.minimizeOptions = null;
	const minimizeOptions = options.minimizeOptions;
	if (
		BX.Type.isPlainObject(minimizeOptions)
		&& BX.Type.isStringFilled(minimizeOptions.entityType)
		&& (BX.Type.isStringFilled(minimizeOptions.entityId) || BX.Type.isNumber(minimizeOptions.entityId))
		&& (BX.Type.isStringFilled(minimizeOptions.url))
	)
	{
		this.minimizeOptions = minimizeOptions;
	}

	this.label = new BX.SidePanel.Label(this, {
		iconClass: 'side-panel-label-icon-close',
		iconTitle: BX.Loc.getMessage('MAIN_SIDEPANEL_CLOSE'),
		onclick: function(label, slider) {
			slider.close();
		}
	});

	var labelOptions = BX.type.isPlainObject(options.label) ? options.label : {};
	this.label.setText(labelOptions.text);
	this.label.setColor(labelOptions.color);
	this.label.setBgColor(labelOptions.bgColor, labelOptions.opacity);

	this.minimizeLabel = null;
	this.newWindowLabel = null;
	this.copyLinkLabel = null;

	if (!this.isSelfContained() && this.minimizeOptions !== null)
	{
		this.minimizeLabel = new BX.SidePanel.Label(this, {
			iconClass: 'side-panel-label-icon-minimize ui-icon-set --arrow-line',
			iconTitle: BX.Loc.getMessage('MAIN_SIDEPANEL_MINIMIZE'),
			bgColor: ['#d9dcdf', 100],
			onclick: (label, slider) => {
				if (this.isLoaded())
				{
					this.minimize();
				}
			},
		});
	}

	if (options.newWindowLabel === true && (!this.isSelfContained() || BX.Type.isStringFilled(options.newWindowUrl)))
	{
		this.newWindowLabel = new BX.SidePanel.Label(this, {
			iconClass: 'side-panel-label-icon-new-window',
			iconTitle: BX.Loc.getMessage('MAIN_SIDEPANEL_NEW_WINDOW'),
			bgColor: ['#d9dcdf', 100],
			onclick: function(label, slider) {
				const url = BX.Type.isStringFilled(options.newWindowUrl) ? options.newWindowUrl : slider.getUrl();
				Object.assign(document.createElement('a'), {
					target: '_blank',
					href: url,
				}).click();
			}
		});
	}

	if (options.copyLinkLabel === true && (!this.isSelfContained() || BX.Type.isStringFilled(options.newWindowUrl)))
	{
		this.copyLinkLabel = new BX.SidePanel.Label(this, {
			iconClass: 'side-panel-label-icon-copy-link',
			iconTitle: BX.Loc.getMessage('MAIN_SIDEPANEL_COPY_LINK'),
			bgColor: ['#d9dcdf', 100],
		});

		BX.clipboard.bindCopyClick(
			this.copyLinkLabel.getIconBox(),
			{
				text: () => {
					if (BX.Type.isStringFilled(options.newWindowUrl))
					{
						return options.newWindowUrl;
					}

					const link = document.createElement('a');
					link.href = this.getUrl();

					return link.href;
				}
			}
		);
	}


	//Compatibility
	if (
		this.url.indexOf("crm.activity.planner/slider.php") !== -1 &&
		options.events &&
		BX.type.isFunction(options.events.onOpen) &&
		options.events.compatibleEvents !== false
	)
	{
		var onOpen = options.events.onOpen;
		delete options.events.onOpen;
		options.events.onLoad = function(event) {
			onOpen(event.getSlider());
		};
	}

	if (options.events)
	{
		for (var eventName in options.events)
		{
			if (BX.type.isFunction(options.events[eventName]))
			{
				BX.addCustomEvent(
					this,
					BX.SidePanel.Slider.getEventFullName(eventName),
					options.events[eventName]
				);
			}
		}
	}
};

/**
 * @public
 * @static
 * @param {string} eventName
 * @returns {string}
 */
BX.SidePanel.Slider.getEventFullName = function(eventName)
{
	return "SidePanel.Slider:" + eventName;
};

BX.SidePanel.Slider.prototype =
{
	/**
	 * @public
	 * @returns {boolean}
	 */
	open: function()
	{
		if (this.isOpen())
		{
			return false;
		}

		if (!this.canOpen())
		{
			return false;
		}

		if (this.isDestroyed())
		{
			return false;
		}

		if (this.maximizing)
		{
			this.fireEvent("onMaximizeStart");
		}

		this.createLayout();
		BX.addClass(this.getOverlay(), "side-panel-overlay-open side-panel-overlay-opening");
		this.adjustLayout();

		BX.ZIndexManager.bringToFront(this.getOverlay());

		this.opened = true;

		this.fireEvent("onOpenStart");

		this.animateOpening();

		return true;
	},

	/**
	 * @public
	 * @param {boolean} [immediately]
	 * @param {function} [callback]
	 * @returns {boolean}
	 */
	close: function(immediately, callback)
	{
		if (!this.isOpen())
		{
			return false;
		}

		if (!this.canClose())
		{
			return false;
		}

		if (this.minimizing)
		{
			this.fireEvent("onMinimizeStart");
		}

		this.fireEvent("onCloseStart");

		this.opened = false;

		if (this.isDestroyed())
		{
			return false;
		}

		if (this.animation)
		{
			this.animation.stop();
		}

		this.fireEvent("onClosing");

		if (immediately === true || BX.browser.IsMobile())
		{
			this.currentParams = this.startParams;
			this.completeAnimation(callback);
		}
		else
		{
			this.animation = new BX.easing({
				duration : this.animationDuration,
				start: this.currentParams,
				finish: this.startParams,
				transition : BX.easing.transitions.linear,
				step: BX.delegate(function(state) {
					this.currentParams = state;
					this.animateStep(state);
				}, this),
				complete: BX.delegate(function() {
					this.completeAnimation(callback);
				}, this)
			});

			// Chrome rendering bug
			this.getContainer().style.opacity = 0.96;

			if (this.animationName === 'scale' && BX.Type.isStringFilled(this.animationOptions.origin))
			{
				this.getContainer().style.transformOrigin = this.animationOptions.origin;
			}

			this.animation.animate();
		}

		return true;
	},

	minimize(immediately, callback)
	{
		this.minimizing = true;

		const success = this.close(immediately, callback);
		if (!success)
		{
			this.minimizing = false;
		}

		return success;
	},

	isMinimizing()
	{
		return this.minimizing;
	},

	maximize()
	{
		this.maximizing = true;
		const success = this.open();
		if (!success)
		{
			this.maximizing = false;
		}

		return success;
	},

	isMaximizing()
	{
		return this.maximizing;
	},

	setAnimation(type, options)
	{
		this.animationName = type === 'scale' ? type : 'sliding';
		this.animationOptions = BX.Type.isPlainObject(options) ? options : {};
	},

	getMinimizeOptions()
	{
		return this.minimizeOptions;
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getUrl: function()
	{
		return this.url;
	},

	setUrl(url)
	{
		if (BX.Type.isStringFilled(url))
		{
			this.url = url;
		}
	},

	focus: function()
	{
		this.getWindow().focus();

		// if (this.isSelfContained())
		// {
		// 	this.getContentContainer().setAttribute("tabindex", "0");
		// 	this.getContentContainer().focus();
		// }
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isOpen: function()
	{
		return this.opened;
	},

	/**
	 * @public
	 * @deprecated
	 * @param {number} zIndex
	 */
	setZindex: function(zIndex)
	{

	},

	/**
	 * @public
	 * @returns {number}
	 */
	getZindex: function()
	{
		var component = BX.ZIndexManager.getComponent(this.getOverlay());

		return component.getZIndex();
	},

	/**
	 * @public
	 * @param {?number} offset
	 */
	setOffset: function(offset)
	{
		if (BX.type.isNumber(offset) || offset === null)
		{
			this.offset = offset;
		}
	},

	/**
	 * @public
	 * @returns {?number}
	 */
	getOffset: function()
	{
		return this.offset;
	},

	/**
	 * @public
	 * @param {number} width
	 */
	setWidth: function(width)
	{
		if (BX.type.isNumber(width))
		{
			this.width = width;
		}
	},

	/**
	 * @public
	 * @returns {number}
	 */
	getWidth: function()
	{
		return this.width;
	},

	/**
	 * @public
	 * @param {string} title
	 */
	setTitle: function(title)
	{
		if (BX.type.isNotEmptyString(title))
		{
			this.title = title;
		}
	},

	/**
	 * @public
	 * @returns {null|string}
	 */
	getTitle: function()
	{
		return this.title;
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Dictionary}
	 */
	getData: function()
	{
		return this.data;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isSelfContained: function()
	{
		return this.contentCallback !== null;
	},

	/**
	 * public
	 * @returns {boolean}
	 */
	isPostMethod: function()
	{
		return this.requestMethod === "post";
	},

	/**
	 * @public
	 * @returns {object}
	 */
	getRequestParams: function()
	{
		return this.requestParams;
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getFrameId: function()
	{
		if (this.iframeId === null)
		{
			this.iframeId = "iframe_" + BX.util.getRandomString(10).toLowerCase();
		}

		return this.iframeId;
	},

	/**
	 * @public
	 * @returns {Window}
	 */
	getWindow: function()
	{
		return this.iframe ? this.iframe.contentWindow : window;
	},

	/**
	 * @public
	 * @returns {Window}
	 */
	getFrameWindow: function()
	{
		return this.iframe ? this.iframe.contentWindow : null;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isHidden: function()
	{
		return this.hidden;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isCacheable: function()
	{
		return this.cacheable;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isFocusable: function()
	{
		return this.autoFocus;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isPrintable: function()
	{
		return this.printable;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isDestroyed: function()
	{
		return this.destroyed;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isLoaded: function()
	{
		return this.loaded;
	},

	canChangeHistory: function()
	{
		return (
			this.allowChangeHistory &&
			!this.isSelfContained() &&
			!this.getUrl().match(/^\/bitrix\/(components|tools)\//i)
		);
	},

	canChangeTitle: function()
	{
		if (this.allowChangeTitle === null)
		{
			if (this.getTitle() !== null)
			{
				return true;
			}

			return this.canChangeHistory();
		}

		return this.allowChangeTitle;
	},

	/**
	 * @public
	 * @param {boolean} [cacheable=true]
	 */
	setCacheable: function(cacheable)
	{
		this.cacheable = cacheable !== false;
	},

	/**
	 * @public
	 * @param {boolean} autoFocus
	 */
	setAutoFocus: function(autoFocus)
	{
		this.autoFocus = autoFocus !== false;
	},

	/**
	 * @public
	 * @param {boolean} printable
	 */
	setPrintable: function(printable)
	{
		this.printable = printable !== false;
		this.printable ? this.showPrintBtn() : this.hidePrintBtn();
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getLoader: function()
	{
		return this.loader;
	},

	/**
	 * @public
	 */
	showLoader: function()
	{
		var loader = this.getLoader();
		if (!this.layout.loader)
		{
			this.createLoader(loader);
		}

		this.layout.loader.style.opacity = 1;
		this.layout.loader.style.display = "block";
	},

	/**
	 * @public
	 */
	closeLoader: function()
	{
		if (this.layout.loader)
		{
			this.layout.loader.style.display = "none";
			this.layout.loader.style.opacity = 0;
		}
	},

	/**
	 * @public
	 */
	showCloseBtn: function()
	{
		this.getLabel().showIcon();
	},

	/**
	 * @public
	 */
	hideCloseBtn: function()
	{
		this.getLabel().hideIcon();
	},

	/**
	 * @public
	 */
	showOrLightenCloseBtn: function()
	{
		if (BX.Type.isStringFilled(this.getLabel().getText()))
		{
			this.getLabel().showIcon();
		}
		else
		{
			this.getLabel().lightenIcon();
		}
	},

	/**
	 * @public
	 */
	hideOrDarkenCloseBtn: function()
	{
		if (BX.Type.isStringFilled(this.getLabel().getText()))
		{
			this.getLabel().hideIcon();
		}
		else
		{
			this.getLabel().darkenIcon();
		}
	},

	/**
	 * @public
	 */
	showPrintBtn: function()
	{
		this.getPrintBtn().classList.add("side-panel-print-visible");
	},

	/**
	 * @public
	 */
	hidePrintBtn: function()
	{
		this.getPrintBtn().classList.remove("side-panel-print-visible");
	},

	showExtraLabels: function()
	{
		this.getExtraLabelsContainer().style.removeProperty('display');
	},

	hideExtraLabels: function()
	{
		this.getExtraLabelsContainer().style.display = 'none';
	},

	/**
	 * @public
	 * @param {string} className
	 */
	setContentClass: function(className)
	{
		if (BX.type.isNotEmptyString(className))
		{
			this.removeContentClass();
			this.contentClassName = className;
			this.getContentContainer().classList.add(className);
		}
	},

	/**
	 * @public
	 */
	removeContentClass: function()
	{
		if (this.contentClassName !== null)
		{
			this.getContentContainer().classList.remove(this.contentClassName);
			this.contentClassName = null;
		}
	},

	/**
	 * @public
	 * @returns {void}
	 */
	applyHacks: function()
	{

	},

	/**
	 * @public
	 * @returns {void}
	 */
	applyPostHacks: function()
	{

	},

	/**
	 * @public
	 * @returns {void}
	 */
	resetHacks: function()
	{

	},

	/**
	 * @public
	 * @returns {void}
	 */
	resetPostHacks: function()
	{

	},

	/**
	 * @public
	 * @returns {number}
	 */
	getTopBoundary: function()
	{
		return 0;
	},

	/**
	 * @protected
	 * @return {number}
	 */
	calculateLeftBoundary: function()
	{
		var customLeftBoundary = this.getCustomLeftBoundary();
		if (customLeftBoundary !== null)
		{
			return customLeftBoundary;
		}

		return this.getLeftBoundary();
	},

	/**
	 * @public
	 * @returns {number}
	 */
	getLeftBoundary: function()
	{
		var windowWidth = BX.browser.IsMobile() ? window.innerWidth : document.documentElement.clientWidth;
		return windowWidth < 1160 ? this.getMinLeftBoundary() : 300;
	},

	/**
	 * @public
	 * @returns {number}
	 */
	getMinLeftBoundary: function()
	{
		return this.hideControls && this.getCustomLeftBoundary() !== null ? 0 : 65;
	},

	/**
	 * @internal
	 * @returns {number}
	 */
	getLeftBoundaryOffset: function()
	{
		var offset = this.getOffset() !== null ? this.getOffset() : 0;

		return Math.max(this.calculateLeftBoundary(), this.getMinLeftBoundary()) + offset;
	},

	/**
	 * @public
	 * @param {number} boundary
	 */
	setCustomLeftBoundary: function(boundary)
	{
		if (BX.type.isNumber(boundary) || boundary === null)
		{
			this.customLeftBoundary = boundary;
		}
	},

	/**
	 * @public
	 * @return {number}
	 */
	getCustomLeftBoundary: function()
	{
		return this.customLeftBoundary;
	},


	/**
	 * @public
	 * @param {number} boundary
	 */
	setCustomRightBoundary: function(boundary)
	{
		if (BX.type.isNumber(boundary) || boundary === null)
		{
			this.customRightBoundary = boundary;
		}
	},

	/**
	 * @public
	 * @return {number}
	 */
	getCustomRightBoundary: function()
	{
		return this.customRightBoundary;
	},

	/**
	 * @protected
	 * @return {number}
	 */
	calculateRightBoundary: function()
	{
		const customRightBoundary = this.getCustomRightBoundary();
		if (customRightBoundary !== null)
		{
			return -window.pageXOffset + customRightBoundary;
		}

		return this.getRightBoundary();
	},

	/**
	 * @public
	 * @returns {number}
	 */
	getRightBoundary: function()
	{
		return -window.pageXOffset;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	destroy: function()
	{
		if (this.isDestroyed())
		{
			return;
		}

		this.firePageEvent("onDestroy");
		this.fireFrameEvent("onDestroy");

		var frameWindow = this.getFrameWindow();
		if (frameWindow && !this.allowCrossOrigin)
		{
			frameWindow.removeEventListener("keydown", this.handleFrameKeyDown);
			frameWindow.removeEventListener("focus", this.handleFrameFocus);
			frameWindow.removeEventListener("unload", this.handleFrameUnload);
		}
		else if (this.allowCrossOrigin)
		{
			window.removeEventListener("message", this.handleCrossOriginWindowMessage);
		}

		BX.Event.EventEmitter.unsubscribe('BX.Main.Popup:onInit', this.handlePopupInit);
		BX.ZIndexManager.unregister(this.layout.overlay);

		BX.remove(this.layout.overlay);

		this.layout.container = null;
		this.layout.overlay = null;
		this.layout.content = null;
		this.layout.closeBtn = null;
		this.layout.printBtn = null;
		this.layout.loader = null;

		this.iframe = null;
		this.destroyed = true;

		if (this.options.events)
		{
			for (var eventName in this.options.events)
			{
				BX.removeCustomEvent(this, BX.SidePanel.Slider.getEventFullName(eventName), this.options.events[eventName]);
			}
		}

		this.firePageEvent("onDestroyComplete");

		return true;
	},

	/**
	 * @private
	 */
	hide: function()
	{
		this.hidden = true;
		this.getContainer().style.display = "none";
		this.getOverlay().style.display = "none";
	},

	/**
	 * @private
	 */
	unhide: function()
	{
		this.hidden = false;
		this.getContainer().style.removeProperty("display");
		this.getOverlay().style.removeProperty("display");
	},

	/**
	 * @public
	 */
	reload: function()
	{
		this.loaded = false;
		if (this.isSelfContained())
		{
			this.contentCallbackInvoved = false;
			this.showLoader();
			this.setContent();
		}
		else
		{
			this.showLoader();
			this.getFrameWindow().location.reload();
		}
	},

	/**
	 * @public
	 */
	adjustLayout: function()
	{
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowHeight = BX.browser.IsMobile() ? window.innerHeight : document.documentElement.clientHeight;

		var topBoundary = this.getTopBoundary();
		var isTopBoundaryVisible = topBoundary - scrollTop > 0;
		topBoundary = isTopBoundaryVisible ? topBoundary : scrollTop;

		var height = isTopBoundaryVisible > 0 ? windowHeight - topBoundary + scrollTop : windowHeight;
		var leftBoundary = this.getLeftBoundaryOffset();
		var rightBoundary = this.calculateRightBoundary();

		this.getOverlay().style.left = window.pageXOffset + "px";
		this.getOverlay().style.top = topBoundary + "px";
		this.getOverlay().style.right = rightBoundary + "px";
		this.getOverlay().style.height = height + "px";

		this.getContainer().style.width = "calc(100% - " + leftBoundary + "px)";
		this.getContainer().style.height = height + "px";

		if (this.getWidth() !== null)
		{
			this.getContainer().style.maxWidth = this.getWidth() + "px";
		}

		this.getLabel().adjustLayout();
	},

	/**
	 * @private
	 */
	createLayout: function()
	{
		if (this.layout.overlay !== null && this.layout.overlay.parentNode)
		{
			return;
		}

		if (this.isSelfContained())
		{
			this.getContentContainer().style.overflow = "auto";
			document.body.appendChild(this.getOverlay());
			this.setContent();

			BX.Event.EventEmitter.subscribe('BX.Main.Popup:onInit', this.handlePopupInit);
		}
		else
		{
			this.getContentContainer().appendChild(this.getFrame());
			document.body.appendChild(this.getOverlay());
			this.setFrameSrc(); //setFrameSrc must be below than appendChild, otherwise POST method fails.
		}

		BX.ZIndexManager.register(this.getOverlay());
	},

	/**
	 * @public
	 * @returns {HTMLIFrameElement}
	 */
	getFrame: function()
	{
		if (this.iframe !== null)
		{
			return this.iframe;
		}

		this.iframe = BX.create("iframe", {
			attrs: {
				"referrerpolicy": this.allowCrossOrigin ? "strict-origin" : false,
				"src": "about:blank",
				"frameborder": "0"
			},
			props: {
				className: "side-panel-iframe",
				name: this.getFrameId(),
				id: this.getFrameId()
			},
			events: {
				load: this.handleFrameLoad.bind(this),
			}
		});

		return this.iframe;
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getOverlay: function()
	{
		if (this.layout.overlay !== null)
		{
			return this.layout.overlay;
		}

		this.layout.overlay = BX.create("div", {
			props: {
				className: "side-panel side-panel-overlay"
			},
			events: {
				mousedown: this.handleOverlayClick.bind(this)
			},
			children: [
				this.getContainer()
			]
		});

		return this.layout.overlay;
	},

	unhideOverlay: function()
	{
		this.getOverlay().classList.remove("side-panel-overlay-hidden");
	},

	hideOverlay: function()
	{
		this.getOverlay().classList.add("side-panel-overlay-hidden");
	},

	hideShadow: function()
	{
		this.getContainer().classList.remove("side-panel-show-shadow");
	},

	showShadow: function()
	{
		this.getContainer().classList.add("side-panel-show-shadow");
	},

	setOverlayAnimation: function(animate)
	{
		if (BX.type.isBoolean(animate))
		{
			this.overlayAnimation = animate;
		}
	},

	getOverlayAnimation: function()
	{
		return this.overlayAnimation;
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getContainer: function()
	{
		if (this.layout.container !== null)
		{
			return this.layout.container;
		}

		this.layout.container = BX.create("div", {
			props: {
				className: "side-panel side-panel-container"
			},
			children:
				this.hideControls
				? [this.getContentContainer()]
				: [this.getContentContainer(), this.getLabelsContainer(), this.getPrintBtn()]
		});

		return this.layout.container;
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getContentContainer: function()
	{
		if (this.layout.content !== null)
		{
			return this.layout.content;
		}

		this.layout.content = BX.create("div", {
			props: {
				className:
					"side-panel-content-container" +
					(this.contentClassName !== null ? " " + this.contentClassName : "")
			}
		});

		return this.layout.content;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getLabelsContainer: function()
	{
		return this.cache.remember('labels-container', function() {
			return BX.create('div', {
					props: {
						className: 'side-panel-labels'
					},
					children: [
						this.getLabel().getContainer(),
						this.getExtraLabelsContainer()
					]
				})
			;
		}.bind(this));
	},

	/**
	 * @returns {Element}
	 */
	getExtraLabelsContainer: function()
	{
		return this.cache.remember('icon-labels', function() {
			return BX.create('div', {
				props: {
					className: 'side-panel-extra-labels'
				},
				children: [
					this.minimizeLabel ? this.minimizeLabel.getContainer() : null,
					this.newWindowLabel ? this.newWindowLabel.getContainer() : null,
					this.copyLinkLabel ? this.copyLinkLabel.getContainer() : null
				]
			});
		}.bind(this));
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getCloseBtn: function()
	{
		return this.getLabel().getIconBox();
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Label}
	 */
	getLabel: function()
	{
		return this.label;
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Label | null}
	 */
	getNewWindowLabel: function()
	{
		return this.newWindowLabel;
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Label | null}
	 */
	getCopyLinkLabel: function()
	{
		return this.copyLinkLabel;
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Label | null}
	 */
	getMinimizeLabel: function()
	{
		return this.minimizeLabel;
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getPrintBtn: function()
	{
		if (this.layout.printBtn !== null)
		{
			return this.layout.printBtn;
		}

		this.layout.printBtn = BX.create("span", {
			props: {
				className: "side-panel-print",
				title: BX.message("MAIN_SIDEPANEL_PRINT")
			},
			events: {
				click: this.handlePrintBtnClick.bind(this)
			}
		});

		return this.layout.printBtn;
	},

	/**
	 * @private
	 */
	setContent: function()
	{
		if (this.contentCallbackInvoved)
		{
			return;
		}

		this.contentCallbackInvoved = true;

		BX.cleanNode(this.getContentContainer());

		var promise = this.contentCallback(this);
		var isPromiseReturned =
				promise &&
				(
					Object.prototype.toString.call(promise) === "[object Promise]" ||
					promise.toString() === "[object BX.Promise]"
				)
		;

		if (!isPromiseReturned)
		{
			promise = Promise.resolve(promise);
		}

		promise.then(
			function(result)
			{
				if (this.isDestroyed())
				{
					return;
				}

				if (BX.type.isPlainObject(result) && BX.type.isNotEmptyString(result.html))
				{
					BX.html(this.getContentContainer(), result.html).then(
						function() {
							this.removeLoader();
							this.loaded = true;
							this.firePageEvent("onLoad");
						}.bind(this),

						function(reason) {
							this.removeLoader();
							this.getContentContainer().innerHTML = reason;
						}.bind(this)
					);
				}
				else
				{
					if (BX.type.isDomNode(result))
					{
						this.getContentContainer().appendChild(result);
					}
					else if (BX.type.isNotEmptyString(result))
					{
						this.getContentContainer().innerHTML = result;
					}

					this.removeLoader();
					this.loaded = true;
					this.firePageEvent("onLoad");
				}
			}.bind(this),
			function(reason)
			{
				this.removeLoader();
				this.getContentContainer().innerHTML = reason;
			}.bind(this)
		);
	},

	/**
	 * @private
	 */
	setFrameSrc: function()
	{
		if (this.iframeSrc === this.getUrl())
		{
			return;
		}

		var url = BX.util.add_url_param(this.getUrl(), { IFRAME: "Y", IFRAME_TYPE: "SIDE_SLIDER" });

		if (this.isPostMethod())
		{
			var form = document.createElement("form");
			form.method = "POST";
			form.action = url;
			form.target = this.getFrameId();
			form.style.display = "none";

			BX.util.addObjectToForm(this.getRequestParams(), form);

			document.body.appendChild(form);

			form.submit();

			BX.remove(form);
		}
		else
		{
			this.iframeSrc = this.getUrl();
			this.iframe.src = url;
		}

		this.loaded = false;
		this.listenIframeLoading();
	},

	/**
	 * @private
	 * @param loader
	 */
	createLoader: function(loader)
	{
		BX.remove(this.layout.loader);

		loader = BX.type.isNotEmptyString(loader) || BX.type.isElementNode(loader) ? loader : "default-loader";

		var oldLoaders = [
			"task-new-loader",
			"task-edit-loader",
			"task-view-loader",
			"crm-entity-details-loader",
			"crm-button-view-loader",
			"crm-webform-view-loader",
			"create-mail-loader",
			"view-mail-loader"
		];

		var matches = null;

		if (BX.type.isElementNode(loader))
		{
			this.layout.loader = this.createHTMLLoader(loader);
		}
		else if (BX.util.in_array(loader, oldLoaders) && this.loaderExists(loader))
		{
			this.layout.loader = this.createOldLoader(loader);
		}
		else if (loader.charAt(0) === "/")
		{
			this.layout.loader = this.createSvgLoader(loader);
		}
		else if (matches = loader.match(/^([a-z0-9-_.]+):([a-z0-9-_.]+)$/i))
		{
			var moduleId = matches[1];
			var svgName = matches[2];
			var svg = "/bitrix/images/" + moduleId + "/slider/" + svgName + ".svg";
			this.layout.loader = this.createSvgLoader(svg);
		}
		else
		{
			loader = "default-loader";
			this.layout.loader = this.createDefaultLoader();
		}

		this.getContainer().appendChild(this.layout.loader);
	},

	createSvgLoader: function(svg)
	{
		return BX.create("div", {
			props: {
				className: "side-panel-loader"
			},
			children: [
				BX.create("div", {
					props: {
						className: "side-panel-loader-container"
					},
					style: {
						backgroundImage: 'url("' + svg +'")'
					}
				})
			]
		});
	},

	createDefaultLoader: function()
	{
		return BX.create("div", {
			props: {
				className: "side-panel-loader"
			},
			children: [
				BX.create("div", {
					props: {
						className: "side-panel-default-loader-container"
					},
					html:
						'<svg class="side-panel-default-loader-circular" viewBox="25 25 50 50">' +
							'<circle ' +
								'class="side-panel-default-loader-path" ' +
								'cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"' +
							'/>' +
						'</svg>'
				})
			]
		});
	},

	/**
	 * @private
	 * @param {string} loader
	 * @returns {Element}
	 */
	createOldLoader: function(loader)
	{
		if (loader === "crm-entity-details-loader")
		{
			return BX.create("div", {
				props: {
					className: "side-panel-loader " + loader
				},
				children: [
					BX.create("img", {
						attrs: {
							src:
								"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BMVEX" +
								"///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC"
						},
						props: {
							className: "side-panel-loader-mask top"
						}
					}),
					BX.create("div", {
						props: {
							className: "side-panel-loader-bg left"
						},
						children: [
							BX.create("img", {
								attrs: {
									src:
										"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1B" +
										"MVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC"
								},
								props: {
									className: "side-panel-loader-mask left"
								}
							})
						]
					}),
					BX.create("div", {
						props: {
							className: "side-panel-loader-bg right"
						},
						children: [
							BX.create("img", {
								attrs: {
									src:
										"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BM" +
										"VEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC"
								},
								props: {
									className: "side-panel-loader-mask right"
								}
							})
						]
					})
				]
			});
		}
		else
		{
			return BX.create("div", {
				props: {
					className: "side-panel-loader " + loader
				},
				children: [
					BX.create("img", {
						attrs: {
							src:
								"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BMVEX" +
								"///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC"
						},
						props: {
							className: "side-panel-loader-mask left"
						}
					}),
					BX.create("img", {
						attrs: {
							src:
								"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA" +
								"1BMVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC"
						},
						props: {
							className: "side-panel-loader-mask right"
						}
					})
				]
			});
		}
	},

	/**
	 * @private
	 * @param {HTMLElement} loader
	 * @returns {Element}
	 */
	createHTMLLoader: function(loader)
	{
		return BX.create("div", {
			children: [
				loader
			]
		});
	},

	loaderExists: function(loader)
	{
		if (!BX.type.isNotEmptyString(loader))
		{
			return false;
		}

		for (var i = 0; i < document.styleSheets.length; i++)
		{
			var style = document.styleSheets[i];
			if (!BX.type.isNotEmptyString(style.href) || style.href.indexOf("sidepanel") === -1)
			{
				continue;
			}

			var rules;
			try
			{
				rules = style.rules || style.cssRules;
			}
			catch (e)
			{
				try
				{
					rules = style.cssRules;
				}
				catch (e)
				{
					rules = [];
				}
			}

			for (var j = 0; j < rules.length; j++)
			{
				var rule = rules[j];
				if (BX.type.isNotEmptyString(rule.selectorText) && rule.selectorText.indexOf(loader) !== -1)
				{
					return true;
				}

			}
		}

		return false;
	},

	/**
	 * @private
	 */
	removeLoader: function()
	{
		BX.remove(this.layout.loader);
		this.layout.loader = null;
	},

	/**
	 * @private
	 */
	animateOpening: function()
	{
		if (this.isPrintable())
		{
			this.showPrintBtn();
		}

		if (this.animation)
		{
			this.animation.stop();
		}

		this.fireEvent("onOpening");

		if (BX.browser.IsMobile())
		{
			this.currentParams = this.endParams;
			this.animateStep(this.currentParams);
			this.completeAnimation();
			return;
		}

		this.currentParams = this.currentParams ? this.currentParams : this.startParams;
		this.animation = new BX.easing({
			duration : this.animationDuration,
			start: this.currentParams,
			finish: this.endParams,
			transition : BX.easing.transitions.linear,
			step: BX.delegate(function(state) {
				this.currentParams = state;
				this.animateStep(state);
			}, this),
			complete: BX.delegate(function() {
				this.completeAnimation();
			}, this)
		});

		if (this.animationName === 'scale' && BX.Type.isStringFilled(this.animationOptions.origin))
		{
			this.getContainer().style.transformOrigin = this.animationOptions.origin;
		}

		this.animation.animate();
	},

	/**
	 * @private
	 * @param {object} state
	 */
	animateStep: function(state)
	{
		if (this.animationName === 'scale')
		{
			this.getContainer().style.transform = "scale(" + state.scale / 100 + ")";
		}
		else
		{
			this.getContainer().style.transform = "translateX(" + state.translateX + "%)";
		}

		if (this.getOverlayAnimation())
		{
			this.getOverlay().style.backgroundColor = "rgba(0, 0, 0, " + state.opacity / 100 + ")";
		}
	},

	/**
	 * @private
	 * @param callback
	 */
	completeAnimation: function(callback)
	{
		this.animation = null;
		if (this.isOpen())
		{
			this.currentParams = this.endParams;
			this.maximizing = false;

			BX.removeClass(this.getOverlay(), "side-panel-overlay-opening");
			if (this.animationName === 'scale')
			{
				this.getContainer().style.removeProperty("transform-origin");
				this.getContainer().style.transform = "translateX(0%)";
			}

			this.firePageEvent("onBeforeOpenComplete");
			this.fireFrameEvent("onBeforeOpenComplete");

			this.firePageEvent("onOpenComplete");
			this.fireFrameEvent("onOpenComplete");

			if (!this.isLoaded())
			{
				this.showLoader();
			}

			if (this.isFocusable())
			{
				this.focus();
			}
		}
		else
		{
			this.currentParams = this.startParams;
			this.minimizing = false;

			BX.removeClass(this.getOverlay(), "side-panel-overlay-open side-panel-overlay-opening");
			if (this.animationName === 'scale')
			{
				this.getContainer().style.removeProperty("transform-origin");
				this.getContainer().style.transform = "translateX(100%)";
			}

			this.getContainer().style.removeProperty("width");
			this.getContainer().style.removeProperty("right");
			this.getContainer().style.removeProperty("opacity");
			this.getContainer().style.removeProperty("max-width");
			this.getContainer().style.removeProperty("min-width");
			this.getCloseBtn().style.removeProperty("opacity");

			this.firePageEvent("onBeforeCloseComplete");
			this.fireFrameEvent("onBeforeCloseComplete");

			this.firePageEvent("onCloseComplete");
			this.fireFrameEvent("onCloseComplete");

			if (BX.type.isFunction(callback))
			{
				callback(this);
			}

			if (!this.isCacheable())
			{
				this.destroy();
			}
		}
	},

	/**
	 * @package
	 * @param eventName
	 * @returns {BX.SidePanel.Event}
	 */
	firePageEvent: function(eventName)
	{
		var event = this.getEvent(eventName);
		if (event === null)
		{
			throw new Error("'eventName' is invalid.");
		}

		BX.onCustomEvent(this, event.getFullName(), [event]);

		//Events for compatibility
		if (BX.util.in_array(eventName, ["onClose", "onOpen"]))
		{
			BX.onCustomEvent("BX.Bitrix24.PageSlider:" + eventName, [this]);
			BX.onCustomEvent("Bitrix24.Slider:" + eventName, [this]);
		}

		return event;
	},

	/**
	 * @package
	 * @param eventName
	 * @returns {BX.SidePanel.Event}
	 */
	fireFrameEvent: function(eventName)
	{
		var event = this.getEvent(eventName);
		if (event === null)
		{
			throw new Error("'eventName' is invalid.");
		}

		if (this.allowCrossOrigin)
		{
			return null;
		}

		var frameWindow = this.getFrameWindow();
		if (frameWindow && frameWindow.BX && frameWindow.BX.onCustomEvent)
		{
			frameWindow.BX.onCustomEvent(this, event.getFullName(), [event]);

			//Events for compatibility
			if (BX.util.in_array(eventName, ["onClose", "onOpen"]))
			{
				frameWindow.BX.onCustomEvent("BX.Bitrix24.PageSlider:" + eventName, [this]);
				frameWindow.BX.onCustomEvent("Bitrix24.Slider:" + eventName, [this]); // Compatibility
			}
		}

		return event;
	},

	fireEvent: function(eventName)
	{
		this.firePageEvent(eventName);
		this.fireFrameEvent(eventName);
	},

	/**
	 * @private
 	 * @param {string|BX.SidePanel.Event} eventName
	 * @returns {BX.SidePanel.Event|null}
	 */
	getEvent: function(eventName)
	{
		var event = null;
		if (BX.type.isNotEmptyString(eventName))
		{
			event = new BX.SidePanel.Event();
			event.setSlider(this);
			event.setName(eventName);
		}
		else if (eventName instanceof BX.SidePanel.Event)
		{
			event = eventName;
		}

		return event;
	},

	/**
	 * @private
	 * @returns {boolean}
	 */
	canOpen: function()
	{
		return this.canAction("open");
	},

	/**
	 * @private
	 * @returns {boolean}
	 */
	canClose: function()
	{
		return this.canAction("close");
	},

	/**
	 * @package
	 * @returns {boolean}
	 */
	canCloseByEsc: function()
	{
		return this.canAction("closeByEsc");
	},

	/**
	 * @private
	 * @param {string} action
	 * @returns {boolean}
	 */
	canAction: function(action)
	{
		if (!BX.type.isNotEmptyString(action))
		{
			return false;
		}

		var eventName = "on" + action.charAt(0).toUpperCase() + action.slice(1);

		var pageEvent = this.firePageEvent(eventName);
		var frameEvent = this.fireFrameEvent(eventName);

		return pageEvent.isActionAllowed() && (!frameEvent || frameEvent.isActionAllowed());
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleCrossOriginWindowMessage: function(event)
	{
		if (this.url.indexOf(event.origin) !== 0)
		{
			return;
		}

		let message = {type: '', data: undefined};
		if (BX.Type.isString(event.data))
		{
			message.type = event.data;
		}
		else if (BX.Type.isPlainObject(event.data))
		{
			message.type = event.data.type;
			message.data = event.data.data;
		}

		if (message.type === 'BX:SidePanel:close')
		{
			this.close();
		}
		else if (message.type === 'BX:SidePanel:load:force')
		{
			if (!this.isLoaded() && !this.isDestroyed())
			{
				this.handleFrameLoad();
			}
		}
		else if (message.type === 'BX:SidePanel:data:send')
		{
			let pageEvent = new BX.SidePanel.MessageEvent({sender: this, data: message.data});
			pageEvent.setName('onXDomainMessage')
			this.firePageEvent(pageEvent);
		}
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameLoad: function(event)
	{
		if (this.loaded)
		{
			return;
		}

		var frameWindow = this.iframe.contentWindow;
		var iframeLocation = frameWindow.location;

		if (this.allowCrossOrigin)
		{
			window.addEventListener("message", this.handleCrossOriginWindowMessage);
		}

		try
		{
			if (iframeLocation.toString() === "about:blank")
			{
				return;
			}
		}
		catch(e)
		{
			if (this.allowCrossOrigin)
			{
				this.loaded = true;
				this.closeLoader();

				return;
			}
			else
			{
				console.warn('SidePanel: Try to use "allowCrossOrigin: true" option.');
				throw e;
			}
		}

		frameWindow.addEventListener("keydown", this.handleFrameKeyDown);
		frameWindow.addEventListener("focus", this.handleFrameFocus);
		frameWindow.addEventListener("unload", this.handleFrameUnload);

		if (BX.browser.IsMobile())
		{
			frameWindow.document.body.style.paddingBottom = window.innerHeight * 2 / 3 + "px";
		}

		var iframeUrl = iframeLocation.pathname + iframeLocation.search + iframeLocation.hash;
		this.iframeSrc = this.refineUrl(iframeUrl);
		this.url = this.iframeSrc;

		if (this.isPrintable())
		{
			this.injectPrintStyles();
		}

		this.loaded = true;
		this.loadedCnt++;

		if (this.loadedCnt > 1)
		{
			this.firePageEvent("onLoad");
			this.fireFrameEvent("onLoad");

			this.firePageEvent("onReload");
			this.fireFrameEvent("onReload");
		}
		else
		{
			this.firePageEvent("onLoad");
			this.fireFrameEvent("onLoad");
		}

		if (this.isFocusable())
		{
			this.focus();
		}

		this.closeLoader();
	},

	/**
	 * @private
	 */
	listenIframeLoading: function()
	{
		if (this.allowCrossOrigin)
		{
			return;
		}

		const isLoaded = setInterval(() => {
			if (this.isLoaded() || this.isDestroyed())
			{
				clearInterval(isLoaded);

				return;
			}

			if (this.iframe.contentWindow.location.toString() === "about:blank")
			{
				return;
			}

			if (
				this.iframe.contentWindow.document.readyState === 'complete'
				|| this.iframe.contentWindow.document.readyState === 'interactive'
			)
			{
				clearInterval(isLoaded);
				this.handleFrameLoad();
			}
		}, 200);
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameUnload: function(event)
	{
		this.loaded = false;
		this.listenIframeLoading();
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameKeyDown: function(event)
	{
		if (event.keyCode !== 27)
		{
			return;
		}

		var popups = BX.findChildren(this.getWindow().document.body, { className: "popup-window" }, false);
		for (var i = 0; i < popups.length; i++)
		{
			var popup = popups[i];
			if (popup.style.display === "block")
			{
				return;
			}
		}

		var centerX = this.getWindow().document.documentElement.clientWidth / 2;
		var centerY = this.getWindow().document.documentElement.clientHeight / 2;
		var element = this.getWindow().document.elementFromPoint(centerX, centerY);

		if (BX.hasClass(element, "bx-core-dialog-overlay") || BX.hasClass(element, "bx-core-window"))
		{
			return;
		}

		if (BX.findParent(element, { className: "bx-core-window" }))
		{
			return;
		}

		this.firePageEvent("onEscapePress");
		this.fireFrameEvent("onEscapePress");
	},

	/**
	 * @private
	 * @param {BaseEvent} event
	 */
	handlePopupInit: function(event)
	{
		var data = event.getCompatData();
		var bindElement = data[1];
		var params = data[2];

		if (!BX.Type.isElementNode(params.targetContainer) && BX.Type.isElementNode(bindElement))
		{
			if (this.getContentContainer().contains(bindElement))
			{
				params.targetContainer = this.getContentContainer();
			}
		}
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameFocus: function(event)
	{
		this.firePageEvent("onFrameFocus");
	},

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handleOverlayClick: function(event)
	{
		if (event.target !== this.getOverlay() || this.animation !== null)
		{
			return;
		}

		this.close();
		event.stopPropagation();
	},

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handlePrintBtnClick: function(event)
	{
		if (this.isSelfContained())
		{
			var frame = document.createElement("iframe");
			frame.src = "about:blank";
			frame.name = "sidepanel-print-frame";
			frame.style.display = "none";
			document.body.appendChild(frame);

			var frameWindow = frame.contentWindow;
			var frameDoc = frameWindow.document;
			frameDoc.open();
			frameDoc.write('<html><head>');

			var headTags = "";
			var links = document.head.querySelectorAll("link, style");
			for (var i = 0; i < links.length; i++)
			{
				var link = links[i];
				headTags += link.outerHTML;
			}

			headTags += "<style>html, body { background: #fff !important; height: 100%; }</style>";

			frameDoc.write(headTags);

			frameDoc.write('</head><body>');
			frameDoc.write(this.getContentContainer().innerHTML);
			frameDoc.write('</body></html>');
			frameDoc.close();

			frameWindow.focus();
			frameWindow.print();

			setTimeout(function() {
				document.body.removeChild(frame);
				window.focus();
			}, 1000);

		}
		else
		{
			this.focus();
			this.getFrameWindow().print();
		}
	},

	/**
	 * @private
	 */
	injectPrintStyles: function()
	{
		var frameDocument = this.getFrameWindow().document;

		var bodyClass = "";

		var classList = frameDocument.body.classList;
		for (var i = 0; i < classList.length; i++)
		{
			var className = classList[i];
			bodyClass += "." + className;
		}

		var bodyStyle = "@media print { body" + bodyClass + " { " +
			"background: #fff !important; " +
			"-webkit-print-color-adjust: exact;" +
			"color-adjust: exact; " +
		"} }";

		var style = frameDocument.createElement("style");
		style.type = "text/css";
		if (style.styleSheet)
		{
			style.styleSheet.cssText = bodyStyle;
		}
		else
		{
			style.appendChild(frameDocument.createTextNode(bodyStyle));
		}

		frameDocument.head.appendChild(style);
	},

	/**
	 * @public
	 * @param {string} url
	 * @returns {string}
	 */
	refineUrl: function(url)
	{
		if (BX.type.isNotEmptyString(url) && url.match(/IFRAME/))
		{
			return BX.util.remove_url_param(url, ["IFRAME", "IFRAME_TYPE"]);
		}

		return url;
	},
};

/**
 *
 * @constructor
 */
BX.SidePanel.Event = function()
{
	this.slider = null;
	this.action = true;
	this.name = null;
};

BX.SidePanel.Event.prototype =
{
	/**
	 * @public
	 */
	allowAction: function()
	{
		this.action = true;
	},

	/**
	 * @public
	 */
	denyAction: function()
	{
		this.action = false;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	isActionAllowed: function()
	{
		return this.action;
	},

	/**
	 * @deprecated use getSlider method
	 * @returns {BX.SidePanel.Slider}
	 */
	getSliderPage: function()
	{
		return this.slider;
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Slider}
	 */
	getSlider: function()
	{
		return this.slider;
	},

	/**
	 * @public
	 * @param {BX.SidePanel.Slider} slider
	 */
	setSlider: function(slider)
	{
		if (slider instanceof BX.SidePanel.Slider)
		{
			this.slider = slider;
		}
	},

	/**
	 *
	 * @returns {string}
	 */
	getName: function()
	{
		return this.name;
	},

	/**
	 *
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
	 *
	 * @returns {string}
	 */
	getFullName: function()
	{
		return BX.SidePanel.Slider.getEventFullName(this.getName());
	}
};

/**
 *
 * @param {object} [options]
 * @param {BX.SidePanel.Slider} [options.sender]
 * @param {object} [options.data]
 * @param {string} [options.name]
 * @param {string} [options.eventId]
 * @param {BX.SidePanel.Slider} [options.slider]
 * @extends {BX.SidePanel.Event}
 * @constructor
 */
BX.SidePanel.MessageEvent = function(options)
{
	BX.SidePanel.Event.apply(this);

	options = BX.type.isPlainObject(options) ? options : {};

	if (!(options.sender instanceof BX.SidePanel.Slider))
	{
		throw new Error("'sender' is not an instance of BX.SidePanel.Slider");
	}

	this.setName("onMessage");
	this.setSlider(options.slider);

	this.sender = options.sender;
	this.data = "data" in options ? options.data : null;
	this.eventId = BX.type.isNotEmptyString(options.eventId) ? options.eventId : null;
};

BX.SidePanel.MessageEvent.prototype =
{
	__proto__: BX.SidePanel.Event.prototype,
	constructor: BX.SidePanel.MessageEvent,

	/**
	 * @public
	 * @returns {BX.SidePanel.Slider|null}
	 */
	getSlider: function()
	{
		return this.slider;
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Slider}
	 */
	getSender: function()
	{
		return this.sender;
	},

	/**
	 * @public
	 * @returns {*}
	 */
	getData: function()
	{
		return this.data;
	},

	/**
	 * @public
	 * @returns {?string}
	 */
	getEventId: function()
	{
		return this.eventId;
	}
};

/**
 *
 * @param {object} [plainObject]
 * @constructor
 */
BX.SidePanel.Dictionary = function(plainObject)
{
	if (plainObject && !BX.type.isPlainObject(plainObject))
	{
		throw new Error("The argument must be a plain object.");
	}

	this.data = plainObject ? plainObject : {};
};

BX.SidePanel.Dictionary.prototype =
{
	/**
	 * @public
	 * @param {string} key
	 * @param {*} value
	 */
	set: function(key, value)
	{
		if (!BX.type.isNotEmptyString(key))
		{
			throw new Error("The 'key' must be a string.")
		}

		this.data[key] = value;
	},

	/**
	 * @public
	 * @param {string} key
	 * @returns {*}
	 */
	get: function(key)
	{
		return this.data[key];
	},

	/**
	 * @public
	 * @param {string} key
	 */
	delete: function(key)
	{
		delete this.data[key];
	},

	/**
	 * @public
	 * @param {string} key
	 * @returns {boolean}
	 */
	has: function(key)
	{
		return key in this.data;
	},

	/**
	 * @public
	 */
	clear: function()
	{
		this.data = {};
	},

	/**
	 * @public
	 * @returns {object}
	 */
	entries: function()
	{
		return this.data;
	}
};

/**
 * @internal
 * @param {BX.SidePanel.Slider} slider
 * @param labelOptions
 * @constructor
 */
BX.SidePanel.Label = function(slider, labelOptions)
{
	/** @type {BX.SidePanel.Slider} */
	this.slider = slider;
	this.color = null;
	this.bgColor = null;
	this.iconClass = '';
	this.iconTitle = '';
	this.onclick = null;
	this.text = null;
	this.cache = new BX.Cache.MemoryCache();

	var options = BX.Type.isPlainObject(labelOptions) ? labelOptions : {};
	this.setBgColor(options.bgColor);
	this.setColor(options.color);
	this.setText(options.text);
	this.setIconClass(options.iconClass);
	this.setIconTitle(options.iconTitle);
	this.setOnclick(options.onclick);
};

BX.SidePanel.Label.MIN_LEFT_OFFSET = 25;
BX.SidePanel.Label.MIN_TOP_OFFSET = 17;
BX.SidePanel.Label.INTERVAL_TOP_OFFSET = 50;

BX.SidePanel.Label.prototype =
{
	/**
	 *
	 * @returns {Element}
	 */
	getContainer: function()
	{
		return this.cache.remember('container', function() {
			return BX.create("div", {
				props: {
					className: "side-panel-label",
				},
				children : [
					this.getIconBox(),
					this.getTextContainer(),
				],
				events: {
					click: this.handleClick.bind(this)
				}
			});
		}.bind(this));
	},

	adjustLayout: function()
	{
		var maxWidth = this.getSlider().getOverlay().offsetWidth - this.getSlider().getContainer().offsetWidth;
		if (maxWidth <= this.getSlider().getMinLeftBoundary())
		{
			this.hideText();
		}
		else
		{
			this.showText();
		}

		this.getContainer().style.maxWidth = (maxWidth - BX.SidePanel.Label.MIN_LEFT_OFFSET) + "px";
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getIconBox: function()
	{
		return this.cache.remember('icon-box', function() {
			return BX.create('div', {
				props: {
					className: 'side-panel-label-icon-box'
				},
				children : [
					this.getIconContainer()
				]
			});
		}.bind(this));
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getIconContainer: function()
	{
		return this.cache.remember('icon-container', function() {
			return BX.create('div', {
				props: {
					className: 'side-panel-label-icon ' + this.getIconClass()
				}
			});
		}.bind(this));
	},

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handleClick: function(event)
	{
		event.stopPropagation();

		var fn = this.getOnclick();
		if (fn)
		{

			fn(this, this.getSlider());
		}
	},

	/**
	 * @public
	 */
	showIcon: function()
	{
		this.getContainer().classList.remove("side-panel-label-icon--hide");
	},

	/**
	 * @public
	 */
	hideIcon: function()
	{
		this.getContainer().classList.add("side-panel-label-icon--hide");
	},

	/**
	 * @public
	 */
	darkenIcon: function()
	{
		this.getContainer().classList.add("side-panel-label-icon--darken");
	},

	/**
	 * @public
	 */
	lightenIcon: function()
	{
		this.getContainer().classList.remove("side-panel-label-icon--darken");
	},

	hideText: function()
	{
		this.getTextContainer().classList.add("side-panel-label-text-hidden");
	},

	showText: function()
	{
		this.getTextContainer().classList.remove("side-panel-label-text-hidden");
	},

	isTextHidden: function()
	{
		return this.getTextContainer().classList.contains("side-panel-label-text-hidden");
	},

	getTextContainer: function()
	{
		return this.cache.remember('text-container', function() {
			return BX.create("span", {
				props: {
					className: "side-panel-label-text"
				}
			});
		}.bind(this));
	},

	setColor: function(color)
	{
		if (BX.type.isNotEmptyString(color))
		{
			this.color = color;
			this.getTextContainer().style.color = color;
		}
	},

	getColor: function()
	{
		return this.color;
	},

	setBgColor: function(bgColor, opacity)
	{
		if (BX.Type.isArray(bgColor))
		{
			opacity = bgColor[1];
			bgColor = bgColor[0];
		}

		if (BX.type.isNotEmptyString(bgColor))
		{
			var matches = bgColor.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/);
			if (matches)
			{
				var hex = matches[1];
				if (hex.length === 3)
				{
					hex = hex.replace(/([a-f0-9])/gi, "$1$1");
				}

				opacity = BX.type.isNumber(opacity) && opacity >= 0 && opacity <= 100 ? opacity : 95;
				var rgb = BX.util.hex2rgb(hex);
				bgColor = "rgba(" + rgb.r + "," + rgb.g + "," + rgb.b + "," +  (opacity / 100) + ")";
			}

			this.bgColor = bgColor;
			this.getContainer().style.backgroundColor = bgColor;
		}
		else if (bgColor === null)
		{
			this.bgColor = bgColor;
			this.getContainer().style.removeProperty("backgroundColor");
		}
	},

	getBgColor: function()
	{
		return this.bgColor;
	},

	setText: function(text)
	{
		if (BX.type.isNotEmptyString(text))
		{
			this.text = text;
			this.getTextContainer().textContent = text;
		}
		else if (text === null)
		{
			this.text = text;
			this.getTextContainer().textContent = "";
		}
	},

	getText: function()
	{
		return this.text;
	},

	setIconClass: function(iconClass)
	{
		if (BX.Type.isStringFilled(iconClass))
		{
			BX.Dom.removeClass(this.getIconContainer(), this.iconClass);
			this.iconClass = iconClass;
			BX.Dom.addClass(this.getIconContainer(), this.iconClass);
		}
		else if (iconClass === null)
		{
			BX.Dom.removeClass(this.getIconContainer(), this.iconClass);
			this.iconClass = iconClass;
		}
	},

	getIconClass: function()
	{
		return this.iconClass;
	},

	setIconTitle: function(iconTitle)
	{
		if (BX.Type.isStringFilled(iconTitle) || iconTitle === null)
		{
			BX.Dom.attr(this.getIconBox(), 'title', iconTitle);
			this.iconTitle = iconTitle;
		}
	},

	getIconTitle: function()
	{
		return this.iconTitle;
	},

	setOnclick: function(fn)
	{
		if (BX.Type.isFunction(fn) || fn === null)
		{
			this.onclick = fn;
		}
	},

	getOnclick: function()
	{
		return this.onclick;
	},

	/**
	 *
	 * @returns {BX.SidePanel.Slider}
	 */
	getSlider: function()
	{
		return this.slider;
	},

	moveAt: function(position)
	{
		if (BX.type.isNumber(position) && position >= 0)
		{
			this.getSlider().getLabelsContainer().style.top =
				BX.SidePanel.Label.MIN_TOP_OFFSET + (position * BX.SidePanel.Label.INTERVAL_TOP_OFFSET) + "px";
		}
	},
};

})();
