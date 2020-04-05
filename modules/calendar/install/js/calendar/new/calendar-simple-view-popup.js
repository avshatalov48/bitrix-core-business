;(function(window) {

	function SimpleViewPopup(calendar)
	{
		this.calendar = calendar;
		this.params = {};
	}

	SimpleViewPopup.prototype = {
		show: function(params)
		{
			var popup;
			this.params = params;
			this.entry = this.params.entry;

			var entryPart = params.entry.getPart(0);
			if (params.specialTarget)
			{
				this.bindNode = params.specialTarget;
			}
			else if (entryPart && entryPart.params && entryPart.params.wrapNode)
			{
				this.bindNode = entryPart.params.wrapNode;
			}

			if (!this.bindNode)
				return;

			popup = new BX.PopupWindow(this.calendar.id + "-simple-view-popup",
				this.bindNode,
				{
					autoHide: true,
					closeByEsc: true,
					offsetTop: 0,
					offsetLeft: 0,
					closeIcon: true,
					titleBar: true,
					draggable: true,
					resizable: false,
					lightShadow: true,
					className: 'calendar-simple-view-popup',
					content: this.createContent()
				});

			// Small hack to use transparent titlebar to drag&drop popup
			BX.addClass(popup.titleBar, 'calendar-add-popup-titlebar');
			BX.removeClass(popup.popupContainer, 'popup-window-with-titlebar');
			BX.removeClass(popup.closeIcon, 'popup-window-titlebar-close-icon');

			popup.show(true);

			this.popup = popup;

			this.popupButtonsContainer = popup.buttonsContainer;
			BX.addClass(popup.contentContainer, 'calendar-view-popup-wrap');
			BX.adjust(popup.contentContainer, {attrs: {style: ""}});

			popup.popupContainer.style.minHeight = (popup.popupContainer.offsetHeight - 20) + 'px';

			BX.bind(document, 'keydown', BX.proxy(this.keyHandler, this));
			BX.addCustomEvent(popup, 'onPopupClose', BX.proxy(this.close, this));
			this.calendar.disableKeyHandler();
		},

		close: function(params)
		{
			if (!params)
				params = {};

			this.calendar.enableKeyHandler();

			if (this.popup)
			{
				BX.removeCustomEvent(this.popup, 'onPopupClose', BX.proxy(this.close, this));
				this.popup.destroy();
			}

			if (this.params.closeCallback && typeof this.params.closeCallback == 'function')
				this.params.closeCallback();

			if (params.deselectEntry)
			{
				setTimeout(BX.delegate(function(){
					this.calendar.getView().deselectEntry();
				}, this), 300);
			}

			BX.unbind(document, 'keydown', BX.proxy(this.keyHandler, this));
		},

		isShown: function()
		{
			return this.popup && this.popup.isShown && this.popup.isShown();
		},

		createContent: function(params)
		{
			if (params && params.entry)
				this.entry = params.entry;

			this.wrap = BX.create('DIV', {props: {className: 'calendar-right-block-event-info'}});
			var row;

			this.DOM = {
				name: this.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-right-block-event-info-title'}, html: '<div class="calendar-right-block-event-info-title-calendar" style="background-color: ' + this.entry.color + ';"></div>' + BX.util.htmlspecialchars(this.entry.name)})),
				date: this.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-right-block-event-info-date'}, text: this.calendar.util.formatDateUsable(this.entry.from)})),
				tableWrap: this.wrap.appendChild(BX.create('TABLE', {props: {className: 'calendar-field-right-block-table'}})),
				buttonsWrap: this.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-right-block-event-info-btn-container'}}))
			};

			if (this.calendar.util.getDayCode(this.entry.from) == this.calendar.util.getDayCode(this.entry.to))
			{
				this.DOM.date.innerHTML = this.calendar.util.formatDateUsable(this.entry.from);
				if (this.entry.fullDay)
				{
					this.DOM.date.innerHTML += ', ' + BX.message('EC_ALL_DAY');
				}
				else
				{
					this.DOM.date.innerHTML += ', ' +
						this.calendar.util.formatTime(this.entry.from.getHours(), this.entry.from.getMinutes())
						+ ' &ndash; '
						+ this.calendar.util.formatTime(this.entry.to.getHours(), this.entry.to.getMinutes());
				}
			}
			else
			{
				this.DOM.date.innerHTML =
					this.calendar.util.formatDateUsable(this.entry.from) + ', '
					+ this.calendar.util.formatTime(this.entry.from.getHours(), this.entry.from.getMinutes())
					+ ' &ndash; '
					+ this.calendar.util.formatDateUsable(this.entry.to) + ', '
					+ this.calendar.util.formatTime(this.entry.to.getHours(), this.entry.to.getMinutes());
			}

			if (this.entry.isRecursive() && this.entry.data['~RRULE_DESCRIPTION'])
			{
				row = this.DOM.tableWrap.insertRow(-1);
				BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-name'}, html: BX.message('EC_REPEAT') + ':'});
				BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-value'}, html: '<div class="calendar-field-container calendar-field-container-text"><div class="calendar-field-block"><div class="calendar-text">' +
				BX.util.htmlspecialchars(this.entry.data['~RRULE_DESCRIPTION']) + '</div></div></div>'});
			}

			if (this.calendar.util.isMeetingsEnabled() && this.entry.isMeeting() && this.entry.getAttendees().length > 0)
			{
				row = this.DOM.tableWrap.insertRow(-1);
				BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-name'}, html: BX.message('EC_HOST') + ':'});

				this.DOM.hostWrap = BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-value'}})
					.appendChild(BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-members'}}))
					.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block'}}))
					.appendChild(BX.create('DIV', {props: {className: 'calendar-add-popup-selected-members'}}));


				var host = false;
				this.showAttendees(this.DOM.hostWrap, this.entry.getAttendees().filter(function(user)
				{
					if (host)
						return false;
					host = user.STATUS == 'H' || (user.USER_ID == this.entry.getMeetingHost());
					return host;
				}, this));

				row = this.DOM.tableWrap.insertRow(-1);
				BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-name'}, html: BX.message('EC_ATTENDEES_LABEL') + ':'});

				this.DOM.attendeesWrap = BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-value'}})
					.appendChild(BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-members'}}))
					.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block'}}))
					.appendChild(BX.create('DIV', {props: {className: 'calendar-add-popup-selected-members'}}));

				this.showAttendees(this.DOM.attendeesWrap, this.entry.getAttendees().filter(function(user){
					return user.STATUS == 'Y' || user.STATUS == 'H';
				}), this.entry.getAttendees().length);
			}

			//var reminders = this.entry.getReminders();
			//if (reminders && reminders.length > 0)
			//{
			//	var i, html = '', str;
			//	for (i = 0; i < reminders.length; i++)
			//	{
			//		str = this.calendar.util.getTextReminder(reminders[i]);
			//		if (str)
			//		{
			//			html += '<span class="calendar-text-link">' + str + '</span>';
			//		}
			//	}
			//	row = this.DOM.tableWrap.insertRow(-1);
			//	BX.adjust(row.insertCell(-1), {html: BX.message('EC_REMIND') + ':'});
			//	BX.adjust(row.insertCell(-1), {html: '<div class="calendar-field-block"><div class="calendar-text">' + html + '</div></div>'});
			//}

			var location = this.calendar.util.getTextLocation(this.entry.location);
			if (location)
			{
				row = this.DOM.tableWrap.insertRow(-1);
				BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-name'}, html: BX.message('EC_LOCATION') + ':'});
				BX.adjust(row.insertCell(-1), {props: {className: 'calendar-field-table-cell-value'}, html: '<div class="calendar-field-container calendar-field-container-text"><div class="calendar-field-block"><div class="calendar-text">' +
				BX.util.htmlspecialchars(location) + '</div></div></div>'});
			}

			if (this.calendar.util.showEventDescriptionInSimplePopup())
			{
				this.entry.getDescription(BX.proxy(function(descriptionHTML)
				{
					if (BX.isNodeInDom(this.DOM.tableWrap) && descriptionHTML)
					{
						row = this.DOM.tableWrap.insertRow(-1);
						BX.adjust(row.insertCell(-1), {attrs: {colSpan: 2, className: 'calendar-field-table-cell-value'}, html: '<div class="calendar-field-container calendar-field-container-text calendar-container-short-description"><div class="calendar-field-block"><div class="calendar-text calendar-description-field">' + descriptionHTML + '</div></div></div>'});
					}
				}, this));
			}

			this.showButtons();

			return this.wrap;
		},

		showButtons: function()
		{
			// Buttons
			if (this.calendar.util.useViewSlider())
			{
				this.DOM.buttonsWrap.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-right-block-event-info-btn'},
					text: BX.message('EC_VIEW'),
					events: {click: BX.proxy(function(){
						if (this.entry.isTask())
						{
							BX.SidePanel.Instance.open(this.calendar.util.getViewTaskPath(this.entry.id), {loader: "task-new-loader"});
						}
						else
						{
							this.calendar.getView().showViewSlider({entry: this.entry});
						}
						this.close({deselectEntry: true});
					}, this)}
				}));
			}

			if (this.calendar.util.isMeetingsEnabled() && this.entry && this.entry.getCurrentStatus())
			{
				var status = this.entry.getCurrentStatus();

				if (status == 'Q')
				{
					this.DOM.buttonsWrap.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-right-block-event-info-btn'},
						text: BX.message('EC_VIEW_DESIDE_BUT_Y'),
						events: {click: BX.proxy(function(){
							this.calendar.entryController.setMeetingStatus(this.entry, 'Y');
							this.close({deselectEntry: true});
						}, this)}
					}));
					this.DOM.buttonsWrap.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-right-block-event-info-btn'},
						text: BX.message('EC_VIEW_DESIDE_BUT_N'),
						events: {click: BX.proxy(function(){
							this.calendar.entryController.setMeetingStatus(this.entry, 'N');
							this.close({deselectEntry: true});
						}, this)}
					}));
				}
				else if(status == 'Y')
				{
					this.DOM.buttonsWrap.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-right-block-event-info-btn'},
						text: BX.message('EC_VIEW_DESIDE_BUT_N'),
						events: {click: BX.proxy(function(){
							this.calendar.entryController.setMeetingStatus(this.entry, 'N');
							this.close({deselectEntry: true});
						}, this)}
					}));
				}
				else if(status == 'N')
				{
					this.DOM.buttonsWrap.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-right-block-event-info-btn'},
						text: BX.message('EC_VIEW_DESIDE_BUT_Y'),
						events: {click: BX.proxy(function(){
							this.calendar.entryController.setMeetingStatus(this.entry, 'Y');
							this.close({deselectEntry: true});
						}, this)}
					}));
				}
			}

			if (this.calendar.entryController.canDo(this.entry, 'edit') && !this.entry.isTask())
			{
				this.DOM.buttonsWrap.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-right-block-event-info-btn'},
					text: BX.message('EC_SEC_EDIT'),
					events: {click: BX.proxy(function(){
						this.calendar.entryController.editEntry({
							entry: this.entry
						});
						this.close({deselectEntry: true});
					}, this)}
				}));
			}

			if (this.calendar.entryController.canDo(this.entry, 'delete') && !this.entry.isTask())
			{
				this.DOM.buttonsWrap.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-right-block-event-info-btn'},
					text: BX.message('EC_SEC_DELETE'),
					events: {click: BX.proxy(function(){
						this.calendar.entryController.deleteEntry(this.entry);
						this.close({deselectEntry: true});
					}, this)}
				}));
			}
		},

		showAttendees: function(wrap, attendees, totalCount)
		{
			var
				i,
				user,
				MAX_USER_COUNT = 5,
				userLength = attendees.length,
				MAX_USER_COUNT_DISPLAY = 7;

			if (userLength > 0)
			{
				if (userLength > MAX_USER_COUNT_DISPLAY)
				{
					userLength = MAX_USER_COUNT;
				}

				for (i = 0; i < userLength; i++)
				{
					user = attendees[i] || {};

					wrap.appendChild(BX.create("IMG", {
						attrs: {
							id: 'simple_view_popup_' + user.ID,
							src: user.AVATAR || ''
						},
						props: {
							title: user.DISPLAY_NAME,
							className: 'calendar-member'
						}}));
					(function (userId){setTimeout(function(){BX.tooltip(userId, "simple_view_popup_" + userId);}, 100)})(user.ID);
				}

				if (userLength < attendees.length)
				{
					this.DOM.moreUsersLink = wrap.appendChild(BX.create("SPAN", {
						props: {className: 'calendar-member-more-count'},
						text: ' ' + BX.message('EC_ATTENDEES_MORE').replace('#COUNT#', attendees.length - userLength),
						events: {click: BX.delegate(function(){
							this.showUserListPopup(this.DOM.moreUsersLink, attendees);
						}, this)}
					}));
				}

				if (totalCount - 1 > 0 && userLength < totalCount)
				{
					this.DOM.allUsersLink = wrap.appendChild(BX.create("SPAN", {
						props: {className: 'calendar-member-total-count'},
						text: ' (' + BX.message('EC_ATTENDEES_TOTAL_COUNT').replace('#COUNT#', (totalCount - 1)) + ')',
						events: {
							click: BX.delegate(function ()
							{
								this.showUserListPopup(this.DOM.allUsersLink, this.entry.getAttendees());
							}, this)
						}
					}));
				}
			}
		},

		showUserListPopup: function(node, userList)
		{
			if (this.userListPopup)
				this.userListPopup.close();

			if (this.popup)
				this.popup.setAutoHide(false);

			if (!userList || !userList.length)
				return;

			this.DOM.userListPopupWrap = BX.create('DIV', {props: {className: 'calendar-user-list-popup-block'}});
			userList.forEach(function(user){
				var userWrap = this.DOM.userListPopupWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-container calendar-slider-sidebar-user-card'}}));

				userWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-block-avatar'}}))
					.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-block-item'}}))
					.appendChild(BX.create('IMG', {props: {width: 34, height: 34, src: user.AVATAR}}));

				userWrap.appendChild(
					BX.create("DIV", {props: {className: 'calendar-slider-sidebar-user-info'}}))
					.appendChild(BX.create("A", {
						props: {
							href: user.URL ? user.URL : '#',
							className: 'calendar-slider-sidebar-user-info-name'
						},
						text: user.DISPLAY_NAME
					}));
			}, this);

			this.userListPopup = new BX.PopupWindow(this.calendar.id + "-view-user-list-popup",
				node,
				{
					autoHide: true,
					closeByEsc: true,
					offsetTop: 0,
					offsetLeft: 0,
					width: 200,
					resizable: false,
					lightShadow: true,
					content: this.DOM.userListPopupWrap,
					className: 'calendar-user-list-popup'
				});

			this.userListPopup.setAngle({offset: 36});
			this.userListPopup.show();
			BX.addCustomEvent(this.userListPopup, 'onPopupClose', BX.delegate(function()
			{
				if (this.popup)
					this.popup.setAutoHide(true);
				this.userListPopup.destroy();
			}, this));
		},


		keyHandler: function(e)
		{
			var
				KEY_CODES = this.calendar.util.getKeyCodes(),
				keyCode = e.keyCode;

			if (this.entry)
			{
				if (keyCode == KEY_CODES['enter'])
				{
					if (this.entry.isTask())
					{
						BX.SidePanel.Instance.open(this.calendar.util.getViewTaskPath(this.entry.id), {loader: "task-new-loader"});
					}
					else
					{
						this.calendar.getView().showViewSlider({entry: this.entry});
					}
					this.close({deselectEntry: true});
				}
				else if (keyCode == KEY_CODES['delete'] || keyCode == KEY_CODES['backspace'])
				{
					this.calendar.entryController.deleteEntry(this.entry);
				}
			}
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.SimpleViewPopup = SimpleViewPopup;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.SimpleViewPopup = SimpleViewPopup;
		});
	}
})(window);