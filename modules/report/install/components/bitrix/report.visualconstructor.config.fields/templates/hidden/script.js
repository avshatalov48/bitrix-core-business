;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.Hidden = function(options)
	{
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
		this.inputField = options.fieldScope;
		this.id = this.inputField.id;
		this.init();
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.Hidden.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.Hidden,
		init: function()
		{
			BX.bind(this.inputField, 'change', BX.delegate(this.changeHandler, this));
		},
		changeHandler: function (e)
		{
			BX.onCustomEvent(this.fieldScope, this.id + '_onChange', [this]);
		}
	}
})();