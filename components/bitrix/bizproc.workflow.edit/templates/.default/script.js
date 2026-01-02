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

	BX.Bizproc.WorkflowEditComponent.Globals.showDrafts = function(signedDocumentType)
	{
		const url = BX.Uri.addParam('/bitrix/tools/bizproc/show_dratfs_tmp.php', { signedDocumentType });

		BX.SidePanel.Instance.open(url, {
			width: 900,
			cacheable: false,
			allowChangeHistory: false,
		});
	};

	BX.Bizproc.WorkflowEditComponent.Globals.loadDraft = function(draftId)
	{
		return new Promise((resolve, reject) => {
			BX.ajax.runAction('bizprocdesigner.Template.loadDraft', {
				data: { draftId },
			}).then((response) => {
				if (!response.data || !response.data.TEMPLATE_DATA)
				{
					return;
				}

				const { TEMPLATE, NAME, DESCRIPTION, AUTO_EXECUTE, SORT, IS_SYSTEM } = response.data.TEMPLATE_DATA;

				if (!TEMPLATE || TEMPLATE.length === 0)
				{
					return;
				}

				arWorkflowTemplate = TEMPLATE[0];
				workflowTemplateName = NAME || '';
				workflowTemplateDescription = DESCRIPTION || '';
				workflowTemplateAutostart = AUTO_EXECUTE || 1;
				workflowTemplateSort = SORT || 10;
				workflowTemplateIsSystem = IS_SYSTEM ?? 'N';

				BPTemplateIsModified = true;

				ReDraw();

				resolve(response.data);
			}).catch((error) => {
				alert(error.errors?.[0]?.message);
				reject(error);
			});
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