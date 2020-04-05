function addElement(Name, thisButton)
{
	if (document.getElementById('main_' + Name))
	{
		var element = document.getElementById('main_' + Name).getElementsByTagName('div');
		if (element && element.length > 0 && element[0])
		{
			var parentElement = element[0].parentNode; // parent
			parentElement.appendChild(element[element.length-1].cloneNode(true));
		}
	}
	return;
}