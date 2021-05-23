;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.DropDownResponsible = function(options)
	{
		BX.Report.VisualConstructor.Widget.ConfigField.Base.apply(this, arguments);
		this.fieldScope = options.fieldScope;
		this.id = this.fieldScope.id;
		this.fullOptions = options.fullOptions;

		this.linesOperators = options.linesOperators;
		this.idOpenLinesOptions = options.idOpenLinesOptions;
		this.init();
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.DropDownResponsible.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.DropDownResponsible,
		init: function()
		{
			delete this.fullOptions.__;
			for (var l in this.linesOperators)
			{
				delete this.linesOperators[l].__;
			}

			this.handlerCurrentValue = this.fieldScope.options[this.fieldScope.selectedIndex].value;
			BX.bind(this.fieldScope, 'change', BX.delegate(this.handlerSelectChangeHandler, this));
			BX.addCustomEvent(BX(this.idOpenLinesOptions), this.idOpenLinesOptions + '_onChange', BX.delegate(function (params) {
				var value = params.fieldScope.options[params.fieldScope.options.selectedIndex].value;

				if(
					!isNaN(value) &&
					this.linesOperators[value]
				)
				{
					this.setOptions(this.linesOperators[value]);
				}
				else
				{
					this.setOptions(this.fullOptions);
				}
			}, this));
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

			var selectedIndex = this.fieldScope.options.selectedIndex;
			var currentOptions = this.fieldScope.options;
			var selectedValue = '__';

			for (var it = currentOptions.length; it > 1; it--)
			{
				var index = it-1;

				if(index === selectedIndex)
				{
					selectedValue = currentOptions[index].value;
				}

				currentOptions[index] = null;
			}

			/**
			 * Fill select options from received data
			 */
			for(var i=0; i < options.length; i++) {
				var key = this.fieldScope.options.length;
				var option = document.createElement('option');
				option.text = BX.util.htmlspecialcharsback(options[i][1]);
				option.value = options[i][0];
				this.fieldScope.options[key] = option;
				if(selectedValue === option.value)
				{
					this.fieldScope.options.selectedIndex = key;
				}
			}
		}
	}
})();