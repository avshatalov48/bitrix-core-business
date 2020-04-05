;(function() {

	"use strict";

	/** @module webpacker */
	/** @var {webpacker} webPacker */
	if(typeof webPacker === "undefined")
	{
		return;
	}

	var modules = [];
	function Module(name)
	{
		this.name = name;
		modules.push(this);
	}
	Module.prototype = {
		messages: {},
		properties: {},
		setProperties: function (props)
		{
			this.properties = props || {};
		},
		loadResources: function (resources)
		{
			return (resources || []).forEach(function (resource) {
				webPacker.resource.load(resource, this);
			}, this);
		},
		message: function (code)
		{
			return (code in this.messages) ? this.messages[code] : '';
		}
	};

	webPacker.getModule = function (name)
	{
		return this.getModules().filter(function (module) {
			return module.name === name;
		})[0];
	};
	webPacker.getModules = function ()
	{
		return modules;
	};
	webPacker.module = Module;
	webPacker.getAddress = function()
	{
		return this.address;
	};
	webPacker.resource = {
		load: function (resource, module)
		{
			switch (resource.type)
			{
				case 'css':
					this.loadCss(resource.content);
					break;

				case 'js':
					this.loadJs(resource.content || resource.src, !resource.content);
					break;

				case 'html':
				case 'layout':
					if (module)
					{
						for (var code in module.messages)
						{
							if (!module.messages.hasOwnProperty(code))
							{
								continue;
							}

							resource.content = resource.content.replace(
								new RegExp('%' + code + '%', 'g'),
								module.messages[code]
							);
						}
					}
					this.loadLayout(resource.content);
					break;
			}
		},
		loadLayout: function (content)
		{
			if (!content)
			{
				return;
			}

			var node = document.createElement('DIV');
			node.innerHTML = content;
			document.body.insertBefore(node, document.body.firstChild);
		},
		loadJs: function (content, isUrl, isRemove)
		{
			if (!content)
			{
				return;
			}

			var node = document.createElement('SCRIPT');
			node.setAttribute("type", "text/javascript");
			node.setAttribute("async", "");
			if (isUrl)
			{
				node.setAttribute("src", src);
			}
			else
			{
				if (webPacker.browser.isIE())
				{
					node.text = text;
				}
				else
				{
					node.appendChild(document.createTextNode(content));
				}
			}

			this.appendToHead(node, !isUrl && isRemove);
		},
		appendToHead: function (node, isRemove)
		{
			(document.getElementsByTagName('head')[0] || document.documentElement).appendChild(node);
			if (isRemove)
			{
				document.head.removeChild(node);
			}
		},
		loadCss: function (content)
		{
			if (!content)
			{
				return;
			}

			var node = document.createElement('STYLE');
			node.setAttribute("type", "text/css");
			if (node.styleSheet)
			{
				node.styleSheet.cssText = content;
			}
			else
			{
				node.appendChild(document.createTextNode(content))
			}

			this.appendToHead(node);
		}
	};
	webPacker.type = {
		isArray: function(item)
		{
			return item && Object.prototype.toString.call(item) === "[object Array]";
		},
		isString: function(item)
		{
			return item === '' ? true : (item ? (typeof (item) === "string" || item instanceof String) : false);
		},
		toArray: function(nodeList)
		{
			return Array.prototype.slice.call(nodeList);
		}
	};
	webPacker.classes = {
		change: function (node, className, isAdd)
		{
			node ? (isAdd ? this.add(node, className) : this.remove(node, className)) : null;
		},
		remove: function (node, className)
		{
			node ? node.classList.remove(className) : null;
		},
		add: function (node, className)
		{
			node ? node.classList.add(className) : null;
		},
		has: function (node, className)
		{
			return node && node.classList.contains(className);
		}
	};
	webPacker.url = {};
	webPacker.url.parameter = {
		list: null,
		get: function (name) {
			var list = this.getObject();
			return list.hasOwnProperty(name) ? decodeURIComponent(list[name]) : null;
		},
		has: function (name) {
			var list = this.getObject();
			return list.hasOwnProperty(name);
		},
		getList: function () {
			if (this.list)
			{
				return this.list;
			}
			var list = window.location.search.substr(1);
			if (list.length <= 1)
			{
				return [];
			}

			this.list = list.split('&').map(function (item) {
				var p = item.split('=');
				return {name: p[0], value: p[1] || ''};
			}, this);

			return this.list;
		},
		getObject: function () {
			return this.getList().reduce(function (result, item) {
				result[item.name] = item.value;
				return result;
			}, {});
		}
	};
	webPacker.addEventListener = function(el, eventName, handler)
	{
		el = el || window;
		if (window.addEventListener)
		{
			el.addEventListener(eventName, handler, false);
		}
		else
		{
			el.attachEvent('on' + eventName, handler);
		}
	};
	webPacker.event = {
		listeners: [],
		on: function (target, eventName, parameters)
		{
			this.listeners.filter(function (listener) {
				return listener[0] === target && listener[1] === eventName;
			}).forEach(function (listener) {
				listener[2].apply(this, parameters);
			});
		},
		listen: function (target, eventName, listener)
		{
			this.listeners.push([target, eventName, listener]);
		}
	};
	webPacker.cookie = {
		setItem: function (name, value, expires) {
			try
			{
				this.set(name, JSON.stringify(value), expires);
			}
			catch (e)
			{
			}
		},
		getItem: function (name) {
			try
			{
				return JSON.parse(this.get(name)) || null;
			}
			catch (e)
			{
				return null;
			}
		},
		set: function (name, value, expires) {
			expires = expires || 3600 * 24 * 365 * 10;
			var cookieDate = new Date(new Date().getTime() + 1000 * expires);
			document.cookie = name + "=" + value + "; path=/; expires=" + cookieDate.toUTCString();
		},
		get: function (name) {
			var matches = document.cookie.match(new RegExp(
				"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
			));
			return matches ? decodeURIComponent(matches[1]) : null;
		}
	};
	webPacker.ls = {
		supported: null,
		removeItem: function (key) {
			if (!this.isSupported()) return;
			window.localStorage.removeItem(key);
		},
		setItem: function (key, value) {
			if (!this.isSupported()) return;
			try
			{
				window.localStorage.setItem(key, JSON.stringify(value));
			}
			catch (e)
			{
			}
		},
		getItem: function (key) {
			if (!this.isSupported()) return null;
			try
			{
				return JSON.parse(window.localStorage.getItem(key)) || null;
			}
			catch (e)
			{
				return null;
			}
		},
		isSupported: function () {
			if (this.supported === null)
			{
				this.supported = false;
				try
				{
					var mod = 'b24crm-x-test';
					window.localStorage.setItem(mod, 'x');
					window.localStorage.removeItem(mod);
					this.supported = true;
				}
				catch (e)
				{

				}
			}

			return this.supported;
		}
	};
	webPacker.browser = {
		isIOS: function () {
			return (/(iPad;)|(iPhone;)/i.test(navigator.userAgent));
		},
		isOpera: function () {
			return navigator.userAgent.toLowerCase().indexOf('opera') !== -1;
		},
		isIE: function () {
			return document.attachEvent && !this.isOpera();
		},
		isMobile: function () {
			return (/(ipad|iphone|android|mobile|touch)/i.test(navigator.userAgent));
		}
	};
})();