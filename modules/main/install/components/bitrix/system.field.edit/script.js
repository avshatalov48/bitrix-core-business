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
}

function addElementFile(Name, thisButton)
{
	var parentElement = document.getElementById('main_' + Name);
	var clone = document.getElementById('main_add_' + Name);
	if(parentElement && clone)
	{
		clone = clone.cloneNode(true);
		clone.id = '';
		clone.style.display = '';
		parentElement.appendChild(clone);
	}
}

function addElementDate(elements, index)
{
	var container = document.getElementById('date_container_'+index);
	var text = document.getElementById('hidden_'+index).innerHTML;
	if (container && text)
	{
		var replaceText = elements[index].fieldName;
		var curIndex = elements[index].index;

		text = text.replace(/[#]FIELD_NAME[#]/g, replaceText+'['+curIndex+']');
		text = text.replace(/[\%]23FIELD_NAME[\%]23/g, escape(replaceText+'['+curIndex+']'));
		var div = container.appendChild(document.createElement('DIV'));
		div.innerHTML += text;
		elements[index].index++;
	}
}
