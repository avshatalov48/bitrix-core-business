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
		this.listElementUrl = parameters.listElementUrl;

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
		var popup = new BX.PopupWindow({
			titleBar: BX.message("CT_BLLE_COPY_POPUP_TITLE"),
			closeIcon: true,
			autoHide: true,
			closeByEsc: true,
			content: BX.message("CT_BLLE_COPY_POPUP_CONTENT"),
			buttons: [
				new BX.UI.Button({
					text: BX.message("CT_BLLE_COPY_POPUP_ACCEPT_BUTTON"),
					size: BX.UI.Button.Size.MEDIUM,
					color: BX.UI.Button.Color.SUCCESS,
					onclick: function(button, event) {
						button.setWaiting();
						BX.ajax.runAction("lists.controller.iblock.copy", {
							data: {
								iblock_type_id: this.iblockTypeId,
								iblock_id: this.iblockId,
								socnet_group_id: this.socnetGroupId,
								list_element_url: this.listElementUrl
							}
						}).then(function (response) {
							this.listTemplateEditUrl = this.listTemplateEditUrl
								.replace("#list_id#", response.data)
								.replace("#group_id#", this.socnetGroupId);
							BX.UI.Notification.Center.notify({
								content: BX.message("CT_BLLE_COPY_POPUP_COPIED_SUCCESS").replace(
									"#URL#", BX.util.htmlspecialchars(this.listTemplateEditUrl)),
								position: "top-right",
								closeButton: false
							});
							popup.close();
						}.bind(this), function (response) {
							button.setWaiting(false);
							BX.UI.Notification.Center.notify({
								content: response.errors.pop().message,
								position: "top-right",
								closeButton: false
							});
						});
					}.bind(this)
				}),
				new BX.UI.Button({
					text: BX.message("CT_BLLE_COPY_POPUP_CANCEL_BUTTON"),
					size: BX.UI.Button.Size.MEDIUM,
					color: BX.UI.Button.Color.LINK,
					onclick: function(button, event) {
						popup.close();
					}
				})
			]
		});
		popup.show();
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
