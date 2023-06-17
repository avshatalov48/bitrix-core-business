import {Type, Dom, Event, Runtime, Tag, Loc, Text} from 'main.core';
import {Util} from 'calendar.util';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Planner} from "calendar.planner";
import {Dialog as EntitySelectorDialog} from 'ui.entity-selector';
import { ControlButton } from 'intranet.control-button';
import {AttendeesList} from "calendar.controls";

export class UserPlannerSelector extends EventEmitter
{
	static VIEW_MODE = 'view';
	static EDIT_MODE = 'edit';
	static MAX_USER_COUNT_DISPLAY = 8;
	static PLANNER_WIDTH = 550;
	zIndex = 4200;
	readOnlyMode = true;
	meetingNotifyValue = true;
	userSelectorDialog = null;
	attendeesEntityList = [];
	inlineEditMode = UserPlannerSelector.VIEW_MODE;
	prevUserList = [];
	loadedAccessibilityData = {};
	REFRESH_PLANNER_DELAY = 500;

	constructor(params = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.UserPlannerSelector');
		this.selectorId = params.id || 'user-selector-' + Math.round(Math.random() * 10000);
		this.BX = Util.getBX();
		this.DOM = {
			outerWrap: params.outerWrap,
			wrap: params.wrap,
			informWrap: params.informWrap,
			informWrapText: params.informWrap.querySelector('.calendar-field-container-inform-text'),
			moreLink: params.outerWrap.querySelector('.calendar-members-more'),
			changeLink: params.outerWrap.querySelector('.calendar-members-change-link'),
			attendeesLabel: params.outerWrap.querySelector('.calendar-attendees-label'),
			attendeesList: params.outerWrap.querySelector('.calendar-attendees-list'),
			userSelectorWrap: params.outerWrap.querySelector('.calendar-user-selector-wrap'),
			plannerOuterWrap: params.plannerOuterWrap,
			videocallWrap: params.outerWrap.querySelector('.calendar-videocall-wrap'),
			hideGuestsWrap: params.hideGuestsWrap,
			hideGuestsIcon: params.hideGuestsWrap.querySelector('.calendar-hide-members-icon-hidden')
		};
		this.refreshPlannerStateDebounce = Runtime.debounce(this.refreshPlannerState, this.REFRESH_PLANNER_DELAY, this);

		if (Type.isBoolean(params.readOnlyMode))
		{
			this.readOnlyMode = params.readOnlyMode;
		}

		this.userId = params.userId;
		this.type = params.type;
		this.ownerId = params.ownerId;
		this.zIndex = params.zIndex || this.zIndex;
		this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat;

		this.plannerFeatureEnabled = !!params.plannerFeatureEnabled;
		this.create();
	}

	create()
	{
		if (this.DOM.changeLink && !this.isReadOnly())
		{
			Event.bind(this.DOM.changeLink, 'click', () => {
				if (!this.userSelectorDialog)
				{
					this.userSelectorDialog = new EntitySelectorDialog({
						targetNode: this.DOM.changeLink,
						context: 'CALENDAR',
						preselectedItems: this.attendeesPreselectedItems,
						enableSearch: true,
						zIndex: this.zIndex + 10,
						events: {
							'Item:onSelect': this.handleUserSelectorChanges.bind(this),
							'Item:onDeselect': this.handleUserSelectorChanges.bind(this),
						},
						entities: [
							{
								id: 'user',
								options: {
									inviteGuestLink: true,
									emailUsers: true,
								}
							},
							{
								id: 'project'
							},
							{
								id: 'department',
								options: {selectMode: 'usersAndDepartments'}
							},
							{
								id: 'meta-user',
								options: { 'all-users': true }
							}
						],
						searchTabOptions: {
							stubOptions: {
								title: Loc.getMessage('EC_USER_DIALOG_404_TITLE'),
								subtitle: Loc.getMessage('EC_USER_DIALOG_404_SUBTITLE'),
								icon: '/bitrix/images/calendar/search-email.svg',
								iconOpacity: 100,
								arrow: true,
							}
						},
					});
				}
				this.userSelectorDialog.show();
			});
		}

		if (this.DOM.moreLink)
		{
			Event.bind(this.DOM.moreLink, 'click', this.showMoreAttendeesPopup.bind(this));
		}

		this.planner = new Planner({
			wrap: this.DOM.plannerOuterWrap,
			minWidth: UserPlannerSelector.PLANNER_WIDTH,
			width: UserPlannerSelector.PLANNER_WIDTH,
			showEntryName: false,
			locked: !this.plannerFeatureEnabled,
			dayOfWeekMonthFormat: this.dayOfWeekMonthFormat

		});

		Event.bind(this.DOM.informWrap, 'click', () => {
			this.setInformValue(!this.meetingNotifyValue);
			this.emit('onNotifyChange');
		});

		this.DOM.attendeesLabel.innerHTML = Text.encode(Loc.getMessage('EC_ATTENDEES_LABEL_ONE'));

		this.planner.subscribe('onDateChange', (event) => {this.emit('onDateChange', event);});
		this.planner.subscribe('onExpandTimeline', this.handleExpandPlannerTimeline.bind(this));

		if (this.DOM.hideGuestsWrap && !this.isReadOnly())
		{
			Event.bind(this.DOM.hideGuestsWrap, 'click', ()=>{
				this.setHideGuestsValue(!this.hideGuests);
			});
		}
	}

