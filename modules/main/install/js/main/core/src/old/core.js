(function(BX) {
	/* list of registered proxy functions */
	var proxyList = new WeakMap();
	var deferList = new WeakMap();

	/* List of denied event handlers */
	var deniedEvents = [];

	/* list of registered custom events */
	var customEvents = new WeakMap();
	var customEventsCnt = 0;

	/* list of external garbage collectors */
	var garbageCollectors = [];

	/* list of loaded CSS files */
	var cssList = [];
	var cssInit = false;

	/* list of loaded JS files */
	var jsList = [];
	var jsInit = false;

	var eventTypes = {
		click: 'MouseEvent',
		dblclick: 'MouseEvent',
		mousedown: 'MouseEvent',
		mousemove: 'MouseEvent',
		mouseout: 'MouseEvent',
		mouseover: 'MouseEvent',
		mouseup: 'MouseEvent',
		focus: 'MouseEvent',
		blur: 'MouseEvent'
	};

	var lastWait = [];

	var CHECK_FORM_ELEMENTS = {tagName: /^INPUT|SELECT|TEXTAREA|BUTTON$/i};

	var PRELOADING = 1;
	var PRELOADED = 2;
	var LOADING = 3;
	var LOADED = 4;
	var assets = {};
	var isAsync = null;

	BX.MSLEFT = 1;
	BX.MSMIDDLE = 2;
	BX.MSRIGHT = 4;

	BX.AM_PM_UPPER = 1;
	BX.AM_PM_LOWER = 2;
	BX.AM_PM_NONE = false;

	BX.ext = function(ob)
	{
		for (var i in ob)
		{
			if(ob.hasOwnProperty(i))
			{
				this[i] = ob[i];
			}
		}
	};

	var r = {
		script: /<script([^>]*)>/ig,
		script_end: /<\/script>/ig,
		script_src: /src=["\']([^"\']+)["\']/i,
		script_type: /type=["\']([^"\']+)["\']/i,
		space: /\s+/,
		ltrim: /^[\s\r\n]+/g,
		rtrim: /[\s\r\n]+$/g,
		style: /<link.*?(rel="stylesheet"|type="text\/css")[^>]*>/i,
		style_href: /href=["\']([^"\']+)["\']/i
	};

	BX.processHTML = function(data, scriptsRunFirst)
	{
		var matchScript, matchStyle, matchSrc, matchHref, matchType, scripts = [], styles = [];
		var textIndexes = [];
		var lastIndex = r.script.lastIndex = r.script_end.lastIndex = 0;

		while ((matchScript = r.script.exec(data)) !== null)
		{
			r.script_end.lastIndex = r.script.lastIndex;
			var matchScriptEnd = r.script_end.exec(data);
			if (matchScriptEnd === null)
			{
				break;
			}

			// skip script tags of special types
			var skipTag = false;
			if ((matchType = matchScript[1].match(r.script_type)) !== null)
			{
				if(
					matchType[1] == 'text/html'
					|| matchType[1] == 'text/template'
					|| matchType[1] == 'extension/settings'
				)
				{
					skipTag = true;
				}
			}

			if(skipTag)
			{
				textIndexes.push([lastIndex, r.script_end.lastIndex - lastIndex]);
			}
			else
			{
				textIndexes.push([lastIndex, matchScript.index - lastIndex]);

				var bRunFirst = scriptsRunFirst || (matchScript[1].indexOf('bxrunfirst') != '-1');

				if ((matchSrc = matchScript[1].match(r.script_src)) !== null)
				{
					scripts.push({"bRunFirst": bRunFirst, "isInternal": false, "JS": matchSrc[1]});
				}
				else
				{
					var start = matchScript.index + matchScript[0].length;
					var js = data.substr(start, matchScriptEnd.index-start);

					scripts.push({"bRunFirst": bRunFirst, "isInternal": true, "JS": js});
				}
			}

			lastIndex = matchScriptEnd.index + 9;
			r.script.lastIndex = lastIndex;
		}

		textIndexes.push([lastIndex, lastIndex === 0 ? data.length : data.length - lastIndex]);
		var pureData = "";
		for (var i = 0, length = textIndexes.length; i < length; i++)
		{
			if (BX.type.isString(data) && BX.type.isFunction(data.substr))
			{
				pureData += data.substr(textIndexes[i][0], textIndexes[i][1]);
			}
		}

		while ((matchStyle = pureData.match(r.style)) !== null)
		{
			if ((matchHref = matchStyle[0].match(r.style_href)) !== null && matchStyle[0].indexOf('media="') < 0)
			{
				styles.push(matchHref[1]);
			}

			pureData = pureData.replace(matchStyle[0], '');
		}

		return {'HTML': pureData, 'SCRIPT': scripts, 'STYLE': styles};
	};

	/* OO emulation utility */
	BX.extend = function(child, parent)
	{
		var f = function() {};
		f.prototype = parent.prototype;

		child.prototype = new f();
		child.prototype.constructor = child;

		child.superclass = parent.prototype;
		child.prototype.superclass = parent.prototype;
		if(parent.prototype.constructor == Object.prototype.constructor)
		{
			parent.prototype.constructor = parent;
		}
	};

	BX.is_subclass_of = function(ob, parent_class)
	{
		if (ob instanceof parent_class)
			return true;

		if (parent_class.superclass)
			return BX.is_subclass_of(ob, parent_class.superclass);

		return false;
	};

	BX.clearNodeCache = function()
	{
		return false;
	};

	BX.bitrix_sessid = function() {return BX.message("bitrix_sessid"); };

	/**
	 * Creates document fragment with child nodes.
	 *
	 * @param {Node[]} nodes
	 * @return {DocumentFragment}
	 */
	BX.createFragment = function(nodes)
	{
		var fragment = document.createDocumentFragment();

		if(!BX.type.isArray(nodes))
		{
			return fragment;
		}
		for(var i = 0; i < nodes.length; i++)
		{
			fragment.appendChild(nodes[i]);
		}

		return fragment;
	};

	/**
	 * @deprecated
	 * @use BX.style
	 * @param element
	 * @param opacity
	 */
	BX.setOpacity = function(element, opacity)
	{
		var opacityValue = parseFloat(opacity);

		if (!isNaN(opacityValue) && BX.type.isDomNode(element))
		{
			opacityValue = opacityValue < 1 ? opacityValue : opacityValue / 100;
			BX.style(element, 'opacity', opacityValue);
		}
	};

	/**
	 * @deprecated
	 * @param el
	 * @return {*}
	 */
	BX.hoverEvents = function(el)
	{
		if (el)
			return BX.adjust(el, {events: BX.hoverEvents()});
		else
			return {mouseover: BX.hoverEventsHover, mouseout: BX.hoverEventsHout};
	};

	/**
	 * @deprecated
	 */
	BX.hoverEventsHover = function(){BX.addClass(this,'bx-hover');this.BXHOVER=true;};
	/**
	 * @deprecated
	 */
	BX.hoverEventsHout = function(){BX.removeClass(this,'bx-hover');this.BXHOVER=false;};

	/**
	 * @deprecated
	 */
	BX.focusEvents = function(el)
	{
		if (el)
			return BX.adjust(el, {events: BX.focusEvents()});
		else
			return {mouseover: BX.focusEventsFocus, mouseout: BX.focusEventsBlur};
	};

	/**
	 * @deprecated
	 */
	BX.focusEventsFocus = function(){BX.addClass(this,'bx-focus');this.BXFOCUS=true;};
	/**
	 * @deprecated
	 */
	BX.focusEventsBlur = function(){BX.removeClass(this,'bx-focus');this.BXFOCUS=false;};

	BX.setUnselectable = function(node)
	{
		BX.style(node, {
			'userSelect': 'none',
			'MozUserSelect': 'none',
			'WebkitUserSelect': 'none',
			'KhtmlUserSelect': 'none',
		});
		node.setAttribute('unSelectable', 'on');
	};

	BX.setSelectable = function(node)
	{
		BX.style(node, {
			'userSelect': null,
			'MozUserSelect': null,
			'WebkitUserSelect': null,
			'KhtmlUserSelect': null,
		});
		node.removeAttribute('unSelectable');
	};

	BX.styleIEPropertyName = function(name)
	{
		if (name == 'float')
			name = BX.browser.IsIE() ? 'styleFloat' : 'cssFloat';
		else
		{
			var res = BX.browser.isPropertySupported(name);
			if (res)
			{
				name = res;
			}
			else
			{
				var reg = /(\-([a-z]){1})/g;
				if (reg.test(name))
				{
					name = name.replace(reg, function () {return arguments[2].toUpperCase();});
				}
			}
		}
		return name;
	};

	BX.focus = function(el)
	{
		try
		{
			el.focus();
			return true;
		}
		catch (e)
		{
			return false;
		}
	};

	BX.firstChild = function(el)
	{
		return BX.type.isDomNode(el) ? el.firstElementChild : null;
	};

	BX.lastChild = function(el)
	{
		return BX.type.isDomNode(el) ? el.lastElementChild : null;
	};

	BX.previousSibling = function(el)
	{
		return BX.type.isDomNode(el) ? el.previousElementSibling : null;
	};

	BX.nextSibling = function(el)
	{
		return BX.type.isDomNode(el) ? el.nextElementSibling : null;
	};

	/*
		params: {
			obj : html node
			className : className value
			recursive : used only for older browsers to optimize the tree traversal, in new browsers the search is always recursively, default - true
		}

		Search all nodes with className
	*/
	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param obj
	 * @param className
	 * @param recursive
	 * @return {*}
	 */
	BX.findChildrenByClassName = function(obj, className, recursive)
	{
		if(!obj || !obj.childNodes) return null;

		var result = [];
		if (typeof(obj.getElementsByClassName) == 'undefined')
		{
			recursive = recursive !== false;
			result = BX.findChildren(obj, {className : className}, recursive);
		}
		else
		{
			var col = obj.getElementsByClassName(className);
			for (i=0,l=col.length;i<l;i++)
			{
				result[i] = col[i];
			}
		}
		return result;
	};

	/*
		params: {
			obj : html node
			className : className value
			recursive : used only for older browsers to optimize the tree traversal, in new browsers the search is always recursively, default - true
		}

		Search first node with className
	*/
	/**
	 * @deprecated
	 * @use .querySelector
	 * @param obj
	 * @param className
	 * @param recursive
	 * @return {*}
	 */
	BX.findChildByClassName = function(obj, className, recursive)
	{
		if(!obj || !obj.childNodes) return null;

		var result = null;
		if (typeof(obj.getElementsByClassName) == 'undefined')
		{
			recursive = recursive !== false;
			result = BX.findChild(obj, {className : className}, recursive);
		}
		else
		{
			var col = obj.getElementsByClassName(className);
			if (col && typeof(col[0]) != 'undefined')
			{
				result = col[0];
			}
			else
			{
				result = null;
			}
		}
		return result;
	};

	/*
		params: {
			tagName|tag : 'tagName',
			className|class : 'className',
			attribute : {attribute : value, attribute : value} | attribute | [attribute, attribute....],
			property : {prop: value, prop: value} | prop | [prop, prop]
		}

		all values can be RegExps or strings
	*/
	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param obj
	 * @param params
	 * @param recursive
	 * @return {*|Node}
	 */
	BX.findChildren = function(obj, params, recursive)
	{
		return BX.findChild(obj, params, recursive, true);
	};

	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param obj
	 * @param params
	 * @param recursive
	 * @param get_all
	 * @return {*}
	 */
	BX.findChild = function(obj, params, recursive, get_all)
	{
		if(!obj || !obj.childNodes) return null;

		recursive = !!recursive; get_all = !!get_all;

		var n = obj.childNodes.length, result = [];

		for (var j=0; j<n; j++)
		{
			var child = obj.childNodes[j];

			if (_checkNode(child, params))
			{
				if (get_all)
					result.push(child);
				else
					return child;
			}

			if(recursive == true)
			{
				var res = BX.findChild(child, params, recursive, get_all);
				if (res)
				{
					if (get_all)
						result = BX.util.array_merge(result, res);
					else
						return res;
				}
			}
		}

		if (get_all || result.length > 0)
			return result;
		else
			return null;
	};

	/**
	 * @deprecated
	 * @use .closest()
	 * @param obj
	 * @param params
	 * @param maxParent
	 * @return {*}
	 */
	BX.findParent = function(obj, params, maxParent)
	{
		if(!obj)
			return null;

		var o = obj;
		while(o.parentNode)
		{
			var parent = o.parentNode;

			if (_checkNode(parent, params))
				return parent;

			o = parent;

			if (!!maxParent &&
				(BX.type.isFunction(maxParent)
					|| typeof maxParent == 'object'))
			{
				if (BX.type.isElementNode(maxParent))
				{
					if (o == maxParent)
						break;
				}
				else
				{
					if (_checkNode(o, maxParent))
						break;
				}
			}
		}
		return null;
	};

	/**
	 * @deprecated
	 * @use .querySelector
	 * @param obj
	 * @param params
	 * @return {*}
	 */
	BX.findNextSibling = function(obj, params)
	{
		if(!obj)
			return null;
		var o = obj;
		while(o.nextSibling)
		{
			var sibling = o.nextSibling;
			if (_checkNode(sibling, params))
				return sibling;
			o = sibling;
		}
		return null;
	};

	/**
	 * @deprecated
	 * @use .querySelector
	 * @param obj
	 * @param params
	 * @return {*}
	 */
	BX.findPreviousSibling = function(obj, params)
	{
		if(!obj)
			return null;

		var o = obj;
		while(o.previousSibling)
		{
			var sibling = o.previousSibling;
			if(_checkNode(sibling, params))
				return sibling;
			o = sibling;
		}
		return null;
	};

	BX.checkNode = function(obj, params)
	{
		return _checkNode(obj, params);
	};

	/**
	 * @deprecated
	 * @use .querySelectorAll
	 * @param form
	 * @return {Array}
	 */
	BX.findFormElements = function(form)
	{
		if (BX.type.isString(form))
			form = document.forms[form]||BX(form);

		var res = [];

		if (BX.type.isElementNode(form))
		{
			if (form.tagName.toUpperCase() == 'FORM')
			{
				res = form.elements;
			}
			else
			{
				res = BX.findChildren(form, CHECK_FORM_ELEMENTS, true);
			}
		}

		return res;
	};

	/**
	 * @deprecated
	 * @use .contains()
	 * @param whichNode
	 * @param forNode
	 * @return {boolean}
	 */
	BX.isParentForNode = function(whichNode, forNode)
	{
		if (BX.type.isDomNode(whichNode) && BX.type.isDomNode(forNode))
		{
			return whichNode.contains(forNode);
		}

		return false;
	};

	BX.getCaretPosition = function(node)
	{
		var pos = 0;

		if(node.selectionStart || node.selectionStart == 0)
		{
			pos = node.selectionStart;
		}
		else if(document.selection)
		{
			node.focus();
			var selection = document.selection.createRange();
			selection.moveStart('character', -node.value.length);
			pos = selection.text.length;
		}

		return (pos);
	};

	BX.setCaretPosition = function(node, pos)
	{
		if(!BX.isNodeInDom(node) || BX.isNodeHidden(node) || node.disabled)
		{
			return;
		}

		if(node.setSelectionRange)
		{
			node.focus();
			node.setSelectionRange(pos, pos);
		}
		else if(node.createTextRange)
		{
			var range = node.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	};

	// access private. use BX.mergeEx instead.
	// todo: refactor BX.merge, make it work through BX.mergeEx
	BX.merge = function(){
		var arg = Array.prototype.slice.call(arguments);

		if(arg.length < 2)
			return {};

		var result = arg.shift();

		for(var i = 0; i < arg.length; i++)
		{
			for(var k in arg[i]){

				if(typeof arg[i] == 'undefined' || arg[i] == null)
					continue;

				if(arg[i].hasOwnProperty(k)){

					if(typeof arg[i][k] == 'undefined' || arg[i][k] == null)
						continue;

					if(typeof arg[i][k] == 'object' && !BX.type.isDomNode(arg[i][k]) && (typeof arg[i][k]['isUIWidget'] == 'undefined')){

						// go deeper

						var isArray = 'length' in arg[i][k];

						if(typeof result[k] != 'object')
							result[k] = isArray ? [] : {};

						if(isArray)
							BX.util.array_merge(result[k], arg[i][k]);
						else
							BX.merge(result[k], arg[i][k]);

					}else
						result[k] = arg[i][k];
				}
			}
		}

		return result;
	};

	BX.mergeEx = function()
	{
		var arg = Array.prototype.slice.call(arguments);
		if(arg.length < 2)
		{
			return {};
		}

		var result = arg.shift();
		for (var i = 0; i < arg.length; i++)
		{
			for (var k in arg[i])
			{
				if (typeof arg[i] == "undefined" || arg[i] == null || !arg[i].hasOwnProperty(k))
				{
					continue;
				}

				if (BX.type.isPlainObject(arg[i][k]) && BX.type.isPlainObject(result[k]))
				{
					BX.mergeEx(result[k], arg[i][k]);
				}
				else
				{
					result[k] = BX.type.isPlainObject(arg[i][k]) ? BX.clone(arg[i][k]) : arg[i][k];
				}
			}
		}

		return result;
	};

	BX.getEventButton = function(e)
	{
		e = e || window.event;

		var flags = 0;

		if (typeof e.which != 'undefined')
		{
			switch (e.which)
			{
				case 1: flags = flags|BX.MSLEFT; break;
				case 2: flags = flags|BX.MSMIDDLE; break;
				case 3: flags = flags|BX.MSRIGHT; break;
			}
		}
		else if (typeof e.button != 'undefined')
		{
			flags = event.button;
		}

		return flags || BX.MSLEFT;
	};

	var captured_events = null, _bind = null;
	BX.CaptureEvents = function(el_c, evname_c)
	{
		if (_bind)
			return;

		_bind = BX.bind;
		captured_events = [];

		BX.bind = function(el, evname, func)
		{
			if (el === el_c && evname === evname_c)
				captured_events.push(func);

			_bind.apply(this, arguments);
		}
	};

	BX.CaptureEventsGet = function()
	{
		if (_bind)
		{
			BX.bind = _bind;

			var captured = captured_events;

			_bind = null;
			captured_events = null;
			return captured;
		}
		return null;
	};

	// Don't even try to use it for submit event!
	BX.fireEvent = function(ob,ev)
	{
		var result = false, e = null;
		if (BX.type.isDomNode(ob))
		{
			result = true;
			if (document.createEventObject)
			{
				// IE
				if (eventTypes[ev] != 'MouseEvent')
				{
					e = document.createEventObject();
					e.type = ev;
					result = ob.fireEvent('on' + ev, e);
				}

				if (ob[ev])
				{
					ob[ev]();
				}
			}
			else
			{
				// non-IE
				e = null;

				switch (eventTypes[ev])
				{
					case 'MouseEvent':
						e = document.createEvent('MouseEvent');
						try
						{
							e.initMouseEvent(ev, true, true, top, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, null);
						}
						catch (initException)
						{
							e.initMouseEvent(ev, true, true, window, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, null);
						}

					break;
					default:
						e = document.createEvent('Event');
						e.initEvent(ev, true, true);
				}

				result = ob.dispatchEvent(e);
			}
		}

		return result;
	};

	BX.getWheelData = function(e)
	{
		e = e || window.event;
		e.wheelData = e.detail ? e.detail * -1 : e.wheelDelta / 40;
		return e.wheelData;
	};

	BX.proxy_context = null;

	BX.delegate = function (func, thisObject)
	{
		if (!func || !thisObject)
			return func;

		return function() {
			var cur = BX.proxy_context;
			BX.proxy_context = this;
			var res = func.apply(thisObject, arguments);
			BX.proxy_context = cur;
			return res;
		}
	};

	BX.delegateLater = function (func_name, thisObject, contextObject)
	{
		return function()
		{
			if (thisObject[func_name])
			{
				var cur = BX.proxy_context;
				BX.proxy_context = this;
				var res = thisObject[func_name].apply(contextObject||thisObject, arguments);
				BX.proxy_context = cur;
				return res;
			}
			return null;
		}
	};

	BX.proxy = function(func, thisObject)
	{
		return getObjectDelegate(func, thisObject, proxyList);
	};

	BX.defer = function(func, thisObject)
	{
		if (!!thisObject)
			return BX.defer_proxy(func, thisObject);
		else
			return function() {
				var arg = arguments;
				setTimeout(function(){func.apply(this,arg)}, 10);
			};
	};

	BX.defer_proxy = function(func, thisObject)
	{
		return getObjectDelegate(func, thisObject, deferList, BX.defer);
	};

	/**
	 *
	 * @private
	 */
	function getObjectDelegate(func, thisObject, collection, decorator)
	{
		if (!BX.type.isFunction(func) || !BX.type.isMapKey(thisObject))
		{
			return func;
		}

		var objectDelegates = collection.get(thisObject);
		if (!objectDelegates)
		{
			objectDelegates = new WeakMap();
			collection.set(thisObject, objectDelegates);
		}

		var delegate = objectDelegates.get(func);
		if (!delegate)
		{
			delegate = decorator ? decorator(BX.delegate(func, thisObject)) : BX.delegate(func, thisObject);
			objectDelegates.set(func, delegate);
		}

		return delegate;
	}

	BX.once = function(el, evname, func)
	{
		var fn = function()
		{
			BX.unbind(el, evname, fn);
			func.apply(this, arguments);
		};

		return fn;
	};

	BX.bindDelegate = function (elem, eventName, isTarget, handler)
	{
		var h = BX.delegateEvent(isTarget, handler);
		BX.bind(elem, eventName, h);
		return h;
	};

	BX.delegateEvent = function(isTarget, handler)
	{
		return function(e)
		{
			e = e || window.event;
			var target = e.target || e.srcElement;

			while (target != this)
			{
				if (_checkNode(target, isTarget))
				{
					return handler.call(target, e);
				}
				if (target && target.parentNode)
					target = target.parentNode;
				else
					break;
			}
			return null;
		}
	};

	BX.False = function() {return false;};
	BX.DoNothing = function() {};

	// TODO: also check event handlers set via BX.bind()
	BX.denyEvent = function(el, ev)
	{
		deniedEvents.push([el, ev, el['on' + ev]]);
		el['on' + ev] = BX.DoNothing;
	};

	BX.allowEvent = function(el, ev)
	{
		for(var i=0, len=deniedEvents.length; i<len; i++)
		{
			if (deniedEvents[i][0] == el && deniedEvents[i][1] == ev)
			{
				el['on' + ev] = deniedEvents[i][2];
				BX.util.deleteFromArray(deniedEvents, i);
				return;
			}
		}
	};

	BX.fixEventPageXY = function(event)
	{
		BX.fixEventPageX(event);
		BX.fixEventPageY(event);
		return event;
	};

	BX.fixEventPageX = function(event)
	{
		if (event.pageX == null && event.clientX != null)
		{
			event.pageX =
				event.clientX +
				(document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) -
				(document.documentElement.clientLeft || 0);
		}

		return event;
	};

	BX.fixEventPageY = function(event)
	{
		if (event.pageY == null && event.clientY != null)
		{
			event.pageY =
				event.clientY +
				(document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) -
				(document.documentElement.clientTop || 0);
		}

		return event;
	};

	/**
	 * @deprecated
	 * @see e.preventDefault()
	 */
	BX.PreventDefault = function(e)
	{
		if(!e) e = window.event;
		if(e.stopPropagation)
		{
			e.preventDefault();
			e.stopPropagation();
		}
		else
		{
			e.cancelBubble = true;
			e.returnValue = false;
		}
		return false;
	};

	/**
	 * @deprecated
	 * @see e.preventDefault();
	 *
	 * @param e
	 * @return {boolean}
	 */
	BX.eventReturnFalse = function(e)
	{
		e=e||window.event;
		if (e && e.preventDefault) e.preventDefault();
		else e.returnValue = false;
		return false;
	};

	/**
	 * @deprecated
	 * @see e.stopPropagation()
	 * @param e
	 */
	BX.eventCancelBubble = function(e)
	{
		e=e||window.event;
		if(e && e.stopPropagation)
			e.stopPropagation();
		else
			e.cancelBubble = true;
	};

	BX.bindDebouncedChange = function(node, fn, fnInstant, timeout, ctx)
	{
		ctx = ctx || window;
		timeout = timeout || 300;

		var dataTag = 'bx-dc-previous-value';
		BX.data(node, dataTag, node.value);

		var act = function(fn, val){

			var pVal = BX.data(node, dataTag);

			if(typeof pVal == 'undefined' || pVal != val){
				if(typeof ctx != 'object')
					fn(val);
				else
					fn.apply(ctx, [val]);
			}
		};

		var actD = BX.debounce(function(){
			var val = node.value;
			act(fn, val);
			BX.data(node, dataTag, val);
		}, timeout);

		BX.bind(node, 'keyup', actD);
		BX.bind(node, 'change', actD);
		BX.bind(node, 'input', actD);

		if(BX.type.isFunction(fnInstant)){

			var actI = function(){
				act(fnInstant, node.value);
			};

			BX.bind(node, 'keyup', actI);
			BX.bind(node, 'change', actI);
			BX.bind(node, 'input', actI);
		}
	};

	BX.parseJSON = function(data, context)
	{
		var result = null;
		if (BX.type.isNotEmptyString(data))
		{
			try {
				if (data.indexOf("\n") >= 0)
					eval('result = ' + data);
				else
					result = (new Function("return " + data))();
			} catch(e) {
				BX.onCustomEvent(context, 'onParseJSONFailure', [data, context])
			}
		}
		else if(BX.type.isPlainObject(data))
		{
			return data;
		}

		return result;
	};

	BX.submit = function(obForm, action_name, action_value, onAfterSubmit)
	{
		action_name = action_name || 'save';
		if (!obForm['BXFormSubmit_' + action_name])
		{
			obForm['BXFormSubmit_' + action_name] = obForm.appendChild(BX.create('INPUT', {
				'props': {
					'type': 'submit',
					'name': action_name,
					'value': action_value || 'Y'
				},
				'style': {
					'display': 'none'
				}
			}));
		}

		if (obForm.sessid)
			obForm.sessid.value = BX.bitrix_sessid();

		setTimeout(BX.delegate(function() {BX.fireEvent(this, 'click'); if (onAfterSubmit) onAfterSubmit();}, obForm['BXFormSubmit_' + action_name]), 10);
	};

	BX.show = function(ob, displayType)
	{
		if (ob.BXDISPLAY || !_checkDisplay(ob, displayType))
		{
			ob.style.display = ob.BXDISPLAY;
		}
	};

	BX.hide = function(ob, displayType)
	{
		if (!ob.BXDISPLAY)
			_checkDisplay(ob, displayType);

		ob.style.display = 'none';
	};

	BX.toggle = function(ob, values)
	{
		if (!values && BX.type.isElementNode(ob))
		{
			var bShow = true;
			if (ob.BXDISPLAY)
				bShow = !_checkDisplay(ob);
			else
				bShow = ob.style.display == 'none';

			if (bShow)
				BX.show(ob);
			else
				BX.hide(ob);
		}
		else if (BX.type.isArray(values))
		{
			for (var i=0,len=values.length; i<len; i++)
			{
				if (ob == values[i])
				{
					ob = values[i==len-1 ? 0 : i+1];
					break;
				}
			}
			if (i==len)
				ob = values[0];
		}

		return ob;
	};

	function _checkDisplay(ob, displayType)
	{
		if (typeof displayType != 'undefined')
			ob.BXDISPLAY = displayType;

		var d = ob.style.display || BX.style(ob, 'display');
		if (d != 'none')
		{
			ob.BXDISPLAY = ob.BXDISPLAY || d;
			return true;
		}
		else
		{
			ob.BXDISPLAY = ob.BXDISPLAY || 'block';
			return false;
		}
	}

	/* some useful util functions */

	BX.util = {
		/**
		 * @deprecated
		 * @use [].filter(value => !BX.Type.isNil(value))
		 * @param ar
		 * @return {*}
		 */
		array_values: function(ar)
		{
			if (!BX.type.isArray(ar))
				return BX.util._array_values_ob(ar);
			var arv = [];
			for(var i=0,l=ar.length;i<l;i++)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(ar[i]);
			return arv;
		},

		/**
		 * @deprecated
		 * @use Object.values([]).filter(value => !BX.Type.isNil(value))
		 * @param ar
		 * @return {Array}
		 * @private
		 */
		_array_values_ob: function(ar)
		{
			var arv = [];
			for(var i in ar)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(ar[i]);
			return arv;
		},

		/**
		 * @deprecated
		 * @use
		 * @param ar
		 * @return {*}
		 */
		array_keys: function(ar)
		{
			if (!BX.type.isArray(ar))
				return BX.util._array_keys_ob(ar);
			var arv = [];
			for(var i=0,l=ar.length;i<l;i++)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(i);
			return arv;
		},

		_array_keys_ob: function(ar)
		{
			var arv = [];
			for(var i in ar)
				if (ar[i] !== null && typeof ar[i] != 'undefined')
					arv.push(i);
			return arv;
		},

		object_keys: function(obj)
		{
			var arv = [];
			for(var k in obj)
			{
				if(obj.hasOwnProperty(k))
				{
					arv.push(k);
				}
			}
			return arv;
		},

		/**
		 * @deprecated
		 * @use firstArr.concat(secondArr);
		 * @param first
		 * @param second
		 * @return {*[]}
		 */
		array_merge: function(first, second)
		{
			if (!BX.type.isArray(first)) first = [];
			if (!BX.type.isArray(second)) second = [];

			var i = first.length, j = 0;

			if (typeof second.length === "number")
			{
				for (var l = second.length; j < l; j++)
				{
					first[i++] = second[j];
				}
			}
			else
			{
				while (second[j] !== undefined)
				{
					first[i++] = second[j++];
				}
			}

			first.length = i;

			return first;
		},

		array_flip: function (object)
		{
			var newObject = {};

			for (var key in object)
			{
				newObject[object[key]] = key;
			}

			return newObject;
		},

		array_diff: function(ar1, ar2, hash)
		{
			hash = BX.type.isFunction(hash) ? hash : null;
			var i, length, v, h, map = {}, result = [];
			for(i = 0, length = ar2.length; i < length; i++)
			{
				v = ar2[i];
				h = hash ? hash(v) : v;
				map[h] = true;
			}

			for(i = 0, length = ar1.length; i < length; i++)
			{
				v = ar1[i];
				h = hash ? hash(v) : v;
				if(typeof(map[h]) === "undefined")
				{
					result.push(v);
				}
			}
			return result;
		},

		/**
		 * @deprecated
		 * @use Set
		 */
		array_unique: function(ar)
		{
			var i=0,j,len=ar.length;
			if(len<2) return ar;

			for (; i<len-1;i++)
			{
				for (j=i+1; j<len;j++)
				{
					if (ar[i]==ar[j])
					{
						ar.splice(j--,1); len--;
					}
				}
			}

			return ar;
		},

		/**
		 * @deprecated
		 * @use myArr.includes(needle)
		 */
		in_array: function(needle, haystack)
		{
			for(var i=0; i<haystack.length; i++)
			{
				if(haystack[i] == needle)
					return true;
			}
			return false;
		},

		/**
		 * @deprecated
		 * @use myArr.findIndex(item => item === needle);
		 */
		array_search: function(needle, haystack)
		{
			for(var i=0; i<haystack.length; i++)
			{
				if(haystack[i] == needle)
					return i;
			}
			return -1;
		},

		object_search_key: function(needle, haystack)
		{
			if (typeof haystack[needle] != 'undefined')
				return haystack[needle];

			for(var i in haystack)
			{
				if (typeof haystack[i] == "object")
				{
					var result = BX.util.object_search_key(needle, haystack[i]);
					if (result !== false)
						return result;
				}
			}
			return false;
		},

		trim: function(s)
		{
			if (BX.type.isString(s))
			{
				return s.trim();
			}

			return s;
		},

		urlencode: function(s){return encodeURIComponent(s);},

		// it may also be useful. via sVD.
		deleteFromArray: function(ar, ind) {return ar.slice(0, ind).concat(ar.slice(ind + 1));},
		insertIntoArray: function(ar, ind, el) {return ar.slice(0, ind).concat([el]).concat(ar.slice(ind));},

		htmlspecialchars: function(str)
		{
			return BX.Text.encode(str);
		},

		htmlspecialcharsback: function(str)
		{
			return BX.Text.decode(str);
		},

		// Quote regular expression characters plus an optional character
		preg_quote: function(str, delimiter)
		{
			if(!str.replace)
				return str;
			return str.replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
		},

		jsencode: function(str)
		{
			if (!str || !str.replace)
				return str;

			var escapes =
				[
					{ c: "\\\\", r: "\\\\" }, // should be first
					{ c: "\\t", r: "\\t" },
					{ c: "\\n", r: "\\n" },
					{ c: "\\r", r: "\\r" },
					{ c: "\"", r: "\\\"" },
					{ c: "'", r: "\\'" },
					{ c: "<", r: "\\x3C" },
					{ c: ">", r: "\\x3E" },
					{ c: "\\u2028", r: "\\u2028" },
					{ c: "\\u2029", r: "\\u2029" }
				];
			for (var i = 0; i < escapes.length; i++)
				str = str.replace(new RegExp(escapes[i].c, 'g'), escapes[i].r);
			return str;
		},

		getCssName: function(jsName)
		{
			if (!BX.type.isNotEmptyString(jsName))
			{
				return "";
			}

			return jsName.replace(/[A-Z]/g, function(match) {
				return "-" + match.toLowerCase();
			});
		},

		getJsName: function(cssName)
		{
			var regex = /\-([a-z]){1}/g;
			if (regex.test(cssName))
			{
				return cssName.replace(regex, function(match, letter) {
					return letter.toUpperCase();
				});
			}

			return cssName;
		},

		nl2br: function(str)
		{
			if (!str || !str.replace)
				return str;

			return str.replace(/([^>])\n/g, '$1<br/>');
		},

		/**
		 * @deprecated
		 * @use .padStart() / .padEnd()
		 * @param input
		 * @param pad_length
		 * @param pad_string
		 * @param pad_type
		 * @return {*}
		 */
		str_pad: function(input, pad_length, pad_string, pad_type)
		{
			pad_string = pad_string || ' ';
			pad_type = pad_type || 'right';
			input = input.toString();

			if (pad_type === 'left')
			{
				return BX.util.str_pad_left(input, pad_length, pad_string);
			}

			return BX.util.str_pad_right(input, pad_length, pad_string);
		},

		str_pad_left: function(input, pad_length, pad_string)
		{
			return input.toString().padStart(pad_length, pad_string);
		},

		str_pad_right: function(input, pad_length, pad_string)
		{
			return input.toString().padEnd(pad_length, pad_string);
		},

		strip_tags: function(str)
		{
			return str.split(/<[^>]+>/g).join('');
		},

		strip_php_tags: function(str)
		{
			return str.replace(/<\?(.|[\r\n])*?\?>/g, '');
		},

		popup: function(url, width, height)
		{
			var w, h;
			if(BX.browser.IsOpera())
			{
				w = document.body.offsetWidth;
				h = document.body.offsetHeight;
			}
			else
			{
				w = screen.width;
				h = screen.height;
			}
			return window.open(url, '', 'status=no,scrollbars=yes,resizable=yes,width='+width+',height='+height+',top='+Math.floor((h - height)/2-14)+',left='+Math.floor((w - width)/2-5));
		},

		shuffle: function(array)
		{
			var temporaryValue, randomIndex;
			var currentIndex = array.length;

			while (0 !== currentIndex)
			{
				randomIndex = Math.floor(Math.random() * currentIndex);
				currentIndex -= 1;

				temporaryValue = array[currentIndex];
				array[currentIndex] = array[randomIndex];
				array[randomIndex] = temporaryValue;
			}

			return array;
		},

		// BX.util.objectSort(object, sortBy, sortDir) - Sort object by property
		// function params: 1 - object for sort, 2 - sort by property, 3 - sort direction (asc/desc)
		// return: sort array [[objectElement], [objectElement]] in sortDir direction

		// example: BX.util.objectSort({'L1': {'name': 'Last'}, 'F1': {'name': 'First'}}, 'name', 'asc');
		// return: [{'name' : 'First'}, {'name' : 'Last'}]
		objectSort: function(object, sortBy, sortDir)
		{
			sortDir = sortDir == 'asc'? 'asc': 'desc';

			var arItems = [], i;
			for (i in object)
			{
				if (object.hasOwnProperty(i) && object[i][sortBy])
				{
					arItems.push([i, object[i][sortBy]]);
				}
			}

			if (sortDir == 'asc')
			{
				arItems.sort(function(i, ii) {
					var s1, s2;
					if (BX.type.isDate(i[1]))
					{
						s1 = i[1].getTime();
					}
					else if (!isNaN(i[1]))
					{
						s1 = parseInt(i[1]);
					}
					else
					{
						s1 = i[1].toString().toLowerCase();
					}

					if (BX.type.isDate(ii[1]))
					{
						s2 = ii[1].getTime();
					}
					else if (!isNaN(ii[1]))
					{
						s2 = parseInt(ii[1]);
					}
					else
					{
						s2 = ii[1].toString().toLowerCase();
					}

					if (s1 > s2)
						return 1;
					else if (s1 < s2)
						return -1;
					else
						return 0;
				});
			}
			else
			{
				arItems.sort(function(i, ii) {
					var s1, s2;
					if (BX.type.isDate(i[1]))
					{
						s1 = i[1].getTime();
					}
					else if (!isNaN(i[1]))
					{
						s1 = parseInt(i[1]);
					}
					else
					{
						s1 = i[1].toString().toLowerCase();
					}

					if (BX.type.isDate(ii[1]))
					{
						s2 = ii[1].getTime();
					}
					else if (!isNaN(ii[1]))
					{
						s2 = parseInt(ii[1]);
					}
					else
					{
						s2 = ii[1].toString().toLowerCase();
					}

					if (s1 < s2)
						return 1;
					else if (s1 > s2)
						return -1;
					else
						return 0;
				});
			}

			var arReturnArray = Array();
			for (i = 0; i < arItems.length; i++)
			{
				arReturnArray.push(object[arItems[i][0]]);
			}

			return arReturnArray;
		},

		objectMerge: function()
		{
			return BX.mergeEx.apply(window, arguments);
		},

		objectClone : function(object)
		{
			return BX.clone(object, true);
		},

		// #fdf9e5 => {r=253, g=249, b=229}
		hex2rgb: function(color)
		{
			var rgb = color.replace(/[# ]/g,"").replace(/^(.)(.)(.)$/,'$1$1$2$2$3$3').match(/.{2}/g);
			for (var i=0;  i<3; i++)
			{
				rgb[i] = parseInt(rgb[i], 16);
			}
			return {'r':rgb[0],'g':rgb[1],'b':rgb[2]};
		},

		/**
		 * @deprecated
		 * @use BX.Uri
		 * @param url
		 * @param param
		 * @return {string}
		 */
		remove_url_param: function(url, param)
		{
			return BX.Uri.removeParam(url, param);
		},

		/*
		{'param1': 'value1', 'param2': 'value2'}
		 */
		/**
		 * @deprecated
		 * @use BX.Uri
		 * @param url
		 * @param params
		 * @return {string}
		 */
		add_url_param: function(url, params)
		{
			var preparedParams = Object.entries(params).reduce(function(acc, item) {
				acc[item[0]] = BX.type.isArray(item[1]) ? item[1].join() : item[1];
				return acc;
			}, {});

			return BX.Uri.addParam(url, preparedParams);
		},

		/*
	{'param1': 'value1', 'param2': 'value2'}
	 */
		buildQueryString: function(params)
		{
			var result = '';
			for (var key in params)
			{
				var value = params[key];
				if(BX.type.isArray(value))
				{
					value.forEach(function(valueElement, index)
					{
						result += encodeURIComponent(key + "[" + index + "]") + "=" + encodeURIComponent(valueElement) + "&";
					});
				}
				else
				{
					result += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
				}
			}

			if(result.length > 0)
			{
				result = result.substr(0, result.length - 1);
			}
			return result;
		},

		even: function(digit)
		{
			return (parseInt(digit) % 2 == 0);
		},

		hashCode: function(str)
		{
			if(!BX.type.isNotEmptyString(str))
			{
				return 0;
			}

			var hash = 0;
			for (var i = 0; i < str.length; i++)
			{
				var c = str.charCodeAt(i);
				hash = ((hash << 5) - hash) + c;
				hash = hash & hash;
			}
			return hash;
		},

		getRandomString: function (length)
		{
			return BX.Text.getRandom(length);
		},

		number_format: function(number, decimals, dec_point, thousands_sep)
		{
			var i, j, kw, kd, km, sign = '';
			decimals = Math.abs(decimals);
			if (isNaN(decimals) || decimals < 0)
			{
				decimals = 2;
			}
			dec_point = dec_point || ',';
			if (typeof thousands_sep === 'undefined')
				thousands_sep = '.';

			number = (+number || 0).toFixed(decimals);
			if (number < 0)
			{
				sign = '-';
				number = -number;
			}

			i = parseInt(number, 10) + '';
			j = (i.length > 3 ? i.length % 3 : 0);

			km = (j ? i.substr(0, j) + thousands_sep : '');
			kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
			kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, '0').slice(2) : '');

			return sign + km + kw + kd;
		},

		getExtension: function (url)
		{
			url = url || "";
			var items = url.split("?")[0].split(".");
			return items[items.length-1].toLowerCase();
		},
		addObjectToForm: function(object, form, prefix)
		{
			if(!BX.type.isString(prefix))
			{
				prefix = "";
			}

			for(var key in object)
			{
				if(!object.hasOwnProperty(key))
				{
					continue;
				}

				var value = object[key];
				var name = prefix !== "" ? (prefix + "[" + key + "]") : key;
				if(BX.type.isArray(value))
				{
					var obj = {};
					for(var i = 0; i < value.length; i++)
					{
						obj[i] = value[i];
					}

					BX.util.addObjectToForm(obj, form, name);
				}
				else if(BX.type.isPlainObject(value))
				{
					BX.util.addObjectToForm(value, form, name);
				}
				else
				{
					value = BX.type.isFunction(value.toString) ? value.toString() : "";
					if(value !== "")
					{
						form.appendChild(BX.create("INPUT", { attrs: { type: "hidden", name: name, value: value } }));
					}
				}
			}
		},

		observe: function(object, enable)
		{
			console.error('BX.util.observe: function is no longer supported by browser.');
			return false;
		},

		escapeRegExp: function(str)
		{
			return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
		}
	};

	BX.validation = {
		checkIfEmail: function(s)
		{
			var atom = "[=a-z0-9_+~'!$&*^`|#%/?{}-]";
			return (new RegExp('^\\s*'+atom+'+(\\.'+atom+'+)*@([a-z0-9-]+\\.)+[a-z0-9-]{2,20}\\s*$', 'i')).test(s);
		},
		checkIfPhone: function(s)
		{
			var regexp = new RegExp(
				typeof(BX.PhoneNumber) === "undefined"
					? BX.PhoneNumber.getValidNumberPattern()
					: '^\\s*\\+?\s*[0-9(-)\\s]+\\s*$',
				'i'
			);
			return regexp.test(s);
		}
	};

	BX.prop =
		{
			get: function(object, key, defaultValue)
			{
				return object && object.hasOwnProperty(key) ? object[key] : defaultValue;
			},
			getObject: function(object, key, defaultValue)
			{
				return object && BX.type.isPlainObject(object[key]) ? object[key] : defaultValue;
			},
			getElementNode: function(object, key, defaultValue)
			{
				return object && BX.type.isElementNode(object[key]) ? object[key] : defaultValue;
			},
			getArray: function(object, key, defaultValue)
			{
				return object && BX.type.isArray(object[key]) ? object[key] : defaultValue;
			},
			getFunction: function(object, key, defaultValue)
			{
				return object && BX.type.isFunction(object[key]) ? object[key] : defaultValue;
			},
			getNumber: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				if(BX.type.isNumber(value))
				{
					return value;
				}

				value = parseFloat(value);
				return !isNaN(value) ? value : defaultValue;
			},
			getInteger: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				if(BX.type.isNumber(value))
				{
					return value;
				}

				value = parseInt(value);
				return !isNaN(value) ? value : defaultValue;
			},
			getBoolean: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				return (BX.type.isBoolean(value)
						? value
						: (BX.type.isString(value) ? (value.toLowerCase() === "true") : !!value)
				);
			},
			getString: function(object, key, defaultValue)
			{
				if(!(object && object.hasOwnProperty(key)))
				{
					return defaultValue;
				}

				var value = object[key];
				return BX.type.isString(value) ? value : (value ? value.toString() : '');
			},
			extractDate: function(datetime)
			{
				if(!BX.type.isDate(datetime))
				{
					datetime = new Date();
				}

				datetime.setHours(0);
				datetime.setMinutes(0);
				datetime.setSeconds(0);
				datetime.setMilliseconds(0);

				return datetime;
			}
		};

	BX.isNodeInDom = function(node, doc)
	{
		return node === (doc || document) ? true :
			(node.parentNode ? BX.isNodeInDom(node.parentNode) : false);
	};

	BX.isNodeHidden = function(node)
	{
		if (node === document)
			return false;
		else if (BX.style(node, 'display') == 'none')
			return true;
		else
			return (node.parentNode ? BX.isNodeHidden(node.parentNode) : true);
	};

	BX.evalPack = function(code)
	{
		while (code.length > 0)
		{
			var c = code.shift();

			if (c.TYPE == 'SCRIPT_EXT' || c.TYPE == 'SCRIPT_SRC')
			{
				BX.loadScript(c.DATA, function() {BX.evalPack(code)});
				return;
			}
			else if (c.TYPE == 'SCRIPT')
			{
				BX.evalGlobal(c.DATA);
			}
		}
	};

	BX.evalGlobal = function(data)
	{
		if (data)
		{
			var head = document.getElementsByTagName("head")[0] || document.documentElement,
				script = document.createElement("script");

			script.type = "text/javascript";

			if (!BX.browser.IsIE())
			{
				script.appendChild(document.createTextNode(data));
			}
			else
			{
				script.text = data;
			}

			head.insertBefore(script, head.firstChild);
			head.removeChild(script);
		}
	};

	BX.garbage = function(call, thisObject)
	{
		garbageCollectors.push({callback: call, context: thisObject});
	};

	BX.GetDocElement = function (pDoc)
	{
		pDoc = pDoc || document;
		return (BX.browser.IsDoctype(pDoc) ? pDoc.documentElement : pDoc.body);
	};

	BX.scrollTop = function(node, val){
		if(typeof val != 'undefined'){

			if(node == window){
				throw new Error('scrollTop() for window is not implemented');
			}else
				node.scrollTop = parseInt(val);

		}else{

			if(node == window)
				return BX.GetWindowScrollPos().scrollTop;

			return node.scrollTop;
		}
	};

	BX.scrollLeft = function(node, val){
		if(typeof val != 'undefined'){

			if(node == window){
				throw new Error('scrollLeft() for window is not implemented');
			}else
				node.scrollLeft = parseInt(val);

		}else{

			if(node == window)
				return BX.GetWindowScrollPos().scrollLeft;

			return node.scrollLeft;
		}
	};

	BX.hide_object = function(ob)
	{
		ob = BX(ob);
		ob.style.position = 'absolute';
		ob.style.top = '-1000px';
		ob.style.left = '-1000px';
		ob.style.height = '10px';
		ob.style.width = '10px';
	};

	BX.is_relative = function(el)
	{
		var p = BX.style(el, 'position');
		return p == 'relative' || p == 'absolute';
	};

	BX.is_float = function(el)
	{
		var p = BX.style(el, 'float');
		return p == 'right' || p == 'left';
	};

	BX.is_fixed = function(el)
	{
		var p = BX.style(el, 'position');
		return p == 'fixed';
	};

	BX.width = function(node, val){
		if(typeof val != 'undefined')
			BX.style(node, 'width', parseInt(val)+'px');
		else{

			if(node == window)
				return window.innerWidth;

			//return parseInt(BX.style(node, 'width'));
			return BX.pos(node).width;
		}
	};

	BX.height = function(node, val){
		if(typeof val != 'undefined')
			BX.style(node, 'height', parseInt(val)+'px');
		else{

			if(node == window)
				return window.innerHeight;

			//return parseInt(BX.style(node, 'height'));
			return BX.pos(node).height;
		}
	};

	BX.align = function(pos, w, h, type)
	{
		if (type)
			type = type.toLowerCase();
		else
			type = '';

		var pDoc = document;
		if (BX.type.isElementNode(pos))
		{
			pDoc = pos.ownerDocument;
			pos = BX.pos(pos);
		}

		var x = pos["left"], y = pos["bottom"];

		var scroll = BX.GetWindowScrollPos(pDoc);
		var size = BX.GetWindowInnerSize(pDoc);

		if((size.innerWidth + scroll.scrollLeft) - (pos["left"] + w) < 0)
		{
			if(pos["right"] - w >= 0 )
				x = pos["right"] - w;
			else
				x = scroll.scrollLeft;
		}

		if(((size.innerHeight + scroll.scrollTop) - (pos["bottom"] + h) < 0) || ~type.indexOf('top'))
		{
			if(pos["top"] - h >= 0 || ~type.indexOf('top'))
				y = pos["top"] - h;
			else
				y = scroll.scrollTop;
		}

		return {'left':x, 'top':y};
	};

	BX.scrollToNode = function(node)
	{
		var obNode = BX(node);

		if (obNode.scrollIntoView)
			obNode.scrollIntoView(true);
		else
		{
			var arNodePos = BX.pos(obNode);
			window.scrollTo(arNodePos.left, arNodePos.top);
		}
	};

	/* non-xhr loadings */
	BX.showWait = function(node, msg)
	{
		node = BX(node) || document.body || document.documentElement;
		msg = msg || BX.message('JS_CORE_LOADING');

		var container_id = node.id || Math.random();

		var obMsg = node.bxmsg = document.body.appendChild(BX.create('DIV', {
			props: {
				id: 'wait_' + container_id
			},
			style: {
				background: 'url("/bitrix/js/main/core/images/wait.gif") no-repeat scroll 10px center #fcf7d1',
				border: '1px solid #E1B52D',
				color: 'black',
				fontFamily: 'Verdana,Arial,sans-serif',
				fontSize: '11px',
				padding: '10px 30px 10px 37px',
				position: 'absolute',
				zIndex:'10000',
				textAlign:'center'
			},
			text: msg
		}));

		setTimeout(BX.delegate(_adjustWait, node), 10);

		lastWait[lastWait.length] = obMsg;
		return obMsg;
	};

	BX.closeWait = function(node, obMsg)
	{
		if(node && !obMsg)
			obMsg = node.bxmsg;
		if(node && !obMsg && BX.hasClass(node, 'bx-core-waitwindow'))
			obMsg = node;
		if(node && !obMsg)
			obMsg = BX('wait_' + node.id);
		if(!obMsg)
			obMsg = lastWait.pop();

		if (obMsg && obMsg.parentNode)
		{
			for (var i=0,len=lastWait.length;i<len;i++)
			{
				if (obMsg == lastWait[i])
				{
					lastWait = BX.util.deleteFromArray(lastWait, i);
					break;
				}
			}

			obMsg.parentNode.removeChild(obMsg);
			if (node) node.bxmsg = null;
			BX.cleanNode(obMsg, true);
		}
	};

	BX.setJSList = function(scripts)
	{
		if (BX.type.isArray(scripts))
		{
			scripts = scripts.map(function(script) {
				return normalizeUrl(script)
			});

			jsList = jsList.concat(scripts);
		}
	};

	BX.getJSList = function()
	{
		initJsList();
		return jsList;
	};

	BX.setCSSList = function(cssFiles)
	{
		if (BX.type.isArray(cssFiles))
		{
			cssFiles = cssFiles.map(function(cssFile) {
				return normalizeUrl(cssFile);
			});

			cssList = cssList.concat(cssFiles);
		}
	};

	BX.getCSSList = function()
	{
		initCssList();
		return cssList;
	};

	BX.getJSPath = function(js)
	{
		return js.replace(/^(http[s]*:)*\/\/[^\/]+/i, '');
	};

	BX.getCSSPath = function(css)
	{
		return css.replace(/^(http[s]*:)*\/\/[^\/]+/i, '');
	};

	BX.getCDNPath = function(path)
	{
		return path;
	};

	BX.loadScript = function(script, callback, doc)
	{
		if (BX.type.isString(script))
		{
			script = [script];
		}

		return BX.load(script, callback, doc);
	};

	BX.loadCSS = function(css, doc, win)
	{
		if (BX.type.isString(css))
		{
			css = [css];
		}

		if (BX.type.isArray(css))
		{
			css = css.map(function(url) {
				return { url: url, ext: "css" }
			});

			BX.load(css, null, doc);
		}
	};

	BX.load = function(items, callback, doc)
	{
		if (!BX.isReady)
		{
			var _args = arguments;
			BX.ready(function() {
				BX.load.apply(this, _args);
			});
			return null;
		}

		doc = doc || document;
		if (isAsync === null)
		{
			isAsync = "async" in doc.createElement("script") || "MozAppearance" in doc.documentElement.style || window.opera;
		}

		return isAsync ? loadAsync(items, callback, doc) : loadAsyncEmulation(items, callback, doc);
	};

	BX.convert =
		{
			toNumber: function(value)
			{
				if(BX.type.isNumber(value))
				{
					return value;
				}

				value = Number(value);
				return !isNaN(value) ? value : 0;
			},
			nodeListToArray: function(nodes)
			{
				try
				{
					return (Array.prototype.slice.call(nodes, 0));
				}
				catch (ex)
				{
					var ary = [];
					for(var i = 0, l = nodes.length; i < l; i++)
					{
						ary.push(nodes[i]);
					}
					return ary;
				}
			}
		};

	function loadAsync(items, callback, doc)
	{
		if (!BX.type.isArray(items))
		{
			return;
		}

		function allLoaded(items)
		{
			items = items || assets;
			for (var name in items)
			{
				if (items.hasOwnProperty(name) && items[name].state !== LOADED)
				{
					return false;
				}
			}

			return true;
		}

		if (!BX.type.isFunction(callback))
		{
			callback = null;
		}

		var itemSet = {}, item, i;
		for (i = 0; i < items.length; i++)
		{
			item = items[i];
			item = getAsset(item);
			itemSet[item.name] = item;
		}

		var callbackWasCalled = false;
		if (items.length > 0)
		{
			for (i = 0; i < items.length; i++)
			{
				item = items[i];
				item = getAsset(item);
				load(item, function () {
					if (allLoaded(itemSet))
					{
						if (!callbackWasCalled)
						{
							callback && callback();
							callbackWasCalled = true;
						}

					}
				}, doc);
			}
		}
		else
		{
			if (typeof callback === 'function')
			{
				callback();
				callbackWasCalled = true;
			}
		}
	}

	function loadAsyncEmulation(items, callback, doc)
	{
		function onPreload(asset)
		{
			asset.state = PRELOADED;
			if (BX.type.isArray(asset.onpreload) && asset.onpreload)
			{
				for (var i = 0; i < asset.onpreload.length; i++)
				{
					asset.onpreload[i].call();
				}
			}
		}

		function preLoad(asset)
		{
			if (asset.state === undefined)
			{
				asset.state = PRELOADING;
				asset.onpreload = [];

				loadAsset(
					{ url: asset.url, type: "cache", ext: asset.ext},
					function () { onPreload(asset); },
					doc
				);
			}
		}

		if (!BX.type.isArray(items))
		{
			return;
		}

		if (!BX.type.isFunction(callback))
		{
			callback = null;
		}

		var rest = [].slice.call(items, 1);
		for (var i = 0; i < rest.length; i++)
		{
			preLoad(getAsset(rest[i]));
		}

		load(getAsset(items[0]), items.length === 1 ? callback : function () {
			loadAsyncEmulation.apply(null, [rest, callback, doc]);
		}, doc);
	}

	function load(asset, callback, doc)
	{
		callback = callback || BX.DoNothing;

		if (asset.state === LOADED)
		{
			callback();
			return;
		}

		if (asset.state === PRELOADING)
		{
			asset.onpreload.push(function () {
				load(asset, callback, doc);
			});
			return;
		}

		asset.state = LOADING;

		loadAsset(
			asset,
			function () {
				asset.state = LOADED;
				callback();
			},
			doc
		);
	}

	function loadAsset(asset, callback, doc)
	{
		callback = callback || BX.DoNothing;

		function error(event)
		{
			ele.onload = ele.onreadystatechange = ele.onerror = null;
			callback();
		}

		function process(event)
		{
			event = event || window.event;
			if (event.type === "load" || (/loaded|complete/.test(ele.readyState) && (!doc.documentMode || doc.documentMode < 9)))
			{
				window.clearTimeout(asset.errorTimeout);
				window.clearTimeout(asset.cssTimeout);
				ele.onload = ele.onreadystatechange = ele.onerror = null;
				callback();
			}
		}

		function isCssLoaded()
		{
			if (asset.state !== LOADED && asset.cssRetries <= 20)
			{
				for (var i = 0, l = doc.styleSheets.length; i < l; i++)
				{
					if (doc.styleSheets[i].href === ele.href)
					{
						process({"type": "load"});
						return;
					}
				}

				asset.cssRetries++;
				asset.cssTimeout = window.setTimeout(isCssLoaded, 250);
			}
		}

		var ele;
		var ext = BX.type.isNotEmptyString(asset.ext) ? asset.ext : BX.util.getExtension(asset.url);

		if (ext === "css")
		{
			ele = doc.createElement("link");
			ele.type = "text/" + (asset.type || "css");
			ele.rel = "stylesheet";
			ele.href = asset.url;

			asset.cssRetries = 0;
			asset.cssTimeout = window.setTimeout(isCssLoaded, 500);
		}
		else
		{
			ele = doc.createElement("script");
			ele.type = "text/" + (asset.type || "javascript");
			ele.src = asset.url;
		}

		ele.onload = ele.onreadystatechange = process;
		ele.onerror = error;

		ele.async = false;
		ele.defer = false;

		asset.errorTimeout = window.setTimeout(function () {
			error({type: "timeout"});
		}, 7000);

		if (ext === "css")
		{
			cssList.push(normalizeMinUrl(normalizeUrl(asset.url)));
		}
		else
		{
			jsList.push(normalizeMinUrl(normalizeUrl(asset.url)));
		}

		var templateLink = null;
		var head = doc.head || doc.getElementsByTagName("head")[0];
		if (ext === "css" && (templateLink = getTemplateLink(head)) !== null)
		{
			templateLink.parentNode.insertBefore(ele, templateLink);
		}
		else
		{
			head.insertBefore(ele, head.lastChild);
		}
	}

	function getAsset(item)
	{
		var asset = {};
		if (typeof item === "object")
		{
			asset = item;
			asset.name = asset.name ? asset.name : BX.util.hashCode(item.url);
		}
		else
		{
			asset = { name: BX.util.hashCode(item), url : item };
		}

		var ext = BX.type.isNotEmptyString(asset.ext) ? asset.ext : BX.util.getExtension(asset.url);
		if ((ext === "css" && isCssLoaded(asset.url)) || isScriptLoaded(asset.url))
		{
			asset.state = LOADED;
		}

		var existing = assets[asset.name];
		if (existing && existing.url === asset.url)
		{
			return existing;
		}

		assets[asset.name] = asset;
		return asset;
	}

	function normalizeUrl(url)
	{
		if (!BX.type.isNotEmptyString(url))
		{
			return "";
		}

		url = BX.getJSPath(url);
		url = url.replace(/\?[0-9]*$/, "");

		return url;
	}

	function normalizeMinUrl(url)
	{
		if (!BX.type.isNotEmptyString(url))
		{
			return "";
		}

		var minPos = url.indexOf(".min");
		return minPos >= 0 ? url.substr(0, minPos) + url.substr(minPos + 4) : url;
	}

	function isCssLoaded(fileSrc)
	{
		initCssList();

		fileSrc = normalizeUrl(fileSrc);
		var fileSrcMin = normalizeMinUrl(fileSrc);

		return (fileSrc !== fileSrcMin && BX.util.in_array(fileSrcMin, cssList)) || BX.util.in_array(fileSrc, cssList);
	}

	function initCssList()
	{
		if(!cssInit)
		{
			var linksCol = document.getElementsByTagName('link');

			if(!!linksCol && linksCol.length > 0)
			{
				for(var i = 0; i < linksCol.length; i++)
				{
					var href = linksCol[i].getAttribute('href');
					if (BX.type.isNotEmptyString(href))
					{
						href = normalizeMinUrl(normalizeUrl(href));
						cssList.push(href);
					}
				}
			}
			cssInit = true;
		}
	}

	function getTemplateLink(head)
	{
		var findLink = function(tag)
		{
			var links = head.getElementsByTagName(tag);
			for (var i = 0, length = links.length; i < length; i++)
			{
				var templateStyle = links[i].getAttribute("data-template-style");
				if (BX.type.isNotEmptyString(templateStyle) && templateStyle == "true")
				{
					return links[i];
				}
			}

			return null;
		};

		var link = findLink("link");
		if (link === null)
		{
			link = findLink("style");
		}

		return link;
	}

	function isScriptLoaded(fileSrc)
	{
		initJsList();

		fileSrc = normalizeUrl(fileSrc);
		var fileSrcMin = normalizeMinUrl(fileSrc);

		return (fileSrc !== fileSrcMin && BX.util.in_array(fileSrcMin, jsList)) || BX.util.in_array(fileSrc, jsList);
	}

	function initJsList()
	{
		if(!jsInit)
		{
			var scriptCol = document.getElementsByTagName('script');

			if(!!scriptCol && scriptCol.length > 0)
			{
				for(var i=0; i<scriptCol.length; i++)
				{
					var src = scriptCol[i].getAttribute('src');

					if (BX.type.isNotEmptyString(src))
					{
						src = normalizeMinUrl(normalizeUrl(src));
						jsList.push(src);
					}
				}
			}
			jsInit = true;
		}
	}

	function reloadInternal(back_url, bAddClearCache)
	{
		if (back_url === true)
		{
			bAddClearCache = true;
			back_url = null;
		}

		var topWindow = BX.PageObject.getRootWindow();
		var new_href = back_url || topWindow.location.href;

		var hashpos = new_href.indexOf('#'), hash = '';

		if (hashpos != -1)
		{
			hash = new_href.substr(hashpos);
			new_href = new_href.substr(0, hashpos);
		}

		if (bAddClearCache && new_href.indexOf('clear_cache=Y') < 0)
			new_href += (new_href.indexOf('?') == -1 ? '?' : '&') + 'clear_cache=Y';

		if (hash)
		{
			// hack for clearing cache in ajax mode components with history emulation
			if (bAddClearCache && (hash.substr(0, 5) == 'view/' || hash.substr(0, 6) == '#view/') && hash.indexOf('clear_cache%3DY') < 0)
				hash += (hash.indexOf('%3F') == -1 ? '%3F' : '%26') + 'clear_cache%3DY';

			new_href = new_href.replace(/(\?|\&)_r=[\d]*/, '');
			new_href += (new_href.indexOf('?') == -1 ? '?' : '&') + '_r='+Math.round(Math.random()*10000) + hash;
		}

		topWindow.location.href = new_href;
	}

	BX.reload = function(back_url, bAddClearCache)
	{
		if (window !== window.top)
		{
			BX.Runtime
				.loadExtension('main.pageobject')
				.then(function() {
					reloadInternal(back_url, bAddClearCache);
				});
		}
		else
		{
			reloadInternal(back_url, bAddClearCache);
		}
	};

	BX.clearCache = function()
	{
		BX.showWait();
		BX.reload(true);
	};

	BX.template = function(tpl, callback, bKillTpl)
	{
		BX.ready(function() {
			_processTpl(BX(tpl), callback, bKillTpl);
		});
	};

	BX.isAmPmMode = function(returnConst)
	{
		if (returnConst === true)
		{
			return BX.message.AMPM_MODE;
		}
		return BX.message.AMPM_MODE !== false;
	};

	BX.formatDate = function(date, format)
	{
		date = date || new Date();

		var bTime = date.getHours() || date.getMinutes() || date.getSeconds(),
			str = !!format
				? format :
				(bTime ? BX.message('FORMAT_DATETIME') : BX.message('FORMAT_DATE')
				);

		return str.replace(/YYYY/ig, date.getFullYear())
			.replace(/MMMM/ig, BX.util.str_pad_left((date.getMonth()+1).toString(), 2, '0'))
			.replace(/MM/ig, BX.util.str_pad_left((date.getMonth()+1).toString(), 2, '0'))
			.replace(/DD/ig, BX.util.str_pad_left(date.getDate().toString(), 2, '0'))
			.replace(/HH/ig, BX.util.str_pad_left(date.getHours().toString(), 2, '0'))
			.replace(/MI/ig, BX.util.str_pad_left(date.getMinutes().toString(), 2, '0'))
			.replace(/SS/ig, BX.util.str_pad_left(date.getSeconds().toString(), 2, '0'));
	};
	BX.formatName = function(user, template, login)
	{
		user = user || {};
		template = (template || '');
		var replacement = {
			TITLE : (user["TITLE"] || ''),
			NAME : (user["NAME"] || ''),
			LAST_NAME : (user["LAST_NAME"] || ''),
			SECOND_NAME : (user["SECOND_NAME"] || ''),
			LOGIN : (user["LOGIN"] || ''),
			NAME_SHORT : user["NAME"] ? user["NAME"].substr(0, 1) + '.' : '',
			LAST_NAME_SHORT : user["LAST_NAME"] ? user["LAST_NAME"].substr(0, 1) + '.' : '',
			SECOND_NAME_SHORT : user["SECOND_NAME"] ? user["SECOND_NAME"].substr(0, 1) + '.' : '',
			EMAIL : (user["EMAIL"] || ''),
			ID : (user["ID"] || ''),
			NOBR : "",
			'/NOBR' : ""
		}, result = template;
		for (var ii in replacement)
		{
			if (replacement.hasOwnProperty(ii))
			{
				result = result.replace("#" + ii+ "#", replacement[ii])
			}
		}
		result = result.replace(/([\s]+)/gi, " ").trim();
		if (result == "")
		{
			result = (login == "Y" ? replacement["LOGIN"] : "");
			result = (result == "" ? "Noname" : result);
		}
		return result;
	};

	BX.getNumMonth = function(month)
	{
		var wordMonthCut = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
		var wordMonth = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

		var q = month.toUpperCase();
		for (i = 1; i <= 12; i++)
		{
			if (q == BX.message('MON_'+i).toUpperCase() || q == BX.message('MONTH_'+i).toUpperCase() || q == wordMonthCut[i-1].toUpperCase() || q == wordMonth[i-1].toUpperCase())
			{
				return i;
			}
		}
		return month;
	};

	BX.parseDate = function(str, bUTC, formatDate, formatDatetime)
	{
		if (BX.type.isNotEmptyString(str))
		{
			if (!formatDate)
				formatDate = BX.message('FORMAT_DATE');
			if (!formatDatetime)
				formatDatetime = BX.message('FORMAT_DATETIME');

			var regMonths = '';
			for (i = 1; i <= 12; i++)
			{
				regMonths = regMonths + '|' + BX.message('MON_'+i);
			}

			var expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig');
			var aDate = str.match(expr),
				aFormat = formatDate.match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
				i, cnt,
				aDateArgs=[], aFormatArgs=[],
				aResult={};

			if (!aDate)
				return null;

			if(aDate.length > aFormat.length)
			{
				aFormat = formatDatetime.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
			}

			for(i = 0, cnt = aDate.length; i < cnt; i++)
			{
				if(BX.util.trim(aDate[i]) != '')
				{
					aDateArgs[aDateArgs.length] = aDate[i];
				}
			}

			for(i = 0, cnt = aFormat.length; i < cnt; i++)
			{
				if(BX.util.trim(aFormat[i]) != '')
				{
					aFormatArgs[aFormatArgs.length] = aFormat[i];
				}
			}


			var m = BX.util.array_search('MMMM', aFormatArgs);
			if (m > 0)
			{
				aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
				aFormatArgs[m] = "MM";
			}
			else
			{
				m = BX.util.array_search('M', aFormatArgs);
				if (m > 0)
				{
					aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
					aFormatArgs[m] = "MM";
				}
			}

			for(i = 0, cnt = aFormatArgs.length; i < cnt; i++)
			{
				var k = aFormatArgs[i].toUpperCase();
				aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
			}

			if(aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0)
			{
				var d = new Date();

				if(bUTC)
				{
					d.setUTCDate(1);
					d.setUTCFullYear(aResult['YYYY']);
					d.setUTCMonth(aResult['MM'] - 1);
					d.setUTCDate(aResult['DD']);
					d.setUTCHours(0, 0, 0, 0);
				}
				else
				{
					d.setDate(1);
					d.setFullYear(aResult['YYYY']);
					d.setMonth(aResult['MM'] - 1);
					d.setDate(aResult['DD']);
					d.setHours(0, 0, 0, 0);
				}

				if(
					(!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G']))
					&& !isNaN(aResult['MI'])
				)
				{
					if (!isNaN(aResult['H']) || !isNaN(aResult['G']))
					{
						var bPM = (aResult['T']||aResult['TT']||'am').toUpperCase()=='PM';
						var h = parseInt(aResult['H']||aResult['G']||0, 10);
						if(bPM)
						{
							aResult['HH'] = h + (h == 12 ? 0 : 12);
						}
						else
						{
							aResult['HH'] = h < 12 ? h : 0;
						}
					}
					else
					{
						aResult['HH'] = parseInt(aResult['HH']||aResult['GG']||0, 10);
					}

					if (isNaN(aResult['SS']))
						aResult['SS'] = 0;

					if(bUTC)
					{
						d.setUTCHours(aResult['HH'], aResult['MI'], aResult['SS']);
					}
					else
					{
						d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
					}
				}

				return d;
			}
		}

		return null;
	};

	BX.selectUtils =
		{
			addNewOption: function(oSelect, opt_value, opt_name, do_sort, check_unique)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var n = oSelect.length;
					if(check_unique !== false)
					{
						for(var i=0;i<n;i++)
						{
							if(oSelect[i].value==opt_value)
							{
								return;
							}
						}
					}

					oSelect.options[n] = new Option(opt_name, opt_value, false, false);
				}

				if(do_sort === true)
				{
					this.sortSelect(oSelect);
				}
			},

			deleteOption: function(oSelect, opt_value)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					for(var i=0;i<oSelect.length;i++)
					{
						if(oSelect[i].value==opt_value)
						{
							oSelect.remove(i);
							break;
						}
					}
				}
			},

			deleteSelectedOptions: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var i=0;
					while(i<oSelect.length)
					{
						if(oSelect[i].selected)
						{
							oSelect[i].selected=false;
							oSelect.remove(i);
						}
						else
						{
							i++;
						}
					}
				}
			},

			deleteAllOptions: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					for(var i=oSelect.length-1; i>=0; i--)
					{
						oSelect.remove(i);
					}
				}
			},

			optionCompare: function(record1, record2)
			{
				var value1 = record1.optText.toLowerCase();
				var value2 = record2.optText.toLowerCase();
				if (value1 > value2) return(1);
				if (value1 < value2) return(-1);
				return(0);
			},

			sortSelect: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var myOptions = [];
					var n = oSelect.options.length;
					var i;
					for (i=0;i<n;i++)
					{
						myOptions[i] = {
							optText:oSelect[i].text,
							optValue:oSelect[i].value
						};
					}
					myOptions.sort(this.optionCompare);
					oSelect.length=0;
					n = myOptions.length;
					for(i=0;i<n;i++)
					{
						oSelect[i] = new Option(myOptions[i].optText, myOptions[i].optValue, false, false);
					}
				}
			},

			selectAllOptions: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var n = oSelect.length;
					for(var i=0;i<n;i++)
					{
						oSelect[i].selected=true;
					}
				}
			},

			selectOption: function(oSelect, opt_value)
			{
				oSelect = BX(oSelect);
				if(oSelect)
				{
					var n = oSelect.length;
					for(var i=0;i<n;i++)
					{
						oSelect[i].selected = (oSelect[i].value == opt_value);
					}
				}
			},

			addSelectedOptions: function(oSelect, to_select_id, check_unique, do_sort)
			{
				oSelect = BX(oSelect);
				if(!oSelect)
					return;
				var n = oSelect.length;
				for(var i=0; i<n; i++)
					if(oSelect[i].selected)
						this.addNewOption(to_select_id, oSelect[i].value, oSelect[i].text, do_sort, check_unique);
			},

			moveOptionsUp: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(!oSelect)
					return;
				var n = oSelect.length;
				for(var i=0; i<n; i++)
				{
					if(oSelect[i].selected && i>0 && oSelect[i-1].selected == false)
					{
						var option = new Option(oSelect[i].text, oSelect[i].value);
						oSelect[i] = new Option(oSelect[i-1].text, oSelect[i-1].value);
						oSelect[i].selected = false;
						oSelect[i-1] = option;
						oSelect[i-1].selected = true;
					}
				}
			},

			moveOptionsDown: function(oSelect)
			{
				oSelect = BX(oSelect);
				if(!oSelect)
					return;
				var n = oSelect.length;
				for(var i=n-1; i>=0; i--)
				{
					if(oSelect[i].selected && i<n-1 && oSelect[i+1].selected == false)
					{
						var option = new Option(oSelect[i].text, oSelect[i].value);
						oSelect[i] = new Option(oSelect[i+1].text, oSelect[i+1].value);
						oSelect[i].selected = false;
						oSelect[i+1] = option;
						oSelect[i+1].selected = true;
					}
				}
			}
		};

	BX.getEventTarget = function(e)
	{
		if(e.target)
		{
			return e.target;
		}
		else if(e.srcElement)
		{
			return e.srcElement;
		}
		return null;
	};

	/******* HINT ***************/
