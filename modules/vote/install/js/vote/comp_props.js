function IsNumeric(input) 
{
	return !isNaN(parseFloat(input)) && isFinite(input);
}

function ComponentPropsVoteIPDelayUpdate()
{
	if (BX("VOTE_UNIQUE_IP_DELAY_INPUT"))
	{
		var node = BX.findChild(BX("VOTE_UNIQUE_IP_DELAY_INPUT").parentNode, {tagName : "INPUT", attrs : {"data-bx-property-id" : "VOTE_UNIQUE_IP_DELAY"}});
		node.value = BX("VOTE_UNIQUE_IP_DELAY_INPUT").value + " " + BX("VOTE_UNIQUE_IP_DELAY_SELECT").value;
	}
}

function ComponentPropsVoteIPDelay(arParams)
{
	var sValue = arParams.oInput.value;

	var arValue = sValue.split(" ");
	if ( ! IsNumeric(BX.util.trim(arValue[0])))
		arValue[0] = 0;
	
	oInput = arParams.oCont.appendChild(BX.create("INPUT", {props: {id: "VOTE_UNIQUE_IP_DELAY_INPUT", type: 'text', size: 5, value: arValue[0]}}));
	oSelect = arParams.oCont.appendChild(BX.create("SELECT", {props:{id:"VOTE_UNIQUE_IP_DELAY_SELECT"}}));
	oInput.onkeypress = oSelect.onclick = oSelect.onkeyup = (function() {setTimeout(ComponentPropsVoteIPDelayUpdate, 50)});
	var oTypes = {S:"SECONDS", M:"MINUTES", H:"HOURS", D:"DAYS"};
	for (type in oTypes)
		oSelect.appendChild(BX.create("OPTION", {props: {value: type}})).innerHTML = arParams.propertyParams.JS_LANG[oTypes[type]];

	if (oSelected = BX.findChild(oSelect, {property:{value: arValue[1]}}))
		oSelected.setAttribute("selected", "selected");
}
