;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.Button = function(options)
	{
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
		this.fieldScope = options.fieldScope;
		this.id = this.fieldScope.id;
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.Button.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.Button
	}
})();