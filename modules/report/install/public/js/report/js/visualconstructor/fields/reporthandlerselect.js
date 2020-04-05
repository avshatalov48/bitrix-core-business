;(function ()
{
	"use strict";

	BX.namespace('BX.Report.VisualConstructor.FieldEventHandlers');

	/**
	 * @param options
	 * @extends {BX.Report.VisualConstructor.Field.BaseHandler}
	 * @constructor
	 */
	BX.Report.VisualConstructor.FieldEventHandlers.ReportHandlerSelect = function (options)
	{
		BX.Report.VisualConstructor.Field.BaseHandler.apply(this, arguments);
	};

	BX.Report.VisualConstructor.FieldEventHandlers.ReportHandlerSelect.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.BaseHandler.prototype,
		constructor: BX.Report.VisualConstructor.FieldEventHandlers.ReportHandlerSelect,
		process: function ()
		{
			switch (this.action)
			{
				case 'categorySelected':
					this.reloadReportHandlerList();
					break;
				case 'reportHandlerSelected':
					this.reportHandlerSelected();
					break;
				case 'removeReportFromConfiguration':
					this.removeReportFromConfiguration();
					break;
			}
		},
		reportHandlerSelected: function ()
		{
			var form = this.currentFieldObject.getForm();
			var reportsConfigurationContainer = form.querySelector('[data-role=reports-configurations-container]');

			var reportConfigurationsContainer = BX.findParent(this.currentFieldObject.fieldScope, {
				attr: {'data-role': 'report-configuration-container'}
			});

			var presetReportHandlerClassName = reportConfigurationsContainer.getAttribute('data-source-report-handler');
			var widgetId = reportsConfigurationContainer.getAttribute('data-widget-id');
			var reportId = reportConfigurationsContainer.getAttribute('data-report-id');
			var viewTypeField = form.querySelector('[data-role=preview-view-type-key]');
			var isCurrentReportPseudo = reportConfigurationsContainer.getAttribute('data-is-pseudo');
			var reportHandlerSelectCurrentValue = this.currentFieldObject.getValue();

			if (presetReportHandlerClassName !== reportHandlerSelectCurrentValue)
			{
				var colorPickerValue = this.getReportConfigurationColorPickerValue(reportConfigurationsContainer);
				BX.Report.VC.Core.ajaxPost('configuration.buildPseudoReportConfiguration', {
					data: {
						params: {
							widgetId: widgetId,
							viewKey: viewTypeField.value,
							reportHandlerClassName: reportHandlerSelectCurrentValue,
							colorFieldValue: colorPickerValue
						}
					},
					onFullSuccess: function (result)
					{
						var newReportConfigFields = BX.create('div', {
							html: result.data
						});
						if (isCurrentReportPseudo === '1')
						{
							newReportConfigFields.firstChild.setAttribute('data-source-report-handler', reportConfigurationsContainer.getAttribute('data-source-report-handler'));
							newReportConfigFields.firstChild.setAttribute('data-source-report-id', reportConfigurationsContainer.getAttribute('data-source-report-id'));
							newReportConfigFields.firstChild.classList.add('report-configuration-container-visible');
							reportConfigurationsContainer.parentNode.insertBefore(newReportConfigFields.firstChild, reportConfigurationsContainer);
							BX.remove(reportConfigurationsContainer);
						}
						else
						{
							//newReportConfigFields.firstChild.setAttribute('data-source-report-handler', this.handlerCurrentValue);
							newReportConfigFields.firstChild.setAttribute('data-source-report-id', reportId);
							newReportConfigFields.firstChild.classList.add('report-configuration-container-visible');
							reportConfigurationsContainer.parentNode.insertBefore(newReportConfigFields.firstChild, reportConfigurationsContainer);

							var removeReportInput = BX.create('input', {
								props: {
									name: 'deletedReports[]',
									type: 'hidden',
									value: reportId
								},
								attrs: {
									'data-report-id': reportId
								}
							});

							form.appendChild(removeReportInput);
							reportConfigurationsContainer.style.display = "none";
							reportConfigurationsContainer.classList.remove('report-configuration-container-visible');
						}
						BX.html(null, result.data);
					}.bind(this)
				});

			}
		},
		getReportConfigurationColorPickerValue: function (reportConfigurationContainer)
		{
			var colorPickerInputField = reportConfigurationContainer.querySelector('[data-role=visualconstructor-fields-picker] > input');
			if (colorPickerInputField)
			{
				return colorPickerInputField.value;
			}
			else
			{
				return '#ffffff'
			}
		},
		reloadReportHandlerList: function ()
		{
			/**
			 * Remove all options except default options
			 */
			BX.Report.VC.Core.ajaxGet('report.getReportHandlersByCategory', {
				urlParams: {
					categoryKey: this.ownerFieldObject.getValue()
				},
				onFullSuccess: function (result)
				{
					this.currentFieldObject.setOptions(result.data);
				}.bind(this)
			});
		},
		removeReportFromConfiguration: function ()
		{
			var reportContainer = BX.findParent(this.currentField, {
				className: 'report-configuration-container'
			});
			var reportId = reportContainer.getAttribute('data-report-id');
			var removeReportInput = BX.create('input', {
				props: {
					name: 'deletedReports[]',
					type: 'hidden',
					value: reportId
				}
			});
			BX.remove(reportContainer);
			/*if (this.getConfiguredReportCount() < this.getMaxReportRenderCount())
			{
				this.showAddReportButton();
			}*/
			this.currentFieldObject.getForm().appendChild(removeReportInput);
		}

	}
})();
