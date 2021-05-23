function get_more_covers(pLink)
{
	if (!pLink)
		return false;
	
	BX.ajax.post(
		pLink.href,
		{"AJAX_CALL" : "Y"},
		function(data)
		{
			// Handle thumbs
			var
				pThumbsCont = BX('photo-cover-images'),
				indBegin = data.indexOf('<!--#THUMBS_BEGIN#-->'),
				indEnd = data.indexOf('<!--#THUMBS_END#-->');

			if (pThumbsCont && indBegin !== -1 && indEnd !== -1)
			{
				var res = data.substr(indBegin + '<!--#THUMBS_BEGIN#-->'.length, indEnd - indBegin - '<!--#THUMBS_BEGIN#-->'.length);
				res = BX.util.trim(res);
				pThumbsCont.innerHTML += res;
			}
			// Handle navi
			var
				pNaviCont = BX('photo-cover-navigation'),
				indBegin = data.indexOf('<!--#NAVI_BEGIN#-->'),
				indEnd = data.indexOf('<!--#NAVI_END#-->');

			if (pNaviCont && indBegin !== -1 && indEnd !== -1)
			{
				var res = data.substr(indBegin + '<!--#NAVI_BEGIN#-->'.length, indEnd - indBegin - '<!--#NAVI_BEGIN#-->'.length);
				res = BX.util.trim(res);
				if (res == '')
					pNaviCont.parentNode.removeChild(pNaviCont);
				else
					pNaviCont.innerHTML = res;
			}
		}
	);
	return false;
}