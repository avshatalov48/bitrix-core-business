import {Type, Loc, Dom, Tag, Text, BookingUtil} from "./resourcebooking";
import {EventEmitter, BaseEvent} from 'main.core.events';
import {UserSelector} from "./controls/userselector";
import {ResourceSelector} from "./controls/resourceselector";
import {ServiceSelector} from "./controls/serviceselector";
import {DurationSelector} from "./controls/durationselector";
import {DateSelector} from "./controls/dateselector";
import {TimeSelector} from "./controls/timeselector";
import {StatusInformer} from "./controls/statusinformer";

export class LiveFieldController extends EventEmitter
{
	constructor(params)
	{
		super(params);
		this.setEventNamespace('BX.Calendar.LiveFieldController');
		this.params = params;
		this.actionAgent = params.actionAgent || BX.ajax.runAction;
		this.timeFrom = params.timeFrom || 7;
		this.timeTo = params.timeTo || 20;
		this.inputName = params.field.name + '[]';
		this.DATE_FORMAT = BookingUtil.getDateFormat();
		this.DATETIME_FORMAT = BookingUtil.getDateTimeFormat();
		this.userIndex = null;
		this.timezoneOffset = null;
		this.timezoneOffsetLabel = null;
		this.userFieldParams = null;
		this.loadedDates = [];

		this.externalSiteContext = Type.isFunction(params.actionAgent);

		this.accessibility = {
			user : {},
			resource: {}
		};
		this.busySlotMatrix = {
			user : {},
			resource: {}
		};

		this.DOM = {
			wrap: this.params.wrap,
			valueInputs: []
		};
	}

	init()
	{
		const settingsData = this.getSettingsData();
		if (!settingsData.users || !settingsData.resources)
		{
			throw new Error('Can\'t init resourcebooking field, because \'settings_data\' parameter is not provided or has incorrect structure');
			return;
		}
		this.scale = parseInt(settingsData.time && settingsData.time.scale ? settingsData.time.scale : 60, 10);

		this.DOM.outerWrap = this.DOM.wrap.appendChild(Tag.render`<div class="calendar-resbook-webform-wrapper"></div>`);

		this.showMainLoader();
		this.requireFormData().then(()=>{
			this.hideMainLoader();
			this.buildFormControls();
			this.onChangeValues();
		});
	}

	check()
	{
		let result = true;

		if (this.usersDisplayed() && !this.getSelectedUser())
		{
			this.userControl.showWarning();
			result = false;
		}

		if (result && this.resourcesDisplayed() && !this.getSelectedResources())
		{
			this.resourceControl.showWarning();
			result = false;
		}

		if (result && !this.getCurrentDuration())
		{
			if (this.durationControl)
			{
				this.durationControl.showWarning();
			}
			else if (this.serviceControl)
			{
				this.serviceControl.showWarning();
			}
			result = false;
		}

		if (result
			&& (
				!this.dateControl.getValue()
				|| this.statusControl.isErrorSet()
			)
		)
		{
			this.dateControl.showWarning();
			result = false;
		}

		if (result && this.timeSelectorDisplayed() && !this.timeControl.getValue())
		{
			this.timeControl.showWarning();
			result = false;
		}

		return result;
	}

	buildFormControls()
	{
		this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-inner"></div>`);
		this.DOM.inputsWrap = this.DOM.innerWrap.appendChild(Tag.render`<div></div>`);

		if (!this.getFieldParams())
		{
			this.statusControl = new StatusInformer({
				outerWrap: this.DOM.innerWrap
			});
			this.statusControl.refresh({});
			this.statusControl.setError('[UF_NOT_FOUND] ' + Loc.getMessage('WEBF_RES_BOOKING_UF_WARNING'));
		}
		else
		{
			if (this.externalSiteContext && BX.ZIndexManager)
			{
				const stack = BX.ZIndexManager.getOrAddStack(document.body);
				stack.baseIndex = 100000;
				stack.sort();
			}

			this.preparaAutoSelectValues();
			this.displayUsersControl();
			this.displayResourcesControl();
			this.displayServicesControl();
			this.displayDurationControl();
			this.displayDateTimeControl();

			if (this.selectedUserId || this.selectedResourceId)
			{
				this.refreshControlsState();
			}
		}
	}

	refreshControlsState()
	{
		if (this.selectorCanBeShown('resources')
			&& this.resourceControl
			&& !this.resourceControl.isShown())
		{
			this.resourceControl.display();
		}

		// Show services
		if (this.selectorCanBeShown('services')
			&& this.serviceControl
			&& !this.serviceControl.isShown())
		{
			this.serviceControl.display();
		}

		// Show duration
		if (this.selectorCanBeShown('duration')
			&& this.durationControl
			&& !this.durationControl.isShown())
		{
			this.durationControl.display();
		}

		let settingsData = this.getSettingsData();
		// Show date & time control
		if (this.selectorCanBeShown('date') && this.dateControl)
		{
			if (this.dateControl.isShown())
			{
				this.dateControl.refresh(
					settingsData.date,
					{
						availableDateIndex: this.getAvailableDateIndex({
							resources: this.getSelectedResources(),
							user: this.getSelectedUser(),
							duration: this.getCurrentDuration()
						})
					}
				);

				if (this.timeControl)
				{
					this.timeControl.refresh(
						settingsData.time,
						{
							slotIndex: this.getSlotIndex({date: this.dateControl.getValue()}),
							currentDate: this.dateControl.getValue()
						});
				}
			}
			else
			{
				let startValue;
				if (settingsData.date.start === 'free')
				{
					startValue = this.getFreeDate({
						resources: this.getSelectedResources(),
						user: this.getSelectedUser(),
						duration: this.getCurrentDuration()
					});
				}
				else
				{
					startValue = new Date();
				}

				this.dateControl.display({
					selectedValue: startValue,
					availableDateIndex: this.getAvailableDateIndex({
						resources: this.getSelectedResources(),
						user: this.getSelectedUser(),
						duration: this.getCurrentDuration()
					})
				});
			}
		}

		this.updateStatusControl();
		this.onChangeValues();
		BookingUtil.fireCustomEvent(window, 'crmWebFormFireResize');
	}

