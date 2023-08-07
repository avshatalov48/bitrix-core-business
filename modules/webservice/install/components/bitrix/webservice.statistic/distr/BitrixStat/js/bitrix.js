
var bitServer = "";
var bitLogin = "";
var bitPassword = "";

var webService;

var arPageArray = new Array();
arPageArray[0] = "common";
arPageArray[1] = "adv";
arPageArray[2] = "events";
arPageArray[3] = "phrases";
arPageArray[4] = "ref";
arPageArray[5] = "searchers";

var arQueryArray = new Array();
arQueryArray["users"] = "class=CStatisticWS&op=UsersOnline";
arQueryArray["common"] = "class=CStatisticWS&op=GetCommonValues";
arQueryArray["adv"] = "class=CStatisticWS&op=GetAdv";
arQueryArray["events"] = "class=CStatisticWS&op=GetEvents";
arQueryArray["phrases"] = "class=CStatisticWS&op=GetPhrases";
arQueryArray["ref"] = "class=CStatisticWS&op=GetRefSites";
arQueryArray["searchers"] = "class=CStatisticWS&op=GetSearchers";

var currentPageIndex = 0;

var timerLongTime = 600000;
var timerShortTime = 30000;

/*
document.onreadystatechange = function()
{
	if (document.readyState=="complete")
	{
		System.Gadget.settingsUI = "settings.html";
		LoadData();
	}
}
*/

var timer;
var bReload = true;
function Reload()
{
	//debugger;
	if (timer)
		clearTimeout(timer);
	if (!bReload)
		return;

	LoadData();

	var tm = timerShortTime;
	if (arPageArray[currentPageIndex] != "common")
		tm = timerLongTime;

	timer = setTimeout(Reload, tm);
}

System.Gadget.onSettingsClosed = function(event)
{
	//debugger;
	if (event.closeAction == event.Action.commit)
	{
		LoadMain();
	}
}

function CheckState()
{
    if (!System.Gadget.docked)
    {
        UndockedState();
    }
    else if (System.Gadget.docked)
    {
        DockedState(); 
    }
}

function DockedState()
{
	with (document.body.style)
	{
		height = "173px";
		width = "130px";
	}

	with (statContentDiv.style)
	{
		top = "30px";
		left = "10px";
		paddingRight = "4px";
	}
}

function UndockedState()
{
	with (document.body.style)
	{
		height = "173px"; //"232px";
		width = "130px"; //"296px";
    }

 	with (statContentDiv.style)
	{
		top = "30px"; //"13px";
		left = "10px"; //"13px";
		paddingRight = "4px"; //"14px";
	}
}

function LoadSettings()
{
	//debugger;
	bitServer = System.Gadget.Settings.read("bitServer");
	bitLogin = System.Gadget.Settings.read("bitLogin");
	bitPassword = System.Gadget.Settings.read("bitPassword");
}

function LoadMain()
{
	timerFlag = true;
	System.Gadget.settingsUI = "settings.html";
	LoadSettings();
	System.Gadget.Flyout.file = "flyout.html";
	Reload();
	document.body.focus();
}

function LoadData()
{
	RefreshData();
	CheckState();
	System.Gadget.onUndock = CheckState;
	System.Gadget.onDock = CheckState;
}

function ReceiveData()
{
	//debugger;
	if (webService.readyState == 4)
	{
		statLoadingDiv.style.display = 'none';
		statContentDiv.style.color = "#FFFFFF";
		if (webService.status == 200)
		{
			ShowData();
		}
		else
		{
			DisplayMessage(lServiceUnavail + " [" + webService.status + "] " + webService.statusText + "<br/>" + lCheckSettings);
		}
	}
}

function TableDeleteAllRows()
{
	statContentDiv.innerHTML = "";
}

