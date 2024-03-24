// @flow
import {Type, Tag, Loc, Dom, Event, Runtime, Text} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Util} from 'calendar.util';
import { MenuManager, Popup, PopupManager } from 'main.popup';
import {
	DateTimeControl,
	Location,
	Reminder,
	SectionSelector,
	UserPlannerSelector,
	ColorSelector,
	BusyUsersDialog
} from "calendar.controls";
import {Entry, EntryManager} from "calendar.entry";
import {SectionManager} from "calendar.sectionmanager";
import { MessageBox } from 'ui.dialogs.messagebox';

export class CompactEventForm extends EventEmitter
{
	static VIEW_MODE = 'view';
	static EDIT_MODE = 'edit';
	static USER_URL = '/company/personal/user/#USER_ID#/';
	STATE = {READY: 1, REQUEST: 2, ERROR: 3};
	zIndex = 1200;
	Z_INDEX_OFFSET = -1000;
	userSettings = '';
	DOM = {};
	mode;
	displayed = false;
	sections = [];
	sectionIndex = {};
	trackingUsersList = [];
	checkDataBeforeCloseMode = true;
	CHECK_CHANGES_DELAY = 500;
	RELOAD_DATA_DELAY = 500;
	excludedUsers = [];

	constructor(options = {})
	{
		super();
		this.setEventNamespace('BX.Calendar.CompactEventForm');
		this.userId = options.userId || Util.getCurrentUserId();
		this.type = options.type || 'user';
		this.isLocationCalendar = options.isLocationCalendar || false;
		this.calendarContext = options.calendarContext || null;
		this.ownerId = options.ownerId || this.userId;
		this.BX = Util.getBX();

		this.checkForChangesDebounce = Runtime.debounce(this.checkForChanges, this.CHECK_CHANGES_DELAY, this);
		this.reloadEntryDataDebounce = Runtime.debounce(this.reloadEntryData, this.RELOAD_DATA_DELAY, this);
		this.checkOutsideClickClose = this.checkOutsideClickClose.bind(this);
		this.outsideMouseDownClose = this.outsideMouseDownClose.bind(this);
		this.keyHandler = this.handleKeyPress.bind(this);

		this.lastUsedSaveOptions = {};
	}

	show(mode = CompactEventForm.EDIT_MODE, params ={})
	{
		this.setParams(params);
		this.setMode(mode);

		this.state = this.STATE.READY;

		this.popupId = 'compact-event-form-' + Math.round(Math.random() * 100000);

		if (this.popup)
		{
			this.popup.destroy();
		}
		this.popup = this.getPopup(params);

		// Small hack to use transparent titlebar to drag&drop popup
		Dom.addClass(this.popup.titleBar, 'calendar-add-popup-titlebar');
		Dom.removeClass(this.popup.popupContainer, 'popup-window-with-titlebar');
		Dom.removeClass(this.popup.closeIcon, 'popup-window-titlebar-close-icon');
		Event.bind(document, "mousedown", this.outsideMouseDownClose);
		Event.bind(document, "mouseup", this.checkOutsideClickClose);
		Event.bind(document, "keydown", this.keyHandler);
		Event.bind(this.popup.popupContainer, 'transitionend', () => {Dom.removeClass(this.popup.popupContainer, 'calendar-simple-view-popup-show');});

		// Fulfill previous deletions to avoid data inconsistency
		if (this.getMode() === CompactEventForm.EDIT_MODE)
		{
			EntryManager.doDelayedActions();
		}

		this.prepareData()
			.then(() => {
				if (this.isLocationMode())
				{
					this.setFormValuesLocation();
				}
				else
				{
					this.setFormValues();
				}

				this.popup.show();
				if (
					this.userPlannerSelector
					&& (
						this.isLocationCalendar
						|| (
							this.userPlannerSelector.attendeesEntityList.length > 1
							&& this.getMode() !== CompactEventForm.VIEW_MODE
						)
					)
				)
				{
					this.userPlannerSelector.showPlanner();
				}
				if (this.isTitleOverflowing())
				{
					this.DOM.titleInput.title = this.entry.name;
				}

				this.checkDataBeforeCloseMode = true;
				if (this.canDo('edit') && this.DOM.titleInput && mode === CompactEventForm.EDIT_MODE)
				{
					this.DOM.titleInput.focus();
					this.DOM.titleInput.select();
				}

				this.displayed = true;

				if (this.getMode() === CompactEventForm.VIEW_MODE)
				{
					Util.sendAnalyticLabel({calendarAction: 'view_event', formType: 'compact'});
					this.popup.getButtons()[0].button.focus();
				}

				if (
					this.getMode() === CompactEventForm.EDIT_MODE
					&& !this.userPlannerSelector.isPlannerDisplayed()
				)
				{
					this.userPlannerSelector.checkBusyTime()
				}
			});
	}

	getPopup(params)
	{
		return new Popup(this.popupId,
			params.bindNode,
			{
				zIndex: this.zIndex + this.Z_INDEX_OFFSET,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				closeIcon: true,
				titleBar: true,
				draggable: true,
				resizable: false,
				lightShadow: true,
				className: 'calendar-simple-view-popup calendar-simple-view-popup-show',
				cacheable: false,
				content: this.isLocationMode()
					? this.getPopupContentLocation()
					: this.getPopupContentCalendar(),
				buttons: this.getButtons(),
				events: {
					onPopupClose: this.close.bind(this)
				},
			});
	}

	isShown()
	{
		return this.displayed;
	}

	close(fromButton = true, fromPopup = false)
	{
		if (
			!fromButton
			&& !this.checkTopSlider()
		)
		{
			if (this.popup)
			{
				this.popup.destroyed = true;
				setTimeout(() => {this.popup.destroyed = false;}, 0);
			}
			return;
		}

		if (
			this.getMode() === CompactEventForm.EDIT_MODE
			&& this.formDataChanged()
			&& this.checkDataBeforeCloseMode
			&& !fromPopup
		)
		{
			this.showConfirmClosePopup();
			// Workaround to prevent form closing even if user don't want to and presses "cancel" in confirm
			if (this.popup)
			{
				this.popup.destroyed = true;
				setTimeout(() => {this.popup.destroyed = false;}, 0);
			}
			return;
		}

		this.displayed = false;
		this.emit('onClose');
		Event.unbind(document, "mousedown", this.outsideMouseDownClose);
		Event.unbind(document, "mouseup", this.checkOutsideClickClose);
		Event.unbind(document, "keydown", this.keyHandler);

		if (this.userPlannerSelector)
		{
			this.userPlannerSelector.destroy();
		}

		if (this.popup)
		{
			this.popup.destroy();
		}

		if (Location)
		{
			Location.setCurrentCapacity(0);
		}
		Util.clearPlannerWatches();
		Util.closeAllPopups();
	}

	getPopupContentCalendar()
	{
		this.DOM.wrap = Tag.render`<div class="calendar-add-popup-wrap">
			${this.DOM.titleOuterWrap = Tag.render`
			<div class="calendar-field-container calendar-field-container-string-select">
				<div class="calendar-field-block">
					${this.getEntryCounter()}
					${this.getTitleControl()}
					${this.getTitleFade()}
					${this.getColorControl()}
				</div>
			</div>`}
			<div class="calendar-field-container calendar-field-container-choice">
				${this.getSectionControl('textselect')}
			</div>

			${this.getDateTimeControl()}

			${this.getUserPlannerSelector()}

			<div class="calendar-field-container calendar-field-container-info">
				${this.getTypeInfoControl()}

					${this.getLocationControl()}

				${this.DOM.remindersOuterWrap = Tag.render`
				<div class="calendar-field-block">
					<div class="calendar-field-title">${Loc.getMessage('EC_REMIND_LABEL')}:</div>
					${this.createRemindersControl()}
				</div>`}
				${this.getRRuleInfoControl()}
			</div>
		</div>`;

		return this.DOM.wrap;
	}

	getPopupContentLocation()
	{
		this.DOM.wrap = Tag.render`<div class="calendar-add-popup-wrap">
			${this.DOM.titleOuterWrap = Tag.render`
			<div class="calendar-field-container calendar-field-container-string-select">
				<div class="calendar-field-block">
					${this.getTitleControlLocation()}
					${this.getTitleFade()}
					${this.getColorControlsLocationView()}
				</div>
			</div>`}
			<div class="calendar-field-container calendar-field-container-choice">
				${this.getSectionControl('location')}
			</div>
			${this.getDateTimeControl()}
			
		</div>`;
		if (this.entry.id !== this.entry.parentId)
		{
			this.DOM.wrap.appendChild(Tag.render`
				${this.getHostControl()}
			`);
		}

		return this.DOM.wrap;
	}

