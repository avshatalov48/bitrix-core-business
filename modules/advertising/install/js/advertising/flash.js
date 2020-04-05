function bx_adv_getFlashVersion()
{
	var v = 0;
	var n = navigator;
	var i, l;
	if (n.platform == 'Win32' && n.userAgent.indexOf('Opera') == (-1) && window.ActiveXObject)
	{
		try
		{
			for (i = 11; i > 2; i--)
				if (new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+i))
					return i;
		}
		catch(e){}
	}
	else if(n.plugins)
	{
		for (i = 0, l = n.plugins.length; i < l; i++)
			if (n.plugins[i].name.indexOf('Flash') != -1)
				v = parseInt(n.plugins[i].description.substr(16, 2));
	}
	return v;
}

var bx_adv_includeFlash = function(container_id, flash_link, width, height, wmode, img_link, flashVer, altHref, target, title)
{
	var obCont = document.getElementById(container_id);

	if (flashVer == null)
		flashVer = 8;

	var obFrame = document.createElement('DIV');
	obFrame.style.height = parseInt(height) + 'px';
	obFrame.style.width = parseInt(width) + 'px';
	obFrame.style.border = '0px';
	
	obCont.appendChild(obFrame);

	if(bx_adv_getFlashVersion() >= flashVer)
	{
		obFrame.innerHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'
		    + 'width="' + width + '" height="' + height + '"'
		    + 'codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab">'
		    + '<param name="movie" value="' + flash_link + '" /><param name="quality" value="high" />'
		    + '<param name="bgcolor" value="#ffffff" />'
		    + '<param name="wmode" value="' + wmode + '" />'
		    + '<embed src="' + flash_link + '" quality="high" bgcolor="#ffffff" '
		    + 'width="' + width + '" height="' + height + '" name="banner" '
		    + 'play="true"'
		    + 'wmode="' + wmode + '"'
		    + 'quality="high"'
		    + 'type="application/x-shockwave-flash"'
		    + 'pluginspage="http://www.macromedia.com/go/getflashplayer">'
		    + '<\/embed>'
		    + '<\/object>';
	}
	else 
	{
		obFrame.innerHTML = (altHref != ''? '<a href="'+altHref+'" target="'+target+'" title="'+title+'">':'')
			+'<img src="' + img_link + '" width="' + width + '" heigh="' + height + '" alt="'+title+'" border="0" />'
			+(altHref != ''? '</a>':'');
	}
};