function TableAddRow(data, action, paramT, param, param1)
{
	//debugger;
	var r = statContentDiv.appendChild(document.createElement("DIV"));
	r.style.width = "105px";

	if (action)
	{
		r.onclick = action;
		r.style.cursor = "hand";
		if (param)
		{
			r.setAttribute("BZATTRT", paramT);
			r.setAttribute("BZATTR", param);
			r.setAttribute("BZATTRI", param1);

			if (System.Gadget.Flyout.show)
			{
				var flyoutDiv = System.Gadget.Flyout.document;
				var id = flyoutDiv.getElementById("flyoutID").innerHTML;
				if (id == param1)
					AddContentToFlyout(paramT, param);
			}
		}
	}

	var c1, c2;

	c1 = r.appendChild(document.createElement("DIV"));
	c1.className = "data-div-style";

	if (data.length > 1)
	{
		c1.style.styleFloat = "left";
		c1.style.textAlign = "left";
		c1.setAttribute("title", data[0]);
	}

	c1.style.lineHeight = "15px";
	if (data.length > 1)
	{
		c1.style.textOverflow = "ellipsis";
		c1.style.overflow = "hidden";  
		c1.style.whiteSpace = "nowrap"; 
	}

	c1.innerHTML = data[0];

	if (data.length > 1)
	{
		c2 = r.appendChild(document.createElement("DIV"));
		c2.className = "data-div-style";

		c2.style.styleFloat = "right";
		c2.style.textAlign = "right";

		c2.innerHTML = data[1];
		if (data.length > 2)
			c2.setAttribute("title", data[2]);

		c1.style.width = (105 - c2.offsetWidth - 10) + "px";
	}
}


function ShowCommon(xml)
{
	//debugger;
	SetTitle(lStatTitle);
	TableDeleteAllRows();

	TableAddRow([lHits, xml.selectSingleNode("/result/TOTAL_HITS").text + "<br />" + xml.selectSingleNode("/result/TODAY_HITS").text, lTotalTodayAlt], ShowFlyAttrib, lHitsTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2"  class="tableHead1">' + lHitsTitleAlt + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/TODAY_HITS").text + '</a></td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/YESTERDAY_HITS").text + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/B_YESTERDAY_HITS").text + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/TOTAL_HITS").text + '</td></tr></table><br/><a href="javascript:Go2Url(\'/bitrix/admin/hit_list.php?lang=' + lLang + '\')">' + lUserHitsL + '</a>', "hits");

	TableAddRow([lHosts, xml.selectSingleNode("/result/TOTAL_HOSTS").text + "<br />" + xml.selectSingleNode("/result/TODAY_HOSTS").text, lTotalTodayAlt], ShowFlyAttrib, lHostsTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2"  class="tableHead1">' + lHostsTitleAlt + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/TODAY_HOSTS").text + '</td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/YESTERDAY_HOSTS").text + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/B_YESTERDAY_HOSTS").text + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/TOTAL_HOSTS").text + '</td></tr></table>', "hosts");

	TableAddRow([lGuests, xml.selectSingleNode("/result/TOTAL_GUESTS").text + "<br />" + xml.selectSingleNode("/result/TODAY_GUESTS").text, lTotalTodayAlt], ShowFlyAttrib, lGuestsTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2"  class="tableHead1">' + lGuestsTitleAlt + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/TODAY_GUESTS").text + '</td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/YESTERDAY_GUESTS").text + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/B_YESTERDAY_GUESTS").text + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + xml.selectSingleNode("/result/TOTAL_GUESTS").text + '</td></tr></table><br/><a href="javascript:Go2Url(\'/bitrix/admin/guest_list.php?lang=' + lLang + '\')">' + lUsersL + '</a>', "guests");

	var nodes = xml.selectNodes("/result/ONLINE_LIST/SESSIONS/SESSION");
	var num = nodes.length;
	if (num > 50)
		num = 50;

	var strTableText = '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td class="tableHead1">' + lUserName + '</td><td class="tableHead2">' + lHitsF + '</td><td class="tableHead3">' + lIP + '</td></tr>';
	var i, j;
	for (i = 0; i < num; i++)
	{
		var id, guest_id, name, hits, ip, url_last;
		for (j = 0; j < nodes[i].childNodes.length; j++)
		{
			if (nodes[i].childNodes[j].nodeName == "USER_NAME")
				name = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "HITS")
				hits = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "IP_LAST")
				ip = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "LAST_USER_ID")
				id = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "GUEST_ID")
				guest_id = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "URL_LAST")
				url_last = nodes[i].childNodes[j].text;
		}
		if (name == "[0]")
			name = lNotRegistered;
		else
			name = '<a href="javascript:Go2Url(\'/bitrix/admin/user_edit.php?ID=' + id + '&lang=' + lLang + '\')">' + name + '</a>';
		strTableText += '<tr><td class="tableBody1">' + name + '</td><td class="tableBody2" align="right"><a href="javascript:Go2Url(\'/bitrix/admin/hit_list.php?find_guest_id=' + guest_id + '&find_guest_id_exact_match=Y&set_filter=Y&lang=' + lLang + '\')" title=\"' + url_last + '\">' + hits + '</a></td><td class="tableBody3"><a href="javascript:Go2Url(\'/' + ip + '\', \'http://www.whois.sc\')">' + ip + '</a></td></tr>';
	}
	strTableText += '</table>';

	TableAddRow([lOnLine, xml.selectSingleNode("/result/ONLINE_GUESTS").text], ShowFlyAttrib, lUsersOnlineAlt, strTableText, "online");
}

