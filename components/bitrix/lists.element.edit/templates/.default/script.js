BX.namespace("BX.Lists");
BX.Lists.ListsElementEditClass = (function ()
{
	var ListsElementEditClass = function (parameters)
	{
		this.formId = parameters.formId;
		this.randomString = parameters.randomString;
		this.urlTabBp = parameters.urlTabBp;
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.elementId = parameters.elementId;
		this.socnetGroupId = parameters.socnetGroupId;
		this.sectionId = parameters.sectionId;
		this.jsClass = 'ListsElementEditClass_' + parameters.randomString;
		this.elementUrl = parameters.elementUrl;
		this.sectionUrl = parameters.sectionUrl;
		this.isConstantsTuned = parameters.isConstantsTuned;
		this.lockStatus = parameters.lockStatus;
		this.startTime = Math.round(Date.now() / 1000);

		this.init();
	};

	ListsElementEditClass.prototype.init = function()
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.element.edit/ajax.php';
		if (this.isConstantsTuned)
		{
			this.setConstants();
		}

		const form = document.forms.namedItem('form_' + this.formId);
		if (form)
		{
			const saveButton = form.querySelector('[name="save"]');
			if (saveButton)
			{
				saveButton.onclick = (event) => {
					const timeElement = BX.Dom.create('input', {
						attrs: {
							name: 'timeToStart',
							type: 'hidden',
							value: Math.round(Date.now() / 1000) - this.startTime,
						},
					});
					BX.Dom.append(timeElement, form);
				};
			}
		}
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
				action: action,
			},
			onsuccess: BX.delegate(function(result)
			{
				if (result.status === 'success')
				{
					BX.Lists.showModalWithStatusAction({
						status: 'success',
						message: result.message,
					});
					setTimeout(BX.delegate(function() {
						document.location.href = this.urlTabBp;
					}, this), 1000);
				}
				else
				{
					result.errors = result.errors || [{}];
					BX.Lists.showModalWithStatusAction({
						status: 'error',
						message: result.errors.pop().message,
					});
				}
			}, this),
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
				sectionId: this.sectionId,
			},
			onsuccess: BX.delegate(function (result)
			{
				if (result.status === 'success')
				{
					if (result.admin === false)
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

	ListsElementEditClass.prototype.unLock = function(onBeforeUnload)
	{
		BX.ajax.runAction('lists.controller.lock.unLock', {
			data: {
				element_id: this.elementId,
				iblock_type_id: this.iblockTypeId,
				iblock_id: this.iblockId,
				socnet_group_id: this.socnetGroupId,
			},
		}).then((response) => {
			if (!onBeforeUnload)
			{
				document.location.href = this.sectionUrl;
			}
		}).catch();
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
				listTemplateId: listTemplateId,
			},
			onsuccess: BX.delegate(function(result)
			{
				content = BX.create('div', {
					props: {
						className: 'lists-fill-constants-content'
					},
					html: result,
				});

				var modalWindow = BX.Lists.modalWindow({
					modalId: 'bx-lists-popup',
					withoutWindowManager: true,
					title: BX.message('CT_BLEE_BIZPROC_CONSTANTS_FILL_TITLE'),
					autoHide: false,
					overlay: false,
					draggable: true,
					contentStyle: {
						width: '600px',
						paddingTop: '10px',
						paddingBottom: '10px',
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

		for (var k in listAdmin)
		{
			let img = null;
			if (listAdmin[k].img)
			{
				img = BX.create('img', {
					attrs: {
						src: listAdmin[k].img,
					},
				});
			}

			domElement.appendChild(
				BX.create('div', {
					props: { className: 'lists-notify-question-item' },
					children: [
						BX.create('a', {
							props: { className: 'lists-notify-question-item-avatar' },
							attrs: {
								href: 'javascript:void(0)',
							},
							children: [
								BX.create('span', {
									props: {
										id: 'lists-notify-question-item-avatar-inner',
										className: 'lists-notify-question-item-avatar-inner',
									},
									children: [img],
								}),
							],
						}),
						BX.create('span', {
							props: { className: 'lists-notify-question-item-info' },
							children: [
								BX.create('span', {
									html: listAdmin[k].name,
								}),
							],
						}),
						BX.create('span', {
							props: {
								id: 'lists-notify-success-' + listAdmin[k].id,
								className: 'lists-notify-success',
							},
						}),
						BX.create('a', {
							props: {
								id: 'lists-notify-button-' + listAdmin[k].id,
								className: 'webform-small-button lists-notify-small-button webform-small-button-blue'
							},
							attrs: {
								href: 'javascript:void(0)',
								onclick: 'BX.Lists["' + this.jsClass + '"].notify("' + listAdmin[k].id + '");',
							},
							html: BX.message('CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE_BUTTON'),
						}),
					],
				}),
			);
		}

		return domElement;
	};

	ListsElementEditClass.prototype.elementDelete = function(formId, message)
	{
		const form = document.getElementById(formId);
		const flag = document.getElementById('action');
		if (form && flag)
		{
			BX.UI.Dialogs.MessageBox.confirm(
				message,
				BX.Loc.getMessage('CT_BLEE_DELETE_POPUP_TITLE'),
				() => {
					flag.value = 'delete';
					form.submit();

					return true;
				},
				BX.Loc.getMessage('CT_BLEE_DELETE_POPUP_ACCEPT_BUTTON'),
			);
		}
	};

	return ListsElementEditClass;
})();
