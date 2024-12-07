/*
if(!Array.prototype.push)
{
    Array.prototype.push = function(elem)
    {
        this[this.length] = elem;
    }
}
*/

function BXSearchInd(ar, wf)
{
	for(var i=0; i<ar.length; i++)
		if(ar[i] == wf)
			return i;
	return -1;
}

if(!String.prototype.trim)
{
    String.prototype.trim = function()
    {
	   var r, re;
	   re = /^[ ]+/g;
	   r = this.replace(re, "");
	   re = /[ ]+$/g;
	   r = r.replace(re, "");
	   return r;
    }
}

function BXCreateElement(sTagname, arParams, arStyles, pDocument)
{
	//alert(this.pDocument.uniqueID);
	var pEl = (pDocument? pDocument.createElement(sTagname): this.pDocument.createElement(sTagname));
	var sParamName;

	if(arParams)
	{
		for(sParamName in arParams)
		{
			if(sParamName.substring(0, 1)=='_' && sParamName!='__exp')
				pEl.setAttribute(sParamName, arParams[sParamName]);
			else
				pEl[sParamName] = arParams[sParamName];
		}
	}

	if(arStyles)
	{
		for(sParamName in arStyles)
			pEl["style"][sParamName] = arStyles[sParamName];
	}
	return pEl;
}

function GAttr(pElement, attr)
{
	if(attr=='className' && !BXIsIE())
		attr = 'class';
	var v = pElement.getAttribute(attr, 2);
	if(v && v!='-1')
		return v;
	return "";
}

function SAttr(pElement, attr, val)
{
	if(attr=='className' && !BXIsIE())
	{
		attr = 'class';
	}
	if(val.length<=0)
		pElement.removeAttribute(attr);
	else
		pElement.setAttribute(attr, val);
}


