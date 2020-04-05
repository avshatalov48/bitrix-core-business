BX.namespace("BX.Lists");
BX.Lists.ListsEditClass = (function ()
{
	var ListsEditClass = function (parameters)
	{
		this.randomString = parameters.randomString;
		this.iblockTypeId = parameters.iblockTypeId;
		this.iblockId = parameters.iblockId;
		this.socnetGroupId = parameters.socnetGroupId;
		this.jsClass = 'ListsEditClass_'+parameters.randomString;
		this.listsUrl = parameters.listsUrl || '';
		this.listAction = parameters.listAction;
		this.listTemplateEditUrl = parameters.listTemplateEditUrl;

		this.init();
	};

	ListsEditClass.prototype.init = function ()
	{
		this.ajaxUrl = '/bitrix/components/bitrix/lists.list.edit/ajax.php';

		this.actionButton = BX('lists-title-action');
		this.actionPopupItems = [];
		this.actionPopupObject = null;
		this.actionPopupId = 'lists-title-action';
		BX.bind(this.actionButton, 'click', BX.delegate(this.showListAction, this));
	};

	ListsEditClass.prototype.showListAction = function ()
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

	ListsEditClass.prototype.copyIblock = function()
	{
		BX.Lists.modalWindow({
			modalId: 'bx-lists-migrate-list',
			title: BX.message('CT_BLLE_COPY_POPUP_TITLE'),
			draggable: true,
			contentClassName: '',
			contentStyle: {
				width: '400px',
				padding: '20px 20px 20px 20px'
			},
			events: {
				onPopupClose : function() {
					this.destroy();
				}
			},
			content: BX.message('CT_BLLE_COPY_POPUP_CONTENT'),
			buttons: [
				BX.create('span', {
					text : BX.message("CT_BLLE_COPY_POPUP_ACCEPT_BUTTON"),
					props: {
						id: 'lists-popup-button-copy-accept',
						className: 'webform-small-button webform-small-button-accept'
					},
					events : {
						click : BX.delegate(function() {
							if(BX.hasClass(BX('lists-popup-button-copy-accept'), 'webform-small-button-wait')) return;
							BX.addClass(BX('lists-popup-button-copy-accept'), 'webform-small-button-wait');
							BX.Lists.ajax({
								method: 'POST',
								dataType: 'json',
								url: BX.Lists.addToLinkParam(this.ajaxUrl, 'action', 'copyIblock'),
								data: {
									iblockTypeId: this.iblockTypeId,
									iblockId: this.iblockId,
									socnetGroupId: this.socnetGroupId
								},
								onsuccess: BX.delegate(function (result)
								{
									if(result.status == 'success')
									{
										BX.Lists.showModalWithStatusAction({
											status: 'success',
											message: result.message
										});
										this.listTemplateEditUrl = this.listTemplateEditUrl
											.replace('#list_id#', result.copyIblockId)
											.replace('#group_id#', this.socnetGroupId);
										setTimeout(BX.delegate(function() {
											document.location.href = this.listTemplateEditUrl
										}, this), 1000);
										BX.removeClass(BX('lists-popup-button-copy-accept'), 'webform-small-button-wait');
									}
									else
									{
										result.errors = result.errors || [{}];
										BX.Lists.showModalWithStatusAction({
											status: 'error',
											message: result.errors.pop().message
										});
									}
								}, this)
							});
						}, this)
					}
				}),
				BX.create('span', {
					text : BX.message("CT_BLLE_COPY_POPUP_CANCEL_BUTTON"),
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
	};

	ListsEditClass.prototype.deleteIblock = function(form_id, message)
	{
		var _form = BX(form_id);
		var _flag = BX('action');
		if(_form && _flag)
		{
			BX.Lists.modalWindow({
				modalId: 'bx-lists-migrate-list',
				title: BX.message('CT_BLLE_DELETE_POPUP_TITLE'),
				draggable: true,
				contentClassName: '',
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
						text : BX.message("CT_BLLE_DELETE_POPUP_ACCEPT_BUTTON"),
						props: {
							className: 'webform-small-button webform-small-button-accept'
						},
						events : {
							click : BX.delegate(function() {
								_flag.value = 'delete';
								_form.submit();
							}, this)
						}
					}),
					BX.create('span', {
						text : BX.message("CT_BLLE_DELETE_POPUP_CANCEL_BUTTON"),
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

	ListsEditClass.prototype.migrateList = function(formId, message)
	{
		var _form = BX(formId);
		var _flag = BX('action');
		if(_form && _flag)
		{
			BX.Lists.modalWindow({
				modalId: 'bx-lists-migrate-list',
				title: BX.message('CT_BLLE_MIGRATE_POPUP_TITLE'),
				draggable: true,
				contentClassName: '',
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
						text : BX.message("CT_BLLE_MIGRATE_POPUP_ACCEPT_BUTTON"),
						props: {
							className: 'webform-small-button webform-small-button-accept'
						},
						events : {
							click : BX.delegate(function() {
								_flag.value = 'migrate';
								_form.submit();
							}, this)
						}
					}),
					BX.create('span', {
						text : BX.message("CT_BLLE_MIGRATE_POPUP_CANCEL_BUTTON"),
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

	return ListsEditClass;
})();