// if function has 2 params - the 2nd one is hint html. otherwise hint_html is third and hint_title - 2nd;
// '<div onmouseover="BX.hint(this, 'This is &lt;b&gt;Hint&lt;/b&gt;')"'>;
// BX.hint(el, 'This is <b>Hint</b>') - this won't work, use constructor
	BX.hint = function(el, hint_title, hint_html, hint_id)
	{
		if (null == hint_html)
		{
			hint_html = hint_title;
			hint_title = '';
		}

		if (null == el.BXHINT)
		{
			el.BXHINT = new BX.CHint({
				parent: el, hint: hint_html, title: hint_title, id: hint_id
			});
			el.BXHINT.Show();
		}
	};

	BX.hint_replace = function(el, hint_title, hint_html)
	{
		if (null == hint_html)
		{
			hint_html = hint_title;
			hint_title = '';
		}

		if (!el || !el.parentNode || !hint_html)
			return null;

		var obHint = new BX.CHint({
			hint: hint_html,
			title: hint_title
		});

		obHint.CreateParent();

		el.parentNode.insertBefore(obHint.PARENT, el);
		el.parentNode.removeChild(el);

		obHint.PARENT.style.marginLeft = '5px';

		return el;
	};

	BX.CHint = function(params)
	{
		this.PARENT = BX(params.parent);

		this.HINT = params.hint;
		this.HINT_TITLE = params.title;

		this.PARAMS = {};
		for (var i in this.defaultSettings)
		{
			if (null == params[i])
				this.PARAMS[i] = this.defaultSettings[i];
			else
				this.PARAMS[i] = params[i];
		}

		if (null != params.id)
			this.ID = params.id;

		this.timer = null;
		this.bInited = false;
		this.msover = true;

		if (this.PARAMS.showOnce)
		{
			this.__show();
			this.msover = false;
			this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
		}
		else if (this.PARENT)
		{
			BX.bind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
			BX.bind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
		}
	};

	BX.CHint.openHints = new Set();

	BX.CHint.globalDisabled = false;

	BX.CHint.handleMenuOpen = function() {
		BX.CHint.globalDisabled = true;

		BX.CHint.openHints.forEach(function(hint) {
			hint.__hide_immediately();
		});
	};

	BX.CHint.handleMenuClose = function() {
		BX.CHint.globalDisabled = false;
	};

	BX.addCustomEvent('onMenuOpen', BX.CHint.handleMenuOpen);
	BX.addCustomEvent('onMenuClose', BX.CHint.handleMenuClose);

	BX.CHint.prototype.defaultSettings = {
		show_timeout: 1000,
		hide_timeout: 500,
		dx: 2,
		showOnce: false,
		preventHide: true,
		min_width: 250
	};

	BX.CHint.prototype.CreateParent = function(element, params)
	{
		if (this.PARENT)
		{
			BX.unbind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
			BX.unbind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
		}

		if (!params) params = {};
		var type = 'icon';

		if (params.type && (params.type == "link" || params.type == "icon"))
			type = params.type;

		if (element)
			type = "element";

		if (type == "icon")
		{
			element = BX.create('IMG', {
				props: {
					src: params.iconSrc
						? params.iconSrc
						: "/bitrix/js/main/core/images/hint.gif"
				}
			});
		}
		else if (type == "link")
		{
			element = BX.create("A", {
				props: {href: 'javascript:void(0)'},
				html: '[?]'
			});
		}

		this.PARENT = element;

		BX.bind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
		BX.bind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));

		return this.PARENT;
	};

	BX.CHint.prototype.Show = function()
	{
		this.msover = true;

		if (null != this.timer)
			clearTimeout(this.timer);

		this.timer = setTimeout(BX.proxy(this.__show, this), this.PARAMS.show_timeout);
	};

	BX.CHint.prototype.Hide = function()
	{
		this.msover = false;

		if (null != this.timer)
			clearTimeout(this.timer);

		this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
	};

	BX.CHint.prototype.__show = function()
	{
		if (!this.msover || this.disabled || BX.CHint.globalDisabled) return;
		if (!this.bInited) this.Init();

		if (this.prepareAdjustPos())
		{
			this.DIV.style.display = 'block';
			this.adjustPos();

			BX.CHint.openHints.add(this);

			BX.bind(window, 'scroll', BX.proxy(this.__onscroll, this));

			if (this.PARAMS.showOnce)
			{
				this.timer = setTimeout(BX.proxy(this.__hide, this), this.PARAMS.hide_timeout);
			}
		}
	};

	BX.CHint.prototype.__onscroll = function()
	{
		if (!BX.admin || !BX.admin.panel || !BX.admin.panel.isFixed()) return;

		if (this.scrollTimer) clearTimeout(this.scrollTimer);

		this.DIV.style.display = 'none';
		this.scrollTimer = setTimeout(BX.proxy(this.Reopen, this), this.PARAMS.show_timeout);
	};

	BX.CHint.prototype.Reopen = function()
	{
		if (null != this.timer) clearTimeout(this.timer);
		this.timer = setTimeout(BX.proxy(this.__show, this), 50);
	};

	BX.CHint.prototype.__hide = function()
	{
		if (this.msover) return;
		if (!this.bInited) return;

		BX.unbind(window, 'scroll', BX.proxy(this.Reopen, this));

		BX.CHint.openHints.delete(this);

		if (this.PARAMS.showOnce)
		{
			this.Destroy();
		}
		else
		{
			this.DIV.style.display = 'none';
		}
	};

	BX.CHint.prototype.__hide_immediately = function()
	{
		this.msover = false;
		this.__hide();
	};

	BX.CHint.prototype.Init = function()
	{
		this.DIV = document.body.appendChild(BX.create('DIV', {
			props: {className: 'bx-panel-tooltip'},
			style: {display: 'none'},
			children: [
				BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-top-border'},
					html: '<div class="bx-panel-tooltip-corner bx-panel-tooltip-left-corner"></div><div class="bx-panel-tooltip-border"></div><div class="bx-panel-tooltip-corner bx-panel-tooltip-right-corner"></div>'
				}),
				(this.CONTENT = BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-content'},
					children: [
						BX.create('DIV', {
							props: {className: 'bx-panel-tooltip-underlay'},
							children: [
								BX.create('DIV', {props: {className: 'bx-panel-tooltip-underlay-bg'}})
							]
						})
					]
				})),

				BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-bottom-border'},
					html: '<div class="bx-panel-tooltip-corner bx-panel-tooltip-left-corner"></div><div class="bx-panel-tooltip-border"></div><div class="bx-panel-tooltip-corner bx-panel-tooltip-right-corner"></div>'
				})
			]
		}));

		if (this.ID)
		{
			this.CONTENT.insertBefore(BX.create('A', {
				attrs: {href: 'javascript:void(0)'},
				props: {className: 'bx-panel-tooltip-close'},
				events: {click: BX.delegate(this.Close, this)}
			}), this.CONTENT.firstChild)
		}

		if (this.HINT_TITLE)
		{
			this.CONTENT.appendChild(
				BX.create('DIV', {
					props: {className: 'bx-panel-tooltip-title'},
					text: this.HINT_TITLE
				})
			)
		}

		if (this.HINT)
		{
			this.CONTENT_TEXT = this.CONTENT.appendChild(BX.create('DIV', {props: {className: 'bx-panel-tooltip-text'}})).appendChild(BX.create('SPAN', {html: this.HINT}));
		}

		if (this.PARAMS.preventHide)
		{
			BX.bind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));
			BX.bind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
		}

		this.bInited = true;
	};

	BX.CHint.prototype.setContent = function(content)
	{
		this.HINT = content;

		if (this.CONTENT_TEXT)
			this.CONTENT_TEXT.innerHTML = this.HINT;
		else
			this.CONTENT_TEXT = this.CONTENT.appendChild(BX.create('DIV', {props: {className: 'bx-panel-tooltip-text'}})).appendChild(BX.create('SPAN', {html: this.HINT}));
	};

	BX.CHint.prototype.prepareAdjustPos = function()
	{
		this._wnd = {scrollPos: BX.GetWindowScrollPos(),scrollSize:BX.GetWindowScrollSize()};
		return BX.style(this.PARENT, 'display') != 'none';
	};

	BX.CHint.prototype.getAdjustPos = function()
	{
		var res = {}, pos = BX.pos(this.PARENT), min_top = 0;

		res.top = pos.bottom + this.PARAMS.dx;

		if (BX.admin && BX.admin.panel.DIV)
		{
			min_top = BX.admin.panel.DIV.offsetHeight + this.PARAMS.dx;

			if (BX.admin.panel.isFixed())
			{
				min_top += this._wnd.scrollPos.scrollTop;
			}
		}

		if (res.top < min_top)
			res.top = min_top;
		else
		{
			if (res.top + this.DIV.offsetHeight > this._wnd.scrollSize.scrollHeight)
				res.top = pos.top - this.PARAMS.dx - this.DIV.offsetHeight;
		}

		res.left = pos.left;
		if (pos.left < this.PARAMS.dx)
			pos.left = this.PARAMS.dx;
		else
		{
			var floatWidth = this.DIV.offsetWidth;

			var max_left = this._wnd.scrollSize.scrollWidth - floatWidth - this.PARAMS.dx;

			if (res.left > max_left)
				res.left = max_left;
		}

		return res;
	};

	BX.CHint.prototype.adjustWidth = function()
	{
		if (this.bWidthAdjusted) return;

		var w = this.DIV.offsetWidth, h = this.DIV.offsetHeight;

		if (w > this.PARAMS.min_width)
			w = Math.round(Math.sqrt(1.618*w*h));

		if (w < this.PARAMS.min_width)
			w = this.PARAMS.min_width;

		this.DIV.style.width = w + "px";

		if (this._adjustWidthInt)
			clearInterval(this._adjustWidthInt);
		this._adjustWidthInt = setInterval(BX.delegate(this._adjustWidthInterval, this), 5);

		this.bWidthAdjusted = true;
	};

	BX.CHint.prototype._adjustWidthInterval = function()
	{
		if (!this.DIV || this.DIV.style.display == 'none')
			clearInterval(this._adjustWidthInt);

		var
			dW = 20,
			maxWidth = 1500,
			w = this.DIV.offsetWidth,
			w1 = this.CONTENT_TEXT.offsetWidth;

		if (w > 0 && w1 > 0 && w - w1 < dW && w < maxWidth)
		{
			this.DIV.style.width = (w + dW) + "px";
			return;
		}

		clearInterval(this._adjustWidthInt);
	};

	BX.CHint.prototype.adjustPos = function()
	{
		this.adjustWidth();

		var pos = this.getAdjustPos();

		this.DIV.style.top = pos.top + 'px';
		this.DIV.style.left = pos.left + 'px';
	};

	BX.CHint.prototype.Close = function()
	{
		if (this.ID && BX.WindowManager)
			BX.WindowManager.saveWindowOptions(this.ID, {display: 'off'});
		this.__hide_immediately();
		this.Destroy();
	};

	BX.CHint.prototype.Destroy = function()
	{
		if (this.PARENT)
		{
			BX.unbind(this.PARENT, 'mouseover', BX.proxy(this.Show, this));
			BX.unbind(this.PARENT, 'mouseout', BX.proxy(this.Hide, this));
		}

		if (this.DIV)
		{
			BX.unbind(this.DIV, 'mouseover', BX.proxy(this.Show, this));
			BX.unbind(this.DIV, 'mouseout', BX.proxy(this.Hide, this));

			BX.cleanNode(this.DIV, true);
		}
	};

	BX.CHint.prototype.enable = function(){this.disabled = false;};
	BX.CHint.prototype.disable = function(){this.__hide_immediately(); this.disabled = true;};


	function _adjustWait()
	{
		if (!this.bxmsg) return;

		var arContainerPos = BX.pos(this),
			div_top = arContainerPos.top;

		if (div_top < BX.GetDocElement().scrollTop)
			div_top = BX.GetDocElement().scrollTop + 5;

		this.bxmsg.style.top = (div_top + 5) + 'px';

		if (this == BX.GetDocElement())
		{
			this.bxmsg.style.right = '5px';
		}
		else
		{
			this.bxmsg.style.left = (arContainerPos.right - this.bxmsg.offsetWidth - 5) + 'px';
		}
	}

	function _processTpl(tplNode, cb, bKillTpl)
	{
		if (tplNode)
		{
			if (bKillTpl)
				tplNode.parentNode.removeChild(tplNode);

			var res = {}, nodes = BX.findChildren(tplNode, {attribute: 'data-role'}, true);

			for (var i = 0, l = nodes.length; i < l; i++)
			{
				res[nodes[i].getAttribute('data-role')] = nodes[i];
			}

			cb.apply(tplNode, [res]);
		}
	}

	function _checkNode(obj, params)
	{
		params = params || {};

		if (BX.type.isFunction(params))
			return params.call(window, obj);

		if (!params.allowTextNodes && !BX.type.isElementNode(obj))
			return false;
		var i,j,len;
		for (i in params)
		{
			if(params.hasOwnProperty(i))
			{
				switch(i)
				{
					case 'tag':
					case 'tagName':
						if (BX.type.isString(params[i]))
						{
							if (obj.tagName.toUpperCase() != params[i].toUpperCase())
								return false;
						}
						else if (params[i] instanceof RegExp)
						{
							if (!params[i].test(obj.tagName))
								return false;
						}
						break;

					case 'class':
					case 'className':
						if (BX.type.isString(params[i]))
						{
							if (!BX.hasClass(obj, params[i]))
								return false;
						}
						else if (params[i] instanceof RegExp)
						{
							if (!BX.type.isString(obj.className) || !params[i].test(obj.className))
								return false;
						}
						break;

					case 'attr':
					case 'attrs':
					case 'attribute':
						if (BX.type.isString(params[i]))
						{
							if (!obj.getAttribute(params[i]))
								return false;
						}
						else if (BX.type.isArray(params[i]))
						{
							for (j = 0, len = params[i].length; j < len; j++)
							{
								if (params[i] && !obj.getAttribute(params[i]))
									return false;
							}
						}
						else
						{
							for (j in params[i])
							{
								if(params[i].hasOwnProperty(j))
								{
									var q = obj.getAttribute(j);
									if (params[i][j] instanceof RegExp)
									{
										if (!BX.type.isString(q) || !params[i][j].test(q))
										{
											return false;
										}
									}
									else
									{
										if (q != '' + params[i][j])
										{
											return false;
										}
									}
								}
							}
						}
						break;

					case 'property':
					case 'props':
						if (BX.type.isString(params[i]))
						{
							if (!obj[params[i]])
								return false;
						}
						else if (BX.type.isArray(params[i]))
						{
							for (j = 0, len = params[i].length; j < len; j++)
							{
								if (params[i] && !obj[params[i]])
									return false;
							}
						}
						else
						{
							for (j in params[i])
							{
								if (BX.type.isString(params[i][j]))
								{
									if (obj[j] != params[i][j])
										return false;
								}
								else if (params[i][j] instanceof RegExp)
								{
									if (!BX.type.isString(obj[j]) || !params[i][j].test(obj[j]))
										return false;
								}
							}
						}
						break;

					case 'callback':
						return params[i](obj);
				}
			}
		}

		return true;
	}

	/* garbage collector */
	function Trash()
	{
		var i,len;

		for (i = 0, len = garbageCollectors.length; i<len; i++)
		{
			try {
				garbageCollectors[i].callback.apply(garbageCollectors[i].context || window);
				delete garbageCollectors[i];
				garbageCollectors[i] = null;
			} catch (e) {}
		}
	}

	if(window.attachEvent) // IE
		window.attachEvent("onunload", Trash);
	else if(window.addEventListener) // Gecko / W3C
		window.addEventListener('unload', Trash, false);
	else
		window.onunload = Trash;
	/* \garbage collector */

