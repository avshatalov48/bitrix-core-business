;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Form");

	/**
	 * Implements interface for works with card form
	 *
	 * @extends {BX.Landing.UI.Form.BaseForm}
	 * @param {{[title]: ?string, [label]: string, [labelBindings]: string|array, [preset]: object}} data
	 * @constructor
	 */
	BX.Landing.UI.Form.CardForm = function(data)
	{
		BX.Landing.UI.Form.BaseForm.apply(this, arguments);
		this.layout.classList.add("landing-ui-form-card");
		this.labelBindings = data.labelBindings;
		this.preset = data.preset;
		this.oldIndex = this.selector.split("@")[1];
	};


	BX.Landing.UI.Form.CardForm.prototype = {
		constructor: BX.Landing.UI.Form.CardForm,
		__proto__: BX.Landing.UI.Form.BaseForm.prototype,

		/**
		 * Serializes form
		 * @return {object}
		 */
		serialize: function()
		{
			return this.fields
				.reduce(function(res, field) {
					return res[field.selector.split("@")[0]] = field.getValue(), res;
				}, {});
		},

		/**
		 * Gets used card preset
		 * @return {*|null}
		 */
		getPreset: function()
		{
			return this.preset || null;
		}
	};
})();