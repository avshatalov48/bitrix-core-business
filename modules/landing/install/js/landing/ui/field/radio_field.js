;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	/**
	 * Implements interface for works with radio field
	 * @extends {BX.Landing.UI.Field.Checkbox}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Field.Radio = function(options)
	{
		BX.Landing.UI.Field.Checkbox.apply(this, arguments);
	};


	BX.Landing.UI.Field.Radio.prototype = {
		constructor: BX.Landing.UI.Field.Radio,
		__proto__: BX.Landing.UI.Field.Checkbox.prototype,
		superclass: BX.Landing.UI.Field.Checkbox.prototype,

		addItem: function(itemOptions)
		{
			var item = this.superclass.addItem.call(this, itemOptions);

			if (item)
			{
				var input = item.querySelector("input");

				if (input)
				{
					input.type = "radio";
					input.name = this.id;
				}
			}
		}
	}

})();