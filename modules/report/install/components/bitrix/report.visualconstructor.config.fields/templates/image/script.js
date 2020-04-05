;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.Image = function(options)
	{
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
		this.fieldScope = options.fieldScope;
		this.id = this.fieldScope.id;
		this.init();
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.Image.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.Image,
		init: function()
		{
			BX.bind(this.fieldScope, 'click', BX.delegate(this.changeHandler, this));
		},
		changeHandler: function (e)
		{
			BX.onCustomEvent(this.fieldScope, this.id + '_onClick', [this]);
		}
	}
})();