BX.namespace('BX.Report');
BX.Report.ReportListClass = (function ()
{
	var ReportListClass = function(parameters)
	{
		this.ajaxUrl = '/bitrix/components/bitrix/report.list/ajax.php';
		this.jsClass = parameters.jsClass;
		this.containerId = parameters.containerId;
		this.editUrl = parameters.editUrl;
		this.deleteUrl = parameters.deleteUrl;
		this.copyUrl = parameters.copyUrl;
		this.deleteConfirmUrl = parameters.deleteConfirmUrl;
		this.ownerId = parameters.ownerId;
		this.sessionError = Boolean(parameters.sessionError);

		this.destFormName = parameters.destFormName || 'report-list-destFormName';

		this.init();
	};

	ReportListClass.prototype.init = function()
	{
		var menuButton = BX.findChildren(
			BX(this.containerId), {
				tagName:'a',
				className:'reports-menu-button'
			}, true);
		for(var i = 0; i < menuButton.length; i++)
		{
			BX.bind(menuButton[i], 'click', BX.delegate(function(e) {
				this.showMenu(e);
			}, this));
		}

		if(this.sessionError)
		{
			BX.Report.showModalWithStatusAction({
				status: 'error',
				message: BX('report-list-error').innerHTML
			});
		}
	};

	ReportListClass.prototype.showMenu = function(e)
	{
		BX.PreventDefault(e);
		var i = 0;
		var element = BX.proxy_context;
		var sid = element.id.substr(4);
		var pos = sid.indexOf('d');
		var markDefault = (pos < 0) ? 0 : parseInt(sid.substr(pos+1));
		var RID = parseInt((pos > 0) ? sid.substr(0,pos) : sid);
		var editUrl = this.editUrl.replace('REPORT_ID', RID);
		var deleteUrl = this.deleteUrl.replace('REPORT_ID', RID);
		var copyUrl = this.copyUrl.replace('REPORT_ID', RID);
		var menuItems = [];
		var access = false, accessMark ='', listAccessMark = ['r', 'e', 'f'];

		for(var k = 0; listAccessMark.length > k; k++)
		{
			var position = sid.indexOf(listAccessMark[k]);
			if(position > 0)
			{
				accessMark = sid.slice(position);
				access = true;
				break;
			}
		}

		menuItems[i++] = {
			text : BX.message('REPORT_COPY_SHORT'),
			title : BX.message('REPORT_COPY_FULL'),
			className : 'reports-menu-popup-item-copy',
			href: copyUrl
		};
		menuItems[i++] = {
			text : BX.message('REPORT_EXPORT_SHORT'),
			title : BX.message('REPORT_EXPORT_FULL'),
			className : 'reports-menu-popup-item-export',
			onclick: 'BX.Report["'+this.jsClass+'"].export("'+RID+'");'
		};
		if(access)
		{
			switch (accessMark)
			{
				case 'r':
					break;
				case 'e':
					menuItems[i++] = {
						text : BX.message('REPORT_EDIT_SHORT'),
						title : BX.message('REPORT_EDIT_FULL'),
						className : 'reports-menu-popup-item-edit',
						href: editUrl
					};
					break;
				case 'f':
					menuItems[i++] = {
						text : BX.message('REPORT_EDIT_SHORT'),
						title : BX.message('REPORT_EDIT_FULL'),
						className : 'reports-menu-popup-item-edit',
						href: editUrl
					};
					menuItems[i++] = {
						text : BX.message('REPORT_DELETE_SHORT'),
						title : BX.message('REPORT_DELETE_FULL'),
						className : 'reports-menu-popup-item-delete',
						href: deleteUrl, onclick: BX.delegate(function(e){
							this.confirmReportDelete(RID); BX.PreventDefault(e);
						}, this)
					};
					break;
			}
		}
		else
		{
			if (markDefault === 0)
			{
				menuItems[i++] = {
					text : BX.message('REPORT_EDIT_SHORT'),
					title : BX.message('REPORT_EDIT_FULL'),
					className : 'reports-menu-popup-item-edit',
					href: editUrl
				};
				menuItems[i++] = {
					text : BX.message('REPORT_SHARING_SHORT'),
					title : BX.message('REPORT_SHARING_FULL'),
					className : 'reports-menu-popup-item-sharing',
					onclick: 'BX.Report["'+this.jsClass+'"].sharing("'+RID+'");'
				};
			}
			menuItems[i++] = {
				text : BX.message('REPORT_DELETE_SHORT'),
				title : BX.message('REPORT_DELETE_FULL'),
				className : 'reports-menu-popup-item-delete',
				href: deleteUrl, onclick: BX.delegate(function(e){
					this.confirmReportDelete(RID); BX.PreventDefault(e);
				}, this)
			};
		}

		BX.PopupMenu.show(RID, element, menuItems, {});
	};

	ReportListClass.prototype.confirmReportDelete = function(id)
	{
		var href = this.deleteConfirmUrl.replace('REPORT_ID', id);

		if(confirm(BX.message('REPORT_DELETE_CONFIRM')))
		{
			var form = BX.create('form', {
				attrs: {method:'post'}
			});
			form.action = href;
			form.appendChild(BX.create('input', {
				attrs: {
					type:'hidden',
					name:'csrf_token',
					value:BX.message('bitrix_sessid')
				}
			}));

			document.body.appendChild(form);
			BX.submit(form);
		}
	};

	var entityToNewShared = {};
	var loadedReadOnlyEntityToNewShared = {};
	var maxAccessName = '';

	ReportListClass.prototype.sharing = function(reportId)
	{
		entityToNewShared = {};
		loadedReadOnlyEntityToNewShared = {};

		BX.PopupMenu.destroy(reportId);

		BX.Report.modalWindowLoader(
			BX.Report.addToLinkParam(this.ajaxUrl, 'action', 'showSharing'),
			{
				id: 'report_show_sharing_'+reportId,
				responseType: 'json',
				postData: {
					reportId: reportId
				},
				afterSuccessLoad: BX.delegate(function(response)
				{
					if(response.status != 'success')
					{
						response.errors = response.errors || [{}];
						BX.Report.showModalWithStatusAction({
							status: 'error',
							message: response.errors.pop().message
						});
					}
					var objectOwner = {
						name: response.owner.name,
						avatar: response.owner.avatar,
						link: response.owner.link
					};
					maxAccessName = response.owner.access;

					BX.Report.modalWindow({
						modalId: 'bx-report-sharing-'+reportId,
						title: BX.message('REPORT_LIST_SHARING_TITLE_POPUP'),
						contentClassName: '',
						contentStyle: {},
						events: {
							onAfterPopupShow: BX.delegate(function () {
								BX.addCustomEvent('onChangeRightOfSharing',
									BX.proxy(this.onChangeRightOfSharing, this));

								for (var i in response.members) {
									if (!response.members.hasOwnProperty(i)) {
										continue;
									}
									entityToNewShared[response.members[i].entityId] = {
										item: {
											id: response.members[i].entityId,
											name: response.members[i].name,
											avatar: response.members[i].avatar
										},
										type: response.members[i].type,
										right: response.members[i].right
									};
								}

								BX.SocNetLogDestination.init({
									name : this.destFormName,
									searchInput : BX('feed-add-post-destination-input'),
									bindMainPopup : {
										'node': BX('feed-add-post-destination-container'),
										'offsetTop' : '5px', 'offsetLeft': '15px'
									},
									bindSearchPopup : {
										'node': BX('feed-add-post-destination-container'),
										'offsetTop' : '5px', 'offsetLeft': '15px'
									},
									callback : {
										select : BX.proxy(this.onSelectDestination, this),
										unSelect : BX.proxy(this.onUnSelectDestination, this),
										openDialog : BX.proxy(this.onOpenDialogDestination, this),
										closeDialog : BX.proxy(this.onCloseDialogDestination, this),
										openSearch : BX.proxy(this.onOpenSearchDestination, this),
										closeSearch : BX.proxy(this.onCloseSearchDestination, this)
									},
									items: response.destination.items,
									itemsLast: response.destination.itemsLast,
									itemsSelected : response.destination.itemsSelected
								});

								var BXSocNetLogDestinationFormName = this.destFormName;
								BX.bind(BX('feed-add-post-destination-container'), 'click', function(e){
									BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName);
									BX.PreventDefault(e);
								});
								BX.bind(BX('feed-add-post-destination-input'), 'keyup',
									BX.proxy(this.onKeyUpDestination, this));
								BX.bind(BX('feed-add-post-destination-input'), 'keydown',
									BX.proxy(this.onKeyDownDestination, this));

							}, this),
							onPopupClose: BX.delegate(function () {
								if(BX.SocNetLogDestination && BX.SocNetLogDestination.isOpenDialog())
								{
									BX.SocNetLogDestination.closeDialog()
								}
								BX.removeCustomEvent('onChangeRightOfSharing',
									BX.proxy(this.onChangeRightOfSharing, this));
								BX.proxy_context.destroy();
							}, this)
						},
						content: [
							BX.create('div', {
								props: {
									className: 'bx-report-popup-content'
								},
								children: [
									BX.create('table', {
										props: {
											className: 'bx-report-popup-shared-people-list'
										},
										children: [
											BX.create('thead', {
												html: '<tr>'+
												'<td class="bx-report-popup-shared-people-list-head-col1">'
												+BX.message('REPORT_LIST_SHARING_OWNER')+'</td></tr>'
											}),
											BX.create('tr', {
												html: '<tr>' +
												'<td class="bx-report-popup-shared-people-list-col1" ' +
												'style="border-bottom: none;">' +
												'<a class="bx-report-filepage-used-people-link" href="'
												+ objectOwner.link + '">' +
												'<span class="bx-report-filepage-used-people-avatar" ' +
												'style="background-image: url(' + objectOwner.avatar + ');">'+
												'</span>'+BX.util.htmlspecialchars(objectOwner.name)+'</a></td></tr>'
											})
										]
									}),
									BX.create('table', {
										props: {
											id: 'bx-report-popup-shared-people-list',
											className: 'bx-report-popup-shared-people-list',
											style: 'display:none;'
										},
										children: [
											BX.create('thead', {
												html: '<tr>' +
												'<td class="bx-report-popup-shared-people-list-head-col1">'
												+BX.message('REPORT_LIST_SHARING_NAME_RIGHTS_USER')
												+'</td>'+
												'<td class="bx-report-popup-shared-people-list-head-col2">'
												+BX.message('REPORT_LIST_SHARING_NAME_RIGHTS')+
												'</td><td class="bx-report-popup-shared-people-list-head-col3">'+
												'</td></tr>'
											})
										]
									}),
									BX.create('div', {
										props: {
											id: 'feed-add-post-destination-container',
											className: 'feed-add-post-destination-wrap'
										},
										children: [
											BX.create('span', {
												props: {
													className: 'feed-add-post-destination-item'
												}
											}),
											BX.create('span', {
												props: {
													id: 'feed-add-post-destination-input-box',
													className: 'feed-add-destination-input-box'
												},
												style: {
													background: 'transparent'
												},
												children: [
													BX.create('input', {
														props: {
															type: 'text',
															value: '',
															id: 'feed-add-post-destination-input',
															className: 'feed-add-destination-inp'
														}
													})
												]
											}),
											BX.create('a', {
												props: {
													href: '#',
													id: 'bx-destination-tag',
													className: 'feed-add-destination-link'
												},
												style: {
													background: 'transparent'
												},
												text: BX.message('REPORT_LIST_SHARING_NAME_ADD_RIGHTS_USER'),
												events: {
													click: BX.delegate(function () {
													}, this)
												}
											})
										]
									})
								]
							})
						],
						buttons: [
							BX.create('a', {
								text: BX.message('REPORT_LIST_BTN_SAVE'),
								props: {
									className: 'bx-report-btn bx-report-btn-big bx-report-btn-green'
								},
								events: {
									click: BX.delegate(function () {

										BX.Report.ajax({
											method: 'POST',
											dataType: 'json',
											url: BX.Report.addToLinkParam(
												this.ajaxUrl, 'action', 'changeSharing'),
											data: {
												reportId: reportId,
												entityToNewShared: entityToNewShared
											},
											onsuccess: BX.delegate(function (response) {
												if (!response) return;
												BX.Report.showModalWithStatusAction(response);
											}, this)
										});

										BX.PopupWindowManager.getCurrentPopup().close();
									}, this)
								}
							}),
							BX.create('a', {
								text: BX.message('REPORT_LIST_BTN_CLOSE'),
								props: {
									className: 'bx-report-btn bx-report-btn-big bx-report-btn-transparent'
								},
								events: {
									click: function () {
										BX.PopupWindowManager.getCurrentPopup().close();
									}
								}
							})
						]
					});
				}, this)
			}
		);
	};

	ReportListClass.prototype.onSelectDestination = function(item, type, search)
	{
		entityToNewShared[item.id] = entityToNewShared[item.id] || {};
		BX.Report.appendNewShared({
			maxAccessName: maxAccessName,
			readOnly: !!loadedReadOnlyEntityToNewShared[item.id],
			destFormName: this.destFormName,
			item: item,
			type: type,
			right: entityToNewShared[item.id].right
		});
		entityToNewShared[item.id] = {
			item: item,
			type: type,
			right: entityToNewShared[item.id].right || 'access_read'
		};
		BX.Report.show(BX('bx-report-popup-shared-people-list'));
	};

	ReportListClass.prototype.onUnSelectDestination = function (item, type, search)
	{
		var entityId = item.id;

		if(!!loadedReadOnlyEntityToNewShared[entityId])
		{
			return false;
		}

		delete entityToNewShared[entityId];

		var child = BX.findChild(BX('bx-report-popup-shared-people-list'),
			{attribute: {'data-dest-id': '' + entityId + ''}}, true);
		if (child) {
			BX.remove(child);
		}

		if(BX.Report.isEmptyObject(entityToNewShared))
		{
			BX.Report.hide(BX('bx-report-popup-shared-people-list'));
		}
	};

	ReportListClass.prototype.onOpenDialogDestination = function()
	{
		BX.style(BX('feed-add-post-destination-input-box'), 'display', 'inline-block');
		BX.style(BX('bx-destination-tag'), 'display', 'none');
		BX.focus(BX('feed-add-post-destination-input'));
		if(BX.SocNetLogDestination.popupWindow)
			BX.SocNetLogDestination.popupWindow.adjustPosition({ forceTop: true });
	};

	ReportListClass.prototype.onCloseDialogDestination = function()
	{
		var input = BX('feed-add-post-destination-input');
		if (!BX.SocNetLogDestination.isOpenSearch() && input && input.value.length <= 0)
		{
			BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
			BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
		}
	};

	ReportListClass.prototype.onOpenSearchDestination = function()
	{
		if(BX.SocNetLogDestination.popupSearchWindow)
			BX.SocNetLogDestination.popupSearchWindow.adjustPosition({ forceTop: true });
	};

	ReportListClass.prototype.onCloseSearchDestination = function()
	{
		var input = BX('feed-add-post-destination-input');
		if (!BX.SocNetLogDestination.isOpenSearch() && input && input.value.length > 0)
		{
			BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
			BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
			BX('feed-add-post-destination-input').value = '';
		}
	};

	ReportListClass.prototype.onKeyUpDestination = function (event)
	{
		var BXSocNetLogDestinationFormName = this.destFormName;
		if (event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 ||
			event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 224 || event.keyCode == 91)
			return false;

		if (event.keyCode == 13) {
			BX.SocNetLogDestination.selectFirstSearchItem(BXSocNetLogDestinationFormName);
			return BX.PreventDefault(event);
		}
		if (event.keyCode == 27) {
			BX('feed-add-post-destination-input').value = '';
		}
		else {
			BX.SocNetLogDestination.search(
				BX('feed-add-post-destination-input').value, true, BXSocNetLogDestinationFormName);
		}

		if (BX.SocNetLogDestination.sendEvent && BX.SocNetLogDestination.isOpenDialog())
			BX.SocNetLogDestination.closeDialog();

		if (event.keyCode == 8) {
			BX.SocNetLogDestination.sendEvent = true;
		}
		return BX.PreventDefault(event);
	};

	ReportListClass.prototype.onKeyDownDestination = function (event)
	{
		var BXSocNetLogDestinationFormName = this.destFormName;
		if (event.keyCode == 8 && BX('feed-add-post-destination-input').value.length <= 0) {
			BX.SocNetLogDestination.sendEvent = false;
			BX.SocNetLogDestination.deleteLastItem(BXSocNetLogDestinationFormName);
		}

		return true;
	};

	ReportListClass.prototype.onChangeRightOfSharing = function(entityId, taskName)
	{
		if(entityToNewShared[entityId])
		{
			entityToNewShared[entityId].right = taskName;
		}
	};

	ReportListClass.prototype.filterUser = function(userData, object)
	{
		if(!userData)
			return;

		var ownerId = object.searchInput.getAttribute('id').replace('filter_search_', '');
		var table = document.querySelector('#reports-company-'+ownerId),
			listItem = table.querySelectorAll('tr:nth-child(n+2)'),
			user = Object.keys(userData);

		this.showAllItem(ownerId);
		for(var i = 0; i < listItem.length; i++)
		{
			var createdBy = listItem[i].getAttribute('data-item');
			if(createdBy !== user[0])
			{
				BX.Report.hide(listItem[i]);
			}
		}

		BX('filter-delete-user-'+ownerId).style.visibility = 'visible';
	};

	ReportListClass.prototype.showAllItem = function(ownerId)
	{
		var table = document.querySelector('#reports-company-'+ownerId),
			listItem = table.querySelectorAll('tr:nth-child(n+2)');
		for(var i = 0; i < listItem.length; i++)
		{
			BX.Report.show(listItem[i]);
		}
	};

	ReportListClass.prototype.clearSelectUser = function(ownerId)
	{
		BX('filter_search_'+ownerId).value = '';
		BX('filter_data_'+ownerId).value = '';
		var object = 'filter_'+ownerId;
		BX.ReportUserSearchPopup.items[object]._currentUser = '';
		BX('filter-delete-user-'+ownerId).style.visibility = '';
		this.showAllItem(ownerId);
	};

	ReportListClass.prototype.export = function(reportId)
	{
		var form = BX.create('form', {
			props: {
				method: 'POST'
			},
			children: [
				BX.create('input', {
					props: {
						type: 'hidden',
						name: 'sessid',
						value: BX.bitrix_sessid()
					}
				}),
				BX.create('input', {
					props: {
						type: 'text',
						name: 'EXPORT_REPORT',
						value: BX.util.htmlspecialchars(reportId)
					}
				})
			]
		});

		BX.PopupMenu.currentItem.popupWindow.close();

		document.body.appendChild(form);
		BX.submit(form);
	};

	ReportListClass.prototype.import = function()
	{
		var content = BX.create('div', {
			props: {
				className: 'bx-report-popup-content bx-report-popup-content-import'
			},
			children: [
				BX.create('form', {
					props: {
						id: 'report-import-form',
						method: 'POST',
						enctype: 'multipart/form-data'
					},
					children: [
						BX.create('input', {
							props: {
								type: 'hidden',
								name: 'sessid',
								value: BX.bitrix_sessid()
							}
						}),
						BX.create('input', {
							props: {
								type: 'hidden',
								name: 'IMPORT_REPORT',
								value: '1'
							}
						}),
						BX.create('span', {
							props: {
								className: 'bx-report-description-import'
							},
							text: BX.message('REPORT_IMPORT_DESCRIPTION')
						}),
						BX.create('span', {
							props: {
								className: 'file-wrapper'
							},
							children: [
								BX.create('span', {
									props: {
										className: 'bx-report-input-file'
									},
									children: [
										BX.create('span', {
											props: {
												className: 'webform-small-button bx-report-small-button'
											},
											text: BX.message('REPORT_IMPORT_BUTTON_TEXT')
										}),
										BX.create('input', {
											props: {
												type: 'file',
												name: 'IMPORT_REPORT_FILE'
											}
										})
									]
								}),
								BX.create('span', {
									props: {
										className: 'fileformlabel bx-report-input-file-name'
									}
								})
							]
						})
					]
				})
			]
		});

		var uploadedFile = false, uploadedFileExt = true;
		BX.Report.modalWindow({
			modalId: 'bx-report-popup',
			contentClassName: 'bx-report-popup-content-import',
			title: BX.message('REPORT_IMPORT_TITLE'),
			overlay: false,
			autoHide: true,
			contentStyle: {
				width: '420px'
			},
			content: [content],
			events : {
				onPopupClose : function() {
				},
				onAfterPopupShow : function(popup) {
					var title = BX.findChild(
						popup.contentContainer, {className: 'bx-report-popup-title'}, true);
					if (title)
					{
						title.style.cursor = 'move';
						BX.bind(title, 'mousedown', BX.proxy(popup._startDrag, popup));
					}

					var wrappers = document.getElementsByClassName('bx-report-input-file');
					for (var i = 0; i < wrappers.length; i++)
					{
						var inputs = wrappers[i].getElementsByTagName('input');
						for (var j = 0; j < inputs.length; j++)
						{
							inputs[j].onchange = getName;
						}
					}
					function getName ()
					{
						uploadedFileExt = true;
						uploadedFile = true;
						var str = this.value, i;
						if (str.lastIndexOf('\\'))
							i = str.lastIndexOf('\\')+1;
						else
							i = str.lastIndexOf('/')+1;
						str = str.slice(i);
						if(this.value.split('.').pop() !== 'csv')
							uploadedFileExt = false;
						this.parentNode.parentNode.getElementsByClassName('fileformlabel')[0].style.color = '';
						var uploaded = this.parentNode.parentNode.getElementsByClassName('fileformlabel')[0];
						uploaded.innerHTML = str;
					}
				}
			},
			buttons: [
				BX.create('a', {
					text : BX.message('REPORT_LIST_BTN_SAVE'),
					props: {
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function (e) {

							var fileNameSpan = BX('bx-report-popup').getElementsByClassName('fileformlabel')[0];
							if(uploadedFile && uploadedFileExt)
								BX('report-import-form').submit();
							else
								fileNameSpan.style.color = 'red';

							if(!uploadedFile)
								fileNameSpan.innerHTML = BX.message('REPORT_IMPORT_ERROR_UPLOADED_FILE');
							if(!uploadedFileExt)
								fileNameSpan.innerHTML = BX.message('REPORT_IMPORT_ERROR_FILE_EXT');

						}, this)
					}
				}),
				BX.create('a', {
					text : BX.message('REPORT_LIST_BTN_CLOSE'),
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
	};

	return ReportListClass;
})();