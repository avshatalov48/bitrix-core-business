"use strict";

import {Type, Event, Loc, Dom, Browser, Tag} from 'main.core';
import {SliderDateTimeControl} from './sliderdatetimecontrol.js';
import {SectionSelector, Reminder, ColorSelector, Location, RepeatSelector, UserSelector, BusyUsersDialog} from 'calendar.controls';
import {Util} from "calendar.util";
import {Entry, EntryManager} from "calendar.entry";
import {CalendarSectionManager} from "calendar.calendarsection";
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Planner} from "calendar.planner";

export class EventEditForm
{
	DOM = {};
	planner = null;
	uid = null;
	sliderId = "calendar:edit-entry-slider";
	zIndex = 3100;
	denyClose = false;
	formType = 'slider_main';
	STATE = {READY: 1, REQUEST: 2, ERROR: 3};
	sections = [];
	sectionIndex = {};
	trackingUsersList = [];
	userSettings = {};

	constructor(options = {})
	{
		this.name = options.name || 'eventeditform';
		this.type = options.type || 'user';
		this.ownerId = options.ownerId;
		this.userId = options.userId || parseInt(Loc.getMessage('USER_ID'));
		this.entryId = parseInt(options.entryId);
		this.entry = options.entry || null;
		this.emitter = new EventEmitter();
		this.emitter.setEventNamespace('BX.Calendar.EventEditForm');
		this.BX = Util.getBX();
		this.formSettings = {
			pinnedFields : {}
		};
		if (!this.ownerId && this.type === 'user')
		{
			this.ownerId = parseInt(Loc.getMessage('USER_ID'));
		}

		this.state = this.STATE.READY;
		this.sliderOnClose = this.hide.bind(this);
	}

	initInSlider(slider, promiseResolve)
	{
		this.sliderId = slider.getUrl();
		this.BX.addCustomEvent(slider, "SidePanel.Slider:onLoad", this.onLoadSlider.bind(this));
		this.BX.addCustomEvent(slider, "SidePanel.Slider:onClose", this.sliderOnClose);
		this.BX.addCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.destroy.bind(this));
		this.setCurrentEntry(this.entry || null);

		this.createContent(slider).then(function(html)
			{
				if (Type.isFunction(promiseResolve))
				{
					promiseResolve(html);
				}
			}.bind(this)
		);

