(function ()
{
	"use strict";
	BX.namespace('BX.Report.VisualConstructor.Widget');
	BX.Report.VisualConstructor.Widget.Form = function(scope, options)
	{
		this.widgetConfigForm = scope;
		this.widgetConigurationsPage = BX.findParent(this.widgetConfigForm, {
			attrs: {'data-role': 'report-configuration-page-wrapper'}
		});
		this.widgetConfigFormFooterContainer = this.widgetConfigForm.querySelector('[data-role="footer-container"]');
		this.reportAddButton = this.widgetConfigForm.querySelector('.add-report-to-widget-button');
		this.saveButton = this.widgetConfigForm.querySelector('[data-type=save-button]');
		this.cancelButton = this.widgetConfigForm.querySelector('[data-type=cancel-button]');
		this.maxRenderReportCountField = this.widgetConfigForm.querySelector('input[data-hidden-field="maxRenderReportCount"]');
		this.widgetId = options.widgetId;
		this.mode = options.mode;
		this.init();
	};

	BX.Report.VisualConstructor.Widget.Form.prototype = {
		init: function()
		{
			BX.bind(this.reportAddButton, 'click', BX.delegate(this.addReportHandler, this));
			BX.bind(this.saveButton, 'click', BX.delegate(this.submitConfigurationForm, this));
			BX.bind(this.cancelButton, 'click', BX.delegate(this.cancelConfigurationForm, this));
			BX.bind(this.widgetConigurationsPage, 'scroll', BX.delegate(this.adjustPositions, this));
			BX.addCustomEvent(this.widgetConfigForm, 'BX.Report:onPseudoConfigRemove', BX.delegate(this.onPseudoConfigRemove, this));
		},
		submitConfigurationForm: function (e)
		{
			e.preventDefault();
			if (this.mode === 'update')
			{
				BX.onCustomEvent('BX.Report.VisualConstructor.Widget.Form:beforeSave', [{
					widgetId: this.widgetId
				}]);
			}

			BX.Report.VC.Core.ajaxSubmit(this.widgetConfigForm, {
					onsuccess: BX.delegate(function (response)
					{
						if (this.mode === 'update')
						{
							BX.onCustomEvent('BX.Report.VisualConstructor.Widget.Form:afterSave', [{
								widgetId: response.data.widgetId,
								mode: this.mode
							}]);
						}
						else
						{
							BX.onCustomEvent("BX.Report.VisualConstructor.afterWidgetAdd", [response.data]);
						}
					}, this)
				}
			);
		},
		cancelConfigurationForm: function(e)
		{
			e.preventDefault();
			BX.onCustomEvent('BX.Report.VisualConstructor.Widget.Form:cancel', [{
				widgetId: this.widgetId,
				mode: this.mode
			}]);
		},
		getReportsConfigurationsContainer: function()
		{
			return this.widgetConfigForm.querySelector('.reports-configurations-container');
		},
		addReportHandler: function (e)
		{
			e.preventDefault();
			this.deactivateAddReportButton();

			BX.Report.VC.Core.ajaxPost('configuration.buildPseudoReportConfiguration', {
				data: {
					params: {
						widgetId: this.widgetId,
						viewKey: this.getSelectedViewValue(),
						existReportCount: this.getConfiguredReportCount()
					}
				},
				onFullSuccess: BX.delegate(function (result)
				{
					var newReportConfigFields = BX.create('div', {
						html: result.data
					});

					for (var i = 0; i < newReportConfigFields.childNodes.length; i++)
					{
						this.getReportsConfigurationsContainer().appendChild(newReportConfigFields.childNodes[i]);
					}

					BX.html(null, result.data);
					if (this.getConfiguredReportCount() - this.getMaxReportRenderCount() === 0)
					{
						this.hideAddReportButton();
					}
					this.activateAddReportButton();
				}, this)
			});
		},
		getSelectedViewValue: function()
		{
			return this.widgetConfigForm.querySelector('[data-role=preview-view-type-key]').value;
		},
		onPseudoConfigRemove: function()
		{
			if (this.getConfiguredReportCount() < this.getMaxReportRenderCount())
			{
				this.showAddReportButton();
			}
		},
		showAddReportButton: function()
		{
			this.reportAddButton.classList.remove('add-report-to-widget-button-invisible');
		},
		hideAddReportButton: function()
		{
			this.reportAddButton.classList.add('add-report-to-widget-button-invisible');
		},
		activateAddReportButton: function()
		{
			this.reportAddButton.disabled = false;
			this.reportAddButton.classList.remove('add-report-to-widget-button-inactive');
		},
		deactivateAddReportButton: function ()
		{
			this.reportAddButton.disabled = true;
			this.reportAddButton.classList.add('add-report-to-widget-button-inactive');
		},
		getConfiguredReportCount: function()
		{
			return this.widgetConfigForm.querySelectorAll('.report-configuration-container').length;
		},
		getMaxReportRenderCount: function()
		{
			return this.maxRenderReportCountField.value;
		},
		adjustPositions: function()
		{
			this.adjustFooterPosition();
			BX.Report.VC.PopupWindowManager.adjustPopupsPositions();
		},
		adjustFooterPosition: function()
		{
			this.widgetConfigFormFooterContainer.style.bottom = -this.widgetConigurationsPage.scrollTop + 'px';
		}
	}
})();