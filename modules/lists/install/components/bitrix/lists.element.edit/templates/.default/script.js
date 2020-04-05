BX.namespace("BX.Lists");
BX.Lists.ListsElementEditClass = (function ()
{
	var ListsElementEditClass = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.urlTabBp = parameters.urlTabBp;
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.elementId = parameters.elementId;
		this.socnetGroupId = parameters.socnetGroupId;
		this.sectionId = parameters.sectionId;
		this.jsClass = 'ListsElementEditClass_'+parameters.randomString;
		this.elementUrl = parameters.elementUrl;
		this.listAction = parameters.listAction;
		this.isConstantsTuned = parameters.isConstantsTuned;

		this.init();
	};

	ListsElementEditClass.prototype.init = function ()
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.element.edit/ajax.php';

		this.actionButton = BX('lists-title-action');
		this.actionPopupItems = [];
		this.actionPopupObject = null;
		this.actionPopupId = 'lists-title-action';
		BX.bind(this.actionButton, 'click', BX.delegate(this.showListAction, this));

		if(this.isConstantsTuned) this.setConstants();
	};

	ListsElementEditClass.prototype.showListAction = function ()
	{
		if(!this.actionPopupItems.length)
		{
			for(var k = 0; k < this.listAction.length; k++)
			{
				this.actionPopupItems.push({
					text : this.listAction[k].text,
					onclick : this.listAction[k].action
				});
			}
		}
		if(!BX.PopupMenu.getMenuById(this.actionPopupId))
		{
			var buttonRect = this.actionButton.getBoundingClientRect();
			this.actionPopupObject = BX.PopupMenu.create(
				this.actionPopupId,
				this.actionButton,
				this.actionPopupItems,
				{
					closeByEsc : true,
					angle: true,
					offsetLeft: buttonRect.width/2,
					events: {
						onPopupShow: BX.proxy(function () {
							BX.addClass(this.actionButton, 'webform-button-active');
						}, this),
						onPopupClose: BX.proxy(function () {
							BX.removeClass(this.actionButton, 'webform-button-active');
						}, this)
					}
				}
			);
		}
		if(this.actionPopupObject) this.actionPopupObject.popupWindow.show();
	};

	ListsElementEditClass.prototype.completeWorkflow = function(workflowId, action)
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'completeWorkflow'),
			data: {
				workflowId: workflowId,
				iblockTypeId: this.iblockTypeId,
				elementId: this.elementId,
				iblockId: this.iblockId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId,
				action: action
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					BX.Lists.showModalWithStatusAction({
						status: 'success',
						message: result.message
					});
					setTimeout(BX.delegate(function() {
						document.location.href = this.urlTabBp
					}, this), 1000);
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

	ListsElementEditClass.prototype.setConstants = function()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'isConstantsTuned'),
			data: {
				iblockTypeId: this.iblockTypeId,
				iblockId: this.iblockId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					if(result.admin === false)
					{
						this.notifyAdmin();
					}
					else
					{
						this.fillConstants(result.templateData);
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

	ListsElementEditClass.prototype.fillConstants = function(listTemplateId)
	{
		if(!listTemplateId)
		{
			return;
		}

		var content = '';
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'html',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'fillConstants'),
			data: {
				iblockId: this.iblockId,
				listTemplateId: listTemplateId
			},
			onsuccess: BX.delegate(function (result)
			{
				content = BX.create('div', {
					props: {
						className: 'lists-fill-constants-content'
					},
					html: result
				});

				var modalWindow = BX.Lists.modalWindow({
					modalId: 'bx-lists-popup',
					withoutWindowManager: true,
					title: BX.message("CT_BLEE_BIZPROC_CONSTANTS_FILL_TITLE"),
					autoHide: false,
					overlay: false,
					draggable: true,
					contentStyle: {
						width: '600px',
						paddingTop: '10px',
						paddingBottom: '10px'
					},
					content: [content],
					events : {
						onPopupClose : function() {
							this.destroy();
						}
					},
					buttons: [
						BX.create('a', {
							text : BX.message("CT_BLEE_BIZPROC_SAVE_BUTTON"),
							props: {
								className: 'webform-small-button webform-small-button-accept'
							},
							events : {
								click : BX.delegate(function (e)
								{
									var form = BX.findChild(content, {tag: 'FORM'}, true);
									if (form)
									{
										form.modalWindow = modalWindow;
										form.onsubmit(form, e);
									}
								})
							}
						}),
						BX.create('a', {
							text : BX.message("CT_BLEE_BIZPROC_CANCEL_BUTTON"),
							props: {
								className: 'webform-small-button webform-button-cancel'
							},
							events : {
								click : BX.delegate(function (e) {
									if(!!modalWindow) modalWindow.close();
								}, this)
							}
						})
					]
				});
			}, this)
		});
	};

	ListsElementEditClass.prototype.notifyAdmin = function()
	{
		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'getListAdmin'),
			data: {
				iblockId: this.iblockId,
				iblockTypeId: this.iblockTypeId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					var content = this.createHtmlNotifyAdmin(result.listAdmin);
					BX('lists-notify-admin-popup-content').appendChild(content);

					BX.Lists.modalWindow({
						modalId: 'bx-lists-popup',
						title: BX.message('CT_BLEE_BIZPROC_NOTIFY_TITLE'),
						overlay: false,
						draggable: true,
						contentStyle: {
							width: '600px',
							paddingTop: '10px',
							paddingBottom: '10px'
						},
						content: [BX('lists-notify-admin-popup-content')],
						events : {
							onPopupClose : function() {
								BX('lists-notify-admin-popup').appendChild(BX('lists-notify-admin-popup-content'));
								this.destroy();
							}
						},
						buttons: [
							BX.create('a', {
								text : BX.message("CT_BLEE_BIZPROC_NOTIFY_ADMIN_BUTTON_CLOSE"),
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

	ListsElementEditClass.prototype.notify = function (userId)
	{
		if(!BX('lists-notify-button-'+userId))
		{
			return;
		}

		BX('lists-notify-button-'+userId).setAttribute('onclick','');

		BX.Lists.ajax({
			method: 'POST',
			dataType: 'json',
			url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'notifyAdmin'),
			data: {
				iblockId: this.iblockId,
				userId: userId,
				iblockTypeId: this.iblockTypeId,
				socnetGroupId: this.socnetGroupId,
				sectionId: this.sectionId,
				elementUrl: this.elementUrl
			},
			onsuccess: BX.delegate(function (result)
			{
				if(result.status == 'success')
				{
					BX.Lists.removeElement(BX('lists-notify-button-'+userId));
					BX('lists-notify-success-'+userId).innerHTML = result.message;
				}
				else
				{
					BX('lists-notify-button-'+userId).setAttribute(
						'onclick',
						'BX.Lists["'+this.jsClass+'"].notify("'+userId+'");'
					);
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message
					})
				}
			}, this)
		});
	};

	ListsElementEditClass.prototype.createHtmlNotifyAdmin = function(listAdmin)
	{
		if(!listAdmin)
		{
			return null;
		}

		var domElement;

		domElement = BX.create('div', {
			children: [
				BX.create('span', {
					props: {
						className: 'lists-notify-question'
					},
					children: [
						BX.create('span', {
							props: {
								innerHTML: '!',
								className: 'icon'
							}
						}),
						BX.create('span', {
							props: {
								innerHTML: BX.message('CT_BLEE_BIZPROC_SELECT_STAFF_SET_RESPONSIBLE')
							}
						})
					]
				}),
				BX.create('p', {
					html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_ONE')
				}),
				BX.create('p', {
					html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_TWO')
				}),
				BX.create('span', {
					props: {className: 'lists-notify-question-title'},
					html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE')
				})
			]
		});

		for(var k in listAdmin)
		{
			var img;
			if(listAdmin[k].img)
			{
				img = BX.create('img', {
					attrs: {
						src: listAdmin[k].img
					}
				});
			}

			domElement.appendChild(
				BX.create('div', {
					props: {className: 'lists-notify-question-item'},
					children: [
						BX.create('a', {
							props: {className: 'lists-notify-question-item-avatar'},
							attrs: {
								href: 'javascript:void(0)'
							},
							children: [
								BX.create('span', {
									props: {
										id: 'lists-notify-question-item-avatar-inner',
										className: 'lists-notify-question-item-avatar-inner'
									},
									children: [img]
								})
							]
						}),
						BX.create('span', {
							props: {className: 'lists-notify-question-item-info'},
							children: [
								BX.create('span', {
									html: listAdmin[k].name
								})
							]
						}),
						BX.create('span', {
							props: {
								id: 'lists-notify-success-'+listAdmin[k].id,
								className: 'lists-notify-success'
							}
						}),
						BX.create('a', {
							props: {
								id: 'lists-notify-button-'+listAdmin[k].id,
								className: 'webform-small-button lists-notify-small-button webform-small-button-blue'
							},
							attrs: {
								href: 'javascript:void(0)',
								onclick: 'BX.Lists["'+this.jsClass+'"].notify("'+listAdmin[k].id+'");'
							},
							html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE_BUTTON')
						})
					]
				})
			);
		}

		return domElement;
	};

	ListsElementEditClass.prototype.elementDelete = function(form_id, message)
	{
		var _form = document.getElementById(form_id);
		var _flag = document.getElementById('action');
		if(_form && _flag)
		{
			BX.Lists.modalWindow({
				modalId: 'bx-lists-migrate-list',
				title: BX.message('CT_BLEE_DELETE_POPUP_TITLE'),
				contentClassName: '',
				draggable: true,
				contentStyle: {
					width: '400px',
					padding: '20px 20px 20px 20px'
				},
				events: {
					onPopupClose : function() {
						this.destroy();
					}
				},
				content: message,
				buttons: [
					BX.create('span', {
						text : BX.message("CT_BLEE_DELETE_POPUP_ACCEPT_BUTTON"),
						props: {
							className: 'webform-small-button webform-small-button-accept'
						},
						events : {
							click : BX.delegate(function() {
								BX.PopupWindowManager.getCurrentPopup().close();
								_flag.value = 'delete';
								_form.submit();
							}, this)
						}
					}),
					BX.create('span', {
						text : BX.message("CT_BLEE_DELETE_POPUP_CANCEL_BUTTON"),
						props: {
							className: 'popup-window-button popup-window-button-link popup-window-button-link-cancel'
						},
						events : {
							click : BX.delegate(function() {
								BX.PopupWindowManager.getCurrentPopup().close();
							}, this)
						}
					})
				]
			});
		}
	};

	return ListsElementEditClass;
})();