// set empty ready handler
	BX(BX.DoNothing);
	window.BX = BX;

	BX.browser.addGlobalClass();

	/* data storage */
	BX.data = function(node, key, value)
	{
		if(typeof node == 'undefined')
			return undefined;

		if(typeof key == 'undefined')
			return undefined;

		if(typeof value != 'undefined')
		{
			// write to manager
			dataStorage.set(node, key, value);
		}
		else
		{
			var data;

			// from manager
			if((data = dataStorage.get(node, key)) != undefined)
			{
				return data;
			}
			else
			{
				// from attribute data-*
				if('getAttribute' in node)
				{
					data = node.getAttribute('data-'+key.toString());
					if(data === null)
					{
						return undefined;
					}
					return data;
				}
			}

			return undefined;
		}
	};

	BX.DataStorage = function()
	{

		this.keyOffset = 1;
		this.data = {};
		this.uniqueTag = 'BX-'+Math.random();

		this.resolve = function(owner, create){
			if(typeof owner[this.uniqueTag] == 'undefined')
				if(create)
				{
					try
					{
						Object.defineProperty(owner, this.uniqueTag, {
							value: this.keyOffset++
						});
					}
					catch(e)
					{
						owner[this.uniqueTag] = this.keyOffset++;
					}
				}
				else
					return undefined;

			return owner[this.uniqueTag];
		};
		this.get = function(owner, key){
			if((owner != document && !BX.type.isElementNode(owner)) || typeof key == 'undefined')
				return undefined;

			owner = this.resolve(owner, false);

			if(typeof owner == 'undefined' || typeof this.data[owner] == 'undefined')
				return undefined;

			return this.data[owner][key];
		};
		this.set = function(owner, key, value){

			if((owner != document && !BX.type.isElementNode(owner)) || typeof value == 'undefined')
				return;

			var o = this.resolve(owner, true);

			if(typeof this.data[o] == 'undefined')
				this.data[o] = {};

			this.data[o][key] = value;
		};
	};

// some internal variables for new logic
	var dataStorage = new BX.DataStorage();	// manager which BX.data() uses to keep data
})(window.BX);