function _BXPopupWindow()
{
	_BXPopupWindow.prototype.Create = function()
	{
		if(this.pFrame)
			return;
		var obj = this;

		if(window.createPopup)
		{
			this.pFrame = window.createPopup() ;
			this.pDocument = this.pFrame.document ;
			this.pDocument.oncontextmenu = function() { return false ; }

			this.pDocument.open();
			this.pDocument.write('<html><head></head><body><table border="0" cellpadding="0" cellspacing="0"><tr><td id=md></td></tr></table></body></html>');
			this.pDocument.close();
		}
		else
		{
			this.pFrame = document.createElement("IFRAME");
			this.pFrame.setAttribute("src", "");
			this.pFrame.className = "bxedpopupframe";
		    this.pFrame.frameBorder	= "0";
		    this.pFrame.scrolling = "no";
			this.pFrame.style.position = "absolute";
			this.pFrame.style.left = 0;
			this.pFrame.style.zIndex = "9999";
			this.pFrame.style.top = 0;
			this.pFrame.unselectable = "on";
			//this.pFrame.style.display = "none";
			this.pFrame.width = 0;
			this.pFrame.height = 0;
			this.pFrame = document.body.appendChild(this.pFrame);

			this.pDocument = this.pFrame.contentWindow.document;

			this.pDocument.open();
			this.pDocument.write('<html><head></head><body><table border="0" cellpadding="0" cellspacing="0"><tr><td id=md></td></tr></table></body></html>');
			this.pDocument.close();

			this.pFrame.contentWindow.onblur = function (){obj.Hide()};
		}
		//pBXEventDispatcher.AddHandler('mousedown', function(e){obj.Hide(e);});

		this.pDocument.body.style.margin = this.pDocument.body.style.padding = "0px";
		this.pDocument.body.style.border = "0px";
		this.pDocument.body.style.backgroundColor = "#FFFFFF";
		this.pDocument.body.style.overflow = "hidden";

		this.pDiv = this.pDocument.getElementById("md");
		//this.pDiv.style.width="1%";
		//this.pDiv.style.height="100%";
		//this.pDiv.innerHTML = 'S@S#@#S@#S@#';
	}

	_BXPopupWindow.prototype.Hide = function()
	{
		//return;
		if(!this.bShowed)
			return;

		if(window.createPopup)
			this.pFrame.hide();
		else
		{
			//this.pFrame.style.display = "none";
			this.pFrame.width = "0";
			this.pFrame.height = "0";
		}
		this.bShowed = false;
		//alert('Hide');
	}

	_BXPopupWindow.prototype.GetDocument = function()
	{
		if(!this.pFrame)
			this.Create();

		return this.pDocument;
	}

	_BXPopupWindow.prototype.Show = function (px, py, pNode)
	{
		if(!this.pFrame)
			this.Create();

		while(this.pDiv.childNodes.length>0)
			this.pDiv.removeChild(this.pDiv.childNodes[0]);

		this.pDiv.appendChild(pNode);

		if(window.createPopup)
			this.pFrame.show(0, 0, 0, 0, document.body);
		else
		{
			this.pFrame.style.left = "1px";
			this.pFrame.style.top = "1px";
			this.pFrame.width = "300px";
			this.pFrame.height = "1px";
			//this.pFrame.style.display = "block";
		}

		var dx = this.pDiv.offsetWidth, dy = this.pDiv.offsetHeight;

		if(typeof(px) == 'object')
		{
			if(parseInt(document.body.clientWidth) - (parseInt(px[0]) - parseInt(document.body.scrollLeft) + parseInt(dx))<0)
				px = parseInt(px[1]) - parseInt(dx);
			else
				px = px[0];
		}

		if(typeof(py) == 'object')
		{
			if(document.body.clientHeight - (parseInt(py[1]) - parseInt(document.body.scrollTop) + parseInt(dy)) < 0)
				py = parseInt(py[0]) - parseInt(dy);
			else
				py = py[1];
		}

		if(window.createPopup)
		{
			this.pFrame.show(px-document.body.scrollLeft, py-document.body.scrollTop, dx, dy, document.body);
		}
		else
		{
			this.pFrame.style.left	= px + "px";
			this.pFrame.style.top	= py + "px";
			this.pFrame.width		= dx + "px";
			this.pFrame.height		= dy + "px";
			this.pFrame.contentWindow.focus();
			//alert(px+'-'+py+'-'+dx+'-'+dy);
		}
		this.bShowed = true;
	}

	_BXPopupWindow.prototype.CreateElement = BXCreateElement;

	_BXPopupWindow.prototype.CreateCustomElement = function(sTagName, arParams)
	{
		var ob = eval('new '+sTagName+'()');
		ob.pMainObj = this;
		ob.pDocument = this.pDocument;
		ob.CreateElement = BXCreateElement;
		if(arParams)
		{
			var sParamName;
			for(sParamName in arParams)
				ob[sParamName] = arParams[sParamName];
		}
		ob._Create();

		return ob;
	}

	_BXPopupWindow.prototype.SetCurStyles = function ()
	{
		var x1 = document.styleSheets;
		var rules, cssText = '', j;
		for(var i=0; i<x1.length; i++)
		{
			if(x1[i].cssText)
				cssText += x1[i].cssText;
			else
			{
				rules = (x1[i].rules ? x1[i].rules : x1[i].cssRules);
				for(j=0; j<rules.length; j++)
				{
					if(rules[j].cssText)
						cssText += rules[j].cssText + '\n';
					else
						cssText += rules[j].selectorText + '{' + rules[j].style.cssText + '}\n';
				}
			}
		}

		var cur = this.pDocument.getElementsByTagName("STYLE");
		for(i=0; i<cur.length; i++)
			cur[i].parentNode.removeChild(cur[i]);

		var xStyle = this.CreateElement("STYLE");
		this.pDocument.getElementsByTagName("HEAD")[0].appendChild(xStyle);
		if(BXIsIE())
			this.pDocument.styleSheets[0].cssText = cssText;
		else
			xStyle.appendChild(this.pDocument.createTextNode(cssText));

	}

}

function debug(aMsg)
{
	setTimeout(function() { throw new Error(aMsg);}, 0);
}

var s=1;
function DD(text)
{
	debug("DD:" + text);
	return true;
}


var BXPopupWindow = new _BXPopupWindow();

function addEvent(el, evname, func, p)
{
	if(el.addEventListener)
		el.addEventListener(evname, func, (p?false:p));
	else
		el["on" + evname] = func;
}

var BXCustomElementEvents = [];

function addCustomElementEvent(elEvent, sEventName, oEventHandler, oHandlerParent)
{
	elEvent.w = sEventName;
	if(!elEvent.__eventHandlers)
		elEvent.__eventHandlers = [];

	//DD('1>'+sEventName+'!'+elEvent.__eventHandlers[sEventName]);
	if(!elEvent.__eventHandlers[sEventName] || elEvent.__eventHandlers[sEventName].length<=0)
	{
		elEvent.__eventHandlers[sEventName] = [];
		addEvent(elEvent, sEventName, onCustomElementEvent);
	}

	var arEvents = elEvent.__eventHandlers[sEventName];
	arEvents.push([oHandlerParent, oEventHandler]);

	if(sEventName == 'contextmenu')
		this.sss = 'sss' ;
}