	onChangeValues()
	{
		let
			allValuesValue = [],
			dateFromValue = '',
			dateFrom = this.getCurrentDate(),
			duration = this.getCurrentDuration() * 60,// Duration in minutes
			serviceName = this.getCurrentServiceName(),
			entries = [];

		// Clear inputs
		Dom.clean(this.DOM.inputsWrap);

		this.DOM.valueInputs = [];

		if (Type.isDate(dateFrom) && !this.statusControl.isErrorSet())
		{
			let resources = this.getSelectedResources();
			if (Type.isArray(resources))
			{
				resources.forEach(function(resourceId)
				{
					entries = entries.concat({type: 'resource', id: resourceId});
				});
			}

			let selectedUser = this.getSelectedUser();
			if (selectedUser)
			{
				entries = entries.concat({type: 'user', id: selectedUser});
			}

			dateFromValue = BookingUtil.formatDate(this.DATETIME_FORMAT, dateFrom.getTime() / 1000);

			entries.forEach(function(entry)
			{
				let value = entry.type + '|' + entry.id + '|' + dateFromValue + '|' + duration + '|' + serviceName;
				allValuesValue.push(value);

				this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(
					Tag.render`
					<input 
						name="${Text.encode(this.inputName)}"
						value="${Text.encode(value)}" 
						type="hidden"
						>
					`
				));
			}, this);
		}

