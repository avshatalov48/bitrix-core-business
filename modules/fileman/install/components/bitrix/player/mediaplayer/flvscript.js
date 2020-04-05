function getFlashVersion()
{
	var v = 0;
	var n = navigator;
	if (n.platform == 'Win32' && n.userAgent.indexOf('Opera') == (-1) && window.ActiveXObject)
	{
		for (var i = 9; i > 2; i--)
			if (new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+i))
				return i;
	}
	else if(n.plugins)
	{
		for (var i = 0, l = n.plugins.length; i < l; i++)
			if (n.plugins[i].name.indexOf('Flash') != -1)
				v = parseInt(n.plugins[i].description.substr(16, 2));
	}
	return v;
}

function showFLVPlayer(id, mess)
{
	var oDiv = document.getElementById(id + '_div');
	if (oDiv)
	{
		oDiv.style.display = 'block';
		if (getFlashVersion() < 9)
			oDiv.innerHTML = mess;
	}
}