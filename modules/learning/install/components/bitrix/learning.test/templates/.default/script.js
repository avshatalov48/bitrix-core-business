function checkForEmpty(warnMessage)
{
	var answers = document.getElementsByName('answer[]');
	if(!answers || answers.length==0)
		answers = document.getElementsByName('answer');
	if(!answers || answers.length==0)
		return true;
	var exAnswers = [];
	for(var i=0;i<answers.length;i++)
	{
		if (answers[i].type == "textarea")
		{
			if (answers[i].value.length > 0)
				return true;
		}
		else
		{
			if(answers[i].selected || answers[i].checked)
				return true;
		}
	}
	return confirm(warnMessage);
}

function checkSorting(warnMessage)
{
	var answers = document.getElementsByName('answer[]');
	if(!answers || answers.length==0)
		return true;
	var exAnswers = [];
	for(var i=0;i<answers.length;i++)
	{
		if (answers[i].value == 0)
			return confirm(warnMessage);
		else
		{
			for(var j=0; j<exAnswers.length;j++)
			{
				if (answers[i].value == exAnswers[j])
					return confirm(warnMessage);
			}
			exAnswers.push(answers[i].value);
		}
	}
	return true;
}

function UpdateClock(seconds)
{
	if(clockID)
		clearTimeout(clockID);

	if (!(seconds >= 0))
		return;

	var SecondsToEnd = seconds;

	var strTime = "";
	var hours = Math.floor(seconds/3600);

	if (hours > 0)
	{
		strTime += (hours < 10 ? "0" : "") + hours + ":";
		seconds = seconds - hours*3600;
	}
	else
	{
		strTime += "00:";
	}

	var minutes = Math.floor(seconds/60);

	if (minutes > 0)
	{
		strTime += (minutes < 10 ? "0" : "") + minutes + ":";
		seconds = seconds - minutes*60;
	}
	else
	{
		strTime += "00:";
	}
	
	var sec = (seconds%60);
	strTime += (sec < 10 ? "0" : "") + sec;

	//alert(strTime);

	document.getElementById("learn-test-timer").innerHTML = strTime;

	clockID = setTimeout("UpdateClock("+(SecondsToEnd-1)+")", 950);
}