;(function()
{
	'use strict';
	BX.namespace('BX.Calendar.UserField');

	/**
	 * Controller used to create and manage instances of resourcebooking user fields during filling of forms
	 *
	 * @constructor
	 * @this  {CrmFormResourceBookingFieldLiveController}
	 * @param {array} params - incoming data params
	 * @param {DOM} params.wrap - DOM node, wrapper of control in the form.
	 */
	// region *CrmFormResourceBookingFieldLiveController*
	function CrmFormResourceBookingFieldLiveController(params)
	{
		this.params = params;
		this.timeFrom = params.timeFrom || 7;
		this.timeTo = params.timeTo || 20;
		this.scale = parseInt(params.field.settings_data.time.scale) || 60;
		this.inputName = params.field.name + '[]';
		this.DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"));
		this.DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"));

		this.loadedDates = [];

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
	BX.Calendar.UserField.CrmFormResourceBookingFieldLiveController = CrmFormResourceBookingFieldLiveController;

	CrmFormResourceBookingFieldLiveController.prototype = {
		init: function()
		{
			this.DOM.outerWrap = this.DOM.wrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-wrapper'}}));

			this.showMainLoader();
			this.requireFormData().then(
				BX.delegate(function()
				{
					this.hideMainLoader();
					this.buildFormControls();
					this.onChangeValues();
				}, this)
			);
		},

		check: function()
		{
			var result = true;
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

			if (result && !this.dateControl.getValue())
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
		},

		buildFormControls: function()
		{
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-inner'}}));
			this.DOM.inputsWrap = this.DOM.innerWrap.appendChild(BX.create("div"));

			if (!this.getFieldParams())
			{
				this.statusControl = new BX.Calendar.UserField.ResourceBookingStatusControl({
					outerWrap: this.DOM.innerWrap
				});
				this.statusControl.refresh({});
				this.statusControl.setError('[UF_NOT_FOUND] ' + BX.message('WEBF_RES_BOOKING_UF_WARNING'));
			}
			else
			{
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
		},

		refreshControlsState: function()
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

			var settingsData = this.getSettingsData();
			// Show date & time control
			if (this.selectorCanBeShown('date') && this.dateControl)
			{
				if (!this.dateControl.isShown())
				{
					var startValue;
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
				else
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
						// this.timeControl.refresh(
						// 	settingsData.time,
						// 	{
						// 		slotIndex: this.getAvailableSlotIndex({
						// 			date: this.dateControl.getValue(),
						// 			resources: this.getSelectedResources(),
						// 			user: this.getSelectedUser(),
						// 			duration: this.getCurrentDuration()
						// 		}),
						// 		currentDate: this.dateControl.getValue()
						// 	});

						this.timeControl.refresh(
							settingsData.time,
							{
								slotIndex: this.getSlotIndex({date: this.dateControl.getValue()}),
								currentDate: this.dateControl.getValue()
							});
					}
				}
			}

			this.updateStatusControl();
			this.onChangeValues();
			BX.onCustomEvent(window, 'crmWebFormFireResize');
		},

		onChangeValues: function()
		{
			var
				allValuesValue = '',
				dateFromValue = '',
				dateFrom = this.getCurrentDate(),
				duration = this.getCurrentDuration() * 60,// Duration in minutes
				serviceName = this.getCurrentServiceName(),
				entries = [];

			// Clear inputs
			BX.cleanNode(this.DOM.inputsWrap);
			this.DOM.valueInputs = [];

			if (BX.type.isDate(dateFrom))
			{
				var resources = this.getSelectedResources();
				if (BX.type.isArray(resources))
				{
					resources.forEach(function(resourceId)
					{
						entries = entries.concat({type: 'resource', id: resourceId});
					});
				}

				var selectedUser = this.getSelectedUser();
				if (selectedUser)
				{
					entries = entries.concat({type: 'user', id: selectedUser});
				}

				dateFromValue = BX.date.format(this.DATETIME_FORMAT, dateFrom.getTime() / 1000);

				entries.forEach(function(entry)
				{
					var value = entry.type + '|' + entry.id + '|' + dateFromValue + '|' + duration + '|' + serviceName;
					allValuesValue += value + '#';

					this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(
						BX.create('INPUT', {
							attrs:{
								name: this.inputName,
								value: value,
								type: 'hidden'
							}})));
				}, this);
			}

			if (!entries.length)
			{
				this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(
					BX.create('INPUT', {
						attrs:{
							name: this.inputName,
							value: 'empty',
							type: 'hidden'
						}})));
			}
		},

		showMainLoader: function()
		{
			if (this.DOM.wrap)
			{
				this.hideMainLoader();
				this.DOM.mainLoader = this.DOM.outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-wrapper-loader-wrap'},
					children: [BX.Calendar.UserField.ResourceBooking.getLoader(160)]
				}));
			}
		},

		hideMainLoader: function()
		{
			BX.remove(this.DOM.mainLoader);
		},


		showStatusLoader: function()
		{
			if (this.DOM.wrap)
			{
				this.hideMainLoader();
				this.DOM.mainLoader = this.DOM.outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-wrapper-loader-wrap'},
					children: [BX.Calendar.UserField.ResourceBooking.getLoader(160)]
				}));
			}
		},

		hideStatusLoader: function()
		{
			BX.remove(this.DOM.mainLoader);
		},

		requestAccessibilityData: function(params)
		{
			if (!this.requestedFormData)
			{
				this.showStatusLoader();

				this.requestedFormData = true;
				var formDataParams = {
					from: params.date
				};

				this.requireFormData(formDataParams).then(
					BX.delegate(function()
					{
						this.hideStatusLoader();
						this.refreshControlsState();
						this.dateControl.refreshCurrentValue();
						this.onChangeValues();
						this.requestedFormData = false;
					}, this)
				);
			}
		},

		requireFormData: function(params)
		{
			params = params || {};
			var
				data = {
					settingsData: this.getSettingsData() || null
				},
				promise = new BX.Promise();

			if (!this.userFieldParams)
			{
				data.fieldName = this.params.field.entity_field_name;
			}

			var
				dateFrom = BX.type.isDate(params.from) ? params.from : new Date(),
				dateTo;

			if (BX.type.isDate(params.to))
			{
				dateTo = params.to;
			}
			else
			{
				dateTo = new Date(dateFrom.getTime());
				dateTo.setDate(dateFrom.getDate() + 60);
			}

			data.from = BX.date.format(this.DATE_FORMAT, dateFrom);
			data.to = BX.date.format(this.DATE_FORMAT, dateTo);

			this.setLoadedDataLimits(dateFrom, dateTo);

			BX.ajax.runAction('calendar.api.resourcebookingajax.getfillformdata', {
				data: data
			}).then(BX.delegate(function(response)
				{
					if (BX.type.isNumber(response.data.timezoneOffset))
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

					promise.fulfill(response.data);
				}, this),
				function (response) {
					/**
					 {
						 "status": "error",
						 "errors": [...]
					 }
					 **/
				});

			return promise;
		},

		setLoadedDataLimits: function(from, to)
		{
			this.loadedDataFrom = BX.type.isDate(from) ? from : BX.parseDate(from);
			this.loadedDataTo = BX.type.isDate(to) ? to : BX.parseDate(to);

			this.loadedDates = this.loadedDates || [];
			this.loadedDatesIndex = this.loadedDatesIndex || {};

		 	var
				dateKey,
				date = new Date(this.loadedDataFrom.getTime());

			while (true)
			{
				dateKey = BX.date.format(this.DATE_FORMAT, date);
				this.loadedDatesIndex[dateKey] = this.loadedDates.length;
				this.loadedDates.push({
					key: BX.date.format(this.DATE_FORMAT, date),
					slots: {},
					slotsCount: {}
				});
				date.setDate(date.getDate() + 1);

				if (date.getTime() > this.loadedDataTo.getTime())
				{
					break;
				}
			}
		},

		fillDataIndex: function(date, time, entityType, entityId)
		{
			var dateIndex = this.loadedDatesIndex[date];
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
		},

		handleAccessibilityData: function(data, entityType)
		{
			if (BX.type.isPlainObject(data) && (entityType === 'user' || entityType === 'resource'))
			{
				// For each entry which has accessibility entries
				for (var entityId in data)
				{
					if (data.hasOwnProperty(entityId))
					{
						data[entityId].forEach(function(entry)
						{
							if (!entry.from)
							{
								entry.from = BX.parseDate(entry.dateFrom);
								if (entry.from)
								{
									entry.from.setSeconds(0,0);
									entry.fromTimestamp = entry.from.getTime();
								}
							}

							if (!entry.to)
							{
								entry.to = BX.parseDate(entry.dateTo);
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
				this.accessibility[entityType] = BX.mergeEx(this.accessibility[entityType], data);
			}
		},

		fillBusySlotMatrix: function(entry, entityType, entityId)
		{
			if (!this.busySlotMatrix[entityType][entityId])
			{
				this.busySlotMatrix[entityType][entityId] = {};
			}

			var
				fromDate = new Date(entry.from.getTime()),
				dateKey = BX.date.format(this.DATE_FORMAT, fromDate),
				dateToKey = BX.date.format(this.DATE_FORMAT, entry.to),
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
						dateKey = BX.date.format(this.DATE_FORMAT, fromDate);
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
		},

		getCaption: function()
		{
			return this.params.field.caption;
		},

		getSettingsData: function()
		{
			return this.params.field.settings_data;
		},

		getUserIndex: function()
		{
			return this.userIndex;
		},

		getFieldParams: function()
		{
			return this.userFieldParams;
		},

		getSettings: function()
		{
			return {
				caption: this.getCaption(),
				data: this.getSettingsData()
			};
		},

		isUserSelectorInAutoMode: function()
		{
			return this.usersDisplayed() && this.getSettingsData().users.show === "N";
		},

		isResourceSelectorInAutoMode: function()
		{
			return this.resourcesDisplayed() && this.getSettingsData().resources.show === "N";
		},

		autoAdjustUserSelector: function()
		{
			var
				currentDate = this.dateControl.getValue(),
				timeValue = this.timeControl.getValue();

			if (BX.type.isDate(currentDate) && timeValue)
			{
				var i, loadedDate = this.loadedDates[this.loadedDatesIndex[BX.date.format(this.DATE_FORMAT, currentDate)]];
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
		},

		autoAdjustResourceSelector: function()
		{
			var
				currentDate = this.dateControl.getValue(),
				timeValue = this.timeControl.getValue();

			if (BX.type.isDate(currentDate) && timeValue)
			{
				var
					i, id,
					loadedDate = this.loadedDates[this.loadedDatesIndex[BX.date.format(this.DATE_FORMAT, currentDate)]];

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
		},

		preparaAutoSelectValues: function ()
		{
			var
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
		},

		getFreeEntitiesForDate: function(date, params)
		{
			var
				settingsData = this.getSettingsData(),
				slotsAmount = params.slotsAmount || 1,
				i, userList, resList;

			if (params.autoSelectUser)
			{
				userList = BX.type.isArray(settingsData.users.value) ? settingsData.users.value : settingsData.users.value.split('|');
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
				resList = BX.type.isArray(settingsData.resources.value) ? settingsData.resources.value : settingsData.resources.value.split('|');

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
		},

		displayUsersControl: function()
		{
			if (this.usersDisplayed())
			{
				this.userControl = new BX.Calendar.UserField.ViewFormUsersControl({
					outerWrap: this.DOM.innerWrap,
					data: this.getSettingsData().users,
					userIndex: this.getUserIndex(),
					previewMode: false,
					autoSelectDefaultValue: this.selectedUserId,
					changeValueCallback: BX.proxy(function(userId)
					{
						BX.onCustomEvent(this, 'CrmFormResourceBookingFieldLiveController:onUserChanged', [userId]);
						this.refreshControlsState();
					}, this)
				});
				this.userControl.display();
			}
		},

		displayResourcesControl: function()
		{
			var
				valueIndex = {},
				dataValue = [],
				fieldParams = this.getFieldParams(),
				settingsData = this.getSettingsData();

			if (this.resourcesDisplayed())
			{
				settingsData.resources.value.split('|').forEach(function(id)
				{
					id = parseInt(id);
					if (id > 0)
					{
						valueIndex[id] = true;
						dataValue.push(id);
					}
				});

				var resourceList = [];
				fieldParams.SELECTED_RESOURCES.forEach(function(res)
				{
					res.id = parseInt(res.id);
					if (valueIndex[res.id])
					{
						resourceList.push(res);
					}
				}, this);

				this.resourceControl = new BX.Calendar.UserField.ViewFormResourcesControl({
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
					changeValueCallback: BX.proxy(function()
					{
						BX.onCustomEvent(this, 'CrmFormResourceBookingFieldLiveController:onResourceChanged', []);
						this.refreshControlsState();
					}, this)
				});

				if (this.selectorCanBeShown('resources'))
				{
					this.resourceControl.display();
				}
			}
		},

		displayServicesControl: function()
		{
			var
				fieldParams = this.getFieldParams(),
				settingsData = this.getSettingsData();

			if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value)
			{
				var dataValueRaw = BX.type.isArray(settingsData.services.value) ? settingsData.services.value : settingsData.services.value.split('|');

				this.serviceControl = new BX.Calendar.UserField.ViewFormServicesControl({
					outerWrap: this.DOM.innerWrap,
					data: settingsData.services,
					serviceList: fieldParams.SERVICE_LIST,
					selectedValue: (BX.type.isArray(dataValueRaw) && dataValueRaw.length > 0) ? dataValueRaw[0] : null,
					changeValueCallback: BX.proxy(function()
					{
						BX.onCustomEvent(this, 'CrmFormResourceBookingFieldLiveController:onServiceChanged', []);
						this.refreshControlsState();
					}, this)
				});

				if (this.selectorCanBeShown('services'))
				{
					this.serviceControl.display();
				}
			}
		},

		displayDurationControl: function()
		{
			var
				fieldParams = this.getFieldParams(),
				settingsData = this.getSettingsData();

			if (!this.serviceControl)
			{
				this.durationControl = new BX.Calendar.UserField.ViewFormDurationControl({
					outerWrap: this.DOM.innerWrap,
					data: settingsData.duration,
					fullDay: fieldParams.FULL_DAY === 'Y',
					changeValueCallback: BX.proxy(function()
					{
						BX.onCustomEvent(this, 'CrmFormResourceBookingFieldLiveController:onDurationChanged', []);
						this.refreshControlsState();
					}, this)
				});

				if (this.selectorCanBeShown('duration'))
				{
					this.durationControl.display();
				}
			}
		},

		displayDateTimeControl: function()
		{
			var
				startValue = null,
				settingsData = this.getSettingsData(),
				fieldParams = this.getFieldParams();

			this.dateControl = new BX.Calendar.UserField.DateSelector({
				outerWrap: this.DOM.innerWrap,
				data: settingsData.date,
				previewMode: false,
				allowOverbooking: fieldParams.ALLOW_OVERBOOKING === "Y",
				changeValueCallback: BX.proxy(this.handleDateChanging, this),
				requestDataCallback: BX.proxy(this.requestAccessibilityData, this)
			});

			if (this.timeSelectorDisplayed())
			{
				var timezone = false;

				if (fieldParams.USE_USER_TIMEZONE === 'N')
				{
					var userTimezoneOffset = -(new Date).getTimezoneOffset()*60;
					if (userTimezoneOffset !== this.timezoneOffset)
					{
						timezone = fieldParams.TIMEZONE;
					}
				}

				this.timeControl = new BX.Calendar.UserField.TimeSelector({
					outerWrap: this.DOM.innerWrap,
					data: settingsData.time,
					previewMode: false,
					changeValueCallback: BX.proxy(this.handleSelectedDateTimeChanging, this),
					timeFrom: this.timeFrom,
					timeTo: this.timeTo,
					timezone: timezone,
					timezoneOffset: this.timezoneOffset,
					timezoneOffsetLabel: this.timezoneOffsetLabel
				});
			}

			this.statusControl = new BX.Calendar.UserField.ResourceBookingStatusControl({
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
		},

		handleDateChanging: function(date, realDate)
		{
			BX.onCustomEvent(this, 'CrmFormResourceBookingFieldLiveController:onDateChanged', []);

			if (this.timeSelectorDisplayed())
			{
				if (realDate)
				{
					this.timeControl.show();
					var
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
		},

		handleSelectedDateTimeChanging: function(value, useTimeout)
		{
			if (useTimeout !== false)
			{
				if (this.updateTimeStatusTimeout)
				{
					this.updateTimeStatusTimeout = clearTimeout(this.updateTimeStatusTimeout);
				}
				this.updateTimeStatusTimeout = setTimeout(BX.proxy(function(){
					this.handleSelectedDateTimeChanging(value, false);
				}, this), 100);
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
				BX.onCustomEvent(window, 'crmWebFormFireResize');
			}
			this.onChangeValues();
		},

		updateStatusControl: function()
		{
			if (this.statusControl && this.selectorCanBeShown('date'))
			{
				var currentDate = this.getCurrentDate();
				if (this.dateControl.isItPastDate(currentDate))
				{
					this.statusControl.setError(BX.message('WEBF_RES_BOOKING_PAST_DATE_WARNING'));
				}
				else
				{
					if (this.timeSelectorDisplayed())
					{
						if (this.timeControl.hasAvailableSlots())
						{
							var timeValue = this.timeControl.getValue();
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
		},

		getFreeDate: function(params)
		{
			var
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
		},

		getAvailableDateIndex: function(params)
		{
			var
				userIsFree, resourcesAreFree,
				dateIndex = {};

			if (this.timeSelectorDisplayed())
			{
				var slotsAmount = Math.ceil(params.duration / this.scale);

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
				var
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
		},

		getSlotIndex: function(params)
		{
			if (params.date)
			{
				params.date = this.dateControl.getValue();
			}

			var slotIndex = {};

			if (BX.type.isDate(params.date))
			{
				if (this.getFieldParams().ALLOW_OVERBOOKING !== "Y"
					&& (this.isUserSelectorInAutoMode() || this.isResourceSelectorInAutoMode()))
				{
					var
						freeSlot,
						i, j, time,
						settingsData = this.getSettingsData(),
						slotGap = 1,
						todayNowTime = 0,
						timeSlots = this.getTimeSlots(),
						dateKey = BX.date.format(this.DATE_FORMAT, params.date),
						loadedDate = this.loadedDates[this.loadedDatesIndex[dateKey]],
						slotsAmount = Math.ceil(this.getCurrentDuration() / this.scale);

					if (this.checkIsTodayDate(dateKey))
					{
						var today = new Date();
						todayNowTime = today.getHours() * 60 + today.getMinutes();
					}

					// Prefill slotIndex
					timeSlots.forEach(function(slot){slotIndex[slot.time] = true;}, this);

					if (this.isUserSelectorInAutoMode())
					{
						var userList = BX.type.isArray(settingsData.users.value) ? settingsData.users.value : settingsData.users.value.split('|');

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
						var resList = BX.type.isArray(settingsData.resources.value) ? settingsData.resources.value : settingsData.resources.value.split('|');

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
		},

		getAvailableSlotIndex: function(params)
		{
			var
				dateKey, loadedDate, i, j, time,
				todayNowTime = 0,
				slotGap,
				userKey = params.user ? 'user' + params.user : null,
				slotsAmount = Math.ceil(params.duration / this.scale),
				userIsFree, resourcesAreFree,
				timeSlots = this.getTimeSlots(),
				allowOverbooking = this.getFieldParams().ALLOW_OVERBOOKING === "Y",
				slotIndex = {};

			// Prefill slotIndex
			timeSlots.forEach(function(slot){slotIndex[slot.time] = true;}, this);

			if (BX.type.isDate(params.date))
			{
				dateKey = BX.date.format(this.DATE_FORMAT, params.date);
				loadedDate = this.loadedDates[this.loadedDatesIndex[dateKey]];
				slotGap = 1;

				if (this.checkIsTodayDate(dateKey))
				{
					var today = new Date();
					todayNowTime = today.getHours() * 60 + today.getMinutes();
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
		},

		checkSlotsForDate: function(date, slotsAmount, params)
		{
			var
				userIsFree = true,
				resourcesAreFree = true,
				dateKey = BX.type.isDate(date) ? BX.date.format(this.DATE_FORMAT, date) : date;

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
				&& BX.type.isArray(params.resources) && params.resources.length  > 0)
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
		},

		entityHasSlotsForDate: function(params)
		{
			var
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
		},

		getSelectedResources: function()
		{
			var result = null;
			if (this.resourceControl)
			{
				result = this.resourceControl.getSelectedValues();
				if (BX.type.isArray(result) && !result.length)
				{
					result = null;
				}
			}
			return result;
		},


		getSelectedUser: function()
		{
			var result = null;
			if (this.userControl)
			{
				result = this.userControl.getSelectedUser();
			}
			return result;
		},

		getCurrentDuration: function()
		{
			var result = null;
			if (this.durationControl)
			{
				result = this.durationControl.getSelectedValue();
			}
			else if (this.serviceControl)
			{
				var service = this.serviceControl.getSelectedService(true);
				if (service && service.duration)
				{
					result = parseInt(service.duration);
				}
			}
			return result;
		},

		getDefaultDurationSlotsAmount: function()
		{
			var
				settingsData = this.getSettingsData(),
				fieldParams = this.getFieldParams(),
				duration, i, slotsAmount,
				prepareServiceId = function(str) {return BX.translit(str).replace(/[^a-z0-9_]/ig, "_");};

			if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value)
			{
				var services = settingsData.services.value.split('|');
				if (BX.type.isArray(fieldParams.SERVICE_LIST)
					&& BX.type.isArray(services)
					&& services.length > 0
				)
				{
					for (i = 0; i < fieldParams.SERVICE_LIST.length; i++)
					{
						if (prepareServiceId(fieldParams.SERVICE_LIST[i].name) === services[0])
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
		},

		getCurrentServiceName: function()
		{
			var result = '';
			if (this.serviceControl)
			{
				var service = this.serviceControl.getSelectedService(true);
				if (service && service.name)
				{
					result = service.name;
				}
			}
			return result;
		},

		getCurrentDate: function()
		{
			var result = null;
			if (this.dateControl && this.dateControl.isShown())
			{
				result = this.dateControl.getValue();
				if (this.timeSelectorDisplayed())
				{
					var
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
		},

		getTimeSlots: function()
		{
			if (!this.slots)
			{
				this.slots = [];
				var slot,
					finishTime, hourFrom, minFrom, hourTo,
					minTo,
					num = 0,
					time = this.timeFrom * 60;

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
		},

		usersDisplayed: function()
		{
			if (this.useUsers === undefined)
			{
				this.useUsers = !!(this.getFieldParams()['USE_USERS'] === 'Y' && this.getSettingsData().users.value);
			}
			return this.useUsers;
		},

		resourcesDisplayed: function()
		{
			if (this.useResources === undefined)
			{
				var fieldParams = this.getFieldParams();
				this.useResources = !!(fieldParams.USE_RESOURCES === 'Y'
					&& fieldParams.SELECTED_RESOURCES
					&& this.getSettingsData().resources.value);
			}
			return this.useResources;
		},

		timeSelectorDisplayed: function()
		{
			if (this.useTime === undefined)
			{
				this.useTime = this.getFieldParams().FULL_DAY !== 'Y';
			}
			return this.useTime;
		},

		selectorCanBeShown: function(type)
		{
			var result = false;
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
		},

		checkIsTodayDate: function(dateKey)
		{
			if (!this.todayDateKey)
			{
				var today = new Date();
				this.todayDateKey = BX.date.format(this.DATE_FORMAT, today);
			}
			return this.todayDateKey === dateKey;
		}
	};
	// endregion
})();