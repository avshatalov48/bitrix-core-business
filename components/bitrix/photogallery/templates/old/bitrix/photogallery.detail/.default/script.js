function EditPhoto(url)
{
	if ((typeof url == "string") && (url.length > 0))
	{
		CPHttpRequest1 = new JCPHttpRequest();
		CPHttpRequest1._SetHandler = function(TID, httpRequest)
		{
			var _this = this;
	
			function __handlerReadyStateChange()
			{
				if(httpRequest.readyState == 4)
				{
					_this._OnDataReady(TID, httpRequest.responseText);
					_this._Close(TID, httpRequest);
				}
			}
	
			httpRequest.onreadystatechange = __handlerReadyStateChange;
		}
		
		TID = CPHttpRequest1.InitThread();
		CPHttpRequest1.SetAction(TID, function(data){
			BX.closeWait();
			var div = document.createElement("DIV");
			div.id = "photo_section_edit";
			div.style.visible = 'hidden';
			div.className = "photo-popup";
			div.style.position = 'absolute';
			div.innerHTML = data;
			
			div.onclick = function(e)
			{
				if (!jsUtils.IsIE)
				{
					e.preventDefault();
					e.stopPropagation();
				}
			}
			var left = parseInt(document.body.scrollLeft + document.body.clientWidth/2 - div.offsetWidth/2);
			var top = parseInt(document.body.scrollTop + document.body.clientHeight/2 - div.offsetHeight/2);
			
			var scripts = div.getElementsByTagName('script');
		    for (var i = 0; i < scripts.length; i++)
		    {
		        var thisScript = scripts[i];
		        var text;
		        var sSrc = thisScript.src.replace(/http\:\/\/[^\/]+\//gi, '');
		        if (thisScript.src && sSrc != 'bitrix/js/main/utils.js' && sSrc != 'bitrix/js/main/admin_tools.js' &&
		        	sSrc != '/bitrix/js/main/utils.js' && sSrc != '/bitrix/js/main/admin_tools.js') 
		        {
		            var newScript = document.createElement("script");
		            newScript.type = 'text/javascript';
		            newScript.src = thisScript.src;
		            document.body.appendChild(newScript);
		        }
		        else if (thisScript.text || thisScript.innerHTML) 
		        {
		        	text = (thisScript.text ? thisScript.text : thisScript.innerHTML);
					text = (""+text).replace(/^\s*<!\-\-/, '').replace(/\-\->\s*$/, '');
		            eval(text);
		        }
		    }
		    
	    	data = data.replace(/\<script([^\>])*\>([^\<]*)\<\/script\>/gi, '');
	    	div.innerHTML = data;
		    document.body.appendChild(div);
			PhotoMenu.PopupShow(div, {'top' : top, 'left' : left});
		});
		
		BX.showWait();
		
		CPHttpRequest1.Send(TID, url, {"AJAX_CALL" : "Y"});
	}
	return false;
}

function CheckForm(form)
{
	if (typeof form != "object")
		return false;
	oData = {"AJAX_CALL" : "Y"};
	for (var ii = 0; ii < form.elements.length; ii++)
	{
		if (form.elements[ii] && form.elements[ii].name)
		{
			if (form.elements[ii].type && form.elements[ii].type == "checkbox")
			{
				if (form.elements[ii].checked == true)
					oData[form.elements[ii].name] = form.elements[ii].value;
			}
			else
			{
				oData[form.elements[ii].name] = form.elements[ii].value;
			}
		}
	}
	
	TID = CPHttpRequest.InitThread();
	CPHttpRequest.SetAction(TID, 
		function(data)
		{
			result = {};
			try
			{
				eval("result = " + data + ";");
				if (result['url'] && result['url'].length > 0)
					jsUtils.Redirect({}, result['url']);
				else
				{
					if (BX("photo_title"))
						BX("photo_title").innerHTML = result['TITLE'];
					if (BX("photo_date"))
						BX("photo_date").innerHTML = result['DATE'];
					if (BX("photo_tags"))
						BX("photo_tags").innerHTML = result['TAGS'];
					if (BX("photo_description"))
						BX("photo_description").innerHTML = result['DESCRIPTION'];
				}
				PhotoMenu.PopupHide('photo_section_edit');
			}
			catch(e)
			{
				if (BX('photo_section_edit'))
					BX('photo_section_edit').innerHTML = data;
			}
			BX.closeWait();
		});
	
	BX.showWait();
	CPHttpRequest.Post(TID, form.action, oData);
	return false;
}

function CancelSubmit()
{
	PhotoMenu.PopupHide('photo_section_edit');
	return false;
}

function ShowOriginal(src, title)
{
	var SrcWidth = screen.availWidth;
	var SrcHeight = screen.availHeight;
	var sizer = false;
	var text = '';
	if (!title)
		title = "";
	if (document.all)
	{
		 sizer = window.open("","","height=SrcHeight,width=SrcWidth,top=0,left=0,scrollbars=yes,fullscreen=yes");
	}
	else
	{
		sizer = window.open('',src,'width=SrcWidth,height=SrcHeight,menubar=no,status=no,location=no,scrollbars=yes,fullscreen=yes,directories=no,resizable=yes');
	}
	text += '<html><head>';
	text += '\n<script language="JavaScript" type="text/javascript">';
text += '\nfunction SetBackGround(div)';
text += '\n{';
text += '\n		if (!div){return false;}';
text += '\n		document.body.style.backgroundColor = div.style.backgroundColor;';
text += '\n}';
text += '\n</script>';
text += '\n</head>';
text += '\n<title>';
text += ('\n' + title);
text += '\n</title>';
text += '\n<body bgcolor="#999999">';
text += '\n';
text += '\n<table width="100%" height="96%" border=0 cellpadding=0 cellspacing=0>';
text += '\n<tr><td align=right>';
text += '\n<table align=center cellpadding=0 cellspacing=2 border=0>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#FFFFFF;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#E5E5E5;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#CCCCCC;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#B3B3B3;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#999999;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#808080;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#666666;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#4D4D4D;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#333333;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#1A1A1A;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px; background-color:#000000;" onmouseover="SetBackGround(this);"></div></td></tr>';
text += '\n<tr><td><div style="width:18px; height:18px;"></div></td>';
text += '\n</table></td>';
text += '\n<td align=center><img alt="" border=0 src="' + src + '" onClick="window.close();" style="cursor:pointer; cursor:hand;" /></td></tr>';
//text += '\n<tr><td align=center>';
//text += '\n<table align=center cellpadding=0 cellspacing=2 border=0>';
//text += '\n<tr>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#FFFFFF;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#E5E5E5;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#CCCCCC;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#B3B3B3;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#999999;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#808080;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#666666;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#4D4D4D;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#333333;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#1A1A1A;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px; background-color:#000000;" onmouseover="SetBackGround(this);"></div></td>';
//text += '\n<td><div style="width:18px; height:18px;"></div></td>';
//text += '\n<td><a href="#" onClick=window.close()><img src="/pics/b_close.gif" width=18 height=18 border=0></a></td>';
//text += '\n</tr></table>';
text += '\n</table></body></html>';
	sizer.document.write(text);


	return true;
}

bPhotoUtilsLoad = true;