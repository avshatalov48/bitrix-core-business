import {BookingUtil, Dom, Loc, Event, Type, SelectInput} from "calendar.resourcebooking";
import {UserSelectorFieldEditControl} from "./controls/userselectorfieldeditcontrol";
import {ResourceSelectorFieldEditControl} from "./controls/resourceselectorfieldeditcontrol";
import {ResourcebookingUserfield} from "calendar.resourcebookinguserfield";
import {PlannerPopup} from "./controls/plannerpopup";

export class EditFieldController
{
	constructor(params)
	{
		this.params = params;
		this.plannerPopup = null;

		this.DOM = {
			outerWrap: BX(params.controlId),
			valueInputs: []
		};

		this.isNew = !this.params.value || !this.params.value.DATE_FROM;

		if (this.params.socnetDestination)
		{
			ResourcebookingUserfield.setSocnetDestination(this.params.socnetDestination);
		}
	}

	init()
	{
		this.buildUserfieldWrap();
		this.createEventHandlers();
		this.setControlValues();
	}

	buildUserfieldWrap()
	{
		this.buildDateControl();
		this.buildTimeControl();
		this.buildServiceControl();
		this.buildDurationControl();
		this.buildUserSelectorControl();
		this.buildResourceSelectorControl();
	}

	createEventHandlers()
	{
		Event.bind(this.DOM.outerWrap, 'click',this.showPlannerPopup.bind(this));
		Event.bind(this.DOM.fromInput, 'focus',this.showPlannerPopup.bind(this));
		Event.bind(this.DOM.durationInput, 'focus',this.showPlannerPopup.bind(this));

		setTimeout(function(){
			BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldSetValidator',
			[
				this.params.controlId,
				function(result)
				{
					if (!this.params.allowOverbooking && this.isOverbooked())
					{
						if (result && result.addError && BX.Crm && BX.Crm.EntityValidationError)
						{
							result.addError(BX.Crm.EntityValidationError.create({field: this}));
						}
					}
					return new Promise((resolve) => {
						resolve();
					});
				}.bind(this)
			]);
		}.bind(this), 100);

