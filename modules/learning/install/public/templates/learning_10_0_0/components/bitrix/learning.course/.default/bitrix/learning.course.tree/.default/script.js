function JCMenu(sOpenedSections, COURSE_ID)
{
	this.oSections = {};
	this.COURSE_ID = COURSE_ID;

	var aSect = sOpenedSections.split(',');
	for(var i in aSect)
		this.oSections[aSect[i]] = true;

	this.OpenChapter = function(oThis, id)
	{
		var li = oThis.parentNode.parentNode.parentNode;
		
		if (!BX.hasClass(li, "tree-item-closed"))
		{
			this.oSections[id] = false;
			BX.addClass(li, "tree-item-closed");
		}
		else
		{
			this.oSections[id] = true;
			BX.removeClass(li, "tree-item-closed");
		}

		var sect='';
		for(var i in this.oSections)
		if(this.oSections[i] == true)
			sect += (sect != ''? ',':'')+i;
		document.cookie = "LEARN_MENU_"+this.COURSE_ID+"=" + sect + "; expires=Thu, 31 Dec 2020 23:59:59 GMT; path=/;";

		return false;
	}

}