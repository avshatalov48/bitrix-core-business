;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.ComplexHtml = function(options)
	{
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
		this.id = this.fieldScope.id;
		this.init();
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.ComplexHtml.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.ComplexHtml,
		init: function()
		{
			BX.bind(this.fieldScope, 'click', BX.delegate(this.clickHandler, this));
		},
		clickHandler: function (e)
		{
			BX.onCustomEvent(this.fieldScope, this.id + '_onClick', [this]);
		}
	}
})();