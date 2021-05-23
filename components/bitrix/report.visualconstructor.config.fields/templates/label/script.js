;(function(){
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget.Config.Fields');

	/**
	 * @param options
	 * @extends BX.Report.VisualConstructor.Field.Base
	 * @constructor
	 */
	BX.Report.VisualConstructor.Widget.Config.Fields.Label = function(options)
	{
		this.fieldScope = options.fieldScope;
		this.id = this.fieldScope.id;
		this.pencil = options.pencil;
		this.init();
		BX.Report.VisualConstructor.Field.Base.apply(this, arguments);
	};

	BX.Report.VisualConstructor.Widget.Config.Fields.Label.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.Base.prototype,
		constructor: BX.Report.VisualConstructor.Widget.Config.Fields.Label,
		init: function ()
		{
			BX.bind(this.fieldScope, 'change', BX.delegate(this.handlerInputChange, this));
			BX.bind(this.fieldScope, 'click', BX.delegate(this.handlerInputClick, this));
			BX.bind(this.fieldScope, 'blur', BX.delegate(this.handlerInputBlur, this));
			BX.bind(this.pencil, 'click', BX.delegate(this.handlerPencilClick, this));
			this.adjustFieldWidth();
		},
		adjustFieldWidth: function()
		{
			this.fieldScope.style.width = ((this.fieldScope.value.length + 1) * 7.5) + 'px';
			this.fieldScope.style.maxWidth = '80%';
		},
		handlerInputChange: function ()
		{
			BX.onCustomEvent(this.fieldScope, this.id + '_onChange', [this]);
		},
		handlerInputClick: function ()
		{
			this.fieldScope.classList.remove('report-configuration-field-input-inactive');
			this.fieldScope.style.width = '80%';
		},
		handlerPencilClick: function()
		{
			this.handlerInputClick();
			this.fieldScope.focus();
		},
		handlerInputBlur: function ()
		{
			this.fieldScope.classList.add('report-configuration-field-input-inactive');
			this.adjustFieldWidth()
		},
		setValue: function(value)
		{
			this.fieldScope.value = value;
			this.adjustFieldWidth();
			this.handlerInputChange();
		}
	}
})();