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
	this.url = BX.util.remove_url_param(url, ["IFRAME", "IFRAME_TYPE"]);
	options = BX.type.isPlainObject(options) ? options : {};
	this.options = options;

	this.slider = null;

	this.contentCallback = BX.type.isFunction(options.contentCallback) ? options.contentCallback : null;
	this.contentCallbackInvoved = false;

	this.zIndex = 3000;
	this.offset = 0;
	this.width = BX.type.isNumber(options.width) ? options.width : null;
	this.cacheable = options.cacheable !== false;
	this.autoFocus = options.autoFocus !== false;
	this.printable = options.printable === true;
	this.allowChangeHistory = options.allowChangeHistory !== false;
	this.data = new BX.SidePanel.Dictionary(BX.type.isPlainObject(options.data) ? options.data : {});

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

	/**
	 *
	 * @type {{overlay: Element, container: Element, loader: Element, content: Element, closeBtn: Element}}
	 */
	this.layout = {
		overlay: null,
		container: null,
		loader: null,
		content: null,
		closeBtn: null,
		printBtn: null
	};

	this.loader =
		BX.type.isNotEmptyString(options.loader)
			? options.loader
			: BX.type.isNotEmptyString(options.typeLoader) ? options.typeLoader : "default-loader"
	;

	this.animation = null;
	this.animationDuration = BX.type.isNumber(options.animationDuration) ? options.animationDuration : 200;
	this.startParams = { translateX: 100, opacity: 0 };
	this.endParams = { translateX: 0, opacity: 40 };
	this.currentParams = null;

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

		this.createLayout();
		this.adjustLayout();

		this.opened = true;
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

		this.opened = false;

		if (this.animation)
		{
			this.animation.stop();
		}

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

			this.animation.animate();
		}

		return true;
	},

	/**
	 * @public
	 * @returns {string}
	 */
	getUrl: function()
	{
		return this.url;
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
	 * @param {number} zIndex
	 */
	setZindex: function(zIndex)
	{
		if (BX.type.isNumber(zIndex))
		{
			this.zIndex = zIndex;
		}
	},

	/**
	 * @public
	 * @returns {number}
	 */
	getZindex: function()
	{
		return this.zIndex;
	},

	/**
	 * @public
	 * @param {number} offset
	 */
	setOffset: function(offset)
	{
		if (BX.type.isNumber(offset))
		{
			this.offset = offset;
		}
	},

	/**
	 * @public
	 * @returns {number}
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
		if (!this.layout.loader || this.layout.loader.dataset.loader !== loader)
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
		this.layout.loader.style.display = "none";
		this.layout.loader.style.opacity = 0;
	},

	/**
	 * @public
	 */
	showCloseBtn: function()
	{
		this.getCloseBtn().style.removeProperty("opacity");
	},

	/**
	 * @public
	 */
	hideCloseBtn: function()
	{
		this.getCloseBtn().style.opacity = 0;
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
		return 65;
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
		this.firePageEvent("onDestroy");
		this.fireFrameEvent("onDestroy");

		BX.remove(this.layout.overlay);

		this.layout.container = null;
		this.layout.overlay = null;
		this.layout.content = null;
		this.layout.closeBtn = null;
		this.iframe = null;

		this.destroyed = true;

		if (this.options.events)
		{
			for (var eventName in this.options.events)
			{
				BX.removeCustomEvent(this, BX.SidePanel.Slider.getEventFullName(eventName), this.options.events[eventName]);
			}
		}

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
	adjustLayout: function()
	{
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowHeight = BX.browser.IsMobile() ? window.innerHeight : document.documentElement.clientHeight;

		var topBoundary = this.getTopBoundary();
		var isTopBoundaryVisible = topBoundary - scrollTop > 0;
		topBoundary = isTopBoundaryVisible ? topBoundary : scrollTop;

		var height = isTopBoundaryVisible > 0 ? windowHeight - topBoundary + scrollTop : windowHeight;
		var leftBoundary = Math.max(this.getLeftBoundary(), this.getMinLeftBoundary()) + this.getOffset();

		this.getOverlay().style.left = window.pageXOffset + "px";
		this.getOverlay().style.top = topBoundary + "px";
		this.getOverlay().style.right = this.getRightBoundary() + "px";
		this.getOverlay().style.height = height + "px";

		this.getContainer().style.width = "calc(100% - " + leftBoundary + "px)";
		this.getContainer().style.height = height + "px";

		if (this.getWidth() !== null)
		{
			this.getContainer().style.maxWidth = this.getWidth() + "px";
		}
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
		}
		else
		{
			this.getContentContainer().appendChild(this.getFrame());
			document.body.appendChild(this.getOverlay());
			this.setFrameSrc(); //setFrameSrc must be below than appendChild, otherwise POST method fails.
		}
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
				"src": "about:blank",
				"frameborder": "0"
			},
			props: {
				className: "side-panel-iframe",
				name: this.getFrameId(),
				id: this.getFrameId()
			},
			events: {
				load: this.handleFrameLoad.bind(this)
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
				click: this.handleOverlayClick.bind(this)
			},
			style: {
				zIndex: this.getZindex()
			},
			children: [
				this.getContainer()
			]
		});

		return this.layout.overlay;
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
			style: {
				zIndex: this.getZindex() + 1
			},
			children: [
				this.getContentContainer(),
				this.getCloseBtn(),
				this.getPrintBtn()
			]
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
				className: "side-panel-content-container"
			}
		});

		return this.layout.content;
	},

	/**
	 * @public
	 * @returns {Element}
	 */
	getCloseBtn: function()
	{
		if (this.layout.closeBtn !== null)
		{
			return this.layout.closeBtn;
		}

		this.layout.closeBtn = BX.create("span", {
			props: {
				className: "side-panel-close",
				title: BX.message("MAIN_SIDEPANEL_CLOSE")
			},
			children : [
				BX.create("span", {
					props: {
						className: "side-panel-close-inner"
					}
				})
			],
			events: {
				click: this.handleCloseBtnClick.bind(this)
			}
		});

		return this.layout.closeBtn;
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

		this.showLoader();

		var promise = new BX.Promise();

		promise
			.then(this.contentCallback)
			.then(
				function(result)
				{
					if (this.isDestroyed())
					{
						return;
					}

					if (BX.type.isDomNode(result))
					{
						this.getContentContainer().appendChild(result);
					}
					else if (BX.type.isNotEmptyString(result))
					{
						this.getContentContainer().innerHTML = result;
					}

					this.loaded = true;
					this.firePageEvent("onLoad");

					this.closeLoader();

				}.bind(this),
				function(reason)
				{
					this.destroy();
					BX.debug("error", reason);
				}
			);

		promise.fulfill(this);
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

		this.showLoader();
	},

	/**
	 * @private
	 * @param loader
	 */
	createLoader: function(loader)
	{
		BX.remove(this.layout.loader);

		loader = BX.type.isNotEmptyString(loader) ? loader : "default-loader";

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
		if (BX.util.in_array(loader, oldLoaders) && this.loaderExists(loader))
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

		this.layout.loader.dataset.loader = loader;
		this.getContentContainer().appendChild(this.layout.loader);
	},

	createSvgLoader: function(svg)
	{
		return BX.create("div", {
			props: {
				className: "side-panel-loader-container"
			},
			style: {
				backgroundImage: 'url("' + svg +'")'
			}
		});
	},

	createDefaultLoader: function()
	{
		return BX.create("div", {
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

			var rules = style.rules || style.cssRules;
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
	animateOpening: function()
	{
		BX.addClass(this.getOverlay(), "side-panel-overlay-open");
		BX.addClass(this.getContainer(), "side-panel-container-open");

		if (this.isPrintable())
		{
			this.showPrintBtn();
		}

		if (this.animation)
		{
			this.animation.stop();
		}

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

		this.animation.animate();
	},

	/**
	 * @private
	 * @param {object} state
	 */
	animateStep: function(state)
	{
		this.getContainer().style.transform = "translateX(" + state.translateX + "%)";
		this.getOverlay().style.backgroundColor = "rgba(0, 0, 0, " + state.opacity / 100 + ")";
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

			this.firePageEvent("onOpenComplete");
			this.fireFrameEvent("onOpenComplete");
		}
		else
		{
			this.currentParams = this.startParams;

			BX.removeClass(this.getOverlay(), "side-panel-overlay-open");
			BX.removeClass(this.getContainer(), "side-panel-container-open");

			this.getContainer().style.removeProperty("width");
			this.getContainer().style.removeProperty("right");
			this.getContainer().style.removeProperty("max-width");
			this.getContainer().style.removeProperty("min-width");
			this.getCloseBtn().style.removeProperty("opacity");

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

		var frameWindow = this.getFrameWindow();
		if (frameWindow && frameWindow.BX)
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

		return pageEvent.isActionAllowed() && frameEvent.isActionAllowed();
	},

	/**
	 * @private
	 * @param {Event} event
	 */
	handleFrameLoad: function(event)
	{
		var frameWindow = this.iframe.contentWindow;
		var iframeLocation = frameWindow.location;

		if (iframeLocation.toString() === "about:blank")
		{
			return;
		}

		frameWindow.addEventListener("keydown", this.handleFrameKeyDown.bind(this));
		frameWindow.addEventListener("focus", this.handleFrameFocus.bind(this));

		if (BX.browser.IsMobile())
		{
			frameWindow.document.body.style.paddingBottom = window.innerHeight * 2 / 3 + "px";
		}

		var iframeUrl = iframeLocation.pathname + iframeLocation.search + iframeLocation.hash;
		this.iframeSrc = BX.util.remove_url_param(iframeUrl, ["IFRAME", "IFRAME_TYPE"]);
		this.url = this.iframeSrc;

		if (this.isPrintable())
		{
			this.injectPrintStyles();
		}

		if (this.loaded)
		{
			this.firePageEvent("onLoad");
			this.fireFrameEvent("onLoad");

			this.firePageEvent("onReload");
			this.fireFrameEvent("onReload");
		}
		else
		{
			this.loaded = true;
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

		if (this.canCloseByEsc())
		{
			this.close();
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
	handleCloseBtnClick: function(event)
	{
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
		frameDocument.body.classList.forEach(function(className) {
			bodyClass += "." + className;
		});

		var bodyStyle = "@media print { body" + bodyClass + " { " +
			"background: #fff !important; " +
			"-webkit-print-color-adjust: exact;" +
			"tcolor-adjust: exact; " +
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
	}
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

})();
