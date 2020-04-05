function addNewRow(tableID, regexp)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt-1);
	var oCell = oRow.insertCell(0);
	var html = tbl.rows[cnt-2].cells[0].innerHTML;
	var reName = /\[(\d+)\]/gi;
	var reNameMatch = html.match(reName);
	var foundName = reNameMatch[reNameMatch.length - 1].replace(/\[|\]/g, "");
	var regExpName = new RegExp('(' + regexp + ')\\[(\\d*)\\]', 'gi');
	var regExpId = new RegExp('(' + regexp + ')_(\\d*)_', 'gi');

	//change names[\d]
	oCell.innerHTML =  html.replace(regExpName, '$1[' + (parseInt(foundName)+1) + ']');
	//change ID _\d_
	oCell.innerHTML = oCell.innerHTML.replace(regExpId, '$1_' + (parseInt(foundName)+1) + '_');

	var file = BX.findChild(oCell, { "tag" : "span", "class" : "adm-input-file"}, true);
	if(file)
	{
		file.firstChild.innerHTML = BX.message('JSADM_FILE');
	}
	if(BX.adminFormTools)
	{
		BX.bind(BX.findChild(oCell, { "tag" : "input", "class" : "adm-designed-file"}, true), 'change', BX.adminFormTools._modified_file_onchange);
	}

	setTimeout(function() {
		var r = BX.findChildren(oCell, {tag: /^(input|select|textarea)$/i});
		if (r && r.length > 0)
		{
			for (var i=0,l=r.length;i<l;i++)
			{
				if (r[i].form && r[i].form.BXAUTOSAVE)
					r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
				else
					break;
			}
		}
	}, 10);

	var re = new RegExp("<script[^>]*?>([\\w\\s\\S]*?)</script>", "gmi");
	var otv;
	while (otv = re.exec(oCell.innerHTML))
	{
		if (otv[1])
		{
			BX.evalGlobal(otv[1]);
		}
	}
}
