;(function() {
	"use strict";

	BX.namespace("BX.Landing.Block.Node");


	/**
	 * Implements interface for works with component node
	 * @extends {BX.Landing.Block.Node}
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.Block.Node.Component = function(options)
	{
		BX.Landing.Block.Node.apply(this, arguments);
		this.type = "component";
	};


	BX.Landing.Block.Node.Component.prototype = {
		constructor: BX.Landing.Block.Node.Component,
		__proto__: BX.Landing.Block.Node.prototype,


		/**
		 * @inheritDoc
		 * @return {BX.Landing.UI.Field.BaseField}
		 */
		getField: function()
		{
			return new BX.Landing.UI.Field.BaseField({
				selector: this.selector
			});
		},


		/**
		 * Gets value
		 * @return {string}
		 */
		getValue: function()
		{
			return "";
		},


		/**
		 * Sets value
		 * @inheritDoc
		 */
		setValue: function(value, preventSave, preventHistory)
		{

		}
	};
})();
