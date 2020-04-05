;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Tool");

	var isEmpty = BX.Landing.Utils.isEmpty;
	var isNumber = BX.Landing.Utils.isNumber;
	var bind = BX.Landing.Utils.bind;
	var unbind = BX.Landing.Utils.unbind;
	var proxy = BX.Landing.Utils.proxy;
	var create = BX.Landing.Utils.create;

	var Popup = BX.Landing.UI.Tool.Popup;

	/**
	 * Implements interface for works with suggest popup
	 * @constructor
	 */
	BX.Landing.UI.Tool.Suggest = function()
	{
		this.lastElement = null;
		this.popup = null;
		this.popupTimeout = 0;

		bind(document, "mousedown", proxy(this.hide, this));
	};


	/**
	 * Stores instance
	 * @type {?BX.Landing.UI.Tool.Suggest}
	 */
	BX.Landing.UI.Tool.Suggest.instance = null;


	/**
	 * Gets instance
	 * @return {BX.Landing.UI.Tool.Suggest}
	 */
	BX.Landing.UI.Tool.Suggest.getInstance = function()
	{
		return (
			BX.Landing.UI.Tool.Suggest.instance ||
			(BX.Landing.UI.Tool.Suggest.instance = new BX.Landing.UI.Tool.Suggest())
		);
	};


	BX.Landing.UI.Tool.Suggest.prototype = {
		/**
		 * Creates content layout
		 * @param {object} options
		 * @return {HTMLElement}
		 */
		createContent: function(options)
		{
			var children = [];

			if (!isEmpty(options.name))
			{
				children.push(
					create("div", {
						props: {className: "landing-ui-field-link-media-help-popup-content-title"},
						html: options.name || options.title
					})
				);
			}

			if (!isEmpty(options.description))
			{
				children.push(
					create("div", {
						props: {className: "landing-ui-field-link-media-help-popup-content-content"},
						html: options.description
					})
				);
			}

			return create("div", {
				props: {className: "landing-ui-field-link-media-help-popup-content"},
				children: children
			});
		},


		/**
		 * Shows suggest popup
		 * @param {HTMLElement} element
		 * @param {{[name]: string, [description]: string, angleOffset: int}} options
		 */
		show: function(element, options)
		{
			if (this.popup === null)
			{
				this.popup = new Popup({
					id: "landing_suggests_popup",
					offsetLeft: -20,
					angle: {offset: 74}
				});
			}

			if (!isNumber(options.angleOffset))
			{
				options.angleOffset = 74;
			}

			this.popup.setBindElement(element);
			this.popup.setContent(this.createContent(options));
			this.lastElement = element;
			this.popupTimeout = showLater.apply(this);

			function showLater()
			{
				return setTimeout(function() {
					bind(element, "mouseleave", proxy(this.hide, this));
					this.popup.show();
					this.popup.setAngle({offset: options.angleOffset, position: "top"});
				}.bind(this), 200);
			}
		},


		/**
		 * Hides suggest popup
		 */
		hide: function()
		{
			if (this.popup && this.popup.isShown())
			{
				unbind(this.lastElement, "mouseleave", proxy(this.hide, this));
				clearTimeout(this.popupTimeout);
				this.popup.close();
			}
		}
	}
})();