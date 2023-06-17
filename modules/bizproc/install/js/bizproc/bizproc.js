if(!DragNDrop)
{


in_array = function (ar, str)
{
	var i;
	for(i=0; i<ar.length; i++)
		if(ar[i]===str)
			return true;
	return false;
}


function CreateActivity(oActivity)
{
	if(!oActivity.Type)
		oActivity = {'Type': oActivity};

	var t = oActivity.Type, a;
	if(arAllActivities[t.toLowerCase()] && arAllActivities[t.toLowerCase()]['JSCLASS'])
	{
		a = eval("new "+arAllActivities[t.toLowerCase()]['JSCLASS']+"()");
		if(!oActivity.Properties)
			oActivity.Properties = {};
		else if (oActivity.Properties instanceof Array)
		{
			var k, properties = BX.clone(oActivity.Properties);
			oActivity.Properties = {};
			for (k in properties)
				if (properties.hasOwnProperty(k))
					oActivity.Properties[k] = properties[k];
		}
		if(!oActivity.Properties['Title'])
			oActivity.Properties['Title'] = arAllActivities[t.toLowerCase()]['NAME'];
		if(!oActivity.Icon && arAllActivities[t.toLowerCase()]['ICON'])
			oActivity.Icon = arAllActivities[t.toLowerCase()]['ICON'];
	}
	else if (typeof window[t] !== 'undefined')
		a = eval("new " + t + "()");
	else
		a = new UnknownBizProcActivity();

	a.Init(oActivity);
	return a;
}

function JSToPHPHidd(v, ob, varname)
{
	if (typeof BPDesignerUseJson !== 'undefined'  && BPDesignerUseJson)
	{
		v[varname] = JSON.stringify(ob, function (i, v)
			{
				if (typeof(v) == 'boolean')
				{
					return v ? '1' : '0';
				}
				return v;
			});
		return true;
	}

	var res, i, key;
	if(typeof(ob)=='object')
	{
		res = [];
		var isSimpleArray = false;
		if(ob instanceof Array)
		{
			isSimpleArray = true;
			for(i in ob)
			{
				if(parseInt(i)!=i)
				{
					isSimpleArray = false;
					break;
				}
			}
		}

		if(isSimpleArray)
		{
			for(i=0; i<ob.length; i++)
				JSToPHPHidd(v, ob[i], varname+'['+i+']');
		}
		else
		{
			for(key in ob)
				JSToPHPHidd(v, ob[key], varname+'['+key+']');
		}
		return true;
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			v[varname] = "1";
		else
			v[varname] = "0";

		return true;
	}

	v[varname] = ob;
	return true;
}

function JSToPHP(ob, varname)
{
	if (typeof BPDesignerUseJson !== 'undefined'  && BPDesignerUseJson)
	{
		return varname + '=' + encodeURIComponent(JSON.stringify(ob, function (i, v)
			{
				if (typeof(v) == 'boolean')
				{
					return v ? '1' : '0';
				}
				return v;
			}));
	}
	var res, i, key;
	if(typeof(ob)=='object')
	{
		res = [];
		var isSimpleArray = false;
		if(ob instanceof Array)
		{
			isSimpleArray = true;
			for(i in ob)
			{
				if(parseInt(i)!=i)
				{
					isSimpleArray = false;
					break;
				}
			}
		}

		if(isSimpleArray)
		{
			for(i=0; i<ob.length; i++)
				res.push(JSToPHP(ob[i], varname+'['+i+']'));
		}
		else
		{
			for(key in ob)
				res.push(JSToPHP(ob[key], varname+'['+key+']'));
		}

		return res.join("&", res);
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			return varname + '=1';
		return varname + "=0";
	}

	return varname + '=' + encodeURIComponent(ob);
}

function ActGetRealPos(el)
{
	if(!el || !el.offsetParent)
		return false;

	return BX.pos(el, true);
}

function XMLEncode(str)
{
	if(!(typeof(str) == "string" || str instanceof String))
		return str;

	str = str.replace(/&/g, '&amp;');
	str = str.replace(/"/g, '&quot;');
	str = str.replace(/'/g, '&apos;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');

	return str;
}

function HTMLEncode(str)
{
	if(!(typeof(str) == "string" || str instanceof String))
		return str;

	str = str.replace(/&/g, '&amp;');
	str = str.replace(/"/g, '&quot;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');

	return str;
}

function GenUniqId()
{
	return parseInt(Math.random()*100000)+'_'+parseInt(Math.random()*100000)+'_'+parseInt(Math.random()*100000)+'_'+parseInt(Math.random()*100000);
}

function FindActivityById(template, id)
{
	if(template.Name == id)
		return template;

	var ar = false;
	if(template.Children)
	{
		for(var i=0; i<template.Children.length; i++)
		{
			ar = FindActivityById(template.Children[i], id);
			if(ar)
				return ar;
		}
	}
	return ar;
}


function _crt(r, c)
{
	r = r || 1;
	c = c || 1;
	var i, j, row, cell, t = document.createElement('TABLE');
	t.width = '100%';
	t.cellSpacing = '0';
	t.cellPadding = '0';
	t.border = '0';
	for (i = 0; i < r; i++)
	{
		row = t.insertRow(-1);
		for (j = 0; j < c; j++)
		{
			cell = row.insertCell(-1);
			cell.align = 'center';
			cell.vAlign = 'center';
		}
	}
	return t;
}

///////////////////////////////////////////////////////////////////////////////////////
// BizProcActivity
///////////////////////////////////////////////////////////////////////////////////////

BizProcActivity = function()
{
	var ob = this;
	ob.childActivities = [];
	ob.parentActivity = null;
	ob.Name = 'A'+ GenUniqId();
	ob.Type = 'Activity';
	ob.Properties = {'Title': ''};

	arAllId[ob.Name] = true;

	this.Init = function(oActivity)
	{
		if(oActivity.Name)
		{
			if(!arAllId[oActivity.Name])
			{
				delete arAllId[this.Name];
				this.Name = oActivity.Name;
				arAllId[this.Name] = true;
			}
		}

		if(oActivity['Properties'])
			this.Properties = BX.clone(oActivity['Properties']);

		if(oActivity['Icon'])
			this.Icon = oActivity['Icon'];

		if(oActivity.Type)
			this.Type = oActivity.Type;

		this.height = 0;
		this.width = 0;

		var activity;
		this.childActivities = [];

		if(!oActivity.Children && oActivity.childActivities)
			oActivity.Children = oActivity.childActivities;

		for(var i in oActivity.Children)
		{
			if (!oActivity.Children.hasOwnProperty(i))
				continue;

			activity = CreateActivity(oActivity.Children[i]);
			activity.parentActivity = this;
			this.childActivities[this.childActivities.length] = activity;
		}
	};


	ob.SerializeToXML = function (e)
	{
		if(ob.childActivities)
		{
			var s = '<activity class="'+XMLEncode(ob.Type)+'" name="'+XMLEncode(ob['Properties'].Title)+'" id="'+XMLEncode(ob.Name)+'" params="" >';
			for(var i = 0; i < ob.childActivities.length; i++)
				s = s + ob.childActivities[i].SerializeToXML();
			return s + '</activity>';
		}
		else
			return '<activity class="'+XMLEncode(ob.Type)+'" name="'+XMLEncode(ob['Properties'].Title)+'" id="'+XMLEncode(ob.Name)+'" params="" />';
	};

	ob.Serialize = function ()
	{
		var s = {'Type': ob.Type, 'Name': ob.Name, 'Properties': ob.Properties, 'Children': []};

		if(ob.childActivities)
		{
			for(var i = 0; i < ob.childActivities.length; i++)
				s['Children'].push(ob.childActivities[i].Serialize());
		}
		return s;
	};

	ob.OnRemoveClick = function (e)
	{
		ob.parentActivity.RemoveChild(ob);
	};

	ob.OnSettingsClick = function (e)
	{
		ob.Settings();
	};

	ob.Settings = function (e)
	{
		var contentUrl = "/bitrix/admin/"+MODULE_ID+"_bizproc_activity_settings.php?mode=public&bxpublic=Y&lang="+BX.message('LANGUAGE_ID')+"&entity="+ENTITY;
		if (window.document_type_signed)
		{
			contentUrl ="/bitrix/tools/bizproc_activity_settings.php?mode=public&bxpublic=Y&lang="+BX.message('LANGUAGE_ID')+"&dts="+window.document_type_signed;
		}

		(new BX.CDialog({
			'content_url': contentUrl,
			'content_post': 'id='+encodeURIComponent(ob.Name)+ '&' +
				'decode=Y&' +
				'document_type=' + encodeURIComponent(document_type) + '&' +
				'activity='+encodeURIComponent(ob.Type)+ '&' +
				JSToPHP(arWorkflowParameters, 'arWorkflowParameters')  + '&' +
				JSToPHP(arWorkflowVariables, 'arWorkflowVariables')  + '&' +
				JSToPHP(Array(rootActivity.Serialize()), 'arWorkflowTemplate') + '&' +
				JSToPHP(arWorkflowConstants, 'arWorkflowConstants') + '&' +
				'current_site_id=' + encodeURIComponent(CURRENT_SITE_ID) + '&' +
				'sessid=' + BX.bitrix_sessid(),
			'height': 500,
			'width': 900
			})).Show();
	};

	ob.RemoveResources = function (self)
	{
		if(ob.div && ob.div.parentNode)
		{
			ob.div.parentNode.removeChild(ob.div);
			ob.div = null;
		}
	};

	ob.RemoveChild = function (ch)
	{
		var i, j;

		for(i = 0; i<ob.childActivities.length; i++)
		{
			if(ob.childActivities[i].Name == ch.Name)
			{
				while(ch.childActivities.length > 0)
				{
					ch.childActivities[0].parentActivity.RemoveChild(ch.childActivities[0]);
				}

				ch.childActivities = [];

				ch.RemoveResources();

				ob.childActivities[i].parentActivity = null;
				delete ob.childActivities[i];

				for(j = i; j<ob.childActivities.length - 1; j++)
					ob.childActivities[j] = ob.childActivities[j+1];

				ob.childActivities.pop();

				delete arAllId[ch.Name];

				break;
			}
		}
		BPTemplateIsModified = true;
	};

	ob.SetError = function (s, setFocus)
	{
		if (!ob.div)
		{
			return false;
		}

		if(s===false)
			ob.div.className = 'activity activity-modern';
		else
			ob.div.className = 'activityerr activity-modern';

		if (setFocus === true && s !== false)
		{
			BX.scrollToNode(ob.div);
		}
	};

	ob.Draw = function (divC)
	{
		ob.div = divC.appendChild(document.createElement('DIV'));
		ob.div.className = (ob.isUnknown ? 'activityerr' : 'activity') + ' activity-modern';
		var d1 = ob.div.appendChild(document.createElement('DIV'));
		d1.className = 'activityhead';

		var a1 = d1.appendChild(document.createElement('A'));
		a1.className = 'activitydel';

		a1.onclick = this.OnRemoveClick;// this!

		if (!ob.isUnknown)
		{
			var a2 = d1.appendChild(document.createElement('A'));
			a2.className = 'activityset';
			a2.onclick = this.OnSettingsClick;// this!

			if (this.OnHideClick)
			{
				var a3 = d1.appendChild(document.createElement('A'));
				a3.className = 'activitymin';
				a3.onclick = this.OnHideClick;// this!
			}
		}

		var sp = d1.appendChild(document.createElement('DIV'));
		sp.style.padding = '9px';
		if (ob.isUnknown)
		{
			sp.style.cursor = 'not-allowed';
		}
		else
		{
			sp.style.cursor = 'move';
			sp.onmousedown = function (e)
			{
				if(!e)
					e = window.event;

				var div = DragNDrop.StartDrag(e, ob);

				div.innerHTML = this.parentNode.parentNode.parentNode.innerHTML;
				div.style.width = this.parentNode.parentNode.offsetWidth + 'px';
			}
		}

		var d2 = ob.div.appendChild(document.createElement('DIV'));
		d2.style.backgroundColor = ob.isUnknown ? '#E6E6E6' : '#ffffff';
		d2.style.overflowX = 'hidden';
		d2.style.overflowY = 'hidden';
		d2.style.height = (ob.activityHeight ? ob.activityHeight : '30px');

		d2.ondblclick = ob.OnSettingsClick;

		if(ob.activityContent)
		{
			d2.appendChild(ob.activityContent);
		}
		else
		{
			var act = d2.appendChild(document.createElement('DIV'));
			if(ob.Icon)
				act.style.background = 'url('+ob.Icon+') left center no-repeat';
			else
				act.style.background = 'url(/bitrix/images/bizproc/act_icon.gif) left center no-repeat';
			act.style.height = '30px';
			act.style.margin = '2px';
			act.style.paddingLeft = '24px';
			act.style.textAlign = 'left';
			act.innerHTML = HTMLEncode(ob['Properties']['Title']);
			act.setAttribute('title', ob['Properties']['Title']);
		}

		ob.div.style.margin = '0 auto';
		ob.div.style.width = (ob.activityWidth ? ob.activityWidth : '170px');

		if(ob.CheckFields && ob.CheckFields()===false)
			ob.SetError(true);

		this.drawEditorComment();
	};

	this.SetHeight = function (iHeight)
	{
		this.height = iHeight;
	};

	this.drawEditorComment = function(container)
	{
		if (!container)
		{
			container = this.div;
		}
		if (
			!container
			|| !BX.getClass('BX.UI.Hint')
			|| !ob['Properties']['EditorComment']
			|| ob['Properties']['EditorComment'].length <= 0
		)
		{
			return false;
		}

		var commentNode = container.appendChild(document.createElement('SPAN'));
		commentNode.className = 'activity-comment';
		commentNode.setAttribute('data-hint',
			BX.util.nl2br(BX.util.htmlspecialchars(ob['Properties']['EditorComment']))
		);
		commentNode.setAttribute('data-hint-html', 'y');

		BX.UI.Hint.init(this.div);
	}

	ob.findChildById = function (id)
	{
		if(ob.childActivities)
		{
			for(var i = 0; i < ob.childActivities.length; i++)
			{
				if (id === ob.childActivities[i]['Name'])
				{
					return ob.childActivities[i];
				}
				else
				{
					var found = ob.childActivities[i].findChildById(id);
					if (found)
					{
						return found;
					}
				}
			}
		}
		return null;
	}
};

function _DragNDrop()
{
	var ob = this;
	var drdrop, antiselect;
	var dragging = true;

	ob.GetDrDr = function()
	{
		if(ob.drdrop)
			return;

		ob.drdrop = document.body.appendChild(document.createElement('DIV'));
		ob.drdrop.style.display = 'none';
		ob.drdrop.style.position = 'absolute';
		ob.drdrop.style.zIndex = '50000';
		ob.drdrop.style.MozOpacity = 0.60;
		ob.drdrop.style.opacity = 0.60;
		ob.drdrop.style.filter = 'gray() alpha(opacity=60)';
		ob.drdrop.style.border = '1px solid #CCCCCC';
		ob.drdrop.style.fontSize = '12px';


		ob.antiselect = document.body.appendChild(document.createElement('DIV'));
		ob.antiselect.id = "antiselect";
		//ob.antiselect.style.height = '100%';
		//ob.antiselect.style.width = '100%';
		ob.antiselect.style.left = '0';
		ob.antiselect.style.top = '0';
		ob.antiselect.style.position = 'absolute';
		ob.antiselect.style.MozUserSelect = 'none !important';
		ob.antiselect.style.display = 'none';
		ob.antiselect.style.backgroundColor = '#FFFFFF';
		ob.antiselect.style.MozOpacity = 0.01;
		ob.antiselect.style.zIndex = '100000';

		jsUtils.addEvent(document.body, "mousemove", ob.Dragging);
		jsUtils.addEvent(document.body, "mouseup", ob.Drop);
	}


	ob.obj = null;
	ob.StartDrag = function (e, obj)
	{
		ob.obj = obj;
		ob.GetDrDr();

		if(!e)
			e = window.event;

		ob.antiselect.style.display = 'block';

	 	var windowSize = jsUtils.GetWindowScrollSize();
		ob.antiselect.style.width = windowSize.scrollWidth + "px";
		ob.antiselect.style.height = windowSize.scrollHeight + "px";
		ob.antiselect.style.opacity = 0.01;
		ob.antiselect.style.filter = 'gray() alpha(opacity=01)';

		ob.dragging = true;

		ob.drdrop.style.display = 'block';

	 	ob.scrollPos = jsUtils.GetWindowScrollPos();
		ob.drdrop.style.top = e.clientY + ob.scrollPos.scrollTop + 1 +'px';
		ob.drdrop.style.left = e.clientX + ob.scrollPos.scrollLeft + 1 + 'px';

		return ob.drdrop;
	}

	ob.Handlers = {};

	ob.AddHandler = function (eventName, func)
	{
		ob.Handlers[eventName] = ob.Handlers[eventName] || [];

		var i = 'i' + Math.random();
		ob.Handlers[eventName][i] = func;
		return i;
	}

	ob.RemoveHandler = function (eventName, i)
	{
		if(ob.Handlers[eventName][i])
			delete ob.Handlers[eventName][i];
	}

	ob.Dragging = function (e)
	{
		if(!ob.dragging)
			return;

		if(!e)
			e = window.event;

		BX.fixEventPageXY(e);
		var X = e.pageX;
		var Y = e.pageY;

		ob.drdrop.style.left = X + 1 + 'px';
		ob.drdrop.style.top = Y + 1 + 'px';

	 	var scrollSize = BX.GetWindowInnerSize();
	 	var scrollPos = BX.GetWindowScrollPos();

	 	if((scrollSize.innerHeight - 30) < e.clientY)
	 		window.scrollBy(0, 20);

	 	if((scrollSize.innerWidth - 30) < e.clientX)
	 		window.scrollBy(20, 0);

	 	if(scrollPos.scrollTop>0 && e.clientY<30)
	 		window.scrollBy(0, -20);

	 	if(scrollPos.scrollLeft>0 && e.clientX<30)
	 		window.scrollBy(-20, 0);

		if(document.selection && document.selection.empty)
			document.selection.empty();
		else
			window.getSelection().removeAllRanges();

		for(var i in ob.Handlers['ondragging'])
		{
			if (!ob.Handlers['ondragging'].hasOwnProperty(i))
				continue;

			if (ob.Handlers['ondragging'][i])
				ob.Handlers['ondragging'][i](e, X, Y);
		}
	}

	ob._UnS = function ()
	{
		if(ob.antiselect)
			ob.antiselect.style.display = 'none';
	}

	ob.Drop = function (e)
	{
		if(!ob.dragging)
			return;

		if(!e)
			e = window.event;

	 	var scrollPos = jsUtils.GetWindowScrollPos();

		var X = e.clientX + scrollPos.scrollLeft + 1 + 'px';
		var Y = e.clientY + scrollPos.scrollTop + 1 +'px';

		for(var i in ob.Handlers['ondrop'])
		{
			if (!ob.Handlers['ondrop'].hasOwnProperty(i))
				continue;
			if (ob.Handlers['ondrop'][i])
				ob.Handlers['ondrop'][i](X, Y, e);
		}
		ob.dragging = false;

		ob.drdrop.style.display = 'none';

		setTimeout(ob._UnS, 0);
	}

}

UnknownBizProcActivity = function()
{
	var ob = new BizProcActivity();
	ob.isUnknown = true;
	return ob;
};

BX.namespace('BX.Bizproc');
BX.Bizproc.cloneTypeControl = function(tableID)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt);
	var oCell = oRow.insertCell(0);
	var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
	var p = 0, s, e, n;
	while (true)
	{
		s = sHTML.indexOf('[n', p);
		if (s < 0)
			break;
		e = sHTML.indexOf(']', s);
		if (e < 0)
			break;
		n = parseInt(sHTML.substr(s + 2, e - s));
		sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
		p = s + 1;
	}
	p = 0;
	while (true)
	{
		s = sHTML.indexOf('__n', p);
		if (s < 0)
			break;
		e = sHTML.indexOf('_', s + 2);
		if (e < 0)
			break;
		n = parseInt(sHTML.substr(s + 3, e - s));
		sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
		p = e + 1;
	}
	oCell.innerHTML = sHTML;
	var pattern = new RegExp('<' + 'script' + '>[^\000]*?<' + '\/' + 'script' + '>', 'ig');
	var code = sHTML.match(pattern);
	if (code)
	{
		for (var i = 0; i < code.length; i++)
		{
			if (code[i] != '')
			{
				s = code[i].substring(8, code[i].length - 9);
				jsUtils.EvalGlobal(s);
			}
		}
	}
};

BX.Bizproc.cloneTypeControlHtml = function(tableID, wrapperId)
{
	var tbl = document.getElementById(tableID);
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt);
	var oCell = oRow.insertCell(0);
	var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
	var p = 0, s, e, n = 0;
	s = sHTML.indexOf('[n', p);
	if (s > -1)
	{
		e = sHTML.indexOf(']', s);
		if (e > -1)
		{
			n = parseInt(sHTML.substr(s + 2, e - s));
			++n;
		}
	}

	BX.ajax({
		method: 'GET',
		dataType: 'html',
		url: '/bitrix/tools/bizproc_get_html_editor.php?site_id='
			+BX.message('SITE_ID')+'&editor_id='+ wrapperId+'__n'
			+n+'_&field_name='+wrapperId+'[n'+n+']',
		onsuccess: function (HTML)
		{
			oCell.innerHTML = HTML;
		}
	});
};

var DragNDrop = new _DragNDrop();
}