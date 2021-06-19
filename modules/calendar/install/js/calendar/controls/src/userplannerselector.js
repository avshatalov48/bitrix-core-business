import {Type, Dom, Event, Runtime, Tag, Loc, Text} from 'main.core';
import {Util} from 'calendar.util';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Planner} from "calendar.planner";
import {Popup, MenuManager} from 'main.popup';
import {Dialog as EntitySelectorDialog} from 'ui.entity-selector';
import { ControlButton } from 'intranet.control-button';

export class UserPlannerSelector extends EventEmitter
{
	static VIEW_MODE = 'view';
	static EDIT_MODE = 'edit';
	static MAX_USER_COUNT = 8; // 8
	static MAX_USER_COUNT_DISPLAY = 10; // 10
	static PLANNER_WIDTH = 450;
	zIndex = 4200;
	readOnlyMode = true;
	meetingNotifyValue = true;
	userSelectorDialog = null;
	attendeesEntityList = [];
	inlineEditMode = UserPlannerSelector.VIEW_MODE;

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
		this.refreshPlanner = Runtime.debounce(this.refreshPlannerState, 100, this);

		if (Type.isBoolean(params.readOnlyMode))
		{
			this.readOnlyMode = params.readOnlyMode;
		}

		this.userId = params.userId;
		this.type = params.type;
		this.ownerId = params.ownerId;
		this.zIndex = params.zIndex || this.zIndex;

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
			showEntryName: false
		});

		Event.bind(this.DOM.informWrap, 'click', () => {
			this.setInformValue(!this.meetingNotifyValue);
			this.emit('onNotifyChange');
		});

		this.DOM.attendeesLabel.innerHTML = Text.encode(Loc.getMessage('EC_ATTENDEES_LABEL_ONE'));

		this.planner.subscribe('onDateChange', (event) => {this.emit('onDateChange', event);});

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
		if (this.attendeesEntityList.length > 1 && !viewMode)
		{
			this.showPlanner();
		}

		this.setEntityList(this.attendeesEntityList);
		this.setInformValue(notify);
		this.setLocationValue(location);

		if (Type.isArray(attendees))
		{
			this.displayAttendees(attendees);
		}
		this.refreshPlanner();


		if (BX?.Intranet?.ControlButton
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
				entityId: this.entryId,
				mainItem: 'chat',
				entityData: {
					dateFrom: Util.formatDate(this.entry.from),
					parentId: this.entry.parentId
				},
				analyticsLabel: {
					formType: 'compact'
				}
			});
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
		this.setEntityList(this.userSelectorDialog.getSelectedItems().map((item) => {
			return {
				entityId: item.entityId,
				id: item.id,
				entityType: item.entityType,
			}}));

		this.refreshPlanner();
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
		else
		{
			selectorEntityList.push({entityId: 'user', id: this.userId});
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
		let dateTime = this.getDateTime();
		this.runPlannerDataRequest({
			entityList: this.getEntityList(),
			from: Util.formatDate(dateTime.from.getTime() - Util.getDayLength() * 3),
			to: Util.formatDate(dateTime.to.getTime() + Util.getDayLength() * 10),
			timezone: dateTime.timezoneFrom,
			location: this.getLocationValue(),
			entryId: this.entryId
		})
			.then((response) =>
			{
				if (Type.isArray(response.data.accessibility[this.ownerId]))
				{
					const from = this.getDateTime().from;
					const to = this.getDateTime().to;
					const preparedData = this.preparedDataAccessibility(response.data.accessibility[this.ownerId]);

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
				from: Util.formatDate(dateTime.from.getTime() - Util.getDayLength() * 3),
				to: Util.formatDate(dateTime.to.getTime() + Util.getDayLength() * 10),
				timezone: dateTime.timezoneFrom,
				location: this.getLocationValue(),
				entryId: this.entryId
			})
				.then((response) => {
					this.displayAttendees(this.prepareAttendeesForDisplay(response.data.entries || []));
				});
		}
	}

	loadPlannerData(params = {})
	{
		this.planner.showLoader();
		return new Promise((resolve) => {
			this.runPlannerDataRequest(params)
				.then((response) => {
						this.planner.hideLoader();
						let dateTime = this.getDateTime();
						this.planner.update(
							response.data.entries,
							response.data.accessibility
						);
						this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);

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
				ownerId: this.ownerId,
				type: this.type,
				entityList: params.entityList || [],
				dateFrom: params.from || '',
				dateTo: params.to || '',
				timezone: params.timezone || '',
				location: params.location || '',
				entries: params.entrieIds || false
			}
		});
	}

	setDateTime(dateTime, updatePlaner = false)
	{
		this.dateTime = dateTime;
		if (this.planner && updatePlaner)
		{
			this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
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
		this.attendeeList = {
			accepted : attendees.filter((user) => {return ['H', 'Y'].includes(user.STATUS);}),
			requested : attendees.filter((user) => {return user.STATUS === 'Q' || user.STATUS === ''}),
			declined : attendees.filter((user) => {return user.STATUS === 'N'})
		};

		let userLength = this.attendeeList.accepted.length;
		if (userLength > 0)
		{
			if (userLength > UserPlannerSelector.MAX_USER_COUNT_DISPLAY)
			{
				userLength = UserPlannerSelector.MAX_USER_COUNT;
			}

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

		if (userLength < attendees.length)
		{
			if (userLength === 1)
			{
				this.DOM.moreLink.innerHTML = Text.encode(Loc.getMessage('EC_ATTENDEES_ALL_COUNT').replace('#COUNT#', attendees.length));
			}
			else
			{
				this.DOM.moreLink.innerHTML = Text.encode(Loc.getMessage('EC_ATTENDEES_ALL'));
			}
			Dom.show(this.DOM.moreLink);
		}
		else
		{
			Dom.hide(this.DOM.moreLink);
		}

		if (this.hasExternalEmailUsers(attendees)
			&& this.isPlannerDisplayed()
			&& !this.isReadOnly())
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
			imageNode = Tag.render`<div title="${Text.encode(user.DISPLAY_NAME)}" class="ui-icon ${(user.EMAIL_USER ? 'ui-icon-common-user-mail' : 'ui-icon-common-user')}"><i></i></div>`;
		}
		else
		{
			imageNode = Tag.render`
			<img 
				title="${Text.encode(user.DISPLAY_NAME)}" 
				class="calendar-member" 
				id="simple_popup_${parseInt(user.ID)}"
				src="${img}"
			>`;
		}
		return imageNode;
	}

	showMoreAttendeesPopup()
	{
		if (this.morePopup)
		{
			this.morePopup.destroy();
		}

		const submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label';
		const menuItems = [];

		[
			{
				code: 'accepted', // Accepted
				title: Loc.getMessage('EC_ATTENDEES_Y_NUM')
			},
			{
				code: 'requested', // Still thinking about
				title: Loc.getMessage('EC_ATTENDEES_Q_NUM')
			},
			{
				code: 'declined', // Declined
				title: Loc.getMessage('EC_ATTENDEES_N_NUM')
			},
		].forEach((group) => {
			let groupUsers = this.attendeeList[group.code];
			if (groupUsers.length > 0)
			{
				menuItems.push({
					html: '<span>' + group.title.replace('#COUNT#', groupUsers.length) + '</span>',
					className: submenuClass
				});

				groupUsers.forEach((user) => {
					user.toString = () => {return user.ID};
					menuItems.push(
						{
							text: BX.util.htmlspecialchars(user.DISPLAY_NAME),
							dataset: {user: user},
							className: 'calendar-add-popup-user-menu-item',
							onclick: () => {
								if (!user.EMAIL_USER)
								{
									BX.SidePanel.Instance.open(
										user.URL,
										{
											loader: "intranet:profile",
											cacheable: false,
											allowChangeHistory: false,
											contentClassName: "bitrix24-profile-slider-content",
											width: 1100
										}
									);
									this.morePopup.close();
								}
							}
						}
					);
				});
			}
		});

		this.morePopup = MenuManager.create(
			'compact-event-form-attendees' + Math.round(Math.random() * 100000),
			this.DOM.moreLink,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: 15,
				angle: true,
				cacheable: false,
				className: 'calendar-popup-user-menu'
			}
		);

		this.morePopup.show();
		this.morePopup.menuItems.forEach((item) => {
			const icon = item.layout.item.querySelector('.menu-popup-item-icon');
			if (Type.isPlainObject(item.dataset))
			{
				icon.appendChild(UserPlannerSelector.getUserAvatarNode(item.dataset.user))
			}
		});
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
		const hideGuestsHint = this.DOM.hideGuestsWrap.querySelector('.calendar-hide-members-helper');
		if (Type.isElementNode(hideGuestsHint))
		{
			BX.UI.Hint.initNode(hideGuestsHint);
		}
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
}