		setTimeout(this.onChangeValues.bind(this), 100);
	}

	setControlValues()
	{
		this.allValuesValue = null;

		let
			dateFrom,
			duration,
			defaultDuration = this.params.fullDay ? 1440 : 60, // One day or one hour as default
			dateTo;

		if (this.isNew)
		{
			let params = ResourcebookingUserfield.getParamsFromHash(this.params.userfieldId);
			if (params && params.length > 1)
			{
				dateFrom = BX.parseDate(params[0]);
				dateTo = BX.parseDate(params[1]);
				if (dateFrom && dateTo)
				{
					duration = Math.round(Math.max((dateTo.getTime() - dateFrom.getTime()) / 60000, 0));
				}
			}

			if (!dateFrom)
			{
				dateFrom = new Date();
				let
					roundMin = 30,
					r = (roundMin || 10) * 60 * 1000,
					timestamp = Math.ceil(dateFrom.getTime() / r) * r;
				dateFrom = new Date(timestamp);
			}
		}
		else
		{
			dateFrom = BX.parseDate(this.params.value.DATE_FROM);
			dateTo = BX.parseDate(this.params.value.DATE_TO);
			duration = Math.round(Math.max((dateTo.getTime() - dateFrom.getTime()) / 60000, 0));
		}

		if (!duration)
		{
			duration = defaultDuration;
		}

		this.DOM.fromInput.value = BookingUtil.formatDate(BookingUtil.getDateFormat(), dateFrom);
		if (this.DOM.timeFromInput)
		{
			this.DOM.timeFromInput.value = BookingUtil.formatDate(BookingUtil.getTimeFormatShort(), dateFrom);
		}

		if (this.durationList)
		{
			this.durationList.setValue(duration);
		}

		if (this.serviceList)
		{
			this.serviceList.setValue(this.params.value.SERVICE_NAME || '');
		}

		let selectedUsers = [];
		let selectedResources = [];
		if (this.params.value && Type.isArray(this.params.value.ENTRIES))
		{
			this.params.value.ENTRIES.forEach(function(entry)
			{
				if (entry.TYPE === 'user')
				{
					selectedUsers.push(parseInt(entry.RESOURCE_ID));
				}
				else
				{
					selectedResources.push({
						id: parseInt(entry.RESOURCE_ID),
						type: entry.TYPE
					});
				}
			});
		}

		if (this.resourceSelector)
		{
			this.resourceSelector.setValues(selectedResources, false);
		}

		if (this.userSelector)
		{
			this.userSelector.setValues(selectedUsers, false);
		}
	}

	buildDateControl()
	{
		this.DOM.dateTimeWrap = this.DOM.outerWrap.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-content-block-detail-wrap-flex"}}));

		this.DOM.dateWrap = this.DOM.dateTimeWrap
			.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
			.appendChild(Dom.create("div", {
				props: { className: "calendar-resourcebook-content-block-detail"},
				html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + Loc.getMessage('USER_TYPE_RESOURCE_DATE_LABEL') + '</span></div>'
			}));

		this.DOM.fromInput = this.DOM.dateWrap.appendChild(Dom.create('INPUT', {
			attrs: {
				value: '',
				placeholder: Loc.getMessage('USER_TYPE_RESOURCE_DATE_LABEL'),
				type: 'text'
			},
			events: {
				click: EditFieldController.showCalendarPicker,
				change: this.triggerUpdatePlanner.bind(this)
			},
			props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime'}
		}));

		this.DOM.emptyInput = this.DOM.dateWrap.appendChild(Dom.create('INPUT', {attrs: {value: '',type: 'text'}, props: {className: 'calendar-resbook-empty-input'}}));
	}

	buildTimeControl()
	{
		if (!this.params.fullDay)
		{
			this.DOM.timeWrap = this.DOM.dateTimeWrap
				.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
				.appendChild(Dom.create("div", {
					props: {className: "calendar-resourcebook-content-block-detail"},
					html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + Loc.getMessage('USER_TYPE_RESOURCE_TIME_LABEL') + '</span></div>'
				}));

			this.DOM.timeFromInput = this.DOM.timeWrap.appendChild(Dom.create('INPUT', {
				attrs: {
					value: '',
					placeholder: Loc.getMessage('USER_TYPE_RESOURCE_TIME_LABEL'),
					type: 'text'
				},
				style: {width: '100px'},
				props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'}
			}));

			this.fromTime = new SelectInput({
				input: this.DOM.timeFromInput,
				values: BookingUtil.getSimpleTimeList(),
				onChangeCallback: this.triggerUpdatePlanner.bind(this),
				onAfterMenuOpen: (ind, popupMenu) => {
					if (!ind && popupMenu)
					{
						const formatDatetime = BX.isAmPmMode()
							? Loc.getMessage("FORMAT_DATETIME").replace(':SS', '')
							: Loc.getMessage("FORMAT_DATETIME");
						const dateFrom = BookingUtil.parseDate(
							this.DOM.fromInput.value + ' ' + this.DOM.timeFromInput.value,
							false,
							false,
							formatDatetime
						);
						let i, menuItem;
						const nearestTimeValue = BookingUtil.adaptTimeValue({
							h: dateFrom.getHours(),
							m: dateFrom.getMinutes()
						});

						if (nearestTimeValue && nearestTimeValue.label)
						{
							for (i = 0; i < popupMenu.menuItems.length; i++)
							{
								menuItem = popupMenu.menuItems[i];
								if (menuItem
									&& nearestTimeValue.label === menuItem.text
									&& menuItem.layout)
								{
									popupMenu.layout.menuContainer.scrollTop = menuItem.layout.item.offsetTop - 2;
								}
							}
						}
					}
				}
			});
		}
	}

	buildServiceControl()
	{
		if (this.params.useServices && Type.isArray(this.params.serviceList) && this.params.serviceList.length > 0)
		{
			if (this.params.fullDay)
			{
				this.DOM.durationWrap = this.DOM.dateTimeWrap;
			}
			else
			{
				this.DOM.durationWrap = this.DOM.outerWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-content-block-detail-wrap-flex"}}));
			}

			this.DOM.servicesWrap = this.DOM.durationWrap
				.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
				.appendChild(Dom.create("div", {
					props: { className: "calendar-resourcebook-content-block-detail"},
					html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_LABEL') + '</span></div>'
				}));

			this.DOM.serviceInput = this.DOM.servicesWrap.appendChild(Dom.create('INPUT', {
				attrs: {
					value: '',
					//value: this.params.value.SERVICE_NAME || '',
					placeholder: Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_LABEL'),
					type: 'text'
				},
				style: {width: '200px'},
				props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'}
			}));

			let serviceListValues = [];
			this.params.serviceList.forEach(function(service)
			{
				if (service.name !== '')
				{
					serviceListValues.push({value: service.duration, label: service.name});
				}
			});

			if (this.isNew && serviceListValues.length >= 1)
			{
				this.DOM.serviceInput.value = serviceListValues[0].label;
				//duration = parseInt(serviceListValues[0].value);
			}

			this.serviceList = new SelectInput({
				input: this.DOM.serviceInput,
				values: serviceListValues,
				onChangeCallback: function(state)
				{
					if (Type.isPlainObject(state) && state.realValue)
					{
						this.durationList.setValue(parseInt(state.realValue));
						this.duration = BookingUtil.parseDuration(this.DOM.durationInput.value);
						this.triggerUpdatePlanner();
					}
				}.bind(this)
			});
		}
	}

	buildDurationControl()
	{
		if (!this.DOM.durationWrap)
		{
			this.DOM.durationWrap = this.DOM.dateTimeWrap;
		}

		// region Duration
		this.DOM.durationControlWrap = this.DOM.durationWrap
			.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
			.appendChild(Dom.create("div", {
				props: { className: "calendar-resourcebook-content-block-detail"},
				html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + Loc.getMessage('USER_TYPE_RESOURCE_DURATION_LABEL') + '</span></div>'
			}));

		this.DOM.durationInput = this.DOM.durationControlWrap.appendChild(Dom.create('INPUT', {
			attrs: {
				//value: duration,
				placeholder: Loc.getMessage('USER_TYPE_RESOURCE_DURATION_LABEL'),
				type: 'text'
			},
			style: {width: '90px'},
			props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'}
		}));

		//this.duration = parseInt(duration);
		this.durationList = new SelectInput({
			input: this.DOM.durationInput,
			values: BookingUtil.getDurationList(this.params.fullDay),
			//value: duration,
			onChangeCallback: function()
			{
				this.duration = BookingUtil.parseDuration(this.DOM.durationInput.value);
				this.triggerUpdatePlanner();
			}.bind(this)
		});
	}

	buildUserSelectorControl()
	{
		if (this.params.useUsers)
		{
			this.DOM.userSelectorWrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-resbook-users-selector-wrap'}
			}));

			this.DOM.userSelectorWrap = this.DOM.outerWrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-resourcebook-content-block-control-field'}}));
			
			let userSelectorTitle = Loc.getMessage('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME');
			this.DOM.userSelectorWrap
				.appendChild(Dom.create('DIV', {props: {className: 'calendar-resourcebook-content-block-title'}}))
				.appendChild(Dom.create('SPAN', {props: {className: 'calendar-resourcebook-content-block-title-text'}, text: userSelectorTitle}));
			this.DOM.userListWrap = this.DOM.userSelectorWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-control custom-field-item"}}));

			let itemsSelected = {};
			if (this.params.value && Type.isArray(this.params.value.ENTRIES))
			{
				this.params.value.ENTRIES.forEach(function(entry)
				{
					if (entry.TYPE === 'user')
					{
						const userKey = 'U' + parseInt(entry.RESOURCE_ID);
						itemsSelected[userKey] = 'users';
					}
				});
			}

			this.userSelector = new UserSelectorFieldEditControl({
				wrapNode: this.DOM.userListWrap,
				socnetDestination: ResourcebookingUserfield.getSocnetDestination(),
				addMessage: Loc.getMessage('USER_TYPE_RESOURCE_SELECT_USER'),
				checkLimitCallback: this.checkResourceCountLimit.bind(this),
				itemsSelected: itemsSelected,
			});

			BX.addCustomEvent('OnResourceBookDestinationAddNewItem', this.triggerUpdatePlanner.bind(this));
			BX.addCustomEvent('OnResourceBookDestinationUnselect', this.triggerUpdatePlanner.bind(this));
		}
	}

	buildResourceSelectorControl()
	{
		if (this.params.useResources)
		{
			this.DOM.resourcesWrap = this.DOM.outerWrap.appendChild(Dom.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add" }}));

			let resSelectorTitle = Loc.getMessage('USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME');
			this.DOM.resourcesWrap
				.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
				.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: resSelectorTitle}));
			this.DOM.resourcesListWrap = this.DOM.resourcesWrap.appendChild(Dom.create("div", {props: {className: "calendar-resourcebook-content-block-control custom-field-item"}}));

			this.resourceSelector = new ResourceSelectorFieldEditControl({
				outerWrap: this.DOM.resourcesWrap,
				blocksWrap: this.DOM.resourcesListWrap,
				values: [],
				resourceList: this.params.resourceList,
				onChangeCallback: this.triggerUpdatePlanner.bind(this),
				checkLimitCallback: this.checkResourceCountLimit.bind(this)
			});
		}
	}

	static showCalendarPicker(e)
	{
		let target = e.target || e.srcElement;
		BX.calendar({node: target, field: target, bTime: false});
		BX.focus(target);
	}

	onChangeValues()
	{
		this.duration = this.duration || BookingUtil.parseDuration(this.DOM.durationInput.value);
		const duration = this.duration * 60;
		let
			allValuesValue = '',
			formatDatetime = BX.isAmPmMode() ? Loc.getMessage("FORMAT_DATETIME").replace(':SS', '') : Loc.getMessage("FORMAT_DATETIME"),
			dateFrom,
			dateFromValue = '',
			serviceName = this.DOM.serviceInput ? this.DOM.serviceInput.value : '',
			entries = [];

		dateFrom = this.params.fullDay ? BookingUtil.parseDate(this.DOM.fromInput.value) : BookingUtil.parseDate(this.DOM.fromInput.value + ' ' + this.DOM.timeFromInput.value, false, false, formatDatetime);

		if (Type.isDate(dateFrom))
		{
			if (this.params.useResources)
			{
				entries = entries.concat(this.getSelectedResourceList());
			}

			if (this.params.useUsers)
			{
				entries = entries.concat(this.getSelectedUserList());
			}
			dateFromValue = BookingUtil.formatDate(BookingUtil.getDateTimeFormat(), dateFrom.getTime() / 1000);
		}

		// Clear inputs
		this.DOM.valueInputs.forEach(function(input){BX.remove(input);});
		this.DOM.valueInputs = [];

		entries.forEach(function(entry)
		{
			let value = entry.type + '|' + entry.id + '|' + dateFromValue + '|' + duration + '|' + serviceName;
			allValuesValue += value + '#';

			this.DOM.valueInputs.push(this.DOM.outerWrap.appendChild(
				Dom.create('INPUT', {
					attrs:{
						name: this.params.inputName,
						value: value,
						type: 'hidden'
					}})));
		}, this);


		if (!entries.length)
		{
			this.DOM.valueInputs.push(this.DOM.outerWrap.appendChild(
				Dom.create('INPUT', {
					attrs:{
						name: this.params.inputName,
						value: 'empty',
						type: 'hidden'
					}})));
		}

		if (this.allValuesValue !== null && this.allValuesValue !== allValuesValue)
		{
			BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldExternalChanged', [this.params.controlId]);
			BX.fireEvent(this.DOM.emptyInput, 'change');
		}
		this.allValuesValue = allValuesValue;
	}

	showPlannerPopup()
	{
		let currentEventList = [];
		if (this.params.value && Type.isArray(this.params.value.ENTRIES))
		{
			this.params.value.ENTRIES.forEach(function(entry)
			{
				currentEventList.push(entry.EVENT_ID);
			});
		}

		if (Type.isNull(this.plannerPopup))
		{
			this.plannerPopup = new PlannerPopup();
		}

		this.plannerPopup.show({
			plannerId: this.params.plannerId,
			bindNode: this.DOM.outerWrap,
			plannerConfig: this.getPlannerConfig(),
			selector: this.getSelectorData(),
			selectorOnChangeCallback: this.plannerSelectorOnChange.bind(this),
			selectEntriesOnChangeCallback: this.plannerSelectedEntriesOnChange.bind(this),
			checkSelectorStatusCallback: this.checkSelectorStatusCallback.bind(this),
			currentEventList: currentEventList
		});

		this.triggerUpdatePlanner();
	}

	triggerUpdatePlanner()
	{
		if (!Type.isNull(this.plannerPopup)
			&& this.plannerPopup.plannerId === this.params.plannerId
			&& this.plannerPopup.isShown())
		{
			this.plannerPopup.update({
				plannerId: this.params.plannerId,
				plannerConfig: this.getPlannerConfig(),
				selector: this.getSelectorData(),
				resourceList: this.getResourceList(),
				selectedResources: this.resourceSelector ? this.resourceSelector.getSelectedValues() : false,
				userList: this.getUserList(),
				selectedUsers: this.userSelector ? this.userSelector.getSelectedValues() : false
			},
			true);
		}

		this.onChangeValues();
	}

	getPlannerConfig()
	{
		if (!this.params.plannerConfig)
		{
			this.params.plannerConfig = {
				id: this.params.plannerId,
				selectEntriesMode: true,
				scaleLimitOffsetLeft: 2,
				scaleLimitOffsetRight: 2,
				maxTimelineSize: 300,
				minEntryRows: 300,
				entriesListWidth: 120,
				timelineCellWidth: 49,
				minWidth: 300,
				accuracy: 300,
				workTime: [parseInt(this.params.workTime[0]), parseInt(this.params.workTime[1])]
			};
		}

		this.params.plannerConfig.clickSelectorScaleAccuracy = Math.max((this.duration * 60) || 300, 3600);

		return this.params.plannerConfig;
	}

	plannerSelectorOnChange(params)
	{
		if (params.plannerId === this.params.plannerId
			&& Type.isDate(params.dateFrom)
			&& Type.isDate(params.dateTo)
		)
		{
			let
				dateFrom = params.dateFrom,
				dateTo = params.dateTo;

			this.DOM.fromInput.value =  BookingUtil.formatDate(BookingUtil.getDateFormat(), dateFrom);

			if (this.DOM.timeFromInput)
			{
				this.DOM.timeFromInput.value = BookingUtil.formatDate(BookingUtil.getTimeFormatShort(), dateFrom);
			}

			// Duration in minutes
			this.duration = (dateTo.getTime() - dateFrom.getTime() + (this.params.fullDay ? BookingUtil.getDayLength() : 0)) / 60000;
			this.duration = Math.round(Math.max(this.duration, 0));
			this.durationList.setValue(this.duration);

			this.onChangeValues();
		}
	}

	plannerSelectedEntriesOnChange(params)
	{
		if (params.plannerId === this.params.plannerId && Type.isArray(params.entries))
		{
			let
				selectedResources = [],
				selectedUsers = [];

			params.entries.forEach(function(entry)
			{
				if (entry.selected)
				{
					if (entry.type === 'user')
					{
						selectedUsers.push(entry.id);
					}
					else
					{
						selectedResources.push({
							id: entry.id,
							type: entry.type
						});
					}
				}
			});

			if (this.resourceSelector)
			{
				this.resourceSelector.setValues(selectedResources, false);
			}
			if (this.userSelector)
			{
				this.userSelector.setValues(selectedUsers, false);
			}

			this.onChangeValues();
		}
	}

	checkSelectorStatusCallback(params)
	{
		if (params.plannerId === this.params.plannerId && !this.params.allowOverbooking)
		{
			let errorClass = 'calendar-resbook-error';
			this.overbooked = params.status === 'busy';

			if (this.overbooked)
			{
				if (!this.DOM.errorNode)
				{
					this.DOM.errorNode = this.DOM.dateTimeWrap.appendChild(Dom.create("div", {
						props: {className: "calendar-resbook-content-error-text"},
						text: Loc.getMessage('USER_TYPE_RESOURCE_BOOKED_ERROR')
					}));
				}

				if (this.DOM.fromInput)
				{
					BX.addClass(this.DOM.fromInput, errorClass);
				}
				if (this.DOM.timeFromInput)
				{
					BX.addClass(this.DOM.timeFromInput, errorClass);
				}
				setTimeout(BX.delegate(function(){BX.focus(this.DOM.fromInput)}, this), 50);
			}
			else
			{
				if (this.DOM.errorNode)
				{
					BX.remove(this.DOM.errorNode);
					this.DOM.errorNode = null;
				}

				if (this.DOM.fromInput)
				{
					BX.removeClass(this.DOM.fromInput, errorClass);
				}
				if (this.DOM.timeFromInput)
				{
					BX.removeClass(this.DOM.timeFromInput, errorClass);
				}
			}
		}
	}

	getSelectorData()
	{
		let
			formatDatetime = BX.isAmPmMode() ? Loc.getMessage("FORMAT_DATETIME").replace(':SS', '') : Loc.getMessage("FORMAT_DATETIME"),
			selector, dateTo,
			duration = this.duration,
			dateFrom = BookingUtil.parseDate(this.DOM.fromInput.value + (this.DOM.timeFromInput ? ' ' + this.DOM.timeFromInput.value : ''), false, false, formatDatetime);

		if (!duration)
		{
			duration = this.params.fullDay ? 1440 : 60;
		}

		if (!Type.isDate(dateFrom))
		{
			dateFrom = new Date();
		}

		dateTo = new Date(dateFrom.getTime() + duration * 60000 - (this.params.fullDay ? BookingUtil.getDayLength() : 0));

		selector = {
			from: dateFrom,
			to: dateTo,
			fullDay: this.params.fullDay,
			updateScaleLimits: true
		};

		return selector;
	}

	getResourceList()
	{
		let entries = [];
		if (this.resourceSelector)
		{
			this.resourceSelector.getValues().forEach(function(value)
			{
				entries.push(
					{
						id: parseInt(value.id),
						type : value.type,
						name : value.title
					}
				);
			});
		}

		return entries;
	}
	getSelectedResourceList()
	{
		let entries = [];
		if (this.resourceSelector)
		{
			this.resourceSelector.getSelectedValues().forEach(function(value)
			{
				entries.push(
					{
						id: parseInt(value.id),
						type : value.type,
						name : value.title
					}
				);
			});
		}

		return entries;
	}

	getUserList()
	{
		let entries = [], index = {}, userId;
		if (this.userSelector)
		{
			if (Type.isArray(this.params.userList))
			{
				this.params.userList.forEach(function(userId){
					if (!index[userId])
					{
						entries.push({id: userId, type : 'user'});
						index[userId] = true;
					}
				});
			}

			this.userSelector.getAttendeesCodesList().forEach(function(code)
			{
				if (code.substr(0, 1) === 'U')
				{
					userId = parseInt(code.substr(1));
					if (!index[userId])
					{
						entries.push({id: userId, type : 'user'});
						index[userId] = true;
					}
				}
			});
		}

		return entries;
	}

	getSelectedUserList()
	{
		let entries = [];
		if (this.userSelector)
		{
			this.userSelector.getAttendeesCodesList().forEach(function(code)
			{
				if (code.substr(0, 1) === 'U')
				{
					entries.push(
						{
							id: parseInt(code.substr(1)),
							type : 'user'
						}
					);
				}
			});
		}
		return entries;
	}

	checkResourceCountLimit()
	{
		return this.params.resourceLimit <= 0 || this.getTotalResourceCount() <= this.params.resourceLimit;
	}

	getTotalResourceCount()
	{
		let result = 0;
		if (this.params.useResources && this.resourceSelector)
		{
			result += this.resourceSelector.getValues().length;
		}

		if (this.params.useUsers)
		{
			result += this.getSelectedUserList().length;
		}

		return result;
	}

	isOverbooked()
	{
		return this.overbooked;
	}
}