	getButtons()
	{
		let buttons = [];
		if (this.isLocationMode())
		{
			buttons = this.getLocationModeButtons();
		}
		else if (this.isInvitedMode())
		{
			buttons = this.getInvitedButtons();
		}
		else if (this.isEditMode())
		{
			buttons = this.getEditModeButtons();
		}
		else if (this.isViewMode())
		{
			buttons = this.getViewModeButtons();
		}

		if (buttons.length > 3)
		{
			const firstTwoButtons = buttons.slice(0, 2);
			const menuButtons = buttons.slice(2);

			buttons = [...firstTwoButtons, this.getMoreButton(menuButtons)];
		}

		if (buttons.length > 2)
		{
			buttons[1].button.className = 'ui-btn ui-btn-light-border';
		}

		return buttons;
	}

	getLocationModeButtons()
	{
		// if (this.entry.id === this.entry.parentId)
		// {
		// 	return [
		// 		new BX.UI.Button({
		// 			className: 'ui-btn ui-btn-disabled',
		// 			text: Loc.getMessage('CALENDAR_UPDATE_PROGRESS'),
		// 		})
		// 	];
		// }

		const buttons = [this.getOpenParentButton()];

		if (this.canDo('release'))
		{
			buttons.push(this.getReleaseLocationButton());
		}

		return buttons;
	}

	getInvitedButtons()
	{
		return [this.getAcceptButton(), this.getDeclineButton(), this.getOpenButton(), ...this.getEditEventButtons()];
	}

	getEditModeButtons()
	{
		const buttons = [this.getSaveButton(), this.getCloseButton()];

		if (this.canDo('edit'))
		{
			buttons.push(this.getFullFormButton());
		}

		return buttons;
	}

	getViewModeButtons()
	{
		const buttons = [this.getOpenButton()];
		if (this.entry.isMeeting() && this.entry.getCurrentStatus() === 'N')
		{
			buttons.push(this.getAcceptButtonWithoutBorder());
		}

		if (this.entry.isMeeting() && this.entry.getCurrentStatus() === 'Y')
		{
			buttons.push(this.getDeclineButton());
		}

		buttons.push(...this.getEditEventButtons());

		return buttons;
	}

	getEditEventButtons()
	{
		const buttons = [];
		if (!this.isNewEntry() && (this.entry.permissions?.['edit'] || this.canDo('edit')))
		{
			buttons.push(this.getEditButton());
		}

		if (!this.isNewEntry() && (this.entry.permissions?.['edit'] || this.canDo('edit')))
		{
			buttons.push(this.getDeleteButton());
		}

		return buttons;
	}

	getOpenButton()
	{
		const className = this.entry.isInvited() ? 'ui-btn-link' : 'ui-btn-primary';
		const openButton = new BX.UI.Button({
			className: `ui-btn ${className}`,
			text: Loc.getMessage('CALENDAR_EVENT_DO_OPEN'),
			events: {
				click: () => {
					this.checkDataBeforeCloseMode = false;
					BX.Calendar.EntryManager.openViewSlider(this.entry.id, {
						entry: this.entry,
						calendarContext: this.calendarContext,
						type: this.type,
						ownerId: this.ownerId,
						userId: this.userId,
						from: this.entry.from,
						timezoneOffset: this.entry && this.entry.data ? this.entry.data.TZ_OFFSET_FROM : null,
					});
					this.close();
				},
			},
		});
		openButton.button.setAttribute('data-role', 'openButton');

		return openButton;
	}

	getEditButton()
	{
		return new BX.UI.Button({
			text: Loc.getMessage('CALENDAR_EVENT_DO_EDIT'),
			className: 'ui-btn ui-btn-link',
			events: {
				click: this.editEntryInSlider.bind(this),
			},
		});
	}

	getSaveButton()
	{
		const messageCode = this.isNewEntry() ? 'CALENDAR_EVENT_DO_ADD' : 'CALENDAR_EVENT_DO_SAVE';
		const saveButton = new BX.UI.Button({
			name: 'save',
			text : Loc.getMessage(messageCode),
			className: 'ui-btn ui-btn-primary',
			events: {
				click: () => {
					this.checkDataBeforeCloseMode = false;
					this.save();
				},
			},
		});
		saveButton.button.setAttribute('data-role', 'saveButton');

		return saveButton;
	}