function onCustomElementEvent(e)
{
	if(!e)
		e = window.event;

	if(e.type == 'contextmenu')
		alert(this.sss);

	var arHandlers = this.__eventHandlers[e.type];
	for(var i=0; i<arHandlers.length; i++)
		arHandlers[i][1].apply(arHandlers[i][0], [e]);
}

function delCustomElementEvent(elEvent, sEventName, oEventHandler)
{
	if(!elEvent.__eventHandlers || !elEvent.__eventHandlers[sEventName])
		return false;

	var arEvents = elEvent.__eventHandlers[sEventName];
	var arNewEvents = [];
	for(var i=0; i<arEvents.length; i++)
	{
		if(arEvents[i][1]!=oEventHandler)
			arNewEvents.push(arEvents[i]);
	}

	arEvents = elEvent.__eventHandlers[sEventName] = arNewEvents;

	if(arEvents.length<=0 && elEvent.addEventListener)
		elEvent.removeEventListener(sEventName, onCustomElementEvent, false);
}


function BXIsIE()
{
	return (document.all?true:false);
}

function BXElementEqual(pElement1, pElement2)
{
	if(pElement1 == pElement2)
		return true;

	return false;

	if(!pElement1)
		return false;
	if(!pElement2)
		return false;
	if(pElement1.nodeType != 1)
		return false;
	if(pElement2.nodeType != 1)
		return false;
	if(pElement1.tagName != pElement2.tagName)
		return false;
	if(pElement1.id != pElement2.id)
		return false;
	if(pElement1.offsetHeight != pElement2.offsetHeight)
		return false;
	if(pElement1.offsetLeft != pElement2.offsetLeft)
		return false;
	if(pElement1.offsetTop != pElement2.offsetTop)
		return false;
	if(pElement1.clientHeight != pElement2.clientHeight)
		return false;
	if(pElement1.clientWidth != pElement2.clientWidth)
		return false;

	return true;
}

function BXFindParentElement(pElement1, pElement2)
{
	var p, arr1 = Array(), arr2 = Array();
	while((pElement1 = pElement1.parentNode)!=null)
		arr1[arr1.length] = pElement1;
	while((pElement2 = pElement2.parentNode)!=null)
		arr2[arr2.length] = pElement2;

	var min, diff1 = 0, diff2 = 0;
	if(arr1.length<arr2.length)
	{
		min = arr1.length;
		diff2 = arr2.length - min;
	}
	else
	{
		min = arr2.length;
		diff1 = arr1.length - min;
	}

	for(var i=0; i<min-1; i++)
	{
		if(BXElementEqual(arr1[i+diff1], arr2[i+diff2]))
			return arr1[i+diff1];
	}
alert('!');
	return arr1[0];
}

function CreateFunction()
{
}

function GetRealPos(el)
{
	if(!el || !el.offsetParent)
		return false;
	var res=Array();
	res["left"] = el.offsetLeft;
	res["top"] = el.offsetTop;
	var objParent = el.offsetParent;
	while(objParent.tagName.toUpperCase()!="BODY")
	{
		res["left"] += objParent.offsetLeft;
		res["top"] += objParent.offsetTop;
		objParent = objParent.offsetParent;
	}
	res["right"]=res["left"] + el.offsetWidth;
	res["bottom"]=res["top"] + el.offsetHeight;

	return res;
}


function GetDisplStr(status)
{
	if(status == 0)
		return "none";
	if(status == 1 && document.all)
		return "block";
	if(status == 1)
		return null;
}

function bxhtmlspecialchars(str)
{
	if(typeof(str)!='string')
		return str;
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/"/g, '&quot;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	return str;
}

/*
Глобальный объект, который будет собирать все необходимые обработчики глобальных событий,
и при их возникновении на документе или внутри ифрэймов - вызывать их.
*/