function ShowAdvs(xml)
{
	//debugger;
	SetTitle(lAdvTitle);
	TableDeleteAllRows();

	var nodes = xml.selectNodes("/result/top");

	var num = nodes.length;
	if (num > 7)
		num = 7;

	var i, j;
	for (i = 0; i < num; i++)
	{
		var id, name, today, yesterday, bef_yesterday, all;
		for (j = 0; j < nodes[i].childNodes.length; j++)
		{
			if (nodes[i].childNodes[j].nodeName == "name")
				name = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "today")
				today = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "yesterday")
				yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "bef_yesterday")
				bef_yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "all")
				all = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "id")
				id = nodes[i].childNodes[j].text;
		}

		TableAddRow([name, today, lTodayAlt], ShowFlyAttrib, lAdvTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2"  class="tableHead1">' + name + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + today + '</td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + yesterday + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + bef_yesterday + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + all + '</td></tr></table><br/><a href="javascript:Go2Url(\'/bitrix/admin/adv_detail.php?lang=' + lLang + '&find=' + id + '&set_filter=Y\')">' + lAdvAnalizL + '</a><br/><a href="javascript:Go2Url(\'/bitrix/admin/visit_section_list.php?lang=' + lLang + '&find_adv[]=' + id + '&set_filter=Y\')">' + lAdvSectL + '</a><br/><a href="javascript:Go2Url(\'/bitrix/admin/path_list.php?lang=' + lLang + '&find_adv[]=' + id + '&set_filter=Y\')">' + lAdvPathL + '</a><br/><a href="javascript:Go2Url(\'/bitrix/admin/adv_dynamic_list.php?lang=' + lLang + '&find_adv_id=' + id + '&find_event_id_exact_match=Y&set_default=Y\')">' + lAdvDynL + '</a>', name);
	}
}

function ShowPhrases(xml)
{
	//debugger;
	SetTitle(lSearchTitle);
	TableDeleteAllRows();

	var nodes = xml.selectNodes("/result/top");

	var num = nodes.length;
	if (num > 7)
		num = 7;

	var i, j;
	for (i = 0; i < num; i++)
	{
		var name, today, yesterday, bef_yesterday, all;
		for (j = 0; j < nodes[i].childNodes.length; j++)
		{
			if (nodes[i].childNodes[j].nodeName == "name")
				name = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "today")
				today = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "yesterday")
				yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "bef_yesterday")
				bef_yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "all")
				all = nodes[i].childNodes[j].text;
		}

		TableAddRow([name, today, lTodayAlt], ShowFlyAttrib, lSearchTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2" class="tableHead1">' + name + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + today + '</td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + yesterday + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + bef_yesterday + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + all + '</td></tr></table>', name);
	}
}

function ShowRefs(xml)
{
	//debugger;
	SetTitle(lLinksTitle);
	TableDeleteAllRows();

	var nodes = xml.selectNodes("/result/top");

	var num = nodes.length;
	if (num > 7)
		num = 7;

	var i, j;
	for (i = 0; i < num; i++)
	{
		var name, today, yesterday, bef_yesterday, all;
		for (j = 0; j < nodes[i].childNodes.length; j++)
		{
			if (nodes[i].childNodes[j].nodeName == "name")
				name = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "today")
				today = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "yesterday")
				yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "bef_yesterday")
				bef_yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "all")
				all = nodes[i].childNodes[j].text;
		}

		TableAddRow([name, today, lTodayAlt], ShowFlyAttrib, lLinksTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2" class="tableHead1">' + name + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + today + '</td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + yesterday + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + bef_yesterday + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + all + '</td></tr></table><br/><a href="javascript:Go2Url(\'/bitrix/admin/referer_list.php?lang=' + lLang + '&find_from_domain=' + name + '&set_filter=Y\')">' + lRefSitesL + '</a>', name);
	}
}

