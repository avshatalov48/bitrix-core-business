;(function(window) {

	function SimpleAddPopup(calendar)
	{
		this.calendar = calendar;
	}

	SimpleAddPopup.prototype = {
		show: function(params)
		{
			this.params = params;
			this.entryTime = params.entryTime;
			this.attendees = [];
			this.attendeesCodesList = false;
			this.attendeesIndex = {};
			this.attendeesCodes = {};
			this.allowInvite = true;
			this.notify = true;
			this.secondSlideIsOpened = false;
			this.checkDataBeforeCloseMode = true;
			this.displayed = false;

			if (this.calendar.ownerUser)
			{
				this.attendees.push(this.calendar.ownerUser);
				this.attendeesIndex[this.calendar.ownerUser.id] = true;
				this.attendeesCodes['U' + this.calendar.ownerUser.id] = 'users';
			}

			this.attendees.push(this.calendar.currentUser);
			this.attendeesIndex[this.calendar.currentUser.id] = true;
			this.attendeesCodes['U' + this.calendar.currentUser.id] = 'users';

			this.initiateAttendees = [];
			this.attendees.forEach(function(user){this.initiateAttendees.push(user.id);}, this);

			// Check angle position
			var
				anglePosition,
				offsetLeft,
				offsetTop = -152,
				POPUP_WIDTH = 390,
				POPUP_HEIGHT = 420,
				ANGLE_WIDTH = 8,
				nodePos = BX.pos(params.entryNode),
				windowSize = BX.GetWindowSize();

			if (nodePos.right + POPUP_WIDTH + ANGLE_WIDTH < windowSize.innerWidth)
			{
				anglePosition = 'left';
				offsetLeft = nodePos.width + ANGLE_WIDTH;
			}
			else
			{
				anglePosition = 'right';
				offsetLeft = -POPUP_WIDTH - ANGLE_WIDTH;
			}

			if (windowSize.scrollTop + windowSize.innerHeight - (nodePos.bottom + POPUP_HEIGHT + offsetTop) < -35)
			{
				anglePosition = false;
			}

			var popup = new BX.PopupWindow(this.calendar.id + "-simple-add-popup",
				params.bindNode || params.entryNode,
				{
					offsetTop: offsetTop,
					offsetLeft: offsetLeft,
					closeIcon: true,
					width: POPUP_WIDTH,
					titleBar: true,
					draggable: true,
					resizable: false,
					lightShadow: true,
					content: this.createContent(),
					overlay: {
						opacity: 1
					},
					buttons : [
						new BX.PopupWindowButton({
							text : BX.message('JS_CORE_WINDOW_SAVE') + ' (ENTER)',
							className : "popup-window-button-accept",
							events : {click : BX.delegate(this.save, this)}
						}),
						new BX.PopupWindowButton({
							text : BX.message('JS_CORE_WINDOW_CANCEL'),
							events : {click : BX.delegate(this.close, this)}
						})
					]
				});

			if (anglePosition !== false)
			{
				popup.setAngle({
					offset: 130,
					position: anglePosition
				});
			}

			// Small hack to use transparent titlebar to drag&drop popup
			BX.addClass(popup.titleBar, 'calendar-add-popup-titlebar');
			BX.removeClass(popup.popupContainer, 'popup-window-with-titlebar');
			BX.removeClass(popup.closeIcon, 'popup-window-titlebar-close-icon');

			popup.show(true);
			this.popup = popup;

			if (popup.overlay && popup.overlay.element)
			{
				this.overlay = popup.overlay.element;
				BX.addClass(popup.overlay.element, 'calendar-popup-overlay');
				setTimeout(BX.delegate(function()
				{
					BX.addClass(popup.overlay.element, 'calendar-popup-overlay-dark');
					popup.overlay = null;
				}, this), 1);
			}

			this.popupButtonsContainer = popup.buttonsContainer;
			BX.addClass(popup.contentContainer, 'calendar-add-popup-wrap');

			popup.popupContainer.style.minHeight = (popup.popupContainer.offsetHeight - 20) + 'px';

			this.nameField.input.focus();
			this.nameField.input.select();

			BX.bind(document, 'keydown', BX.proxy(this.keyHandler, this));
			BX.addCustomEvent(popup, 'onPopupClose', BX.proxy(this.close, this));
			BX.bind(this.overlay, 'click', BX.proxy(this.close, this));

			this.calendar.disableKeyHandler();
			setTimeout(BX.delegate(function(){this.calendar.disableKeyHandler();}, this), 100);
			this.displayed = true;
		},

		save: function(params)
		{
			params = params || {};

			// check users accessibility
			if (params.checkBusyUsers !== false && this.calendar.util.isMeetingsEnabled())
			{
				var busyUsers = this.getBusyUserList();
				if (busyUsers && busyUsers.length > 0)
				{
					if (!this.busyUsersDialog)
						this.busyUsersDialog = new window.BXEventCalendar.BusyUsersDialog(this.calendar);

					this.busyUsersDialog.show({
						users: busyUsers,
						saveCallback: BX.delegate(function()
						{
							var i, userIds = [];
							for (i = 0; i < busyUsers.length; i++)
							{
								userIds.push(busyUsers[i].id);
							}
							this.excludeUsers = userIds.join(',');
							params.checkBusyUsers = false;
							this.save(params);
						}, this)
					});
					return;
				}
			}

			if (this.params.section.id)
			{
				var section = this.calendar.sectionController.getSection(this.params.section.id);
				if (section)
				{
					section.show();
				}
			}

			this.calendar.entryController.saveEntry(this.getPopupData());
			if (BX.type.isFunction(this.params.saveCallback))
			{
				this.params.saveCallback();
			}

			this.checkDataBeforeCloseMode = false;
			this.close();
		},

		close: function()
		{
			if (!this.popup || !this.popup.isShown())
			{
				return;
			}

			if (this.checkDataBeforeCloseMode
				&& !this.checkBeforeClose()
				&& !confirm(BX.message('EC_SAVE_ENTRY_CONFIRM')))
			{
				return;
			}

			this.calendar.enableKeyHandler();

			if (this.popup)
			{
				BX.removeCustomEvent(this.popup, 'onPopupClose', BX.proxy(this.close, this));
				this.popup.destroy();
			}

			if (this.overlay)
			{
				BX.removeClass(this.overlay, 'calendar-popup-overlay-dark');
				setTimeout(BX.delegate(function(){BX.remove(this.overlay);}, this), 300);
			}

			BX.removeCustomEvent('OnDestinationAddNewItem', BX.proxy(this.checkPlannerState, this));
			BX.removeCustomEvent('OnDestinationUnselect', BX.proxy(this.checkPlannerState, this));
			BX.removeCustomEvent('OnDestinationUnselect', BX.proxy(this.clearSecondSlideHeight, this));
			BX.removeCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.onCalendarPlannerSelectorChanged, this));
			if (this.plannerId)
			{
				BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{plannerId: this.plannerId}]);
			}

			if (BX.type.isFunction(this.params.closeCallback))
			{
				this.params.closeCallback();
			}

			BX.unbind(document, 'keydown', BX.proxy(this.keyHandler, this));
			BX.unbind(this.overlay, 'click', BX.proxy(this.close, this));

			this.displayed = false;
		},

		isShown: function()
		{
			return this.displayed;
		},

		couldBeClosedByEsc: function()
		{
			return !((this.dateTimeField.fromTime && this.dateTimeField.fromTime.shown) // from popup
				|| (this.dateTimeField.toTime && this.dateTimeField.toTime.shown) // to popup
				|| this.secondSlideIsOpened // second slider
				|| (this.reminderField && this.reminderField.reminder && this.reminderField.reminder.reminderMenu)
				|| (this.sectionSelector && this.sectionSelector.sectionMenu)
				|| (this.locationSelector && this.locationSelector.selectContol && this.locationSelector.selectContol.popupMenu)
				|| BX.PopupWindowManager.getCurrentPopup()
				);
		},

		getPopupData: function()
		{
			var attendees = [];
			this.attendees.forEach(function(user){attendees.push(user.id);});

			var
				fromTime = BX.Calendar.Util.parseTime(this.dateTimeField.fromTimeInput.value),
				toTime = BX.Calendar.Util.parseTime(this.dateTimeField.toTimeInput.value),
				fromDate = new Date(this.entryTime.from.getTime()),
				toDate = new Date(this.entryTime.from.getTime());

			fromDate.setHours(fromTime.h, fromTime.m, 0);
			toDate.setHours(toTime.h, toTime.m, 0);

			if (!this.attendeesCodesList && this.attendeesCodes)
			{
				this.attendeesCodesList = [];
				for (var i in this.attendeesCodes)
				{
					if (this.attendeesCodes.hasOwnProperty(i))
					{
						this.attendeesCodesList.push(i);
					}
				}
			}

			return {
				name: this.nameField.input.value,
				from: fromDate,
				to: toDate,
				dateFrom: this.calendar.util.formatDateTime(fromDate),
				dateTo: this.calendar.util.formatDateTime(toDate),
				defaultTz: this.timezoneField && this.timezoneField.select ? this.timezoneField.select.value : this.calendar.util.getUserOption('timezoneName'),
				section: this.params.section.id,
				location: this.locationSelector.getTextValue(),
				locationValue: this.locationSelector.getValue(),
				remind: this.reminderValues || false,
				attendees: attendees,
				attendeesCodes: this.attendeesCodes,
				attendeesCodesList: this.attendeesCodesList,
				meetingNotify: this.notify,
				allowInvite:  this.allowInvite,
				excludeUsers: this.excludeUsers || ''
			};
		},

		getEntry: function ()
		{
			var data = this.getPopupData();
			var entryData = {
				ID: null,
				NAME: data.name,
				dateFrom: data.from,
				dateTo: data.to,
				TZ_FROM: data.defaultTz,
				TZ_TO: data.defaultTz,
				SECT_ID: parseInt(data.section),
				LOCATION: data.location,
				ATTENDEES_CODES: data.attendeesCodesList,
				remind: data.remind
			};

			return new BX.Calendar.Entry({data: entryData});
		},

		createContent: function()
		{
			this.mainSlide = BX.create('DIV', {props: {className: 'calendar-add-popup-main-slide'}});
			this.secondSlide = false;

			// Entry name
			this.nameField = this.createField('string-select', this.mainSlide);
			this.nameField.input = this.nameField.innerWrap.appendChild(BX.create('INPUT', {
					props: {className: 'calendar-field calendar-field-string'},
					attrs: {
						value: this.params.entryName,
						placeholder: BX.message('EC_ENTRY_NAME'),
						type: 'text'
					},
					events:{
						keyup: BX.proxy(this.entryNameChanged, this),
						blur: BX.proxy(this.entryNameChanged, this),
						change: BX.proxy(this.entryNameChanged, this)
					}
				}));

			// Section select
			this.createSectionSelector();

			// Date & time
			this.createDateTimeField();

			// Reminder
			this.createReminderField();

			// Location
			this.createlocationField();

			// Attendees
			if (this.calendar.util.isMeetingsEnabled())
			{
				this.createPlannerField();
			}

			// Entry name
			this.fullFormField = this.createField('container-text', this.mainSlide);
			this.fullFormField.link = this.fullFormField.innerWrap.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-text-link'},
				text: BX.message('EC_FULL_FORM_LABEL'),
				events: {click : function()
					{
						this.checkDataBeforeCloseMode = false;
						if (BX.type.isFunction(this.params.fullFormCallback))
						{
							this.params.fullFormCallback();
						}
					}.bind(this)
				}
			}));

			this.mainSlide.appendChild(BX.create('HR', {props: {className: 'calendar-filed-separator'}}));

			this.sliderContainer = BX.create('DIV', {
				props: {
					className: "calendar-add-popup-slider-container"
				},
				children : [this.mainSlide]
			});

			return this.sliderContainer;
		},

		prepareSecondSlide: function(params)
		{
			this.closeSecondSlideCallback = params.closeCallback || null;
			if (this.secondSlide)
			{
				BX.cleanNode(this.secondSlide);
			}
			else
			{
				this.secondSlide = this.sliderContainer.appendChild(BX.create('DIV', {props: {className: 'calendar-add-popup-second-slide'}}));
			}

			this.backButton = this.secondSlide
				.appendChild(BX.create('DIV', {props: {className: 'calendar-add-popup-second-slide-header'}}))
				.appendChild(BX.create('SPAN', {props: {className: 'calendar-add-popup-second-slide-back-btn'}, html: BX.message('EC_SIMPLE_FORM_BACK')}));

			BX.bind(this.backButton, 'click', BX.proxy(this.closeSecondSlide, this));
			BX.bind(document, "keyup", BX.proxy(this.secondSlideEscHandler, this));

			this.popupButtonsContainer.style.display = 'none';
			this.resizeSecondSlide();

			setTimeout(BX.delegate(function(){BX.addClass(this.popup.contentContainer, 'calendar-add-popup-wrap-second-tab-active');}, this), 0);

			this.secondSlideIsOpened = true;
		},

		secondSlideEscHandler: function(e)
		{
			if(e.keyCode === this.calendar.util.KEY_CODES['escape'])
			{
				this.closeSecondSlide()
			}
		},

		closeSecondSlide: function()
		{
			if(this.closeSecondSlideCallback)
			{
				this.closeSecondSlideCallback();
			}

			BX.unbind(document, "keyup", BX.proxy(this.secondSlideEscHandler, this));
			BX.removeClass(this.popup.contentContainer, 'calendar-add-popup-wrap-second-tab-active');

			this.popupButtonsContainer.style.display = '';
			this.clearSecondSlideHeight();
			if (this.resizeTimeout)
			{
				this.resizeTimeout = clearTimeout(this.resizeTimeout);
			}
			BX.cleanNode(this.secondSlide);
			this.secondSlideIsOpened = false;
		},

		resizeSecondSlide: function()
		{
			if (this.secondSlide)
			{
				var height = this.secondSlide.scrollHeight;
				if (height != parseInt(this.popup.contentContainer.style.minHeight))
				{
					this.secondSlide.style.minHeight = height + 'px';
					this.sliderContainer.style.minHeight = height + 'px';
				}
				this.resizeTimeout = setTimeout(BX.proxy(this.resizeSecondSlide, this), 100);
			}
		},

		clearSecondSlideHeight: function()
		{
			if (this.secondSlide)
				this.secondSlide.style.minHeight = '';
			if (this.sliderContainer)
				this.sliderContainer.style.minHeight = '';
		},

		createField: function(type, parentNode)
		{
			if (!type)
				type = 'string';

			var
				outerWrap = BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-' + type}}),
				innerWrap = outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block'}}));

			if (parentNode)
				parentNode.appendChild(outerWrap);

			return {
				outerWrap: outerWrap,
				innerWrap: innerWrap
			};
		},

		createSectionSelector: function()
		{
			this.sectionSelector = new window.BXEventCalendar.SectionSelector({
				outerWrap: this.nameField.innerWrap,
				sectionList: this.calendar.sectionController.getSectionListForEdit(),
				sectionGroupList: this.calendar.sectionController.getSectionGroupList(),
				mode: 'compact',
				getCurrentSection: BX.delegate(function() {
					return this.params.section;
				}, this),
				selectCallback: BX.delegate(function(section) {
					if (section)
					{
						this.params.section = this.calendar.sectionController.getSection(section.id);
						//this.calendar.util.setUserOption('lastUsedSection', section.id);
						if (BX.type.isFunction(this.params.changeSectionCallback))
						{
							this.params.changeSectionCallback(section);
						}
					}
				}, this),
				// openPopupCallback: function() {this.popup.setAutoHide(false);}.bind(this),
				// closePopupCallback: function() {this.popup.setAutoHide(true);}.bind(this)
			});
		},

		createDateTimeField: function()
		{
			var _this = this;
			this.initiateTimeFrom = this.calendar.util.formatTime(this.entryTime.from.getHours(), this.entryTime.from.getMinutes());
			this.initiateTimeTo = this.calendar.util.formatTime(this.entryTime.to.getHours(), this.entryTime.to.getMinutes());

			this.dateTimeField = {
				outerWrap: this.mainSlide.appendChild(BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-datetime'}}))
			};

			this.dateTimeField.dateWrap = this.dateTimeField.outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block calendar-field-block-prefix'}, text: this.calendar.util.formatDateUsable(this.entryTime.from, false)}));

			this.dateTimeField.fromTimeInputWrap = this.dateTimeField.outerWrap.appendChild(BX.create('DIV', {
				props: {className: 'calendar-field-block calendar-field-block-left'
			}}));

			this.dateTimeField.fromTimeInput = this.dateTimeField.fromTimeInputWrap.appendChild(BX.create('INPUT', {
				attrs: {
					value: this.initiateTimeFrom,
					placeholder: BX.message('EC_TIME_FROM_PLACEHOLDER'),
					type: 'text'
				},
				props: {className: 'calendar-field calendar-field-datetime-menu'
			}}));
			this._fromDateValue = this.entryTime.from;

			this.dateTimeField.outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-field-block calendar-field-block-between'}}));

			this.dateTimeField.toTimeInputWrap = this.dateTimeField.outerWrap.appendChild(BX.create('DIV', {
				props: {className: 'calendar-field-block calendar-field-block-right'
			}}));

			this.dateTimeField.toTimeInput = this.dateTimeField.toTimeInputWrap.appendChild(BX.create('INPUT', {
				attrs: {
					value: this.initiateTimeTo,
					placeholder: BX.message('EC_TIME_TO_PLACEHOLDER'),
					type: 'text'
				},
				props: {className: 'calendar-field calendar-field-datetime-menu'
			}}));

			this.dateTimeField.fromTime = new window.BXEventCalendar.SelectInput({
				input: this.dateTimeField.fromTimeInput,
				value: this.calendar.util.adaptTimeValue(_this.calendar.util.parseTime(_this.dateTimeField.fromTimeInput.value)),
				values: this.calendar.util.getSimpleTimeList(),
				onChangeCallback: BX.proxy(function()
				{
					var
						fromTime = this.calendar.util.parseTime(this.dateTimeField.fromTimeInput.value),
						toTime = this.calendar.util.parseTime(this.dateTimeField.toTimeInput.value),
						fromDate = new Date(this.entryTime.from.getTime()),
						toDate = new Date(this.entryTime.from.getTime());

					fromDate.setHours(fromTime.h, fromTime.m, 0);
					toDate.setHours(toTime.h, toTime.m, 0);

					if (this._fromDateValue)
					{
						this.entryTime.to = new Date(fromDate.getTime() + ((toDate.getTime() - this._fromDateValue.getTime()) || 3600000));
						this.dateTimeField.toTimeInput.value = this.calendar.util.formatTime(this.entryTime.to.getHours(), this.entryTime.to.getMinutes());
					}

					this.entryTime.from = fromDate;
					this._fromDateValue = fromDate;

					if (this.params.timeNode)
					{
						this.params.timeNode.innerHTML = this.calendar.util.formatTime(fromTime.h, fromTime.m);
					}

					if (BX.type.isFunction(this.params.changeTimeCallback))
					{
						this.params.changeTimeCallback(fromTime, toTime);
					}
				}, this)
			});

			this.dateTimeField.toTime = new window.BXEventCalendar.SelectInput({
				input: this.dateTimeField.toTimeInput,
				value: this.calendar.util.adaptTimeValue(this.calendar.util.parseTime(this.dateTimeField.toTimeInput.value)),
				values: this.calendar.util.getSimpleTimeList()
			});

			// Default timezone
			if (!this.calendar.util.getUserOption('timezoneName'))
			{
				this.timezoneField = this.createField('container-string', this.mainSlide);
				this.timezoneField.innerWrap.appendChild(BX.create('LABEL', {props: {className: 'calendar-timezone-label'}, text: BX.message('EC_ASK_TZ')}));
				this.timezoneField.select = this.timezoneField.innerWrap.appendChild(
					BX.create('SELECT', {
						props: {className: 'calendar-field calendar-field-select'},
						events : {change : function() {
							_this.calendar.util.setUserOption('timezoneName', _this.timezoneField.select.value);
						}}
					}));
				var timezone_id, timezoneList = this.calendar.util.getTimezoneList();
				for (timezone_id in timezoneList)
				{
					if (timezoneList.hasOwnProperty(timezone_id))
						this.timezoneField.select.options.add(new Option(timezoneList[timezone_id].title, timezone_id, false, false));
				}

				this.timezoneField.select.value = this.calendar.util.getUserOption('timezoneDefaultName') || '';
				this.timezoneField.innerWrap.appendChild(BX.create('SPAN', {props: {
					title: BX.message('EC_EVENT_TZ_DEF_HINT'),
					className: 'calendar-event-quest'},
					html: '?'
				}));

				BX.addClass(this.timezoneField.innerWrap, 'calendar-field-timezone');
			}
		},

		setDateTimeValues: function(fromDate, toDate)
		{
			if (fromDate && toDate && this.dateTimeField && this.dateTimeField.dateWrap)
			{
				this.dateTimeField.dateWrap.innerHTML = this.calendar.util.formatDateUsable(fromDate);
				this.dateTimeField.fromTimeInput.value = this.calendar.util.formatTime(fromDate.getHours(), fromDate.getMinutes());
				this.dateTimeField.toTimeInput.value = this.calendar.util.formatTime(toDate.getHours(), toDate.getMinutes());

				this.entryTime.from = fromDate;
				this.entryTime.to = toDate;
				this._fromDateValue = fromDate;

				if (this.params.timeNode)
					this.params.timeNode.innerHTML = this.dateTimeField.fromTimeInput.value;
			}
		},

		createReminderField: function()
		{
			var _this = this;
			this.reminderValues = [];
			this.reminderField = this.createField('container-text', this.mainSlide);
			this.reminderField.innerCont = this.reminderField.innerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-text'}, text: BX.message('EC_REMIND_LABEL') + ':'}));

			this.reminderField.reminder = new BX.Calendar.Controls.Reminder({
				wrap: this.reminderField.innerCont,
				selectedValues: [this.calendar.util.getUserOption('defaultReminder', 15)],
				//valuesContainerNode: this.reminderField.innerCont.appendChild(BX.create('SPAN')),
				//addButtonNode: this.reminderField.innerCont.appendChild(BX.create('SPAN', {props: {className:
				// 'calendar-notification-btn-container calendar-notification-btn-add'}, html: '<span class="calendar-notification-icon"></span>'})),
				changeCallack: function(values){
					_this.reminderValues = values;
				},
				// showPopupCallBack: function()
				// {
				// 	_this.popup.setAutoHide(false);
				// },
				// hidePopupCallBack: function()
				// {
				// 	_this.popup.setAutoHide(true);
				// }
			});
		},

		createlocationField: function()
		{
			this.locationField = {
				outerWrap: this.mainSlide.appendChild(BX.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-select'}}))
			};

			this.locationSelector = new window.BXEventCalendar.LocationSelector(
				this.calendar.id + '-simple-slider-location',
				{
					value: this.entry ? this.entry.location : '',
					wrapNode: this.locationField.outerWrap,
					getControlContentCallback: BX.delegate(function()
					{
						this.prepareSecondSlide({
							closeCallback : BX.proxy(this.locationSelector.saveValues, this.locationSelector)
						});
						this.secondSlide.appendChild(BX.create('DIV', {
							props: {className: 'calendar-title-text'},
							text: BX.message('EC_MEETING_ROOM_LIST_TITLE')
						}));
						return this.secondSlide;
					}, this)
				}, this.calendar);
		},

		createPlannerField: function()
		{
			this.plannerField = this.createField('container-members', this.mainSlide);
			this.plannerField.innerWrapAttendeesWrap = this.plannerField.innerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-members-selected'}, html: '<span>' + BX.message('EC_ATTENDEES_LABEL') + ':</span>'}));

			this.plannerField.currentAttendeesWrap = this.plannerField.innerWrapAttendeesWrap.appendChild(BX.create('SPAN', {props: {className: 'calendar-attendees-list'}}));
			this.showAttendees();

			this.plannerField.plannerLink = this.plannerField.innerWrapAttendeesWrap.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-members-change-link'},
				html: BX.message('EC_ATTENDEES_EDIT'),
				events: {
					click: BX.delegate(this.showPlannerSlide, this)
				}
			}));
		},

		showAttendees: function()
		{
			BX.cleanNode(this.plannerField.currentAttendeesWrap);

			var
				i,
				user,
				MAX_USER_COUNT = 3,
				userLength = this.attendees.length,
				MAX_USER_COUNT_DISPLAY = 5;

			if (userLength > 0)
			{
				if (userLength > MAX_USER_COUNT_DISPLAY)
				{
					userLength = MAX_USER_COUNT;
				}

				for (i = 0; i < userLength; i++)
				{
					user = this.attendees[i] || {};
					this.plannerField.currentAttendeesWrap.appendChild(BX.create("div", {
						attrs: {
							id: 'simple_popup_' + user.id,
							// src: user.smallAvatar || '',
							'bx-tooltip-user-id': user.id
						},
						props: {
							className: 'ui-icon ui-icon-common-user calendar-member'
						},
						children: [
							BX.create("i", {
								style: {
									backgroundImage: 'url('+user.smallAvatar || ''+')'
								}
							})
						]
					}));
				}

				if (userLength < this.attendees.length)
				{
					this.plannerField.currentAttendeesWrap.appendChild(BX.create("SPAN", {
						text: BX.message('EC_ATTENDEES_MORE').replace('#COUNT#', this.attendees.length - userLength)
					}));
				}
			}
		},

		showPlannerSlide: function()
		{
			// if (this.popup)
			// {
			// 	this.popup.setAutoHide(false);
			// }
			this.prepareSecondSlide({
				closeCallback : BX.proxy(this.hidePlannerSlide, this)
			});

			// Attendees selector
			this.attendeesSelector = new window.BXEventCalendar.DestinationSelector(this.calendar.id + '-simple-popup-destination',
			{
				wrapNode: this.secondSlide,
				itemsSelected : this.attendeesCodes || this.calendar.util.getSocnetDestinationConfig('itemsSelected'),
				calendar: this.calendar
			});

			this.loader = BX.adjust(this.calendar.util.getLoader(), {style: {height: '180px'}});
			this.secondSlide.appendChild(this.loader);

			// Prepare planner control and connected stuff
			this.initPlannerControl();

			this.meetingOptionsWrap = this.secondSlide.appendChild(BX.create('DIV', {
				props: {className: 'calendar-add-popup-meeting-options-wrap'},
				style: {display: 'none'}
			}));

			//this.allowInviteField = this.createField('container-checkbox', this.meetingOptionsWrap);
			//this.allowInviteField.label = this.allowInviteField.innerWrap.appendChild(BX.create('LABEL', {props: {className: 'calendar-field-checkbox-label'}, html: BX.message('EC_ALLOW_INVITE_LABEL')}));
			//this.allowInviteField.checkbox = this.allowInviteField.label.appendChild(BX.create('INPUT', {attrs: {type: 'checkbox'}, props: {className: 'calendar-field-checkbox'}}));
			//this.allowInviteField.checkbox.checked = this.allowInvite;

			this.notifyField = this.createField('container-checkbox', this.meetingOptionsWrap);
			this.notifyField.label = this.notifyField.innerWrap.appendChild(BX.create('LABEL', {props: {className: 'calendar-field-checkbox-label'}, html: BX.message('EC_NOTIFY_STATUS_LABEL')}));
			this.notifyField.checkbox = this.notifyField.label.appendChild(BX.create('INPUT', {attrs: {type: 'checkbox'}, props: {className: 'calendar-field-checkbox'}}));
			this.notifyField.checkbox.checked = this.notify;
		},

		hidePlannerSlide: function()
		{
			// if (this.popup)
			// {
			// 	this.popup.setAutoHide(true);
			// }

			if (this.allowInviteField)
			{
				this.allowInvite = !!this.allowInviteField.checkbox.checked;
			}

			if (this.notifyField)
			{
				this.notify = !!this.notifyField.checkbox.checked;
			}

			this.destroyDestinationControls();
		},

		entryNameChanged: function()
		{
			var value = this.nameField.input.value || BX.message('EC_ENTRY_NAME');
			if (this.params.nameNode)
			{
				this.params.nameNode.innerHTML = BX.util.htmlspecialchars(value);
			}

			if (BX.type.isFunction(this.params.changeNameCallback))
			{
				this.params.changeNameCallback(value);
			}
		},

		initPlannerControl: function()
		{
			this.plannerId = this.calendar.id + '-simple-planner';
			this.plannerShown = false;
			this.plannerWrap = this.secondSlide.appendChild(BX.create('DIV', {props: {className:'calendar-add-popup-planner-wrap'}}));

			BX.addCustomEvent('OnDestinationAddNewItem', BX.proxy(this.checkPlannerState, this));
			BX.addCustomEvent('OnDestinationUnselect', BX.proxy(this.checkPlannerState, this));
			BX.addCustomEvent('OnDestinationUnselect', BX.proxy(this.clearSecondSlideHeight, this));
			BX.addCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.onCalendarPlannerSelectorChanged, this));

			if (!this.requestPlanner)
			{
				this.requestPlanner = true;
				BX.ajax.get(this.calendar.util.getActionUrl(), {
						action: 'get_planner',
						sessid: BX.bitrix_sessid(),
						bx_event_calendar_request: 'Y',
						reqId: Math.round(Math.random() * 1000000),
						planner_id: this.plannerId
					},
					BX.delegate(function (html)
					{
						if (this.loader)
							BX.remove(this.loader);

						html = BX.util.trim(html);
						this.plannerWrap.innerHTML = html;
						this.requestPlanner = false;
						this.checkPlannerState();
					}, this)
				);
			}
		},

		onCalendarPlannerSelectorChanged: function(params)
		{
			if (this.calendar.util.getDayCode(this.entryTime.from) !== this.calendar.util.getDayCode(params.dateFrom)
				&& this.params.changeDateCallback
			)
			{
				this.params.changeDateCallback(params.dateFrom);
				if (this.popup.angle)
				{
					this.popup.setAngle(false);
				}
			}
			this.setDateTimeValues(params.dateFrom, params.dateTo);
		},

		destroyDestinationControls: function()
		{
			if (BX.SocNetLogDestination)
			{
				if (BX.SocNetLogDestination.isOpenDialog())
					BX.SocNetLogDestination.closeDialog();

				if (BX.SocNetLogDestination.popupWindow)
					BX.SocNetLogDestination.popupWindow.close();

				BX.SocNetLogDestination.closeSearch();
			}

			BX.removeCustomEvent('OnDestinationAddNewItem', BX.proxy(this.checkPlannerState, this));
			BX.removeCustomEvent('OnDestinationUnselect', BX.proxy(this.checkPlannerState, this));
			BX.removeCustomEvent('OnDestinationUnselect', BX.proxy(this.clearSecondSlideHeight, this));
			BX.removeCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.onCalendarPlannerSelectorChanged, this));
		},

		plannerIsShown: function()
		{
			return this.plannerWrap && BX.hasClass(this.plannerWrap, 'calendar-add-popup-show-planner');
		},

		checkPlannerState: function()
		{
			var
				params = {
					codes: this.attendeesSelector.getCodes(),
					from: this.calendar.util.formatDate(this.entryTime.from.getTime() - this.calendar.util.dayLength * 3),
					to: this.calendar.util.formatDate(this.entryTime.from.getTime() + this.calendar.util.dayLength * 10),
					location: this.locationSelector.getTextValue()
				};

			this.attendeesCodes = this.attendeesSelector.getAttendeesCodes();
			this.attendeesCodesList = this.attendeesSelector.getAttendeesCodesList(this.attendeesCodes);
			this.updatePlanner(params);
		},

		updatePlanner: function(params)
		{
			if (!params)
				params = {};

			this.plannerLoadedlocation = params.location || '';
			var
				_this = this,
				curEventId = 0;

			this.calendar.request({
				data: {
					action: 'update_planner',
					codes: params.codes || [],
					cur_event_id: curEventId,
					date_from: params.dateFrom || params.from || '',
					date_to: params.dateTo || params.to || '',
					timezone: this.timezoneField && this.timezoneField.select ? this.timezoneField.select.value : this.calendar.util.getUserOption('timezoneName'),
					location: this.plannerLoadedlocation,
					//roomEventId: params.roomEventId || '',
					entries: params.entrieIds || false,
					add_cur_user_to_list: 'N'
				},
				handler: function(response)
				{
					var
						i, attendees = [],
						attendeesIndex = {},
						updateAttendeesControl = false,
						showPlanner = !!(params.entries || (response.entries && response.entries.length > 0));

					for (i = 0; i < response.entries.length; i++)
					{
						if (response.entries[i].type === 'user')
						{
							attendees.push({
								id: response.entries[i].id,
								name: response.entries[i].name,
								avatar: response.entries[i].avatar,
								smallAvatar: response.entries[i].smallAvatar || response.entries[i].avatar,
								url: response.entries[i].url
							});
							attendeesIndex[response.entries[i].id] = true;

							if (!_this.attendeesIndex[response.entries[i].id])
								updateAttendeesControl = true;
						}
					}

					if (!updateAttendeesControl)
					{
						for (var id in _this.attendeesIndex)
						{
							if (_this.attendeesIndex.hasOwnProperty(id) && !attendeesIndex[id])
							{
								updateAttendeesControl = true;
								break;
							}
						}
					}

					if (updateAttendeesControl)
					{
						_this.attendees = attendees;
						_this.showAttendees();
					}

					// Show first time or refresh it state
					if (showPlanner)
					{
						var refreshParams = {
							show: showPlanner && !_this.plannerIsShown()
						};

						if (params.entries)
						{
							response.entries = params.entries;
							refreshParams.scaleFrom = params.from;
							refreshParams.scaleTo = params.to;
						}

						refreshParams.loadedDataFrom = params.from;
						refreshParams.loadedDataTo = params.to;

						refreshParams.data = {
							entries: response.entries,
							accessibility: response.accessibility
						};

						refreshParams.focusSelector = params.focusSelector == undefined ? false : params.focusSelector;
						_this.refreshPlannerState(refreshParams);
					}
					else if (!showPlanner && _this.plannerIsShown()) // Hide
					{
						_this.hidePlanner();
					}
				}
			});
		},

		refreshPlannerState: function(params)
		{
			if (!params || typeof params !== 'object')
				params = {};

			this.plannerData = params.data;

			var
				workTime = this.calendar.util.getWorkTime(),
				config = {
					changeFromFullDay: {
						scaleType: '1hour',
						timelineCellWidth: 40
					},
					shownScaleTimeFrom: workTime.start,
					shownScaleTimeTo: workTime.end,
					width: this.plannerWrap.offsetWidth || 400,
					minWidth: this.plannerWrap.offsetWidth || 400,
					entriesListWidth: 50,
					showEntiesHeader: false,
					showEntryName: false
				},
				plannerShown = this.plannerIsShown();

			if (params.focusSelector == undefined)
			{
				params.focusSelector = true;
			}

			if (!plannerShown && !params.data)
			{
				this.checkPlannerState();
			}
			else
			{
				// Show planner cont
				if (params.show)
				{
					BX.addClass(this.plannerWrap, 'calendar-add-popup-show-planner');
					this.meetingOptionsWrap.style.display = '';
					if (!plannerShown && params.show)
					{
						params.focusSelector = true;
					}
				}

				var
					fromTime = this.calendar.util.parseTime(this.dateTimeField.fromTimeInput.value),
					toTime = this.calendar.util.parseTime(this.dateTimeField.toTimeInput.value),
					fromDate = new Date(this.entryTime.from.getTime()),
					toDate = new Date(this.entryTime.from.getTime());

				fromDate.setHours(fromTime.h, fromTime.m, 0);
				toDate.setHours(toTime.h, toTime.m, 0);

				BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
					{
						plannerId: this.plannerId,
						config: config,
						focusSelector: params.focusSelector,
						selector: {
							from: fromDate,
							to: toDate,
							fullDay: false,
							animation: true,
							updateScaleLimits: true
						},
						data: params.data || false,
						loadedDataFrom: params.loadedDataFrom,
						loadedDataTo: params.loadedDataTo,
						show: !!params.show
					}
				]);
			}
		},

		getBusyUserList: function()
		{
			var i, busyUsers = [];
			if (this.plannerData)
			{
				for (i in this.plannerData.entries)
				{
					if (this.plannerData.entries.hasOwnProperty(i) &&
						this.plannerData.entries[i].id &&
						this.plannerData.entries[i].status !== 'h' &&
						parseInt(this.plannerData.entries[i].strictStatus) &&
						!this.plannerData.entries[i].currentStatus
					)
					{
						busyUsers.push(this.plannerData.entries[i]);
					}
				}
			}
			return busyUsers;
		},

		hidePlanner: function()
		{
			this.meetingOptionsWrap.style.display = 'none';
			this.plannerWrap.style.opacity = 0;
			this.plannerWrap.style.height = 0;
			this.plannerWrap.style.overflow = 'hidden';
			BX.removeClass(this.plannerWrap, 'calendar-add-popup-show-planner');
		},

		keyHandler: function(e)
		{
			if(e.keyCode === this.calendar.util.KEY_CODES['enter'])
			{
				this.save();
			}
			else if(e.keyCode === this.calendar.util.KEY_CODES['escape'] && this.couldBeClosedByEsc())
			{
				this.close();
			}
		},

		checkBeforeClose: function()
		{
			var popupData = this.getPopupData();
			return !(this.params.entryName !== popupData.name
				|| popupData.location
				|| this.initiateAttendees.join(',') !== popupData.attendees.join(',')
				|| this.initiateTimeFrom !== this.dateTimeField.fromTimeInput.value
				|| this.initiateTimeTo !== this.dateTimeField.toTimeInput.value
			);
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.SimpleAddPopup = SimpleAddPopup;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.SimpleAddPopup = SimpleAddPopup;
		});
	}
})(window);
