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