		if (!entries.length)
		{
			allValuesValue.push('empty');
			this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(
				Tag.render`
					<input 
						name="${Text.encode(this.inputName)}"
						value="empty" 
						type="hidden"
						>
					`
			));
		}

		this.emit('change', allValuesValue);
	}

	showMainLoader()
	{
		if (this.DOM.wrap)
		{
			this.hideMainLoader();
			let loaderWrap = Tag.render`<div class="calendar-resbook-webform-wrapper-loader-wrap"></div>`;
			loaderWrap.appendChild(BookingUtil.getLoader(160));
			this.DOM.mainLoader = this.DOM.outerWrap.appendChild(loaderWrap);
		}
	}

	hideMainLoader()
	{
		Dom.remove(this.DOM.mainLoader);
	}

	showStatusLoader()
	{
		this.showMainLoader();
	}

	hideStatusLoader()
	{
		this.hideMainLoader();
	}

	requestAccessibilityData(params)
	{
		if (!this.requestedFormData)
		{
			this.showStatusLoader();

			this.requestedFormData = true;
			let formDataParams = {
				from: params.date
			};

			this.requireFormData(formDataParams).then(() => {
				this.hideStatusLoader();
				this.refreshControlsState();
				this.dateControl.refreshCurrentValue();
				this.onChangeValues();
				this.requestedFormData = false;
			});
		}
	}

	requireFormData(params)
	{
		params = Type.isPlainObject(params) ? params : {};

		return new Promise((resolve, reject) => {
			let
				data = {
					settingsData: this.getSettingsData() || null
				};

			if (!this.userFieldParams)
			{
				data.fieldName = this.params.field.entity_field_name;
			}

			let
				dateFrom = Type.isDate(params.from) ? params.from : new Date(),
				dateTo;

			if (Type.isDate(params.to))
			{
				dateTo = params.to;
			}
			else
			{
				dateTo = new Date(dateFrom.getTime());
				dateTo.setDate(dateFrom.getDate() + 60);
			}

			data.from = BookingUtil.formatDate(this.DATE_FORMAT, dateFrom);
			data.to = BookingUtil.formatDate(this.DATE_FORMAT, dateTo);

			this.setLoadedDataLimits(dateFrom, dateTo);

			this.actionAgent('calendar.api.resourcebookingajax.getfillformdata', {
				data: data
			}).then((response) => {
					if (!Type.isPlainObject(response) || !response.data)
					{
						resolve(response);
					}
					else
					{
						if (Type.isNumber(response.data.timezoneOffset))
						{
							this.timezoneOffset = response.data.timezoneOffset;
							this.timezoneOffsetLabel = response.data.timezoneOffsetLabel;
						}

						if (response.data.workTimeStart !== undefined && response.data.workTimeEnd !== undefined)
						{
							this.timeFrom = parseInt(response.data.workTimeStart);
							this.timeTo = parseInt(response.data.workTimeEnd);
						}

						if (response.data.fieldSettings)
						{
							this.userFieldParams = response.data.fieldSettings;
						}
						if (response.data.userIndex)
						{
							this.userIndex = response.data.userIndex;
						}

						this.handleAccessibilityData(response.data.usersAccessibility, 'user');
						this.handleAccessibilityData(response.data.resourcesAccessibility, 'resource');

						resolve(response.data);
					}
				},
				(response) => {
					resolve(response);
				});
		});
	}

	setLoadedDataLimits(from, to)
	{
		this.loadedDataFrom = Type.isDate(from) ? from : BookingUtil.parseDate(from);
		this.loadedDataTo = Type.isDate(to) ? to : BookingUtil.parseDate(to);

		this.loadedDates = this.loadedDates || [];
		this.loadedDatesIndex = this.loadedDatesIndex || {};

		let
			dateKey,
			date = new Date(this.loadedDataFrom.getTime());

		while (true)
		{
			dateKey = BookingUtil.formatDate(this.DATE_FORMAT, date);
			this.loadedDatesIndex[dateKey] = this.loadedDates.length;
			this.loadedDates.push({
				key: BookingUtil.formatDate(this.DATE_FORMAT, date),
				slots: {},
				slotsCount: {}
			});
			date.setDate(date.getDate() + 1);

			if (date.getTime() > this.loadedDataTo.getTime())
			{
				break;
			}
		}
	}

	fillDataIndex(date, time, entityType, entityId)
	{
		let dateIndex = this.loadedDatesIndex[date];
		if (this.loadedDates[dateIndex])
		{
			if (!this.loadedDates[dateIndex].slots[time])
			{
				this.loadedDates[dateIndex].slots[time] = {};
			}
			if (this.loadedDates[dateIndex].slotsCount[entityType + entityId] === undefined)
			{
				this.loadedDates[dateIndex].slotsCount[entityType + entityId] = 0;
			}
			this.loadedDates[dateIndex].slots[time][entityType + entityId] = true;
			this.loadedDates[dateIndex].slotsCount[entityType + entityId]++;
		}
	}

	handleAccessibilityData(data, entityType)
	{
		if (Type.isPlainObject(data) && (entityType === 'user' || entityType === 'resource'))
		{
			// For each entry which has accessibility entries
			for (let entityId in data)
			{
				if (data.hasOwnProperty(entityId))
				{
					data[entityId].forEach(function(entry)
					{
						if (!entry.from)
						{
							entry.from = BookingUtil.parseDate(entry.dateFrom);
							if (entry.from)
							{
								entry.from.setSeconds(0,0);
								entry.fromTimestamp = entry.from.getTime();
							}
						}

						if (!entry.to)
						{
							entry.to = BookingUtil.parseDate(entry.dateTo);
							if (entry.to)
							{
								if (entry.fullDay)
								{
									entry.to.setHours(23, 59, 0, 0);
								}
								else
								{
									entry.to.setSeconds(0, 0);
								}
								entry.toTimestamp = entry.to.getTime();
							}
						}

						if (entry.from && entry.to)
						{
							this.fillBusySlotMatrix(entry, entityType, entityId);
						}
					}, this);
				}
			}
			this.accessibility[entityType] = BookingUtil.mergeEx(this.accessibility[entityType], data);
		}
	}

	fillBusySlotMatrix(entry, entityType, entityId)
	{
		if (!this.busySlotMatrix[entityType][entityId])
		{
			this.busySlotMatrix[entityType][entityId] = {};
		}

		let
			fromDate = new Date(entry.from.getTime()),
			dateKey = BookingUtil.formatDate(this.DATE_FORMAT, fromDate),
			dateToKey = BookingUtil.formatDate(this.DATE_FORMAT, entry.to),
			timeValueFrom = fromDate.getHours() * 60 + fromDate.getMinutes(),
			duration = Math.round((entry.toTimestamp - entry.fromTimestamp) / 60000), // in minutes
			timeValueTo = timeValueFrom + duration,
			slots = this.getTimeSlots(),
			count = 0,
			i;

		if (duration > 0)
		{
			while (true)
			{
				if (!this.busySlotMatrix[entityType][entityId][dateKey])
				{
					this.busySlotMatrix[entityType][entityId][dateKey] = {};
				}

				for (i = 0; i < slots.length; i++)
				{
					if (timeValueFrom < (slots[i].time + this.scale) && timeValueTo > slots[i].time)
					{
						this.busySlotMatrix[entityType][entityId][dateKey][slots[i].time] = true;
						this.fillDataIndex(dateKey, slots[i].time, entityType, entityId);
					}
				}

				if (dateKey === dateToKey)
				{
					break;
				}
				else
				{
					fromDate.setDate(fromDate.getDate() + 1);
					dateKey = BookingUtil.formatDate(this.DATE_FORMAT, fromDate);
					timeValueFrom = 0;
					if (dateKey === dateToKey)
					{
						timeValueTo = entry.to.getHours() * 60 + entry.to.getMinutes();
					}
					else
					{
						timeValueTo = 1440; // end of the day - 24 hours
					}
				}

				count++;
				if (count > 10000) // emergency exit
				{
					break;
				}
			}
		}
	}

	getCaption()
	{
		return this.params.field.caption;
	}

	getSettingsData()
	{
		return this.params.field.settings_data || {};
	}

	getUserIndex()
	{
		return this.userIndex;
	}

	getFieldParams()
	{
		return this.userFieldParams;
	}

	getSettings()
	{
		return {
			caption: this.getCaption(),
			data: this.getSettingsData()
		};
	}

	isUserSelectorInAutoMode()
	{
		return this.usersDisplayed() && this.getSettingsData().users.show === "N";
	}

	isResourceSelectorInAutoMode()
	{
		return this.resourcesDisplayed() && this.getSettingsData().resources.show === "N";
	}

	autoAdjustUserSelector()
	{
		let
			currentDate = this.dateControl.getValue(),
			timeValue = this.timeControl.getValue();

		if (Type.isDate(currentDate) && timeValue)
		{
			let i, loadedDate = this.loadedDates[this.loadedDatesIndex[BookingUtil.formatDate(this.DATE_FORMAT, currentDate)]];
			if (loadedDate.slots[timeValue])
			{
				for (i = 0; i < this.userControl.values.length; i++)
				{
					if (!loadedDate.slots[timeValue]['user' + this.userControl.values[i]])
					{
						this.userControl.setSelectedUser(this.userControl.values[i]);
						break;
					}
				}
			}
		}
	}

	autoAdjustResourceSelector()
	{
		let
			currentDate = this.dateControl.getValue(),
			timeValue = this.timeControl.getValue();

		if (Type.isDate(currentDate) && timeValue)
		{
			let
				i, id,
				loadedDate = this.loadedDates[this.loadedDatesIndex[BookingUtil.formatDate(this.DATE_FORMAT, currentDate)]];

			if (loadedDate.slots[timeValue])
			{
				for (i = 0; i < this.resourceControl.resourceList.length; i++)
				{
					id = parseInt(this.resourceControl.resourceList[i].id);
					if (!loadedDate.slots[timeValue]['resource' + id])
					{
						this.resourceControl.setSelectedResource(id);
						break;
					}
				}
			}
		}
	}

	preparaAutoSelectValues ()
	{
		let
			settingsData = this.getSettingsData(),
			autoSelectUser = this.usersDisplayed() && (settingsData.users.defaultMode === 'auto' || settingsData.users.show === "N"),
			autoSelectResource = this.resourcesDisplayed() && (settingsData.resources.defaultMode === 'auto' || settingsData.resources.show === "N"),
			autoSelectDate = settingsData.date.start === 'free',
			maxStepsAuto = 60,
			date, i;

		this.selectedUserId = false;
		this.selectedResourceId = false;

		date = new Date();
		// Walk through each date searching for free space
		for (i = 0; i <= maxStepsAuto; i++)
		{
			this.getFreeEntitiesForDate(date, {
				autoSelectUser: autoSelectUser,
				autoSelectResource: autoSelectResource,
				slotsAmount: this.getDefaultDurationSlotsAmount()
			});

			if ((this.selectedUserId || !autoSelectUser)
				&&
				(this.selectedResourceId || !autoSelectResource))
			{
				break;
			}

			if (!autoSelectDate)
			{
				break;
			}
			date.setDate(date.getDate() + 1);
		}
	}

	getFreeEntitiesForDate(date, params)
	{
		let
			settingsData = this.getSettingsData(),
			slotsAmount = params.slotsAmount || 1,
			i, userList, resList;

		if (params.autoSelectUser)
		{
			userList = this.getUsersValue();
			for (i = 0; i < userList.length; i++)
			{
				if (this.checkSlotsForDate(date, slotsAmount, {
					user: parseInt(userList[i])
				}))
				{
					this.selectedUserId = parseInt(userList[i]);
					break;
				}
			}
		}

		if (params.autoSelectResource)
		{
			resList = this.getResourceValue();
			for (i = 0; i < resList.length; i++)
			{
				if (this.checkSlotsForDate(date, slotsAmount, {
					resources: [parseInt(resList[i])],
					user: this.selectedUserId || null
				}))
				{
					this.selectedResourceId = parseInt(resList[i]);
					break;
				}
			}
		}
	}

	displayUsersControl()
	{
		if (this.usersDisplayed())
		{
			this.userControl = new UserSelector({
				outerWrap: this.DOM.innerWrap,
				data: this.getSettingsData().users,
				userIndex: this.getUserIndex(),
				previewMode: false,
				autoSelectDefaultValue: this.selectedUserId,
				changeValueCallback: function(userId)
				{
					this.emit('BX.Calendar.Resourcebooking.LiveFieldController:userChanged', new BaseEvent({data: {userId: userId}}));
					this.refreshControlsState();
				}.bind(this)
			});
			this.userControl.display();
		}
	}

	displayResourcesControl()
	{
		let
			valueIndex = {},
			dataValue = [],
			fieldParams = this.getFieldParams(),
			settingsData = this.getSettingsData();

		if (this.resourcesDisplayed())
		{
			this.getResourceValue().forEach(function(id)
			{
				id = parseInt(id);
				if (id > 0)
				{
					valueIndex[id] = true;
					dataValue.push(id);
				}
			});

			let resourceList = [];
			fieldParams.SELECTED_RESOURCES.forEach(function(res)
			{
				res.id = parseInt(res.id);
				if (valueIndex[res.id])
				{
					resourceList.push(res);
				}
			}, this);

			this.resourceControl = new ResourceSelector({
				outerWrap: this.DOM.innerWrap,
				data: {
					show: settingsData.resources.show,
					defaultMode: settingsData.resources.defaultMode,
					label: settingsData.resources.label,
					multiple: settingsData.resources.multiple,
					value: settingsData.resources.value
				},
				resourceList: resourceList,
				autoSelectDefaultValue: this.selectedResourceId,
				changeValueCallback: function()
				{
					this.emit('BX.Calendar.Resourcebooking.LiveFieldController:resourceChanged');
					this.refreshControlsState();
				}.bind(this)
			});

			if (this.selectorCanBeShown('resources'))
			{
				this.resourceControl.display();
			}
		}
	}

	displayServicesControl()
	{
		let
			fieldParams = this.getFieldParams(),
			settingsData = this.getSettingsData();

		if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value)
		{
			let dataValueRaw = this.getServicesValue();

			this.serviceControl = new ServiceSelector({
				outerWrap: this.DOM.innerWrap,
				data: settingsData.services,
				serviceList: fieldParams.SERVICE_LIST,
				selectedValue: dataValueRaw.length > 0 ? dataValueRaw[0] : null,
				changeValueCallback: function()
				{
					this.emit('BX.Calendar.Resourcebooking.LiveFieldController:serviceChanged');
					this.refreshControlsState();
				}.bind(this)
			});

			if (this.selectorCanBeShown('services'))
			{
				this.serviceControl.display();
			}
		}
	}

	displayDurationControl()
	{
		let
			fieldParams = this.getFieldParams(),
			settingsData = this.getSettingsData();

		if (!this.serviceControl)
		{
			this.durationControl = new DurationSelector({
				outerWrap: this.DOM.innerWrap,
				data: settingsData.duration,
				fullDay: fieldParams.FULL_DAY === 'Y',
				changeValueCallback: function()
				{
					this.emit('BX.Calendar.Resourcebooking.LiveFieldController:durationChanged');
					this.refreshControlsState();
				}.bind(this)
			});

			if (this.selectorCanBeShown('duration'))
			{
				this.durationControl.display();
			}
		}
	}

	displayDateTimeControl()
	{
		let
			timezone = false,
			startValue = null,
			settingsData = this.getSettingsData(),
			fieldParams = this.getFieldParams();

		this.dateControl = new DateSelector({
			outerWrap: this.DOM.innerWrap,
			data: settingsData.date,
			previewMode: false,
			allowOverbooking: fieldParams.ALLOW_OVERBOOKING === "Y",
			changeValueCallback: this.handleDateChanging.bind(this),
			requestDataCallback: this.requestAccessibilityData.bind(this)
		});

		if (this.timeSelectorDisplayed())
		{
			if (fieldParams.USE_USER_TIMEZONE === 'N')
			{
				let userTimezoneOffset = -(new Date).getTimezoneOffset()*60;
				if (userTimezoneOffset !== this.timezoneOffset)
				{
					timezone = fieldParams.TIMEZONE;
				}
			}

			this.timeControl = new TimeSelector({
				outerWrap: this.DOM.innerWrap,
				data: settingsData.time,
				previewMode: false,
				changeValueCallback: this.handleSelectedDateTimeChanging.bind(this),
				timeFrom: this.timeFrom,
				timeTo: this.timeTo,
				timezone: timezone,
				timezoneOffset: this.timezoneOffset,
				timezoneOffsetLabel: this.timezoneOffsetLabel
			});
		}

		this.statusControl = new StatusInformer({
			outerWrap: this.DOM.innerWrap,
			timezone: timezone,
			timezoneOffsetLabel: this.timezoneOffsetLabel
		});

		if (this.selectorCanBeShown('date'))
		{
			this.statusControl.show();
			if (settingsData.date.start === 'free')
			{
				startValue = this.getFreeDate({
					resources: this.getSelectedResources(),
					user: this.getSelectedUser(),
					duration: this.getCurrentDuration()
				});
			}

			this.dateControl.display({
				selectedValue: startValue
			});

			if (this.timeControl && !this.timeControl.isShown())
			{
				this.timeControl.display();
			}
		}
		else
		{
			this.statusControl.hide();
		}
	}

	handleDateChanging(date, realDate)
	{
		this.emit('BX.Calendar.Resourcebooking.LiveFieldController:dateChanged');

		if (this.timeSelectorDisplayed())
		{
			if (realDate)
			{
				this.timeControl.show();
				let
					timeValueFrom,
					currentDate = this.getCurrentDate();

				if (currentDate)
				{
					timeValueFrom = currentDate.getHours() * 60 + currentDate.getMinutes();
				}

				this.timeControl.refresh(
					this.getSettingsData().time,
					{
						slotIndex: this.getSlotIndex({date: realDate}),
						currentDate: realDate,
						selectedValue: timeValueFrom
					});

				// this.timeControl.refresh(
				// 	this.getSettingsData().time,
				// 	{
				// 		slotIndex: this.getAvailableSlotIndex({
				// 			date: realDate,
				// 			resources: this.getSelectedResources(),
				// 			user: this.getSelectedUser(),
				// 			duration: this.getCurrentDuration()
				// 		}),
				// 		currentDate: realDate,
				// 		selectedValue: timeValueFrom
				// 	});
			}
		}
		else
		{
			this.handleSelectedDateTimeChanging(null, true);
		}
		this.onChangeValues();
	}

	handleSelectedDateTimeChanging(value, useTimeout)
	{
		if (useTimeout !== false)
		{
			if (this.updateTimeStatusTimeout)
			{
				this.updateTimeStatusTimeout = clearTimeout(this.updateTimeStatusTimeout);
			}
			this.updateTimeStatusTimeout = setTimeout(function(){
				this.handleSelectedDateTimeChanging(value, false);
			}.bind(this), 100);
		}
		else
		{
			if (this.isUserSelectorInAutoMode())
			{
				this.autoAdjustUserSelector();
			}
			if (this.isResourceSelectorInAutoMode())
			{
				this.autoAdjustResourceSelector();
			}

			this.updateStatusControl();
			BookingUtil.fireCustomEvent(window, 'crmWebFormFireResize');
		}
		this.onChangeValues();
	}

	updateStatusControl()
	{
		if (this.statusControl && this.selectorCanBeShown('date'))
		{
			let currentDate = this.getCurrentDate();
			if (this.dateControl.isItPastDate(currentDate))
			{
				this.statusControl.setError(Loc.getMessage('WEBF_RES_BOOKING_PAST_DATE_WARNING'));
			}
			else
			{
				if (this.timeSelectorDisplayed())
				{
					if (this.timeControl.hasAvailableSlots())
					{
						let timeValue = this.timeControl.getValue();
						this.statusControl.refresh({
							dateFrom: timeValue ? currentDate : null,
							duration: timeValue ? this.getCurrentDuration() : null,
							fullDay: false
						});
					}
					else
					{
						this.statusControl.hide();
					}
				}
				else
				{
					this.statusControl.refresh({
						dateFrom: this.dateControl.isDateAvailable(currentDate) ? currentDate : null,
						duration: this.getCurrentDuration(),
						fullDay: true
					});
				}
			}
		}
	}

	getFreeDate(params)
	{
		let
			slotsAmount = Math.ceil(params.duration / this.scale),
			freeDate = null,
			date = this.loadedDataFrom;

		// Walk through each date searching for free space
		while (true)
		{
			if (this.checkSlotsForDate(date, slotsAmount, {
				resources: params.resources,
				user: params.user
			}))
			{
				freeDate = date;
				break;
			}

			date.setDate(date.getDate() + 1);
			if (date.getTime() >= this.loadedDataTo.getTime())
			{
				break;
			}
		}

		return freeDate;
	}

	getAvailableDateIndex(params)
	{
		let
			userIsFree, resourcesAreFree,
			dateIndex = {};

		if (this.timeSelectorDisplayed())
		{
			let slotsAmount = Math.ceil(params.duration / this.scale);

			this.loadedDates.forEach(function(date)
			{
				dateIndex[date.key] = this.checkSlotsForDate(date.key, slotsAmount, {
					resources: params.resources,
					user: params.user
				});
			}, this);
		}
		else
		{
			let
				i, daysGap, date, j,
				userKey = params.user ? 'user' + params.user : null,
				daysAmount = Math.ceil(params.duration / 1440);

			daysGap = 1;
			for (i = this.loadedDates.length; i--; i >= 0)
			{
				userIsFree = true;
				resourcesAreFree = true;
				date = this.loadedDates[i];

				if (userKey)
				{
					// All day is free for user
					userIsFree = !date.slotsCount[userKey];
				}

				if (userIsFree && params.resources && params.resources.length > 0)
				{
					for (j = 0; j < params.resources.length; j++)
					{
						resourcesAreFree = resourcesAreFree && !date.slotsCount['resource' + params.resources[j]];
						if (!resourcesAreFree)
						{
							break;
						}
					}
				}

				if (userIsFree && resourcesAreFree)
				{
					daysGap++;
				}
				else
				{
					daysGap = 0;
				}

				dateIndex[date.key] = userIsFree && resourcesAreFree && daysAmount <= daysGap;
			}
		}

		return dateIndex;
	}

	getSlotIndex(params)
	{
		if (params.date)
		{
			params.date = this.dateControl.getValue();
		}

		let slotIndex = {};
		if (Type.isDate(params.date))
		{
			if (this.getFieldParams().ALLOW_OVERBOOKING !== "Y"
				&& (this.isUserSelectorInAutoMode() || this.isResourceSelectorInAutoMode()))
			{
				const fieldParams = this.getFieldParams();
				let
					freeSlot,
					i, j, time,
					slotGap = 1,
					todayNowTime = 0,
					timeSlots = this.getTimeSlots(),
					dateKey = BookingUtil.formatDate(this.DATE_FORMAT, params.date),
					loadedDate = this.loadedDates[this.loadedDatesIndex[dateKey]],
					slotsAmount = Math.ceil(this.getCurrentDuration() / this.scale);

				if (this.checkIsTodayDate(dateKey))
				{
					const today = new Date();
					const deltaOffset = fieldParams.USE_USER_TIMEZONE === 'N'
						? today.getTimezoneOffset() * 60 + this.timezoneOffset:
						0;
					todayNowTime = today.getHours() * 60 + today.getMinutes() + (deltaOffset / 60);
				}

				// Prefill slotIndex
				timeSlots.forEach(function(slot){slotIndex[slot.time] = true;}, this);

				if (this.isUserSelectorInAutoMode())
				{
					const userList = this.getUsersValue();

					for (i = timeSlots.length; i--; i >= 0)
					{
						time = timeSlots[i].time;
						freeSlot = false;

						if (todayNowTime && time < todayNowTime)
						{
							slotIndex[time] = false;
							continue;
						}

						for (j = 0; j < userList.length; j++)
						{
							if (!loadedDate.slots[time]
								|| !loadedDate.slots[time]['user' + userList[j]])
							{
								freeSlot = true;
								break;
							}
						}

						slotIndex[time] = slotIndex[time] && freeSlot && slotsAmount <= slotGap;
						slotGap = freeSlot ? slotGap + 1 : 1;
					}
				}

				if (this.isResourceSelectorInAutoMode())
				{
					const resList = this.getResourceValue();
					for (i = timeSlots.length; i--; i >= 0)
					{
						time = timeSlots[i].time;
						freeSlot = false;

						if (todayNowTime && time < todayNowTime)
						{
							slotIndex[time] = false;
							continue;
						}

						for (j = 0; j < resList.length; j++)
						{
							if (!loadedDate.slots[time]
								|| !loadedDate.slots[time]['resource' + resList[j]])
							{
								freeSlot = true;
								break;
							}
						}
						slotIndex[time] = slotIndex[time] && freeSlot && slotsAmount <= slotGap;
						slotGap = freeSlot ? slotGap + 1 : 1;
					}
				}
			}
			else
			{
				slotIndex =  this.getAvailableSlotIndex({
					date: params.date || this.dateControl.getValue(),
					resources: this.getSelectedResources(),
					user: this.getSelectedUser(),
					duration: this.getCurrentDuration()
				})
			}
		}

		return slotIndex;
	}

	getAvailableSlotIndex(params)
	{
		let todayNowTime = 0;
		const fieldParams = this.getFieldParams();
		let
			dateKey, loadedDate, i, j, time,
			slotGap,
			userKey = params.user ? 'user' + params.user : null,
			slotsAmount = Math.ceil(params.duration / this.scale),
			userIsFree, resourcesAreFree,
			timeSlots = this.getTimeSlots(),
			allowOverbooking = fieldParams.ALLOW_OVERBOOKING === "Y",
			slotIndex = {};

		// Prefill slotIndex
		timeSlots.forEach(function(slot){slotIndex[slot.time] = true;}, this);

		if (Type.isDate(params.date))
		{
			dateKey = BookingUtil.formatDate(this.DATE_FORMAT, params.date);
			loadedDate = this.loadedDates[this.loadedDatesIndex[dateKey]];
			slotGap = 1;

			if (this.checkIsTodayDate(dateKey))
			{
				const today = new Date();
				const deltaOffset = fieldParams.USE_USER_TIMEZONE === 'N'
					? today.getTimezoneOffset() * 60 + this.timezoneOffset:
					0;
				todayNowTime = today.getHours() * 60 + today.getMinutes() + (deltaOffset / 60);
			}

			for (i = timeSlots.length; i--; i >= 0)
			{
				time = timeSlots[i].time;
				if (todayNowTime && time < todayNowTime)
				{
					slotIndex[time] = false;
					continue;
				}

				if (allowOverbooking)
				{
					slotIndex[time] = slotsAmount <= slotGap;
					slotGap++;
				}
				else
				{
					userIsFree = true;
					resourcesAreFree = true;

					if (userKey)
					{
						// Time is free for user
						userIsFree = !loadedDate.slots[time] || !loadedDate.slots[time][userKey];
					}

					if (params.resources && params.resources.length > 0)
					{
						for (j = 0; j < params.resources.length; j++)
						{
							resourcesAreFree = resourcesAreFree && (!loadedDate.slots[time] || !loadedDate.slots[time]['resource' + params.resources[j]]);

							if (!resourcesAreFree)
							{
								break;
							}
						}
					}
					slotIndex[time] = userIsFree && resourcesAreFree && slotsAmount <= slotGap;

					if (userIsFree && resourcesAreFree)
					{
						slotGap++;
					}
					else
					{
						slotGap = 1;
					}
				}
			}
		}
		return slotIndex;
	}

	checkSlotsForDate(date, slotsAmount, params)
	{
		let
			userIsFree = true,
			resourcesAreFree = true,
			dateKey = Type.isDate(date) ? BookingUtil.formatDate(this.DATE_FORMAT, date) : date;

		params = params || {};
		if (this.usersDisplayed() && params.user)
		{
			if (this.busySlotMatrix.user[params.user]
				&& !this.entityHasSlotsForDate({
					entityType: 'user',
					entityId: params.user,
					dateKey: dateKey,
					slotsAmount: slotsAmount
				})
			)
			{
				userIsFree = false;
			}
		}

		if (this.resourcesDisplayed() && userIsFree
			&& Type.isArray(params.resources) && params.resources.length  > 0)
		{
			params.resources.forEach(function(resourceId)
			{
				if (resourcesAreFree
					&& this.busySlotMatrix.resource[resourceId]
					&& !this.entityHasSlotsForDate({
						entityType: 'resource',
						entityId: resourceId,
						dateKey: dateKey,
						slotsAmount: slotsAmount
					})
				)
				{
					resourcesAreFree = false;
				}
			}, this);
		}

		return userIsFree && resourcesAreFree;
	}

	entityHasSlotsForDate(params)
	{
		let
			busySlotList,
			slots, i,
			freeSlotCount = 0,
			hasFreeSlots = false;

		if (this.busySlotMatrix[params.entityType][params.entityId] &&
			this.busySlotMatrix[params.entityType][params.entityId][params.dateKey])
		{
			busySlotList = this.busySlotMatrix[params.entityType][params.entityId][params.dateKey];
			slots = this.getTimeSlots();
			for (i = 0; i < slots.length; i++)
			{
				if (!busySlotList[slots[i].time])
				{
					freeSlotCount++;
					if (freeSlotCount >= params.slotsAmount)
					{
						hasFreeSlots = true;
						break;
					}
				}
				else
				{
					freeSlotCount = 0;
				}
			}
		}
		else
		{
			hasFreeSlots = true;
		}

		return hasFreeSlots;
	}

	getSelectedResources()
	{
		let result = null;
		if (this.resourceControl)
		{
			result = this.resourceControl.getSelectedValues();
			if (Type.isArray(result) && !result.length)
			{
				result = null;
			}
		}
		return result;
	}


	getSelectedUser()
	{
		let result = null;
		if (this.userControl)
		{
			result = this.userControl.getSelectedUser();
		}
		return result;
	}

	getCurrentDuration()
	{
		let result = null;
		if (this.durationControl)
		{
			result = this.durationControl.getSelectedValue();
		}
		else if (this.serviceControl)
		{
			let service = this.serviceControl.getSelectedService(true);
			if (service && service.duration)
			{
				result = parseInt(service.duration);
			}
		}
		return result;
	}

	getDefaultDurationSlotsAmount()
	{
		let
			settingsData = this.getSettingsData(),
			fieldParams = this.getFieldParams(),
			duration, i, slotsAmount;

		if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value)
		{
			const services = this.getServicesValue();
			if (Type.isArray(fieldParams.SERVICE_LIST) && services.length > 0)
			{
				for (i = 0; i < fieldParams.SERVICE_LIST.length; i++)
				{
					if (BookingUtil.translit(fieldParams.SERVICE_LIST[i].name) === services[0])
					{
						duration = parseInt(fieldParams.SERVICE_LIST[i].duration);
						break;
					}
				}
			}
		}
		else
		{
			duration = parseInt(settingsData.duration.defaultValue);
		}

		slotsAmount = Math.ceil(duration / this.scale);
		return slotsAmount;
	}

	getCurrentServiceName()
	{
		let result = '';
		if (this.serviceControl)
		{
			let service = this.serviceControl.getSelectedService(true);
			if (service && service.name)
			{
				result = service.name;
			}
		}
		return result;
	}

	getCurrentDate()
	{
		let result = null;
		if (this.dateControl && this.dateControl.isShown())
		{
			result = this.dateControl.getValue();
			if (this.timeSelectorDisplayed())
			{
				let
					hour, min,
					timeValue = this.timeControl.getValue();

				if (timeValue)
				{
					hour = Math.floor(timeValue / 60);
					min = timeValue - hour * 60;
					result.setHours(hour, min, 0, 0);
				}
			}
			else
			{
				result.setHours(0, 0, 0, 0);
			}
		}

		return result;
	}

	getTimeSlots()
	{
		if (!this.slots)
		{
			this.slots = [];
			let slot;
			let finishTime, hourFrom,  hourTo;
			let minTo, minFrom;
			let num = 0;
			let time = this.timeFrom * 60;

			while (time < this.timeTo * 60)
			{
				hourFrom = Math.floor(time / 60);
				minFrom = (time) - hourFrom * 60;
				finishTime = time + this.scale;
				hourTo = Math.floor(finishTime / 60);
				minTo = (finishTime) - hourTo * 60;

				slot = {
					time: time
				};

				this.slots.push(slot);
				time += this.scale;
				num++;
			}
		}
		return this.slots;
	}

	usersDisplayed()
	{
		if (this.useUsers === undefined)
		{
			this.useUsers = this.getFieldParams()['USE_USERS'] === 'Y';
		}
		return this.useUsers;
	}

	resourcesDisplayed()
	{
		if (this.useResources === undefined)
		{
			let fieldParams = this.getFieldParams();
			this.useResources = !!(fieldParams.USE_RESOURCES === 'Y'
				&& fieldParams.SELECTED_RESOURCES);
		}
		return this.useResources;
	}

	timeSelectorDisplayed()
	{
		if (this.useTime === undefined)
		{
			this.useTime = this.getFieldParams().FULL_DAY !== 'Y';
		}
		return this.useTime;
	}

	selectorCanBeShown(type)
	{
		let result = false;
		if (type === 'resources')
		{
			if (this.resourcesDisplayed() && !this.usersDisplayed())
			{
				result = true;
			}
			else if (this.usersDisplayed())
			{
				result = this.getSelectedUser();
			}
		}
		else if (type === 'date' || type === 'services' || type === 'duration')
		{
			if (this.usersDisplayed() && this.resourcesDisplayed())
			{
				result = this.getSelectedUser() && this.getSelectedResources();
			}
			else if (this.usersDisplayed())
			{
				result = this.getSelectedUser();
			}
			else if (this.resourcesDisplayed())
			{
				result = this.getSelectedResources();
			}
		}
		return result;
	}

	checkIsTodayDate(dateKey)
	{
		if (!this.todayDateKey)
		{
			let today = new Date();
			this.todayDateKey = BookingUtil.formatDate(this.DATE_FORMAT, today);
		}
		return this.todayDateKey === dateKey;
	}

	getResourceValue()
	{
		const settingsData = this.getSettingsData();
		let value = [];
		if (Type.isArray(settingsData.resources.value))
		{
			value = settingsData.resources.value;
		}
		else if (Type.isString(settingsData.resources.value))
		{
			value = settingsData.resources.value.split('|');
		}
		return value;
	}

	getUsersValue()
	{
		const settingsData = this.getSettingsData();
		let value = [];
		if (Type.isArray(settingsData.users.value))
		{
			value = settingsData.users.value;
		}
		else if (Type.isString(settingsData.users.value))
		{
			value = settingsData.users.value.split('|');
		}
		return value;
	}

	getServicesValue()
	{
		const settingsData = this.getSettingsData();
		let value = [];
		if (Type.isArray(settingsData.services.value))
		{
			value = settingsData.services.value;
		}
		else if (Type.isString(settingsData.services.value))
		{
			value = settingsData.services.value.split('|');
		}
		return value;
	}
}