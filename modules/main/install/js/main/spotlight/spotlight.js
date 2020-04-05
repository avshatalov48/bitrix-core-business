;(function ()
{
	"use strict";

	BX.SpotLight = function (options)
	{
		this.container = null;
		this.popup = null;
		this.id = "spotlight-" + BX.util.getRandomString().toLowerCase();
		this.options = {};

		this.targetElement = null;
		this.targetElementRect = null;
		this.targetVertex = "top-left";
		this.content = null;
		this.top = 0;
		this.left = 0;
		this.lightMode = false;
		this.autoSave = false;
		this.zIndex = null;

		this.observerTimeoutId = null;
		this.observerTimeout = 1000;

		this.setOptions(options);

		if (!this.targetElement)
		{
			throw new Error("BX.SpotLight: 'targetElement' is not a DOMNode.");
		}

		this.handlePageResize = this.handlePageResize.bind(this);
	};

	BX.SpotLight.prototype =
	{
		setOptions: function(options)
		{
			options = BX.type.isPlainObject(options) ? options : {};

			this.options = options;

			this.setTargetElement(options.renderTo); //compatibility
			this.setTargetElement(options.targetElement);
			this.setTargetVertex(options.targetVertex);
			this.setZindex(options.zIndex);
			this.setLightMode(options.lightMode);
			this.setContent(options.content);
			this.setOffsetLeft(options.left);
			this.setOffsetTop(options.top);
			this.setAutoSave(options.autoSave);
			this.setObserverTimeout(options.observerTimeout);
			this.setId(options.id);
		},

		bindEvents: function(events)
		{
			if (!BX.type.isPlainObject(events))
			{
				return;
			}

			for (var eventName in events)
			{
				var cb = BX.type.isFunction(events[eventName]) ? events[eventName] : BX.getClass(events[eventName]);
				if (cb)
				{
					BX.addCustomEvent(this, this.getFullEventName(eventName), cb);
				}
			}
		},

		unbindEvents: function(events)
		{
			if (!BX.type.isPlainObject(events))
			{
				return;
			}

			for (var eventName in events)
			{
				var cb = BX.type.isFunction(events[eventName]) ? events[eventName] : BX.getClass(events[eventName]);
				if (cb)
				{
					BX.removeCustomEvent(this, this.getFullEventName(eventName), cb);
				}
			}
		},

		getOptions: function()
		{
			return this.options;
		},

		getId: function()
		{
			return this.id;
		},

		setId: function(id)
		{
			if (BX.type.isNotEmptyString(id))
			{
				this.id = id;
			}
		},

		getZindex: function()
		{
			if (this.zIndex !== null)
			{
				return this.zIndex;
			}

			return this.getGlobalIndex(this.getTargetElement()) + 1;
		},

		getGlobalIndex: function(element)
		{
			var index = 0;

			do
			{
				var propertyValue = BX.style(element, "z-index");
				if (propertyValue !== "auto")
				{
					index = BX.type.stringToInt(propertyValue);
				}

				element = element.parentNode;
			}
			while (
				element && element.tagName !== "BODY"
			);

			return index;
		},

		setZindex: function(zIndex)
		{
			if (BX.type.isNumber(zIndex) || zIndex === null)
			{
				this.zIndex = zIndex;
			}
		},

		getContent: function()
		{
			return this.content;
		},

		setContent: function(content)
		{
			if (BX.type.isNotEmptyString(content) || BX.type.isDomNode(content) || content === null)
			{
				this.content = content;
			}
		},

		getTargetElement: function()
		{
			return this.targetElement;
		},

		setTargetElement: function(targetElement)
		{
			if (BX.type.isNotEmptyString(targetElement))
			{
				targetElement = document.querySelector(targetElement) || BX(targetElement);
			}

			if (BX.type.isDomNode(targetElement))
			{
				this.targetElement = targetElement;
				this.renderTo = targetElement; //compatibility
			}
		},

		getOffsetLeft: function()
		{
			return this.left;
		},

		setOffsetLeft: function(offset)
		{
			if (BX.type.isNumber(offset))
			{
				this.left = offset;
			}
		},

		getOffsetTop: function()
		{
			return this.top;
		},

		setOffsetTop: function(offset)
		{
			if (BX.type.isNumber(offset))
			{
				this.top = offset;
			}
		},

		getLightMode: function()
		{
			return this.lightMode;
		},

		setLightMode: function(mode)
		{
			if (BX.type.isBoolean(mode))
			{
				this.lightMode = mode;
			}
		},

		getAutoSave: function()
		{
			return this.autoSave;
		},

		setAutoSave: function(mode)
		{
			if (BX.type.isBoolean(mode))
			{
				this.autoSave = mode;
			}
		},

		getObserverTimeout: function()
		{
			return this.observerTimeout;
		},

		setObserverTimeout: function(timeout)
		{
			if (BX.type.isNumber(timeout) && timeout >= 0)
			{
				this.observerTimeout = timeout;
			}
		},

		getTargetVertex: function()
		{
			return this.targetVertex;
		},

		setTargetVertex: function(vertex)
		{
			if (BX.type.isNotEmptyString(vertex))
			{
				this.targetVertex = vertex;
			}
		},

		getPopup: function()
		{
			if (this.popup)
			{
				return this.popup;
			}

			this.popup = new BX.PopupWindow("spotlight-" + BX.util.getRandomString(), this.container, {
				className: "main-spot-light-popup",
				angle: {
					position: "top",
					offset: 41
				},
				closeByEsc: true,
				overlay: true,
				content: this.getContent(),
				events: {
					onPopupShow: function() {
						this.fireEvent("onPopupShow");
					}.bind(this),
					onPopupClose: function() {
						this.close();
						this.fireEvent("onPopupClose");
					}.bind(this)
				},
				buttons: [
					new BX.PopupWindowCustomButton({
						text: BX.message("MAIN_SPOTLIGHT_UNDERSTAND"),
						className: "webform-small-button webform-small-button-blue",
						events: {
							click: function() {
								this.close();
								this.fireEvent("onPopupAccept");

								BX.onCustomEvent(this, "spotLightOk", [this.getTargetElement(), this]); //compatibility
							}.bind(this)
						}
					})
				]
			});

			return this.popup;
		},

		getTargetContainer: function()
		{
			if (this.container)
			{
				return this.container;
			}

			this.container = BX.create("div", {
				attrs: {
					className: this.getLightMode() ? "main-spot-light main-spot-light-white" : "main-spot-light"
				},
				events: {
					mouseenter: this.handleTargetMouseEnter.bind(this),
					mouseleave: this.handleTargetMouseLeave.bind(this)
				}
			});

			if ("ontouchstart" in window)
			{
				BX.bind(this.container, "touchstart", this.handleTargetMouseEnter.bind(this));
			}

			return this.container;
		},

		adjustPosition: function()
		{
			this.targetElementRect = BX.pos(this.getTargetElement());

			var targetElement = this.getTargetElement();
			var isVisible = Boolean(
				targetElement.offsetWidth || targetElement.offsetHeight || targetElement.getClientRects().length
			);

			if (!isVisible)
			{
				this.container.hidden = true;
				return;
			}

			var left = 0;
			var top = 0;

			var vertex = this.getTargetVertex();
			switch (vertex)
			{
				case "top-left":
				default:
					left = this.targetElementRect.left;
					top = this.targetElementRect.top;
					break;
				case "top-center":
					left = this.targetElementRect.left + this.targetElementRect.width / 2;
					top = this.targetElementRect.top;
					break;
				case "top-right":
					left = this.targetElementRect.right;
					top = this.targetElementRect.top;
					break;
				case "middle-left":
					left = this.targetElementRect.left;
					top = this.targetElementRect.top + this.targetElementRect.height / 2;
					break;
				case "middle-center":
					left = this.targetElementRect.left + this.targetElementRect.width / 2;
					top = this.targetElementRect.top + this.targetElementRect.height / 2;
					break;
				case "middle-right":
					left = this.targetElementRect.right;
					top = this.targetElementRect.top + this.targetElementRect.height / 2;
					break;
				case "bottom-left":
					left = this.targetElementRect.left;
					top = this.targetElementRect.bottom;
					break;
				case "bottom-center":
					left = this.targetElementRect.left + this.targetElementRect.width / 2;
					top = this.targetElementRect.bottom;
					break;
				case "bottom-right":
					left = this.targetElementRect.right;
					top = this.targetElementRect.bottom;
					break;
			}

			this.container.hidden = false;
			this.container.style.left = left + this.getOffsetLeft() + "px";
			this.container.style.top = top + this.getOffsetTop() + "px";
			this.container.style.zIndex = this.getZindex();
		},

		handlePageResize: function()
		{
			this.adjustPosition();
		},

		handleTargetMouseEnter: function()
		{
			this.fireEvent("onTargetEnter");

			if (this.getContent())
			{
				this.getPopup().show();
			}

			if (this.getAutoSave())
			{
				this.save();
			}
		},

		handleTargetMouseLeave: function()
		{
			this.fireEvent("onTargetLeave");
		},

		handleTargetElementResize: function()
		{
			var currentRect = BX.pos(this.getTargetElement());
			if (
				currentRect.left !== this.targetElementRect.left ||
				currentRect.right !== this.targetElementRect.right ||
				currentRect.top !== this.targetElementRect.top ||
				currentRect.bottom !== this.targetElementRect.bottom
			)
			{
				this.adjustPosition();
			}
		},

		show: function()
		{
			if (!this.getTargetContainer().parentNode)
			{
				BX.bind(window, "resize", this.handlePageResize);
				BX.bind(window, "load", this.handlePageResize);
				BX.addCustomEvent("onFrameDataProcessed", this.handlePageResize);

				this.bindEvents(this.getOptions().events);

				document.body.appendChild(this.getTargetContainer());

				if (this.getObserverTimeout())
				{
					this.observerTimeoutId = setInterval(
						this.handleTargetElementResize.bind(this),
						this.getObserverTimeout()
					);
				}
			}

			this.fireEvent("onShow");
			this.adjustPosition();
		},

		close: function()
		{
			this.fireEvent("onClose");

			if (this.popup)
			{
				this.popup.destroy();
				this.popup = null;
			}

			if (this.observerTimeoutId)
			{
				clearInterval(this.observerTimeoutId);
				this.observerTimeoutId = null;
			}

			BX.unbind(window, "resize", this.handlePageResize);
			BX.unbind(window, "load", this.handlePageResize);
			BX.removeCustomEvent("onFrameDataProcessed", this.handlePageResize);

			this.unbindEvents(this.getOptions().events);

			BX.remove(this.container);
			this.container = null;
		},

		save: function()
		{
			var optionName = "view_date_" + this.getId();
			BX.userOptions.save("spotlight", optionName, null, Math.floor(Date.now() / 1000));
			BX.userOptions.send(null);
		},

		fireEvent: function(eventName)
		{
			if (BX.type.isNotEmptyString(eventName))
			{
				BX.onCustomEvent(this, this.getFullEventName(eventName), [this]);
			}
		},

		getFullEventName: function(shortName)
		{
			return "BX.SpotLight:" + shortName;
		}
	};

	BX.SpotLight.Manager =
	{
		spotlights: {},

		/**
		 *
		 * @param {object} options
		 * @returns {BX.SpotLight}
		 */
		create: function(options)
		{
			options = BX.type.isPlainObject(options) ? options : {};

			var id = options.id;
			if (!BX.type.isNotEmptyString(id))
			{
				throw new Error("'id' parameter is required.")
			}

			if (this.get(id))
			{
				throw new Error("The spotlight instance with the same 'id' already exists.");
			}

			var spotlight = new BX.SpotLight(options);
			this.spotlights[id] = spotlight;

			return spotlight;
		},

		/**
		 *
		 * @param {string} id
		 * @returns {BX.SpotLight|null}
		 */
		get: function(id)
		{
			return id in this.spotlights ? this.spotlights[id] : null;
		},

		/**
		 *
		 * @param {string} id
		 */
		remove: function(id)
		{
			delete this.spotlights[id];
		}
	};
})();