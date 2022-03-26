;(function () {
	'use strict';

	BX.namespace('BX.Bizproc.WorkflowEditComponent');

	if (typeof BX.Bizproc.WorkflowEditComponent.Globals !== 'undefined')
	{
		return;
	}

	BX.Bizproc.WorkflowEditComponent.Globals = {};

	BX.Bizproc.WorkflowEditComponent.Globals.init = function (params)
	{
		this.documentTypeSigned = String(params.documentTypeSigned);
	};

	BX.Bizproc.WorkflowEditComponent.Globals.onAfterSliderClose = function (slider, target)
	{
		var sliderInfo = slider.getData();
		if (sliderInfo.get('upsert'))
		{
			var newGFields = sliderInfo.get('upsert');
			for (var fieldId in newGFields)
			{
				target[fieldId] = newGFields[fieldId];
			}
		}
		if (sliderInfo.get('delete'))
		{
			var deletedGFields = sliderInfo.get('delete');
			for (var i in deletedGFields)
			{
				delete target[deletedGFields[i]];
			}
		}
	};

	BX.Bizproc.WorkflowEditComponent.Globals.showGlobalVariables = function ()
	{
		var me = this;

		BX.Bizproc.Globals.Manager.Instance.showGlobals(
			BX.Bizproc.Globals.Manager.Instance.mode.variable,
			String(this.documentTypeSigned)
		).then(function (slider) {
			me.onAfterSliderClose(slider, arWorkflowGlobalVariables);
		});
	};

	BX.Bizproc.WorkflowEditComponent.Globals.showGlobalConstants = function ()
	{
		var me = this;

		BX.Bizproc.Globals.Manager.Instance.showGlobals(
			BX.Bizproc.Globals.Manager.Instance.mode.constant,
			String(this.documentTypeSigned)
		).then(function (slider) {
			me.onAfterSliderClose(slider, arWorkflowGlobalConstants);
		});
	};

})();

function BPImportToClipboard()
{
	var dataString = JSON.stringify({
		template: rootActivity.Serialize(),
		parameters: arWorkflowParameters,
		variables: arWorkflowVariables,
		constants: arWorkflowConstants
	});

	BX.clipboard.copy(encodeURIComponent(dataString));
}

function BPExportFromString(rawString)
{
	try
	{
		var data = JSON.parse(decodeURIComponent(rawString));
	}
	catch (e)
	{
		data = {}
	}

	if (data.parameters && BX.type.isPlainObject(data.parameters))
	{
		arWorkflowParameters = data.parameters;
	}
	if (data.variables && BX.type.isPlainObject(data.variables))
	{
		arWorkflowVariables = data.variables;
	}
	if (data.constants && BX.type.isPlainObject(data.constants))
	{
		arWorkflowConstants = data.constants;
	}

	if (data.template && BX.type.isPlainObject(data.template))
	{
		arWorkflowTemplate = data.template;
		ReDraw();
	}
}