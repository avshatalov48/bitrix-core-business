;(function() {
	"use strict";

	BX.namespace("BX");


	if (typeof BX.Loader !== "undefined")
	{
		return;
	}


	var STATE_READY = "ready";
	var STATE_SHOWN = "shown";
	var STATE_HIDDEN = "hidden";


	/**
	 * Shows element
	 * @param {HTMLElement} element
	 * @return {BX.Promise}
	 */
	function show(element)
	{
		var promise = new BX.Promise();

		if (!!element && (element.dataset.isShown === "false" || !element.dataset.isShown))
		{
			var handler = function(event) {
				promise.fulfill(event);
				element.removeEventListener("animationend", handler);
				element.removeEventListener("oAnimationEnd", handler);
				element.removeEventListener("webkitAnimationEnd", handler);
			};

			element.addEventListener("animationend", handler);
			element.addEventListener("oAnimationEnd", handler);
			element.addEventListener("webkitAnimationEnd", handler);

			requestAnimationFrame(function() {
				element.dataset.isShown = true;
				element.style.display = null;
				element.classList.remove("main-ui-hide");
				element.classList.add("main-ui-show");
			});
		}
		else
		{
			promise.fulfill();
		}

		return promise;
	}


	/**
	 * Hides element
	 * @param {HTMLElement} element
	 * @return {BX.Promise}
	 */
	function hide(element)
	{
		var promise = new BX.Promise();

		if (!!element && element.dataset.isShown === "true")
		{
			var handler = function(event) {
				element.style.display = "none";
				element.removeEventListener("animationend", handler);
				element.removeEventListener("oAnimationEnd", handler);
				element.removeEventListener("webkitAnimationEnd", handler);
				promise.fulfill(event);
			};

			element.addEventListener("animationend", handler);
			element.addEventListener("oAnimationEnd", handler);
			element.addEventListener("webkitAnimationEnd", handler);

			requestAnimationFrame(function() {
				element.dataset.isShown = false;
				element.classList.remove("main-ui-show");
				element.classList.add("main-ui-hide");
			});
		}
		else
		{
			promise.fulfill();
		}

		return promise;
	}


	/**
	 * Applies options to loader
	 * @param {BX.Loader} loader
	 * @param {loaderOptions} options
	 */
	function applyOptions(loader, options)
	{
		var layoutStyles = {};
		var circleStyles = {};

		if (typeof options.target === "object" && !!options.target)
		{
			loader.target = options.target;
		}

		if (typeof options.size === "number")
		{
			layoutStyles["width"] = options.size + "px";
			layoutStyles["height"] = options.size + "px";
		}

		if (typeof options.color === "string")
		{
			circleStyles["stroke"] = options.color;
		}

		if (typeof options.offset === "object" && !!options.offset)
		{
			if (typeof options.offset.top === "string")
			{
				layoutStyles[options.mode === "inline" || options.mode === "custom" ? "top" : "margin-top"] = options.offset.top;
			}

			if (typeof options.offset.left === "string")
			{
				layoutStyles[options.mode === "inline" || options.mode === "custom" ? "left" : "margin-left"] = options.offset.left;
			}
		}

		if (options.mode === "inline")
		{
			loader.layout.classList.add("main-ui-loader-inline");
		}
		else
		{
			loader.layout.classList.remove("main-ui-loader-inline");
		}

		if (options.mode === "custom")
		{
			loader.layout.classList.add("main-ui-loader-custom");
			loader.layout.classList.remove("main-ui-loader-inline");
		}

		requestAnimationFrame(function() {
			for (var layoutProp in layoutStyles)
			{
				if (layoutStyles.hasOwnProperty(layoutProp))
				{
					loader.layout.style[layoutProp] = layoutStyles[layoutProp];
				}
			}

			for (var circleProp in circleStyles)
			{
				if (circleStyles.hasOwnProperty(circleProp))
				{
					loader.circle.style[circleProp] = circleStyles[circleProp];
				}
			}
		});
	}


	/**
	 * @typedef {object} loaderOptions
	 * @property {HTMLElement} [target]
	 * @property {int} [size = 110] - Loader size
	 * @property {string} [color = #BFC3C8]
	 * @property {string} [mode = "absolute"] - absolute|inline|custom
	 * @property {offsetOptions} [offset]
	 */
	/**
	 * @typedef {object} offsetOptions
	 * @property {string} [top]
	 * @property {string} [left]
	 */
	/**
	 * Implements interface for works with loader
	 * @param {loaderOptions} options
	 * @constructor
	 */
	BX.Loader = function(options)
	{
		this.state = STATE_READY;
		this.layout = this.createLayout();
		this.circle = this.layout.querySelector(".main-ui-loader-svg-circle");
		this.target = null;
		applyOptions(this, options);
	};


	BX.Loader.prototype = {
		/**
		 * Creates loader layout Element
		 * @return {HTMLElement}
		 */
		createLayout: function() {
			var loader = "" +
				"<div class=\"main-ui-loader\">" +
					"<svg class=\"main-ui-loader-svg\" viewBox=\"25 25 50 50\">" +
						"<circle class=\"main-ui-loader-svg-circle\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>" +
					"</svg>" +
				"</div>";
			return BX.create("div", {html: loader}).firstElementChild;
		},


		/**
		 * Shows loader
		 * @param {HTMLElement} [target = this.target]
		 * @return {BX.Promise}
		 */
		show: function(target)
		{
			var promise = new BX.Promise();
			promise.setAutoResolve();

			target = !!target ? target : this.target;

			if (!!target && target !== this.layout.parentNode)
			{
				target.appendChild(this.layout);
			}

			if (this.state !== STATE_SHOWN)
			{
				this.state = STATE_SHOWN;
				promise = show(this.layout);
			}

			return promise;
		},


		/**
		 * Hides loader
		 * @return {BX.Promise}
		 */
		hide: function()
		{
			var promise = new BX.Promise();
			promise.setAutoResolve();

			if (this.state !== STATE_HIDDEN)
			{
				this.state = STATE_HIDDEN;
				promise = hide(this.layout);
			}

			return promise;
		},


		/**
		 * Checks that loader is shown
		 * @return {boolean}
		 */
		isShown: function()
		{
			return this.state === STATE_SHOWN;
		},


		/**
		 * Sets loader options
		 * @param {loaderOptions} options
		 */
		setOptions: function(options)
		{
			applyOptions(this, options);
		},


		/**
		 * Destroys loader
		 */
		destroy: function()
		{
			this.layout.remove();
		}
	};
})();