		this.opened = true;
		this.bindEventHandlers();
	}

	show(params = {})
	{
		this.setCurrentEntry(params.entry);
		if (params.formType)
		{
			this.formType = params.formType;
		}

		this.BX.SidePanel.Instance.open(this.sliderId, {
			contentCallback: this.createContent.bind(this),
			label: {
				text: Loc.getMessage('CALENDAR_EVENT'),
				bgColor: "#55D0E0"
			},
			events: {
				onClose: this.sliderOnClose,
				onCloseComplete: this.destroy.bind(this),
				onLoad: this.onLoadSlider.bind(this)
			}
		});

		this.opened = true;
		this.bindEventHandlers();
	}

	isOpened()
	{
		return this.opened;
	}

	bindEventHandlers()
	{
		this.keyHandlerBind = this.keyHandler.bind(this);
		Event.bind(document, "click", Util.applyHacksForPopupzIndex);
		Event.bind(document, 'keydown', this.keyHandlerBind);

		// region 'protection from closing slider by accident'
		this.mouseUpNodeCheck = null;

		Event.bind(document, 'mousedown', (e)=>{this.mousedownTarget = e.target || e.srcElement;});
		Event.bind(document, 'mouseup', (e)=>{
			let target = e.target || e.srcElement;
			if (this.mousedownTarget !== target)
			{
				this.mouseUpNodeCheck = false;
			}
			setTimeout(()=>{this.mouseUpNodeCheck = null;}, 0);
		});
		// endregion

		this.BX.addCustomEvent(window, "onCalendarControlChildPopupShown", this.BX.proxy(this.denySliderClose, this));
		this.BX.addCustomEvent(window, "onCalendarControlChildPopupClosed", this.BX.proxy(this.allowSliderClose, this));
	}

	onLoadSlider(event)
	{
		let slider = event.getSlider();
		this.DOM.content = slider.layout.content;
		this.sliderId = slider.getUrl();

		// Used to execute javasctipt and attach CSS from ajax responce
		this.BX.html(slider.layout.content, slider.getData().get("sliderContent"));
		this.initControls(this.uid);

		this.setFormValues();
	}

	close()
	{
		if (!this.checkDenyClose())
		{
			this.state = this.STATE.READY;
			this.BX.SidePanel.Instance.close();
		}
	}

	save(options = {})
	{
		if (this.state === this.STATE.REQUEST)
			return;

		options = Type.isPlainObject(options) ? options : {};

		if (this.entry.id && this.entry.isRecursive()
			&& !options.confirmed && this.checkForSignificantChanges())
		{
			EntryManager.showConfirmEditDialog({
				callback: (params) => {
					this.save({
						recursionMode: params.recursionMode,
						confirmed: true
					});
				}
			});
			return false;
		}

		Dom.addClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
		Dom.addClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);

		this.DOM.form.id.value = this.entry.id || 0;

		// Location
		this.DOM.form.location.value = this.locationSelector.getTextValue();

		if (this.editor)
		{
			this.editor.SaveContent();
		}

		let section = this.getCurrentSection();
		if (section)
		{
			// Color
			if (section.COLOR.toLowerCase() !== this.colorSelector.getValue().toLowerCase())
			{
				this.DOM.form.color.value = this.colorSelector.getValue();
			}
			this.BX.userOptions.save('calendar', 'user_settings', 'lastUsedSection', parseInt(section.ID));
		}

		this.DOM.form.current_date_from.value = options.recursionMode ? Util.formatDate(this.entry.from) : '';
		this.DOM.form.rec_edit_mode.value = options.recursionMode || '';
		this.state = this.STATE.REQUEST;

		this.BX.ajax.runAction('calendar.api.calendarajax.editEntry', {
			data: new FormData(this.DOM.form)
		}).then((response) => {
				Dom.removeClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
				Dom.removeClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);

				this.state = this.STATE.READY;
				if (response.data.entryId)
				{
					if (this.entry.id)
					{
						EntryManager.showEditEntryNotification(response.data.entryId);
					}
					else
					{
						EntryManager.showNewEntryNotification(response.data.entryId);
					}
				}

				this.emitter.emit('onSave', new BaseEvent({
					data: {
						responseData: response.data,
						options: options
					}
				}));
				this.close();
			},
			(response) => {
				Dom.removeClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
				Dom.removeClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);

				if (response.data && Type.isPlainObject(response.data.busyUsersList))
				{
					this.handleBusyUsersError(response.data.busyUsersList);

					let errors = [];
					response.errors.forEach((error) => {
						if (error.code !== "edit_entry_user_busy")
						{
							errors.push(error);
						}
					});
					response.errors = errors;
				}

				if (response.errors && response.errors.length)
				{
					this.showError(response.errors);
				}

				this.state = this.STATE.ERROR;
			}
		);
	}

	handleBusyUsersError(busyUsers)
	{
		let
			users = [],
			userIds = [];

		for (let id in busyUsers)
		{
			if (busyUsers.hasOwnProperty(id))
			{
				users.push(busyUsers[id]);
				userIds.push(id);
			}
		}

		this.busyUsersDialog = new BusyUsersDialog();
		this.busyUsersDialog.subscribe('onSaveWithout', () => {
			this.DOM.form.exclude_users.value = userIds.join(',');
			this.save();
		});

		this.busyUsersDialog.show({users: users});
	}

	clientSideCheck()
	{

	}

	hide(event)
	{
		if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId)
		{
			if (this.checkDenyClose())
			{
				event.denyAction();
			}
			else
			{
				this.BX.removeCustomEvent("SidePanel.Slider::onClose", this.sliderOnClose);
				if (this.attendeesSelector)
				 	this.attendeesSelector.closeAll();

				this.destroy(event);
			}
		}
	}

	destroy(event)
	{
		if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
		{
			// if (window.LHEPostForm && window.LHEPostForm.unsetHandler && LHEPostForm.getHandler(this.editorId))
			// {
			// 	window.LHEPostForm.unsetHandler(this.editorId);
			// }
			this.BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{plannerId: this.plannerId}]);
			this.BX.removeCustomEvent('OnDestinationAddNewItem', this.BX.proxy(this.checkPlannerState, this));
			this.BX.removeCustomEvent('OnDestinationUnselect', this.BX.proxy(this.checkPlannerState, this));
			//this.BX.removeCustomEvent('OnCalendarPlannerSelectorChanged',
			// this.BX.proxy(this.onCalendarPlannerSelectorChanged, this));

			Event.unbind(document, 'keydown', this.keyHandlerBind);

			//this.BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", this.BX.proxy(this.destroy, this));
			this.BX.SidePanel.Instance.destroy(this.sliderId);

			//if (this.attendeesSelector)
			//	this.attendeesSelector.closeAll();

			//this.calendar.enableKeyHandler();
			Event.unbind(document, "click", Util.applyHacksForPopupzIndex);

			this.planner = null;
			this.opened = false;
		}
	}

	createContent(slider)
	{
		let promise = new this.BX.Promise();

		let entry = this.getCurrentEntry();

		this.BX.ajax.runAction('calendar.api.calendarajax.getEditEventSlider', {
			data: {
				event_id: this.entryId || entry.id,
				date_from: entry ? Util.formatDate(entry.from) : '',
				form_type: this.formType,
				type: this.type,
				ownerId: this.ownerId
			}
		})
			.then(
				// Success
				(response) => {
					if ((Type.isFunction(slider.isOpen) && slider.isOpen()) || slider.isOpen === true)
					{
						let html = this.BX.util.trim(response.data.html);
						slider.getData().set("sliderContent", html);

						let params = response.data.additionalParams;

						this.uid = params.uniqueId;
						this.editorId = params.editorId;
						this.lastUsedSection = params.lastSection;
						this.socnetDestination = params.socnetDestination || {};
						this.formSettings = this.getSettings(params.formSettings || []);
						this.setUserSettings(params.userSettings);
						this.handleSections(params.sections, params.trackingUsersList);
						this.handleLocationData(params.locationFeatureEnabled, params.locationList, params.iblockMeetingRoomList);

						if (!entry.id && !entry.sectionId)
						{
							this.setCurrentEntry();
						}

						this.updateEntryData(params.entry, {
							userSettings: this.userSettings
						});

						promise.fulfill(html);

						if (window.top.BX !== window.BX)
						{
							// TODO: Dirty Hack! Must get rid of it
							window.top.BX.loadCSS([
								'/bitrix/components/bitrix/calendar.grid/templates/.default/style.css',
								'/bitrix/js/calendar/new/calendar.css',
								'/bitrix/js/calendar/cal-style.css'
							]);
						}
					}
					else
					{
						// if (window["BXHtmlEditor"])
						// {
						// 	let editor = window["BXHtmlEditor"].Get(this.editorId);
						// 	if (editor)
						// 	{
						// 		editor.Destroy();
						// 	}
						// }
					}
				},
				// Failure
				(response)=>{
					//this.calendar.displayError(response.errors);
				}
			);
		return promise;
	}


	initControls(uid)
	{
		this.DOM.title = this.DOM.content.querySelector(`#${uid}_title`);

		this.DOM.formWrap = this.DOM.content.querySelector(`#${uid}_form_wrap`);
		this.DOM.form = this.DOM.content.querySelector(`#${uid}_form`);

		this.DOM.saveBtn = this.DOM.content.querySelector(`#${uid}_save`);
		this.DOM.closeBtn = this.DOM.content.querySelector(`#${uid}_close`);
		Event.bind(this.DOM.saveBtn, 'click', this.save.bind(this));
		Event.bind(this.DOM.closeBtn, 'click', this.close.bind(this));
		this.DOM.content.querySelector(`#${uid}_save_cmd`).innerHTML = Browser.isMac() ? '(Cmd+Enter)' : '(Ctrl+Enter)';

		this.initFormFieldManager(uid);
		this.initDateTimeControl(uid);
		this.initNameControl(uid);
		this.initEditorControl(uid);
		this.initAttendeesControl(uid);
		this.initPlanner(uid);
		this.initReminderControl(uid);
		this.initSectionSelector(uid);
		this.initLocationControl(uid);
		this.initRepeatRuleControl(uid);
		this.initColorControl(uid);
		this.initCrmUfControl(uid);
		this.initAdditionalControls(uid);

		this.checkLastItemBorder();
	}

	updateEntryData(entryData, options = {})
	{
		if (this.entry instanceof Entry)
		{
			let userSettings = options.userSettings || {};

			if (Type.isPlainObject(entryData))
			{
				this.entry.prepareData(entryData);
			}
			else
			{
				if (!this.entry.getTimezoneFrom() || this.entry.getTimezoneTo())
				{
					this.entry.setTimezone(userSettings.timezoneName || userSettings.timezoneDefaultName || null);
				}
			}
		}
	}

	handleSections(sections, trackingUsersList)
	{
		this.sections = sections;
		this.sectionIndex = {};
		this.trackingUsersList = trackingUsersList || [];

		if (Type.isArray(sections))
		{
			sections.forEach((value, ind) => {this.sectionIndex[parseInt(value.ID)] = ind;}, this);
		}
	}

	handleLocationData(locationFeatureEnabled, locationList, iblockMeetingRoomList)
	{
		this.locationFeatureEnabled = !!locationFeatureEnabled;
		this.locationList = locationList || [];
		this.iblockMeetingRoomList = iblockMeetingRoomList || [];

		Location.setLocationList(locationList);
		Location.setMeetingRoomList(iblockMeetingRoomList);
	}

	setUserSettings(userSettings)
	{
		this.userSettings = userSettings;
	}

	setFormValues()
	{
		let entry = this.entry;

		// Date time
		this.dateTimeControl.setValue({
			from: entry.from,
			to: entry.to,
			fullDay: entry.fullDay,
			timezoneFrom: entry.getTimezoneFrom() || '',
			timezoneTo: entry.getTimezoneTo() || '',
			timezoneName: this.userSettings.timezoneName
		});

		this.DOM.entryName.value = entry.getName();

		// Section
		this.DOM.sectionInput.value = this.getCurrentSectionId();
		this.sectionSelector.updateValue();

		if (!this.fieldIsPinned('section'))
		{
			let currentSection = this.getCurrentSection();
			if (currentSection['CAL_TYPE'] !== this.type || currentSection['CAL_TYPE'] === this.type && parseInt(currentSection['OWNER_ID']) !== this.ownerId)
			{
				this.pinField('section');
			}
		}

		// Color
		this.colorSelector.setValue(entry.getColor() || this.getCurrentSection().COLOR);

		// Reminders
		this.reminderControl.setValue(entry.getReminders());

		// Recursion
		this.repeatSelector.setValue(entry.getRrule());

		// accessibility
		if (this.DOM.accessibilityInput)
		{
			this.DOM.accessibilityInput.value = entry.accessibility;
		}

		// Location
		this.locationSelector.setValue(entry.getLocation());

		// Private
		if (this.DOM.privateEventCheckbox)
		{
			this.DOM.privateEventCheckbox.checked = entry.private;
		}

		// Importance
		if (this.DOM.importantEventCheckbox)
		{
			this.DOM.importantEventCheckbox.checked = entry.important;
		}

		if (this.userSelector)
		{
			this.userSelector.setValue(entry.getAttendeesCodes());
		}

		this.loadPlannerData({
			codes: entry.getAttendeesCodes(),
			from: Util.formatDate(entry.from.getTime() - Util.getDayLength() * 3),
			to: Util.formatDate(entry.to.getTime() + Util.getDayLength() * 10),
			timezone: entry.getTimezoneFrom(),
			location: this.locationSelector.getTextValue()
		});
	}

	switchFullDay(value)
	{
		value = !!this.DOM.fullDay.checked;
		if (value && Type.isString(this.userSettings.timezoneName)
			&& (!this.DOM.fromTz.value || !this.DOM.toTz.value))
		{
			this.DOM.fromTz.value = this.userSettings.timezoneName;
			this.DOM.toTz.value = this.userSettings.timezoneName;
			this.DOM.defTimezone.value = this.userSettings.timezoneName;
		}

		if (value)
		{
			Dom.addClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
		}
		else
		{
			Dom.removeClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
		}

		if(this.reminderControl)
		{
			this.reminderControl.setFullDayMode(value);
		}

		this.refreshPlannerState();
	}

	switchTimezone()
	{
		if (Dom.hasClass(this.DOM.tzCont, 'calendar-options-timezone-collapse'))
		{
			Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
			Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
		}
		else
		{
			Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
			Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
		}
	}

	initFormFieldManager(uid)
	{
		this.DOM.mainBlock = this.DOM.content.querySelector(`#${uid}_main_block_wrap`);
		this.DOM.additionalBlockWrap = this.DOM.content.querySelector(`#${uid}_additional_block_wrap`);
		this.DOM.additionalBlock = this.DOM.content.querySelector(`#${uid}_additional_block`);
		this.DOM.pinnedNamesWrap = this.DOM.content.querySelector(`#${uid}_additional_pinned_names`);
		this.DOM.additionalSwitch = this.DOM.content.querySelector(`#${uid}_additional_switch`);

		Event.bind(this.DOM.additionalSwitch, 'click', () => {
			Dom.toggleClass(this.DOM.additionalSwitch, 'opened');
			Dom.toggleClass(this.DOM.additionalBlock, 'invisible');
		});

		Event.bind(this.DOM.formWrap, 'click', (e) => {
			let target = e.target || e.srcElement;
			if (target && target.getAttribute && target.getAttribute('data-bx-fixfield'))
			{
				let fieldName = target.getAttribute('data-bx-fixfield');
				if (!this.fieldIsPinned(fieldName))
				{
					this.pinField(fieldName);
				}
				else
				{
					this.unPinField(fieldName);
				}
			}
		});
	}

	initDateTimeControl(uid)
	{
		this.dateTimeControl = new SliderDateTimeControl(uid, {
			showTimezone: true,
			outerContent: this.DOM.content
		});
	}

	initNameControl(uid)
	{
		this.DOM.entryName = this.DOM.content.querySelector(`#${uid}_entry_name`);
		setTimeout(() => {
			this.DOM.entryName.focus();
			this.DOM.entryName.select();
		}, 500);
	}

	initReminderControl(uid)
	{
		this.reminderValues = [];
		this.DOM.reminderWrap = this.DOM.content.querySelector(`#${uid}_reminder`);
		this.DOM.reminderInputsWrap = this.DOM.reminderWrap.appendChild(Tag.render`<span></span>`);

		this.reminderControl = new Reminder({
			wrap: this.DOM.reminderWrap,
			zIndex: this.zIndex,
			// showPopupCallBack: function()
			// {
			// 	//_this.denySliderClose();
			// },
			// hidePopupCallBack: function()
			// {
			// 	//_this.allowSliderClose();
			// }
		});

		this.reminderControl.subscribe('onChange', (event) =>
		{
			if (event instanceof BaseEvent)
			{
				this.reminderValues = event.getData().values;
				Dom.clean(this.DOM.reminderInputsWrap);
				this.reminderValues.forEach((value) => {
					this.DOM.reminderInputsWrap.appendChild(Dom.create('INPUT', {
						props: {name: 'reminder[]', type: 'hidden'},
						attrs: {value: value}}));
				});
			}
		});

		if (this.dateTimeControl)
		{
			this.dateTimeControl.subscribe('onChange', function (event)
			{
				if (event instanceof BaseEvent)
				{
					let value = event.getData().value;
					this.reminderControl.setFullDayMode(value.fullDay);

					if (this.newPlanner)
					{
						this.newPlanner.updateSelector(value.from, value.to, value.fullDay);
					}
				}
			}.bind(this));
		}
	}

	initSectionSelector(uid)
	{
		this.DOM.sectionInput = this.DOM.content.querySelector(`#${uid}_section`);
		this.sectionSelector = new SectionSelector({
			outerWrap: this.DOM.content.querySelector(`#${uid}_section_wrap`),
			defaultCalendarType: this.type,
			defaultOwnerId: this.ownerId,
			sectionList: this.sections,
			sectionGroupList: CalendarSectionManager.getSectionGroupList({
				type: this.type || 'user',
				ownerId: this.ownerId || this.userId,
				userId: this.userId,
				trackingUsersList: this.trackingUsersList,
			}),
			mode: 'full',
			zIndex: this.zIndex,
			getCurrentSection: ()=>{
				let section = this.getCurrentSection();
				if (section)
				{
					return {
						id: section.ID,
						name: section.NAME,
						color: section.COLOR
					}
				}
				return false;
			},
			selectCallback: (sectionValue) =>{
				if (sectionValue)
				{
					//this.BX.userOptions.save('calendar', 'user_settings', 'lastUsedSection', sectionValue.id);
					this.DOM.sectionInput.value = sectionValue.id;
					if (this.colorSelector)
					{
						this.colorSelector.setValue(sectionValue.color);
					}
					this.entry.setSectionId(sectionValue.id);
				}
			}
		});
	}

	initEditorControl(uid)
	{
		if (!window["BXHtmlEditor"])
		{
			return setTimeout(BX.delegate(this.initEditorControl, this), 50);
		}

		this.editor = null;
		if (window["BXHtmlEditor"])
		{
			this.editor = window["BXHtmlEditor"].Get(this.editorId);
		}

		if (!this.editor && top["BXHtmlEditor"] !== window["BXHtmlEditor"])
		{
			this.editor = top["BXHtmlEditor"].Get(this.editorId);
		}

		if (this.editor && this.editor.IsShown())
		{
			this.customizeHtmlEditor();
		}
		else
		{
			this.BX.addCustomEvent(window["BXHtmlEditor"], 'OnEditorCreated', function (editor)
			{
				if (editor.id === this.editorId)
				{
					this.editor = editor;
					this.customizeHtmlEditor();
				}
			}.bind(this));
		}
	}

	customizeHtmlEditor()
	{
		let editor = this.editor;
		if (editor.toolbar && editor.toolbar.controls && editor.toolbar.controls.spoiler)
		{
			Dom.remove(editor.toolbar.controls.spoiler.pCont);
		}
	}

	initLocationControl(uid)
	{
		this.DOM.locationWrap = this.DOM.content.querySelector(`#${uid}_location_wrap`);
		this.DOM.locationInput = this.DOM.content.querySelector(`#${uid}_location`);

		this.locationSelector = new Location(
			{
				inputName: 'lo_cation', // don't use 'location' word here mantis:107863
				wrap: this.DOM.locationWrap,
				richLocationEnabled: this.locationFeatureEnabled,
				locationList: this.locationList,
				iblockMeetingRoomList: this.iblockMeetingRoomList,
				onChangeCallback: this.checkPlannerState.bind(this)
			}
		);
	}

	initRepeatRuleControl(uid)
	{
		this.DOM.rruleWrap = this.DOM.content.querySelector(`#${uid}_rrule_wrap`);
		this.repeatSelector = new RepeatSelector(
			{
				wrap: this.DOM.rruleWrap,
				rruleType: this.DOM.content.querySelector(`#${uid}_rrule_type`),
				getDate: function() {return this.dateTimeControl.getValue().from;}.bind(this)
			}
		);

		this.dateTimeControl.subscribe('onChange', ()=>{
			if (this.repeatSelector.getType() === 'weekly')
			{
				this.repeatSelector.changeType(this.repeatSelector.getType());
			}
		});
	}

	initAttendeesControl(uid)
	{
		// return;
		// if (!this.calendar.util.isMeetingsEnabled())
		// {
		// 	Dom.remove(this.DOM.formWrap.querySelector('.calendar-options-item-destination'));
		// 	Dom.remove(this.DOM.formWrap.querySelector('.calendar-options-item-planner'));
		// 	return;
		// }
		this.DOM.attendeesWrap = this.DOM.content.querySelector(`#${uid}_attendees_wrap`);
		this.DOM.plannerWrap = this.DOM.content.querySelector(`#${uid}_planner_wrap`);
		this.DOM.attendeesTitle = this.DOM.content.querySelector(`#${uid}_attendees_title_wrap`);
		this.plannerId = uid + '_slider_planner';

		// let attendeesCodes = null;
		// if (this.entry.id)
		// {
		// 	this.DOM.attendeesCodesInput = BX(this.id + '_attendees_codes');
		// 	if (this.DOM.attendeesCodesInput && this.DOM.attendeesCodesInput.value)
		// 	{
		// 		this.attendeesCodes = this.DOM.attendeesCodesInput.value.split(',');
		// 	}
		// }
		//
		// if (!this.attendeesCodes)
		// {
		// 	this.attendeesCodes = this.entry.getAttendeesCodes ? this.entry.getAttendeesCodes() : (this.entry.attendeesCodes || false);
		// }

		this.userSelector = new UserSelector(
			{
				wrapNode: this.DOM.attendeesWrap,
				items: this.getUserSelectorConfig('items'),
				itemsSelected: this.getUserSelectorConfig('itemsSelected'),
				itemsLast: this.getUserSelectorConfig('itemsLast')
			}
		);

		// this.attendees = this.entry.attendees || [this.calendar.currentUser];
		// this.attendeesIndex = {};
		// this.attendees.forEach(function(userId){this.attendeesIndex[userId] = true;}, this);
		// this.attendeesCodes = null;
		// if (this.entry.id)
		// {
		// 	this.DOM.attendeesCodesInput = BX(this.id + '_attendees_codes');
		// 	if (this.DOM.attendeesCodesInput && this.DOM.attendeesCodesInput.value)
		// 	{
		// 		this.attendeesCodes = this.DOM.attendeesCodesInput.value.split(',');
		// 	}
		// }
		//
		// if (!this.attendeesCodes)
		// {
		// 	this.attendeesCodes = this.entry.getAttendeesCodes ? this.entry.getAttendeesCodes() : (this.entry.attendeesCodes || false);
		// }

		this.DOM.plannerWrap = this.DOM.content.querySelector(`#${uid}_planner_wrap1`);
		this.DOM.attendeesTitle = this.DOM.content.querySelector(`#${uid}_attendees_title_wrap`);
		this.plannerId = uid + '_slider_planner';

		setTimeout(()=> {
			this.BX.addCustomEvent('OnDestinationAddNewItem', this.checkPlannerState.bind(this));
			this.BX.addCustomEvent('OnDestinationUnselect', this.checkPlannerState.bind(this));
		}, 100);
		//BX.addCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.onCalendarPlannerSelectorChanged, this));
		//BX.addCustomEvent('OnCalendarPlannerUpdated', BX.proxy(this.onCalendarPlannerUpdatedHandler, this));

		this.DOM.moreOuterWrap = BX(this.id + '_more_outer_wrap');

		//this.DOM.moreLink = BX.adjust(BX(this.id + '_more'), {events: {click: BX.delegate(function(){BX.toggleClass(this.DOM.moreWrap, 'collapse');}, this)}});
		//this.DOM.moreWrap = BX(this.id + '_more_wrap');

		if (this.DOM.form.allow_invite)
		{
			if (this.entry.data)
				this.DOM.form.allow_invite.checked = this.entry.data.MEETING && this.entry.data.MEETING.ALLOW_INVITE;
			else
				this.DOM.form.allow_invite.checked = this.entry.allowInvite;
		}

		if (this.DOM.form.meeting_notify)
		{
			if (this.entry.data && this.entry.data.MEETING)
				this.DOM.form.meeting_notify.checked = this.entry.data.MEETING.NOTIFY;
			else
				this.DOM.form.meeting_notify.checked = true; // default value
		}

		this.dateTimeControl.subscribe('onChange', function (event)
		{
			// TODO: we don't need to do additional request here. Need to check it
			//this.checkPlannerState();
		}.bind(this));
	}

	initPlanner(uid)
	{
		this.DOM.plannerOuterWrap = this.DOM.content.querySelector(`#${uid}_planner_outer_wrap`);

		this.newPlanner = new Planner({
			wrap: this.DOM.plannerOuterWrap,
			minWidth: parseInt(this.DOM.plannerOuterWrap.offsetWidth),
		});

		this.newPlanner.subscribe('onDateChange', this.handlePlannerSelectorChanges.bind(this));


		this.newPlanner.show();
		this.newPlanner.showLoader();
	}

	loadPlannerData(params = {})
	{
		this.newPlanner.showLoader();
		return new Promise((resolve) => {
			this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
				data: {
					entryId: this.entry.id || 0,
					codes: params.codes || [],
					dateFrom: params.from || '',
					dateTo: params.to || '',
					timezone: params.timezone || '',
					location: params.location || '',
					entries: params.entrieIds || false
				}
			})
				.then((response) => {
						this.newPlanner.hideLoader();

						let
							attendees = [],
							attendeesIndex = {},
							entries = response.data.entries;

						if (Type.isArray(response.data.entries))
						{
							response.data.entries.forEach((entry) => {
								if (entry.type === 'user')
								{
									attendees.push({
										id: entry.id,
										name: entry.name,
										avatar: entry.avatar,
										smallAvatar: entry.smallAvatar || entry.avatar,
										url: entry.url
									});
								}
							});
						}

						let dateTime = this.dateTimeControl.getValue();
						this.newPlanner.update(
							response.data.entries,
							response.data.accessibility
						);
						this.newPlanner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);

						// let
						// 	i,
						// 	attendees = [],
						// 	attendeesIndex = {},
						// 	updateAttendeesControl = false,
						// 	entries = response.data.entries,
						// 	accessibility = response.data.accessibility,
						// 	showPlanner = !!(params.entries || (entries && entries.length > 0));
						//
						// if (Type.isArray(entries))
						// {
						// 	for (i = 0; i < entries.length; i++)
						// 	{
						// 		if (entries[i].type === 'user')
						// 		{
						// 			attendees.push({
						// 				id: entries[i].id,
						// 				name: entries[i].name,
						// 				avatar: entries[i].avatar,
						// 				smallAvatar: entries[i].smallAvatar || entries[i].avatar,
						// 				url: entries[i].url
						// 			});
						// 			attendeesIndex[entries[i].id] = true;
						// 		}
						// 	}
						// }

						resolve(response);

						// // Show first time or refresh it state
						// if (showPlanner)
						// {
						// 	let refreshParams = {};
						//
						// 	if (params.entries)
						// 	{
						// 		entries = params.entries;
						// 		refreshParams.scaleFrom = params.from;
						// 		refreshParams.scaleTo = params.to;
						// 	}
						//
						// 	refreshParams.loadedDataFrom = params.from;
						// 	refreshParams.loadedDataTo = params.to;
						//
						// 	refreshParams.data = {
						// 		entries: entries,
						// 		accessibility: accessibility
						// 	};
						//
						// 	refreshParams.focusSelector = params.focusSelector === undefined ? false : params.focusSelector;
						// 	this.refreshPlanner(refreshParams);
						// }
					},
					(response) => {resolve(response);}
				);

		});
	}


	initAdditionalControls(uid)
	{
		this.DOM.accessibilityInput = this.DOM.content.querySelector(`#${uid}_accessibility`);
		this.DOM.privateEventCheckbox = this.DOM.content.querySelector(`#${uid}_private`);
		this.DOM.importantEventCheckbox = this.DOM.content.querySelector(`#${uid}_important`);
	}

	initColorControl(uid)
	{
		this.DOM.colorWrap = this.DOM.content.querySelector(`#${uid}_color_selector_wrap`);
		this.colorSelector = new ColorSelector(
			{
				wrap: this.DOM.colorWrap
			}
		);
	}

	initCrmUfControl(uid)
	{
		this.DOM.crmUfWrap = BX(uid + '-uf-crm-wrap');

		if (this.DOM.crmUfWrap)
		{
			let entry = this.getCurrentEntry();
			let loader = this.DOM.crmUfWrap.appendChild(Dom.adjust(Util.getLoader(50), {style: {height: '40px', width: '40px'}}));

			setTimeout(function(){
				this.BX.ajax.runAction('calendar.api.calendarajax.getCrmUserfield', {
					data: {
						event_id: (entry && entry.id) ? entry.id : 0
					}
				}).then(
					// Success
					function(response)
					{
						if (Type.isDomNode(this.DOM.crmUfWrap))
						{
							this.BX.html(this.DOM.crmUfWrap, response.data.html);
						}
					}.bind(this),
					// Failure
					function (response)
					{
						Dom.remove(loader);
					}.bind(this)
				);
			}.bind(this), 800);
		}
	}

	denySliderClose()
	{
		this.denyClose = true;
	}

	allowSliderClose()
	{
		this.denyClose = false;
	}

	checkDenyClose()
	{
		// pending request
		if (this.state === this.STATE.REQUEST)
		{
			return true;
		}

		// Check if closing of slider was caused during selection.
		if (!Type.isNull(this.mouseUpNodeCheck))
		{
			return !this.mouseUpNodeCheck;
		}

		// if (top.BX(this.id + '_time_from_div') && top.BX(this.id + '_time_from_div').style.display !== 'none')
		// 	return true;
		//
		// if (top.BX(this.id + '_time_to_div') && top.BX(this.id + '_time_to_div').style.display !== 'none')
		// 	return true;

		return this.denyClose;
	}

	setCurrentEntry(entry = null, userIndex = null)
	{
		this.entry = EntryManager.getEntryInstance(entry, userIndex);
	}

	getCurrentEntry()
	{
		return this.entry;
	}

	getCurrentSection()
	{
		let
			section = false,
			sectionId = this.getCurrentSectionId();

		if (sectionId
			&& this.sectionIndex[sectionId] !== undefined
			&& this.sections[this.sectionIndex[sectionId]] !== undefined)
		{
			section = this.sections[this.sectionIndex[sectionId]];
		}

		return section;
	}

	getCurrentSectionId()
	{
		let
			section = 0,
			entry = this.getCurrentEntry();

		if (entry instanceof Entry)
		{
			section = parseInt(entry.sectionId);
		}

		if (!section && this.lastUsedSection && this.sections[this.sectionIndex[parseInt(this.lastUsedSection)]])
		{
			section = parseInt(this.lastUsedSection);
		}

		if (!section && this.sections[0])
		{
			section = parseInt(this.sections[0].ID);
		}
		return section;
	}

	getUserSelectorConfig(key)
	{
		let userSelectorConfig;
		if (key === 'items')
		{
			userSelectorConfig = {
				users: this.socnetDestination.USERS || {},
				groups: this.socnetDestination.EXTRANET_USER === 'Y'
				|| this.socnetDestination.DENY_TOALL ? {} :
					{UA: {id: 'UA', name: this.socnetDestination.DEPARTMENT ? Loc.getMessage('EC_SOCNET_DESTINATION_4') : Loc.getMessage('EC_SOCNET_DESTINATION_3')}},
				sonetgroups: this.socnetDestination.SONETGROUPS || {},
				department: this.socnetDestination.DEPARTMENT || {},
				departmentRelation: this.socnetDestination.DEPARTMENT_RELATION || {}
			};
		}
		else if (key === 'itemsLast' && this.socnetDestination.LAST)
		{
			userSelectorConfig = {
				users: this.socnetDestination.LAST.USERS || {},
				groups: this.socnetDestination.EXTRANET_USER === 'Y' ? {} : {UA: true},
				sonetgroups: this.socnetDestination.LAST.SONETGROUPS || {},
				department: this.socnetDestination.LAST.DEPARTMENT || {}
			};
		}
		else if (key === 'itemsSelected')
		{
			userSelectorConfig = this.socnetDestination.SELECTED || {};
		}

		return userSelectorConfig;
	}

	setUserSelectorConfig(socnetDestination)
	{
		this.socnetDestination = socnetDestination;
	}

	pinField(fieldName)
	{
		let [placeHolders, placeHoldersAdditional] = this.getPlaceholders();
		let
			field = placeHoldersAdditional[fieldName],
			newField = placeHolders[fieldName],
			fieldHeight = field.offsetHeight;

		field.style.height = fieldHeight + 'px';
		setTimeout(function(){Dom.addClass(field, 'calendar-hide-field');}, 0);
		newField.style.height = '0';

		if (fieldName === 'description')
		{
			setTimeout(function()
			{
				if (!this.DOM.descriptionAdditionalWrap)
				{
					this.DOM.descriptionAdditionalWrap = this.DOM.additionalBlock.querySelector('.calendar-info-panel-description');
				}

				if (this.DOM.descriptionAdditionalWrap)
				{

					while(this.DOM.descriptionAdditionalWrap.firstChild)
					{
						newField.appendChild(this.DOM.descriptionAdditionalWrap.firstChild);
					}
				}
				newField.style.height = fieldHeight + 'px';
			}.bind(this), 200);

			setTimeout(function(){
				Dom.removeClass(field, 'calendar-hide-field');
				field.style.display = 'none';
				newField.style.height = '';
				this.pinnedFieldsIndex[fieldName] = true;
				let editor = window["BXHtmlEditor"].Get(this.editorId);
				if (editor)
				{
					editor.CheckAndReInit();
				}
				this.saveSettings();
				this.updateAdditionalBlockState();
			}.bind(this), 500);
		}
		else
		{
			setTimeout(function()
			{
				while(field.firstChild)
				{
					newField.appendChild(field.firstChild);
				}
				newField.style.height = fieldHeight + 'px';
			}, 200);

			setTimeout(() => {
				Dom.removeClass(field, 'calendar-hide-field');
				field.style.height = '';
				newField.style.height = '';
				this.pinnedFieldsIndex[fieldName] = true;
				this.saveSettings();
				this.updateAdditionalBlockState();
			}, 300);
		}
	}

	unPinField(fieldName)
	{
		let [placeHolders, placeHoldersAdditional] = this.getPlaceholders();
		let
			field = placeHolders[fieldName],
			newField = placeHoldersAdditional[fieldName],
			fieldHeight = field.offsetHeight;

		field.style.height = fieldHeight + 'px';
		setTimeout(function(){
			Dom.addClass(field, 'calendar-hide-field');
		}, 0);
		newField.style.height = '0';

		if (fieldName === 'description')
		{
			setTimeout(function(){
				if (!this.DOM.descriptionAdditionalWrap)
				{
					this.DOM.descriptionAdditionalWrap = this.DOM.additionalBlock.querySelector('.calendar-info-panel-description');
				}

				if (this.DOM.descriptionAdditionalWrap)
				{
					while(field.firstChild)
					{
						this.DOM.descriptionAdditionalWrap.appendChild(field.firstChild);
					}
				}

				newField.style.display = '';
				newField.style.height = fieldHeight + 'px';
			}.bind(this), 200);

			setTimeout(function(){
				Dom.removeClass(field, 'calendar-hide-field');
				field.style.height = '';
				newField.style.height = '';
				this.pinnedFieldsIndex[fieldName] = false;

				let editor = window["BXHtmlEditor"].Get(this.editorId);
				if (editor)
				{
					editor.CheckAndReInit();
				}

				this.saveSettings();
				this.updateAdditionalBlockState();
			}.bind(this), 300);
		}
		else
		{
			setTimeout(function(){
				while(field.firstChild)
				{
					newField.appendChild(field.firstChild);
				}
				newField.style.height = fieldHeight + 'px';
			}, 200);

			setTimeout(function(){
				Dom.removeClass(field, 'calendar-hide-field');
				field.style.height = '';
				newField.style.height = '';
				this.pinnedFieldsIndex[fieldName] = false;

				this.saveSettings();
				this.updateAdditionalBlockState();
			}.bind(this), 300);
		}
	}

	fieldIsPinned(fieldName)
	{
		return this.pinnedFieldsIndex[fieldName];
	}

	getPlaceholders()
	{
		if (!this.placeHolders)
		{
			this.placeHolders = {};
			this.placeHoldersAdditional = {};

			let
				i,
				fieldId,
				nodes = this.DOM.formWrap.querySelectorAll('.calendar-field-additional-placeholder');

			for (i = 0; i < nodes.length; i++)
			{
				fieldId = nodes[i].getAttribute('data-bx-block-placeholer');
				if (fieldId)
				{
					this.placeHoldersAdditional[fieldId] = nodes[i];
				}
			}

			nodes = this.DOM.formWrap.querySelectorAll('.calendar-field-placeholder');
			for (i = 0; i < nodes.length; i++)
			{
				fieldId = nodes[i].getAttribute('data-bx-block-placeholer');
				if (fieldId)
				{
					this.placeHolders[fieldId] = nodes[i];
				}
			}
		}

		return [this.placeHolders, this.placeHoldersAdditional];
	}

	getSettings(settings)
	{
		this.pinnedFieldsIndex = {};
		let i, pinnedFields = [];

		for (i in settings.pinnedFields)
		{
			if (settings.pinnedFields.hasOwnProperty(i))
			{
				pinnedFields.push(settings.pinnedFields[i]);
				this.pinnedFieldsIndex[settings.pinnedFields[i]] = true;
			}
		}
		settings.pinnedFields = pinnedFields;
		return settings;
	}

	saveSettings()
	{
		let fieldName, pinnedFields = [];

		for (fieldName in this.pinnedFieldsIndex)
		{
			if (this.pinnedFieldsIndex.hasOwnProperty(fieldName) && this.pinnedFieldsIndex[fieldName])
			{
				pinnedFields.push(fieldName);
			}
		}

		this.formSettings.pinnedFields = pinnedFields;
		this.BX.userOptions.save('calendar', this.formType, 'pinnedFields', pinnedFields);
	}

	updateAdditionalBlockState(timeout)
	{
		if (timeout !== false)
		{
			if (this.updateAdditionalBlockTimeout)
			{
				clearTimeout(this.updateAdditionalBlockTimeout);
				this.updateAdditionalBlockTimeout = null;
			}
			this.updateAdditionalBlockTimeout = setTimeout(() => {this.updateAdditionalBlockState(false)}, 300);
		}
		else
		{
			let i, names = this.DOM.additionalBlock.getElementsByClassName('js-calendar-field-name');
			Dom.clean(this.DOM.pinnedNamesWrap);
			for (i = 0; i < names.length; i++)
			{
				this.DOM.pinnedNamesWrap.appendChild(Dom.create("SPAN", {props: {className: 'calendar-additional-alt-promo-text'}, html: names[i].innerHTML}));
			}

			if (!names.length)
			{
				Dom.addClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
			}
			else if (Dom.hasClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden'))
			{
				Dom.removeClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
			}

			this.checkLastItemBorder();
		}
	}

	checkLastItemBorder()
	{
		let
			noBorderClass = 'no-border',
			i, nodes;

		nodes = this.DOM.mainBlock.querySelectorAll('.calendar-options-item-border');
		for (i = 0; i < nodes.length; i++)
		{
			if (i === nodes.length - 1)
			{
				Dom.addClass(nodes[i], noBorderClass);
			}
			else
			{
				Dom.removeClass(nodes[i], noBorderClass);
			}
		}

		nodes = this.DOM.additionalBlock.querySelectorAll('.calendar-options-item-border');
		for (i = 0; i < nodes.length; i++)
		{
			if (i === nodes.length - 1)
			{
				Dom.addClass(nodes[i], noBorderClass);
			}
			else
			{
				Dom.removeClass(nodes[i], noBorderClass);
			}
		}
	}

	handlePlannerSelectorChanges(event)
	{
		if (event instanceof BaseEvent)
		{
			let data = event.getData();
			// Date time
			this.dateTimeControl.setValue({
				from: data.dateFrom,
				to: data.dateTo
			});
			//this.checkLocationAccessibility();
		}
	}

	// onCalendarPlannerSelectorChanged(params)
	// {
	// 	if (params.plannerId === this.plannerId)
	// 	{
	// 		if (!this.planner)
	// 		{
	// 			this.planner = params.planner;
	// 		}
	//
	// 		// Date time
	// 		this.dateTimeControl.setValue({
	// 			from: params.dateFrom,
	// 			to: params.dateTo
	// 		});
	// 		//this.checkLocationAccessibility();
	// 	}
	// }

	// onCalendarPlannerUpdatedHandler(planner, params)
	// {
	// 	if (!this.planner)
	// 	{
	// 		this.planner = planner;
	// 		//this.checkLocationAccessibility();
	// 	}
	// }

	checkPlannerState()
	{
		let dateTime = this.dateTimeControl.getValue();
		this.loadPlannerData({
			codes: this.userSelector.getCodes(),
			from: Util.formatDate(dateTime.from.getTime() - Util.getDayLength() * 3),
			to: Util.formatDate(dateTime.to.getTime() + Util.getDayLength() * 10),
			timezone: dateTime.timezoneFrom,
			location: this.locationSelector.getTextValue()
		});
	}

	updatePlanner(params)
	{
		if (!params)
		{
			params = {};
		}
		//let currentEntryLocation = this.calendar.util.parseLocation(this.entry.location);

		this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
			data: {
				entry_id: this.entry.id || 0,
				codes: params.codes || [],
				date_from: params.from || '',
				date_to: params.to || '',
				timezone: params.timezone || '',
				location: params.location || '',
				//roomEventId: currentEntryLocation ? (currentEntryLocation.room_event_id || currentEntryLocation.mrevid ||
				// false) : false,
				entries: params.entrieIds || false,
				//add_cur_user_to_list: this.calendar.util.userIsOwner() ? 'Y' : 'N'
			}
		})
			.then(
				// Success
				function(response)
				{
					let
						i,
						attendees = [],
						attendeesIndex = {},
						updateAttendeesControl = false,
						entries = response.data.entries,
						accessibility = response.data.accessibility,
						showPlanner = !!(params.entries || (entries && entries.length > 0));

					if (Type.isArray(entries))
					{
						for (i = 0; i < entries.length; i++)
						{
							if (entries[i].type === 'user')
							{
								attendees.push({
									id: entries[i].id,
									name: entries[i].name,
									avatar: entries[i].avatar,
									smallAvatar: entries[i].smallAvatar || entries[i].avatar,
									url: entries[i].url
								});
								attendeesIndex[entries[i].id] = true;

								// if (!_this.attendeesIndex[response.entries[i].id])
								// 	updateAttendeesControl = true;
							}
						}
					}

					// if (!updateAttendeesControl)
					// {
					// 	for (let id in _this.attendeesIndex)
					// 	{
					// 		if (this.attendeesIndex.hasOwnProperty(id) && !attendeesIndex[id])
					// 		{
					// 			updateAttendeesControl = true;
					// 			break;
					// 		}
					// 	}
					// }

					// Show first time or refresh it state
					if (showPlanner)
					{
						let refreshParams = {};

						if (params.entries)
						{
							entries = params.entries;
							refreshParams.scaleFrom = params.from;
							refreshParams.scaleTo = params.to;
						}

						refreshParams.loadedDataFrom = params.from;
						refreshParams.loadedDataTo = params.to;

						refreshParams.data = {
							entries: entries,
							accessibility: accessibility
						};

						refreshParams.focusSelector = params.focusSelector === undefined ? false : params.focusSelector;
						this.refreshPlanner(refreshParams);
					}
				}.bind(this),
				// Failure
				function (response)
				{

				}.bind(this)
			);
	}

	refreshPlanner(params = {})
	{
		let dateTime = this.dateTimeControl.getValue();

		//this.plannerData = params.data;
		let
			from = dateTime.from,
			to = dateTime.to,
			config = {},
			scaleFrom, scaleTo;

		if (Type.isDate(from) && Type.isDate(to) &&
			from.getTime() <= to.getTime())
		{
			Dom.addClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
			if (!this.plannerIsShown() && params.show || params.focusSelector === undefined)
			{
				params.focusSelector = true;
			}

			if (dateTime.fullDay)
			{
				scaleFrom = params.scaleFrom || new Date(from.getTime() - Util.getDayLength() * 3);
				scaleTo = params.scaleTo || new Date(scaleFrom.getTime() + Util.getDayLength() * 10);

				config.scaleType = '1day';
				config.scaleDateFrom = scaleFrom;
				config.scaleDateTo = scaleTo;
				config.adjustCellWidth = false;
			}
			else
			{
				config.changeFromFullDay = {
					scaleType: '1hour',
					timelineCellWidth: 40
				};
				//config.shownScaleTimeFrom = parseInt(this.calendar.util.getWorkTime().start);
				//config.shownScaleTimeTo = parseInt(this.calendar.util.getWorkTime().end);
			}
			config.entriesListWidth = this.DOM.attendeesTitle.offsetWidth + 16;
			config.width = this.DOM.plannerWrap.offsetWidth;

			if (this.DOM.moreOuterWrap)
			{
				this.DOM.moreOuterWrap.style.paddingLeft = config.entriesListWidth + 'px';
			}

			// RRULE
			let RRULE = false;
			// if (this.DOM.rruleType.value !== 'NONE' && false)
			// {
			// 	RRULE = {
			// 		FREQ: this.RepeatSelect.value,
			// 		INTERVAL: this.RepeatCount.value,
			// 		UNTIL: this.RepeatDiapTo.value
			// 	};
			//
			// 	if (RRULE.UNTIL == EC_MESS.NoLimits)
			// 		RRULE.UNTIL = '';
			//
			// 	if (RRULE.FREQ == 'WEEKLY')
			// 	{
			// 		RRULE.WEEK_DAYS = [];
			// 		for (i = 0; i < 7; i++)
			// 		{
			// 			if (this.RepeatWeekDaysCh[i].checked)
			// 			{
			// 				RRULE.WEEK_DAYS.push(this.RepeatWeekDaysCh[i].value);
			// 			}
			// 		}
			//
			// 		if (!RRULE.WEEK_DAYS.length)
			// 		{
			// 			RRULE = false;
			// 		}
			// 	}
			// }

			//this.checkLocationAccessibility();
			this.BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
				{
					plannerId: this.plannerId,
					config: config,
					focusSelector: params.focusSelector,
					selector: {
						from: from,
						to: to,
						fullDay: dateTime.fullDay,
						RRULE: RRULE,
						animation: true,
						updateScaleLimits: true
					},
					data: params.data || false,
					loadedDataFrom: params.loadedDataFrom,
					loadedDataTo: params.loadedDataTo,
					show: true
				}
			]);
		}
	}

	plannerIsShown()
	{
		return this.DOM.plannerWrap && Dom.hasClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
	}


	keyHandler(e)
	{
		if((e.ctrlKey || e.metaKey) && !e.altKey && e.keyCode === Util.getKeyCode('enter'))
		{
			this.save();
		}
	}

	showError(errorList)
	{
		let errorText = '';

		if (Type.isArray(errorList))
		{
			errorList.forEach((error) => {
				if (error.code === "edit_entry_location_busy")
				{
					return Util.showFieldError(error.message, this.DOM.locationWrap, {clearTimeout: 10000});
				}
				errorText += error.message + "\n";
			});
		}

		if (errorText !== '')
		{
			alert(errorText);
		}
	}

	checkForSignificantChanges()
	{
		return true;
		let res = false;

		// Name
		if (!res && this.entry.name !== this.DOM.form.name.value)
			res = true;

		// Description
		if (!res && this.descriptionValue !== this.DOM.form.desc.value)
			res = true;

		// Location
		if (!res && this.entry.data.LOCATION !== this.DOM.form.lo_cation.value)
			res = true;

		// Date & time
		if (!res && this.entry.isFullDay() != this.DOM.form.skip_time.checked)
			res = true;

		if (!res)
		{
			let
				from = BX.parseDate(this.entry.data.DATE_FROM),
				to = new Date(from.getTime() + (this.entry.data.DT_LENGTH - (this.entry.isFullDay() ? 1 : 0)) * 1000);

			if (Math.abs(from.getTime() - this.fromDate.getTime()) > 1000
				|| Math.abs(to.getTime() - this.toDate.getTime()) > 1000)
				res = true;

			if (!res && !this.entry.isFullDay()
				&& (this.entry.data.TZ_FROM !== this.DOM.form.tz_from.value
					|| this.entry.data.TZ_TO !== this.DOM.form.tz_to.value))
			{
				res = true;
			}
		}

		// Attendees
		if (!res && this.plannerData && false)
		{
			let i, attendeesInd = {}, count = 0;
			if (this.entry.isMeeting())
			{
				let attendeeList = this.entry.getAttendees();


				for (i in attendeeList)
				{
					if (attendeeList.hasOwnProperty(i) && attendeeList[i]['ID'])
					{
						attendeesInd[attendeeList[i]['ID']] = true;
						count++
					}
				}
			}

			// Check if we have new attendees
			for (i in this.plannerData.entries)
			{
				if (this.plannerData.entries.hasOwnProperty(i) && this.plannerData.entries[i].type == 'user' && this.plannerData.entries[i].id)
				{
					if (attendeesInd[this.plannerData.entries[i].id])
					{
						attendeesInd[this.plannerData.entries[i].id] = '+';
					}
					else
					{
						res = true;
						break;
					}
				}
			}

			// Check if we have all old attendees
			if (!res && attendeesInd)
			{
				for (i in attendeesInd)
				{
					if (attendeesInd.hasOwnProperty(i) && attendeesInd[i] !== '+')
					{
						res = true;
						break;
					}
				}
			}
		}

		// Recurtion
		//if (!res && (this.oEvent.RRULE.FREQ != this.RepeatSelect.value))
		//	res = true;
		//
		//if (!res && (this.oEvent.RRULE.INTERVAL != this.RepeatCount.value))
		//	res = true;
		//
		//if (!res && this.oEvent.RRULE.FREQ == 'WEEKLY' && this.oEvent.RRULE.BYDAY)
		//{
		//	let BYDAY = [];
		//	for (i in this.oEvent.RRULE.BYDAY)
		//	{
		//		if (this.oEvent.RRULE.BYDAY.hasOwnProperty(i))
		//		{
		//			BYDAY.push(this.oEvent.RRULE.BYDAY[i]);
		//		}
		//	}
		//	if (BYDAY.join(',') != top.BX('event-rrule-byday' + this.id).value)
		//		res = true;
		//}

		return res;
	}
}