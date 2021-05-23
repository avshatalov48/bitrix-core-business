function addElementVideo(Name, thisButton, tmpName)
{
	var
		div,
		o = thisButton,
		className = "bx-tmp-field-div"
		pInpCount = jsUtils.FindNextSibling(thisButton, "INPUT"),
		curCount = parseInt(pInpCount.value),
		NewName = Name.replace("[]", "[" + (curCount + 1)+ "]"),
		Name = tmpName,
		id = Name.replace(/\[|\]/ig, "_"),
		newId = NewName.replace(/\[|\]/ig, "_");

	while(o.previousSibling)
	{
		var sibling = o.previousSibling;
		if(sibling.tagName && sibling.tagName.toUpperCase() == "DIV" && sibling.className == className)
		{
			div = sibling;
			break;
		}
		o = sibling;
	}

	if (!div)
		return;

	pInpCount.value = curCount + 1;

	var html = div.innerHTML;
	// Cut style
	html = html.replace(/<style>[\s\S]*?<\/style>/ig, "");

	// Replace id
	html = html.replace(new RegExp(id, 'ig'), newId);

	// Replace Name
	var Name_ = Name.replace(/\[/ig, "\\[");
	Name_ = Name_.replace(/\]/ig, "\\]");
	html = html.replace(new RegExp(Name_, 'ig'), NewName);

	var code = [], start, end, i, cnt;
	while((start = html.indexOf('<' + 'script>')) != -1)
	{
		var end = html.indexOf('</' + 'script>', start);
		if(end == -1)
			break;
		code[code.length] = html.substr(start + 8, end - start - 8);
		html = html.substr(0, start) + html.substr(end + 9);
	}

	for(var i = 0, cnt = code.length; i < cnt; i++)
		if(code[i] != '')
			jsUtils.EvalGlobal(code[i]);

	var newDiv = jsUtils.CreateElement("DIV", {}, {padding: "5px", border: "0px solid red"});
	newDiv.innerHTML = html;

	div.parentNode.insertBefore(newDiv, thisButton);
}
