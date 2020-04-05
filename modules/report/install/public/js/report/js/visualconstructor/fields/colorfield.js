;(function()
{
	"use strict";

	BX.namespace('BX.Report.VisualConstructor.FieldEventHandlers');

	/**
	 * @param options
	 * @extends {BX.Report.VisualConstructor.Field.BaseHandler}
	 * @constructor
	 */
	BX.Report.VisualConstructor.FieldEventHandlers.ColorField = function (options)
	{
		BX.Report.VisualConstructor.Field.BaseHandler.apply(this, arguments);
	};

	BX.Report.VisualConstructor.FieldEventHandlers.ColorField.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.BaseHandler.prototype,
		constructor: BX.Report.VisualConstructor.FieldEventHandlers.ColorField,
		process: function ()
		{
			switch (this.action)
			{
				case 'selectColorInConfigurationForm':
					this.selectColorInConfigurationForm();
					break;
			}
		},
		selectColorInConfigurationForm: function ()
		{
			//TODO change to data attributes
			var configurationContainer = BX.findParent(this.currentField, {
				className: 'report-configuration-container'
			});
			var configurationHeadContainer = configurationContainer.querySelector('.report-configuration-head');
			var configurationMainContainer = configurationContainer.querySelector('.report-configuration-main');
			configurationHeadContainer.style.backgroundColor = this.currentFieldObject.getValue();
			configurationMainContainer.style.backgroundColor = this.currentFieldObject.getValue() + '5f';
		}
	}
})();