function ShowSearchers(xml)
{
	//debugger;
	SetTitle(lIndexTitle);
	TableDeleteAllRows();

	var nodes = xml.selectNodes("/result/top");

	var num = nodes.length;
	if (num > 7)
		num = 7;

	var i, j;
	for (i = 0; i < num; i++)
	{
		var id, name, today, yesterday, bef_yesterday, all;
		for (j = 0; j < nodes[i].childNodes.length; j++)
		{
			if (nodes[i].childNodes[j].nodeName == "name")
				name = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "today")
				today = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "yesterday")
				yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "bef_yesterday")
				bef_yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "all")
				all = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "id")
				id = nodes[i].childNodes[j].text;
		}

		TableAddRow([name, today, lTodayAlt], ShowFlyAttrib, lIndexTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2" class="tableHead1">' + name + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + today + '</td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + yesterday + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + bef_yesterday + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + all + '</td></tr></table><br/><a href="javascript:Go2Url(\'/bitrix/admin/searcher_graph_list.php?lang=' + lLang + '&find_searchers[]=' + id + '&set_filter=Y\')">' + lIndexGraphL + '</a>', name);
	}
}

function ShowEvents(xml)
{
	//debugger;
	SetTitle(lEventsTitle);
	TableDeleteAllRows();

	var nodes = xml.selectNodes("/result/top");

	var num = nodes.length;
	if (num > 7)
		num = 7;

	var i, j;
	for (i = 0; i < num; i++)
	{
		var id, name, today, yesterday, bef_yesterday, all;
		for (j = 0; j < nodes[i].childNodes.length; j++)
		{
			if (nodes[i].childNodes[j].nodeName == "name")
				name = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "today")
				today = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "yesterday")
				yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "bef_yesterday")
				bef_yesterday = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "all")
				all = nodes[i].childNodes[j].text;
			else if (nodes[i].childNodes[j].nodeName == "id")
				id = nodes[i].childNodes[j].text;
		}

		TableAddRow([name, today, lTodayAlt], ShowFlyAttrib, lEventsTitleAlt, '<table cellpadding="3" cellspacing="0" border="0" style="width:100%;"><tr><td colspan="2" class="tableHead1">' + name + '</td></tr><tr><td class="tableBody1">' + lToday + '</td><td class="tableBody3" align="right">' + today + '</td></tr><tr><td class="tableBody1">' + lYesterday + '</td><td class="tableBody3" align="right">' + yesterday + '</td></tr><tr><td class="tableBody1">' + lBefYesterday + '</td><td class="tableBody3" align="right">' + bef_yesterday + '</td></tr><tr><td class="tableBody1">' + lTotal + '</td><td class="tableBody3" align="right">' + all + '</td></tr></table><br/><a href="javascript:Go2Url(\'/bitrix/admin/event_graph_list.php?lang=' + lLang + '&find_events[]=' + id + '&set_filter=Y\')">' + lEventGraphL + '</a>', name);
	}
}

function ShowFlyAttrib()
{
	var dataTitle = event.srcElement.parentElement.getAttribute("BZATTRT");
	var data = event.srcElement.parentElement.getAttribute("BZATTR");
	var id = event.srcElement.parentElement.getAttribute("BZATTRI");
	if (!dataTitle)
	{
		dataTitle = event.srcElement.getAttribute("BZATTRT");
		data = event.srcElement.getAttribute("BZATTR");
		id = event.srcElement.getAttribute("BZATTRI");
	}
	ShowFlyout(dataTitle, data, id);
}

function SetPrevPage()
{
	currentPageIndex = currentPageIndex - 1;
	if (currentPageIndex < 0)
		currentPageIndex = 5;

	Reload();
}

function SetNextPage()
{
	currentPageIndex = currentPageIndex + 1;
	if (currentPageIndex > 5)
		currentPageIndex = 0;

	Reload();
}

