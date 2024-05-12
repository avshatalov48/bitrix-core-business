if(!DragNDrop)
{


in_array = function (ar, str)
{
	for(let i = 0; i < ar.length; i++)
	{
		if (ar[i] === str)
		{
			return true;
		}
	}

	return false;
}


function CreateActivity(activityInfo)
{
	if(!activityInfo.Type)
	{
		activityInfo = {'Type': activityInfo};
	}

	const type = activityInfo.Type;
	let a;

	if(arAllActivities[type.toLowerCase()] && arAllActivities[type.toLowerCase()]['JSCLASS'])
	{
		a = eval("new " + arAllActivities[type.toLowerCase()]['JSCLASS'] + "()");
		if(!activityInfo.Properties)
		{
			activityInfo.Properties = {};
		}
		else if (activityInfo.Properties instanceof Array)
		{
			let k;
			const properties = BX.clone(activityInfo.Properties);
			activityInfo.Properties = {};
			for (k in properties)
			{
				if (properties.hasOwnProperty(k))
				{
					activityInfo.Properties[k] = properties[k];
				}
			}
		}
		if(!activityInfo.Properties['Title'])
		{
			activityInfo.Properties['Title'] = arAllActivities[type.toLowerCase()]['NAME'];
		}
		if(!activityInfo.Icon && arAllActivities[type.toLowerCase()]['ICON'])
		{
			activityInfo.Icon = arAllActivities[type.toLowerCase()]['ICON'];
		}
	}
	else if (!BX.Type.isUndefined(window[type]))
	{
		a = eval("new " + type + "()");
	}
	else
	{
		a = new UnknownBizProcActivity();
	}

	a.Init(activityInfo);

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
	{
		return false;
	}

	return BX.pos(el, true);
}

function XMLEncode(str)
{
	if(!(typeof(str) == "string" || str instanceof String))
	{
		return str;
	}

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
	{
		return str;
	}

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
		const rowsCount = r || 1;
		const columnCount = c || 1;

		const table = BX.Dom.create('table', {
			attrs: {
				width: '100%',
				cellSpacing: '0',
				cellPadding: '0',
				border: '0',
			},
		});
		for (let i = 0; i < rowsCount; i++)
		{
			const row = table.insertRow(-1);
			for (let j = 0; j < columnCount; j++)
			{
				const cell = row.insertCell(-1);
				BX.Dom.attr(cell, {
					align: 'center',
					vAlign: 'center',
				});
			}
		}

		return table;
	}

///////////////////////////////////////////////////////////////////////////////////////
// BizProcActivity
///////////////////////////////////////////////////////////////////////////////////////

BizProcActivity = function()
{
	const ob = this;
	ob.childActivities = [];
	ob.parentActivity = null;
	ob.Name = 'A' + GenUniqId();
	ob.Type = 'Activity';
	ob.Properties = {'Title': ''};
	ob.Activated = 'Y';

	ob.canBeActivated = true;

	arAllId[ob.Name] = true;

	this.Init = function(activityInfo)
	{
		if(activityInfo.Name)
		{
			if(!arAllId[activityInfo.Name])
			{
				delete arAllId[this.Name];
				this.Name = activityInfo.Name;
				arAllId[this.Name] = true;
			}
		}

		if(activityInfo['Properties'])
		{
			this.Properties = BX.Runtime.clone(activityInfo['Properties']);
		}

		if(activityInfo['Icon'])
		{
			this.Icon = activityInfo['Icon'];
		}

		if(activityInfo.Type)
		{
			this.Type = activityInfo.Type;
		}

		this.Activated = activityInfo.Activated === 'N' ? 'N' : 'Y';

		this.height = 0;
		this.width = 0;

		let activity;
		this.childActivities = [];

		if(!activityInfo.Children && activityInfo.childActivities)
		{
			activityInfo.Children = activityInfo.childActivities;
		}

		const canBeActivatedChild = this.getCanBeActivatedChild();
		for(const i in activityInfo.Children)
		{
			if (!activityInfo.Children.hasOwnProperty(i))
			{
				continue;
			}

			activity = CreateActivity(activityInfo.Children[i]);
			activity.parentActivity = this;
			this.childActivities[this.childActivities.length] = activity;

			if (!canBeActivatedChild)
			{
				activity.setCanBeActivated(false);
			}
		}
	};

	ob.SerializeToXML = function ()
	{
		if(ob.childActivities)
		{
			let s =
				'<activity class="'
				+ XMLEncode(ob.Type)
				+ '" name="'
				+ XMLEncode(ob['Properties'].Title)
				+ '" id="'
				+ XMLEncode(ob.Name)
				+ '" params="" >'
			;
			for(let i = 0; i < ob.childActivities.length; i++)
			{
				s = s + ob.childActivities[i].SerializeToXML();
			}

			return s + '</activity>';
		}

		return (
			'<activity class="'
			+ XMLEncode(ob.Type)
			+ '" name="'
			+ XMLEncode(ob['Properties'].Title)
			+ '" id="'
			+ XMLEncode(ob.Name)
			+ '" params="" />'
		);
	};

	ob.Serialize = function ()
	{
		const property = {
			Type: ob.Type,
			Name: ob.Name,
			Activated: ob.Activated,
			Properties: ob.Properties,
			Children: [],
		};

		if(ob.childActivities)
		{
			for(let i = 0; i < ob.childActivities.length; i++)
			{
				property['Children'].push(ob.childActivities[i].Serialize());
			}
		}

		return property;
	};

	ob.OnRemoveClick = function ()
	{
		ob.parentActivity.RemoveChild(ob);
	};

	ob.OnSettingsClick = function ()
	{
		document.location.hash = ob.Name;
		ob.Settings();
	};

	ob.Settings = function ()
	{
		let contentUrl =
			"/bitrix/admin/"
			+ MODULE_ID
			+ "_bizproc_activity_settings.php?mode=public&bxpublic=Y&lang="
			+ BX.message('LANGUAGE_ID')
			+ "&entity="
			+ ENTITY
		;
		if (window.document_type_signed)
		{
			contentUrl =
				"/bitrix/tools/bizproc_activity_settings.php?mode=public&bxpublic=Y&lang="
				+ BX.message('LANGUAGE_ID')
				+ "&dts="
				+ window.document_type_signed
			;
		}

		const id = 'id=' + encodeURIComponent(ob.Name);
		const documentType = 'document_type=' + encodeURIComponent(document_type);
		const activity = 'activity='+encodeURIComponent(ob.Type);
		const parameters = JSToPHP(arWorkflowParameters, 'arWorkflowParameters');
		const variables = JSToPHP(arWorkflowVariables, 'arWorkflowVariables');
		const template = JSToPHP(Array(rootActivity.Serialize()), 'arWorkflowTemplate');
		const constants = JSToPHP(arWorkflowConstants, 'arWorkflowConstants');
		const currentSiteId = 'current_site_id=' + encodeURIComponent(CURRENT_SITE_ID);
		const sessid = 'sessid=' + BX.bitrix_sessid();

		const canBeActivated = 'can_be_activated=' + (ob.canBeActivated ? 'Y' : 'N');

		const postData = [
			id,
			'decode=Y',
			documentType,
			activity,
			parameters,
			variables,
			template,
			constants,
			currentSiteId,
			canBeActivated,
			sessid,
		];

		(new BX.CDialog({
			'content_url': contentUrl,
			'content_post': postData.join('&'),
			'height': 500,
			'width': 900
			})
		).Show();
	};

	ob.RemoveResources = function ()
	{
		if(ob.div && ob.div.parentNode)
		{
			BX.Dom.remove(ob.div);
			ob.div = null;
		}
	};

	ob.RemoveChild = function (ch)
	{
		let i, j;

		for(i = 0; i < ob.childActivities.length; i++)
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
				{
					ob.childActivities[j] = ob.childActivities[j + 1];
				}

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

		if(s === false)
		{
			ob.div.className = 'activity activity-modern';
		}
		else
		{
			ob.div.className = 'activityerr activity-modern';
		}

		if (setFocus === true && s !== false)
		{
			BX.scrollToNode(ob.div);
		}
	};

	ob.focusAndBlink = function()
	{
		if (!ob.div)
		{
			return false;
		}
		BX.scrollToNode(ob.div);
		BX.Dom.addClass(ob.div, 'activity-modern-blink');
		setTimeout(() => BX.Dom.removeClass(ob.div, 'activity-modern-blink'), 1500);
	};

	ob.Draw = function (wrapper)
	{
		ob.div = BX.Dom.create('div', {
			attrs: {
				className: (ob.isUnknown ? 'activityerr' : 'activity') + ' activity-modern',
			},
			style: {margin: '0 auto', width: (ob.activityWidth ? ob.activityWidth : '170px')}
		});
		BX.Dom.append(ob.div, wrapper);

		const activityHead = BX.Dom.create('div', {
			attrs: {
				className: 'activityhead' + ((ob.Activated === 'N' || !ob.canBeActivated) ? ' --deactivated' : '')
			},
			children: [
				BX.Dom.create('a', {
					attrs: {
						className: 'ui-icon-set --cross-45 activitydel'
					},
					events: {
						click: this.OnRemoveClick, // this!
					}
				}),
			],
		});
		BX.Dom.append(activityHead, ob.div);

		if (!ob.isUnknown)
		{
			const activitySettings = BX.Dom.create('a', {
				attrs: {
					className: 'ui-icon-set --settings-2 activityset'
				},
				events: {
					click: this.OnSettingsClick, // this!
				}
			});
			BX.Dom.append(activitySettings, activityHead);

			if (this.OnHideClick)
			{
				const activityMin = BX.Dom.create('a', {
					attrs: {
						className: 'ui-icon-set --line activitymin',
					},
					events: {
						click: this.OnHideClick, // this!
					}
				});
				BX.Dom.append(activityMin, activityHead);
			}
		}

		const sp = BX.Dom.create('div', {
			style: {padding: '9px', cursor: (ob.isUnknown ? 'not-allowed' : 'move')}
		});
		BX.Dom.append(sp, activityHead);

		if (!ob.isUnknown)
		{
			sp.onmousedown = function (e)
			{
				if(!e)
				{
					e = window.event;
				}

				const div = DragNDrop.StartDrag(e, ob);
				div.innerHTML = this.parentNode.parentNode.parentNode.innerHTML;
				BX.Dom.style(div, 'width', this.parentNode.parentNode.offsetWidth + 'px');
			}
		}

		const activityBody = BX.Dom.create('div', {
			style: {
				backgroundColor: ob.isUnknown ? '#E6E6E6' : '#ffffff',
				overflowX: 'hidden',
				overflowY: 'hidden',
				height: (ob.activityHeight ? ob.activityHeight : '30px'),
				'border-bottom-left-radius': 'var(--ui-border-radius-xs)',
				'border-bottom-right-radius': 'var(--ui-border-radius-xs)',
			},
		});
		activityBody.ondblclick = ob.OnSettingsClick;
		BX.Dom.append(activityBody, ob.div);

		if(ob.activityContent)
		{
			BX.Dom.append(ob.activityContent, activityBody);
		}
		else
		{
			const act = BX.Dom.create('div', {
				html: HTMLEncode(ob['Properties']['Title']),
				attrs: {
					title: BX.Text.encode(ob['Properties']['Title']),
				},
				style: {
					background:
						ob.Icon
							? 'url('+ob.Icon+') left center no-repeat'
							: 'url(/bitrix/images/bizproc/act_icon.gif) left center no-repeat'
					,
					height: '30px',
					margin: '2px',
					paddingLeft: '24px',
					textAlign: 'left',
				}
			});
			BX.Dom.append(act, activityBody);
		}

		if(ob.CheckFields && ob.CheckFields() === false)
		{
			ob.SetError(true);
		}

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

		const commentNode = BX.Dom.create('span', {
			props: {
				className: 'activity-comment',
			},
			dataset: {
				hint: BX.Text.encode(ob['Properties']['EditorComment']).replace(/([^>])\n/g, '$1<br/>'),
			}
		});
		commentNode.setAttribute('data-hint-html', 'y');
		BX.Dom.append(commentNode, container);
		BX.UI.Hint.init(this.div);
	};

	ob.setActivated = function(activated)
	{
		if ((['Y', 'N'].includes(activated)))
		{
			ob.Activated = activated;
			ob.childActivities.forEach((child) => child.setCanBeActivated(ob.getCanBeActivatedChild()));
		}
	}

	ob.setCanBeActivated = function (can)
	{
		if (BX.Type.isBoolean(can))
		{
			ob.canBeActivated = can;
			if (ob.childActivities.length > 0)
			{
				ob.childActivities.forEach((activity) => activity.setCanBeActivated(ob.getCanBeActivatedChild()));
			}
		}
	};

	ob.getCanBeActivatedChild = function ()
	{
		return ob.canBeActivated && ob.Activated === 'Y';
	}

	ob.findChildById = function (id)
	{
		if(ob.childActivities)
		{
			for(let i = 0; i < ob.childActivities.length; i++)
			{
				if (id === ob.childActivities[i]['Name'])
				{
					return ob.childActivities[i];
				}
				else
				{
					const found = ob.childActivities[i].findChildById(id);
					if (found)
					{
						return found;
					}
				}
			}
		}

		return null;
	};
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
