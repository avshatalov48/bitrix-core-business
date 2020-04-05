;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var addClass = BX.Landing.Utils.addClass;
	var bind = BX.Landing.Utils.bind;
	var proxy = BX.Landing.Utils.proxy;
	var decodeDataValue = BX.Landing.Utils.decodeDataValue;


	/**
	 * Implements interface for works with html field
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Field.Html = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-html");

		this.onContentChange = proxy(this.onContentChange, this);
		this.onMousewheel = proxy(this.onMousewheel, this);

		bind(this.input, "input", this.onContentChange);
		bind(this.input, "keydown", this.onContentChange);
		bind(this.input, "mousewheel", this.onMousewheel);

		this.input.value = decodeDataValue(this.content);

		setTimeout(function() {
			this.adjustHeight();
		}.bind(this), 20);
	};


	BX.Landing.UI.Field.Html.prototype = {
		constructor: BX.Landing.UI.Field.Html,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		/**
		 * Creates field input
		 * @return {HTMLElement}
		 */
		createInput: function()
		{
			return BX.create("textarea", {props: {className: "landing-ui-field-input"}, html: this.content});
		},


		onMousewheel: function(event)
		{
			event.stopPropagation();
		},


		/**
		 * Handles paste event
		 */
		onPaste: function()
		{
			// Prevent BX.Landing.UI.Field.BaseField.onPaste
		},


		/**
		 * Handles content change event
		 */
		onContentChange: function()
		{
			this.adjustHeight();
			this.onValueChangeHandler(this);
		},


		/**
		 * Adjusts input height
		 */
		adjustHeight: function()
		{
			this.input.style.height = "0px";
			this.input.style.height = Math.min(this.input.scrollHeight, 180) + "px";
		},


		/**
		 * Gets field value
		 * @return {string}
		 */
		getValue: function()
		{
			return this.input.value;
		}
	};
})();