;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Panel");

	var create = BX.Landing.Utils.create;

	if (window.localStorage && window.localStorage.getItem("selectGroup") === null)
	{
		window.localStorage.setItem("selectGroup", "true");
	}

	/**
	 * Implements style panel interface
	 *
	 * @extends {BX.Landing.UI.Panel.Content}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Panel.StylePanel = function(id, data)
	{
		BX.Landing.UI.Panel.Content.apply(this, arguments);
		this.layout.classList.add("landing-ui-panel-style");
		this.overlay.classList.add("landing-ui-panel-style-overlay");
		this.layout.hidden = true;
		this.title.innerText = BX.message("LANDING_DESIGN_PANEL_HEADER");
		this.pseudoContent = BX.clone(this.content);
		this.pseudoContent.innerHTML = "";
		this.pseudoContent.style.marginLeft = "20px";
		this.body.appendChild(this.pseudoContent);

		this.loader = new BX.Landing.UI.Card.Loader();
		this.loader.layout.classList.add("landing-ui-card-loader-style");
		this.pseudoContent.appendChild(this.loader.layout);

		this.switcher = new BX.Landing.UI.Field.Switch({
			title: BX.message("LANDING_STYLE_PANEL_SELECT_GROUP_SWITCH"),
			onValueChange: function() {
				if (window.localStorage)
				{
					if (this.switcher.getValue())
					{
						window.localStorage.setItem("selectGroup", "true");
					}
					else
					{
						window.localStorage.setItem("selectGroup", "false");
					}
				}
			}.bind(this),
			value: window.localStorage.getItem("selectGroup") === "true"
		});

		this.footer.appendChild(this.switcher.layout);

		BX.prepend(this.layout, window.top.document.body.querySelector(".landing-ui-view-container"));
	};


	/**
	 * Gets panel instance
	 * @static
	 * @returns {BX.Landing.UI.Panel.StylePanel}
	 */
	BX.Landing.UI.Panel.StylePanel.getInstance = function()
	{
		if (!window.top.BX.Landing.UI.Panel.StylePanel.instance && !BX.Landing.UI.Panel.StylePanel.instance)
		{
			window.top.BX.Landing.UI.Panel.StylePanel.instance = new BX.Landing.UI.Panel.StylePanel();
		}

		return (window.top.BX.Landing.UI.Panel.StylePanel.instance || BX.Landing.UI.Panel.StylePanel.instance);
	};


	/**
	 * @static
	 * @type {BX.Landing.UI.Panel.StylePanel}
	 */
	BX.Landing.UI.Panel.StylePanel.instance = null;


	BX.Landing.UI.Panel.StylePanel.prototype = {
		constructor: BX.Landing.UI.Panel.StylePanel,
		__proto__: BX.Landing.UI.Panel.Content.prototype,

		show: function()
		{
			this.content.hidden = true;
			this.pseudoContent.hidden = false;

			return new Promise(function(resolve) {
				BX.Landing.UI.Panel.Content.prototype.show.call(this).then(function() {
					this.loader.show();
					setTimeout(function() {
						this.content.hidden = false;
						this.pseudoContent.hidden = true;

						BX.Landing.PageObject.getInstance().view().then(function(frame) {
							frame.contentDocument.body.classList.add("landing-ui-collapsed");
						});
					}.bind(this), 400);

					BX.DOM.write(function() {
						top.document.querySelector(".landing-ui-view-container").style.width = "calc(100% - 320px)";
					}.bind(this));

					resolve(this);
					BX.onCustomEvent("BX.Landing.Style:enable", []);
				}.bind(this));
			}.bind(this));
		},

		hide: function()
		{
			return new Promise(function(resolve) {
				setTimeout(function() {
					BX.Landing.UI.Panel.Content.prototype.hide.call(this).then(function() {
						BX.Landing.PageObject.getInstance().view().then(function(frame) {
							frame.contentDocument.body.classList.remove("landing-ui-collapsed");
						});

						BX.DOM.write(function() {
							top.document.querySelector(".landing-ui-view-container").style.width = null;
						}.bind(this));

						resolve(this);
						BX.onCustomEvent("BX.Landing.Style:disable", []);
					}.bind(this));
				}.bind(this), 100);

			}.bind(this));
		}
	};
})();