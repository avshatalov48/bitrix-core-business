var jsBXMI = {

	Init: function (arParams)
	{
		this.STEP = arParams.STEP;	
		this.STEPS = arParams.STEPS;
		this.ID = arParams.ID;
		this.TEXT = arParams.TEXT;
	}

}

function BXWdStepBnr(obText, obPrev, obNext, nav)
{
	nav = (nav == 'prev' ? 'prev' : 'next');
	if (nav == 'next' && jsBXMI.STEP < jsBXMI.STEPS) 
		jsBXMI.STEP++;
	else if (nav == 'prev' && jsBXMI.STEP > 1) 
		jsBXMI.STEP--;
	else
		nav = 'current';
	var iStep = jsBXMI.STEP;
	if (nav == 'next')
	{
		obPrev.style.display = '';
		obNext.style.display = (iStep < jsBXMI.STEPS ? '' : 'none');
	}
	else
	{
		obPrev.style.display = (iStep > 1 ? '' : 'none');
		obNext.style.display = '';
	}
	obText.innerHTML = jsBXMI.TEXT[iStep-1];
	if (null != jsUserOptions)
	{
		if(!jsUserOptions.options)
			jsUserOptions.options = new Object();
		jsUserOptions.options['main.informer_'+jsBXMI.ID+'.step'] = ['main', 'informer_'+jsBXMI.ID, 'step', iStep, false];
		jsUserOptions.SendData(null);
	}
}

function BXWdCloseBnr(obBnr)
{
	if (null != obBnr)
		obBnr.parentNode.removeChild(obBnr);
	if (null != jsUserOptions)
	{
		if(!jsUserOptions.options)
			jsUserOptions.options = new Object();
		jsUserOptions.options['main.informer_'+jsBXMI.ID+'.show'] = ['main', 'informer_'+jsBXMI.ID, 'show', false, false];
		jsUserOptions.SendData(null);
	}
}

