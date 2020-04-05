var sonetEventsFRDiv;
var sonetEventsGRDiv;
var sonetEventsMSDiv;
var sonetEventsErrorDiv;

var sonetDynevRand = Math.floor(Math.random() * 1000);
var sonetDynevTout;
var sonetDynevOldTitle = "";
var sonetDynevNewTitle = "";

if (!window.XMLHttpRequest)
{
	var XMLHttpRequest = function()
	{
		try { return new ActiveXObject("MSXML3.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP.3.0") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
	}
}

var sonetEventXmlHttpGet = new XMLHttpRequest();
var sonetEventXmlHttpSet = new XMLHttpRequest();

jsUtils.addEvent(window, "load", sonet_dynev_onload);

function sonet_dynev_msgs_set(params)
{
	if (sonetEventXmlHttpSet.readyState % 4)
		return;

	sonetEventsErrorDiv.style.display = "none";
	sonetDynevRand += 1;

	sonetEventXmlHttpSet.open(
		"get",
		sonetDynevMsgSetPath + "?rand=" + sonetDynevRand
			+ "&" + sonetDynevSessid
			+ "&" + params
			+ "&r=" + Math.floor(Math.random() * 1000)
	);
	sonetEventXmlHttpSet.send(null);

	sonetEventXmlHttpSet.onreadystatechange = function()
	{
		if (sonetEventXmlHttpSet.readyState == 4 && sonetEventXmlHttpSet.status == 200)
		{
			if (sonetEventXmlHttpSet.responseText)
			{
				sonetEventsErrorDiv.style.display = "block";
				sonetEventsErrorDiv.innerHTML = sonetEventXmlHttpSet.responseText;
			}
			sonet_dynev_reset();
		}
	}
}

function sonet_dynev_onload()
{
	eventsDiv = document.getElementById('sonet_events_div');
	if (eventsDiv)
	{
		eventsDiv.parentNode.removeChild(eventsDiv);
		document.body.appendChild(eventsDiv);
		
	 	windowSize = BX.GetWindowInnerSize();
		eventsDiv.style.top = ((windowSize.innerHeight/2)-150) + "px";
		eventsDiv.style.left = ((windowSize.innerWidth/2)-150) + "px";		
	}
	sonetEventsFRDiv = document.getElementById('sonet_events_fr');
	sonetEventsGRDiv = document.getElementById('sonet_events_gr');
	sonetEventsMSDiv = document.getElementById('sonet_events_ms');
	sonetEventsErrorDiv = document.getElementById('sonet_events_err');
	sonet_dynev_reset();
}

function sonet_dynev_reset()
{
	sonetEventsFRDiv.style.display = "none";
	sonetEventsGRDiv.style.display = "none";
	sonetEventsMSDiv.style.display = "none";

	clearTimeout(sonetDynevTout);
	sonetEventXmlHttpGet.abort();
	sonetDynevNewTitle = "";
	sonet_dynev_settitle();

	sonetDynevTout = setTimeout("sonet_dynev_msgs_get();", 1);
}

function sonet_dynev_parse(str)
{
	str = str.replace(/^\s+|\s+$/g, '');
	while (str.length > 0 && str.charCodeAt(0) == 65279)
		str = str.substring(1);

	if (str.length <= 0)
		return false;

	eval("arData = " + str);
	return arData;
}

function sonet_dynev_msgs_get()
{
	if (sonetDynevUserId <= 0)
		return;

	clearTimeout(sonetDynevTout);
	sonetDynevTout = setTimeout("sonet_dynev_msgs_get();", Math.round(1000 * sonetDynevTimeout));
	if (sonetEventXmlHttpGet.readyState % 4)
		return;

	sonetDynevRand += 1;
	sonetEventXmlHttpGet.open(
		"get",
		sonetDynevMsgGetPath + "?" + "rand=" + sonetDynevRand
		+ "&cuid=" + escape(sonetDynevUserId)
		+ "&site=" + escape(sonetDynevSiteId)
		+ "&up=" + escape(sonetDynevPath2User)
		+ "&gp=" + escape(sonetDynevPath2Group)
		+ "&mp=" + escape(sonetDynevPath2Message)
		+ "&mpm=" + escape(sonetDynevPath2MessageMess)
		+ "&cp=" + escape(sonetDynevPath2Chat)
		+ "&nt=" + escape(sonetDynevUserNameTemplate)
		+ "&sl=" + escape(sonetDynevUserShowLogin)
		+ "&popup=" + escape(sonetDynevUserPopup)
		+ "&r=" + Math.floor(Math.random() * 1000)
		+ "&pt=Y"
	);
	sonetEventXmlHttpGet.send(null);

	sonetEventXmlHttpGet.onreadystatechange = function()
	{
		if (sonetEventXmlHttpGet.readyState == 4 && sonetEventXmlHttpGet.status == 200)
		{
			var data = sonet_dynev_parse(sonetEventXmlHttpGet.responseText);

			if (data)
			{
				if (data[0] == '*')
				{
//					sonet_dynev_onload();
					return;
				}

				if (data[0] == 'FR')
				{
					sonet_dynev_out_msg_fr(data);
				}
				if (data[0] == 'GR')
				{
					sonet_dynev_out_msg_gr(data);
				}
				if (data[0] == 'M')
				{
					sonet_dynev_out_msg_ms(data);
				}
				sonet_dynev_settitle();
			}
		}
	}
}

function sonet_dynev_out_msg_fr(data)
{
	clearTimeout(sonetDynevTout);
	sonetEventXmlHttpGet.abort();

	jsUtils.removeAllEvents(document.getElementById('sonet_events_fr_add'));
	jsUtils.removeAllEvents(document.getElementById('sonet_events_fr_reject'));

	eventsDiv = document.getElementById('sonet_events_div');
	if (eventsDiv)
	{
	 	var windowSize = BX.GetWindowInnerSize();
		var scrollPos = BX.GetWindowScrollPos();

		eventsDiv.style.top = ((windowSize.innerHeight/2)-150+scrollPos.scrollTop) + "px";
		eventsDiv.style.left = ((windowSize.innerWidth/2)-150+scrollPos.scrollLeft) + "px";		
	}

	sonetEventsFRDiv.style.display = "block";
	sonetEventsGRDiv.style.display = "none";
	sonetEventsMSDiv.style.display = "none";

	document.getElementById('sonet_events_fr_sender_photo').innerHTML = data[4];
	
	var s = '';

	var anchor_id = Math.floor(Math.random()*100000) + 1;

	if (data[6] == 'Y')
		s += "<a href=\"" + data[5] + "\" id=\"anchor_" + anchor_id + "\">";	
	s += data[3];
	if (data[6] == 'Y')
		s += "</a>";
	if (data[7] == 'Y')
		s += "<br><span class=\"sonet_online\">" + sonetDynevTrOnline + "</span>";

	document.getElementById('sonet_events_fr_sender_desc').innerHTML = s;

	if (sonetDynevUseTooltip == 'Y')
		BX.tooltip(data[2], "anchor_" + anchor_id, sonetDynevMULAjaxPage);
	
	document.getElementById('sonet_events_fr_date').innerHTML = data[8];
	document.getElementById('sonet_events_fr_message').innerHTML = data[9];

	jsUtils.addEvent(document.getElementById('sonet_events_fr_add'), "click", function () {sonet_dynev_msgs_set(data[10]);});
	jsUtils.addEvent(document.getElementById('sonet_events_fr_reject'), "click", function () {sonet_dynev_msgs_set(data[11]);});

	sonetDynevNewTitle = sonetDynevTrFrTitle;
}

function sonet_dynev_out_msg_gr(data)
{
	clearTimeout(sonetDynevTout);
	sonetEventXmlHttpGet.abort();

	jsUtils.removeAllEvents(document.getElementById('sonet_events_gr_add'));
	jsUtils.removeAllEvents(document.getElementById('sonet_events_gr_reject'));

	eventsDiv = document.getElementById('sonet_events_div');
	if (eventsDiv)
	{
	 	var windowSize = BX.GetWindowInnerSize();
		var scrollPos = BX.GetWindowScrollPos();

		eventsDiv.style.top = ((windowSize.innerHeight/2)-150+scrollPos.scrollTop) + "px";
		eventsDiv.style.left = ((windowSize.innerWidth/2)-150+scrollPos.scrollLeft) + "px";		
	}

	sonetEventsFRDiv.style.display = "none";
	sonetEventsGRDiv.style.display = "block";
	sonetEventsMSDiv.style.display = "none";

	document.getElementById('sonet_events_gr_group_photo').innerHTML = data[9];
	
	var s = '';
	if (data[11] == 'Y')
		s += "<a href=\"" + data[10] + "\">";
	s += data[8];
	if (data[11] == 'Y')
		s += "</a>";

	document.getElementById('sonet_events_gr_group_desc').innerHTML = s;
	document.getElementById('sonet_events_gr_date').innerHTML = data[7];

	var anchor_id = Math.floor(Math.random()*100000) + 1;

	s = sonetDynevTrGrInv + ': ';
	if (data[6] == 'Y')
		s += "<a href=\"" + data[5] + "\" id=\"anchor_" + anchor_id + "\">";	
	s += data[3];
	if (data[6] == 'Y')
		s += "</a>";
	
	document.getElementById('sonet_events_gr_sender').innerHTML = s;

	if (sonetDynevUseTooltip == 'Y')
		BX.tooltip(data[2], "anchor_" + anchor_id, sonetDynevMULAjaxPage);

	document.getElementById('sonet_events_gr_message').innerHTML = data[12];

	jsUtils.addEvent(document.getElementById('sonet_events_gr_add'), "click", function () {sonet_dynev_msgs_set(data[13]);});
	jsUtils.addEvent(document.getElementById('sonet_events_gr_reject'), "click", function () {sonet_dynev_msgs_set(data[14]);});

	sonetDynevNewTitle = sonetDynevTrGrTitle;
}

function sonet_dynev_out_msg_ms(data)
{
	clearTimeout(sonetDynevTout);
	sonetEventXmlHttpGet.abort();

	jsUtils.removeAllEvents(document.getElementById('sonet_events_ms_answer'));
	jsUtils.removeAllEvents(document.getElementById('sonet_events_ms_close'));

	eventsDiv = document.getElementById('sonet_events_div');
	if (eventsDiv)
	{
	 	var windowSize = BX.GetWindowInnerSize();
		var scrollPos = BX.GetWindowScrollPos();

		eventsDiv.style.top = ((windowSize.innerHeight/2)-150+scrollPos.scrollTop) + "px";
		eventsDiv.style.left = ((windowSize.innerWidth/2)-150+scrollPos.scrollLeft) + "px";		
	}

	sonetEventsFRDiv.style.display = "none";
	sonetEventsGRDiv.style.display = "none";
	sonetEventsMSDiv.style.display = "block";

	document.getElementById('sonet_events_ms_sender_photo').innerHTML = data[4];
	
	var s = '';

	var anchor_id = Math.floor(Math.random()*100000) + 1;

	if (data[6] == 'Y')
		s += "<a href=\"" + data[5] + "\" id=\"anchor_" + anchor_id + "\">";
	s += data[3];
	if (data[6] == 'Y')
		s += "</a>";
	if (data[7] == 'Y')
		s += "<br><span class=\"sonet_online\">" + sonetDynevTrOnline + "</span>";

	document.getElementById('sonet_events_ms_sender_desc').innerHTML = s;

	if (sonetDynevUseTooltip == 'Y')
		BX.tooltip(data[2], "anchor_" + anchor_id, sonetDynevMULAjaxPage);

	document.getElementById('sonet_events_ms_date').innerHTML = data[8];
	document.getElementById('sonet_events_ms_message').innerHTML = data[10].replace(/<WBR\/>&shy;/gi, " ");

	if (data[11] == "Y")
		document.getElementById('sonet_events_ms_answer').style.display = "inline";
	else
		document.getElementById('sonet_events_ms_answer').style.display = "none";

	jsUtils.addEvent(document.getElementById('sonet_events_ms_answer'), "click", 
		function () 
		{
			window.open(data[12], '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5));
			sonetEventsMSDiv.style.display = 'none';
			sonetDynevNewTitle = "";
			sonetDynevTout = setTimeout("sonet_dynev_msgs_get();", Math.round(1000 * sonetDynevTimeout));
		}
	);
	jsUtils.addEvent(document.getElementById('sonet_events_ms_close'), "click", function () {sonet_dynev_msgs_set(data[14]);});

	if (data[15] == "Y")
	{
		document.getElementById('sonet_events_ms_ban').style.display = "block";
		document.getElementById('sonet_events_ms_ban_link').href = "javascript:sonet_dynev_msgs_set('" + data[16] + "');";
	}
	else
	{
		document.getElementById('sonet_events_ms_ban').style.display = "none";
	}

	sonetDynevNewTitle = sonetDynevTrMsTitle;
}

var bbb = true;
function sonet_dynev_settitle()
{
	if (sonetDynevNewTitle.length > 0)
	{
		if (sonetDynevOldTitle.length <= 0)
			sonetDynevOldTitle = document.title;

		if (bbb)
			document.title = sonetDynevNewTitle;
		else
			document.title = "*"+sonetDynevNewTitle;

		bbb = !bbb;

		setTimeout("sonet_dynev_settitle()", 1000);
	}
	else
	{
		if (sonetDynevOldTitle.length > 0 && document.title != sonetDynevOldTitle)
			document.title = sonetDynevOldTitle;
	}
}