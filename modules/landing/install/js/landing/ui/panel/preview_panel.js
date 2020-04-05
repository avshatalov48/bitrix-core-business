;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");


	/**
	 * Implements preview panel interface
	 *
	 * @extends {BX.Landing.UI.Panel.Content}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Panel.Preview = function(id, data)
	{
		this.size = null;
		this.headerButtons = new BX.Landing.UI.Collection.ButtonCollection();
		this.iframe = BX.Landing.UI.Panel.Preview.createIframe();
		this.iframe.dataset.postfix = "";
		BX.Landing.UI.Panel.Content.apply(this, arguments);

		// Add class names
		this.layout.classList.add("landing-ui-panel-preview");
		this.overlay.classList.add("landing-ui-panel-preview-overlay");

		// Bind on iFrame events
		this.iframe.addEventListener("load", this.onFrameLoad.bind(this));
		this.iframe.style.opacity = 0;

		BX.remove(this.footer);
	};


	BX.Landing.UI.Panel.Preview.createIframe = function()
	{
		return BX.create("iframe", {props: {
			className: "landing-ui-panel-preview-iframe",
			src: BX.util.add_url_param(window.location.toString(), {"landing_mode": "style"})
		}});
	};


	BX.Landing.UI.Panel.Preview.prototype = {
		constructor: BX.Landing.UI.Panel.Preview,
		__proto__: BX.Landing.UI.Panel.Content.prototype,
		superclass: BX.Landing.UI.Panel.Content.prototype,

		init: function()
		{
			// Init super class
			this.superclass.init.call(this);

			// Add desktop button
			this.addHeaderButton(new BX.Landing.UI.Button.BaseButton("desktop_button", {
				className: ["landing-ui-button-desktop", "active"],
				onClick: this.onDesktopSizeChange.bind(this)
			}));

			// Add tablet button
			this.addHeaderButton(new BX.Landing.UI.Button.BaseButton("tablet_button", {
				className: "landing-ui-button-tablet",
				onClick: this.onTabletSizeChange.bind(this)
			}));

			// Add mobile button
			this.addHeaderButton(new BX.Landing.UI.Button.BaseButton("mobile_button", {
				className: "landing-ui-button-mobile",
				onClick: this.onMobileSizeChange.bind(this)
			}));


			// Create custom size field
			this.size = new BX.Landing.UI.Field.Unit({
				selector: "size",
				content: "0",
				unit: "px",
				onInput: this.onSizeInput.bind(this),
				className: "landing-ui-panel-preview-size",
				min: 320,
				max: 12000,
				step: 1
			});

			this.title.appendChild(this.size.layout);

			// Add iFrame to content area of panel
			this.content.appendChild(this.iframe);
		},


		/**
		 * Handles size input event
		 */
		onSizeInput: function(field)
		{
			requestAnimationFrame(function() {
				this.iframe.style.width = field.getValue() + "px";
			}.bind(this));
		},


		/**
		 * Handles iFrame load event
		 */
		onFrameLoad: function()
		{
			this.size.setValue(this.body.getBoundingClientRect().width);
			this.iframe.contentWindow.scrollTo(0, window.scrollY);
			requestAnimationFrame(function() {
				this.iframe.style.opacity = 1;
			}.bind(this));
		},


		/**
		 * Handles desktop size change event
		 */
		onDesktopSizeChange: function()
		{
			this.headerButtons.forEach(function(button) {
				button.layout.classList.remove("active");
			});

			this.headerButtons.get("desktop_button").layout.classList.add("active");
			this.size.setValue(this.body.getBoundingClientRect().width);
			this.iframe.dataset.postfix = "";
		},


		/**
		 * Handles tablet size change event
		 */
		onTabletSizeChange: function()
		{
			this.headerButtons.forEach(function(button) {
				button.layout.classList.remove("active");
			});

			this.headerButtons.get("tablet_button").layout.classList.add("active");
			this.size.setValue(767);
			this.iframe.dataset.postfix = "--md";
		},


		/**
		 * Handles mobile size change event
		 */
		onMobileSizeChange: function()
		{
			this.headerButtons.forEach(function(button) {
				button.layout.classList.remove("active");
			});

			this.headerButtons.get("mobile_button").layout.classList.add("active");
			this.size.setValue(479);
			this.iframe.dataset.postfix = "--sm";
		},


		/**
		 * Shows preview panel
		 */
		show: function()
		{
			this.superclass.show.call(this);
			document.documentElement.style.overflow = "hidden";
			this.iframe.contentWindow.scrollTo(0, window.scrollY);
		},


		/**
		 * Hides preview panel
		 */
		hide: function()
		{
			this.superclass.hide.call(this);
			document.documentElement.style.overflow = null;
		},


		/**
		 * Appends header button
		 * @param {BX.Landing.UI.Button.BaseButton} button
		 */
		addHeaderButton: function(button)
		{
			this.title.appendChild(button.layout);
			this.headerButtons.add(button);
		}
	};
})();