function __photo_change_template(anchor, package_id)
{
	var item_value = anchor.href.match(/template\=(\w+)/gi);
	if (!item_value) { return false; }
	item_value = item_value[0].replace("template=", ""); 
	if (window['__photo_buffer']) {
		for (var ii in window['__photo_buffer'])
		{
			if (ii.indexOf('sight') >= 0) {	window['__photo_buffer'][ii] = false; }
		}
	}
	__photo_change_template_data('template', item_value, package_id, {'template' : item_value});
	
	var nodes = anchor.parentNode.parentNode.getElementsByTagName('li');
	for (var ii = 0; ii < nodes.length; ii++)
	{
		nodes[ii].className = nodes[ii].className.replace(/\s[a-z\-]+/gi, '');
	}
	anchor.parentNode.className += ' ' + anchor.parentNode.className + '-active';
}

function __photo_change_template_data(item_name, item_value, package_id, params)
{
	if (!window['__photo_buffer']) { window['__photo_buffer'] = {}; }
	if (window['__photo_buffer'][item_name + item_value + package_id])
	{
		var div = document.getElementById("photo_list_" + package_id);
		div.innerHTML = window['__photo_buffer'][item_name + item_value + package_id];
		if (window.__photo_to_init_slider)
		{
			__photo_to_init_slider();
		}
		if (null != jsUserOptions)
		{
			if(!jsUserOptions.options)
				jsUserOptions.options = new Object();
			jsUserOptions.options['photogallery.template.' + item_name] = ['photogallery', 'template', item_name, item_value, false];
			jsUserOptions.SendData(null);
		}
		return true;
	}

	var TID = jsAjax.InitThread();
	eval("jsAjax.AddAction(TID, function(data){" + 
		"try { " + 
			"jsAjaxUtil.CloseLocalWaitWindow(TID, 'photo_list_" + package_id + "'); " + 
			"var index1 = data.indexOf('<!-- Photo List " + package_id + " -->'); " + 
			"var index2 = data.indexOf('<!-- Photo List End " + package_id + " -->'); " + 
			"var div = document.getElementById('photo_list_" + package_id + "'); " + 
			"if (index1 >= 0 && index2 >= 0 && div) {" + 
				" window['__photo_buffer']['" + item_name + item_value + package_id + "'] = div.innerHTML = data.substring(index1, index2); " + 
				" if (window.__photo_to_init_slider) { " +
					" __photo_to_init_slider(); " + 
				" }" + 
			"} " + 
		"} catch (e) {alert(e.message);}});");
	var url = window.location.href.replace(/PICTURES\_SIGHT\=(\w+)/gi, '').replace(/\#(.*)/gi, '').replace(/template\=(\w+)/gi, ''); 
	params = (params ? params : {});
	params['package_id'] = package_id;
	params['sessid'] = phpVars.bitrix_sessid;
	jsAjaxUtil.ShowLocalWaitWindow(TID, 'photo_list_' + package_id, true); 
	jsAjax.Send(TID, url, params);
}