	setValue({attendeesEntityList, attendees, location, notify, hideGuests, viewMode, entry})
	{
		this.attendeesEntityList = Type.isArray(attendeesEntityList) ? attendeesEntityList : [];
		this.attendeesPreselectedItems = this.attendeesEntityList.map((item) => {return [item.entityId, item.id]});

		this.entry = entry;
		this.entryId = this.entry.id;

		this.setEntityList(this.attendeesEntityList);
		this.setInformValue(notify);
		this.setLocationValue(location);

		if (Type.isArray(attendees))
		{
			this.displayAttendees(attendees);
		}
		this.refreshPlannerStateDebounce();

		let dateTime = this.getDateTime();
		if (dateTime)
		{
			this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
		}

		if (
			this.entryId
			&& this.entry
			&& this.entry.data['PARENT_ID']
			&& (
				this.entry.data['EVENT_TYPE'] === '#shared#'
				|| this.entry.data['EVENT_TYPE'] === '#shared_crm#'
			)
			&& this.entry.getCurrentStatus() !== false
		)
		{
			Dom.clean(this.DOM.videocallWrap);
			Dom.removeClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');

			this.conferenceButton = Tag.render`
				<div class="calendar-text-link --gray">${Loc.getMessage('EC_CONFERENCE_START')}</div>
			`;
			Event.bind(this.conferenceButton, 'click', this.handleVideoconferenceButtonClick.bind(this));

			Dom.append(this.conferenceButton, this.DOM.videocallWrap);
		}
		else if (
			BX?.Intranet?.ControlButton
			&& this.DOM.videocallWrap
			&& this.entryId
			&& this.entry.getCurrentStatus() !== false
		)
		{
			Dom.clean(this.DOM.videocallWrap);
			Dom.removeClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');

			this.intranetControllButton = new ControlButton({
				container: this.DOM.videocallWrap,
				entityType: 'calendar_event',
				entityId: this.entry.parentId,
				mainItem: 'chat',
				entityData: {
					dateFrom: Util.formatDate(this.entry.from),
					parentId: this.entry.parentId
				},
				analyticsLabel: {
					formType: 'compact'
				}
			});

			// For testing purposes
			if (Type.isElementNode(this.intranetControllButton.button))
			{
				this.intranetControllButton.button.setAttribute('data-role', 'videocallButton');
			}
		}
		else if(this.DOM.videocallWrap)
		{
			Dom.addClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');
		}

		this.setHideGuestsValue(hideGuests);
	}

	handleUserSelectorChanges()
	{
		this.showPlanner();
		const dateTime = this.getDateTime();
		this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);

		this.setEntityList(this.userSelectorDialog.getSelectedItems().map((item) => {
			return {
				entityId: item.entityId,
				id: item.id,
				entityType: item.entityType,
			}}));

