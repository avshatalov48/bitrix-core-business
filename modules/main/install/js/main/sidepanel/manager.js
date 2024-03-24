(function() {

"use strict";

/**
 * @typedef {object} BX.SidePanel.Options
 * @property {function} [contentCallback]
 * @property {number} [width]
 * @property {string} [title]
 * @property {boolean} [cacheable=true]
 * @property {boolean} [autoFocus=true]
 * @property {boolean} [printable=true]
 * @property {boolean} [allowCrossOrigin=false]
 * @property {boolean} [allowChangeHistory=true]
 * @property {boolean} [allowChangeTitle]
 * @property {boolean} [hideControls=false]
 * @property {string} [requestMethod]
 * @property {object} [requestParams]
 * @property {string} [loader]
 * @property {string} [contentClassName]
 * @property {object} [data]
 * @property {object} [minimizeOptions]
 * @property {string} [typeLoader] - option for compatibility
 * @property {number} [animationDuration]
 * @property {number} [customLeftBoundary]
 * @property {number} [customRightBoundary]
 * @property {number} [customTopBoundary]
 * @property {object} [label]
 * @property {boolean} [newWindowLabel]
 * @property {string} [newWindowUrl]
 * @property {boolean} [copyLinkLabel]
 * @property {boolean} [minimizeLabel]
 * @property {?object.<string, function>} [events]
 */

/**
 * @typedef {object} BX.SidePanel.Link
 * @property {string} url - URL
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

		var topWindow = BX.PageObject.getRootWindow();
		if (topWindow !== window)
		{
			return topWindow.BX.SidePanel.Instance;
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
	this.anchorBinding = true;

	this.openSliders = [];
	this.lastOpenSlider = null;

	this.opened = false;
	this.hidden = false;
	this.hacksApplied = false;

	this.pageUrl = this.getCurrentUrl();
	this.pageTitle = this.getCurrentTitle();
	this.titleChanged = false;

	this.toolbar = null;

	this.fullScreenSlider = null;

	this.handleAnchorClick = this.handleAnchorClick.bind(this);
	this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);
	this.handleWindowResize = BX.throttle(this.handleWindowResize, 300, this);
	this.handleWindowScroll = this.handleWindowScroll.bind(this);
	this.handleTouchMove = this.handleTouchMove.bind(this);

	this.handleSliderOpenStart = this.handleSliderOpenStart.bind(this);
	this.handleSliderOpenComplete = this.handleSliderOpenComplete.bind(this);
	this.handleSliderMaximizeStart = this.handleSliderMaximizeStart.bind(this);
	this.handleSliderCloseStart = this.handleSliderCloseStart.bind(this);
	this.handleSliderCloseComplete = this.handleSliderCloseComplete.bind(this);
	this.handleSliderMinimizeStart = this.handleSliderMinimizeStart.bind(this);
	this.handleSliderLoad = this.handleSliderLoad.bind(this);
	this.handleSliderDestroy = this.handleSliderDestroy.bind(this);
	this.handleEscapePress = this.handleEscapePress.bind(this);
	this.handleFullScreenChange = this.handleFullScreenChange.bind(this);

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

BX.SidePanel.Manager.prototype = {
	/**
	 * @public
	 * @param {string} url
	 * @param {BX.SidePanel.Options} [options]
	 */
	open: function(url, options)
	{
		const slider = this.createSlider(url, options);
		if (slider === null)
		{
			return false;
		}

		return this.tryApplyHacks(
			slider,
			() => slider.open(),
		);
	},

	/**
	 * @private
	 * @param url
	 * @param options
	 * @returns {BX.SidePanel.Slider | null}
	 */
	createSlider(url, options)
	{
		if (!BX.type.isNotEmptyString(url))
		{
			return null;
		}

		url = this.refineUrl(url);

		if (this.isHidden())
		{
			this.unhide();
		}

		const topSlider = this.getTopSlider();
		if (topSlider && topSlider.isOpen() && topSlider.getUrl() === url)
		{
			return null;
		}

		let slider = null;
		if (this.getLastOpenSlider() && this.getLastOpenSlider().getUrl() === url)
		{
			slider = this.getLastOpenSlider();
		}
		else
		{
			const rule = this.getUrlRule(url);
			const ruleOptions = rule !== null && BX.Type.isPlainObject(rule.options) ? rule.options : {};
			if (BX.Type.isUndefined(options))
			{
				options = ruleOptions;
			}
			else if (
				BX.Type.isPlainObject(ruleOptions.minimizeOptions)
				&& BX.Type.isPlainObject(options)
				&& !BX.Type.isPlainObject(options.minimizeOptions)
			)
			{
				options.minimizeOptions = ruleOptions.minimizeOptions;
			}

			if (this.getToolbar() === null && options.minimizeOptions)
			{
				options.minimizeOptions = null;
			}

			var sliderClass = BX.SidePanel.Manager.getSliderClass();
			slider = new sliderClass(url, options);

			var offset = null;
			if (slider.getWidth() === null && slider.getCustomLeftBoundary() === null)
			{
				offset = 0;
				var lastOffset = this.getLastOffset();
				if (topSlider && lastOffset !== null)
				{
					offset = Math.min(lastOffset + this.getMinOffset(), this.getMaxOffset());
				}
			}

			slider.setOffset(offset);

			if (topSlider && topSlider.getCustomRightBoundary() !== null)
			{
				const rightBoundary = slider.calculateRightBoundary();
				if (rightBoundary > topSlider.getCustomRightBoundary())
				{
					slider.setCustomRightBoundary(topSlider.getCustomRightBoundary());
				}
			}

			BX.addCustomEvent(slider, "SidePanel.Slider:onOpenStart", this.handleSliderOpenStart);
			BX.addCustomEvent(slider, "SidePanel.Slider:onBeforeOpenComplete", this.handleSliderOpenComplete);
			BX.addCustomEvent(slider, "SidePanel.Slider:onMaximizeStart", this.handleSliderMaximizeStart);
			BX.addCustomEvent(slider, "SidePanel.Slider:onCloseStart", this.handleSliderCloseStart);
			BX.addCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.handleSliderCloseComplete);
			BX.addCustomEvent(slider, "SidePanel.Slider:onMinimizeStart", this.handleSliderMinimizeStart);
			BX.addCustomEvent(slider, "SidePanel.Slider:onLoad", this.handleSliderLoad);
			BX.addCustomEvent(slider, "SidePanel.Slider:onDestroy", this.handleSliderDestroy);
			BX.addCustomEvent(slider, "SidePanel.Slider:onEscapePress", this.handleEscapePress);
		}

		return slider;
	},

	getMinimizeOptions(url)
	{
		const rule = this.getUrlRule(url);
		const ruleOptions = rule !== null && BX.Type.isPlainObject(rule.options) ? rule.options : {};

		return BX.Type.isPlainObject(ruleOptions.minimizeOptions) ? ruleOptions.minimizeOptions : null;
	},

	maximize(url, options)
	{
		const slider = this.createSlider(url, options);
		if (slider === null)
		{
			return false;
		}

		return this.tryApplyHacks(
			slider,
			() => slider.maximize(),
		);
	},

	tryApplyHacks(slider, cb)
	{
		if (!this.isOpen())
		{
			this.applyHacks(slider);
		}

		const success = cb();
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

	minimize(immediately, callback)
	{
		const topSlider = this.getTopSlider();
		if (topSlider)
		{
			topSlider.minimize(immediately, callback);
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
	 */
	reload: function()
	{
		var topSlider = this.getTopSlider();
		if (topSlider)
		{
			topSlider.reload();
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
	 * @return {BX.SidePanel.Slider|null}
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

	createToolbar(options)
	{
		if (this.toolbar === null)
		{
			this.toolbar = new Toolbar(options);
		}

		return this.toolbar;
	},

	/**
	 *
	 * @returns {Toolbar}
	 */
	getToolbar()
	{
		return this.toolbar;
	},

	/**
	 * @private
	 * @return {?number}
	 */
	getLastOffset: function()
	{
		var openSliders = this.getOpenSliders();
		for (var i = openSliders.length - 1; i >= 0; i--)
		{
			var slider = openSliders[i];
			if (slider.getOffset() !== null)
			{
				return slider.getOffset();
			}
		}

		return null;
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
	 * @returns {string}
	 */
	getPageTitle: function()
	{
		return this.pageTitle;
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getCurrentTitle: function()
	{
		var title = document.title;
		if (typeof BXIM !== 'undefined')
		{
			title = title.replace(/^\([0-9]+\) /, ""); //replace a messenger counter.
		}

		return title;
	},

	enterFullScreen: function()
	{
		if (!this.getTopSlider() || this.getFullScreenSlider())
		{
			return;
		}

		var container = document.body;
		if (container.requestFullscreen)
		{
			BX.bind(document, "fullscreenchange", this.handleFullScreenChange);
			container.requestFullscreen();
		}
		else if (container.webkitRequestFullScreen)
		{
			BX.bind(document, "webkitfullscreenchange", this.handleFullScreenChange);
			container.webkitRequestFullScreen();
		}
		else if (container.msRequestFullscreen)
		{
			BX.bind(document, "MSFullscreenChange", this.handleFullScreenChange);
			container.msRequestFullscreen();
		}
		else if (container.mozRequestFullScreen)
		{
			BX.bind(document, "mozfullscreenchange", this.handleFullScreenChange);
			container.mozRequestFullScreen();
		}
		else
		{
			console.log("Slider: Full Screen mode is not supported.");
		}
	},

	exitFullScreen: function()
	{
		if (!this.getFullScreenSlider())
		{
			return;
		}

		if (document.exitFullscreen)
		{
			document.exitFullscreen();
		}
		else if (document.webkitExitFullscreen)
		{
			document.webkitExitFullscreen();
		}
		else if (document.msExitFullscreen)
		{
			document.msExitFullscreen();
		}
		else if (document.mozCancelFullScreen)
		{
			document.mozCancelFullScreen();
		}
	},

	getFullScreenElement: function()
	{
		return (
			document.fullscreenElement ||
			document.webkitFullscreenElement ||
			document.mozFullScreenElement ||
			document.msFullscreenElement ||
			null
		);
	},

	getFullScreenSlider: function()
	{
		return this.fullScreenSlider;
	},

	handleFullScreenChange: function(event)
	{
		if (this.getFullScreenElement())
		{
			this.fullScreenSlider = this.getTopSlider();
			BX.addClass(this.fullScreenSlider.getOverlay(), "side-panel-fullscreen");

			this.fullScreenSlider.fireEvent("onFullScreenEnter");
		}
		else
		{
			if (this.getFullScreenSlider())
			{
				BX.removeClass(this.getFullScreenSlider().getOverlay(), "side-panel-fullscreen");
				this.fullScreenSlider.fireEvent("onFullScreenExit");
				this.fullScreenSlider = null;
			}

			BX.unbind(document, event.type, this.handleFullScreenChange);
			window.scrollTo(0, this.pageScrollTop);

			setTimeout(function() {
				this.adjustLayout();
				var event = document.createEvent("Event");
				event.initEvent("resize", true, true);
				window.dispatchEvent(event);
			}.bind(this), 1000);
		}
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
			slider.fireFrameEvent(event);
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

		if (BX.type.isArray(parameters.rules) && parameters.rules.length)
		{
			if (this.anchorRules.length === 0)
			{
				this.registerAnchorListener(window.document);
			}

			if (!(parameters.rules instanceof Object))
			{
				console.error(
					"BX.SitePanel: anchor rules were created in a different context. " +
					"This might be a reason for a memory leak."
				);

				console.trace();
			}

			parameters.rules.forEach((function(rule) {
				if (BX.type.isArray(rule.condition))
				{
					for (var m = 0; m < rule.condition.length; m++)
					{
						if (BX.type.isString(rule.condition[m]))
						{
							rule.condition[m] = new RegExp(rule.condition[m], "i");
						}
					}
				}

				rule.options = BX.type.isPlainObject(rule.options) ? rule.options : {};
				if (BX.type.isNotEmptyString(rule.loader) && !BX.type.isNotEmptyString(rule.options.loader))
				{
					rule.options.loader = rule.loader;
					delete rule.loader;
				}

				this.anchorRules.push(rule);
			}).bind(this));
		}
	},

	/**
	 * @public
	 */
	isAnchorBinding: function()
	{
		return this.anchorBinding;
	},

	/**
	 * @public
	 */
	enableAnchorBinding: function()
	{
		this.anchorBinding = true;
	},

	/**
	 * @public
	 */
	disableAnchorBinding: function()
	{
		this.anchorBinding = false;
	},

	/**
	 * @public
	 */
	registerAnchorListener: function(targetDocument)
	{
		targetDocument.addEventListener("click", this.handleAnchorClick, true);
	},

	/**
	 * @public
	 */
	unregisterAnchorListener: function(targetDocument)
	{
		targetDocument.removeEventListener("click", this.handleAnchorClick, true);
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderOpenStart: function(event)
	{
		if (!event.isActionAllowed())
		{
			return;
		}

		var slider = event.getSlider();
		if (slider.isDestroyed())
		{
			return;
		}

		if (this.getTopSlider())
		{
			this.exitFullScreen();

			this.getTopSlider().hideOverlay();

			var sameWidth = (
				this.getTopSlider().getOffset() === slider.getOffset() &&
				this.getTopSlider().getWidth() === slider.getWidth() &&
				this.getTopSlider().getCustomLeftBoundary() === slider.getCustomLeftBoundary()
			);

			if (!sameWidth)
			{
				this.getTopSlider().showShadow();
			}

			this.getTopSlider().hideOrDarkenCloseBtn();
			this.getTopSlider().hidePrintBtn();
			this.getTopSlider().hideExtraLabels();
		}
		else
		{
			slider.setOverlayAnimation(true);
		}

		this.addOpenSlider(slider);

		this.getOpenSliders().forEach(function(slider, index, openSliders) {
			slider.getLabel().moveAt(openSliders.length - index - 1); //move down
		}, this);

		this.losePageFocus();

		if (!this.opened)
		{
			this.pageUrl = this.getCurrentUrl();
			this.pageTitle = this.getCurrentTitle();
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
		this.updateBrowserTitle();
		event.getSlider().setAnimation('sliding');
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderCloseStart: function(event)
	{
		if (!event.isActionAllowed())
		{
			return;
		}

		if (event.getSlider() && event.getSlider().isDestroyed())
		{
			return;
		}

		var previousSlider = this.getPreviousSlider();
		var topSlider = this.getTopSlider();

		this.exitFullScreen();

		this.getOpenSliders().forEach(function(slider, index, openSliders) {
			slider.getLabel().moveAt(openSliders.length - index - 2); //move up
		}, this);

		if (previousSlider)
		{
			previousSlider.unhideOverlay();
			previousSlider.hideShadow();
			previousSlider.showOrLightenCloseBtn();

			if (topSlider)
			{
				topSlider.hideOverlay();
				topSlider.hideShadow();
			}
		}
	},

	handleSliderMaximizeStart: function(event)
	{
		if (!event.isActionAllowed() || this.getToolbar() === null)
		{
			return;
		}

		const slider = event.getSlider();
		if (slider && slider.isDestroyed())
		{
			return;
		}

		const { entityType, entityId } = slider.getMinimizeOptions() || {};
		const item = this.getToolbar().getItem(entityType, entityId);

		this.getToolbar().request('maximize', item);

		const origin = this.getItemOrigin(slider, item);
		slider.setAnimation('scale', { origin });
	},

	handleSliderMinimizeStart: function(event)
	{
		if (!event.isActionAllowed() || this.getToolbar() === null)
		{
			return;
		}

		const slider = event.getSlider();
		if (slider && slider.isDestroyed())
		{
			return;
		}

		if (!this.getToolbar().isShown())
		{
			this.getToolbar().show();
		}

		let title = slider.getTitle();
		if (!title)
		{
			title = slider.getFrameWindow() ? slider.getFrameWindow().document.title : null;
		}

		this.getToolbar().expand(true);

		const minimizeOptions = this.getMinimizeOptions(slider.getUrl());
		const { entityType, entityId, url } = minimizeOptions || slider.getMinimizeOptions() || {};

		const item = this.getToolbar().minimizeItem({
			title,
			url: BX.Type.isStringFilled(url) ? url : slider.getUrl(),
			entityType,
			entityId,
		});

		const origin = this.getItemOrigin(slider, item);
		slider.setAnimation('scale', { origin });
	},

	/**
	 * @private
	 * @param {ToolbarItem} item
	 */
	getItemOrigin(slider, item)
	{
		if (item && item.getContainer().offsetWidth > 0)
		{
			const rect = item.getContainer().getBoundingClientRect();
			const offset = slider.getContainer().getBoundingClientRect().left;
			const left = rect.left - offset + rect.width / 2;

			return `${left}px ${rect.top}px`;
		}

		return '50% 100%';
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

		event.getSlider().setAnimation('sliding');

		this.cleanUpClosedSlider(slider);
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Event} event
	 */
	handleSliderDestroy: function(event)
	{
		var slider = event.getSlider();

		BX.removeCustomEvent(slider, "SidePanel.Slider:onOpenStart", this.handleSliderOpenStart);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onBeforeOpenComplete", this.handleSliderOpenComplete);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onMaximizeStart", this.handleSliderMaximizeStart);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onCloseStart", this.handleSliderCloseStart);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.handleSliderCloseComplete);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onMinimizeStart", this.handleSliderMinimizeStart);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onLoad", this.handleSliderLoad);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onDestroy", this.handleSliderDestroy);
		BX.removeCustomEvent(slider, "SidePanel.Slider:onEscapePress", this.handleEscapePress);

		var frameWindow = event.getSlider().getFrameWindow();
		if (frameWindow && !event.getSlider().allowCrossOrigin)
		{
			this.unregisterAnchorListener(frameWindow.document);
		}

		if (slider === this.getLastOpenSlider())
		{
			this.lastOpenSlider = null;
		}

		this.cleanUpClosedSlider(slider);
	},

	handleEscapePress: function(event)
	{
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
	 * @param {BX.SidePanel.Slider} slider
	 */
	cleanUpClosedSlider: function(slider)
	{
		this.removeOpenSlider(slider);

		slider.unhideOverlay();
		slider.hideShadow();

		this.getOpenSliders().forEach(function(slider, index, openSliders) {
			slider.getLabel().moveAt(openSliders.length - index - 1); //update position
		}, this);

		if (this.getTopSlider())
		{
			this.getTopSlider().showOrLightenCloseBtn();
			this.getTopSlider().unhideOverlay();
			this.getTopSlider().hideShadow();
			this.getTopSlider().showExtraLabels();

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
		this.updateBrowserTitle();
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
			this.registerAnchorListener(frameWindow.document);
		}

		this.setBrowserHistory(event.getSlider());
		this.updateBrowserTitle();
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
		BX.Dom.style(document.body, '--scroll-shift-width', `${scrollWidth}px`);
		BX.addClass(document.body, "side-panel-disable-scrollbar");
		this.pageScrollTop = window.pageYOffset || document.documentElement.scrollTop;
	},

	/**
	 * @private
	 */
	enablePageScrollbar: function()
	{
		document.body.style.removeProperty("padding-right");
		BX.Dom.style(document.body, '--scroll-shift-width', null);
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
		window.scrollTo(0, this.pageScrollTop);
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
		if (!this.isAnchorBinding())
		{
			return;
		}

		var link = this.extractLinkFromEvent(event);

		if (!link || BX.data(link.anchor, "slider-ignore-autobinding"))
		{
			return;
		}

		if (BX.data(event.target, "slider-ignore-autobinding"))
		{
			return;
		}

		var rule = this.getUrlRule(link.url, link);

		if (!this.isValidLink(rule, link))
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
			this.open(link.url, rule.options);
		}
	},
	/**
	 * @public
	 * @param {string} url
	 */
	emulateAnchorClick: function(url)
	{
		var link = {
			url : url,
			anchor : null,
			target : null
		};
		var rule = this.getUrlRule(url, link);

		if (!this.isValidLink(rule, link))
		{
			BX.reload(url);
		}
		else if (BX.type.isFunction(rule.handler))
		{
			rule.handler(
				new Event(
					"slider",
					{
						"bubbles" : false,
						"cancelable" : true
					}
				),
				link
			);
		}
		else
		{
			this.open(link.url, rule.options);
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

		if (!BX.Type.isPlainObject(link))
		{
			const a = document.createElement('a');
			a.href = href;

			link = { url: href, anchor: a, target: '' };
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
				var matches = href.match(rule.condition[m]);
				if (matches && !this.hasStopParams(href, rule.stopParameters))
				{
					link.matches = matches;
					const minimizeOptions = BX.Type.isFunction(rule.minimizeOptions) ? rule.minimizeOptions(link) : null;
					if (BX.Type.isPlainObject(minimizeOptions))
					{
						if (BX.Type.isPlainObject(rule.options))
						{
							rule.options.minimizeOptions = minimizeOptions;
						}
						else
						{
							rule.options = { minimizeOptions };
						}
					}

					return rule;
				}
			}
		}

		return null;
	},
	/**
	 * @private
	 * @param {BX.SidePanel.Rule} rule
	 * @param {BX.SidePanel.Link} link
	 * @returns {boolean}
	 */
	isValidLink: function(rule, link)
	{
		if (!rule)
		{
			return false;
		}

		if (rule.allowCrossDomain !== true && BX.ajax.isCrossDomain(link.url))
		{
			return false;
		}

		if (rule.mobileFriendly !== true && BX.browser.IsMobile())
		{
			return false;
		}

		if (BX.type.isFunction(rule.validate) && !rule.validate(link))
		{
			return false;
		}

		return true;
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

	/**
	 * @private
	 */
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
	 * @public
	 */
	updateBrowserTitle: function()
	{
		var title = null;
		var openSliders = this.getOpenSliders();
		for (var i = openSliders.length - 1; i >= 0; i--)
		{
			title = this.getBrowserTitle(openSliders[i]);
			if (BX.type.isNotEmptyString(title))
			{
				break;
			}
		}

		if (BX.type.isNotEmptyString(title))
		{
			document.title = title;
			this.titleChanged = true;
		}
		else if (this.titleChanged)
		{
			document.title = this.getPageTitle();
			this.titleChanged = false;
		}
	},

	/**
	 * @private
	 * @param {BX.SidePanel.Slider} slider
	 */
	getBrowserTitle: function(slider)
	{
		if (!slider || !slider.canChangeTitle() || !slider.isOpen() || !slider.isLoaded())
		{
			return null;
		}

		var title = slider.getTitle();
		if (!title && !slider.isSelfContained())
		{
			title = slider.getFrameWindow() ? slider.getFrameWindow().document.title : null;
		}

		return BX.type.isNotEmptyString(title) ? title : null;
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

class Toolbar extends BX.Event.EventEmitter
{
	constructor(toolbarOptions)
	{
		super();
		this.setEventNamespace('BX.Main.SidePanel.Toolbar');

		const options = BX.Type.isPlainObject(toolbarOptions) ? toolbarOptions : {};
		if (!BX.Type.isStringFilled(options.context))
		{
			throw new Error('BX.Main.SidePanel.Toolbar: "context" parameter is required.');
		}

		this.context = options.context;
		this.items = [];
		this.rendered = false;
		this.refs = new BX.Cache.MemoryCache();
		this.container = null;
		this.lsKey = 'bx.sidepanel.toolbar.item';

		this.initialPosition = { right: '5px', bottom: '20px' };
		this.shiftedPosition = { right: '5px', bottom: '20px' };
		if (BX.Type.isPlainObject(options.position))
		{
			this.initialPosition = options.position;
		}

		if (BX.Type.isPlainObject(options.shiftedPosition))
		{
			this.shiftedPosition = options.shiftedPosition;
		}

		this.collapsed = options.collapsed !== false;
		this.muted = false;
		this.shifted = false;

		this.maxVisibleItems = BX.Type.isNumber(options.maxVisibleItems) ? Math.max(options.maxVisibleItems, 1) : 5;

		this.addItems(options.items);

		const item = this.restoreItemFromLocalStorage();
		if (item !== null)
		{
			const { entityType, entityId } = item;
			if (this.getItem(entityType, entityId))
			{
				this.clearLocalStorage();
			}
			else
			{
				this.minimizeItem(item);
			}
		}
	}

	show()
	{
		BX.Dom.addClass(this.getContainer(), '--show');
	}

	isShown()
	{
		return BX.Dom.hasClass(this.getContainer(), '--show');
	}

	hide()
	{
		BX.Dom.removeClass(this.getContainer(), '--show');
	}

	mute()
	{
		if (this.muted)
		{
			return false;
		}

		this.muted = true;
		BX.Dom.addClass(this.getContainer(), '--muted');

		return true;
	}

	unmute()
	{
		if (!this.muted)
		{
			return false;
		}

		this.muted = false;
		BX.Dom.removeClass(this.getContainer(), '--muted');

		return true;
	}

	isMuted()
	{
		return this.muted;
	}

	toggleMuteness()
	{
		if (this.canShowOnTop())
		{
			return this.unmute();
		}

		return this.mute();
	}

	shift()
	{
		if (this.shifted)
		{
			return false;
		}

		this.shifted = true;
		BX.Dom.addClass(this.getContainer(), '--shifted');
		BX.Dom.style(document.body, '--side-panel-toolbar-shifted', 1);
		this.setPosition(this.getContainer(), this.shiftedPosition);

		return true;
	}

	unshift()
	{
		if (!this.shifted)
		{
			return false;
		}

		this.shifted = false;
		BX.Dom.removeClass(this.getContainer(), '--shifted');
		BX.Dom.style(document.body, '--side-panel-toolbar-shifted', null);
		this.setPosition(this.getContainer(), this.initialPosition);

		return true;
	}

	isShifted()
	{
		return this.shifted;
	}

	toggleShift()
	{
		const sliders = BX.SidePanel.Instance.getOpenSliders();
		if (sliders.length === 0 || (sliders.length === 1 && !sliders[0].isOpen()))
		{
			return this.unshift();
		}

		return this.shift();
	}

	setPosition(container, position)
	{
		for (const prop of ['top', 'right', 'bottom', 'left'])
		{
			BX.Dom.style(container, prop, null);
			if (BX.Type.isStringFilled(position[prop]))
			{
				BX.Dom.style(container, prop, position[prop]);
			}
		}
	}

	collapse(immediately)
	{
		if (this.collapsed)
		{
			return;
		}

		if (immediately === true)
		{
			BX.Dom.addClass(this.getContainer(), '--collapsed');
			BX.Dom.style(this.getContentContainer(), 'width', null);
		}
		else
		{
			const width = this.getContentContainer().scrollWidth;
			BX.Dom.style(this.getContentContainer(), 'width', `${width}px`);

			BX.Event.unbindAll(this.getContentContainer(), 'transitionend');

			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					BX.Dom.style(this.getContentContainer(), 'width', 0);
					BX.Event.bindOnce(this.getContentContainer(), 'transitionend', () => {
						BX.Dom.addClass(this.getContainer(), '--collapsed');
						BX.Dom.style(this.getContentContainer(), 'width', null);
					});
				});
			});
		}

		this.collapsed = true;
	}

	expand(immediately)
	{
		if (!this.collapsed)
		{
			return;
		}

		if (immediately === true)
		{
			BX.Dom.removeClass(this.getContainer(), '--collapsed');
			BX.Dom.style(this.getContentContainer(), 'width', null);
		}
		else
		{
			BX.Dom.removeClass(this.getContainer(), '--collapsed');
			const width = this.getContentContainer().scrollWidth;
			BX.Dom.style(this.getContentContainer(), 'width', 0);

			BX.Event.unbindAll(this.getContentContainer(), 'transitionend');

			requestAnimationFrame(() => {
				requestAnimationFrame(() => {
					BX.Dom.style(this.getContentContainer(), 'width', `${width}px`);
					BX.Event.bindOnce(this.getContentContainer(), 'transitionend', () => {
						BX.Dom.style(this.getContentContainer(), 'width', null);
					});
				});
			});
		}

		this.collapsed = false;
	}

	toggle()
	{
		if (this.collapsed)
		{
			this.request('expand');
			this.expand();
		}
		else
		{
			this.request('collapse');
			this.collapse();
		}
	}

	isCollapsed()
	{
		return this.collapsed;
	}

	getItems()
	{
		return this.items;
	}

	getItemsCount()
	{
		return this.items.length;
	}

	addItems(itemsOptions)
	{
		if (BX.Type.isArrayFilled(itemsOptions))
		{
			itemsOptions.forEach((itemOptions) => {
				this.addItem(itemOptions);
			});
		}
	}

	/**
	 *
	 * @param itemOptions
	 * @returns {ToolbarItem|null}
	 */
	addItem(itemOptions)
	{
		const item = this.createItem(itemOptions);
		if (item === null)
		{
			return null;
		}

		this.items.push(item);

		if (this.rendered)
		{
			this.redraw();
		}

		return item;
	}

	/**
	 *
	 * @param itemOptions
	 * @returns {ToolbarItem|null}
	 */
	prependItem(itemOptions)
	{
		const item = this.createItem(itemOptions);
		if (item === null)
		{
			return null;
		}

		this.items.unshift(item);

		if (this.rendered)
		{
			this.redraw();
		}

		return item;
	}

	createItem(itemOptions)
	{
		const options = BX.Type.isPlainObject(itemOptions) ? itemOptions : {};

		if (
			!BX.Type.isStringFilled(options.entityType)
			|| !(BX.Type.isStringFilled(options.entityId) || BX.Type.isNumber(options.entityId))
			|| !BX.Type.isStringFilled(options.title)
			|| !BX.Type.isStringFilled(options.url)
		)
		{
			return null;
		}

		const item = new ToolbarItem(options);
		if (!BX.Type.isStringFilled(item.getEntityName()))
		{
			const minimizeOptions = BX.SidePanel.Instance.getMinimizeOptions(item.getUrl());
			if (BX.Type.isPlainObject(minimizeOptions) && BX.Type.isStringFilled(minimizeOptions.entityName))
			{
				item.setEntityName(minimizeOptions.entityName);
			}
		}

		item.subscribe('onRemove', this.handleItemRemove.bind(this));

		return item;
	}

	/**
	 * @private
	 * @param itemOptions
	 * @returns {ToolbarItem|null}
	 */
	minimizeItem(itemOptions)
	{
		const { entityType, entityId } = itemOptions;
		let item = this.getItem(entityType, entityId);
		const itemExists = item !== null;
		if (!itemExists)
		{
			item = this.prependItem(itemOptions);
		}

		if (item !== null)
		{
			if (!itemExists)
			{
				this.saveItemToLocalStorage(item);
			}

			this.request('minimize', item)
				.then((response) => {
					if (response.status === 'success')
					{
						this.clearLocalStorage();
					}
				}).catch(() => {
					this.clearLocalStorage();
					this.removeItem(item);
				})
			;
		}

		return item;
	}

	saveItemToLocalStorage(item)
	{
		const cache = { item, ttl: Date.now() };
		localStorage.setItem(this.lsKey, JSON.stringify(cache));
	}

	restoreItemFromLocalStorage()
	{
		const data = localStorage.getItem(this.lsKey);
		if (BX.Type.isStringFilled(data))
		{
			const { item, ttl } = JSON.parse(data);
			if ((Date.now() - ttl) > 10000)
			{
				this.clearLocalStorage();

				return null;
			}

			if (BX.Type.isPlainObject(item))
			{
				return item;
			}
		}

		return null;
	}

	clearLocalStorage()
	{
		localStorage.removeItem(this.lsKey);
	}

	getContext()
	{
		return this.context;
	}

	request(action, item, data)
	{
		const additional = BX.Type.isPlainObject(data) ? data : {};

		return BX.ajax.runAction(`main.api.sidepanel.toolbar.${action}`, {
			json: {
				toolbar: {
					context: this.getContext(),
				},
				item: item ? item.toJSON() : null,
				...additional,
			},
		});
	}

	handleItemRemove(event)
	{
		const item = event.getTarget();
		item.hideTooltip();
		this.removeItem(item);
	}

	handleMenuItemRemove(event)
	{
		event.preventDefault();
		event.stopPropagation();

		const itemId = event.currentTarget.dataset.menuItemId;
		const itemToRemove = this.getItemById(itemId);
		if (itemToRemove)
		{
			this.removeItem(itemToRemove);
		}

		const menu = this.getMenu();
		if (menu)
		{
			menu.removeMenuItem(itemId);

			const invisibleItemsCount = this.getItems().reduce((count, item) => {
				return item.isRendered() ? count : count + 1;
			}, 0);

			if (invisibleItemsCount > 0)
			{
				menu.getPopupWindow().adjustPosition();
			}
			else
			{
				menu.close();
			}
		}
	}

	removeItem(itemToRemove)
	{
		itemToRemove.remove();
		this.items = this.items.filter((item) => {
			return item !== itemToRemove;
		});

		const restored = this.restoreItemFromLocalStorage();
		if (restored !== null)
		{
			const { entityType, entityId } = restored;
			if (itemToRemove.getEntityType() === entityType && itemToRemove.getEntityId() === entityId)
			{
				this.clearLocalStorage();
			}
		}

		if (this.rendered)
		{
			this.redraw();
			this.request('remove', itemToRemove);

			if (this.getItemsCount() === 0)
			{
				this.hide();
			}
		}
	}

	redraw()
	{
		let visibleItemsCount = 0;
		for (let i = 0; i < this.getItems().length; i++)
		{
			const item = this.getItems()[i];
			if (visibleItemsCount >= this.maxVisibleItems)
			{
				if (item.isRendered())
				{
					item.remove();
				}
			}
			else
			{
				if (!item.isRendered())
				{
					const previousItem = this.getItems()[i - 1] || null;
					const nextItem = this.getItems()[i + 1] || null;
					if (previousItem)
					{
						item.insertAfter(previousItem.getContainer());
					}
					else if (nextItem)
					{
						item.insertBefore(nextItem.getContainer());
					}
					else
					{
						item.appendTo(this.getItemsContainer());
					}
				}

				visibleItemsCount++;
			}
		}
	}

	removeAll()
	{
		this.getItemsContainer().innerHTML = '';
		this.items = [];
		this.clearLocalStorage();
	}

	/**
	 *
	 * @returns {ToolbarItem|null}
	 * @param entityType
	 * @param entityId
	 */
	getItem(entityType, entityId)
	{
		return this.items.find((item) => item.getEntityType() === entityType && item.getEntityId() === entityId) || null;
	}

	/**
	 *
	 * @param {string} url
	 * @returns {ToolbarItem|null}
	 */
	getItemByUrl(url)
	{
		return this.items.find((item) => item.getUrl() === url) || null;
	}

	/**
	 *
	 * @param {string} id
	 * @returns {ToolbarItem|null}
	 */
	getItemById(id)
	{
		return this.items.find((item) => item.getId() === id) || null;
	}

	getContainer()
	{
		return this.refs.remember('container', () => {
			const classes = [];
			if (this.collapsed)
			{
				classes.push('--collapsed');
			}

			const container = BX.Tag.render`
				<div class="side-panel-toolbar ${classes.join(' ')}">
					${this.getContentContainer()}
					<div class="side-panel-toolbar-toggle" onclick="${this.handleToggleClick.bind(this)}"></div>
				</div>
			`;

			this.setPosition(container, this.initialPosition);
			BX.Dom.append(container, document.body);
			BX.ZIndexManager.register(container, { alwaysOnTop: true });
			this.rendered = true;

			const toggleMuteness = BX.Runtime.debounce(this.toggleMuteness, 50, this);
			BX.Event.EventEmitter.subscribe('BX.Main.Popup:onShow', toggleMuteness);
			BX.Event.EventEmitter.subscribe('BX.Main.Popup:onClose', toggleMuteness);
			BX.Event.EventEmitter.subscribe('BX.Main.Popup:onDestroy', toggleMuteness);
			BX.Event.EventEmitter.subscribe('onWindowClose', toggleMuteness);
			BX.Event.EventEmitter.subscribe('onWindowRegister', toggleMuteness);

			let forceCollapsed = false;
			const onSliderClose = () => {
				this.toggleMuteness();
				if (this.isMuted())
				{
					return;
				}

				this.toggleShift();
				if (!this.isShifted() && forceCollapsed)
				{
					forceCollapsed = false;
					this.expand();
				}
			};

			BX.Event.EventEmitter.subscribe('SidePanel.Slider:onClosing', onSliderClose);
			BX.Event.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', onSliderClose);
			BX.Event.EventEmitter.subscribe('SidePanel.Slider:onDestroyComplete', onSliderClose);
			BX.Event.EventEmitter.subscribe('SidePanel.Slider:onOpening', () => {
				this.toggleMuteness();
				if (this.isMuted())
				{
					return;
				}

				if (!this.isCollapsed())
				{
					forceCollapsed = true;
					this.collapse();
				}

				this.toggleShift();
			});

			BX.Event.EventEmitter.subscribe('BX.UI.Viewer.Controller:onBeforeShow', toggleMuteness);
			BX.Event.EventEmitter.subscribe(
				'BX.UI.Viewer.Controller:onClose',
				BX.Runtime.debounce(this.toggleMuteness, 500, this),
			);

			BX.Event.bind(window, 'resize', BX.Runtime.throttle(() => {
				const menu = this.getMenu();
				if (menu !== null)
				{
					menu.close();
				}
			}, 300));

			return container;
		});
	}

	getContentContainer()
	{
		return this.refs.remember('content-container', () => {
			return BX.Tag.render`
				<div class="side-panel-toolbar-content">
					<div class="side-panel-toolbar-collapse-btn" onclick="${this.handleToggleClick.bind(this)}">
						<div class="ui-icon-set --chevron-right"></div>
					</div>
					${this.getItemsContainer()}
					${this.getMoreButton()}
				</div>
			`;
		});
	}

	/**
	 *
	 * @returns {HTMLElement}
	 */
	getItemsContainer()
	{
		return this.refs.remember('items-container', () => {
			const container = BX.Tag.render`<div class="side-panel-toolbar-items"></div>`;
			[...this.items].slice(0, this.maxVisibleItems).forEach((item) => {
				item.appendTo(container);
			});

			return container;
		});
	}

	getMoreButton()
	{
		return this.refs.remember('more-button', () => {
			return BX.Tag.render`
				<div class="side-panel-toolbar-more-btn" onclick="${this.handleMoreBtnClick.bind(this)}">
					<div class="ui-icon-set --more"></div>
				</div>
			`;
		});
	}

	handleMoreBtnClick(event)
	{
		const targetNode = this.getMoreButton();
		const rect = targetNode.getBoundingClientRect();
		const targetNodeWidth = rect.width;

		const items = [...this.items].filter((item) => !item.isRendered()).map((item) => {
			const title = (
				BX.Type.isStringFilled(item.getEntityName())
					? `${item.getEntityName()}\n${item.getTitle()}`
					: item.getTitle()
			);

			return {
				id: item.getId(),
				html: this.createMenuItemText(item),
				title,
				href: item.getUrl(),
				onclick: () => {
					menu.close();
				},
			};
		});

		if (items.length > 0)
		{
			items.push({
				delimiter: true,
			});
		}

		items.push({
			text: BX.Loc.getMessage('MAIN_SIDEPANEL_REMOVE_ALL'),
			onclick: () => {
				this.removeAll();
				this.hide();
				menu.close();

				this.request('removeAll');
			},
		});

		const menu = BX.Main.MenuManager.create({
			id: 'sidepanel-toolbar-more-btn',
			cacheable: false,
			bindElement: rect,
			bindOptions: {
				forceBindPosition: true,
				forceTop: true,
				position: 'top',
			},
			maxWidth: 260,
			fixed: true,
			offsetTop: 0,
			maxHeight: 305,
			items,
			events: {
				onShow: (event) => {
					const popup = event.getTarget();
					const popupWidth = popup.getPopupContainer().offsetWidth;
					const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
					const angleShift = BX.Main.Popup.getOption('angleLeftOffset') - BX.Main.Popup.getOption('angleMinTop');

					popup.setAngle({ offset: popupWidth / 2 - angleShift });
					popup.setOffset({ offsetLeft: offsetLeft + BX.Main.Popup.getOption('angleLeftOffset') });
				},
			},
		});

		menu.show();
	}

	canShowOnTop()
	{
		const popups = BX.Main.PopupManager.getPopups();
		for (const popup of popups)
		{
			if (!popup.isShown())
			{
				continue;
			}

			if (
				popup.getId().startsWith('timeman_weekly_report_popup_')
				|| popup.getId().startsWith('timeman_daily_report_popup_')
				|| BX.Dom.hasClass(popup.getPopupContainer(), 'b24-whatsnew__popup')
			)
			{
				return false;
			}
		}

		if (BX.Reflection.getClass('BX.UI.Viewer.Instance') && BX.UI.Viewer.Instance.isOpen())
		{
			return false;
		}

		const sliders = BX.SidePanel.Instance.getOpenSliders();
		for (const slider of sliders)
		{
			const sliderId = slider.getUrl().toString();
			if (
				sliderId.startsWith('im:slider')
				|| sliderId.startsWith('release-slider')
				|| sliderId.startsWith('main:helper')
				|| sliderId.startsWith('ui:info_helper')
			)
			{
				return false;
			}
		}

		const stack = BX.ZIndexManager.getStack(document.body);
		const components = stack === null ? [] : stack.getComponents();
		for (const component of components)
		{
			if (component.getOverlay() !== null && component.getOverlay().offsetWidth > 0)
			{
				return false;
			}
		}

		return true;
	}

	getMenu()
	{
		return BX.Main.MenuManager.getMenuById('sidepanel-toolbar-more-btn');
	}

	createMenuItemText(item)
	{
		return BX.Tag.render`
			<span class="side-panel-toolbar-menu-item">${[
				BX.Tag.render`
					<span class="side-panel-toolbar-menu-item-title">${BX.Text.encode(item.getTitle())}</span>
				`,
				BX.Tag.render`
					<span
						class="side-panel-toolbar-menu-item-remove"
						data-slider-ignore-autobinding="true"
						data-menu-item-id="${item.getId()}"
						onclick="${this.handleMenuItemRemove.bind(this)}"
					>
						<span class="ui-icon-set --cross-20" data-slider-ignore-autobinding="true"></span>
					</span>
				`,
			]}</span>
		`;
	}

	handleToggleClick()
	{
		this.toggle();
	}
}

class ToolbarItem extends BX.Event.EventEmitter
{
	constructor(itemOptions)
	{
		super();
		this.setEventNamespace('BX.Main.SidePanel.ToolbarItem');

		const options = BX.Type.isPlainObject(itemOptions) ? itemOptions : {};

		this.id = BX.Type.isStringFilled(options.id) ? options.id : `toolbar-item-${BX.Text.getRandom().toLowerCase()}`;
		this.title = '';
		this.url = '';
		this.entityType = '';
		this.entityId = 0;
		this.entityName = '';

		this.refs = new BX.Cache.MemoryCache();
		this.rendered = false;

		this.setTitle(options.title);
		this.setUrl(options.url);
		this.setEntityType(options.entityType);
		this.setEntityId(options.entityId);
	}

	getId()
	{
		return this.id;
	}

	getUrl()
	{
		return this.url;
	}

	setUrl(url)
	{
		if (BX.Type.isStringFilled(url))
		{
			this.url = url;
			if (this.rendered)
			{
				this.getContainer().href = url;
			}
		}
	}

	getTitle()
	{
		return this.title;
	}

	setTitle(title)
	{
		if (BX.Type.isStringFilled(title))
		{
			this.title = title;
			if (this.rendered)
			{
				this.getTitleContainer().textContent = title;
			}
		}
	}

	getEntityType()
	{
		return this.entityType;
	}

	setEntityType(entityType)
	{
		if (BX.Type.isStringFilled(entityType))
		{
			this.entityType = entityType;
		}
	}

	getEntityId()
	{
		return this.entityId;
	}

	setEntityId(entityId)
	{
		if (BX.Type.isNumber(entityId) || BX.Type.isStringFilled(entityId))
		{
			this.entityId = entityId;
		}
	}

	getEntityName()
	{
		return this.entityName;
	}

	setEntityName(entityName)
	{
		if (BX.Type.isStringFilled(entityName))
		{
			this.entityName = entityName;
		}
	}

	getContainer()
	{
		return this.refs.remember('container', () => {
			return BX.Tag.render`
				<div class="side-panel-toolbar-item" 
					onclick="${this.handleClick.bind(this)}"
					onmouseenter="${this.handleMouseEnter.bind(this)}"
					onmouseleave="${this.handleMouseLeave.bind(this)}"
				>
					${this.getTitleContainer()}
					<div class="side-panel-toolbar-item-remove-btn" onclick="${this.handleRemoveBtnClick.bind(this)}">
						<div class="ui-icon-set --cross-20" style="--ui-icon-set__icon-size: 100%;"></div>
					</div>
				</div>
			`;
		});
	}

	isRendered()
	{
		return this.rendered;
	}

	getTitleContainer()
	{
		return this.refs.remember('title', () => {
			return BX.Tag.render`
				<a 
					class="side-panel-toolbar-item-title"
					href="${encodeURI(this.getUrl())}"
					data-slider-ignore-autobinding="true"
				>${BX.Text.encode(this.getTitle())}</a>
			`;
		});
	}

	prependTo(node)
	{
		if (BX.Type.isDomNode(node))
		{
			BX.Dom.prepend(this.getContainer(), node);
			this.rendered = true;
		}
	}

	appendTo(node)
	{
		if (BX.Type.isDomNode(node))
		{
			BX.Dom.append(this.getContainer(), node);
			this.rendered = true;
		}
	}

	insertBefore(node)
	{
		if (BX.Type.isDomNode(node))
		{
			BX.Dom.insertBefore(this.getContainer(), node);
			this.rendered = true;
		}
	}

	insertAfter(node)
	{
		if (BX.Type.isDomNode(node))
		{
			BX.Dom.insertAfter(this.getContainer(), node);
			this.rendered = true;
		}
	}

	remove()
	{
		BX.Dom.remove(this.getContainer());
		this.rendered = false;
	}

	showTooltip()
	{
		const targetNode = this.getContainer();
		const rect = targetNode.getBoundingClientRect();
		const targetNodeWidth = rect.width;
		const popupWidth = Math.min(Math.max(100, this.getTitleContainer().scrollWidth + 20), 300);

		const hint = BX.Main.PopupManager.create({
			id: 'sidepanel-toolbar-item-hint',
			cacheable: false,
			bindElement: rect,
			bindOptions: {
				forceBindPosition: true,
				forceTop: true,
				position: 'top',
			},
			width: popupWidth,
			content: BX.Tag.render`
				<div class="sidepanel-toolbar-item-hint">
					<div class="sidepanel-toolbar-item-hint-title">${BX.Text.encode(this.getEntityName())}</div>
					<div class="sidepanel-toolbar-item-hint-content">${BX.Text.encode(this.getTitle())}</div>
				</div>
			`,
			darkMode: true,
			fixed: true,
			offsetTop: 0,
			events: {
				onShow: (event) => {
					const popup = event.getTarget();
					const offsetLeft = (targetNodeWidth / 2) - (popupWidth / 2);
					const angleShift = BX.Main.Popup.getOption('angleLeftOffset') - BX.Main.Popup.getOption('angleMinTop');

					popup.setAngle({ offset: popupWidth / 2 - angleShift });
					popup.setOffset({ offsetLeft: offsetLeft + BX.Main.Popup.getOption('angleLeftOffset') });
				},
			},
		});

		hint.show();
		hint.adjustPosition();
	}

	hideTooltip()
	{
		const hint = BX.Main.PopupManager.getPopupById('sidepanel-toolbar-item-hint');
		if (hint)
		{
			hint.close();
		}
	}

	handleClick(event)
	{
		if (event.ctrlKey || event.metaKey)
		{
			return;
		}

		event.preventDefault();
		BX.SidePanel.Instance.maximize(this.getUrl());
	}

	handleMouseEnter(event)
	{
		this.showTooltip();
	}

	handleMouseLeave(event)
	{
		this.hideTooltip();
	}

	handleRemoveBtnClick(event)
	{
		event.stopPropagation();
		this.emit('onRemove');
	}

	toJSON()
	{
		return {
			title: this.getTitle(),
			url: this.getUrl(),
			entityType: this.getEntityType(),
			entityId: this.getEntityId(),
		};
	}
}

})();
