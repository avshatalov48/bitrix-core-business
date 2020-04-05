(function() {

"use strict";

/**
 * @typedef {object} BX.SidePanel.Options
 * @property {function} [contentCallback]
 * @property {number} [width]
 * @property {boolean} [cacheable=true]
 * @property {boolean} [autoFocus=true]
 * @property {boolean} [printable=true]
 * @property {boolean} [allowChangeHistory=true]
 * @property {string} [requestMethod]
 * @property {object} [requestParams]
 * @property {string} [loader]
 * @property {object} [data]
 * @property {string} [typeLoader] - option for compatibility
 * @property {number} [animationDuration]
 * @property {?object.<string, function>} [events]
 */

/**
 * @typedef {object} BX.SidePanel.Link
 * @property {string} href - URL
 * @property {string} target - Target Attribute
 * @property {Element} anchor - Dom Node
 * @property {array} [matches] - RegExp Matches
 */

/**
 * @typedef {object} BX.SidePanel.Rule
 * @property {string[]|RegExp[]} condition
 * @property {string[]} [stopParameters]
 * @property {function} [handler]
 * @property {function} [validate]
 * @property {boolean} [allowCrossDomain=false]
 * @property {boolean} [mobileFriendly=false]
 * @property {BX.SidePanel.Options} [options]
 */

/**
 * @namespace BX.SidePanel
 */
BX.namespace("BX.SidePanel");

var instance = null;

/**
 * @memberOf BX.SidePanel
 * @name BX.SidePanel#Instance
 * @type BX.SidePanel.Manager
 * @static
 * @readonly
 */
Object.defineProperty(BX.SidePanel, "Instance", {
	enumerable: false,
	get: function() {

		if (window.top !== window)
		{
			return window.top.BX.SidePanel.Instance;
		}

		if (instance === null)
		{
			instance = new BX.SidePanel.Manager({});
		}

		return instance;
	}
});

/**
 * @class BX.SidePanel.Manager
 * @param {object} options
 * @constructor
 */
BX.SidePanel.Manager = function(options)
{
	this.anchorRules = [];
	this.anchorHandler = null;

	this.openSliders = [];
	this.lastOpenSlider = null;

	this.opened = false;
	this.hidden = false;
	this.hacksApplied = false;

	this.pageUrl = this.getCurrentUrl();

	this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);
	this.handleWindowResize = BX.throttle(this.handleWindowResize, 300, this);
	this.handleWindowScroll = this.handleWindowScroll.bind(this);
	this.handleTouchMove = this.handleTouchMove.bind(this);

	this.handleSliderOpen = this.handleSliderOpen.bind(this);
	this.handleSliderOpenComplete = this.handleSliderOpenComplete.bind(this);
	this.handleSliderClose = this.handleSliderClose.bind(this);
	this.handleSliderCloseComplete = this.handleSliderCloseComplete.bind(this);
	this.handleSliderLoad = this.handleSliderLoad.bind(this);
	this.handleSliderDestroy = this.handleSliderDestroy.bind(this);

	BX.addCustomEvent("SidePanel:open", this.open.bind(this));
	BX.addCustomEvent("SidePanel:close", this.close.bind(this));
	BX.addCustomEvent("SidePanel:closeAll", this.closeAll.bind(this));
	BX.addCustomEvent("SidePanel:destroy", this.destroy.bind(this));
	BX.addCustomEvent("SidePanel:hide", this.hide.bind(this));
	BX.addCustomEvent("SidePanel:unhide", this.unhide.bind(this));

	BX.addCustomEvent("SidePanel:postMessage", this.postMessage.bind(this));
	BX.addCustomEvent("SidePanel:postMessageAll", this.postMessageAll.bind(this));
	BX.addCustomEvent("SidePanel:postMessageTop", this.postMessageTop.bind(this));

	BX.addCustomEvent("BX.Bitrix24.PageSlider:close", this.close.bind(this)); //Compatibility
	BX.addCustomEvent("Bitrix24.Slider:postMessage", this.handlePostMessageCompatible.bind(this)); // Compatibility
};

var sliderClassName = null;

/**
 * @static
 * @param {string} className
 */
BX.SidePanel.Manager.registerSliderClass = function(className)
{
	if (BX.type.isNotEmptyString(className))
	{
		sliderClassName = className;
	}
};

/**
 * @static
 * @returns {BX.SidePanel.Slider}
 */
BX.SidePanel.Manager.getSliderClass = function()
{
	var sliderClass = sliderClassName !== null ? BX.getClass(sliderClassName) : null;
	return sliderClass !== null ? sliderClass : BX.SidePanel.Slider;
};

BX.SidePanel.Manager.prototype =
{
	/**
	 * @public
	 * @param {string} url
	 * @param {BX.SidePanel.Options} [options]
	 */
	open: function(url, options)
	{
		if (!BX.type.isNotEmptyString(url))
		{
			return false;
		}

		url = this.refineUrl(url);

		if (this.isHidden())
		{
			this.unhide();
		}

		var topSlider = this.getTopSlider();
		if (topSlider)
		{
			if (topSlider.isOpen() && topSlider.getUrl() === url)
			{
				return false;
			}
		}

		var slider = null;
		if (this.getLastOpenSlider() && this.getLastOpenSlider().getUrl() === url)
		{
			slider = this.getLastOpenSlider();
		}
		else
		{
			var sliderClass = BX.SidePanel.Manager.getSliderClass();
			slider = new sliderClass(url, options);

			var zIndex = topSlider ? topSlider.getZindex() + 1 : slider.getZindex();
			var offset = topSlider ? Math.min(topSlider.getOffset() + this.getMinOffset(), this.getMaxOffset()) : 0;

			slider.setZindex(zIndex);
			slider.setOffset(offset);

			BX.addCustomEvent(slider, "SidePanel.Slider:onOpen", this.handleSliderOpen);
			BX.addCustomEvent(slider, "SidePanel.Slider:onBeforeOpenComplete", this.handleSliderOpenComplete);
			BX.addCustomEvent(slider, "SidePanel.Slider:onClose", this.handleSliderClose);
			BX.addCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.handleSliderCloseComplete);
			BX.addCustomEvent(slider, "SidePanel.Slider:onLoad", this.handleSliderLoad);
			BX.addCustomEvent(slider, "SidePanel.Slider:onDestroy", this.handleSliderDestroy);
		}

		if (!this.isOpen())
		{
			this.applyHacks(slider);
		}

		var success = slider.open();
		if (!success)
		{
			this.resetHacks(slider);
		}

		return success;
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
	 * @param {boolean} [immediately]
	 * @param {function} [callback]
	 */
	close: function(immediately, callback)
	{
		var topSlider = this.getTopSlider();
		if (topSlider)
		{
			topSlider.close(immediately, callback);
		}
	},

	/**
	 * @public
	 * @param {boolean} [immediately]
	 */
	closeAll: function(immediately)
	{
		var openSliders = this.getOpenSliders();
		for (var i = openSliders.length - 1; i >= 0; i--)
		{
			var slider = openSliders[i];
			var success = slider.close(immediately);
			if (!success)
			{
				break;
			}
		}
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	hide: function()
	{
		if (this.hidden)
		{
			return false;
		}

		var topSlider = this.getTopSlider();

		this.getOpenSliders().forEach(function(slider) {
			slider.hide();
		});

		this.hidden = true;

		this.resetHacks(topSlider);

		return true;
	},

	/**
	 * @public
	 * @returns {boolean}
	 */
	unhide: function()
	{
		if (!this.hidden)
		{
			return false;
		}

		this.getOpenSliders().forEach(function(slider) {
			slider.unhide();
		});

		this.hidden = false;

		setTimeout(function() {
			this.applyHacks(this.getTopSlider());
		}.bind(this), 0);

		return true;
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
	 * @param {string} url
	 */
	destroy: function(url)
	{
		if (!BX.type.isNotEmptyString(url))
		{
			return;
		}

		url = this.refineUrl(url);
		var sliderToDestroy = this.getSlider(url);

		if (this.getLastOpenSlider() && (sliderToDestroy || this.getLastOpenSlider().getUrl() === url))
		{
			this.getLastOpenSlider().destroy();
		}

		if (sliderToDestroy !== null)
		{
			var openSliders = this.getOpenSliders();
			for (var i = openSliders.length - 1; i >= 0; i--)
			{
				var slider = openSliders[i];
				slider.destroy();

				if (slider === sliderToDestroy)
				{
					break;
				}
			}
		}
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Slider|null}
	 */
	getTopSlider: function()
	{
		var count = this.openSliders.length;
		return this.openSliders[count - 1] ? this.openSliders[count - 1] : null;
	},

	getPreviousSlider: function(currentSlider)
	{
		var previousSlider = null;
		var openSliders = this.getOpenSliders();
		currentSlider = currentSlider || this.getTopSlider();

		for (var i = openSliders.length - 1; i >= 0; i--)
		{
			var slider = openSliders[i];
			if (slider === currentSlider)
			{
				previousSlider = openSliders[i - 1] ? openSliders[i - 1] : null;
				break;
			}
		}

		return previousSlider;
	},

	/**
	 * @public
	 * @param {string} url
	 * @returns {BX.SidePanel.Slider}
	 */
	getSlider: function(url)
	{
		url = this.refineUrl(url);

		var openSliders = this.getOpenSliders();
		for (var i = 0; i < openSliders.length; i++)
		{
			var slider = openSliders[i];
			if (slider.getUrl() === url)
			{
				return slider;
			}
		}

		return null;
	},

	/**
	 * @public
	 * @param {Window} window
	 */
	getSliderByWindow: function(window)
	{
		var openSliders = this.getOpenSliders();
		for (var i = 0; i < openSliders.length; i++)
		{
			var slider = openSliders[i];
			if (slider.getFrameWindow() === window)
			{
				return slider;
			}
		}

		return null;
	},

	/**
	 * @public
	 * @returns {BX.SidePanel.Slider[]}
	 */
	getOpenSliders: function()
	{
		return this.openSliders;
	},

	/**
	 * @public
	 * @returns {Number}
	 */
	getOpenSlidersCount: function()
	{
		return this.openSliders.length;
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} slider
	 */
	addOpenSlider: function(slider)
	{
		if (!(slider instanceof BX.SidePanel.Slider))
		{
			throw new Error("Slider is not an instance of BX.SidePanel.Slider");
		}

		this.openSliders.push(slider);
	},

	/**
	 * @private
	 * @returns {boolean}
	 */
	removeOpenSlider: function(slider)
	{
		var openSliders = this.getOpenSliders();
		for (var i = 0; i < openSliders.length; i++)
		{
			var openSlider = openSliders[i];
			if (openSlider === slider)
			{
				this.openSliders.splice(i, 1);
				return true;
			}
		}

		return false;
	},

	/**
	 * @public
	 * @returns {null|BX.SidePanel.Slider}
	 */
	getLastOpenSlider: function()
	{
		return this.lastOpenSlider;
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} slider
	 */
	setLastOpenSlider: function(slider)
	{
		if (this.lastOpenSlider !== slider)
		{
			if (this.lastOpenSlider)
			{
				this.lastOpenSlider.destroy();
			}

			this.lastOpenSlider = slider;
		}
	},

	/**
	 * @private
	 */
	resetLastOpenSlider: function()
	{
		if (this.lastOpenSlider && this.getTopSlider() !== this.lastOpenSlider)
		{
			this.lastOpenSlider.destroy();
		}

		this.lastOpenSlider = null;
	},

	/**
	 * @public
	 */
	adjustLayout: function()
	{
		this.getOpenSliders().forEach(function(/*BX.SidePanel.Slider*/slider) {
			slider.adjustLayout();
		});
	},

	/**
	 * @public
	 * @param {string} url
	 * @returns {string}
	 */
	refineUrl: function(url)
	{
		return BX.util.remove_url_param(url, ["IFRAME", "IFRAME_TYPE"]);
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getPageUrl: function()
	{
		return this.pageUrl;
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getCurrentUrl: function()
	{
		return window.location.pathname + window.location.search + window.location.hash;
	},

	/**
	 * @public
	 * @param {string|Window|BX.SidePanel.Slider} source
	 * @param {string} eventId
	 * @param {object} data
	 */
	postMessage: function(source, eventId, data)
	{
		var sender = this.getSliderFromSource(source);
		if (!sender)
		{
			return;
		}

		var previousSlider = null;
		var openSliders = this.getOpenSliders();
		for (var i = openSliders.length - 1; i >= 0; i--)
		{
			var slider = openSliders[i];
			if (slider === sender)
			{
				previousSlider = openSliders[i - 1] ? openSliders[i - 1] : null;
				break;
			}
		}

		var sliderWindow = previousSlider && previousSlider.getWindow() || window;
		sliderWindow.BX.onCustomEvent("Bitrix24.Slider:onMessage", [slider, data]); //Compatibility

		var event = new BX.SidePanel.MessageEvent({
			sender: sender,
			slider: previousSlider ? previousSlider : null,
			data: data,
			eventId: eventId
		});

		if (previousSlider)
		{
			previousSlider.firePageEvent(event);
			previousSlider.fireFrameEvent(event);
		}
		else
		{
			BX.onCustomEvent(window, event.getFullName(), [event]);
		}
	},

	/**
	 * @public
	 * @param {string|Window|BX.SidePanel.Slider} source
	 * @param {string} eventId
	 * @param {object} data
	 */
	postMessageAll: function(source, eventId, data)
	{
		var sender = this.getSliderFromSource(source);
		if (!sender)
		{
			return;
		}

		var event = null;
		var openSliders = this.getOpenSliders();
		for (var i = openSliders.length - 1; i >= 0; i--)
		{
			var slider = openSliders[i];
			if (slider === sender)
			{
				continue;
			}

			event = new BX.SidePanel.MessageEvent({
				sender: sender,
				slider: slider,
				data: data,
				eventId: eventId
			});

			slider.firePageEvent(event);
			slider.fireFrameEvent(event)
		}

		event = new BX.SidePanel.MessageEvent({
			sender: sender,
			slider: null,
			data: data,
			eventId: eventId
		});

		BX.onCustomEvent(window, event.getFullName(), [event]);
	},

	/**
	 * @public
	 * @param {string|Window|BX.SidePanel.Slider} source
	 * @param {string} eventId
	 * @param {object} data
	 */
	postMessageTop: function(source, eventId, data)
	{
		var sender = this.getSliderFromSource(source);
		if (!sender)
		{
			return;
		}

		var event = new BX.SidePanel.MessageEvent({
			sender: sender,
			slider: null,
			data: data,
			eventId: eventId
		});

		BX.onCustomEvent(window, event.getFullName(), [event]);
	},

	/**
	 * @private
	 * @returns {number}
	 */
	getMinOffset: function()
	{
		return 63;
	},

	/**
	 * @private
	 * @returns {number}
	 */
	getMaxOffset: function()
	{
		return this.getMinOffset() * 3;
	},

	/**
	 * @public
	 * @param {{rules: BX.SidePanel.Rule[]}} parameters
	 */
	bindAnchors: function(parameters)
	{
		parameters = parameters || {};

		if (BX.type.isArray(parameters.rules))
		{
			this.anchorRules = this.anchorRules.concat(parameters.rules);
		}

		if (!this.anchorHandler)
		{
			this.anchorHandler = this.handleAnchorClick.bind(this);
			window.document.addEventListener("click", this.anchorHandler, true);
		}
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderOpen: function(event)
	{
		if (!event.isActionAllowed())
		{
			return;
		}

		var slider = event.getSlider();

		if (this.getTopSlider())
		{
			this.getTopSlider().hideOverlay();
			this.getTopSlider().hideCloseBtn();
			this.getTopSlider().hidePrintBtn();
		}
		else
		{
			slider.setOverlayAnimation(true);
		}

		this.addOpenSlider(slider);
		this.losePageFocus();

		if (!this.opened)
		{
			this.pageUrl = this.getCurrentUrl();
		}

		this.opened = true;

		this.resetLastOpenSlider();
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderOpenComplete: function(event)
	{
		this.setBrowserHistory(event.getSlider());
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderClose: function(event)
	{
		var previousSlider = this.getPreviousSlider();
		var topSlider = this.getTopSlider();

		if (previousSlider)
		{
			previousSlider.unhideOverlay();
			topSlider && topSlider.hideOverlay();
		}
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderCloseComplete: function(event)
	{
		var slider = event.getSlider();
		if (slider === this.getTopSlider())
		{
			this.setLastOpenSlider(slider);
		}

		this.cleanUpClosedSlider(slider);
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderDestroy: function(event)
	{
		var slider = event.getSlider();

		BX.removeCustomEvent(slider, "SidePanel.Slider:onOpen", this.handleSliderOpen);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onBeforeOpenComplete", this.handleSliderOpenComplete);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.handleSliderCloseComplete);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onLoad", this.handleSliderLoad);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onDestroy", this.handleSliderDestroy);

		if (slider === this.getLastOpenSlider())
		{
			this.lastOpenSlider = null;
		}

		this.cleanUpClosedSlider(slider);
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} slider
	 */
	cleanUpClosedSlider: function(slider)
	{
		this.removeOpenSlider(slider);

		slider.unhideOverlay();

		if (this.getTopSlider())
		{
			this.getTopSlider().showCloseBtn();
			if (this.getTopSlider().isPrintable())
			{
				this.getTopSlider().showPrintBtn();
			}
			this.getTopSlider().focus();
		}
		else
		{
			window.focus();
		}

		if (!this.getOpenSlidersCount())
		{
			this.resetHacks(slider);
			this.opened = false;
		}

		this.resetBrowserHistory();
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderLoad: function(event)
	{
		var frameWindow = event.getSlider().getFrameWindow();
		if (frameWindow)
		{
			frameWindow.document.addEventListener("click", this.handleAnchorClick.bind(this), true);
		}

		this.setBrowserHistory(event.getSlider());
	},

	/**
	 * @private
	 * @param {string|Window|BX.SidePanel.Slider} source
	 * @param {object} data
	 */
	handlePostMessageCompatible: function(source, data)
	{
		this.postMessage(source, "", data);
	},

	/**
	 * @private
	 * @param {string|Window|BX.SidePanel.Slider} source
	 */
	getSliderFromSource: function(source)
	{
		if (source instanceof BX.SidePanel.Slider)
		{
			return source;
		}
		else if (BX.type.isNotEmptyString(source))
		{
			return this.getSlider(source);
		}
		else if (source !== null && source === source.window && window !== source)
		{
			return this.getSliderByWindow(source);
		}

		return null;
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} [slider]
	 * @returns {boolean}
	 */
	applyHacks: function(slider)
	{
		if (this.hacksApplied)
		{
			return false;
		}

		slider && slider.applyHacks();

		this.disablePageScrollbar();
		this.bindEvents();

		slider && slider.applyPostHacks();

		this.hacksApplied = true;

		return true;
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} [slider]
	 * @returns {boolean}
	 */
	resetHacks: function(slider)
	{
		if (!this.hacksApplied)
		{
			return false;
		}

		slider && slider.resetPostHacks();

		this.enablePageScrollbar();
		this.unbindEvents();

		slider && slider.resetHacks();

		this.hacksApplied = false;

		return true;
	},

	/**
	 * @private
	 */
	bindEvents: function()
	{
		BX.bind(document, "keydown", this.handleDocumentKeyDown);
		BX.bind(window, "resize", this.handleWindowResize);
		BX.bind(window, "scroll", this.handleWindowScroll); //Live Comments can change scrollTop

		if (BX.browser.IsMobile())
		{
			BX.bind(document.body, "touchmove", this.handleTouchMove);
		}
	},

	/**
	 * @private
	 */
	unbindEvents: function()
	{
		BX.unbind(document, "keydown", this.handleDocumentKeyDown);
		BX.unbind(window, "resize", this.handleWindowResize);
		BX.unbind(window, "scroll", this.handleWindowScroll);

		if (BX.browser.IsMobile())
		{
			BX.unbind(document.body, "touchmove", this.handleTouchMove);
		}
	},

	/**
	 * @private
	 */
	disablePageScrollbar: function()
	{
		var scrollWidth = window.innerWidth - document.documentElement.clientWidth;
		document.body.style.paddingRight = scrollWidth + "px";
		BX.addClass(document.body, "side-panel-disable-scrollbar");
	},

	/**
	 * @private
	 */
	enablePageScrollbar: function()
	{
		document.body.style.removeProperty("padding-right");
		BX.removeClass(document.body, "side-panel-disable-scrollbar");
	},

	/**
	 * @private
	 */
	losePageFocus: function()
	{
		if (BX.type.isDomNode(document.activeElement))
		{
			document.activeElement.blur();
		}
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleDocumentKeyDown: function(event)
	{
		if (event.keyCode !== 27)
		{
			return;
		}

		event.preventDefault(); //otherwise an iframe loading can be cancelled by a browser

		if (this.isOnTop() && this.getTopSlider())
		{
			if (this.getTopSlider().canCloseByEsc())
			{
				this.getTopSlider().close();
			}
		}
	},

	/**
	 * @private
	 */
	handleWindowResize: function()
	{
		this.adjustLayout();
	},

	/**
	 * @private
	 */
	handleWindowScroll: function()
	{
		this.adjustLayout();
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleTouchMove: function(event)
	{
		event.preventDefault();
	},

	/**
	 * @private
	 * @returns {boolean}
	 */
	isOnTop: function()
	{
		//Photo Slider or something else can cover Side Panel.
		var centerX = document.documentElement.clientWidth / 2;
		var centerY = document.documentElement.clientHeight / 2;
		var element = document.elementFromPoint(centerX, centerY);

		return BX.hasClass(element, "side-panel") || BX.findParent(element, { className: "side-panel" }) !== null;
	},

	/**
	 * @private
	 * @param {MouseEvent} event
	 * @returns {BX.SidePanel.Link|null} link
	 */
	extractLinkFromEvent: function(event)
	{
		event = event || window.event;
		var target = event.target;

		if (event.which !== 1 || !BX.type.isDomNode(target) || event.ctrlKey || event.metaKey)
		{
			return null;
		}

		var a = target;
		if (target.nodeName !== "A")
		{
			a = BX.findParent(target, { tag: "A" }, 1);
		}

		if (!BX.type.isDomNode(a))
		{
			return null;
		}

		// do not use a.href here, the code will fail on links like <a href="#SG13"></a>
		var href = a.getAttribute("href");
		if (href)
		{
			return {
				url: href,
				anchor: a,
				target: a.getAttribute("target")
			};
		}

		return null;
	},

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handleAnchorClick: function(event)
	{
		var link = this.extractLinkFromEvent(event);
		if (!link || BX.data(link.anchor, "slider-ignore-autobinding"))
		{
			return;
		}

		var rule = this.getUrlRule(link.url, link);
		if (!rule)
		{
			return;
		}

		if (rule.allowCrossDomain !== true && BX.ajax.isCrossDomain(link.url))
		{
			return;
		}

		if (rule.mobileFriendly !== true && BX.browser.IsMobile())
		{
			return;
		}

		var isValidLink = BX.type.isFunction(rule.validate) ? rule.validate(link) : this.isValidLink(link);
		if (!isValidLink)
		{
			return;
		}

		if (BX.type.isFunction(rule.handler))
		{
			rule.handler(event, link);
		}
		else
		{
			event.preventDefault();

			var options = BX.type.isPlainObject(rule.options) ? rule.options : {};
			if (!BX.type.isNotEmptyString(options.loader) && BX.type.isNotEmptyString(rule.loader))
			{
				options.loader = rule.loader;
			}

			this.open(link.url, options);
		}
	},

	/**
	 * @private
	 * @param {string} href
	 * @param {BX.SidePanel.Link} [link]
	 * @returns {BX.SidePanel.Rule|null}
	 */
	getUrlRule: function(href, link)
	{
		if (!BX.type.isNotEmptyString(href))
		{
			return null;
		}

		for (var k = 0; k < this.anchorRules.length; k++)
		{
			var rule = this.anchorRules[k];

			if (!BX.type.isArray(rule.condition))
			{
				continue;
			}

			for (var m = 0; m < rule.condition.length; m++)
			{
				if (BX.type.isString(rule.condition[m]))
				{
					rule.condition[m] = new RegExp(rule.condition[m], "i");
				}

				var matches = href.match(rule.condition[m]);
				if (matches && !this.hasStopParams(href, rule.stopParameters))
				{
					if (link)
					{
						link.matches = matches;
					}

					return rule;
				}
			}
		}

		return null;
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Link} link
	 * @returns {boolean}
	 */
	isValidLink: function(link)
	{
		return true;
		// return link.target !== "_blank" && link.target !== "_top";
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} slider
	 */
	setBrowserHistory: function(slider)
	{
		if (!(slider instanceof BX.SidePanel.Slider))
		{
			return;
		}

		if (slider.canChangeHistory() && slider.isOpen() && slider.isLoaded())
		{
			window.history.replaceState({}, "", slider.getUrl());
		}
	},

	resetBrowserHistory: function()
	{
		var topSlider = null;
		var openSliders = this.getOpenSliders();
		for (var i = openSliders.length - 1; i >= 0; i--)
		{
			var slider = openSliders[i];
			if (slider.canChangeHistory() && slider.isOpen() && slider.isLoaded())
			{
				topSlider = slider;
				break;
			}
		}

		var url = topSlider ? topSlider.getUrl() : this.getPageUrl();
		if (url)
		{
			window.history.replaceState({}, "", url);
		}
	},

	/**
	 * @private
	 * @param url
	 * @param params
	 * @returns {boolean}
	 */
	hasStopParams: function(url, params)
	{
		if (!params || !BX.type.isArray(params) || !BX.type.isNotEmptyString(url))
		{
			return false;
		}

		var questionPos = url.indexOf("?");
		if (questionPos === -1)
		{
			return false;
		}

		var query = url.substring(questionPos);
		for (var i = 0; i < params.length; i++)
		{
			var param = params[i];
			if (query.match(new RegExp("[?&]" + param + "=", "i")))
			{
				return true;
			}
		}

		return false;
	},

	/**
	 * @deprecated
	 * @public
	 * @returns {null|BX.SidePanel.Slider}
	 */
	getLastOpenPage: function()
	{
		return this.getLastOpenSlider();
	},

	/**
	 * @deprecated use getTopSlider method
	 * @public
	 * @returns {BX.SidePanel.Slider}
	 */
	getCurrentPage: function()
	{
		return this.getTopSlider();
	}
};

})();
