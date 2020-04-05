function DropTables()
{
	function __refreshLog(data)
	{
		var obContainer = document.getElementById('output');
		if (obContainer)
			obContainer.innerHTML = data;
	}

	BX.ajax.post(
		path + '/scripts/drop.php',
		{
			sessid:sessid,
			to_node_id:to_node_id,
			module:module,
			lang:LANG
		},
		__refreshLog
	);
}

function MoveTables(STEP)
{
	if (STEP == null) STEP = 1;
	if (typeof(STEP) == 'object') STEP = 1;

	function __refreshLog(data)
	{
		var obContainer = document.getElementById('output');
		if (obContainer)
			obContainer.innerHTML = data;
	}

	BX.ajax.post(
		path + '/scripts/move.php',
		{
			sessid:sessid,
			from_node_id:from_node_id,
			to_node_id:to_node_id,
			module:module,
			status:status,
			STEP:STEP,
			lang:LANG
		},
		__refreshLog
	);
}

function RunError()
{
	var obErrorMessage = document.getElementById('error_message');
	if (obErrorMessage) obErrorMessage.style.display = 'inline';
}

function RunAgain()
{
	var obOut = document.getElementById('output');
	var obErrorMessage = document.getElementById('error_message');

	obOut.innerHTML = '';
	obErrorMessage.style.display = 'none';
	Run(1);
}

function DisableButton(e)
{
	var obNextButton = document.forms[formID][nextButtonID];
	obNextButton.disabled = true;
}

function EnableButton()
{
	var obNextButton = document.forms[formID][nextButtonID];
	obNextButton.disabled = false;
}
