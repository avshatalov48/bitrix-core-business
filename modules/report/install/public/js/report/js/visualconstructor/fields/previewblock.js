;(function(){
	"use strict";

	BX.namespace('BX.Report.VisualConstructor.FieldEventHandlers');

	/**
	 * @param options
	 * @extends {BX.Report.VisualConstructor.Field.BaseHandler}
	 * @constructor
	 */
	BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock = function(options)
	{
		BX.Report.VisualConstructor.Field.BaseHandler.apply(this, arguments);
	};

	BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock.prototype = {
		__proto__: BX.Report.VisualConstructor.Field.BaseHandler.prototype,
		constructor: BX.Report.VisualConstructor.FieldEventHandlers.PreviewBlock,
		process: function()
		{
			switch (this.action)
			{
				case 'viewTypeSelect':

					if (this.optionsFromEvent.mode === 'reloadConfigurations')
					{
						this.reloadForm();
					}
					else if(this.optionsFromEvent.mode === 'reloadWidgetPreview')
					{
						this.reloadWidgetPreview();
					}
					break;
				case 'whatWillCalculateChange':
					this.whatWillCalculateFieldChangeHandler();
					break;
				case 'groupByChange':
					this.groupByFieldChange();
					break;
				case 'reloadWidgetPreview':
					this.reloadWidgetPreview();
					break;
			}
		},
		whatWillCalculateFieldChangeHandler: function()
		{

		},
		groupByFieldChange: function ()
		{
		},
		reloadWidgetPreview: function()
		{
			this.rebuildPreviewWidgetData();
		},
		reloadForm: function()
		{
			var params = BX.ajax.prepareForm(this.currentFieldObject.getForm()).data;
			params.viewType = this.currentFieldObject.value;

			BX.Report.VC.Core.ajaxPost('widget.buildForm', {
				data: {
					params: params
				},
				onFullSuccess: function (result)
				{
					var formContentParentNode = BX.findParent(this.currentFieldObject.getForm(), {className:'report-widget-configuration'}).parentNode;
					BX.cleanNode(formContentParentNode);
					BX.html(formContentParentNode, result.data);
				}.bind(this)
			});
		},
		rebuildPreviewWidgetData: function()
		{
			this.currentFieldObject.showLoader();
			var params = BX.ajax.prepareForm(this.currentFieldObject.getForm()).data;
			params.viewType = this.currentFieldObject.value;
			BX.Report.VC.Core.ajaxPost('widget.constructPseudoWidget', {
				data: {
					params: params
				},
				onFullSuccess: function(response)
				{
					this.currentFieldObject.handlerChangePseudoWidget(params.viewType, response);
				}.bind(this)
			});
		}

	}
})();