	getDeleteButton()
	{
		return new BX.UI.Button({
			text: Loc.getMessage('CALENDAR_EVENT_DO_DELETE'),
			className: 'ui-btn ui-btn-link',
			events: {
				click: () => {
					EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', () => {
						this.checkDataBeforeCloseMode = false;
						this.close();
					});

					EntryManager.deleteEntry(this.entry);

					if (!this.entry.wasEverRecursive())
					{
						this.close();
					}
				},
			},
		});
	}

	getCloseButton()
	{
		const closeButton = new BX.UI.Button({
			text: Loc.getMessage('CALENDAR_EVENT_DO_CANCEL'),
			className: 'ui-btn ui-btn-link',
			events: {
				click: () => {
					if (this.isNewEntry())
					{
						this.checkDataBeforeCloseMode = false;
						this.close();
					}
					else
					{
						this.setFormValues();

						if (this.userPlannerSelector)
						{
							this.userPlannerSelector.destroy();
						}

						this.setMode(CompactEventForm.VIEW_MODE);
						this.popup.setButtons(this.getButtons());
					}
				},
			},
		});
		closeButton.button.setAttribute('data-role', 'closeButton');

		return closeButton;
	}

	getFullFormButton()
	{
		const fullFormButton = new BX.UI.Button({
			text: Loc.getMessage('CALENDAR_EVENT_FULL_FORM'),
			className: 'ui-btn calendar-full-form-btn',
			events: {
				click: this.editEntryInSlider.bind(this),
			},
		});
		fullFormButton.button.setAttribute('data-role', 'fullForm');

		return fullFormButton;
	}

	getAcceptButtonWithoutBorder()
	{
		return this.getAcceptButton(true);
	}

	getAcceptButton(withoutBorder = false)
	{
		const className = withoutBorder ? 'ui-btn-link' : 'ui-btn-primary';
		const acceptButton = new BX.UI.Button({
			className: `ui-btn ${className}`,
			text: Loc.getMessage('EC_DESIDE_BUT_Y'),
			events: {
				click: () => {
					EntryManager.setMeetingStatus(this.entry, 'Y')
						.then(this.refreshMeetingStatus.bind(this));
				},
			}
		});
		acceptButton.button.setAttribute('data-role', 'accept');

		return acceptButton;
	}

	getDeclineButton()
	{
		const declineButton = new BX.UI.Button({
			className: 'ui-btn ui-btn-link',
			text: Loc.getMessage('EC_DESIDE_BUT_N'),
			events: {
				click: () => {
					EntryManager.setMeetingStatus(this.entry, 'N').then(() => {
						if (this.isShown())
						{
							this.close();
						}
					});
				},
			},
		});
		declineButton.button.setAttribute('data-role', 'decline');

		return declineButton;
	}

	getOpenParentButton()
	{
		const className = this.entry.isInvited() ? 'ui-btn-link' : 'ui-btn-primary';
		return new BX.UI.Button({
			className: `ui-btn ${className}`,
			text: Loc.getMessage('CALENDAR_EVENT_DO_OPEN_PARENT'),
			events: {
				click: () => {
					this.checkDataBeforeCloseMode = false;
					BX.Calendar.EntryManager.openViewSlider(
						this.entry.parentId,
						{
							userId: this.userId,
							from: this.entry.from,
							timezoneOffset: this.entry && this.entry.data ? this.entry.data.TZ_OFFSET_FROM : null
						}
					);
					this.close();
				},
			},
		});
	}

	getReleaseLocationButton()
	{
		return new BX.UI.Button({
			name: 'release',
			text: Loc.getMessage('CALENDAR_EVENT_DO_RELEASE'),
			className: 'ui-btn ui-btn-light-border',
			events: {
				click: () => {
					this.checkDataBeforeCloseMode = false;
					this.releaseLocation();
				},
			},
		});
	}

	getMoreButton(buttons)
	{
		let buttonsMenu;

		const moreButton = new BX.UI.Button({
			text: Loc.getMessage('CALENDAR_EVENT_DO_MORE'),
			className: 'ui-btn ui-btn-light-border ui-btn-dropdown',
			events: {
				click: () => {
					buttonsMenu.show();
				},
			},
		});

		const buttonsItems = buttons.map((button) => {
			return {
				text: button.button.innerText,
				onclick: () => {
					button.button.click();
				},
			};
		});

		buttonsMenu = MenuManager.create({
			id: 'calendar-compact-event-form-more' + new Date().getTime(),
			bindElement: moreButton.button,
			items: buttonsItems,
		});

		return moreButton;
	}

	freezePopup()
	{
		if (this.popup)
		{
			this.popup.buttons.forEach((button) => {
				if (button?.options?.name === 'save')
				{
					button.setClocking(true);
				}
				else
				{
					button.setDisabled(true);
				}
			});
		}
	}

	unfreezePopup()
	{
		if (this.popup)
		{
			this.popup.buttons.forEach((button) => {
				button.setClocking(false);
				button.setDisabled(false);
			});
		}
	}

	refreshMeetingStatus()
	{
		this.emit('doRefresh');
		this.popup.setButtons(this.getButtons());
		if (this.entry.isInvited())
		{
			Dom.removeClass(this.DOM.entryCounter, 'calendar-event-invite-counter-none');
		}
		else
		{
			Dom.addClass(this.DOM.entryCounter, 'calendar-event-invite-counter-none');
		}
		if (this.userPlannerSelector)
		{
			this.userPlannerSelector.displayAttendees(this.entry.getAttendees());
		}
	}

	hideLoader()
	{
		if (Type.isDomNode(this.DOM.loader))
		{
			Dom.remove(this.DOM.loader);
			this.DOM.loader = null;
		}
	}

	showInEditMode(params = {})
	{
		return this.show(CompactEventForm.EDIT_MODE, params);
	}

	showInViewMode(params = {})
	{
		return this.show(CompactEventForm.VIEW_MODE, params);
	}

	isLocationMode()
	{
		return this.isViewMode() && this.type === 'location';
	}

	isInvitedMode()
	{
		return this.entry.isInvited();
	}

	isEditMode()
	{
		return this.getMode() === CompactEventForm.EDIT_MODE;
	}

	isViewMode()
	{
		return this.getMode() === CompactEventForm.VIEW_MODE;
	}

	setMode(mode)
	{
		if (mode === 'edit' || mode === 'view')
		{
			this.mode = mode;
		}
	}

	getMode()
	{
		return this.mode;
	}

	checkForChanges()
	{
		if (
			!this.isNewEntry()
			&& this.getMode() === CompactEventForm.VIEW_MODE
			&& this.formDataChanged()
		)
		{
			this.setMode(CompactEventForm.EDIT_MODE);
			this.popup.setButtons(this.getButtons());
		}
		else if (
			!this.isNewEntry()
			&& this.getMode() === CompactEventForm.EDIT_MODE
			&& !this.formDataChanged()
		)
		{
			this.setMode(CompactEventForm.VIEW_MODE);
			this.popup.setButtons(this.getButtons());
		}
		this.emitOnChange();
	}

	updateEventNameInputTitle()
	{
		if (this.isTitleOverflowing())
		{
			this.DOM.titleInput.title = this.DOM.titleInput.value;
		}
		else
		{
			this.DOM.titleInput.title = '';
		}
	}

	isTitleOverflowing()
	{
		const el = this.DOM.titleInput;
		return el.clientWidth < el.scrollWidth || el.clientHeight < el.scrollHeight;
	}

	checkLocationForm(event)
	{
		if (event && event instanceof BaseEvent)
		{
			const data = event.getData();
			const usersCount = data.usersCount;

			let locationCapacity = Location.getCurrentCapacity() || 0;

			if (this.locationSelector.value.type === undefined)
			{
				if (locationCapacity)
				{
					locationCapacity = 0;
					Location.setCurrentCapacity(0);
				}
			}

			if (locationCapacity < usersCount && locationCapacity !== 0)
			{
				this.locationSelector.addCapacityAlert();
			}
			else
			{
				this.locationSelector.removeCapacityAlert();
			}
		}
	}

	getFormDataChanges(excludes = [])
	{
		const entry = this.entry;
		let fields = [];

		// Name
		if (!excludes.includes('name') && entry.name !== this.DOM.titleInput.value)
		{
			fields.push('name');
		}

		// Location
		if (
			!excludes.includes('location')
			&&
			this.locationSelector.getTextLocation(Location.parseStringValue(entry.getLocation()))
			!==
			this.locationSelector.getTextLocation(Location.parseStringValue(this.locationSelector.getTextValue()))
		)
		{
			fields.push('location');
		}

		// Date + time
		const dateTime = this.dateTimeControl.getValue();
		if (
			!excludes.includes('date&time')
			&&
			(entry.isFullDay() !== dateTime.fullDay
				|| dateTime.from.toString() !== entry.from.toString()
				|| dateTime.to.toString() !== entry.to.toString()
			)
		)
		{
			fields.push('date&time');
		}

		// Notify
		if (
			!excludes.includes('notify')
			&& (!entry.isMeeting() || entry.getMeetingNotify()) !== this.userPlannerSelector.getInformValue()
		)
		{
			fields.push('notify');
		}

		// Section
		if (!excludes.includes('section') && parseInt(entry.sectionId) !== parseInt(this.sectionValue))
		{
			fields.push('section');
		}

		// Access codes
		if (
			!excludes.includes('codes')
			&&
			this.userPlannerSelector.getEntityList().map((item)=>{return item.entityId + ':' + item.id}).join('|')
			!==
			entry.getAttendeesEntityList().map((item)=>{return item.entityId + ':' + item.id}).join('|')
		)
		{
			fields.push('codes');
		}

		return fields;
	}

	formDataChanged()
	{
		return this.getFormDataChanges().length > 0;
	}

	setParams(params = {})
	{
		this.userId = params.userId || Util.getCurrentUserId();
		this.type = params.type || 'user';
		this.isLocationCalendar = params.isLocationCalendar || false;
		this.locationAccess = params.locationAccess || false;
		this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat || false;
		this.calendarContext = params.calendarContext || null;
		this.ownerId = params.ownerId ? params.ownerId : 0;
		if (this.type === 'user' && !this.ownerId)
		{
			this.ownerId = this.userId;
		}
		this.entry = EntryManager.getEntryInstance(params.entry, params.userIndex, {type: this.type, ownerId: this.ownerId});
		this.sectionValue = null;

		if (
			!this.entry.id
			&& Type.isPlainObject(params.entryTime)
			&& Type.isDate(params.entryTime.from)
			&& Type.isDate(params.entryTime.to)
		)
		{
			this.entry.setDateTimeValue(params.entryTime);
		}

		if (Type.isPlainObject(params.userSettings))
		{
			this.userSettings = params.userSettings;
		}

		this.locationFeatureEnabled = !!params.locationFeatureEnabled;
		this.locationList = Type.isArray(params.locationList)
			? params.locationList.filter(locationItem => {return locationItem.PERM.view_full})
			: [];

		this.roomsManager = params.roomsManager || null;
		this.iblockMeetingRoomList = params.iblockMeetingRoomList || [];

		this.plannerFeatureEnabled = !!params.plannerFeatureEnabled;

		this.setSections(params.sections, params.trackingUserList)
	}

	setSections(sections, trackingUsersList = [])
	{
		this.sections = sections;
		this.sectionIndex = {};
		this.trackingUsersList = trackingUsersList || [];

		if (Type.isArray(this.sections))
		{
			this.sections.forEach((value, ind) => {
				const id = parseInt(value.ID || value.id);
				if (id > 0)
				{
					this.sectionIndex[id] = ind;
				}
			}, this);
		}

		const section = this.getCurrentSection();
		if (this.entry.id)
		{
			this.getSectionsForEditEvent(this.sections, section);
		}
	}

	prepareData(params = {})
	{
		return new Promise((resolve) => {
			const section = this.getCurrentSection();
			if (section && section.canDo)
			{
				resolve();
			}
			else
			{
				this.BX.ajax.runAction('calendar.api.calendarajax.getCompactFormData', {
					data: {
						entryId: this.entry.id,
						loadSectionId: this.entry.sectionId
					}
				}).then((response) => {
					if (response && response.data && response.data.section)
					{
						// todo: refactor this part to new Section entities
						this.sections.push(new window.BXEventCalendar.Section(Util.getCalendarContext(), response.data.section));
						this.setSections(this.sections);
						resolve();
					}
				});
			}
		});
	}

	getEntryCounter()
	{
		if (!this.DOM.entryCounter)
		{
			this.DOM.entryCounter = Tag.render`
				<span class="calendar-event-invite-counter calendar-event-invite-counter-big">1</span>
			`;
		}

		if (this.entry.isInvited())
		{
			Dom.removeClass(this.DOM.entryCounter, 'calendar-event-invite-counter-none');
		}
		else
		{
			Dom.addClass(this.DOM.entryCounter, 'calendar-event-invite-counter-none');
		}

		return this.DOM.entryCounter;
	}

	getTitleControl()
	{
		this.DOM.titleInput = Tag.render`
			<input class="calendar-field calendar-field-string --text-overflow-none"
				value=""
				placeholder="${Loc.getMessage('EC_ENTRY_NAME')}"
				type="text"
			/>
		`;

		this.bindFade();

		Event.bind(this.DOM.titleInput, 'keyup', this.checkForChangesDebounce);
		Event.bind(this.DOM.titleInput, 'change', this.checkForChangesDebounce);
		Event.bind(this.DOM.titleInput, 'keyup', this.updateEventNameInputTitle.bind(this));
		Event.bind(this.DOM.titleInput, 'change', this.updateEventNameInputTitle.bind(this));

		return this.DOM.titleInput;
	}

	bindFade()
	{
		let isInputFocus = false;

		Event.bind(this.DOM.titleInput, 'focusout', ()=> {
			if (this.DOM.titleInput.scrollWidth > this.DOM.titleInput.offsetWidth)
			{
				this.getTitleFade().classList.add('--show');
			}
			else
			{
				this.getTitleFade().classList.remove('--show');
			}
			isInputFocus = false;
		});

		Event.bind(this.DOM.titleInput, 'focus', ()=> {
			this.getTitleFade().classList.remove('--show');
			isInputFocus = true;
		});

		Event.bind(this.DOM.titleInput, 'scroll', ()=> {
			if (
				this.DOM.titleInput.scrollWidth > this.DOM.titleInput.offsetWidth
				&& Math.ceil(this.DOM.titleInput.offsetWidth + this.DOM.titleInput.scrollLeft) < this.DOM.titleInput.scrollWidth
				&& !isInputFocus
			)
			{
				this.getTitleFade().classList.add('--show');
			}
			else
			{
				this.getTitleFade().classList.remove('--show');
			}
		});
	}

	getTitleFade()
	{
		if (!this.DOM.titleFade)
		{
			this.DOM.titleFade = Tag.render`
				<div class="calendar-field-title-fade"></div>	
			`;
		}

		return this.DOM.titleFade;
	}

	getTitleControlLocation()
	{
		this.DOM.titleInput = Tag.render`
			<input class="calendar-field calendar-field-string --text-overflow-none"
				value=""
				placeholder="${Loc.getMessage('EC_ENTRY_NAME')}"
				type="text"
				readonly
			/>
		`;

		this.bindFade();

		return this.DOM.titleInput;
	}
	getHostControl()
	{
		const userId = this.entry.data.CREATED_BY;
		const userUrl = CompactEventForm.USER_URL.replace('#USER_ID#', userId);
		const userAvatar = this.BX.Calendar.EntryManager.userIndex[userId]
			? this.BX.Calendar.EntryManager.userIndex[userId].AVATAR
			: ''
		;

		this.DOM.hostBar = Tag.render`
			<div class="calendar-slider-detail-option-without-border">
				<div class="calendar-slider-detail-option-block">
					<div class="calendar-field-value">
						${Loc.getMessage('EC_HOST') + ': '}
					</div>
					<span class="calendar-field-location-host-img">
						<a href="${userUrl}">
							${this.renderAvatar(userAvatar)}
						</a>
					</span>
					<div class="calendar-slider-detail-option-value">
						<a href="${userUrl}" class="calendar-slider-sidebar-user-info-name calendar-slider-sidebar-user-info-name-padding">${BX.util.htmlspecialchars(this.entry.name)}</a>
					</div>
				</div>
			</div>
		`;
		return this.DOM.hostBar;
	}

	renderAvatar(src)
	{
		const avatarClassName = 'calendar-field-location-host-img-value';

		if (this.isAvatar(src))
		{
			return Tag.render`
				<img class="${avatarClassName}" src="${src}" alt="">
			`;
		}

		return Tag.render`
			<div class="ui-icon ui-icon-common-user ${avatarClassName}"><i></i></div>
		`;
	}

	isAvatar(src)
	{
		return Type.isStringFilled(src) && src !== '/bitrix/images/1.gif';
	}

	getColorControl()
	{
		this.DOM.colorSelect = Tag.render`<div class="calendar-field calendar-field-select calendar-field-tiny"></div>`;
		this.colorSelector = new ColorSelector({
			wrap: this.DOM.colorSelect,
			mode: 'selector'
		});

		this.colorSelector.subscribe('onChange', (event) => {
			if (event instanceof BaseEvent)
			{
				const color = event.getData().value;
				if (
					!this.isNewEntry()
					&& (this.canDo('edit') || this.entry.getCurrentStatus() !== false)
				)
				{
					this.BX.ajax.runAction('calendar.api.calendarajax.updateColor', {
						data: {
							entryId: this.entry.id,
							userId: this.userId,
							color: color
						}
					});
					this.entry.data.COLOR = color;

					this.emit('doRefresh');
					this.emitOnChange();
				}
			}
		});


		return this.DOM.colorSelect;
	}

	getColorControlsLocationView()
	{
		this.DOM.colorSelect = Tag.render`<div class="calendar-field calendar-field-select calendar-colorpicker-readonly calendar-field-tiny"></div>`;
		this.colorSelector = new ColorSelector({
			wrap: this.DOM.colorSelect,
			mode: 'view'
		});
		return this.DOM.colorSelect;
	}

	getSectionControl(mode)
	{
		this.DOM.sectionSelectWrap = Tag.render`<div class="calendar-field-choice-calendar"></div>`;
		this.sectionSelector = new SectionSelector({
			outerWrap: this.DOM.sectionSelectWrap,
			defaultCalendarType: this.type,
			defaultOwnerId: this.ownerId,
			sectionList: this.sections,
			sectionGroupList: SectionManager.getSectionGroupList({
				type: this.type,
				ownerId: this.ownerId,
				userId: this.userId,
				trackingUsersList: this.trackingUsersList,
			}),
			mode: mode,
			zIndex: this.zIndex,
			getCurrentSection: () => {
				const section = this.getCurrentSection();
				if (section)
				{
					return {
						id: section.id,
						name: section.name,
						color: section.color
					}
				}
				return false;
			},
			selectCallback: (sectionValue) => {
				if (sectionValue)
				{
					if (this.colorSelector)
					{
						this.colorSelector.setValue(sectionValue.color);
					}
					this.sectionValue = sectionValue.id;
					this.checkForChangesDebounce();

					SectionManager.saveDefaultSectionId(this.sectionValue);
				}
			}
		});

		return this.DOM.sectionSelectWrap;
	}

	getDateTimeControl()
	{
		this.DOM.dateTimeWrap = Tag.render`<div class="calendar-field-container calendar-field-container-datetime"></div>`;

		this.dateTimeControl = new DateTimeControl(null, {
			showTimezone: false,
			outerWrap: this.DOM.dateTimeWrap,
			inlineEditMode: true
		});

		this.dateTimeControl.subscribe('onSetValue', () => {
			this.excludedUsers = [];
		})

		this.dateTimeControl.subscribe('onChange', (event) => {
			if (event instanceof BaseEvent)
			{
				let value = event.getData().value;
				if (this.remindersControl)
				{
					this.remindersControl.setFullDayMode(value.fullDay);

					if (this.isNewEntry() && !this.remindersControl.wasChangedByUser())
					{
						const defaultReminders = EntryManager.getNewEntryReminders(
							value.fullDay ? 'fullDay' : 'withTime'
						);

						this.remindersControl.setValue(defaultReminders, false);
					}
				}

				if (this.userPlannerSelector)
				{
					if (!this.userPlannerSelector.isPlannerDisplayed())
					{
						this.userPlannerSelector.showPlanner();
					}
					this.userPlannerSelector.setLocationValue(this.locationSelector.getTextValue());
					this.userPlannerSelector.setDateTime(value, true);
					this.userPlannerSelector.refreshPlannerStateDebounce();
				}

				if (this.locationSelector)
				{
					this.locationSelector.checkLocationAccessibility(
						{
							from: event.getData().value.from,
							to: event.getData().value.to,
							fullDay: event.getData().value.fullDay,
							currentEventId: this.entry.id
						},
					)
				}

				this.checkForChangesDebounce();
			}
		});

		return this.DOM.dateTimeWrap;
	}

	getUserPlannerSelector()
	{
		this.DOM.userPlannerSelectorOuterWrap = Tag.render`<div>
			<div class="calendar-field-container calendar-field-container-members">
				${this.DOM.userSelectorWrap = Tag.render`
				<div class="calendar-field-block">
					<div class="calendar-members-selected">
						<span class="calendar-attendees-label"></span>
						<span class="calendar-attendees-list"></span>
						<span class="calendar-members-more">${Loc.getMessage('EC_ATTENDEES_MORE')}</span>
						<span class="calendar-members-change-link">${Loc.getMessage('EC_SEC_SLIDER_CHANGE')}</span>
					</div>
				</div>`}
				<span class="calendar-videocall-wrap calendar-videocall-hidden"></span>
				${this.DOM.informWrap = Tag.render`
				<div class="calendar-field-container-inform">
					<span class="calendar-field-container-inform-text">${Loc.getMessage('EC_NOTIFY_OPTION')}</span>
				</div>`}
			</div>
			<div class="calendar-user-selector-wrap"></div>
			<div class="calendar-add-popup-planner-wrap calendar-add-popup-show-planner">
				${this.DOM.plannerOuterWrap = Tag.render`
				<div class="calendar-planner-wrapper" style="height: 0">
				</div>`}
			</div>
			${this.DOM.hideGuestsWrap = Tag.render`
			<div class="calendar-hide-members-container" style="display: none;">
				<div class="calendar-hide-members-container-inner">
					<div class="calendar-hide-members-icon-hidden"></div>
					<div class="calendar-hide-members-text">${Loc.getMessage('EC_HIDE_GUEST_NAMES')}</div>
					<span class="calendar-hide-members-helper" data-hint="${Loc.getMessage('EC_HIDE_GUEST_NAMES_HINT')}"></span>
				</div>
			</div>`}
		<div>`;

		this.userPlannerSelector = new UserPlannerSelector({
			outerWrap: this.DOM.userPlannerSelectorOuterWrap,
			wrap: this.DOM.userSelectorWrap,
			informWrap: this.DOM.informWrap,
			plannerOuterWrap: this.DOM.plannerOuterWrap,
			hideGuestsWrap: this.DOM.hideGuestsWrap,
			readOnlyMode: false,
			userId: this.userId,
			type: this.type,
			ownerId: this.ownerId,
			zIndex: this.zIndex + 10,
			plannerFeatureEnabled: this.plannerFeatureEnabled,
			dayOfWeekMonthFormat: this.dayOfWeekMonthFormat,
			isEditableSharingEvent: this.entry.isSharingEvent() && (this.entry.permissions?.['edit'] || this.canDo('edit')),
			openEditFormCallback: () => {
				this.editEntryInSlider('userSelector');
			},
		});

		this.userPlannerSelector.subscribe('onDateChange', this.handlePlannerSelectorChanges.bind(this));
		this.userPlannerSelector.subscribe('onNotifyChange', this.checkForChangesDebounce);
		this.userPlannerSelector.subscribe('onUserCodesChange', this.checkForChangesDebounce);

		return this.DOM.userPlannerSelectorOuterWrap;
	}

	getLocationControl()
	{
		this.DOM.locationWrap = Tag.render`<div class="calendar-field-place"></div>`;
		this.DOM.locationOuterWrap = Tag.render`<div class="calendar-field-block">
			<div class="calendar-field-title calendar-field-title-align-top">${Loc.getMessage('EC_LOCATION_LABEL')}:</div>
			${this.DOM.locationWrap}
		</div>`;

		this.locationSelector = new Location(
			{
				wrap: this.DOM.locationWrap,
				richLocationEnabled: this.locationFeatureEnabled,
				locationList: this.locationList || [],
				roomsManager: this.roomsManager || null,
				locationAccess: this.locationAccess || false,
				iblockMeetingRoomList: this.iblockMeetingRoomList || [],
				inlineEditModeEnabled: !this.isLocationCalendar,
				onChangeCallback: () => {
					if (this.userPlannerSelector)
					{
						this.userPlannerSelector.setLocationValue(this.locationSelector.getTextValue());
						if (this.locationSelector.getValue().type !== undefined
							&& !this.userPlannerSelector.isPlannerDisplayed())
						{
							this.userPlannerSelector.showPlanner();
						}
						this.userPlannerSelector.refreshPlannerStateDebounce();
					}
					this.checkForChangesDebounce();
				}
			}
		);

		if (this.shouldShowFakeLocationControl())
		{
			const locationName = this.locationSelector.getTextLocation(Location.parseStringValue(this.entry.getLocation()));
			const editLocationInFullForm = Tag.render`
				<div class="calendar-field-place-link">
					<span class="calendar-notification-text">
						${locationName || Loc.getMessage('EC_REMIND1_ADD')}
					</span>
				</div>
			`;
			this.DOM.locationOuterWrap.append(editLocationInFullForm);

			Event.bind(editLocationInFullForm, 'click', () => this.editEntryInSlider('location'));

			return this.DOM.locationOuterWrap;
		}

		if (this.userPlannerSelector)
		{
			this.userPlannerSelector.subscribe('onDisplayAttendees', this.checkLocationForm.bind(this));
			this.userPlannerSelector.planner?.subscribe('onDisplayAttendees', this.checkLocationForm.bind(this));
		}

		return this.DOM.locationOuterWrap;
	}

	createRemindersControl()
	{
		this.reminderValues = [];
		this.DOM.remindersWrap = Tag.render`<div class="calendar-text"></div>`;
		this.remindersControl = new Reminder({
			wrap: this.DOM.remindersWrap,
			zIndex: this.zIndex
		});

		this.remindersControl.subscribe('onChange', (event) => {
			if (event instanceof BaseEvent)
			{
				this.reminderValues = event.getData().values;
				if (!this.isNewEntry()
					&& (this.canDo('edit')
						|| this.entry.getCurrentStatus() !== false))
				{
					this.BX.ajax.runAction('calendar.api.calendarajax.updateReminders', {
						data: {
							entryId: this.entry.id,
							userId: this.userId,
							reminders: this.reminderValues
						}
					}).then((response) => {
						this.entry.data.REMIND = response.data.REMIND;
					});
				}
			}
		});

		return this.DOM.remindersWrap;
	}

	getTypeInfoControl()
	{
		this.DOM.typeInfoTitle = Tag.render`<div class="calendar-field-title"></div>`;
		this.DOM.typeInfoLink = Tag.render`<div class="calendar-field-link"></div>`;

		this.DOM.typeInfoWrap = Tag.render`
			<div class="calendar-field-block" style="display: none">
				${this.DOM.typeInfoTitle}
				${this.DOM.typeInfoLink}
			</div>
		`;
		return this.DOM.typeInfoWrap;
	}

	getRRuleInfoControl()
	{
		this.DOM.rruleInfo = Tag.render`<div class="calendar-text"></div>`;
		this.DOM.rruleInfoWrap = Tag.render`
			<div class="calendar-field-block" style="display: none">
				<div class="calendar-field-title">${Loc.getMessage('EC_REPEAT')}:</div>
				${this.DOM.rruleInfo}
			</div>
		`;
		return this.DOM.rruleInfoWrap;
	}

	getTimezoneInfoControl()
	{
		this.DOM.timezoneInfo = Tag.render`<div class="calendar-text"></div>`;
		this.DOM.timezoneInfoWrap = Tag.render`
			<div class="calendar-field-block" style="display: none">
				<div class="calendar-field-title">${Loc.getMessage('EC_TIMEZONE')}:</div>
				${this.DOM.timezoneInfo}
			</div>
		`;
		return this.DOM.timezoneInfoWrap;
	}

	isNewEntry()
	{
		return !this.entry.id;
	}

	canDo(action)
	{
		const section = this.getCurrentSection();

		if (action === 'edit' || action === 'delete')
		{
			if ((this.entry.isMeeting() && this.entry.id !== this.entry.parentId))
			{
				return false;
			}

			if (this.entry.isResourcebooking())
			{
				return false;
			}

			return section.canDo('edit');
		}

		if (action === 'view')
		{
			if (this.entry.permissions && Type.isBoolean(this.entry.permissions['view_time']))
			{
				return this.entry.permissions['view_time'] === true;
			}

			return section.canDo('view_time');
		}

		if (action === 'viewFull')
		{
			if (this.entry.permissions && Type.isBoolean(this.entry.permissions['view_full']))
			{
				return this.entry.permissions['view_full'] === true;
			}

			return section.canDo('view_full');
		}

		if(action === 'release')
		{
			return section.canDo('access');
		}

		if (action === 'editLocation')
		{
			return (this.isNewEntry())
				? true
				: this.entry.permissions?.['edit_location'] || false;
		}

		if (action === 'editAttendees')
		{
			return (this.isNewEntry())
				? true
				: this.entry.permissions?.['edit_attendees'] || false;
		}

		return true;
	}

	setFormValues()
	{
		const entry = this.entry;
		const section = this.getCurrentSection();
		const readOnly = !this.canDo('edit');

		// Date time
		this.dateTimeControl.setValue({
			from: Util.adjustDateForTimezoneOffset(entry.from, entry.userTimezoneOffsetFrom, entry.fullDay),
			to: Util.adjustDateForTimezoneOffset(entry.to, entry.userTimezoneOffsetTo, entry.fullDay),
			fullDay: entry.fullDay,
			timezoneFrom: entry.getTimezoneFrom() || '',
			timezoneTo: entry.getTimezoneTo() || '',
			timezoneName: this.userSettings.timezoneName,
		});
		this.dateTimeControl.setInlineEditMode(this.isNewEntry() ? 'edit' : 'view');
		this.dateTimeControl.setViewMode(readOnly);

		// Title
		this.setEventNameInputValue(entry.getName());

		if (readOnly)
		{
			Dom.attr(this.DOM.titleInput, 'readonly', 'readonly');
		}

		// Color
		this.colorSelector.setValue(entry.getColor() || section.color, false);
		this.colorSelector.setViewMode(readOnly && this.entry.getCurrentStatus() === false);

		// Section
		this.sectionValue = this.getCurrentSectionId();
		this.sectionSelector.updateValue();
		if ((this.isSyncSection(section) || entry.isSharingEvent()) && entry.id)
		{
			this.sectionSelector.setViewMode(true);
		}
		else
		{
			this.sectionSelector.setViewMode(readOnly);
		}

		// Reminders
		this.remindersControl.setValue(entry.getReminders(), false);
		this.remindersControl.setViewMode(readOnly && this.entry.getCurrentStatus() === false);
		if (readOnly && this.entry.getCurrentStatus() === false)
		{
			this.DOM.remindersOuterWrap.style.display = 'none';
		}

		// Recurcion
		if (entry.isRecursive())
		{
			this.DOM.rruleInfoWrap.style = '';
			Dom.adjust(this.DOM.rruleInfo, {text: entry.getRRuleDescription()});
		}

		// Location
		const readOnlyLocation = !(this.canDo('editLocation') || this.canDo('edit'));
		let location = entry.getLocation();
		if (this.shouldShowFakeLocationControl())
		{
			this.DOM.locationWrap.style.display = 'none';
		}
		else if (readOnlyLocation && !location)
		{
			this.DOM.locationOuterWrap.style.display = 'none';
		}
		else
		{
			this.locationSelector.setViewMode(readOnlyLocation);
			if (this.isLocationCalendar)
			{
				this.locationSelector.setValue(this.locationSelector.default);
				location = this.locationSelector.default;
			}
			else
			{
				this.DOM.locationOuterWrap.style.display = '';
				this.locationSelector.setValue(entry.getLocation());
			}
		}
		if (this.locationSelector)
		{
			this.locationSelector.checkLocationAccessibility(
				{
					from: this.dateTimeControl.getValue().from,
					to: this.dateTimeControl.getValue().to,
					fullDay: this.dateTimeControl.getValue().fullDay,
					currentEventId: this.entry.id
				},
			);
		}

		//User Planner Selector
		if (
			this.userPlannerSelector
			&& (this.canDo('viewFull') || entry.getCurrentStatus() !== false)
		)
		{
			this.userPlannerSelector.setValue({
				attendeesEntityList: entry.getAttendeesEntityList(),
				location: location,
				attendees: entry.getAttendees(),
				notify: !entry.isMeeting() || entry.getMeetingNotify(),
				viewMode: this.getMode() === CompactEventForm.VIEW_MODE,
				entry: entry,
				hideGuests: entry.getHideGuests()
			});
			this.userPlannerSelector.setDateTime(this.dateTimeControl.getValue());
			const readOnlyUserPlanner = !(this.canDo('editAttendees') || this.canDo('edit'));
			if (readOnlyUserPlanner)
			{
				this.userPlannerSelector.setViewMode(readOnlyUserPlanner);
			}
			if (this.entry.isSharingEvent() && (this.entry.permissions?.['edit'] || this.canDo('edit')))
			{
				this.userPlannerSelector.setEditableSharingEventMode();
			}
		}
		else
		{
			Dom.remove(this.DOM.userPlannerSelectorOuterWrap);
		}

		let hideInfoContainer = true;
		this.DOM.infoContainer = this.DOM.wrap.querySelector('.calendar-field-container-info');
		for(let i = 0; i <= this.DOM.infoContainer.childNodes.length; i++)
		{
			if (
				Type.isElementNode(this.DOM.infoContainer.childNodes[i])
				&& this.DOM.infoContainer.childNodes[i].style.display !== 'none'
			)
			{
				hideInfoContainer = false;
			}
		}
		if (hideInfoContainer)
		{
			this.DOM.infoContainer.style.display = 'none';
		}
	}

	shouldShowFakeLocationControl()
	{
		return this.entry.isSharingEvent() && (this.entry.permissions?.['edit'] || this.canDo('edit'));
	}

	setEventNameInputValue(name: string)
	{
		this.DOM.titleInput.value = name;
	}

	setFormValuesLocation()
	{
		const entry = this.entry;
		const section = this.getCurrentSection();
		const readOnly = true;

		// Date time
		this.dateTimeControl.setValue({
			from: Util.adjustDateForTimezoneOffset(entry.from, entry.userTimezoneOffsetFrom, entry.fullDay),
			to: Util.adjustDateForTimezoneOffset(entry.to, entry.userTimezoneOffsetTo, entry.fullDay),
			fullDay: entry.fullDay,
			timezoneFrom: entry.getTimezoneFrom() || '',
			timezoneTo: entry.getTimezoneTo() || '',
			timezoneName: this.userSettings.timezoneName,
		});
		this.dateTimeControl.setInlineEditMode(this.isNewEntry() ? 'edit' : 'view');
		this.dateTimeControl.setViewMode(readOnly);

		// Title
		let name = '';
		if (this.entry.id === this.entry.parentId)
		{
			name = Loc.getMessage('CALENDAR_UPDATE');
		}
		else
		{
			name = `${section.name}: ${BX.util.htmlspecialchars(entry.getName())}`;
		}
		this.setEventNameInputValue(name);

		// Color
		this.colorSelector.setValue(entry.getColor() || section.color, false);
		this.colorSelector.setViewMode(!readOnly);

		// Section
		this.sectionValue = this.getCurrentSectionId();
		this.sectionSelector.updateValue();
		this.sectionSelector.setViewMode(readOnly);
	}

	save(options = {}): boolean
	{
		if (this.state === this.STATE.REQUEST)
		{
			return false;
		}

		const entry = this.getCurrentEntry();
		options = Type.isPlainObject(options) ? options : {};

		if (
			this.isNewEntry()
			&& this.userPlannerSelector.hasExternalEmailUsers()
			&& Util.checkEmailLimitationPopup()
			&& !options.emailLimitationDialogShown
		)
		{
			EntryManager.showEmailLimitationDialog({
				callback: (params) => {
					options.emailLimitationDialogShown = true;
					this.save(options);
				}
			});
			return false;
		}

		if (
			!this.userSettings.sendFromEmail
			&& this.userPlannerSelector.hasExternalEmailUsers()
		)
		{
			EntryManager.showConfirmedEmailDialog({
				callback: (params) => {
					this.save(options);
				}
			});
			return false;
		}

		if (
			!this.isNewEntry()
			&& entry.isRecursive()
			&& !options.confirmed
			&& this.getFormDataChanges(['section', 'notify']).length > 0
		)
		{
			EntryManager.showConfirmEditDialog({
				callback: (params) => {
					options.recursionMode = (entry.isFirstInstance() && params.recursionMode === 'next')
						? 'all'
						: params.recursionMode;
					options.confirmed = true;
					this.lastUsedSaveOptions = options;
					this.save(options);
				}
			});
			return false;
		}

		if (
			!this.isNewEntry()
			&& entry.isMeeting()
			&& options.sendInvitesAgain === undefined
			&& this.getFormDataChanges().includes('date&time')
			&& entry.getAttendees().find((item) => {return item.STATUS === 'N';})
		)
		{
			EntryManager.showReInviteUsersDialog({
				callback: (params) => {
					options.sendInvitesAgain = params.sendInvitesAgain;
					this.lastUsedSaveOptions = options;
					this.save(options);
				}
			});
			return false;
		}

		if (
			!this.isNewEntry()
			&& entry.isRecursive()
			&& !options.confirmed
			&& this.getFormDataChanges().includes('section')
		)
		{
			options.recursionMode = entry.isFirstInstance() ? 'all' : 'next';
		}

		const dateTime = this.dateTimeControl.getValue();
		const data = {
			id: entry.id,
			section: this.sectionValue,
			name: this.DOM.titleInput.value,
			desc: entry.getDescription(),
			reminder: this.remindersControl.getSelectedValues(),
			date_from: dateTime.fromDate,
			date_to: dateTime.toDate,
			skip_time: dateTime.fullDay ? 'Y' : 'N',
			time_from: Util.formatTime(Util.adjustDateForTimezoneOffset(dateTime.from, -entry.userTimezoneOffsetFrom, dateTime.fullDay)),
			time_to: Util.formatTime(Util.adjustDateForTimezoneOffset(dateTime.to, -entry.userTimezoneOffsetTo, dateTime.fullDay)),
			location: this.locationSelector.getTextValue(),
			tz_from: entry.getTimezoneFrom(),
			tz_to: entry.getTimezoneTo(),
			meeting_notify: this.userPlannerSelector.getInformValue() ? 'Y' : 'N',
			meeting_host: entry.data.MEETING_HOST || '0',
			chat_id: entry.data.MEETING
				? entry.data.MEETING.CHAT_ID
				: 0,
			exclude_users: (this.excludedUsers || []).map((user) => user.ID).join(','),
			attendeesEntityList: this.userPlannerSelector.getEntityList(),
			sendInvitesAgain: options.sendInvitesAgain ? 'Y' : 'N',
			hide_guests: this.userPlannerSelector.hideGuests ? 'Y' : 'N',
			requestUid: BX.Calendar.Util.registerRequestId(),
			private_event: entry.isPrivate() ? 'Y' : 'N',
		};

		let checkCurrentUsersAccessibility = !entry.id || this.checkCurrentUsersAccessibility();
		if (!checkCurrentUsersAccessibility
			&& this.getFormDataChanges().includes('codes'))
		{
			const previousAttendeesList = entry.getAttendeesEntityList();
			const newAttendeesList = [];
			data.attendeesEntityList.forEach(entity => {
				if (!previousAttendeesList.find((item) => {
					return entity.entityId === item.entityId
						&& parseInt(entity.id) === parseInt(item.id);
				}))
				{
					if (entity.entityId === 'user')
					{
						newAttendeesList.push(entity.id);
					}
					else
					{
						checkCurrentUsersAccessibility = true;
					}
				}
			});
			data.newAttendeesList = newAttendeesList;
		}
		data.checkCurrentUsersAccessibility = checkCurrentUsersAccessibility ? 'Y' : 'N';

		data.doCheckOccupancy = options.doCheckOccupancy === false ? 'N' : 'Y';

		if (entry.id && entry.isRecursive())
		{
			data.EVENT_RRULE = entry.data.RRULE;
		}

		if (options.recursionMode)
		{
			data.rec_edit_mode = options.recursionMode;
			data.current_date_from = Util.formatDate(entry.from);
		}

		if (this.getCurrentSection().color.toLowerCase() !== this.colorSelector.getValue().toLowerCase())
		{
			data.color = this.colorSelector.getValue();
		}

		this.state = this.STATE.REQUEST;

		this.freezePopup();
		this.BX.ajax.runAction('calendar.api.calendarentryajax.editEntry', {
				data: data,
				analyticsLabel: {
					calendarAction: this.isNewEntry() ? 'create_event' : 'edit_event',
					formType: 'compact',
					emailGuests: this.userPlannerSelector.hasExternalEmailUsers() ? 'Y' : 'N',
					markView: Util.getCurrentView() || 'outside',
					markCrm: 'N',
					markRrule: 'NONE',
					markMeeting: this.entry.isMeeting() ? 'Y' : 'N',
					markType: this.type
				}
			})
			.then((response) => {
					if (this.isLocationCalendar && this.roomsManager)
					{
						this.roomsManager.unsetHiddenRoom(Location.parseStringValue(data.location).room_id);
					}

					if (this.excludedUsers)
					{
						this.excludedUsers = [];
					}

					// unset section from hidden
					const section = this.getCurrentSection();
					if (section && !section.isShown())
					{
						section.show();
					}

					this.unfreezePopup();
					this.state = this.STATE.READY;
					if (response.data.entryId)
					{
						if (entry.id)
						{
							EntryManager.showEditEntryNotification(response.data.entryId);
						}
						else
						{
							EntryManager.showNewEntryNotification(response.data.entryId);
						}
					}

					this.emit('onSave', new BaseEvent({
						data: {
							responseData: response.data,
							options: options
						}
					}));
					this.close();

					if (response.data.countEventWithEmailGuestAmount)
					{
						Util.setEventWithEmailGuestAmount(response.data.countEventWithEmailGuestAmount);
					}

					if (Type.isArray(response.data.eventList)
						&& response.data.eventList.length
						&& response.data.eventList[0].REMIND
					)
					{
						EntryManager.setNewEntryReminders(
							dateTime.fullDay ? 'fullDay' : 'withTime',
							response.data.eventList[0].REMIND
						);
					}
				},
				(response) => {
					this.unfreezePopup();

					let errors = [];
					response.errors.forEach((error) => {
						if (
							error.code !== "edit_entry_user_busy"
							&& error.code !== "edit_entry_location_repeat_busy"
							&& error.code !== "edit_entry_location_busy_recurrence"
						)
						{
							errors.push(error);
						}
						else if (error.code === "edit_entry_location_repeat_busy")
						{
							this.showLocationRepeatBusyErrorPopup(error.message);
						}
					});

					response.errors = errors;

					if (response.data && Type.isPlainObject(response.data.busyUsersList))
					{
						this.handleBusyUsersError(response.data.busyUsersList);
					}

					if (response.errors && response.errors.length)
					{
						this.showError(response.errors);
					}

					this.state = this.STATE.ERROR;
				}
			);

		return true;
	}

	showLocationRepeatBusyErrorPopup(message)
	{
		if (!this.DOM.locationRepeatBusyErrorPopup)
		{
			this.DOM.locationRepeatBusyErrorPopup = EntryManager.getLocationRepeatBusyErrorPopup({
				message,
				onYesCallback: () => {
					this.lastUsedSaveOptions.doCheckOccupancy = false;
					this.save(this.lastUsedSaveOptions);
					this.lastUsedSaveOptions = {};
					this.DOM.locationRepeatBusyErrorPopup.close();
				},
				onCancelCallback: () => {
					this.DOM.locationRepeatBusyErrorPopup.close();
				},
				onPopupCloseCallback: () => {
					delete this.DOM.locationRepeatBusyErrorPopup;
				},
			});

			this.DOM.locationRepeatBusyErrorPopup.show();
		}
	}

	handleBusyUsersError(busyUsers)
	{
		const users = [];

		for (let id in busyUsers)
		{
			if (busyUsers.hasOwnProperty(id))
			{
				users.push(busyUsers[id]);
			}
		}

		this.busyUsersDialog = new BusyUsersDialog();

		this.busyUsersDialog.subscribe('onContinueEditing', () => {
			this.excludedUsers = [];
		});

		this.busyUsersDialog.subscribe('onSaveWithout', () => {
			this.excludedUsers.push(...users);
			this.save();
		});

		this.busyUsersDialog.show({users: users});
	}

	handleKeyPress(e)
	{
		if (
			this.getMode() === CompactEventForm.EDIT_MODE
			&& e.keyCode === Util.getKeyCode('enter')
			&& (e.ctrlKey || e.metaKey) && !e.altKey
			&& !this.isAdditionalPopupShown()
		)
		{
			if (this.busyUsersDialog && this.busyUsersDialog.isShown())
			{
				return;
			}

			this.checkDataBeforeCloseMode = false;
			this.locationSelector.selectContol.onChangeCallback();
			this.save();
		}
		else if (
			this.checkTopSlider()
			&& e.keyCode === Util.getKeyCode('escape')
			&& e.type === 'keyup'
			&& this.couldBeClosedByEsc()
		)
		{
			this.close();
		}
		else if (
			e.keyCode === Util.getKeyCode('delete')
			&& !this.isNewEntry()
			&& this.canDo('delete')
		)
		{
			const target = event.target || event.srcElement;
			const tagName = Type.isElementNode(target) ? target.tagName.toLowerCase() : null;
			if (tagName && !['input', 'textarea'].includes(tagName))
			{
				EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', () => {
					this.checkDataBeforeCloseMode = false;
					this.close();
				});
				EntryManager.deleteEntry(this.entry);
			}
		}
		else if (
			e.keyCode === Util.getKeyCode('enter')
			&& this.DOM.confirmPopup
		)
		{
			this.close(true, true);
		}
	}

	isAdditionalPopupShown(): boolean
	{
		return this.DOM.locationRepeatBusyErrorPopup;
	}

	getCurrentEntry()
	{
		return this.entry;
	}

	getCurrentSection()
	{
		let section = false;
		const sectionId = this.getCurrentSectionId();

		if (
			sectionId
			&& this.sectionIndex[sectionId] !== undefined
			&& this.sections[this.sectionIndex[sectionId]] !== undefined)
		{
			section = this.sections[this.sectionIndex[sectionId]];
		}
		return section;
	}

	getCurrentSectionId()
	{
		let sectionId = 0;
		if (this.sectionValue)
		{
			sectionId = this.sectionValue;
		}
		else
		{
			const entry = this.getCurrentEntry();
			if (entry instanceof Entry)
			{
				sectionId = parseInt(entry.sectionId);
			}

			// TODO: refactor - don't take first section
			if (!sectionId && this.sections[0])
			{
				sectionId = parseInt(this.sections[0].id);
			}
		}
		return sectionId;
	}

	handlePlannerSelectorChanges(event)
	{
		if (event instanceof BaseEvent)
		{
			const dateTimeValue = this.dateTimeControl.getValue();
			dateTimeValue.from = event.getData().dateFrom;
			dateTimeValue.to = event.getData().dateTo;
			// Date time
			this.dateTimeControl.setValue(dateTimeValue);
			this.userPlannerSelector.setDateTime(this.dateTimeControl.getValue());

			if (this.locationSelector)
			{
				this.locationSelector.checkLocationAccessibility(
					{
						from: event.getData().dateFrom,
						to: event.getData().dateTo,
						fullDay: event.getData().fullDay,
						currentEventId: this.entry.id
					},
				)
			}
			this.checkForChangesDebounce();
		}
	}

	editEntryInSlider(jumpToControl = false)
	{
		this.checkDataBeforeCloseMode = false;
		const dateTime = this.dateTimeControl.getValue();
		const calendarContext = Util.getCalendarContext();
		BX.Calendar.EntryManager.openEditSlider({
			calendarContext: calendarContext,
			entry: this.entry,
			type: this.type,
			isLocationCalendar: this.isLocationCalendar,
			locationAccess: this.locationAccess,
			dayOfWeekMonthFormat: this.dayOfWeekMonthFormat,
			roomsManager: this.roomsManager,
			locationCapacity: Location.getCurrentCapacity(), // for location component
			ownerId: this.ownerId,
			userId: this.userId,
			formDataValue: {
				section: this.sectionValue,
				name: this.DOM.titleInput.value,
				reminder: this.remindersControl.getSelectedRawValues(),
				color: this.colorSelector.getValue(),
				from: Util.adjustDateForTimezoneOffset(dateTime.from, -this.entry.userTimezoneOffsetFrom, dateTime.fullDay),
				to: Util.adjustDateForTimezoneOffset(dateTime.to, -this.entry.userTimezoneOffsetTo, dateTime.fullDay),
				fullDay: dateTime.fullDay,
				location: this.locationSelector.getTextValue(),
				meetingNotify: this.userPlannerSelector.getInformValue() ? 'Y' : 'N',
				hideGuests: this.userPlannerSelector.hideGuests ? 'Y' : 'N',
				attendeesEntityList: this.userPlannerSelector.getEntityList()
			},
			jumpToControl: jumpToControl,
		});
		this.close();
	}

	outsideMouseDownClose(event)
	{
		let target = event.target || event.srcElement;
		this.outsideMouseDown = !target.closest('div.popup-window');
	}

	checkTopSlider()
	{
		return !Util.getBX().SidePanel.Instance.getTopSlider();
	}

	checkOutsideClickClose(event)
	{
		let target = event.target || event.srcElement;
		this.outsideMouseUp = !target.closest('div.popup-window');
		if (this.couldBeClosedByEsc()
			&& this.outsideMouseDown
			&& this.outsideMouseUp
			&& (this.getMode() === CompactEventForm.VIEW_MODE
				|| !this.formDataChanged()
				|| this.isNewEntry())
		)
		{
			setTimeout(() => {
				this.close(false);
			}, 0);
		}
	}

	couldBeClosedByEsc()
	{
		return !PopupManager._popups.find((popup)=>{return popup && popup.getId() !== this.popupId && popup.isShown();});
	}

	emitOnChange()
	{
		this.emit('onChange', new BaseEvent({
			data: {
				form: this,
				entry: this.entry
			}
		}));
	}

	showError(errorList)
	{
		let errorText = '';

		if (Type.isArray(errorList))
		{
			errorList.forEach((error) => {
				errorText += error.message + "\n";
			});
		}

		if (errorText !== '')
		{
			alert(errorText);
		}
	}

	reloadEntryData()
	{
		if (this.isShown()
			&& !this.isNewEntry()
			&& this.getMode() === CompactEventForm.VIEW_MODE)
		{
			const calendar = Util.getCalendarContext();
			if (calendar)
			{
				const entry = EntryManager.getEntryInstance(
					calendar.getView().getEntryById(this.entry.getUniqueId())
				);

				if (entry && entry.getUniqueId())
				{
					this.entry = entry;
					if (this.isLocationMode())
					{
						this.setFormValuesLocation();
					}
					else
					{
						this.setFormValues();
					}
				}
			}
		}
	}

	checkCurrentUsersAccessibility()
	{
		return this.getFormDataChanges().includes('date&time');
	}

	handlePull(params)
	{
		if (
			this.userPlannerSelector
			&& this.userPlannerSelector?.planner?.isShown()
		)
		{
			const userIdList = Type.isArray(params?.fields?.ATTENDEES) ? params.fields.ATTENDEES: [];
			const eventOwner = params?.fields?.CAL_TYPE === 'user'
				? parseInt(params?.fields?.OWNER_ID)
				: parseInt(params?.fields?.CREATED_BY);
			if (!userIdList.includes(eventOwner))
			{
				userIdList.push(eventOwner);
			}
			this.userPlannerSelector.clearAccessibilityData(userIdList);

			this.userPlannerSelector.refreshPlannerStateDebounce();
		}

		const entry = this.getCurrentEntry();
		if (
			!this.isNewEntry()
			&& entry
			&& entry.parentId === parseInt(params?.fields?.PARENT_ID)
		)
		{
			if (params.command === 'delete_event'
				&& entry.getType() === params?.fields?.CAL_TYPE)
			{
				this.close();
			}
			else
			{
				const onEntryListReloadHandler = () => {
					this.reloadEntryDataDebounce();
					BX.Event.EventEmitter.unsubscribe('BX.Calendar:onEntryListReload', onEntryListReloadHandler);
				};
				BX.Event.EventEmitter.subscribe('BX.Calendar:onEntryListReload', onEntryListReloadHandler);
			}
		}
	}

	isSyncSection(section)
	{
		return section.isGoogle()
			|| section.isIcloud()
			|| section.isOffice365()
			|| section.isCalDav()
			|| section.hasConnection()
		;
	}

	getSectionsForEditEvent(sections, currentSection)
	{
		const result = [];
		const currentType = currentSection.type;
		result.push(currentSection);

		sections.forEach((section) => {
			if (!this.isSyncSection(section) && section.type === currentType)
			{
				result.push(section);
			}
		});

		this.sections = result;
		this.sectionIndex = [];
		if (Type.isArray(this.sections))
		{
			this.sections.forEach((value, ind) => {
				const id = parseInt(value.ID || value.id);
				if (id > 0)
				{
					this.sectionIndex[id] = ind;
				}
			}, this);
		}
	}

	releaseLocation(options = {})
	{
		const entry = this.getCurrentEntry();

		if (entry.id && entry.isRecursive()
			&& !options.confirmed
		)
		{
			EntryManager.showConfirmEditDialog({
				callback: (params) => {
					options.confirmed = true;
					this.releaseLocation({
						recursionMode: params.recursionMode,
						confirmed: true
					});
				}
			});
			return false;
		}

		if(!options.recursionMode)
		{
			options.recursionMode = '';
		}

		this.state = this.STATE.REQUEST;

		this.freezePopup();

		this.BX.ajax.runAction('calendar.api.locationajax.cancelBooking', {
			data:{
				parent_event_id: entry.parentId,
				recursion_mode: options.recursionMode,
				section_id: entry.sectionId,
				current_event_date_from: entry.data.DATE_FROM,
				current_event_date_to: entry.data.DATE_TO,
				owner_id: entry.data.CREATED_BY,
			}
		})
		.then(
			(response) => {
				this.unfreezePopup();
				this.state = this.STATE.READY;
				EntryManager.showReleaseLocationNotification();
				this.calendarContext.reloadDebounce();
				this.close();
			},
			(response) => {
				this.unfreezePopup();
				this.state = this.STATE.ERROR;
				this.close();
			}
		);

		return true;
	}

	showConfirmClosePopup()
	{
		this.DOM.confirmPopup = new MessageBox({
			message: this.getConfirmContent(),
			minHeight: 120,
			minWidth: 350,
			maxWidth: 350,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
			onOk: () => {
				this.DOM.confirmPopup.close();
				this.close(true, true);
			},
			onCancel: () => {
				this.DOM.confirmPopup.close();
			},
			okCaption: Loc.getMessage('EC_SEC_SLIDER_CLOSE'),
			popupOptions: {
				events: {
					onPopupClose: () => {
						delete this.DOM.confirmPopup;
					},
				},
				closeByEsc: true,
				padding: 0,
				contentPadding: 0,
				animation: 'fading-slide',
			}
		});

		this.DOM.confirmPopup.show();
	}

	getConfirmContent()
	{
		return Tag.render`
			<div class="calendar-list-slider-messagebox-text">${Loc.getMessage('EC_LEAVE_EVENT_CONFIRM_QUESTION') 
				+ '<br>' 
				+ Loc.getMessage('EC_LEAVE_EVENT_CONFIRM_DESC')}</div>
		`;
	}
}