		this.refreshPlannerStateDebounce();
		this.emit('onUserCodesChange');
	}

	getEntityList()
	{
		return this.selectorEntityList;
	}

	setEntityList(selectorEntityList)
	{
		if (this.type === 'user' && this.userId !== this.ownerId)
		{
			selectorEntityList.push({entityId: 'user', id: this.ownerId});
		}

		this.selectorEntityList = selectorEntityList;
	}

	isReadOnly()
	{
		return this.readOnlyMode;
	}

	getUserSelector()
	{
		return BX.UI.SelectorManager.instances[this.selectorId];
	}

	showPlanner()
	{
		if (!this.isPlannerDisplayed())
		{
			Dom.addClass(this.DOM.outerWrap, 'user-selector-edit-mode');
			this.planner.show();
			this.planner.showLoader();
		}
	}

	checkBusyTime()
	{
		const dateTime = this.getDateTime();
		const entityList = this.getEntityList();
		this.planner.updateScaleLimitsFromEntry(dateTime.from, dateTime.to);

		this.runPlannerDataRequest({
			entityList: entityList,
			timezone: dateTime.timezoneFrom,
			location: this.getLocationValue(),
			entryId: this.entryId
		})
			.then((response) =>
			{
				for (let id in response.data.accessibility)
				{
					if (response.data.accessibility.hasOwnProperty(id))
					{
						this.loadedAccessibilityData[id] = response.data.accessibility[id];
					}
				}

				if (Type.isArray(response.data.entries))
				{
					response.data.entries.forEach((entry) => {
						if (entry.type === 'user' && !this.prevUserList.includes(parseInt(entry.id)))
						{
							this.prevUserList.push(parseInt(entry.id));
						}
					});
				}

				if (Type.isArray(response.data.accessibility[this.ownerId]))
				{
					const from = this.getDateTime().from;
					const to = this.getDateTime().to;
					const preparedData = this.preparedDataAccessibility(response.data.accessibility[this.ownerId]);

					if (!this.planner.currentFromDate)
					{
						this.planner.currentFromDate = from;
					}
					if (!this.planner.currentToDate)
					{
						this.planner.currentToDate = to;
					}

					const item = this.planner.checkTimePeriod(from, to, preparedData);
					if (
						Type.isObject(item)
						&& Type.isArray(response.data.entries)
					)
					{
						this.showPlanner();
						this.planner.update(response.data.entries, response.data.accessibility);
						this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
						this.planner.hideLoader();
						this.displayAttendees(this.prepareAttendeesForDisplay(response.data.entries));
					}
				}
			})
	}

	prepareAttendeesForDisplay(attendees)
	{
		return (attendees)
			.filter((item) =>
			{
				return item.type === 'user';
			})
			.map((item) =>
			{
				return {
					ID: item.id,
					AVATAR: item.avatar,
					DISPLAY_NAME: item.name,
					EMAIL_USER: item.emailUser,
					SHARING_USER: item.sharingUser,
					STATUS: (item.status || '').toUpperCase(),
					URL: item.url
				};
			});
	}

	refreshPlannerState()
	{
		if (this.planner && this.planner.isShown())
		{
			let dateTime = this.getDateTime();
			this.loadPlannerData({
				entityList: this.getEntityList(),
				timezone: dateTime.timezoneFrom,
				location: this.getLocationValue(),
				entryId: this.entryId,
				prevUserList: this.prevUserList
			});
		}
	}

	loadPlannerData(params = {})
	{
		this.planner.showLoader();
		return new Promise((resolve) => {
			this.runPlannerDataRequest(params)
				.then((response) => {
					for (let id in response.data.accessibility)
					{
						if (response.data.accessibility.hasOwnProperty(id))
						{
							this.loadedAccessibilityData[id] = response.data.accessibility[id];
						}
					}

					if (Type.isArray(response.data.entries))
					{
						response.data.entries.forEach((entry) => {
							if (entry.type === 'user' && !this.prevUserList.includes(parseInt(entry.id)))
							{
								this.prevUserList.push(parseInt(entry.id));
							}
						});
					}

					this.planner.hideLoader();
					this.planner.update(
						response.data.entries,
						this.loadedAccessibilityData
					);

					resolve(response);
				},
				(response) => {resolve(response);}
			);
		});
	}

	runPlannerDataRequest(params)
	{
		return this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
			data: {
				entryId: params.entryId || 0,
				entryLocation: this.entry.data.LOCATION || '',
				ownerId: this.ownerId,
				type: this.type,
				entityList: params.entityList || [],
				dateFrom: Util.formatDate(this.planner.scaleDateFrom),
				dateTo: Util.formatDate(this.planner.scaleDateTo),
				timezone: params.timezone || '',
				location: params.location || '',
				entries: params.entrieIds || false,
				prevUserList: params.prevUserList || []
			}
		});
	}

	setDateTime(dateTime, updatePlaner = false)
	{
		this.dateTime = dateTime;
		this.planner.currentFromDate = dateTime.from;
		this.planner.currentToDate = dateTime.to;
		if (this.planner && updatePlaner)
		{
			this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
		}
		else if (this.planner)
		{
			let fromHours = parseInt(dateTime.from.getHours()) + Math.floor(dateTime.from.getMinutes() / 60);
			let toHours = parseInt(dateTime.to.getHours()) + Math.floor(dateTime.to.getMinutes() / 60);
			if (
				(fromHours !== 0 && fromHours <= this.planner.shownScaleTimeFrom)
				|| (toHours !== 0  && toHours !== 23 && toHours + 1 >= this.planner.shownScaleTimeTo)
			)
			{
				this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
			}
		}
	}

	getDateTime()
	{
		return this.dateTime;
	}

	setLocationValue(location)
	{
		this.location = location;
	}

	getLocationValue()
	{
		return this.location;
	}

	displayAttendees(attendees = [])
	{
		Dom.clean(this.DOM.attendeesList);
		this.attendeeList = AttendeesList.sortAttendees(attendees);
		const usersCount = this.attendeeList.accepted.length
			+ this.attendeeList.requested.length;
		this.emit('onDisplayAttendees', new BaseEvent({
			data: {
				usersCount: usersCount
			}
		}));

		const userLength = Math.min(this.attendeeList.accepted.length, UserPlannerSelector.MAX_USER_COUNT_DISPLAY);
		if (userLength > 0)
		{
			for (let i = 0; i < userLength; i++)
			{
				this.attendeeList.accepted[i].shown = true;
				this.DOM.attendeesList.appendChild(UserPlannerSelector.getUserAvatarNode(this.attendeeList.accepted[i]));
			}
		}

		if (userLength > 1)
		{
			this.DOM.attendeesLabel.innerHTML = Text.encode(Loc.getMessage('EC_ATTENDEES_LABEL_NUM')).replace('#COUNT#', `<span>(</span>${this.attendeeList.accepted.length}<span>)</span>`);
		}
		else
		{
			this.DOM.attendeesLabel.innerHTML = Text.encode(Loc.getMessage('EC_ATTENDEES_LABEL_ONE'));
		}

		if (attendees.length > 1)
		{
			this.DOM.moreLink.innerHTML = Text.encode(Loc.getMessage('EC_ATTENDEES_ALL_COUNT').replace('#COUNT#', attendees.length));
			Dom.show(this.DOM.moreLink);
		}
		else
		{
			Dom.hide(this.DOM.moreLink);
		}

		if (
			this.hasExternalEmailUsers(attendees)
			&& this.isPlannerDisplayed()
			&& !this.isReadOnly()
		)
		{
			this.showHideGuestsOption();
		}
		else
		{
			this.hideHideGuestsOption();
		}
	}

	static getUserAvatarNode(user)
	{
		let
			imageNode,
			img = user.AVATAR || user.SMALL_AVATAR;
		if (!img || img === "/bitrix/images/1.gif")
		{
			let defaultAvatarClass = 'ui-icon-common-user';
			if (user.EMAIL_USER)
			{
				defaultAvatarClass = 'ui-icon-common-user-mail';
			}
			if (user.SHARING_USER)
			{
				defaultAvatarClass += ' ui-icon-common-user-sharing';
			}
			imageNode = Tag.render`<div title="${Text.encode(user.DISPLAY_NAME)}" class="ui-icon ${defaultAvatarClass}"><i></i></div>`;
		}
		else
		{
			imageNode = Tag.render`
			<img
				title="${Text.encode(user.DISPLAY_NAME)}"
				class="calendar-member"
				id="simple_popup_${parseInt(user.ID)}"
				src="${encodeURI(img)}"
			>`;
		}
		return imageNode;
	}

	showMoreAttendeesPopup()
	{
		(new AttendeesList(this.DOM.moreLink, this.attendeeList)).showPopup();
	}

	setInformValue(value)
	{
		if (Type.isBoolean(value))
		{
			const DISABLED_CLASS = 'calendar-field-container-inform-off';
			this.meetingNotifyValue = value;
			if (this.meetingNotifyValue)
			{
				Dom.removeClass(this.DOM.informWrap, DISABLED_CLASS);
				this.DOM.informWrap.title = Loc.getMessage('EC_NOTIFY_OPTION_ON_TITLE');
				this.DOM.informWrapText.innerHTML = Loc.getMessage('EC_NOTIFY_OPTION');
			}
			else
			{
				Dom.addClass(this.DOM.informWrap, DISABLED_CLASS);
				this.DOM.informWrap.title = Loc.getMessage('EC_NOTIFY_OPTION_OFF_TITLE');
				this.DOM.informWrapText.innerHTML = Loc.getMessage('EC_DONT_NOTIFY_OPTION');
			}
		}
	}

	getInformValue(value)
	{
		return this.meetingNotifyValue;
	}

	setViewMode(readOnlyMode)
	{
		this.readOnlyMode = readOnlyMode;
		if (this.readOnlyMode)
		{
			Dom.addClass(this.DOM.outerWrap, 'calendar-userselector-readonly');
		}
		else
		{
			Dom.removeClass(this.DOM.outerWrap, 'calendar-userselector-readonly');
		}
	}

	isPlannerDisplayed()
	{
		return this.planner.isShown();
	}

	hasExternalEmailUsers(attendees = [])
	{
		return !!attendees.find((item) => {return item.EMAIL_USER;})
			|| !!this.getEntityList().find((item) => {return item.entityType === 'email';});
	}

	destroy()
	{
		if (this.userSelectorDialog && this.userSelectorDialog.destroy)
		{
			this.userSelectorDialog.destroy();
		 	this.userSelectorDialog = null;
		}

		if (this.intranetControllButton && this.intranetControllButton.destroy)
		{
			this.intranetControllButton.destroy();
			this.intranetControllButton = null;
		}
	}

	showHideGuestsOption()
	{
		this.DOM.hideGuestsWrap.style.display = '';
		Util.initHintNode(this.DOM.hideGuestsWrap.querySelector('.calendar-hide-members-helper'));
	}

	hideHideGuestsOption()
	{
		this.DOM.hideGuestsWrap.style.display = 'none';
	}

	setHideGuestsValue(hideGuests = true)
	{
		this.hideGuests = hideGuests;

		if (Type.isElementNode(this.DOM.hideGuestsIcon))
		{
			this.DOM.hideGuestsIcon.className = this.hideGuests ? 'calendar-hide-members-icon-hidden' : 'calendar-hide-members-icon-visible'
		}

		const hideGuestsText = this.DOM.hideGuestsWrap.querySelector('.calendar-hide-members-text');
		if (Type.isElementNode(hideGuestsText))
		{
			hideGuestsText.innerHTML = this.hideGuests
				? Loc.getMessage('EC_HIDE_GUEST_NAMES')
				: Loc.getMessage('EC_SHOW_GUEST_NAMES');
		}
	}

	preparedDataAccessibility(calendarEventsAccessibility)
	{
		return calendarEventsAccessibility.map((item) => {
			return Planner.prepareAccessibilityItem(item);
		});
	}

	clearAccessibilityData(userIdList: Object): void
	{
		if (Type.isArray(userIdList) && userIdList.length && this.prevUserList.length)
		{
			this.prevUserList = this.prevUserList.filter((userId) => {
				return !userIdList.includes(userId);
			});
		}
	}

	handleExpandPlannerTimeline(event)
	{
		if (event && event.getData)
		{
			let data = event.getData();
			if (data.reload)
			{
				const dateTime = this.getDateTime();
				this.loadPlannerData({
					entityList: this.getEntityList(),
					timezone: dateTime.timezoneFrom,
					location: this.getLocationValue(),
					entryId: this.entryId,
					focusSelector: false
				});
			}
		}
	}

	handleVideoconferenceButtonClick()
	{
		this.getConferenceChatId();
	}

	getConferenceChatId()
	{
		return this.BX.ajax.runAction('calendar.api.calendarajax.getConferenceChatId', {
			data: {
				eventId: this.entry.data['PARENT_ID'],
			},
		}).then(
			(response) => {
				if (top.window.BXIM && response.data && response.data.chatId)
				{
					top.BXIM.openMessenger('chat' + parseInt(response.data.chatId));

					return null;
				}

				alert(Loc.getMessage('EC_CONFERENCE_ERROR'));

				return null;
			},
			(response) => {
				alert(Loc.getMessage('EC_CONFERENCE_ERROR'));

				return null;
			}
		);
	}
}