function BXEventDispatcher()
{
	this.arHandlers = Array();
	this.arEditorHandlers = Array();
	this.arEditors = Array();
	/*
	Функция-диспетчер делает объект событие совместимый с IE/FF,
	вычисляет параметры pageX/pageY - координаты относительно главного окна
	*/
	BXEventDispatcher.prototype.OnEvent = function(pDoc, e)
	{
		var arFramePos;
		if(window.event)
			e = window.event;

		if(pDoc["className"] && (pDoc.className == 'pEditorDocument' || pDoc.className == 'pSourceDocument'))
		{
			if(pDoc.pMainObj.pEditorWindow.event)
			{
				//arFramePos = GetRealPos(pDoc.pMainObj.pEditorFrame);
				e = pDoc.pMainObj.pEditorWindow.event;
			}
			else
			{
				//if(e && !pDoc.pMainObj.bDragging)
					arFramePos = GetRealPos(pDoc.pMainObj.pEditorFrame);
			}
		}

		var arHandlers = pBXEventDispatcher.arHandlers[e.type];
		if(e.target) e.targetElement = e.target;
		else if(e.srcElement) e.targetElement = e.srcElement;

		if(e.targetElement.nodeType == 3)
			e.targetElement = e.targetElement.parentNode;

		if(e.pageX || e.pageY)
		{
			e.realX = e.pageX;
			e.realY = e.pageY;
		}
		else if(e.clientX || e.clientY)
		{
			e.realX = e.clientX + document.body.scrollLeft;
			e.realY = e.clientY + document.body.scrollTop;
		}

		if(arFramePos)
		{
			e.realX += arFramePos["left"];
			e.realY += arFramePos["top"];
		}

		var res = true;
		for(var i=0; i<arHandlers.length; i++)
		{
			if(!arHandlers[i](e))
				res = false;
		}
		return res;
	}

	/*
	Метод добавляет обработчик pEventHandler для глобального события eventName
	*/
	BXEventDispatcher.prototype.AddHandler = function (eventName, pEventHandler)
	{
		if(!this.arHandlers[eventName])
		{
			this.arHandlers[eventName] = new Array();
			for(var i=0; i<this.arEditors.length; i++)
			{
				var pObject = this.arEditors[i];
				addEvent(pObject.pDocument, eventName, function (e) {pBXEventDispatcher.OnEvent(pObject.pDocument, e);});
				addEvent(pObject.pEditorDocument, eventName,  function (e) {pBXEventDispatcher.OnEvent(pObject.pEditorDocument, e);});
			}
		}

		this.arHandlers[eventName][this.arHandlers[eventName].length] = pEventHandler;
	}

	BXEventDispatcher.prototype.SetEvents = function(pDocument)
	{
		var i=0;
		for(var eventName in this.arHandlers)
		{
			for(i=0; i<this.arHandlers[eventName].length; i++)
				addEvent(pDocument, eventName,  function (e) {pBXEventDispatcher.OnEvent(pDocument, e);});
		}
	}

	/*
	Служебный метод для добавления объекта типа BXHTMLEditor
	*/
	BXEventDispatcher.prototype.__Add = function (pObject)
	{
		for(var eventName in this.arHandlers)
		{
			if(this.arEditors.length <= 0)
				addEvent(pObject.pDocument, eventName, function (e) {pBXEventDispatcher.OnEvent(pObject.pDocument, e);});
			addEvent(pObject.pEditorDocument, eventName,  function (e) {pBXEventDispatcher.OnEvent(pObject.pEditorDocument, e);});
		}
		this.arEditors[this.arEditors.length] = pObject;
	}

	/*
	Установка курсора для всех документов
	*/
	BXEventDispatcher.prototype.SetCursor = function (sCursor)
	{
		for(var i=0; i<this.arEditors.length; i++)
		{
			var pObject = this.arEditors[i];
			pObject.pDocument.body.style.cursor = sCursor;
			pObject.pEditorDocument.body.style.cursor = sCursor;
		}
	}

	BXEventDispatcher.prototype.AddEditorHandler = function (eventName, pEventHandler)
	{
		if(!this.arEditorHandlers[eventName])
			this.arEditorHandlers[eventName] = new Array();
		this.arEditorHandlers[eventName][this.arEditorHandlers[eventName].length] = pEventHandler;
	}

	BXEventDispatcher.prototype.OnEditorEvent = function (eventName, pMainObj, arParams)
	{
		if(!this.arEditorHandlers[eventName])
			return true;

		var res = true;
		for(var i=0; i<this.arEditorHandlers[eventName].length; i++)
		{
			if(!this.arEditorHandlers[eventName][i](pMainObj, arParams))
				res = false;
		}
		return res;
	}
}

function BXCloneObject(what)
{
    for(i in what)
    {
		if(typeof what[i] == 'object')
			this[i] = new BXCloneObject(what[i]);
		else
			this[i] = what[i];
    }
}

