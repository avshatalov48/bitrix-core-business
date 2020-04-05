;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;

	/**
	 * Implements interface for works with content panel
	 * Panel contains scrollable sidebar and body area, also fixed footer and header
	 *
	 * @extends {BX.Landing.UI.Panel.BasePanel}
	 *
	 * @param {string} id - Panel id
	 * @param {{
	 * 		[title]: ?string,
	 * 		[subTitle]: string,
	 * 		[footer]: ?BX.Landing.UI.Button.BaseButton[]|HTMLElement[],
	 * 		[className]: string
 	 * }} [data]
	 * @constructor
	 */
	BX.Landing.UI.Panel.Content = function(id, data)
	{
		BX.Landing.UI.Panel.BasePanel.apply(this, arguments);
		this.layout.classList.remove("landing-ui-hide");
		this.data = Object.freeze(BX.type.isPlainObject(data) ? data : {});
		this.layout.classList.add("landing-ui-panel-content");
		this.overlay = BX.Landing.UI.Panel.Content.createOverlay();
		this.overlay.classList.add(this.classHide);
		this.header = BX.Landing.UI.Panel.Content.createHeader();
		this.title = BX.Landing.UI.Panel.Content.createTitle();
		this.body = BX.Landing.UI.Panel.Content.createBody();
		this.footer = BX.Landing.UI.Panel.Content.createFooter();
		this.sidebar = BX.Landing.UI.Panel.Content.createSidebar();
		this.content = BX.Landing.UI.Panel.Content.createContent();
		this.scrollTarget = this.content;
		this.forms = new BX.Landing.UI.Collection.FormCollection();
		this.buttons = new BX.Landing.UI.Collection.ButtonCollection();
		this.sidebarButtons = new BX.Landing.UI.Collection.ButtonCollection();
		this.closeButton = new BX.Landing.UI.Button.BaseButton("close", {
			className: "landing-ui-panel-content-close",
			onClick: this.hide.bind(this),
			attrs: {title: BX.Landing.Loc.getMessage("LANDING_TITLE_OF_SLIDER_CLOSE")}
		});
		this.shouldAdjustTopPanelControls = (
			this.shouldAdjustTopPanelControls !== false
			&& BX.Landing.Env.getInstance().getType() !== 'EXTERNAL'
		);

		if (!!data && typeof data.className === "string")
		{
			this.layout.classList.add(data.className);
			this.overlay.classList.add(data.className+"-overlay");
		}

		this.header.appendChild(this.title);

		if (!!data && typeof data.subTitle === "string" && !!data.subTitle)
		{
			this.subTitle = BX.create("div", {
				props: {className: "landing-ui-panel-content-subtitle"},
				html: data.subTitle
			});

			this.header.appendChild(this.subTitle);
			this.layout.classList.add("landing-ui-panel-content-with-subtitle");
		}

		this.body.appendChild(this.sidebar);
		this.body.appendChild(this.content);

		this.layout.appendChild(this.header);
		this.layout.appendChild(this.body);
		this.layout.appendChild(this.footer);
		this.layout.appendChild(this.closeButton.layout);

		if (typeof window.onwheel !== "undefined")
		{
			this.wheelEventName = "wheel";
		}
		else if (typeof window.onmousewheel !== "undefined")
		{
			this.wheelEventName = "mousewheel";
		}

		this.init();

		var rootWindow = BX.Landing.PageObject.getRootWindow();
		rootWindow.addEventListener("keydown", this.onKeydown.bind(this));

		BX.Landing.PageObject.getInstance().view().then(function(frame) {
			void (!!frame && frame.contentWindow.addEventListener("keydown", this.onKeydown.bind(this)));
		}.bind(this), console.warn);

		if (this.data.scrollAnimation)
		{
			this.scrollObserver = new IntersectionObserver(this.onIntersecting.bind(this));
		}
	};


	/**
	 * Creates overlay
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.Content.createOverlay = function()
	{
		return BX.create("div", {
			props: {className: "landing-ui-panel-content-overlay landing-ui-hide"},
			attrs: {
				"data-is-shown": "false",
				"hidden": true
			}
		});
	};


	/**
	 * Creates header
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.Content.createHeader = function()
	{
		return BX.create("div", {
			props: {
				className: [
					"landing-ui-panel-content-element",
					"landing-ui-panel-content-header"
				].join(" ")
			}
		});
	};


	/**
	 * Creates title
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.Content.createTitle = function()
	{
		return BX.create("div", {
			props: {className: "landing-ui-panel-content-title"}
		});
	};


	/**
	 * Creates body
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.Content.createBody = function()
	{
		return BX.create("div", {
			props: {
				className: [
					"landing-ui-panel-content-element",
					"landing-ui-panel-content-body"
				].join(" ")
			}
		});
	};


	/**
	 * Creates sidebar
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.Content.createSidebar = function()
	{
		return BX.create("div", {
			props: {className: "landing-ui-panel-content-body-sidebar"}
		});
	};


	/**
	 * Creates content
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.Content.createContent = function()
	{
		return BX.create("div", {
			props: {className: "landing-ui-panel-content-body-content"}
		});
	};


	/**
	 * Creates footer
	 * @static
	 * @return {HTMLElement}
	 */
	BX.Landing.UI.Panel.Content.createFooter = function()
	{
		return BX.create("div", {
			props: {
				className: [
					"landing-ui-panel-content-element",
					"landing-ui-panel-content-footer"
				].join(" ")
			}
		});
	};


	/**
	 * Makes transition duration value in milliseconds
	 * @static
	 * @param {int} diff - Absolute difference between the start and end value
	 * @return {number}
	 */
	BX.Landing.UI.Panel.Content.calculateTransitionDuration = function(diff)
	{
		var defaultDuration = 300;
		diff = parseInt(diff);
		diff = diff === diff ? diff : 0;
		return Math.min((400/500) * diff, defaultDuration);
	};


	/**
	 * Animates scroll to node in scrollable container
	 * @static
	 * @param {HTMLElement|HTMLDocument|window} container - Scrollable container
	 * @param {HTMLElement} element - Target element
	 */
	BX.Landing.UI.Panel.Content.scrollTo = function(container, element)
	{
		return new Promise(function(resolve) {
			var elementTop = 0;
			var duration = 0;

			if (element)
			{
				var defaultMargin = 20;
				var elementMarginTop = Math.max(parseInt(BX.style(element, "margin-top")), defaultMargin);
				var containerScrollTop = container.scrollTop;
				if (!(container instanceof HTMLIFrameElement))
				{
					elementTop = element.offsetTop - (container.offsetTop || 0) - elementMarginTop;
				}
				else
				{
					containerScrollTop = container.contentWindow.scrollY;
					elementTop = BX.pos(element).top - elementMarginTop - 100;
				}

				duration = BX.Landing.UI.Panel.Content.calculateTransitionDuration(
					Math.abs(elementTop - containerScrollTop)
				);

				var start = Math.max(containerScrollTop, 0);
				var finish = Math.max(elementTop, 0);

				if (start !== finish)
				{
					(new BX.easing({
						duration: duration,
						start: {scrollTop: start},
						finish: {scrollTop: finish},
						step: function(state) {
							if (!(container instanceof HTMLIFrameElement))
							{
								container.scrollTop = state.scrollTop;
							}
							else
							{
								container.contentWindow.scrollTo(0, Math.max(state.scrollTop, 0));
							}
						}.bind(this)
					})).animate();

					setTimeout(resolve, duration);
				}
				else
				{
					resolve();
				}
			}
			else
			{
				resolve();
			}
		});
	};


	/**
	 * Gets delta values from MouseWheelEvent
	 * @param {WheelEvent} event
	 * @param {number} [event.wheelDeltaX] - Safari only
	 * @param {number} [event.wheelDeltaY] - Safari only
	 * @return {{x: number, y: number}}
	 */
	BX.Landing.UI.Panel.Content.getDeltaFromEvent = function(event)
	{
		var deltaX = event.deltaX;
		var deltaY = -1 * event.deltaY;

		if (typeof deltaX === "undefined" || typeof deltaY === "undefined")
		{
			deltaX = -1 * event.wheelDeltaX / 6;
			deltaY = event.wheelDeltaY / 6;
		}

		if (event.deltaMode && event.deltaMode === 1)
		{
			deltaX *= 10;
			deltaY *= 10;
		}

		/** NaN checks */
		if (deltaX !== deltaX && deltaY !== deltaY)
		{
			deltaX = 0;
			deltaY = event.wheelDelta;
		}

		return {x: deltaX, y: deltaY};
	};


	BX.Landing.UI.Panel.Content.prototype = {
		constructor: BX.Landing.UI.Panel.Content,
		__proto__: BX.Landing.UI.Panel.BasePanel.prototype,
		init: function()
		{
			document.body.appendChild(this.overlay);
			this.overlay.addEventListener("click", this.hide.bind(this));
			this.layout.addEventListener("mouseenter", this.onMouseEnter.bind(this));
			this.layout.addEventListener("mouseleave", this.onMouseLeave.bind(this));
			this.content.addEventListener("mouseenter", this.onMouseEnter.bind(this));
			this.content.addEventListener("mouseleave", this.onMouseLeave.bind(this));
			this.sidebar.addEventListener("mouseenter", this.onMouseEnter.bind(this));
			this.sidebar.addEventListener("mouseleave", this.onMouseLeave.bind(this));
			this.header.addEventListener("mouseenter", this.onMouseEnter.bind(this));
			this.header.addEventListener("mouseleave", this.onMouseLeave.bind(this));
			this.footer.addEventListener("mouseenter", this.onMouseEnter.bind(this));
			this.footer.addEventListener("mouseleave", this.onMouseLeave.bind(this));

			requestAnimationFrame(function() {
				if (this.right)
				{
					this.right.addEventListener("mouseenter", this.onMouseEnter.bind(this));
					this.right.addEventListener("mouseleave", this.onMouseLeave.bind(this));
				}
			}.bind(this));

			if ("title" in this.data)
			{
				this.setTitle(this.data.title);
			}

			if ("footer" in this.data)
			{
				if (BX.type.isArray(this.data.footer))
				{
					this.data.footer.forEach(function(item) {
						if (item instanceof BX.Landing.UI.Button.BaseButton)
						{
							this.appendFooterButton(item);
						}

						if (BX.type.isDomNode(item))
						{
							this.footer.appendChild(item);
						}
					}, this);
				}
			}
		},

		onIntersecting: function(items)
		{
			items.forEach(function(item) {
				if (item.isIntersecting)
				{
					removeClass(item.target, "landing-ui-is-not-visible");
					addClass(item.target, "landing-ui-is-visible");
				}
				else
				{
					addClass(item.target, "landing-ui-is-not-visible");
					removeClass(item.target, "landing-ui-is-visible");
				}
			});
		},

		onKeydown: function(event)
		{
			if (event.keyCode === 27)
			{
				this.hide();
			}
		},

		onMouseEnter: function(event)
		{
			event.stopPropagation();

			BX.bind(this.layout, this.wheelEventName, BX.proxy(this.onMouseWheel, this));
			BX.bind(this.layout, "touchmove", BX.proxy(this.onMouseWheel, this));

			if (this.sidebar.contains(event.target) ||
				this.content.contains(event.target) ||
				this.header.contains(event.target) ||
				this.footer.contains(event.target) ||
				(this.right && this.right.contains(event.target)))
			{
				this.scrollTarget = event.currentTarget;
			}
		},


		onMouseLeave: function(event)
		{
			event.stopPropagation();
			BX.unbind(this.layout, this.wheelEventName, BX.proxy(this.onMouseWheel, this));
			BX.unbind(this.layout, "touchmove", BX.proxy(this.onMouseWheel, this));
		},


		onMouseWheel: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			var delta = BX.Landing.UI.Panel.Content.getDeltaFromEvent(event);
			var scrollTop = this.scrollTarget.scrollTop;

			requestAnimationFrame(function() {
				this.scrollTarget.scrollTop = scrollTop - delta.y;
			}.bind(this));
		},


		/**
		 * Scroll content area to element
		 * @param {HTMLElement} element
		 */
		scrollTo: function(element)
		{
			BX.Landing.UI.Panel.Content.scrollTo(this.content, element);
		},


		/**
		 * Checks that panel is shown
		 * @return {boolean}
		 */
		isShown: function()
		{
			return this.state === "shown";
		},


		/**
		 * Shows panel
		 */
		show: function()
		{
			if (!this.isShown())
			{
				if (this.shouldAdjustTopPanelControls)
				{
					BX.Landing.UI.Panel.Top.getInstance().disableHistory();
					BX.Landing.UI.Panel.Top.getInstance().disableDevices();
				}

				void BX.Landing.Utils.Show(this.overlay);
				return BX.Landing.Utils.Show(this.layout)
					.then(function() {
						this.state = "shown";
					}.bind(this));
			}

			return Promise.resolve(true);
		},


		/**
		 * Hides panel
		 */
		hide: function()
		{
			var promise = Promise.resolve(true);
			if (this.isShown())
			{
				if (this.shouldAdjustTopPanelControls)
				{
					BX.Landing.UI.Panel.Top.getInstance().enableHistory();
					BX.Landing.UI.Panel.Top.getInstance().enableDevices();
				}

				void BX.Landing.Utils.Hide(this.overlay);
				return BX.Landing.Utils.Hide(this.layout)
					.then(function() {
						this.state = "hidden";
					}.bind(this));
			}

			return promise;
		},


		/**
		 * Appends form to panel body
		 * @param {BX.Landing.UI.Form.BaseForm} form
		 */
		appendForm: function(form)
		{
			this.forms.add(form);
			this.content.appendChild(form.getNode());
		},


		/**
		 * Appends card
		 * @param {BX.Landing.UI.Card.BaseCard} card
		 */
		appendCard: function(card)
		{
			if (this.data.scrollAnimation)
			{
				addClass(card.layout, "landing-ui-is-not-visible");
				this.scrollObserver.observe(card.layout);
			}

			this.content.appendChild(card.layout);
		},


		/**
		 * Clears all content
		 */
		clear: function()
		{
			this.clearContent();
			this.clearSidebar();
			this.forms.clear();
		},


		/**
		 * Clears content
		 */
		clearContent: function()
		{
			this.content.innerHTML = "";
		},


		/**
		 * Clears sidebar
		 */
		clearSidebar: function()
		{
			this.sidebar.innerHTML = "";
		},


		/**
		 * Sets panel title
		 * @param {?string} value
		 */
		setTitle: function(value)
		{
			this.title.innerHTML = value;
		},


		/**
		 * Appends button to footer
		 * @param {BX.Landing.UI.Button.BaseButton} button
		 */
		appendFooterButton: function(button)
		{
			this.buttons.add(button);
			this.footer.appendChild(button.layout);
		},

		/**
		 * Appends button to sidebar
		 * @param {BX.Landing.UI.Button.BaseButton} button
		 */
		appendSidebarButton: function(button)
		{
			this.sidebarButtons.add(button);
			this.sidebar.appendChild(button.layout);
		}
	};
})();