function ShowData()
{
	//debugger;
	if (arPageArray[currentPageIndex] == "common")
	{
		ShowCommon(webService.responseXML);
	}
	else if (arPageArray[currentPageIndex] == "adv")
	{
		ShowAdvs(webService.responseXML);
	}
	else if (arPageArray[currentPageIndex] == "events")
	{
		ShowEvents(webService.responseXML);
	}
	else if (arPageArray[currentPageIndex] == "phrases")
	{
		ShowPhrases(webService.responseXML);
	}
	else if (arPageArray[currentPageIndex] == "ref")
	{
		ShowRefs(webService.responseXML);
	}
	else if (arPageArray[currentPageIndex] == "searchers")
	{
		ShowSearchers(webService.responseXML);
	}
}

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function base64_encode(inp)
{
	var out = "";
	var chr1, chr2, chr3 = "";
	var enc1, enc2, enc3, enc4 = "";
	var i = 0;
	do
	{
		chr1 = inp.charCodeAt(i++);
		chr2 = inp.charCodeAt(i++);
		chr3 = inp.charCodeAt(i++);

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2))
		{
			enc3 = enc4 = 64;
		}
		else if (isNaN(chr3))
		{
			enc4 = 64;
		}

		out = out + keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4);
		chr1 = chr2 = chr3 = "";
		enc1 = enc2 = enc3 = enc4 = "";
	}
	while (i < inp.length);

	return out;
}

function RefreshData()
{
	//debugger;
	try
	{
		webService = new ActiveXObject("Microsoft.XMLHTTP");
		webService.onreadystatechange = ReceiveData;
		webService.open("POST", bitServer + "/bitrix/tools/stat_gadget.php?" + arQueryArray[arPageArray[currentPageIndex]] + "&directcall=1&rnd=" + Math.random());
		webService.setRequestHeader("Authorization", "Basic " + base64_encode(bitLogin + ":" + bitPassword));
		webService.send(null);

		statLoadingDiv.style.display = 'block';
		statContentDiv.style.color = "#888888";
	}
	catch (e)
	{
		if (bitServer.length <= 0)
			DisplayMessage(lCheckSettingsI, lMessageTitle);
		else
			DisplayMessage(lServiceUnavail + " " + e.message + "<br/>" + lCheckSettings);
	}
}

function SetTitle(title)
{
	gTitle.innerText = title;
}

function DisplayMessage(errorText, errorTitle)
{
	if (errorTitle)
		SetTitle(errorTitle);
	else
		SetTitle(lError);
	TableDeleteAllRows();
	TableAddRow([errorText]);
}

function CheckHref(sURL)
{
	var safeURL = "";
	var prefixIndex = sURL.search("http://");
	if (prefixIndex == 0)
		return sURL;

	prefixIndex = sURL.search("https://");
	if (prefixIndex == 0)
		return sURL;

	prefixIndex = sURL.search("ftp://");
	if (prefixIndex == 0)
		return sURL;

	return safeURL;
}

function ShowFlyout(dataTitle, data, id)
{
	if (System.Gadget.Flyout.show)
	{
		AddContentToFlyout(dataTitle, data, id);
	}
	else
	{
		System.Gadget.Flyout.show = true;
		System.Gadget.Flyout.onShow = function()
		{
			AddContentToFlyout(dataTitle, data, id);
		}
	}
}

function AddContentToFlyout(dataTitle, data, id)
{
	//debugger;
	try
	{
		if (System.Gadget.Flyout.show)
		{
			var flyoutDiv = System.Gadget.Flyout.document;
			try
			{
				flyoutDiv.getElementById("flyoutTitle").innerHTML = dataTitle;
				flyoutDiv.getElementById("flyoutContentDiv").innerHTML = data;
				flyoutDiv.getElementById("flyoutID").innerHTML = id;
			}
			catch (e)
			{
			}
		}
	}
	catch (e)
	{
		//catch slow flyout - no div object will be available.
	}
}

function KeyNavigateClose()
{
	switch (event.keyCode)
	{
		case 27:
			HideFlyout();
			break;
	}
}

function HideFlyout()
{
	System.Gadget.Flyout.show = false;
}

function Go2Bitrix()
{
	System.Shell.execute(CheckHref(lBitrixUri));
}

function Go2Url(url, host)
{
	if (host)
	{
		System.Shell.execute(CheckHref(host + url));
	}
	else
	{
		LoadSettings();
		System.Shell.execute(CheckHref(bitServer + url));
	}
}