function BXDeleteNode(pNode)
{
	while(pNode.childNodes.length>0)
		pNode.parentNode.insertBefore(pNode.childNodes[0], pNode);

	pNode.parentNode.removeChild(pNode);
}

function BXIsArrayAssoc(ob)
{
	for(var i in ob)
	{
		if(parseInt(i)!=i)
			return true;
	}
	return false;
}


function BXSerializeAttr(ob, arAttr)
{
	var new_ob = {}, sAttrName;
	for(var i=0; i<arAttr.length; i++)
	{
		sAttrName = arAttr[i];
		if(ob[sAttrName])
			new_ob[sAttrName] = ob[sAttrName];
	}
	return BXSerialize(new_ob);
}

function BXUnSerializeAttr(sOb, ob, arAttr)
{
	var new_ob = BXUnSerialize(sOb);
	for(var sAttrName in new_ob)
	{
		ob[sAttrName] = new_ob[sAttrName];
	}
}

function BXSerialize(ob)
{
	var res, i, key;
	if(typeof(ob)=='object')
	{
		res = [];
		if(ob instanceof Array && !BXIsArrayAssoc(ob))
		{
			for(i=0; i<ob.length; i++)
				res.push(BXSerialize(ob[i]));
			return '[' + res.join(', ', res) + ']';
		}

		for(key in ob)
			res.push("'"+key+"': "+BXSerialize(ob[key]));

		return "{" + res.join(", ", res) + "}";
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			return "true";
		return "false";
	}

	if(typeof(ob)=='number')
		return ob;

	res = ob;
	res = res.replace(/\\/g, "\\\\");
	res = res.replace(/\n/g, "\\n");
	res = res.replace(/\r/g, "\\r");
	res = res.replace(/'/g, "\\'");

	return "'"+res+"'";
}

function BXUnSerialize(str)
{
	var res;
	eval("res = "+str);
	return res;
}

function BXPHPVal(ob, pref)
{
	var res, i, key;
	if(typeof(ob)=='object')
	{
		res = [];
		if(ob instanceof Array && !BXIsArrayAssoc(ob))
		{
			for(i=0; i<ob.length; i++)
				res.push(BXPHPVal(ob[i], (pref?pref:'undef')+'[]'));
		}
		else
		{
			for(key in ob)
				res.push(BXPHPVal(ob[key], (pref?pref+'['+key+']':key)));
		}

		return res.join("&", res);
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			return pref+'=1';
		return pref+"=0";
	}

	return pref+'='+escape(ob);
	return pref+'='+ob;
}

function BXPHPValArray(ob)
{
	var res, i, key;
	if(typeof(ob)=='object')
	{
		res = [];
		if(ob instanceof Array && !BXIsArrayAssoc(ob))
		{
			for(i=0; i<ob.length; i++)
				res.push(BXPHPValArray(ob[i]));
			return 'Array(' + res.join(', ', res) + ')';
		}

		for(key in ob)
			res.push("'"+key+"'=> "+BXPHPValArray(ob[key]));

		return "Array(" + res.join(", ", res) + ")";
	}

	if(typeof(ob)=='boolean')
	{
		if(ob)
			return "true";
		return "false";
	}

	if(typeof(ob)=='number')
		return ob;

	res = ob;
	res = res.replace(/\\/g, "\\\\");
	res = res.replace(/'/g, "\\'");

	return "'"+res+"'";
}



// инициализация глобального объекта
var pBXEventDispatcher = new BXEventDispatcher();

var BXEditorLoaded = false;
var arBXEditorObjects = [];
function BXEditorLoad()
{
	BXEditorLoaded = true;
	for(var i=0; i<arBXEditorObjects.length; i++)
		arBXEditorObjects[i].onLoad();
}

function BXEditorRegister(obj)
{
	arBXEditorObjects.push(obj);
}

BXFindParentByTagName = function (pElement, tagName)
{
	tagName = tagName.toUpperCase();
	while(pElement && (pElement.nodeType!=1 || pElement.tagName.toUpperCase() != tagName))
	{
		pElement = pElement.parentNode;
	}
	return pElement;
}


if(document.addEventListener && !document.all)
{
    document.addEventListener("DOMContentLoaded", BXEditorLoad, null);
}
else if(document.addEventListener)
{
	document.addEventListener("load", BXEditorLoad, null);
}
else
{
	/*@cc_on @*/
	/*@if (@_win32)
	    document.write("<script defer src='/bitrix/admin/fileman_js.php?script_name=ie_onload.js'><"+"/script>");
	/*@end @*/

    //window.onload = BXEditorLoad;
}
