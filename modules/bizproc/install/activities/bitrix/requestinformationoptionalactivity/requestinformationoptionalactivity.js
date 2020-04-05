////////////////////////////////////////////////////////////////////////////////////////
// RequestInformationOptionalActivity
////////////////////////////////////////////////////////////////////////////////////////

RequestInformationOptionalActivity = function()
{
	var ob = new ParallelActivity();
	ob.Type = 'RequestInformationOptionalActivity';
	ob.__parallelActivityInitType = 'SequenceActivity';

	ob.DrawParallelActivity = ob.Draw;

	ob.Draw = function (d)
	{
		var act = _crt(1, 4);
		act.style.fontSize = '11px';

		act.rows[0].cells[1].style.background = 'url('+ob.Icon+') 2px 2px no-repeat';
		act.rows[0].cells[1].style.height = '24px';
		act.rows[0].cells[1].style.width = '24px';

		act.rows[0].cells[2].align = 'left';
		act.rows[0].cells[2].innerHTML = HTMLEncode(ob['Properties']['Title']);

		act.rows[0].cells[0].width = '33';
		act.rows[0].cells[0].align = 'left';
		act.rows[0].cells[0].innerHTML = '&nbsp;<span style="color: #007700">'+BPMESS['BPRIOA_OK']+'</span>';
		act.rows[0].cells[3].align = 'right';
		act.rows[0].cells[3].innerHTML = '<span style="color: #770000">'+BPMESS['BPRIOA_CANCEL']+'</span>&nbsp;';

		ob.activityContent = act;
		ob.activityHeight = '30px';
		ob.activityWidth = '200px';
		ob.DrawParallelActivity(d);
	};

	return ob;
};
