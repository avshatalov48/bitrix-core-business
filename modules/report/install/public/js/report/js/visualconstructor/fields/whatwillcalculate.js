;(function(){
	"use strict";

	BX.namespace('BX.Report.VisualConstructor.FieldEventHandlers');

	/**
	 * @param options
	 * @extends {BX.Report.VisualConstructor.Field.BaseHandler}
	 * @constructor
	 */
	BX.Report.VisualConstructor.FieldEventHandlers.WhatWillCalculate =  function(options)
	{
		BX.Report.VisualConstructor.Field.BaseHandler.apply(this, arguments);

		if (this.currentField)
		{
			this.reportsConfigurrationContainer = BX.findParent(this.currentField, {
				attr: {'data-role': 'reports-configurations-container'}
			});
			this.reportConfigurationsContainer = BX.findParent(this.currentField, {
				attr: {'data-role': 'report-configuration-container'}
			});
			this.widgetIdInput = this.currentFieldObject.getForm().querySelector('#widgetId');
		}

	};

	BX.Report.VisualConstructor.FieldEventHandlers.WhatWillCalculate.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.BaseHandler.prototype,
		constructor: BX.Report.VisualConstructor.FieldEventHandlers.WhatWillCalculate,
		process: function()
		{
			switch (this.action)
			{
				case 'reloadCompatibleCalculatedTypes':
					this.reloadCompatibleCalculatedTypes();
					break;
			}
		},
		reloadCompatibleCalculatedTypes: function()
		{
			var viewTypeField = this.currentFieldObject.getForm().querySelector('[data-role=preview-view-type-key]');
			BX.Report.VC.Core.ajaxPost('configuration.loadWhatWillCalculateByGroup', {
				data: {
					params: {
						widgetId: this.widgetIdInput.value,
						viewKey: viewTypeField.value,
						groupBy:  this.ownerFieldObject.getValue(),
						reportHandlerClassName: this.reportConfigurationsContainer.querySelector('[data-field-type="report-handler-class"]').value
					}

				},
				onFullSuccess: function(result) {
					this.currentFieldObject.setOptions(result.data);
				}.bind(this)
			});
		}
	}
})();
