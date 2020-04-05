(function ()
{
	"use strict";
	BX.namespace('BX.Report.VC');
	BX.Report.VC.Core = {
		entryUrl: '/bitrix/services/main/ajax.php',
		moduleName: 'report',
		currentRunningAjaxRequests: [],
		abortAllRunningRequests: function()
		{
			for (var i = 0; i < this.currentRunningAjaxRequests.length; i++)
			{
				this.currentRunningAjaxRequests[i].abort();
			}
		},
		ajaxGet: function (action, config, module)
		{
			if (module === undefined)
			{
				module = 'report';
			}
			BX.ajax.runAction(module + '.api.' + action, {
				data: config.urlParams || {},
				onrequeststart: function(xhr) {
					this.currentRunningAjaxRequests.push(xhr);
					if (BX.type.isFunction(config.onrequeststart))
					{
						config.onrequeststart(xhr);
					}
				}.bind(this)
			}).then(function (result) {
				this._successHandler(result, config)
			}.bind(this)).catch(function(response) {
				if(response.errors)
				{
					console.error(response.errors.map(function(er){return er.message}).join("\n"));
				}
				else
				{
					console.error(response);
				}
			});
		},
		ajaxPost: function (action, config)
		{
			BX.ajax.runAction('report.api.' + action, {
				data: config.data || {},
				analyticsLabel: config.analyticsLabel || undefined,
				onrequeststart: function(xhr) {
					this.currentRunningAjaxRequests.push(xhr);
					if (BX.type.isFunction(config.onrequeststart))
					{
						config.onrequeststart(xhr);
					}
				}.bind(this)
			}).then(function (result) {
				this._successHandler(result, config)
			}.bind(this)).catch(function(response) {
				var errors = response.errors;

				console.error(errors.map(function(er){return er.message}).join("\n"));
			});

		},
		ajaxSubmit: function (form, config)
		{
			config.data = config.data || {};
			config.data['formParams'] = BX.ajax.prepareForm(form).data;
			BX.ajax.runAction('report.api.' + form.getAttribute('action'), {
				data: config.data || {}
			}).then(function (result) {
				config.onsuccess(result);
			}).catch(function(response) {
				var errors = response.errors;

				console.error(errors.map(function(er){return er.message}).join("\n"));
			});

		},
		_successHandler: function(result, config)
		{
			if (!result.assets)
			{
				config.onFullSuccess(result);
				return;
			}

			this.loadAssets(result.assets).then(function ()
			{
				if (!BX.type.isArray(result.assets['string']))
				{
					config.onFullSuccess(result);
					return;
				}

				for (var i = 0; i < result.assets['string'].length; i++)
				{
					BX.html(null, result.assets['string'][i]);
				}
				config.onFullSuccess(result);
			});
		},
		loadAssets: function(assets)
		{
			if(!assets)
			{
				assets = {};
			}
			if(!assets['js'])
			{
				assets['js'] = [];
			}
			if(!assets['css'])
			{
				assets['css'] = [];
			}
			return new Promise(function(resolve)
			{
				BX.load(assets['css'], function() {
					BX.load(assets['js'], function () {
						resolve();
					})
				})
			});
		},
		loadJsStings: function(strings, callback)
		{
			for (var i = 0; i < strings.length; i++)
			{
				BX.html(null, strings[i]);
			}
			callback();
		},
		getPopup: function (uniquePopupId, bindElement, params)
		{
			return new BX.PopupWindow(uniquePopupId, bindElement, {
				closeIcon: {right: "20px", top: "10px"},
				titleBar: params.title,
				width: 570,
				height: 500,
				zIndex: 0,
				offsetLeft: 0,
				offsetTop: 0,
				draggable: {restrict: false},
				overlay: {backgroundColor: 'black', opacity: '80'},
				events: params.events || {},
				buttons: params.buttons || {},
				content: params.content || ''
			});
		},
		getClass: function (fullClassName)
		{
			if (!BX.type.isNotEmptyString(fullClassName))
			{
				return null;
			}

			var classFn = null;
			var currentNamespace = window;
			var namespaces = fullClassName.split(".");
			for (var i = 0; i < namespaces.length; i++)
			{
				var namespace = namespaces[i];
				if (!currentNamespace[namespace])
				{
					return null;
				}

				currentNamespace = currentNamespace[namespace];
				classFn = currentNamespace;
			}

			return classFn;
		},
		getFunction: function (functionName)
		{
			if (!BX.type.isNotEmptyString(functionName))
			{
				return null;
			}

			var currentObject = window;
			var nameParts = functionName.split(".");
			for (var i = 0; i < nameParts.length - 1; i++)
			{
				if (!currentObject[nameParts[i]])
				{
					return null;
				}

				currentObject = currentObject[nameParts[i]];
			}

			return currentObject[nameParts[nameParts.length - 1]] ? currentObject[nameParts[nameParts.length - 1]].bind(currentObject) : functionName;
		}
	};


	BX.Report.VC.PopupWindowManager = BX.PopupWindowManager;
	BX.Report.VC.PopupWindowManager.getPopups = function()
	{
		return this._popups;
	};

	BX.Report.VC.PopupWindowManager.adjustPopupsPositions = function ()
	{
		var popups = this.getPopups();
		for (var i = 0; i < popups.length; i++)
		{
			popups[i].adjustPosition();
		}
	};

	BX.Report.VC.PopupWindowManager.closeAllPopups = function ()
	{
		var popups = this.getPopups();
		for (var i = 0; i < popups.length; i++)
		{
			popups[i].close();
		}
	};

	BX.Report.VC.SetFontSize = function(options)
	{
		this.items = options.node;
		this.init();
	};

	BX.Report.VC.SetFontSize.prototype = {

		init: function()
		{
			this.show();
			// window.addEventListener('resize', this.adjustFontSize.bind(this));
		},
		adjustFontSize: function()
		{
			this.show();
		},
		getFontSize: function(fontNode) {
			return getComputedStyle(fontNode).fontSize.slice(0, -2)
		},
		appendNode: function(node) {
			var fontSize = this.getFontSize(node);
			if (fontSize)
			{
				for(var i = node.parentNode.offsetWidth, a = +fontSize; i < (node.offsetWidth + 40); a--)
				{
					node.style.fontSize = a + 'px';
				}
			}
		},

		show: function() {
			for (var i = 0; i < this.items.length; i++)
			{
				this.appendNode(this.items[i])
			}
		}

	};



})();


