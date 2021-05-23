;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.DropDown = function(options)
	{
		BX.Report.VisualConstructor.Widget.ConfigField.Base.apply(this, arguments);
		this.fieldScope = options.fieldScope;
		this.id = this.fieldScope.id;
		this.init();
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.DropDown.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.DropDown,
		init: function()
		{
			this.handlerCurrentValue = this.fieldScope.options[this.fieldScope.selectedIndex].value;
			BX.bind(this.fieldScope, 'change', BX.delegate(this.handlerSelectChangeHandler, this));
		},
		handlerSelectChangeHandler: function (e)
		{
			BX.onCustomEvent(this.fieldScope, this.id + '_onChange', [this]);
		},
		setOptions: function(options)
		{
			if (typeof options === 'object')
			{
				var result = [];
				for (var j in options)
				{
					result.push([j, options[j]]);
				}
				options = result;
			}

			var currentOptions = this.fieldScope.options;
			for (var it = currentOptions.length; it > 1; it--)
			{
				currentOptions[it-1] = null;
			}
			/**
			 * Fill select options from received data
			 */
			for(var i=0; i < options.length; i++) {
				var option = document.createElement('option');
				option.text = options[i][1];
				option.value = options[i][0];
				this.fieldScope.options[this.fieldScope.options.length] = option;
			}
		}
	}
})();