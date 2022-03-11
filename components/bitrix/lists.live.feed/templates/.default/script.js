BX.namespace("BX.Lists");
BX.Lists.LiveFeedClass = (function ()
{
	var LiveFeedClass = function (parameters)
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.live.feed/ajax.php';
		this.socnetGroupId = parameters.socnetGroupId;
		this.randomString = parameters.randomString;
		this.listData = parameters.listData;

		var _this = this;
		BX.addCustomEvent('onDisplayClaimLiveFeed', function(iblock) {
			_this.init(iblock);
		});

		if (this.listData)
		{
			var iblock = [
				this.listData.ID,
				this.listData.NAME,
				this.listData.DESCRIPTION,
				this.listData.PICTURE,
				this.listData.CODE
			];

			BX.addCustomEvent('BX.Socialnetwork.Livefeed.Post.Form.Tabs:onInitialized', function(event) {
				var tabsInstance = event.getData().tabsInstance;
				if (tabsInstance)
				{
					tabsInstance.changePostFormTab('lists', iblock);
				}
			});
		}
	};

	LiveFeedClass.prototype.init = function (iblock)
	{
		this.manyTemplate = false;
		this.constantsPopup = null;
		this.templateId = null;

		if(iblock instanceof Array)
		{
			var iblockId = iblock[0],
				iblockName = iblock[1],
				iblockDescription = iblock[2],
				iblockPicture = iblock[3],
				iblockCode = iblock[4];

			this.setPicture(iblockPicture);
			this.setTitle(iblockName);
			this.getList(iblockId, iblockDescription, iblockCode);
			this.isConstantsTuned(iblockId);
		}
	};

	LiveFeedClass.prototype.isConstantsTuned = function (iblockId)
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'isConstantsTuned'),
			data: {
				iblockId: iblockId
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					var value = '', k, count = 0;
					for(k in result.templateData)
					{
						value += k + ',';
						count++;
					}
					if(count > 1)
					{
						this.manyTemplate = true;
					}
					BX('bx-lists-template-id').value = value;
					if(result.admin === true)
					{
						this.setResponsible();
					}
					else if(result.admin === false)
					{
						this.notifyAdmin();
						BX('bx-lists-check-notify-admin').value = 1;
					}
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.setPicture = function (iblockPicture)
	{
		BX('bx-lists-table-td-title-img').innerHTML = iblockPicture;
	};

	LiveFeedClass.prototype.setTitle = function (iblockName)
	{
		BX('bx-lists-table-td-title').innerHTML = BX.util.htmlspecialchars(iblockName);
		BX('bx-lists-title-notify-admin-popup').value = BX.util.htmlspecialchars(iblockName);
	};

	LiveFeedClass.prototype.getList = function (iblockId, iblockDescription, iblockCode)
	{
		var lists = BX.findChildrenByClassName(BX('bx-lists-store-lists'), 'bx-lists-input-list');
		for (var i = 0; i < lists.length; i++)
		{
			if(lists[i].value == iblockId)
			{
				BX.show(BX('bx-lists-div-list-'+lists[i].value));
			}
			else
			{
				BX.hide(BX('bx-lists-div-list-'+lists[i].value));
			}
		}

		BX('bx-lists-selected-list').value = iblockId;

		if(BX('bx-lists-input-list-'+iblockId))
		{
			return;
		}

		BX.Lists.ajax({
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'getList'),
			method: 'POST',
			dataType: 'html',
			processData: false,
			data: {
				iblockId: iblockId,
				iblockDescription: iblockDescription,
				iblockCode: iblockCode,
				socnetGroupId: this.socnetGroupId,
				randomString: this.randomString
			},
			onsuccess: BX.delegate(function (data)
			{
				BX('bx-lists-store-lists').appendChild(
					BX.create('input', {
						props: {
							id: 'bx-lists-input-list-'+iblockId,
							className: 'bx-lists-input-list'
						},
						attrs: {
							type: 'hidden',
							value: iblockId
						}
					})
				);
				BX('bx-lists-total-div-id').appendChild(
					BX.create('div', {
						props: {
							id: 'bx-lists-div-list-'+iblockId,
							className: 'bx-lists-div-list'
						},
						attrs: {
							style: 'display: block;'
						},
						html: data
					})
				);
				var ob = BX.processHTML(data);
				BX.ajax.processScripts(ob.SCRIPT);
			}, this)
		});
		BX.unbindAll(BX('blog-submit-button-save'));
		BX.bind(BX('blog-submit-button-save'), 'click', BX.proxy(function(e) {
			this.submitForm(e);
		}, this));
	};

	LiveFeedClass.prototype.addNewFileTableRow = function(tableID, col_count, regexp, rindex)
	{
		var tbl = document.getElementById(tableID);
		var cnt = tbl.rows.length;
		var oRow = tbl.insertRow(cnt);

		for(var i=0;i<col_count;i++)
		{
			var oCell = oRow.insertCell(i);
			var html = tbl.rows[cnt-1].cells[i].innerHTML;

			var tmp = document.createElement('div');
			tmp.innerHTML = html;
			tmp.firstChild.lastChild.innerHTML = '';
			html = tmp.innerHTML;

			oCell.innerHTML = html.replace(regexp,
				function(html)
				{
					return html.replace('[n'+arguments[rindex]+']', '[n'+(1+parseInt(arguments[rindex]))+']');
				}
			);
		}
	};

	LiveFeedClass.prototype.getNameInputFile = function()
	{
		var wrappers = document.getElementsByClassName('bx-lists-input-file');
		for (var i = 0; i < wrappers.length; i++)
		{
			var inputs = wrappers[i].getElementsByTagName('input');
			for (var j = 0; j < inputs.length; j++)
			{
				inputs[j].onchange = getName;
			}
		}
	};

	LiveFeedClass.prototype.createAdditionalHtmlEditor = function(tableId, fieldId, formId)
	{
		var tbl = document.getElementById(tableId);
		var cnt = tbl.rows.length;
		var oRow = tbl.insertRow(cnt);
		var oCell = oRow.insertCell(0);
		var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
		var p = 0;
		while (true)
		{
			var s = sHTML.indexOf('[n', p);
			if (s < 0)
				break;
			var e = sHTML.indexOf(']', s);
			if (e < 0)
				break;
			var n = parseInt(sHTML.substr(s + 2, e - s));
			sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
			p = s + 1;
		}
		var p = 0;
		while (true)
		{
			var s = sHTML.indexOf('__n', p);
			if (s < 0)
				break;
			var e = sHTML.indexOf('_', s + 2);
			if (e < 0)
				break;
			var n = parseInt(sHTML.substr(s + 3, e - s));
			sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
			p = e + 1;
		}
		oCell.innerHTML = sHTML;

		var idEditor = 'id_'+fieldId+'__n'+cnt+'_';
		var fieldIdName = fieldId+'[n'+cnt+'][VALUE]';
		window.BXHtmlEditor.Show(
		{
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
	};

	LiveFeedClass.prototype.createSettingsDropdown = function (e)
	{
		BX.PreventDefault(e);
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'createSettingsDropdown'),
			data: {
				iblockId: BX('bx-lists-selected-list').value,
				randomString: this.randomString
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					var menu = BX.PopupMenu.getMenuById('settings-lists');
					if(menu && menu.popupWindow)
					{
						if(menu.popupWindow.isShown())
						{
							BX.PopupMenu.destroy('settings-lists');
							return;
						}
					}
					BX.PopupMenu.show('settings-lists',BX('bx-lists-settings-btn'),result.settingsDropdown,
					{
						autoHide : true,
						offsetTop: 0,
						offsetLeft: 0,
						angle: { offset: 15 },
						events:
						{
							onPopupClose : function(){}
						}
					});
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.setDelegateResponsible = function ()
	{
		if(BX.PopupWindowManager.getCurrentPopup())
		{
			BX.PopupWindowManager.getCurrentPopup().close();
		}

		var hide = BX.Lists.hide,
			addToLinkParam = BX.Lists.addToLinkParam,
			showModalWithStatusAction = BX.Lists.showModalWithStatusAction,
			ajaxUrl = this.ajaxUrl;

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'checkDelegateResponsible'),
			data: {
				iblockId: BX('bx-lists-selected-list').value
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					BX.show(BX('feed-add-lists-right'));
					BX.Lists.modalWindow({
						modalId: 'bx-lists-popup',
						title: BX.message("LISTS_SELECT_STAFF_SET_RIGHT"),
						draggable: true,
						overlay: false,
						autoHide: false,
						contentStyle: {
							width: '600px',
							paddingTop: '10px',
							paddingBottom: '10px'
						},
						content: [BX('feed-add-lists-right')],
						events : {
							onPopupClose : function() {
								BX.hide(BX('feed-add-lists-right'));
								BX('bx-lists-total-div-id').appendChild(BX('feed-add-lists-right'));
							},
							onAfterPopupShow : function(popup) {
								BX.PopupMenu.destroy('settings-lists');
							}
						},
						buttons: [
							BX.create('a', {
								text : BX.message("LISTS_SAVE_BUTTON_SET_RIGHT"),
								props: {
									className: 'webform-small-button webform-small-button-accept'
								},
								events : {
									click : BX.delegate(function (e) {
										var selectSpan = BX.findChildrenByClassName(
											BX('feed-add-post-lists-item'), 'feed-add-post-lists'),
											selectUsers = [];
										for(var i = 0; i < selectSpan.length; i++)
										{
											selectUsers.push(selectSpan[i].getAttribute('data-id'));
										}
										BX.Lists.ajax({
											method: 'POST',
											dataType: 'json',
											url: addToLinkParam(ajaxUrl, 'action', 'setDelegateResponsible'),
											data: {
												iblockId: BX('bx-lists-selected-list').value,
												selectUsers: selectUsers
											},
											onsuccess: function (result) {
												if(result.status == 'success')
												{
													BX.PopupWindowManager.getCurrentPopup().close();
													showModalWithStatusAction({
														status: 'success',
														message: result.message
													})
												}
												else
												{
													BX.PopupWindowManager.getCurrentPopup().close();
													result.errors = result.errors || [{}];
													showModalWithStatusAction({
														status: 'error',
														message: result.errors.pop().message
													})
												}
											}
										});
									}, this)
								}
							}),
							BX.create('a', {
								text : BX.message("LISTS_CANCEL_BUTTON_SET_RIGHT"),
								props: {
									className: 'webform-small-button webform-button-cancel'
								},
								events : {
									click : BX.delegate(function (e) {
										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							})
						]
					});
					for(var k in result.listUser)
					{
						var selected = BX.findChildrenByClassName(
							BX('feed-add-post-lists-item'), 'feed-add-post-lists');
						for(var i in selected)
						{
							if(result.listUser[k].id == selected[i].getAttribute('data-id'))
							{
								delete result.listUser[k];
							}
						}
						BXfpListsSelectCallback(result.listUser[k]);
					}
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.jumpSettingProcess = function ()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'checkPermissions'),
			data: {
				iblockId: BX('bx-lists-selected-list').value
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					document.location.href = BX('bx-lists-lists-page').value+
						BX('bx-lists-selected-list').value+'/edit/';
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.jumpProcessDesigner = function ()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'getBizprocTemplateId'),
			data: {
				iblockId: BX('bx-lists-selected-list').value
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					var k;
					if(result.manyTemplate)
					{
						var html = '<p>'+BX.message("LISTS_DESIGNER_POPUP_DESCRIPTION")+'</p>';
						for(k in result.templateData)
						{
							var url = BX('bx-lists-lists-page').value+BX('bx-lists-selected-list').value+'/bp_edit/'+result.templateData[k].ID+'/';
							html += '<a href="'+url+'"><div class="bx-lists-designer-item">'+result.templateData[k].NAME+'</div></a>';
						}
						html += '';
						BX('bx-lists-designer-template-popup-content').innerHTML = html;
						BX.Lists.modalWindow({
							modalId: 'bx-lists-popup',
							title: BX.message("LISTS_DESIGNER_POPUP_TITLE"),
							draggable: true,
							overlay: false,
							contentStyle: {
								width: '400px',
								paddingTop: '10px',
								paddingBottom: '10px'
							},
							content: [BX('bx-lists-designer-template-popup-content')],
							events : {
								onPopupClose : function() {
									BX('bx-lists-designer-template-popup-content').innerHTML = '';
									BX('bx-lists-designer-template-popup')
										.appendChild(BX('bx-lists-designer-template-popup-content'));
								},
								onAfterPopupShow : function(popup) {
									BX.PopupMenu.destroy('settings-lists');
								}
							},
							buttons: [
								BX.create('a', {
									text : BX.message("LISTS_CANCEL_BUTTON_CLOSE"),
									props: {
										className: 'webform-small-button webform-button-cancel'
									},
									events : {
										click : BX.delegate(function (e) {
											BX.PopupWindowManager.getCurrentPopup().close();
										}, this)
									}
								})
							]
						});
					}
					else
					{
						for(k in result.templateData)
						{
							document.location.href = BX('bx-lists-lists-page').value+BX('bx-lists-selected-list').value+'/bp_edit/'+result.templateData[k].ID+'/';
						}
					}
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.notify = function (userId)
	{
		BX('bx-lists-notify-button-'+userId).setAttribute('onclick','');
		var siteDir = '/', siteId = null;
		if(BX('bx-lists-select-site-dir'))
		{
			siteDir = BX('bx-lists-select-site-dir').value;
		}
		if(BX('bx-lists-select-site-id'))
		{
			siteId = BX('bx-lists-select-site-id').value;
		}
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'notifyAdmin'),
			data: {
				iblockId: BX('bx-lists-selected-list').value,
				iblockName: BX('bx-lists-title-notify-admin-popup').value,
				userId: userId,
				siteDir: siteDir,
				siteId: siteId
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					BX.Lists.removeElement(BX('bx-lists-notify-button-'+userId));
					BX('bx-lists-notify-success-'+userId).innerHTML = result.message;
				}
				else
				{
					BX('bx-lists-notify-button-'+userId).setAttribute(
						'onclick','BX.Lists["LiveFeedClass_'+this.randomString+'"].notify('+userId+')');
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.notifyAdmin = function ()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'getListAdmin'),
			data: {
				iblockId: BX('bx-lists-selected-list').value
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					var html = '<span class="bp-question"><span>!</span>'
						+BX.message('LISTS_NOTIFY_ADMIN_TITLE_WHY').replace('#NAME_PROCESSES#', BX('bx-lists-title-notify-admin-popup').value)+'</span>';
					html += '<p>'+BX.message('LISTS_NOTIFY_ADMIN_TEXT_ONE').replace('#NAME_PROCESSES#', BX('bx-lists-title-notify-admin-popup').value)+'</p>';
					html += '<p>'+BX.message('LISTS_NOTIFY_ADMIN_TEXT_TWO').replace('#NAME_PROCESSES#', BX('bx-lists-title-notify-admin-popup').value)+'</p>';
					html += '<span class="bp-question-title">'+BX.message('LISTS_NOTIFY_ADMIN_MESSAGE')+'</span>';
					for(var k in result.listAdmin)
					{
						var img ='';
						if(result.listAdmin[k].img)
						{
							img = '<img src="'+result.listAdmin[k].img+'" alt="">';
						}
						html += '<div class="bp-question-item"><a href="#" class="bp-question-item-avatar"><span class="bp-question-item-avatar-inner">'+img +
						'</span></a><span class="bp-question-item-info"><span>'+result.listAdmin[k].name+'</span></span>' +
							'<span id="bx-lists-notify-success-'+result.listAdmin[k].id+'" class="bx-lists-notify-success"></span>'+
						'<a id="bx-lists-notify-button-'+result.listAdmin[k].id+'" href="#" onclick=\'BX.Lists["LiveFeedClass_'+this.randomString+'"].notify('+result.listAdmin[k].id+');\' class="webform-small-button bp-small-button webform-small-button-blue">' +
						''+BX.message('LISTS_NOTIFY_ADMIN_MESSAGE_BUTTON')+'</a></div>';
					}

					BX('bx-lists-notify-admin-popup-content').innerHTML = html;

					BX.Lists.modalWindow({
						modalId: 'bx-lists-popup',
						title: BX('bx-lists-title-notify-admin-popup').value,
						draggable: true,
						overlay: false,
						contentStyle: {
							width: '600px',
							paddingTop: '10px',
							paddingBottom: '10px'
						},
						content: [BX('bx-lists-notify-admin-popup-content')],
						events : {
							onPopupClose : function() {
								BX('bx-lists-notify-admin-popup-content').innerHTML = '';
								BX('bx-lists-notify-admin-popup')
									.appendChild(BX('bx-lists-notify-admin-popup-content'));
							},
							onAfterPopupShow : function(popup) {
								BX.PopupMenu.destroy('settings-lists');
							}
						},
						buttons: [
							BX.create('a', {
								text : BX.message("LISTS_CANCEL_BUTTON_CLOSE"),
								props: {
									className: 'webform-small-button webform-button-cancel'
								},
								events : {
									click : BX.delegate(function (e) {
										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							})
						]
					});
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.setResponsible = function (templateId)
	{
		this.templateId = templateId;

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'checkPermissions'),
			data: {
				iblockId: BX('bx-lists-selected-list').value
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status === 'success')
				{
					BX.Lists.ajax({
						url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'setResponsible'),
						method: 'POST',
						dataType: 'html',
						data: {
							iblockId: BX('bx-lists-selected-list').value,
							randomString: this.randomString,
							templateId: this.templateId
						},
						onsuccess: BX.delegate(function (data)
						{
							this.showConstantsPopup(data);
						}, this)
					});
				}
				else
				{
					if(BX('bx-lists-check-notify-admin').value)
					{
						this.notifyAdmin();
					}
					else
					{
						result.errors = result.errors || [{}];
						BX.Lists.showModalWithStatusAction({
							status: 'error',
							message: result.errors.pop().message
						})
					}
				}
			}, this)
		});
	};

	LiveFeedClass.prototype.showConstantsPopup = function(contentHtml)
	{
		if(BX.PopupWindowManager.getCurrentPopup())
			BX.PopupWindowManager.getCurrentPopup().close();

		if(this.manyTemplate && !this.templateId)
		{
			this.constantsPopup = BX.Lists.modalWindow({
				modalId: 'bx-lists-popup',
				title: BX.message("LISTS_DESIGNER_POPUP_TITLE"),
				overlay: false,
				draggable: true,
				contentStyle: {
					width: '600px',
					paddingBottom: '10px'
				},
				content: [this.getConstantsForm(contentHtml)],
				events : {
					onPopupClose : function() {
						this.constantsPopup = null;
					}.bind(this),
					onAfterPopupShow : function(popup) {
						BX.PopupMenu.destroy('settings-lists');
					}
				},
				buttons: [
					BX.create('a', {
						text : BX.message("LISTS_CANCEL_BUTTON_CLOSE"),
						props: {
							className: 'webform-small-button webform-button-cancel'
						},
						events : {
							click : BX.delegate(function (e) {
								if(!!this.constantsPopup) this.constantsPopup.close();
							}, this)
						}
					})
				]
			});
		}
		else
		{
			this.constantsPopup = BX.Lists.modalWindow({
				modalId: 'bx-lists-popup',
				title: BX.message("LISTS_SELECT_STAFF_SET_RESPONSIBLE"),
				overlay: false,
				draggable: true,
				withoutWindowManager: true,
				contentStyle: {
					width: '600px',
					paddingBottom: '10px'
				},
				content: [this.getConstantsForm(contentHtml)],
				events : {
					onPopupClose : function() {
						this.constantsPopup = null;
					}.bind(this),
					onAfterPopupShow : function(popup) {
						BX.PopupMenu.destroy('settings-lists');
					}
				},
				buttons: [
					BX.create('a', {
						text : BX.message("LISTS_SAVE_BUTTON_SET_RIGHT"),
						props: {
							className: 'webform-small-button webform-small-button-accept'
						},
						events : {
							click : BX.delegate(function (e)
							{
								var form = BX.findChild(BX('bx-lists-set-responsible-content'),
									{tag: 'FORM'}, true);
								if(form)
								{
									form.modalWindow = this.constantsPopup;
									form.onsubmit(form, e);
								}
							}, this)
						}
					}),
					BX.create('a', {
						text : BX.message("LISTS_CANCEL_BUTTON_SET_RIGHT"),
						props: {
							className: 'webform-small-button webform-button-cancel'
						},
						events : {
							click : BX.delegate(function (e) {
								if (!!this.constantsPopup) this.constantsPopup.close();
							}, this)
						}
					})
				]
			});
		}
	};

	LiveFeedClass.prototype.getConstantsForm = function(html)
	{
		return BX.create("div", {
			children: [
				BX.create("div", {
					props: {
						id: "bx-lists-set-responsible-content",
						className: "bx-lists-set-responsible-content"
					},
					html: html
				})
			]
		});
	};

	LiveFeedClass.prototype.submitForm = function(e)
	{
		BX.unbindAll(BX('blog-submit-button-save'));

		if (BX('feed-add-post-content-lists').style.display === 'none')
		{
			BX.bind(BX('blog-submit-button-save'), 'click', submitBlogPostForm());
		}

		BX.addClass(BX('blog-submit-button-save'), 'ui-btn-clock');
		var lists = BX.findChildrenByClassName(BX('bx-lists-store-lists'), 'bx-lists-input-list');
		for (var i = 0; i < lists.length; i++)
		{
			if(lists[i].value !== BX('bx-lists-selected-list').value)
			{
				BX.Lists.removeElement(BX('bx-lists-div-list-'+lists[i].value));
				BX.Lists.removeElement(BX('bx-lists-input-list-'+lists[i].value));
			}
		}

		BX.ajax.submitAjax(BX('blogPostForm'), {
			method : "POST",
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'checkDataElementCreation'),
			processData : true,
			onsuccess: BX.delegate(function (startResult)
			{
				var result = BX.parseJSON(startResult, {});

				if(result !== null && result !== undefined)
				{
					if(result.status === 'success')
					{
						BX.bind(BX('blog-submit-button-save'), 'click', submitBlogPostForm());
					}
					else
					{
						BX.removeClass(BX('blog-submit-button-save'), 'ui-btn-clock');
						BX('bx-lists-block-errors').innerHTML = result.errors.pop().message;
						BX.show(BX('bx-lists-block-errors'));
						BX.bind(BX('blog-submit-button-save'), 'click', BX.proxy(function(e) {
							this.submitForm(e);
						}, this));
					}
				}
				else
				{
					BX.removeClass(BX('blog-submit-button-save'), 'ui-btn-clock');
					BX('bx-lists-block-errors').innerHTML = startResult;
					BX.show(BX('bx-lists-block-errors'));
					BX.bind(BX('blog-submit-button-save'), 'click', BX.proxy(function(e) {
						this.submitForm(e);
					}, this));
				}

			}, this)
		});

		e.preventDefault();
	};

	LiveFeedClass.prototype.errorPopup = function (message)
	{
		BX.Lists.showModalWithStatusAction({
			status: 'error',
			message: message
		})
	};

	return LiveFeedClass;

})();
