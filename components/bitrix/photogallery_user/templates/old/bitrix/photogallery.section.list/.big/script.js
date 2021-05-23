function EditAlbum(url)
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
			PhotoMenu.PopupShow(div);
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
	for (var ii in form.elements)
	{
		if (form.elements[ii] && form.elements[ii].name)
		{
			if (form.elements[ii].type && form.elements[ii].type.toLowerCase() == "checkbox")
			{
				if (form.elements[ii].checked == true)
					oData[form.elements[ii].name] = form.elements[ii].value;
			}
			else
				oData[form.elements[ii].name] = form.elements[ii].value;
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
				if (document.getElementById("photo_album_name_" + result['ID']))
					document.getElementById("photo_album_name_" + result['ID']).innerHTML = result['NAME'];
				if (document.getElementById("photo_album_date_" + result['ID']))
					document.getElementById("photo_album_date_" + result['ID']).innerHTML = result['DATE'];
				if (document.getElementById("photo_album_description_" + result['ID']))
					document.getElementById("photo_album_description_" + result['ID']).innerHTML = result['DESCRIPTION'];

				if (document.getElementById("photo_album_password_" + result['ID']))
				{
					if (result['PASSWORD'].length <= 0)
						document.getElementById("photo_album_password_" + result['ID']).style.display = 'none';
					else
						document.getElementById("photo_album_password_" + result['ID']).style.display = '';
				}
				PhotoMenu.PopupHide('photo_section_edit');
			}
			catch(e)
			{
				if (document.getElementById('photo_section_edit'))
					document.getElementById('photo_section_edit').innerHTML = data;
			}
			BX.closeWait();
		});
	
	BX.showWait();
	CPHttpRequest.Post(TID, form.action, oData);
	return false;
}

function CheckFormEditIcon(form)
{
	if (typeof form != "object")
		return false;
	oData = {"AJAX_CALL" : "Y"};
	for (var ii in form.elements)
	{
		if (form.elements[ii] && form.elements[ii].name)
		{
			if (form.elements[ii].type && form.elements[ii].type.toLowerCase() == "checkbox")
			{
				if (form.elements[ii].checked == true)
					oData[form.elements[ii].name] = form.elements[ii].value;
			}
			else
				oData[form.elements[ii].name] = form.elements[ii].value;
		}
	}
	oData["photos"] = [];
	for (var ii = 0; ii < form.elements["photos[]"].length; ii++)
	{
		if (form.elements["photos[]"][ii].checked == true)
			oData["photos"].push(form.elements["photos[]"][ii].value);
	}
	
	TID = CPHttpRequest.InitThread();
	CPHttpRequest.SetAction(TID, 
		function(data)
		{
			result = {};
			try
			{
				eval("result = " + data + ";");
				if (document.getElementById("photo_album_img_" + result['ID']))
					document.getElementById("photo_album_img_" + result['ID']).src = result['SRC'];
				else if (document.getElementById("photo_album_cover_" + result['ID']))
					document.getElementById("photo_album_cover_" + result['ID']).style.backgroundImage = "url(" + result['SRC'] + ")";
				PhotoMenu.PopupHide('photo_section_edit');
			}
			catch(e)
			{
				if (document.getElementById('photo_section_edit'))
					document.getElementById('photo_section_edit').innerHTML = data;
			}
			BX.closeWait();
		});
	
	BX.showWait();
	CPHttpRequest.Post(TID, form.action, oData);
	return false;
}

function CheckFormEditIconCancel()
{
	PhotoMenu.PopupHide('photo_section_edit');
	return false;
}

function CancelSubmit()
{
	PhotoMenu.PopupHide('photo_section_edit');
	return false;
}

function DropAlbum(url)
{
	if ((typeof url == "string") && (url.length > 0))
	{
		TID = CPHttpRequest.InitThread();
		CPHttpRequest.SetAction(TID, function(data){
			BX.closeWait();
			result = {};
			try
			{
				eval("result = " + data + ";");
				if (result['ID'] && document.getElementById("photo_album_info_" + result['ID']))
					document.getElementById("photo_album_info_" + result['ID']).style.display = 'none';
			}
			catch(e){}
		});
		
		BX.showWait();
		
		CPHttpRequest.Send(TID, url, {"AJAX_CALL" : "Y"});
	}
	return false;
}


