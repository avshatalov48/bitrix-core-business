function JCMenu(sOpenedSections, COURSE_ID)
{
	this.oSections = {};
	this.COURSE_ID = COURSE_ID;

	var aSect = sOpenedSections.split(',');
	for(var i in aSect)
		this.oSections[aSect[i]] = true;

	this.OpenChapter = function(oThis, id)
	{
		if (oThis.parentNode.className == '')
		{
			this.oSections[id] = false;
			oThis.parentNode.className = 'close';
		}
		else
		{
			this.oSections[id] = true;
			oThis.parentNode.className = '';
		}

		var sect='';
		for(var i in this.oSections)
		if(this.oSections[i] == true)
			sect += (sect != ''? ',':'')+i;
		document.cookie = "LEARN_MENU_"+this.COURSE_ID+"=" + sect + "; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=/;";

		return false;
	}

}