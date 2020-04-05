;(function(window) {
	function ConfirmEditDialog(calendar)
	{
		this.calendar = calendar;
		this.id = this.calendar.id + '_confirm_edit';
		this.zIndex = 3200;
	}

	ConfirmEditDialog.prototype = {
		show: function (params)
		{
			var content = BX.create('DIV');
			this.dialog = new BX.PopupWindow(this.id, null, {
				overlay: {opacity: 10},
				autoHide: true,
				closeByEsc : true,
				zIndex: this.zIndex,
				offsetLeft: 0,
				offsetTop: 0,
				draggable: true,
				bindOnResize: false,
				titleBar: BX.message('EC_EDIT_REC_EVENT'),
				closeIcon: { right : "12px", top : "10px"},
				className: 'bxc-popup-window',
				buttons: [
					new BX.PopupWindowButtonLink({
						text: BX.message('EC_SEC_SLIDER_CANCEL'),
						className: "popup-window-button-link-cancel",
						events: {click : BX.proxy(this.close, this)}
					})
				],
				content: content,
				events: {}
			});

			content.appendChild(new BX.PopupWindowButton({
				text: BX.message('EC_REC_EV_ONLY_THIS_EVENT'),
				events: {click : BX.delegate(function()
				{
					if (params.callback && typeof params.callback == 'function')
					{
						params.params.recurentEventEditMode = 'this';
						params.params.confirmed = true;
						params.callback(params.params);
					}
					this.close();
				}, this)}
			}).buttonNode);

			content.appendChild(new BX.PopupWindowButton({
				text: BX.message('EC_REC_EV_NEXT'),
				events: {click : BX.delegate(function()
				{
					if (params.callback && typeof params.callback == 'function')
					{
						params.params.recurentEventEditMode = 'next';
						params.params.confirmed = true;
						params.callback(params.params);
					}
					this.close();
				}, this)}
			}).buttonNode);

			content.appendChild(new BX.PopupWindowButton(
				{
					text: BX.message('EC_REC_EV_ALL'),
					events: {click : BX.delegate(function()
					{
						if (params.callback && typeof params.callback == 'function')
						{
							params.params.recurentEventEditMode = 'all';
							params.params.confirmed = true;
							params.callback(params.params);
						}
						this.close();
					}, this)}
				}).buttonNode);

			this.dialog.show();
		},

		close: function()
		{
			if (this.dialog)
			{
				this.dialog.close();
			}
		}
	};


	function ConfirmDeleteDialog(calendar)
	{
		this.calendar = calendar;
		this.id = this.calendar.id + '_confirm_delete';
		this.zIndex = 3200;
	}

	ConfirmDeleteDialog.prototype = {
		show: function (entry)
		{
			var content = BX.create('DIV');
			this.dialog = new BX.PopupWindow(this.id, null, {
				overlay: {opacity: 10},
				autoHide: true,
				closeByEsc : true,
				zIndex: this.zIndex,
				offsetLeft: 0,
				offsetTop: 0,
				draggable: true,
				bindOnResize: false,
				titleBar: BX.message('EC_DEL_REC_EVENT'),
				closeIcon: { right : "12px", top : "10px"},
				className: 'bxc-popup-window',
				buttons: [
					new BX.PopupWindowButtonLink({
						text: BX.message('EC_SEC_SLIDER_CANCEL'),
						className: "popup-window-button-link-cancel",
						events: {click : BX.proxy(this.close, this)}
					})
				],
				content: content,
				events: {}
			});

			content.appendChild(new BX.PopupWindowButton({
				text: BX.message('EC_REC_EV_ONLY_THIS_EVENT'),
				events: {click : BX.delegate(function()
				{
					if (entry.isRecursive())
					{
						this.calendar.entryController.excludeRecursionDate(entry);
					}
					else if (entry.hasRecurrenceId())
					{
						this.calendar.entryController.deleteEntry(entry, {
							confirmed: true,
							recursionMode: 'this'
						});
					}
					this.close();
				}, this)}
			}).buttonNode);

			content.appendChild(new BX.PopupWindowButton({
				text: BX.message('EC_REC_EV_NEXT'),
				events: {click : BX.delegate(function()
				{
					if (entry.isRecursive() && entry.isFirstReccurentEntry())
					{
						this.calendar.entryController.deleteAllReccurent(entry);
					}
					else
					{
						this.calendar.entryController.cutOffRecursiveEvent(entry);
					}
					this.close();
				}, this)}
			}).buttonNode);

			content.appendChild(new BX.PopupWindowButton(
				{
					text: BX.message('EC_REC_EV_ALL'),
					events: {click : BX.delegate(function()
					{
						this.calendar.entryController.deleteAllReccurent(entry);
						this.close();
					}, this)}
				}).buttonNode);

			this.dialog.show();
		},

		close: function()
		{
			if (this.dialog)
			{
				this.dialog.close();
			}
		}
	};

	function ConfirmDeclineDialog(calendar)
	{
		this.calendar = calendar;
		this.id = this.calendar.id + '_confirm_decline';
		this.zIndex = 3200;
	}

	ConfirmDeclineDialog.prototype = {
		show: function (entry)
		{
			var content = BX.create('DIV');
			this.dialog = new BX.PopupWindow(this.id, null, {
				overlay: {opacity: 10},
				autoHide: true,
				closeByEsc : true,
				zIndex: this.zIndex,
				offsetLeft: 0,
				offsetTop: 0,
				draggable: true,
				bindOnResize: false,
				titleBar: BX.message('EC_DECLINE_REC_EVENT'),
				closeIcon: { right : "12px", top : "10px"},
				className: 'bxc-popup-window',
				buttons: [
					new BX.PopupWindowButtonLink({
						text: BX.message('EC_SEC_SLIDER_CANCEL'),
						className: "popup-window-button-link-cancel",
						events: {click : BX.proxy(this.close, this)}
					})
				],
				content: content,
				events: {}
			});

			content.appendChild(new BX.PopupWindowButton({
				text: BX.message('EC_DECLINE_ONLY_THIS'),
				events: {click : BX.delegate(function()
				{
					this.calendar.entryController.setMeetingStatus(entry, 'N',
					{
						confirmed: true,
						recursionMode: 'this'
					});
					this.close();
				}, this)}
			}).buttonNode);

			content.appendChild(new BX.PopupWindowButton({
				text: BX.message('EC_DECLINE_NEXT'),
				events: {click : BX.delegate(function()
				{
					this.calendar.entryController.setMeetingStatus(entry, 'N',
					{
						confirmed: true,
						recursionMode: 'next'
					});
					this.close();
				}, this)}
			}).buttonNode);

			content.appendChild(new BX.PopupWindowButton(
				{
					text: BX.message('EC_DECLINE_ALL'),
					events: {click : BX.delegate(function()
					{
						this.calendar.entryController.setMeetingStatus(entry, 'N',
						{
							confirmed: true,
							recursionMode: 'all'
						});
						this.close();
					}, this)}
				}).buttonNode);

			this.dialog.show();
		},

		close: function()
		{
			if (this.dialog)
			{
				this.dialog.close();
			}
		}
	};

	function BusyUsersDialog(calendar)
	{
		this.calendar = calendar;
		this.id = this.calendar.id + '_busy_users';
		this.zIndex = 3200;
		this.plural = false;
	}

	BusyUsersDialog.prototype = {
		show: function (params)
		{
			this.plural = params.users.length > 1;

			var i, userNames = [];
			for (i = 0; i < params.users.length; i++)
			{
				userNames.push(params.users[i].name);
			}
			userNames = userNames.join(', ');

			var content = BX.create('DIV', {
				props: {className: 'calendar-busy-users-content-wrap'},
				html: '<div class="calendar-busy-users-content">'
				+ BX.util.htmlspecialchars(this.plural ?
					BX.message('EC_BUSY_USERS_PLURAL').replace('#USER_LIST#', userNames)
					:
					BX.message('EC_BUSY_USERS_SINGLE').replace('#USER_NAME#', params.users[0].name))
				+ '</div>'
			});

			this.dialog = new BX.PopupWindow(this.id, null, {
				overlay: {opacity: 10},
				autoHide: true,
				closeByEsc : true,
				zIndex: this.zIndex,
				offsetLeft: 0,
				offsetTop: 0,
				draggable: true,
				bindOnResize: false,
				titleBar: BX.message('EC_BUSY_USERS_TITLE'),
				closeIcon: { right : "12px", top : "10px"},
				className: 'bxc-popup-window',
				buttons: [
					new BX.PopupWindowButtonLink({
						text: BX.message('EC_BUSY_USERS_CLOSE'),
						className: "popup-window-button-link-cancel",
						events: {click : BX.delegate(function()
							{
								if (this.simpleEntryPopup)
									this.simpleEntryPopup.close();

								if (this.calendar.editSlider)
									this.calendar.editSlider.close();

								this.close();
							}, this)
						}
					})
				],
				content: content,
				events: {}
			});

			content.appendChild(new BX.PopupWindowButton({
				text: BX.message('EC_BUSY_USERS_BACK2EDIT'),
				events: {click : BX.delegate(function()
					{
						this.close();
					}, this)
				}
			}).buttonNode);

			content.appendChild(new BX.PopupWindowButton({
				text: this.plural ? BX.message('EC_BUSY_USERS_EXCLUDE_PLURAL') : BX.message('EC_BUSY_USERS_EXCLUDE_SINGLE'),
				events: {click : BX.delegate(function()
				{
					if (BX.type.isFunction(params.saveCallback))
					{
						params.saveCallback();
					}
					this.close();
				}, this)}
			}).buttonNode);

			this.dialog.show();
		},

		close: function()
		{
			if (this.dialog)
			{
				this.dialog.close();
			}
		}
	};


	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.ConfirmEditDialog = ConfirmEditDialog;
		window.BXEventCalendar.ConfirmDeleteDialog = ConfirmDeleteDialog;
		window.BXEventCalendar.ConfirmDeclineDialog = ConfirmDeclineDialog;
		window.BXEventCalendar.BusyUsersDialog = BusyUsersDialog;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.ConfirmEditDialog = ConfirmEditDialog;
			window.BXEventCalendar.ConfirmDeleteDialog = ConfirmDeleteDialog;
			window.BXEventCalendar.ConfirmDeclineDialog = ConfirmDeclineDialog;
			window.BXEventCalendar.BusyUsersDialog = BusyUsersDialog;
		});
	}
})(window);