(function ()
{
	"use strict";
	BX.namespace('BX.Report.VC');
	BX.Report.VC.Core = {
		entryUrl: '/bitrix/services/main/ajax.php',
		moduleName: 'report',
		ajaxGet: function (action, config)
		{
			BX.ajax.runAction('report.api.' + action, {
				data: config.urlParams || {}
			}).then(function (result) {
				this._successHandler(result, config)
			}.bind(this));
		},
		ajaxPost: function (action, config)
		{
			BX.ajax.runAction('report.api.' + action, {
				data: config.data || {}
			}).then(function (result) {
				this._successHandler(result, config)
			}.bind(this));

		},
		ajaxSubmit: function (form, config)
		{
			config.data = config.data || {};
			config.data['formParams'] = BX.ajax.prepareForm(form).data;
			BX.ajax.runAction('report.api.' + form.getAttribute('action'), {
				data: config.data || {}
			}).then(function (result) {
				config.onsuccess(result);
			});

		},
		_successHandler: function(result, config)
		{
			if (result.assets)
			{
				if (result.assets['css'].length)
				{
					BX.load(result.assets['css'], function ()
					{
						if (result.assets['js'].length)
						{
							BX.load(result.assets['js'], function ()
							{
								config.onFullSuccess(result);
							});
						}
						else
						{
							config.onFullSuccess(result);
						}
					});
				}
				else if (result.assets['js'].length)
				{
					BX.load(result.assets['js'], function ()
					{
						config.onFullSuccess(result);
					});
				}
				else
				{
					config.onFullSuccess(result);
				}
			}
			else
			{
				config.onFullSuccess(result);
			}
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


