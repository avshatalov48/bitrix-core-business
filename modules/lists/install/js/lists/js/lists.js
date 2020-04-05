BX.namespace("BX.Lists");
BX.Lists = (function ()
{
	var firstButtonInModalWindow = null;
	var windowsWithoutManager = {};

	return {
		ajax: function (config)
		{
			config.data = config.data || {};
			config.data['SITE_ID'] = BX.message('SITE_ID');
			config.data['sessid'] = BX.bitrix_sessid();

			return BX.ajax(config);
		},
		modalWindow: function (params)
		{
			params = params || {};
			params.title = params.title || false;
			params.bindElement = params.bindElement || null;
			params.overlay = typeof params.overlay === "undefined" ? true : params.overlay;
			params.autoHide = params.autoHide || false;
			params.closeIcon = typeof params.closeIcon === "undefined"? {right: "20px", top: "10px"} : params.closeIcon;
			params.modalId = params.modalId || 'lists_modal_window_' + (Math.random() * (200000 - 100) + 100);
			params.withoutContentWrap = typeof params.withoutContentWrap === "undefined" ?
				false : params.withoutContentWrap;
			params.contentClassName = params.contentClassName || '';
			params.contentStyle = params.contentStyle || {};
			params.content = params.content || [];
			params.buttons = params.buttons || false;
			params.events = params.events || {};
			params.draggable = params.draggable || false;
			params.withoutWindowManager = !!params.withoutWindowManager || false;

			var contentDialogChildren = [];
			if (params.withoutContentWrap) {
				contentDialogChildren = contentDialogChildren.concat(params.content);
			}
			else {
				contentDialogChildren.push(BX.create('div', {
					props: {
						className: 'bx-lists-popup-content ' + params.contentClassName
					},
					style: params.contentStyle,
					children: params.content
				}));
			}
			var buttons = [];
			if (params.buttons) {
				for (var i in params.buttons) {
					if (!params.buttons.hasOwnProperty(i)) {
						continue;
					}
					if (i > 0) {
						buttons.push(BX.create('SPAN', {html: '&nbsp;'}));
					}
					buttons.push(params.buttons[i]);
				}

				contentDialogChildren.push(BX.create('div', {
					props: {
						className: 'bx-lists-popup-buttons'
					},
					children: buttons
				}));
			}

			var contentDialog = BX.create('div', {
				props: {
					className: 'bx-lists-popup-container'
				},
				children: contentDialogChildren
			});

			params.events.onPopupShow = BX.delegate(function () {
				if (buttons.length) {
					firstButtonInModalWindow = buttons[0];
					BX.bind(document, 'keydown', BX.proxy(this._keyPress, this));
				}

				if(params.events.onPopupShow)
					BX.delegate(params.events.onPopupShow, BX.proxy_context);
			}, this);
			var closePopup = params.events.onPopupClose;
			params.events.onPopupClose = BX.delegate(function () {

				firstButtonInModalWindow = null;
				try
				{
					BX.unbind(document, 'keydown', BX.proxy(this._keypress, this));
				}
				catch (e) { }

				if(closePopup)
				{
					BX.delegate(closePopup, BX.proxy_context)();
				}

				if(params.withoutWindowManager)
				{
					delete windowsWithoutManager[params.modalId];
				}

				BX.proxy_context.destroy();
			}, this);

			var modalWindow;
			if(params.withoutWindowManager)
			{
				if(!!windowsWithoutManager[params.modalId])
				{
					return windowsWithoutManager[params.modalId]
				}
				modalWindow = new BX.PopupWindow(params.modalId, params.bindElement, {
					titleBar: params.title,
					draggable: params.draggable,
					content: contentDialog,
					closeByEsc: true,
					closeIcon: params.closeIcon,
					autoHide: params.autoHide,
					overlay: params.overlay,
					events: params.events,
					buttons: [],
					zIndex : isNaN(params["zIndex"]) ? 0 : params.zIndex
				});
				windowsWithoutManager[params.modalId] = modalWindow;
			}
			else
			{
				modalWindow = BX.PopupWindowManager.create(params.modalId, params.bindElement, {
					titleBar: params.title,
					draggable: params.draggable,
					content: contentDialog,
					closeByEsc: true,
					closeIcon: params.closeIcon,
					autoHide: params.autoHide,
					overlay: params.overlay,
					events: params.events,
					buttons: [],
					zIndex : isNaN(params["zIndex"]) ? 0 : params.zIndex
				});

			}

			modalWindow.show();

			return modalWindow;
		},
		removeElement: function (elem)
		{
			return elem.parentNode ? elem.parentNode.removeChild(elem) : elem;
		},
		addToLinkParam: function (link, name, value)
		{
			if (!link.length) {
				return '?' + name + '=' + value;
			}
			link = BX.util.remove_url_param(link, name);
			if (link.indexOf('?') != -1) {
				return link + '&' + name + '=' + value;
			}
			return link + '?' + name + '=' + value;
		},
		getFirstErrorFromResponse: function(reponse)
		{
			reponse = reponse || {};
			if(!reponse.errors)
				return '';

			return reponse.errors.shift().message;
		},
		showModalWithStatusAction: function (response, action)
		{
			response = response || {};
			if (!response.message) {
				if (response.status == 'success') {
					response.message = BX.message('LISTS_ASSETS_JS_STATUS_ACTION_SUCCESS');
				}
				else {
					response.message = BX.message('LISTS_ASSETS_JS_STATUS_ACTION_ERROR') + '. '
						+ this.getFirstErrorFromResponse(response);
				}
			}
			var messageBox = BX.create('div', {
				props: {
					className: 'bx-lists-alert'
				},
				children: [
					BX.create('span', {
						props: {
							className: 'bx-lists-aligner'
						}
					}),
					BX.create('span', {
						props: {
							className: 'bx-lists-alert-text'
						},
						text: response.message
					}),
					BX.create('div', {
						props: {
							className: 'bx-lists-alert-footer'
						}
					})
				]
			});

			var currentPopup = BX.PopupWindowManager.getCurrentPopup();
			if(currentPopup)
			{
				currentPopup.destroy();
			}

			var idTimeout = setTimeout(function ()
			{
				var w = BX.PopupWindowManager.getCurrentPopup();
				if (!w || w.uniquePopupId != 'bx-lists-status-action') {
					return;
				}
				w.close();
				w.destroy();
			}, 3500);
			var popupConfirm = BX.PopupWindowManager.create('bx-lists-status-action', null, {
				content: messageBox,
				onPopupClose: function ()
				{
					this.destroy();
					clearTimeout(idTimeout);
				},
				autoHide: true,
				zIndex: 2000,
				className: 'bx-lists-alert-popup'
			});
			popupConfirm.show();

			BX('bx-lists-status-action').onmouseover = function (e)
			{
				clearTimeout(idTimeout);
			};

			BX('bx-lists-status-action').onmouseout = function (e)
			{
				idTimeout = setTimeout(function ()
				{
					var w = BX.PopupWindowManager.getCurrentPopup();
					if (!w || w.uniquePopupId != 'bx-lists-status-action') {
						return;
					}
					w.close();
					w.destroy();
				}, 3500);
			};
		},
		addNewTableRow: function(tableID, col_count, regexp, rindex)
		{
			var tbl = document.getElementById(tableID);
			var cnt = tbl.rows.length;
			var oRow = tbl.insertRow(cnt);
			var i = 0;

			for(i=0;i<col_count;i++)
			{
				var html = tbl.rows[cnt-1].innerHTML;
				html = html.replace(regexp,
					function(html)
					{
						return html.replace('[n'+arguments[rindex]+']', '[n'+(1+parseInt(arguments[rindex]))+']');
					}
				);
				var tmpObject = {'html': html};
				BX.onCustomEvent(window, 'onAddNewRowBeforeInner', [tmpObject]);
				html = tmpObject.html;
				oRow.innerHTML = html;
				var ob = BX.processHTML(html);
				BX.ajax.processScripts(ob.SCRIPT);
			}
		},
		createAdditionalHtmlEditor: function(tableId, fieldId, formId)
		{
			var tbl = document.getElementById(tableId);
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

			var idEditor = 'id_'+fieldId+'__n'+cnt+'_';
			var fieldIdName = fieldId+'[n'+cnt+'][VALUE]';
			window.BXHtmlEditor.Show({
				'id':idEditor,
				'inputName':fieldIdName,
				'name' : fieldIdName,
				'content':'',
				'width':'100%',
				'height':'200',
				'allowPhp':false,
				'limitPhpAccess':false,
				'templates':[],
				'templateId':'',
				'templateParams':[],
				'componentFilter':'',
				'snippets':[],
				'placeholder':'Text here...',
				'actionUrl':'/bitrix/tools/html_editor_action.php',
				'cssIframePath':'/bitrix/js/fileman/html_editor/iframe-style.css?1412693817',
				'bodyClass':'',
				'bodyId':'',
				'spellcheck_path':'/bitrix/js/fileman/html_editor/html-spell.js?v=1412693817',
				'usePspell':'N',
				'useCustomSpell':'Y',
				'bbCode': false,
				'askBeforeUnloadPage':false,
				'settingsKey':'user_settings_1',
				'showComponents':true,
				'showSnippets':true,
				'view':'wysiwyg',
				'splitVertical':false,
				'splitRatio':'1',
				'taskbarShown':false,
				'taskbarWidth':'250',
				'lastSpecialchars':false,
				'cleanEmptySpans':true,
				'lazyLoad':false,
				'showTaskbars':false,
				'showNodeNavi':false,
				'controlsMap':[
					{'id':'Bold','compact':true,'sort':'80'},
					{'id':'Italic','compact':true,'sort':'90'},
					{'id':'Underline','compact':true,'sort':'100'},
					{'id':'Strikeout','compact':true,'sort':'110'},
					{'id':'RemoveFormat','compact':true,'sort':'120'},
					{'id':'Color','compact':true,'sort':'130'},
					{'id':'FontSelector','compact':false,'sort':'135'},
					{'id':'FontSize','compact':false,'sort':'140'},
					{'separator':true,'compact':false,'sort':'145'},
					{'id':'OrderedList','compact':true,'sort':'150'},
					{'id':'UnorderedList','compact':true,'sort':'160'},
					{'id':'AlignList','compact':false,'sort':'190'},
					{'separator':true,'compact':false,'sort':'200'},
					{'id':'InsertLink','compact':true,'sort':'210'},
					{'id':'InsertImage','compact':false,'sort':'220'},
					{'id':'InsertVideo','compact':true,'sort':'230'},
					{'id':'InsertTable','compact':false,'sort':'250'},
					{'id':'Smile','compact':false,'sort':'280'},
					{'separator':true,'compact':false,'sort':'290'},
					{'id':'Fullscreen','compact':false,'sort':'310'},
					{'id':'More','compact':true,'sort':'400'}],
				'autoResize':true,
				'autoResizeOffset':'40',
				'minBodyWidth':'350',
				'normalBodyWidth':'555'
			});
			var htmlEditor = BX.findChildrenByClassName(BX(tableId), 'bx-html-editor');
			for(var k in htmlEditor)
			{
				var editorId = htmlEditor[k].getAttribute('id');
				var frameArray = BX.findChildrenByClassName(BX(editorId), 'bx-editor-iframe');
				if(frameArray.length > 1)
				{
					for(var i = 0; i < frameArray.length - 1; i++)
					{
						frameArray[i].parentNode.removeChild(frameArray[i]);
					}
				}

			}
		}
	}
})();