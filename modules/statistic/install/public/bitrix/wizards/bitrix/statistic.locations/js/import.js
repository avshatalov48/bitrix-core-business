function Import(STEP, arTmpData)
{
	if (STEP == null) STEP = 1;
	if (typeof(STEP) == 'object') STEP = 1;

	function __refreshLog(data)
	{
		var obContainer = document.getElementById('output');

		if (obContainer)
		{
			obContainer.innerHTML += data;
		}

		BX.hide(BX('wait_message'));
	}

	BX('wait_message').innerHTML = BX.message('JS_CORE_LOADING');
	BX.show(BX('wait_message'));

	if (STEP <= 2)
	{
		if (arTmpData != null)
		{
			var percent = Math.round((arTmpData.POS/arTmpData.AMOUNT) * 100);
			jsPB.Update(percent);

			var obWaitMessage = document.getElementById('wait_message');
			obWaitMessage.appendChild(document.createTextNode(' '+ percent + '%(' + STEP + '/2)'));
		}
		else
		{
			jsPB.Init('progress');
			var obWaitMessage = document.getElementById('wait_message');
			obWaitMessage.appendChild(document.createTextNode(' 0% (' + STEP + '/2)'));
		}
	}
	else if (STEP == 3)
	{
		jsPB.Update(100);
	}

	BX.ajax.post(
		path + '/scripts/import.php',
		{
			import_type:import_type,
			file_name:file_name,
			STEP:STEP,
			file_position:arTmpData? arTmpData.file_position: 0,
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

var jsPB = {

	bInit:false,
	curValue:0,
	width:0,

	Init: function(cont_id)
	{
		if (this.bInit)
		{
			this.Update(0);
			return;
		}

		var obContainer = document.getElementById(cont_id);
		if (!obContainer) return false;

		this.width = obContainer.offsetWidth;

		var obPb = document.createElement('DIV');
		obPb.id = 'pb';
		obPb.style.height = obContainer.offsetHeight;
		obPb.style.backgroundColor = '#E0E0E0';
		obPb.style.width = this.width + 'px';

		var obIndicator = document.createElement('DIV');
		obIndicator.id = 'pb_indicator';

		obIndicator.style.height = obContainer.offsetHeight;
		obIndicator.style.backgroundColor = 'blue';
		obIndicator.style.width = '0px';

		obPb.appendChild(obIndicator);
		obContainer.appendChild(obPb);

		this.bInit = true;
	},

	Update: function(percent)
	{
		this.curValue = percent;

		var obIndicator = document.getElementById('pb_indicator');
		obIndicator.style.width = Math.round(this.width * percent / 100);
	},

	Remove: function(bRemoveParent)
	{
		if (bRemoveParent == null) bRemoveParent = false;
		var obPb = document.getElementById('pb');

		if (obPb)
		{
			if (!bRemoveParent)
				obPb.parentNode.removeChild(obPb);
			else
				obPb.parentNode.parentNode.removeChild(obPb.parentNode);
		}
	}
};