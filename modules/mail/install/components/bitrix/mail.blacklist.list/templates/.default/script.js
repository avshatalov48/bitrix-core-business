;(function ()
{
	BX.namespace('BX.Mail.Blacklist.List');
	BX.Mail.Blacklist.List = function (options)
	{
		this.gridId = options.gridId;
		this.mailAddBtn = document.querySelector('[data-role="blacklist-create-btn"]');
		this.addEventHandlers();
		this.popupButtonSave = new BX.PopupWindowButton({
			text: BX.message('MAIL_BLACKLIST_LIST_POPUP_BTN_ADD'),
			className: "popup-window-button-accept",
			events: {click: BX.delegate(this.onAddMailClick, this)}
		});
		this.popupButtonClose = new BX.PopupWindowButton({
			text: BX.message('MAIL_BLACKLIST_LIST_POPUP_BTN_CLOSE'),
			className: "popup-window-button-close",
			events: {click: BX.delegate(this.closePopup, this)}
		});
		this.popupEmailsList = this.getPopupInstance();
		BX.Mail.Blacklist.Repository.add(this);
	};
	BX.Mail.Blacklist.List.prototype = {
		addEventHandlers: function ()
		{
			BX.bind(this.mailAddBtn, 'click', BX.delegate(this.onMailAddBtnClick, this));
		},
		onAddMailClick: function ()
		{
			BX.ajax.runComponentAction('bitrix:mail.blacklist.list', 'addMails', {
				mode: 'class',
				data: new FormData(this.formBlacklist)
			}).then(
				function ()
				{
					var textarea = this.getMailsTextArea();
					if (textarea && textarea.value)
					{
						textarea.value = '';
					}
					this.reloadGrid.bind(this, {apply_filter: 'Y'})()
				}.bind(this),
				this.reloadGrid.bind(this, {hasAjaxDeleteError: 1, apply_filter: 'Y'})
			);
		},
		getMailsTextArea: function()
		{
			if (!this.textarea)
			{
				this.textarea = document.querySelector('[data-role="blacklist-mails-textarea"]');
			}
			return this.textarea;
		},
		onDeleteClick: function (id)
		{
			if (!window.confirm(BX.message('MAIL_BLACKLIST_LIST_AJAX_DELETE_CONFIRM')))
			{
				return false;
			}
			BX.ajax.runComponentAction('bitrix:mail.blacklist.list', 'delete', {
				mode: 'class',
				data: {id: id}
			}).then(
				this.reloadGrid.bind(this, {apply_filter: 'Y'}),
				this.reloadGrid.bind(this, {hasAjaxDeleteError: 1, apply_filter: 'Y'})
			);
		},
		onMailAddBtnClick: function ()
		{
			this.popupEmailsList.show();
		},
		closePopup: function (e)
		{
			this.popupEmailsList.close(e)
		},
		getPopupInstance: function ()
		{
			if (this.popupEmailsList)
			{
				return this.popupEmailsList;
			}
			this.popupEmailsList = new BX.PopupWindow('bx-messenger-popup-settings', null, {
				autoHide: true,
				zIndex: 200,
				overlay: {opacity: 50, backgroundColor: '#000000'},
				buttons: [this.popupButtonSave, this.popupButtonClose],
				draggable: {restrict: true},
				closeByEsc: true,
				events: {
					onAfterPopupShow: function ()
					{
						this.formBlacklist = document.querySelector('[name="form-add-mails-to-blacklist"]');
					}.bind(this)
				},
				titleBar: BX.message('MAIL_BLACKLIST_LIST_POPUP_TITLE'),
				closeIcon: true,
				contentColor: 'white',
				content: document.querySelector('.mail-blacklist-popup-wrapper')
			});
			return this.popupEmailsList;
		},
		reloadGrid: function (options)
		{
			this.popupEmailsList.close();
			var gridObject = BX.Main.gridManager.getById(this.gridId);
			if (gridObject.hasOwnProperty('instance'))
			{
				gridObject.instance.reloadTable('POST', options);
			}
		}
	};

	BX.Mail.Blacklist.Repository = {
		repo: [],
		add: function (list)
		{
			this.repo[list.gridId] = list;
		},
		getById: function (id)
		{
			return this.repo[id];
		}
	};
	BX.Mail.Blacklist.List.onDeleteClick = function (gridId, gridElementId)
	{
		var List = BX.Mail.Blacklist.Repository.getById(gridId);
		List.onDeleteClick(gridElementId);
	}
})();
