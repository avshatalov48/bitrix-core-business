;(function()
{
	'use strict';

	BX.namespace('BX.Calendar.UserField');

	if(typeof BX.Calendar.UserField.ResourceBooking !== 'undefined' || !BX.Main.UF || !BX.Main.UF.BaseType)
	{
		return;
	}

	var
		DAY_LENGTH = 86400000,
		TIME_FORMAT, TIME_FORMAT_SHORT,
		DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE")),
		DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"));

	if ((DATETIME_FORMAT.substr(0, DATE_FORMAT.length) == DATE_FORMAT))
		TIME_FORMAT = BX.util.trim(DATETIME_FORMAT.substr(DATE_FORMAT.length));
	else
		TIME_FORMAT = BX.date.convertBitrixFormat(BX.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS');
	TIME_FORMAT_SHORT = TIME_FORMAT.replace(':s', '');

	BX.Calendar.UserField.ResourceBooking = function(params)
	{
		this.params = params;

		this.DOM = {
			outerWrap: BX(params.controlId),
			valueInputs: []
		};

		this.isNew = !this.params.value || !this.params.value.DATE_FROM;

		if (this.params.socnetDestination)
		{
			BX.Calendar.UserField.ResourceBooking.prototype.socnetDestination = this.params.socnetDestination;
		}
	};
	BX.extend(BX.Calendar.UserField.ResourceBooking, BX.Main.UF.BaseType);

	BX.Calendar.UserField.ResourceBooking.prototype.showEditLayout = function()
	{
		this.allValuesValue = '';
		this.DOM.dateTimeWrap = this.DOM.outerWrap.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-content-block-detail-wrap-flex"}}));

		var
			dateFrom,
			duration,
			defaultDuration = this.params.fullDay ? 1440 : 60, // One day or one hour as default
			dateTo;

		if (this.isNew)
		{
			var params = BX.Calendar.UserField.ResourceBooking.getParamsFromHash(this.params.userfieldId);
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
				var
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

		// region Date
		this.DOM.dateWrap = this.DOM.dateTimeWrap
			.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
			.appendChild(BX.create("div", {
				props: { className: "calendar-resourcebook-content-block-detail"},
				html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + BX.message('USER_TYPE_RESOURCE_DATE_LABEL') + '</span></div>'
			}));

		this.DOM.fromInput = this.DOM.dateWrap.appendChild(BX.create('INPUT', {
			attrs: {
				value: BX.date.format(DATE_FORMAT, dateFrom.getTime() / 1000),
				placeholder: BX.message('USER_TYPE_RESOURCE_DATE_LABEL'),
				type: 'text'
			},
			events: {
				click: BX.proxy(this.showSmallCalendar, this),
				change: BX.proxy(this.triggerUpdatePlanner, this)
			},
			props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime'}
		}));
		// endregion

		// region Time
		if (!this.params.fullDay)
		{
			this.DOM.timeWrap = this.DOM.dateTimeWrap
				.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
				.appendChild(BX.create("div", {
					props: { className: "calendar-resourcebook-content-block-detail"},
					html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + BX.message('USER_TYPE_RESOURCE_TIME_LABEL') + '</span></div>'
				}));

			this.DOM.timeFromInput = this.DOM.timeWrap.appendChild(BX.create('INPUT', {
				attrs: {
					value: BX.date.format(TIME_FORMAT_SHORT, dateFrom.getTime() / 1000),
					placeholder: BX.message('USER_TYPE_RESOURCE_TIME_LABEL'),
					type: 'text'
				},
				style: {width: '100px'},
				props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'}
			}));

			this.fromTime = new SelectInput({
				input: this.DOM.timeFromInput,
				values: BX.Calendar.UserField.ResourceBooking.getSimpleTimeList(),
				onChangeCallback: BX.proxy(function()
				{
					this.triggerUpdatePlanner();
				}, this),
				onAfterMenuOpen: BX.proxy(function(ind, popupMenu)
				{
					if (!ind && popupMenu)
					{
						var
							i, menuItem,
							nearestTimeValue = BX.Calendar.UserField.ResourceBooking.adaptTimeValue({h: dateFrom.getHours(), m: dateFrom.getMinutes()});

						if (nearestTimeValue && nearestTimeValue.label)
						{
							for (i = 0; i < popupMenu.menuItems.length; i++)
							{
								menuItem = popupMenu.menuItems[i];
								if (menuItem
									&& nearestTimeValue.label == menuItem.text
									&& menuItem.layout)
								{
									popupMenu.layout.menuContainer.scrollTop = menuItem.layout.item.offsetTop - 2;
								}
							}
						}
					}
				}, this)
			});
		}
		// endregion

		// region Service
		if (this.params.useServices && BX.type.isArray(this.params.serviceList) && this.params.serviceList.length > 0)
		{
			if (this.params.fullDay)
			{
				this.DOM.durationWrap = this.DOM.dateTimeWrap;
			}
			else
			{
				this.DOM.durationWrap = this.DOM.outerWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-content-block-detail-wrap-flex"}}));
			}

			this.DOM.servicesWrap = this.DOM.durationWrap
				.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
				.appendChild(BX.create("div", {
					props: { className: "calendar-resourcebook-content-block-detail"},
					html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + BX.message('USER_TYPE_RESOURCE_SERVICE_LABEL') + '</span></div>'
				}));

			this.DOM.serviceInput = this.DOM.servicesWrap.appendChild(BX.create('INPUT', {
				attrs: {
					value: this.params.value.SERVICE_NAME || '',
					placeholder: BX.message('USER_TYPE_RESOURCE_SERVICE_LABEL'),
					type: 'text'
				},
				style: {width: '200px'},
				events: {
				},
				props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'}
			}));

			var serviceListValues = [];
			this.params.serviceList.forEach(function(service)
			{
				if (service.name != '')
				{
					serviceListValues.push({value: service.duration, label: service.name});
				}
			});

			if (this.isNew && serviceListValues.length >= 1)
			{
				this.DOM.serviceInput.value = serviceListValues[0].label;
				duration = parseInt(serviceListValues[0].value);
			}

			this.serviceList = new SelectInput({
				input: this.DOM.serviceInput,
				values: serviceListValues,
				onChangeCallback: BX.proxy(function(state)
				{
					if (BX.type.isPlainObject(state) && state.realValue)
					{
						this.durationList.setValue(parseInt(state.realValue));
						this.duration = BX.Calendar.UserField.ResourceBooking.parseDuration(this.DOM.durationInput.value);
						this.triggerUpdatePlanner();
					}
				}, this)
			});
		}
		// endregion

		if (!this.DOM.durationWrap)
		{
			this.DOM.durationWrap = this.DOM.dateTimeWrap;
		}

		// region Duration
		this.DOM.durationControlWrap = this.DOM.durationWrap
			.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"}}))
			.appendChild(BX.create("div", {
				props: { className: "calendar-resourcebook-content-block-detail"},
				html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + BX.message('USER_TYPE_RESOURCE_DURATION_LABEL') + '</span></div>'
			}));

		this.DOM.durationInput = this.DOM.durationControlWrap.appendChild(BX.create('INPUT', {
			attrs: {
				value: duration,
				placeholder: BX.message('USER_TYPE_RESOURCE_DURATION_LABEL'),
				type: 'text'
			},
			style: {width: '90px'},
			props: {className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'}
		}));

		this.duration = parseInt(duration);
		this.durationList = new SelectInput({
			input: this.DOM.durationInput,
			values: BX.Calendar.UserField.ResourceBooking.getDurationList(this.params.fullDay),
			value: duration,
			onChangeCallback: BX.proxy(function()
			{
				this.duration = BX.Calendar.UserField.ResourceBooking.parseDuration(this.DOM.durationInput.value);
				this.triggerUpdatePlanner();
			}, this)
		});
		// endregion

		BX.bind(this.DOM.outerWrap, 'click', BX.proxy(this.showPlannerPopup, this));
		BX.bind(this.DOM.fromInput, 'focus', BX.proxy(this.showPlannerPopup, this));
		BX.bind(this.DOM.durationInput, 'focus', BX.proxy(this.showPlannerPopup, this));

		// region User Selector
		if (this.params.useUsers)
		{
			this.DOM.userSelectorWrap = this.DOM.outerWrap.appendChild(BX.create('DIV', {
				props: {className: 'calendar-resbook-users-selector-wrap'}
			}));

			this.DOM.userSelectorWrap = this.DOM.outerWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-resourcebook-content-block-control-field'}}));

			var userSelectorTitle = BX.message('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME');
			this.DOM.userSelectorWrap
				.appendChild(BX.create('DIV', {props: {className: 'calendar-resourcebook-content-block-title'}}))
				.appendChild(BX.create('SPAN', {props: {className: 'calendar-resourcebook-content-block-title-text'}, text: userSelectorTitle}));
			this.DOM.userListWrap = this.DOM.userSelectorWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-control custom-field-item"}}));

			var itemsSelected = [];
			if (this.params.value && BX.type.isArray(this.params.value.ENTRIES))
			{
				this.params.value.ENTRIES.forEach(function(entry)
				{
					if (entry.TYPE === 'user')
					{
						itemsSelected.push('U' + parseInt(entry.RESOURCE_ID));
					}
				});
			}

			this.userSelector = new UserSelector({
				wrapNode: this.DOM.userListWrap,
				socnetDestination: this.params.socnetDestination,
				itemsSelected: itemsSelected,
				addMessage: BX.message('USER_TYPE_RESOURCE_SELECT_USER'),
				checkLimitCallback: BX.proxy(this.checkResourceCountLimit, this)
			});

			BX.addCustomEvent('OnDestinationAddNewItem', BX.proxy(this.triggerUpdatePlanner, this));
			BX.addCustomEvent('OnDestinationUnselect', BX.proxy(this.triggerUpdatePlanner, this));
		}
		// endregion

		// region Resources selector
		if (this.params.useResources)
		{
			this.DOM.resourcesWrap = this.DOM.outerWrap.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add" }}));

			var resSelectorTitle = BX.message('USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME');
			this.DOM.resourcesWrap
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: resSelectorTitle}));
			this.DOM.resourcesListWrap = this.DOM.resourcesWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-control custom-field-item"}}));

			var values = [];
			if (this.params.value && BX.type.isArray(this.params.value.ENTRIES))
			{
				this.params.value.ENTRIES.forEach(function(entry)
				{
					if (entry.TYPE !== 'user')
					{
						values.push({
							type: entry.TYPE,
							id: parseInt(entry.RESOURCE_ID)
						});
					}
				});
			}

			this.resourceSelector = new ResourceListSelector({
				outerWrap: this.DOM.resourcesWrap,
				blocksWrap: this.DOM.resourcesListWrap,
				values: values,
				resourceList: this.params.resourceList,
				onChangeCallback: BX.proxy(function()
				{
					this.triggerUpdatePlanner();
				}, this),
				checkLimitCallback: BX.proxy(this.checkResourceCountLimit, this)
			});
		}
		// endregion

		var _this = this;
		setTimeout(BX.delegate(function(){
			BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldSetValidator', [this.params.controlId, function(result)
			{
				if (!_this.params.allowOverbooking && _this.overbooked)
				{
					if (result && result.addError && BX.Crm && BX.Crm.EntityValidationError)
					{
						result.addError(BX.Crm.EntityValidationError.create({field: this}));
					}
				}

				var _validationPromise = new BX.Promise();
				_validationPromise.fulfill();
				return _validationPromise;
			}]);
		}, this), 100);

		setTimeout(BX.proxy(this.onChangeValues, this), 100);
	};

	BX.Calendar.UserField.ResourceBooking.prototype.showSmallCalendar = function(e)
	{
		var target = e.target || e.srcElement;
		BX.calendar({node: target, field: target, bTime: false});
		BX.focus(target);
		//if (BX.calendar.get().popup)
		//{
		//	BX.removeCustomEvent(BX.calendar.get().popup, 'onPopupClose', BX.proxy(_this.allowSliderClose, _this));
		//}
		//BX.addCustomEvent(BX.calendar.get().popup, 'onPopupClose', BX.proxy(_this.allowSliderClose, _this));
	};

	BX.Calendar.UserField.ResourceBooking.prototype.onChangeValues = function()
	{
		var
			allValuesValue = '',
			dateFrom, dateFromValue = '',
			duration = this.duration * 60,// Duration in minutes
			serviceName = this.DOM.serviceInput ? this.DOM.serviceInput.value : '',
			entries = [];

		dateFrom = this.params.fullDay ? BX.parseDate(this.DOM.fromInput.value) : BX.parseDate(this.DOM.fromInput.value + ' ' + this.DOM.timeFromInput.value);

		if (BX.type.isDate(dateFrom))
		{
			if (this.params.useResources)
			{
				entries = entries.concat(this.getSelectedResourceList());
			}

			if (this.params.useUsers)
			{
				entries = entries.concat(this.getSelectedUserList());
			}
			dateFromValue = BX.date.format(DATETIME_FORMAT, dateFrom.getTime() / 1000);
		}

		// Clear inputs
		this.DOM.valueInputs.forEach(function(input){BX.remove(input);});
		this.DOM.valueInputs = [];

		entries.forEach(function(entry)
		{
			var
				value = entry.type + '|' + entry.id;
				value += '|' + dateFromValue + '|' + duration + '|' + serviceName;

			allValuesValue += value + '#';

			this.DOM.valueInputs.push(this.DOM.outerWrap.appendChild(
				BX.create('INPUT', {
					attrs:{
						name: this.params.inputName,
						value: value,
						type: 'hidden'
					}})));
		}, this);


		if (!entries.length)
		{
			this.DOM.valueInputs.push(this.DOM.outerWrap.appendChild(
				BX.create('INPUT', {
					attrs:{
						name: this.params.inputName,
						value: 'empty',
						type: 'hidden'
					}})));
		}

		if (!this.allValuesValue)
		{
			this.allValuesValue = allValuesValue;
		}
		else if (this.allValuesValue !== allValuesValue)
		{
			BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldExternalChanged', [this.params.controlId]);
		}
	};

	BX.Calendar.UserField.ResourceBooking.prototype.showPlannerPopup = function()
	{
		var currentEventList = [];
		if (this.params.value && BX.type.isArray(this.params.value.ENTRIES))
		{
			this.params.value.ENTRIES.forEach(function(entry)
			{
				currentEventList.push(entry.EVENT_ID);
			});
		}

		BX.Calendar.UserField.ResourceBooking.plannerPopup.show({
			plannerId: this.params.plannerId,
			bindNode: this.DOM.outerWrap,
			plannerConfig: this.getPlannerConfig(),
			selector: this.getSelectorData(),
			selectorOnChangeCallback: BX.proxy(this.plannerSelectorOnChange, this),
			selectEntriesOnChangeCallback: BX.proxy(this.plannerSelectedEntriesOnChange, this),
			checkSelectorStatusCallback: BX.proxy(this.checkSelectorStatusCallback, this),
			currentEventList: currentEventList
		});

		this.triggerUpdatePlanner();
	};

	BX.Calendar.UserField.ResourceBooking.prototype.triggerUpdatePlanner = function()
	{
		if (BX.Calendar.UserField.ResourceBooking.plannerPopup.plannerId == this.params.plannerId
			&& BX.Calendar.UserField.ResourceBooking.plannerPopup.isShown())
		{
			BX.Calendar.UserField.ResourceBooking.plannerPopup.update({
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
	};

	BX.Calendar.UserField.ResourceBooking.prototype.getPlannerConfig = function()
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
				accuracy: 300
			};
		}

		this.params.plannerConfig.clickSelectorScaleAccuracy = Math.max((this.duration * 60) || 300, 3600);

		return this.params.plannerConfig;
	};

	BX.Calendar.UserField.ResourceBooking.prototype.plannerSelectorOnChange = function(params)
	{
		if (params.plannerId == this.params.plannerId)
		{
			var
				dateFrom = params.dateFrom,
				dateTo = params.dateTo;

			this.DOM.fromInput.value =  BX.date.format(DATE_FORMAT, dateFrom.getTime() / 1000);
			if (this.DOM.timeFromInput)
			{
				this.DOM.timeFromInput.value = BX.date.format(TIME_FORMAT_SHORT, dateFrom.getTime() / 1000);
			}

			// Duration in minutes
			if (this.params.fullDay)
			{
				this.duration = (dateTo.getTime() - dateFrom.getTime() + DAY_LENGTH) / 60000;
			}
			else
			{
				this.duration = (dateTo.getTime() - dateFrom.getTime()) / 60000;
			}
			this.duration = parseInt(Math.round(Math.max(this.duration, 0)));
			this.durationList.setValue(this.duration);

			this.onChangeValues();
		}
	};

	BX.Calendar.UserField.ResourceBooking.prototype.plannerSelectedEntriesOnChange = function(params)
	{
		if (params.plannerId == this.params.plannerId && BX.type.isArray(params.entries))
		{
			var
				selectedResources = [],
				selectedUsers = [];

			params.entries.forEach(function(entry)
			{
				if (entry.selected)
				{
					if (entry.type == 'user')
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
	};

	BX.Calendar.UserField.ResourceBooking.prototype.checkSelectorStatusCallback = function(params)
	{
		if (params.plannerId == this.params.plannerId && !this.params.allowOverbooking)
		{
			var errorClass = 'calendar-resbook-error';
			this.overbooked = params.status == 'busy';

			if (this.overbooked)
			{
				if (!this.DOM.errorNode)
				{
					this.DOM.errorNode = this.DOM.dateTimeWrap.appendChild(BX.create("div", {
						props: {className: "calendar-resbook-content-error-text"},
						text: BX.message('USER_TYPE_RESOURCE_BOOKED_ERROR')
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
	};

	BX.Calendar.UserField.ResourceBooking.prototype.getSelectorData = function()
	{
		var
			selector, dateTo,
			duration = this.duration,
			dateFrom = BX.parseDate(this.DOM.fromInput.value + (this.DOM.timeFromInput ? ' ' + this.DOM.timeFromInput.value : ''));

		if (!duration)
		{
			duration = this.params.fullDay ? 1440 : 60;
		}

		if (!BX.type.isDate(dateFrom))
		{
			dateFrom = new Date();
		}

		if (this.params.fullDay)
		{
			dateTo = new Date(dateFrom.getTime() + duration * 60000 - DAY_LENGTH)
		}
		else
		{
			dateTo = new Date(dateFrom.getTime() + duration * 60000);
		}

		selector = {
			from: dateFrom,
			to: dateTo,
			fullDay: this.params.fullDay,
			updateScaleLimits: true
		};

		return selector;
	};

	BX.Calendar.UserField.ResourceBooking.prototype.getResourceList = function()
	{
		var entries = [];
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
	};
	BX.Calendar.UserField.ResourceBooking.prototype.getSelectedResourceList = function()
	{
		var entries = [];
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
	};

	BX.Calendar.UserField.ResourceBooking.prototype.getUserList = function()
	{
		var entries = [], index = {}, userId;
		if (this.userSelector)
		{
			if (BX.type.isArray(this.params.userList))
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
				if (code.substr(0, 1) == 'U')
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
	};

	BX.Calendar.UserField.ResourceBooking.prototype.getSelectedUserList = function()
	{
		var entries = [];
		if (this.userSelector)
		{
			this.userSelector.getAttendeesCodesList().forEach(function(code)
			{
				if (code.substr(0, 1) == 'U')
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
	};

	BX.Calendar.UserField.ResourceBooking.USER_TYPE_ID = 'resourcebooking';

	BX.Calendar.UserField.ResourceBooking.openExternalSettingsSlider = function(params)
	{
		var settingsSlider = new SettingsSlider();
		settingsSlider.show(params);
	};


	BX.Calendar.UserField.ResourceBooking.getDurationList = function(fullDay)
	{
		var
			values = [5, 10, 15, 20, 25, 30, 40, 45, 50, 60, 90,
				120, 180, 240, 300, 360,
				1440, 1440 * 2, 1440 * 3, 1440 * 4, 1440 * 5, 1440 * 6, 1440 * 7, 1440 * 10],
			val, i, res = [];

		for (i = 0; i < values.length; i++)
		{
			val = values[i];

			// Days
			if (val % 1440 == 0)
			{
				res.push({value: val, label: BX.message('USER_TYPE_DURATION_X_DAY').replace('#NUM#', val / 1440)});
			}
			else if (!fullDay)
			{
				// Hours
				if (val % 60 == 0 && val != 60)
				{
					res.push({value: val, label: BX.message('USER_TYPE_DURATION_X_HOUR').replace('#NUM#', val / 60)});

				}
				// Minutes
				else
				{
					res.push({value: val, label: BX.message('USER_TYPE_DURATION_X_MIN').replace('#NUM#', val)});
				}
			}
		}
		return res;
	};

	BX.Calendar.UserField.ResourceBooking.parseDuration = function(value)
	{
		var
			stringValue = value,
			numValue = parseInt(value),
			parsed = false,
			dayRegexp = new RegExp('(\\d)\\s*(' + BX.message('USER_TYPE_DURATION_REGEXP_DAY') + ').*', 'ig'),
			hourRegexp = new RegExp('(\\d)\\s*(' + BX.message('USER_TYPE_DURATION_REGEXP_HOUR') + ').*', 'ig');

		value = value.replace(dayRegexp, function(str, num){parsed = true;return num;});
		// It's days
		if (parsed)
		{
			value = numValue * 1440;
		}
		else
		{
			value = stringValue.replace(hourRegexp, function(str, num){parsed = true;return num;});
			// It's hours
			if (parsed)
			{
				value = numValue * 60;
			}
			else // Minutes
			{
				value = numValue;
			}
		}

		return parseInt(value) || 0;
	};

	BX.Calendar.UserField.ResourceBooking.getSimpleTimeList = function(params)
	{
		var i, res = [];
		for (i = 0; i < 24; i++)
		{
			res.push({value: i * 60, label: this.formatTime(i, 0)});
			res.push({value: i * 60 + 30, label: this.formatTime(i, 30)});
		}
		BX.Calendar.UserField.ResourceBooking.getSimpleTimeList = function(){return res;};
		return res;
	};

	BX.Calendar.UserField.ResourceBooking.adaptTimeValue = function(timeValue)
	{
		timeValue = parseInt(timeValue.h * 60) + parseInt(timeValue.m);
		var
			timeList = BX.Calendar.UserField.ResourceBooking.getSimpleTimeList(),
			diff = 24 * 60,
			ind = false,
			i;

		for (i = 0; i < timeList.length; i++)
		{
			if (Math.abs(timeList[i].value - timeValue) < diff)
			{
				diff = Math.abs(timeList[i].value - timeValue);
				ind = i;
				if (diff <= 15)
					break;
			}
		}

		return timeList[ind || 0];
	};

	BX.Calendar.UserField.ResourceBooking.formatTime = function(h, m)
	{
		var d = new Date();
		d.setHours(h, m, 0);
		return BX.date.format(TIME_FORMAT_SHORT, d.getTime() / 1000);
	};

	BX.Calendar.UserField.ResourceBooking.getSocnetDestination = function()
	{
		if (this.prototype.socnetDestination)
		{
			return this.prototype.socnetDestination;
		}
		return null;
	};

	BX.Calendar.UserField.ResourceBooking.getLoader = function(size)
	{
		return BX.create('DIV', {props:{className: 'calendar-loader'}, html: '<svg class="calendar-loader-circular"' +
			(size ? 'style="width: '+ parseInt(size) +'px; height: '+ parseInt(size) +'px;"' : '') +
			' viewBox="25 25 50 50">' +
			'<circle class="calendar-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>' +
			'<circle class="calendar-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>' +
			'</svg>'});
	};

	BX.Calendar.UserField.ResourceBooking.getParamsFromHash = function(userfieldId)
	{
		var
			params, regRes,
			hash = unescape(window.location.hash);

		if (hash)
		{
			regRes = new RegExp('#calendar:' + userfieldId + '\\|(.*)', 'ig').exec(hash);
			if (regRes && regRes.length > 1)
			{
				params = regRes[1].split('|');
			}
		}
		return params;
	};

	BX.Calendar.UserField.ResourceBooking.showLimitationPopup = function()
	{
		if (window.B24 && B24.licenseInfoPopup)
		{
			BX.ajax.runAction('calendar.api.resourcebookingajax.initb24limitation', {})
				.then(function (response)
				{
					if (BX.type.isPlainObject(response.data))
					{
						B24.licenseInfoPopup.init(response.data);
						B24.licenseInfoPopup.show(
							'calendar_resourcebooking',
							BX.message('USER_TYPE_RESOURCE_B24_LIMITATION_TITLE'),
							BX.message('USER_TYPE_RESOURCE_B24_LIMITATION') +
								' <a href="javascript:void(0);" onclick="BX.Helper.show(\'redirect=detail&code=7481073\')">' +
							BX.message('USER_TYPE_RESOURCE_B24_LIMITATION_LINK') + '</a>');
					}
				});
		}
	};

	BX.Calendar.UserField.ResourceBooking.prototype.checkResourceCountLimit = function()
	{
		return this.params.resourceLimit <= 0 || this.getTotalResourceCount() < this.params.resourceLimit;
	};

	BX.Calendar.UserField.ResourceBooking.prototype.getTotalResourceCount = function()
	{
		var result = 0;
		if (this.params.useResources && this.resourceSelector)
		{
			result += this.resourceSelector.getValues().length;
		}

		if (this.params.useUsers)
		{
			result += this.getSelectedUserList().length;
		}

		return result;
	};


	// region ** Planner popup **
	function PlannerPopup() {}
	PlannerPopup.prototype = {
		show: function (params)
		{
			if (!params)
			{
				params = {};
			}
			this.params = params;
			this.bindNode = params.bindNode;
			this.plannerId = this.params.plannerId;
			this.config = this.params.plannerConfig;

			if (this.isShown() || !this.bindNode)
			{
				return;
			}

			if (this.lastPlannerIdShown && this.lastPlannerIdShown != this.plannerId)
			{
				this.close({animation: false});
			}

			this.currentEntries = [];

			this.plannerWrap = BX.create('DIV', {
				attrs: {
					id: this.plannerId,
					className: 'calendar-planner-wrapper'
				}
			});

			this.popup = new BX.PopupWindow(this.plannerId + "_popup",
				this.bindNode,
				{
					autoHide: false,
					closeByEsc: true,
					offsetTop: - parseInt(this.bindNode.offsetHeight) - 20,
					offsetLeft: this.bindNode.offsetWidth + 38,
					lightShadow: true,
					content: this.plannerWrap
				});

			this.popup.setAngle({offset: 100, position: 'left'});
			this.popup.show(true);
			this.lastPlannerIdShown = this.plannerId;

			var
				bindPos = BX.pos(this.bindNode),
				winSize = BX.GetWindowSize();

			this.plannerWidth = winSize.innerWidth - bindPos.right - 120;
			this.config.width = this.plannerWidth;

			setTimeout(BX.delegate(function(){
				BX.addClass(this.popup.popupContainer, 'calendar-resbook-planner-popup');
				this.popup.popupContainer.style.width = 0;
			}, this), 1);
			setTimeout(BX.delegate(function(){
				this.popup.popupContainer.style.width = this.plannerWidth + 'px';
				BX.addClass(this.popup.popupContainer, 'show');
				BX.bind(document, 'click', BX.proxy(this.handleClick, this));
			}, this), 50);
			setTimeout(BX.proxy(this.showPlanner, this), 350);

			BX.addCustomEvent(this.popup, 'onPopupClose', BX.proxy(this.close, this));
		},

		update: function (params, refreshParams)
		{
			if (!this.isShown())
			{
				return;
			}

			var
				codes = [], i, k, code,
				codeIndex = {},
				_this = this,
				plannerConfig = BX.clone(_this.config, true),
				fromTimestamp, toTimestamp,
				dateFrom, dateTo;

			if (BX.type.isPlainObject(this.lastUpdateParams) && BX.type.isPlainObject(params) && refreshParams !== true)
			{
				for (k in params)
				{
					if (params.hasOwnProperty(k))
					{
						this.lastUpdateParams[k] = params[k];
					}
				}
				params = this.lastUpdateParams;
			}

			// Save selector information
			if (BX.type.isPlainObject(params))
			{
				this.lastUpdateParams = params;
			}

			params.focusSelector = params.focusSelector !== false;

			if (params.from && params.to)
			{
				dateFrom = BX.parseDate(params.from);
				dateTo = BX.parseDate(params.to);
				fromTimestamp = dateFrom.getTime();
				toTimestamp = dateTo.getTime();
			}
			else
			{
				if (params.selector.fullDay)
				{
					fromTimestamp = params.selector.from.getTime() - DAY_LENGTH * 12;
					toTimestamp = params.selector.from.getTime() + DAY_LENGTH * 14;
				}
				else
				{
					fromTimestamp = params.selector.from.getTime() - DAY_LENGTH * 3;
					toTimestamp = params.selector.from.getTime() + DAY_LENGTH * 5;
				}

				dateFrom = new Date(fromTimestamp);
				dateTo = new Date(toTimestamp);

				plannerConfig.scaleDateFrom = dateFrom;
				plannerConfig.scaleDateTo = dateTo;
			}

			if (BX.type.isArray(params.userList))
			{
				for (i = 0; i < params.userList.length; i++)
				{
					code = 'U' + params.userList[i].id;
					if (!codeIndex[code])
					{
						codes.push(code);
						codeIndex[code] = true;
					}
				}
			}

			if (BX.type.isArray(params.selectedUsers))
			{
				for (i = 0; i < params.selectedUsers.length; i++)
				{
					code = 'U' + params.selectedUsers[i];
					if (!codeIndex[code])
					{
						codes.push(code);
						codeIndex[code] = true;
					}
				}
			}

			var requestData = {
				codes: codes,
				resources: params.resourceList,
				from: BX.date.format(DATE_FORMAT, fromTimestamp / 1000),
				to: BX.date.format(DATE_FORMAT, toTimestamp / 1000),
				currentEventList: this.params.currentEventList || []
			};

			if (this.checkUpdateParams(requestData) && this.isShown())
			{
				this.showPlannerLoader();
				BX.ajax.runAction('calendar.api.resourcebookingajax.getplannerdata', {
					data: requestData
				}).then(function (response)
				{
					_this.hidePlannerLoader();

					if (_this.lastRequestData)
					{
						_this.lastRequestData.response = response;
					}

					_this.currentEntries = response.data.entries;
					_this.currentAccessibility = response.data.accessibility;
					_this.currentLoadedDataFrom = dateFrom;
					_this.currentLoadedDataTo = dateTo;

					if (BX.type.isArray(response.data.entries))
					{
						response.data.entries.forEach(function(entry){
							if ((entry.type == 'user'
								&& params.selectedUsers.find(function(userId){return entry.id == userId;}))
								||
								(entry.type == 'resource'
								&& params.selectedResources.find(function(item){return entry.type == item.type && entry.id == item.id;}))
							)
							{
								entry.selected = true;
							}
							else
							{
								entry.selected = false;
							}
						});
					}

					if (_this.isShown())
					{
						BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
							{
								plannerId: _this.plannerId,
								config: plannerConfig,
								focusSelector: params.focusSelector,
								selector: {
									from: params.selector.from,
									to: params.selector.to,
									fullDay: params.selector.fullDay,
									animation: params.focusSelector,
									updateScaleLimits: params.focusSelector
								},
								data: {
									entries: response.data.entries,
									accessibility: response.data.accessibility
								},
								loadedDataFrom: dateFrom,
								loadedDataTo: dateTo,
								show: false
							}
						]);
					}
				},
				function (response) {
					/**
					 {
						 "status": "error",
						 "errors": [...]
					 }
					 **/
				});
			}
			else if (BX.type.isPlainObject(this.lastRequestData.response))
			{
				var response = this.lastRequestData.response;
				_this.currentEntries = response.data.entries;
				_this.currentAccessibility = response.data.accessibility;
				_this.currentLoadedDataFrom = dateFrom;
				_this.currentLoadedDataTo = dateTo;

				if (BX.type.isArray(response.data.entries))
				{
					response.data.entries.forEach(function(entry){
						if ((entry.type == 'user'
							&& params.selectedUsers.find(function(userId){return entry.id == userId;}))
							||
							(entry.type == 'resource'
							&& params.selectedResources.find(function(item){return entry.type == item.type && entry.id == item.id;}))
						)
						{
							entry.selected = true;
						}
						else
						{
							entry.selected = false;
						}
					});
				}

				if (this.isShown())
				{
					BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
						{
							plannerId: _this.plannerId,
							config: plannerConfig,
							focusSelector: params.focusSelector,
							selector: {
								from: params.selector.from,
								to: params.selector.to,
								fullDay: params.selector.fullDay,
								animation: params.focusSelector,
								updateScaleLimits: params.focusSelector
							},
							data: {
								entries: response.data.entries,
								accessibility: response.data.accessibility
							},
							loadedDataFrom: dateFrom,
							loadedDataTo: dateTo,
							show: false
						}
					]);
				}
			}
		},

		checkUpdateParams: function (requestData)
		{
			var requestPlannerUpdate = false;
			if (!this.lastRequestData || this.lastRequestPlannerId !== this.plannerId)
			{
				requestPlannerUpdate = true;
			}

			// 1. Compare dates
			if (!requestPlannerUpdate && requestData.from != this.lastRequestData.from)
			{
				requestPlannerUpdate = true;
			}
			// 2. Compare users
			if (!requestPlannerUpdate
				&& BX.type.isArray(requestData.codes) && BX.type.isArray(this.lastRequestData.codes)
				&& BX.util.array_diff(requestData.codes, this.lastRequestData.codes).length > 0
			)
			{
				requestPlannerUpdate = true;
			}

			// 3. Compare resources
			if (!requestPlannerUpdate && BX.type.isArray(requestData.resources) && BX.type.isArray(this.lastRequestData.resources))
			{
				if (requestData.resources.length != this.lastRequestData.resources.length)
				{
					requestPlannerUpdate = true;
				}
				else
				{
					var resIndex = {};
					requestData.resources.forEach(function (res)
					{
						resIndex[res.type + '_' + res.id] = true
					});

					this.lastRequestData.resources.forEach(function(res)
					{
						if (!resIndex[res.type + '_' + res.id])
						{
							requestPlannerUpdate = true;
						}
					});
				}
			}

			// Save request data for future comparing
			if (requestPlannerUpdate)
			{
				this.lastRequestData = requestData;
				this.lastRequestPlannerId = this.plannerId;
			}

			return requestPlannerUpdate;
		},

		showPlanner: function ()
		{
			this.planner = new CalendarPlanner(
				this.params.plannerConfig,
				{
					config: this.config,
					data: {
						accessibility: this.currentAccessibility || {},
						entries: this.currentEntries
					},
					selector: {
						from: this.params.selector.from,
						to: this.params.selector.to,
						fullDay: this.params.selector.fullDay,
						updateScaleLimits: true,
						updateScaleType: false,
						focus: true,
						RRULE: false,
						animation: false
					},
					loadedDataFrom: this.currentLoadedDataFrom,
					loadedDataTo: this.currentLoadedDataTo,
					focusSelector: true,
					plannerId: this.plannerId,
					show: true
				}
			);

			// planner events
			if (BX.type.isFunction(this.params.selectorOnChangeCallback))
			{
				BX.addCustomEvent('OnCalendarPlannerSelectorChanged', this.params.selectorOnChangeCallback);
			}
			if (BX.type.isFunction(this.params.selectEntriesOnChangeCallback))
			{
				BX.addCustomEvent('OnCalendarPlannerSelectedEntriesOnChange', this.params.selectEntriesOnChangeCallback);
			}
			if (BX.type.isFunction(this.params.checkSelectorStatusCallback))
			{
				BX.addCustomEvent('OnCalendarPlannerSelectorStatusOnChange', this.params.checkSelectorStatusCallback);
			}

			BX.addCustomEvent('OnCalendarPlannerScaleChanged', BX.proxy(function(params)
			{
				this.update({
					from: params.from,
					to: params.to,
					focusSelector: params.focusSelector === true
				});
			}, this));

		},

		showPlannerLoader: function ()
		{
			if (this.planner && this.planner.outerWrap)
			{
				if (this.loader)
				{
					BX.remove(this.loader);
				}
				this.loader = this.planner.outerWrap.appendChild(BX.Calendar.UserField.ResourceBooking.getLoader(150));
			}
		},

		hidePlannerLoader: function ()
		{
			if (this.loader)
			{
				BX.remove(this.loader);
				this.loader = false;
			}
		},

		close: function(params)
		{
			if (this.popup)
			{
				if (params && params.animation)
				{
					BX.removeClass(this.popup.popupContainer, 'show');
					setTimeout(BX.delegate(function()
					{
						params.animation = false;
						this.close(params);
					}, this), 300);
				}
				else
				{
					BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
					BX.removeCustomEvent(this.popup, 'onPopupClose', BX.proxy(this.close, this));
					this.popup.destroy();
					this.planner = null;
					this.popup = null;
				}
			}
		},

		isShown: function()
		{
			return this.lastPlannerIdShown == this.plannerId
				&& this.popup && this.popup.isShown && this.popup.isShown();
		},

		getPlannerId: function()
		{
			if (typeof this.plannerId === 'undefined')
			{
				this.plannerId = 'calendar-planner-' + Math.round(Math.random() * 100000);
			}
			return this.plannerId;
		},

		getPlannerContainer: function()
		{
			return BX('calendar-planner-outer' + this.getPlannerId(), true);
		},

		refreshDateTimeView: function(params)
		{
		},

		handleClick: function(e)
		{
			var target = e.target || e.srcElement;
			if (this.isShown()
				&& !BX.isParentForNode(this.bindNode, target)
				&& !BX.isParentForNode(BX('BXSocNetLogDestination'), target)
				&& !BX.isParentForNode(this.popup.popupContainer, target)
			)
			{
				if (!document.querySelector('div.popup-window-resource-select'))
				{
					this.close({animation: true});
				}
			}
		}
	};
	// endregion

	function SettingsSlider(params)
	{
		this.id = 'calendar_custom_settings_' + Math.round(Math.random() * 1000000);
		this.zIndex = 3100;
		this.sliderId = "calendar:resbook-settings-slider";

		this.SLIDER_WIDTH = 400;
		this.SLIDER_DURATION = 80;
		this.DOM = {};
	}

	SettingsSlider.prototype = {
		show: function (params)
		{
			this.params = params;

			BX.SidePanel.Instance.open(this.sliderId, {
				contentCallback: BX.delegate(this.create, this),
				width: this.SLIDER_WIDTH,
				animationDuration: this.SLIDER_DURATION
			});

			BX.addCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
			BX.addCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
		},

		close: function ()
		{
			BX.SidePanel.Instance.close();
		},

		hide: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				if (this.denyClose)
				{
					event.denyAction();
				}
				else
				{
					BX.removeCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
				}
			}
		},

		destroy: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
				BX.SidePanel.Instance.destroy(this.sliderId);
				//this.calendar.enableKeyHandler();
			}
		},

		create: function ()
		{
			var promise = new BX.Promise();

			var html = '<div class="webform-buttons calendar-form-buttons-fixed">' +
				'<span id="' + this.id + '_save" class="webform-small-button webform-small-button-blue">' + BX.message('USER_TYPE_RESOURCE_SAVE') + '</span>' +
				'<span id="' + this.id + '_close" class="webform-button-link">' + BX.message('USER_TYPE_RESOURCE_CLOSE') + '</span>' +
			'</div>' +
			'<div class="calendar-slider-calendar-wrap">' +
				'<div class="calendar-slider-header"><div class="calendar-head-area"><div class="calendar-head-area-inner"><div class="calendar-head-area-title">' +
				'<span class="calendar-head-area-name">' + BX.message('USER_TYPE_RESOURCE_SETTINGS') + 			'</span>' +
				'</div></div></div></div>' +
				'<div class="resource-booking-slider-workarea"><div class="resource-booking-slider-content"><div id="' + this.id + '_content" class="resource-booking-settings"></div></div></div></div>';

			promise.fulfill(BX.util.trim(html));
			setTimeout(BX.delegate(this.initControls, this), 100);
			//this.initControls();

			return promise;
		},

		initControls: function ()
		{
			this.DOM.content = BX(this.id + '_content');

			BX.bind(BX(this.id + '_save'), 'click', BX.proxy(this.save, this));
			BX.bind(BX(this.id + '_close'), 'click', BX.proxy(this.close, this));

			// 1. Field
			if (this.params && BX.type.isArray(this.params.filterSelectValues))
			{
				this.DOM.fieldOuterWrap = this.DOM.content.appendChild(BX.create('DIV', {attrs: {className: 'calendar-settings-control'}}));
				this.DOM.fieldOuterWrap.appendChild(BX.create('DIV', {
					attrs: {className: 'calendar-settings-control-name'},
					text: BX.message('USER_TYPE_RESOURCE_FILTER_NAME')
				}));
				this.DOM.fieldSelect = this.DOM.fieldOuterWrap.appendChild(BX.create('DIV', {attrs: {className: 'calendar-field-container calendar-field-container-select'}}))
					.appendChild(BX.create('DIV', {attrs: {className: 'calendar-field-block'}}))
					.appendChild(BX.create('select', {attrs: {className: 'calendar-field calendar-field-select'}}));

				this.params.filterSelectValues.forEach(function(value){
					this.DOM.fieldSelect.options.add(
						new Option(value.TEXT, value.VALUE, this.params.filterSelect == value.VALUE, this.params.filterSelect == value.VALUE));
				}, this);
			}
		},

		save: function ()
		{
			var entityType = this.params.entityType || 'none';
			BX.userOptions.save('calendar', 'resourceBooking', entityType, this.DOM.fieldSelect.value);
			this.close();
			BX.reload();
		}
	};


	function UserSelector(params)
	{
		this.params = params || {};
		this.id = this.params.id || 'user-selector-' + Math.round(Math.random() * 100000);
		this.wrapNode = this.params.wrapNode;
		this.zIndex = this.params.zIndex || 3100;
		this.destinationInputName = this.params.inputName || 'EVENT_DESTINATION';
		this.params.selectGroups = false;
		this.addMessage = this.params.addMessage || BX.message('USER_TYPE_RESOURCE_ADD_USER');
		this.checkLimit = BX.type.isFunction(params.checkLimitCallback) ? params.checkLimitCallback : false;

		if (BX.type.isArray(this.params.itemsSelected))
		{
			this.params.itemsSelected = this.convertAttendeesCodes(this.params.itemsSelected);
		}
		else
		{
			this.params.itemsSelected = this.getSocnetDestinationConfig('itemsSelected');
		}

		this.DOM = {
			outerWrap: this.params.outerWrap,
			wrapNode: this.params.wrapNode
		};

		this.create();
	}

	UserSelector.prototype = {
		create: function ()
		{
			if (this.DOM.outerWrap)
			{
				BX.addClass(this.DOM.outerWrap, 'calendar-resourcebook-folding-block' + (this.params.shown !== false ? ' shown' : ''));
			}

			var id = this.id;

			BX.bind(this.wrapNode, 'click', BX.delegate(function (e)
			{
				var target = e.target || e.srcElement;
				if (target.className == 'calendar-resourcebook-content-block-control-delete') // Delete button
				{
					BX.SocNetLogDestination.deleteItem(target.getAttribute('data-item-id'), target.getAttribute('data-item-type'), id);
					var block = BX.findParent(target, {className: 'calendar-resourcebook-content-block-control-inner'});
					if (block && BX.hasClass(block, 'shown'))
					{
						BX.removeClass(block, 'shown');
						setTimeout(function(){BX.remove(block);}, 300);
					}
				}
				else
				{
					BX.SocNetLogDestination.openDialog(id);
				}
			}, this));

			this.socnetDestinationInputWrap = this.wrapNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-resourcebook-destination-input-box'}}));
			this.socnetDestinationInput = this.socnetDestinationInputWrap.appendChild(BX.create('INPUT', {
				props: {
					id: id + '-inp',
					className: 'calendar-resourcebook-destination-input'
				},
				attrs: {
					value: '',
					type: 'text'
				},
				events: {
					keydown: function (e)
					{
						return BX.SocNetLogDestination.searchBeforeHandler(e, {
							formName: id, inputId: id + '-inp'
						});
					}, keyup: function (e)
					{
						return BX.SocNetLogDestination.searchHandler(e, {
							formName: id,
							inputId: id + '-inp',
							linkId: 'event-grid-dest-add-link',
							sendAjax: true
						});
					}
				}
			}));

			this.socnetDestinationLink = this.wrapNode.appendChild(BX.create('DIV', {
				props: {className: 'calendar-resourcebook-content-block-control-text calendar-resourcebook-content-block-control-text-add'},
				text: this.addMessage
			}));

			//if (this.params.itemsSelected && !this.checkItemsSelected(
			//		this.getSocnetDestinationConfig('items'),
			//		this.getSocnetDestinationConfig('itemsLast'),
			//		this.getSocnetDestinationConfig('itemsSelected'),
			//		BX.proxy(this.init, this)
			//	))
			//{
			//	return;
			//}

			this.init();
		},

		show: function ()
		{
			if (this.DOM.outerWrap)
			{
				BX.addClass(this.DOM.outerWrap, 'shown');
			}
		},

		hide: function ()
		{
			if (this.DOM.outerWrap)
			{
				BX.removeClass(this.DOM.outerWrap, 'shown');
			}
		},

		isShown: function ()
		{
			if (this.DOM.outerWrap)
			{
				return BX.hasClass(this.DOM.outerWrap, 'shown');
			}
		},

		init: function ()
		{
			if (!this.socnetDestinationInput || !this.wrapNode)
				return;

			var _this = this;

			this.params.items = this.getSocnetDestinationConfig('items');
			this.params.itemsLast = this.getSocnetDestinationConfig('itemsLast');

			if (this.params.selectGroups === false)
			{
				this.params.items.groups = {};
				this.params.items.department = {};
				this.params.items.sonetgroups = {};
			}

			BX.SocNetLogDestination.init({
				name: this.id,
				searchInput: this.socnetDestinationInput,
				extranetUser: false,
				userSearchArea: 'I',
				bindMainPopup: {
					node: this.wrapNode, offsetTop: '5px', offsetLeft: '15px'
				},
				bindSearchPopup: {
					node: this.wrapNode, offsetTop: '5px', offsetLeft: '15px'
				},
				callback: {
					select: BX.proxy(this.selectCallback, this),
					unSelect: BX.proxy(this.unSelectCallback, this),
					openDialog: BX.proxy(this.openDialogCallback, this),
					closeDialog: BX.proxy(this.closeDialogCallback, this),
					openSearch: BX.proxy(this.openDialogCallback, this),
					closeSearch: function ()
					{
						_this.closeDialogCallback(true);
					}
				},
				items: this.params.items,
				itemsLast: this.params.itemsLast,
				itemsSelected: this.params.itemsSelected,
				departmentSelectDisable: this.params.selectGroups === false
			});
		},

		//checkItemsSelected: function (items, itemsLast, selected, callback)
		//{
		//	var codes = [];
		//	for (var code in selected)
		//	{
		//		if (selected.hasOwnProperty(code))
		//		{
		//			if (selected[code] == 'users' && !items.users[code])
		//			{
		//				codes.push(code);
		//			}
		//		}
		//	}
		//
		//	return;
		//
		//	if (codes.length > 0)
		//	{
		//		var loader = this.wrapNode.appendChild(BX.adjust(this.calendar.util.getLoader(40), {style: {height: '50px'}}));
		//
		//		this.calendar.request({
		//			type: 'get', data: {
		//				action: 'get_destination_items', codes: codes
		//			}, handler: BX.delegate(function (response)
		//			{
		//				if (loader)
		//					BX.remove(loader);
		//
		//				//this.calendar.util.mergeSocnetDestinationConfig(response.destinationItems);
		//				//this.params.items = this.calendar.util.getSocnetDestinationConfig('items');
		//				//this.params.itemsLast = this.calendar.util.getSocnetDestinationConfig('itemsLast');
		//
		//				if (callback && typeof callback == 'function')
		//					callback();
		//			}, this)
		//		});
		//		return false;
		//	}
		//
		//	return true;
		//},

		closeAll: function ()
		{
			if (BX.SocNetLogDestination.isOpenDialog())
			{
				BX.SocNetLogDestination.closeDialog();
			}
			BX.SocNetLogDestination.closeSearch();
		},

		selectCallback: function(item, type)
		{
			if (type == 'users')
			{
				this.addUserBlock(item);
				BX.onCustomEvent('OnDestinationAddNewItem', [item]);
				this.socnetDestinationInput.value = '';
			}
		},

		addUserBlock: function(item, animation)
		{
			if (this.checkLimit && !this.checkLimit())
			{
				return BX.Calendar.UserField.ResourceBooking.showLimitationPopup();
			}
			var i, blocks = this.wrapNode.querySelectorAll('.calendar-resourcebook-content-block-control-inner.shown');
			for (i = 0; i < blocks.length; i++)
			{
				if (blocks[i].getAttribute('data-id') == item.id)
				{
					BX.remove(blocks[i]);
				}
			}

			var itemWrap = this.wrapNode.appendChild(BX.create("DIV", {
				attrs: {
					'data-id': item.id, className: "calendar-resourcebook-content-block-control-inner green"
				},
				html: '<div class="calendar-resourcebook-content-block-control-text">' + item.name + '</div>' + '<div data-item-id="' + item.id + '" data-item-type="users" class="calendar-resourcebook-content-block-control-delete"></div>' + '<input type="hidden" name="' + this.destinationInputName + '[U][]' + '" value="' + item.id + '">'
			}));

			if (animation !== false)
			{
				setTimeout(BX.delegate(function (){BX.addClass(itemWrap, 'shown');}, this), 1);
			}
			else
			{
				BX.addClass(itemWrap, 'shown');
			}

			this.wrapNode.appendChild(this.socnetDestinationInputWrap);
			this.wrapNode.appendChild(this.socnetDestinationLink);
		},

		unSelectCallback: function(item, type, search)
		{
			var elements = BX.findChildren(this.wrapNode, {attribute: {'data-id': item.id}}, true);
			if (elements != null)
			{
				for (var j = 0; j < elements.length; j++)
				{
					BX.remove(elements[j]);
				}
			}

			BX.onCustomEvent('OnDestinationUnselect');
			this.socnetDestinationInput.value = '';
			this.socnetDestinationLink.innerHTML = this.addMessage;
		},

		openDialogCallback: function ()
		{
			if (BX.SocNetLogDestination.popupWindow)
			{
				// Fix zIndex for slider issues
				BX.SocNetLogDestination.popupWindow.params.zIndex = this.zIndex;
				BX.SocNetLogDestination.popupWindow.popupContainer.style.zIndex = this.zIndex;
			}

			if (BX.SocNetLogDestination.popupSearchWindow)
			{
				// Fix zIndex for slider issues
				BX.SocNetLogDestination.popupSearchWindow.params.zIndex = this.zIndex;
				BX.SocNetLogDestination.popupSearchWindow.popupContainer.style.zIndex = this.zIndex;
			}

			BX.style(this.socnetDestinationInputWrap, 'display', 'inline-block');
			BX.style(this.socnetDestinationLink, 'display', 'none');
			BX.focus(this.socnetDestinationInput);
		},

		closeDialogCallback: function(cleanInputValue)
		{
			if (!BX.SocNetLogDestination.isOpenSearch() && this.socnetDestinationInput.value.length <= 0)
			{
				BX.style(this.socnetDestinationInputWrap, 'display', 'none');
				BX.style(this.socnetDestinationLink, 'display', 'inline-block');
				if (cleanInputValue === true)
					this.socnetDestinationInput.value = '';

				// Disable backspace
				if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null)
					BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);

				BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(e)
				{
					if (e.keyCode == 8)
					{
						e.preventDefault();
						return false;
					}
				});

				setTimeout(function()
				{
					BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
					BX.SocNetLogDestination.backspaceDisable = null;
				}, 5000);
			}
		},

		getCodes: function()
		{
			var
				inputsList = this.wrapNode.getElementsByTagName('INPUT'),
				codes = [], i, value;

			for (i = 0; i < inputsList.length; i++)
			{
				value = BX.util.trim(inputsList[i].value);
				if (value)
				{
					codes.push(inputsList[i].value);
				}
			}
			return codes;
		},

		getAttendeesCodes: function()
		{
			var
				inputsList = this.wrapNode.getElementsByTagName('INPUT'),
				values = [],
				i;

			for (i = 0; i < inputsList.length; i++)
			{
				values.push(inputsList[i].value);
			}

			return this.convertAttendeesCodes(values);
		},

		convertAttendeesCodes: function(values)
		{
			var attendeesCodes = {};

			if (BX.type.isArray(values))
			{
				values.forEach(function(code){
					if (code.substr(0, 2) == 'DR')
					{
						attendeesCodes[code] = "department";
					}
					else if (code.substr(0, 2) == 'UA')
					{
						attendeesCodes[code] = "groups";
					}
					else if (code.substr(0, 2) == 'SG')
					{
						attendeesCodes[code] = "sonetgroups";
					}
					else if (code.substr(0, 1) == 'U')
					{
						attendeesCodes[code] = "users";
					}
				});
			}

			return attendeesCodes;
		},

		getAttendeesCodesList: function(codes)
		{
			var result = [];
			if (!codes)
				codes = this.getAttendeesCodes();
			for (var i in codes)
			{
				if (codes.hasOwnProperty(i))
				{
					result.push(i);
				}
			}
			return result;
		},

		getSocnetDestinationConfig: function(key)
		{
			var
				res,
				socnetDestination = this.params.socnetDestination || {};

			if (key == 'items')
			{
				res = {
					users: socnetDestination.USERS || {},
					groups: socnetDestination.EXTRANET_USER == 'Y' || socnetDestination.DENY_TOALL
						? {}
						: {UA: {id: 'UA', name: BX.message('USER_TYPE_RESOURCE_TO_ALL_USERS')}},
					sonetgroups: socnetDestination.SONETGROUPS || {},
					department: socnetDestination.DEPARTMENT || {},
					departmentRelation: socnetDestination.DEPARTMENT_RELATION || {}
				};
			}
			else if (key == 'itemsLast' && socnetDestination.LAST)
			{
				res = {
					users: socnetDestination.LAST.USERS || {},
					groups: socnetDestination.EXTRANET_USER == 'Y' ? {} : {UA: true},
					sonetgroups: socnetDestination.LAST.SONETGROUPS || {},
					department: socnetDestination.LAST.DEPARTMENT || {}
				};
			}
			else if (key == 'itemsSelected')
			{
				res = socnetDestination.SELECTED || {};
			}
			return res || {};
		},

		getSelectedValues: function()
		{
			var
				result = [], i,
				inputs = this.wrapNode.querySelectorAll('input');

			for (i = 0; i < inputs.length; i++)
			{
				if (inputs[i].type == 'hidden' && inputs[i].value)
				{
					if (inputs[i].value.substr(0, 1) == 'U')
					{
						result.push(parseInt(inputs[i].value.substr(1)));
					}
				}
			}

			return result;
		},

		setValues: function(userList, trigerOnChange)
		{
			var blocks, i, user;
			blocks = this.wrapNode.querySelectorAll('.calendar-resourcebook-content-block-control-inner.shown');
			for (i = 0; i < blocks.length; i++)
			{
				BX.remove(blocks[i]);
			}

			for (i = 0; i < userList.length; i++)
			{
				if (BX.SocNetLogDestination.obItems[this.id]['users'])
				{
					user = BX.SocNetLogDestination.obItems[this.id]['users']['U' + userList[i]];
					if (user)
					{
						this.addUserBlock({
							id: 'U' + userList[i],
							name: user.name
						}, false);
					}
				}
			}

			if (trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback))
			{
				setTimeout(BX.proxy(this.onChangeCallback, this), 100);
			}
		}
	};


	function ResourceListSelector(params)
	{
		this.params = params || {};
		this.editMode = !!this.params.editMode;
		this.id = this.params.id || 'resource-selector-' + Math.round(Math.random() * 100000);
		this.resourceList = BX.type.isArray(params.resourceList) ? params.resourceList : [];
		this.checkLimit = BX.type.isFunction(params.checkLimitCallback) ? params.checkLimitCallback : false;

		this.selectedValues = [];
		this.selectedValuesIndex = {};

		this.selectedBlocks = [];
		this.newValues = [];

		this.DOM = {
			outerWrap: this.params.outerWrap,
			blocksWrap: this.params.blocksWrap || false,
			listWrap: this.params.listWrap
		};

		if (this.editMode)
		{
			this.DOM.controlsWrap = this.params.controlsWrap;
		}
		else
		{
			this.DOM.arrowNode = BX.create("span", {props: {className: "calendar-resourcebook-content-block-detail-icon calendar-resourcebook-content-block-detail-icon-arrow"}});
		}

		this.onChangeCallback = this.params.onChangeCallback || null;

		this.create();
		this.setValues(params.values);
	}

	ResourceListSelector.prototype = {
		create: function ()
		{
			BX.addClass(this.DOM.outerWrap, 'calendar-resourcebook-resource-list-wrap calendar-resourcebook-folding-block' + (this.params.shown !== false ? ' shown' : ''));

			if (this.editMode)
			{
				this.DOM.addButton = this.DOM.controlsWrap.appendChild(BX.create("span", {
					props: {className: "calendar-resource-content-block-add-link"},
					text: BX.message('USER_TYPE_RESOURCE_ADD'),
					events: {click: BX.delegate(this.addResourceBlock, this)}
				}));

				if (this.resourceList.length > 0)
				{
					this.DOM.selectButton = this.DOM.controlsWrap.appendChild(BX.create("span", {
						props: {className: "calendar-resource-content-block-add-link"},
						text: BX.message('USER_TYPE_RESOURCE_SELECT'),
						events: {click: BX.delegate(this.openResourcesPopup, this)}
					}));
				}
			}
			else
			{
				BX.bind(this.DOM.blocksWrap, 'click', BX.delegate(this.handleBlockClick, this));
			}
		},
		show: function ()
		{
			BX.addClass(this.DOM.outerWrap, 'shown');
		},

		hide: function ()
		{
			BX.removeClass(this.DOM.outerWrap, 'shown');
		},

		isShown: function ()
		{
			return BX.hasClass(this.DOM.outerWrap, 'shown');
		},

		handleBlockClick: function (e)
		{
			var target = e.target || e.srcElement;

			if (target)
			{
				var blockValue = target.getAttribute('data-bx-remove-block');
				if (blockValue)
				{
					// Remove from blocks
					this.selectedBlocks.find(function(element, index)
					{
						if (element.value == blockValue)
						{
							BX.removeClass(element.wrap, 'shown');
							setTimeout(BX.delegate(function ()
							{
								BX.remove(element.wrap)
							}, this), 300);

							this.selectedBlocks = BX.util.deleteFromArray(this.selectedBlocks, index);
						}
					}, this);

					// Remove from values
					this.selectedValues.find(function(element, index)
					{
						if (element.title == blockValue)
						{
							this.selectedValues = BX.util.deleteFromArray(this.selectedValues, index);
						}
					}, this);

					if (BX.type.isFunction(this.onChangeCallback))
					{
						setTimeout(BX.proxy(this.onChangeCallback, this), 100);
					}

					this.checkBlockWrapState();
				}

				if (!blockValue)
				{
					this.openResourcesPopup();
				}
			}
		},

		openResourcesPopup: function ()
		{
			if (!this.resourceList.length)
			{
				return this.addResourceBlock();
			}

			if (this.isResourcesPopupShown())
			{
				return;
			}

			var menuItems = [];

			this.resourceList.forEach(function(resource)
			{
				if (resource.deleted)
				{
					return;
				}

				menuItems.push({
					text: BX.util.htmlspecialchars(resource.title),
					dataset: {
						type: resource.type,
						id: resource.id,
						title: resource.title
					},
					onclick: BX.delegate(function(e, menuItem)
					{
						var
							selectAllcheckbox,
							target = e.target || e.srcElement,
							checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox'),
							foundResource = this.resourceList.find(function(resource)
							{
								return resource.id == menuItem.dataset.id && resource.type == menuItem.dataset.type;
							}, this);

						if (foundResource)
						{
							// Complete removing of the resource
							if (target && BX.hasClass(target, "calendar-resourcebook-content-block-control-delete"))
							{
								this.removeResourceBlock({
									resource: foundResource,
									trigerOnChange: true
								});
								this.selectedValues = this.getSelectedValues();
								this.checkResourceInputs();

								selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
								this.selectAllChecked = false;
								if (selectAllcheckbox)
								{
									selectAllcheckbox.checked = false;
								}

								var menuItemNode = BX.findParent(target, {className: 'menu-popup-item'});
								if (menuItemNode)
								{
									BX.addClass(menuItemNode, 'menu-popup-item-resource-remove-loader');

									var loader = menuItemNode.appendChild(BX.Calendar.UserField.ResourceBooking.getLoader(25));
									var textNode = menuItemNode.querySelector('.menu-popup-item-text');
									if (textNode)
									{
										textNode.innerHTML = BX.message('USER_TYPE_RESOURCE_DELETING');
									}
								}

								foundResource.deleted = true;
								setTimeout(BX.delegate(function()
								{
									if (menuItemNode)
									{
										menuItemNode.style.maxHeight = '0';
									}

									if (!this.resourceList.find(function(resource){return !resource.deleted;}))
									{
										BX.PopupMenu.destroy(this.id);
										this.DOM.selectButton.style.opacity = 0;

										setTimeout(BX.delegate(function(){BX.remove(this.DOM.selectButton);}, this), 500);
									}
								}, this), 500);
							}
							else if (target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox") || BX.hasClass(target, "menu-popup-item-inner") ))
							{
								if (!BX.hasClass(target, "menu-popup-item-resource-checkbox"))
								{
									checkbox.checked = !checkbox.checked;
								}

								if (checkbox.checked)
								{
									this.addResourceBlock({
										resource: foundResource,
										value: foundResource.title,
										trigerOnChange: true
									});
									this.selectedValues = this.getSelectedValues();
								}
								else
								{
									this.removeResourceBlock({
										resource: foundResource,
										trigerOnChange: true
									});
									this.selectedValues = this.getSelectedValues();
									this.checkResourceInputs();

									selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
									this.selectAllChecked = false;
									if (selectAllcheckbox)
									{
										selectAllcheckbox.checked = false;
									}
								}
							}
						}
					}, this)
				});
			}, this);

			if (menuItems.length > 1)
			{
				menuItems.push({
					text: BX.message('USER_TYPE_RESOURCE_SELECT_ALL'),
					onclick: BX.delegate(function(e, menuItem)
					{
						var target = e.target || e.srcElement;
						if (target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox")))
						{
							var checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

							if (BX.hasClass(target, "menu-popup-item"))
							{
								checkbox.checked = !checkbox.checked;
							}

							var i, checkboxes = this.popupContainer.querySelectorAll('input.menu-popup-item-resource-checkbox');
							this.selectAllChecked = checkbox.checked;

							for (i = 0; i < checkboxes.length; i++)
							{
								checkboxes[i].checked = this.selectAllChecked;
							}

							this.resourceList.forEach(function(resource){
								if (resource.deleted)
								{
									return;
								}

								if (this.selectAllChecked)
								{
									this.addResourceBlock({
										resource: resource,
										value: resource.title,
										trigerOnChange: true
									});
								}
								else
								{
									this.removeResourceBlock({
										resource: resource,
										trigerOnChange: true
									});
								}
							}, this);

							this.selectedValues = this.getSelectedValues();
							this.checkResourceInputs();
						}
					}, this)
				});
			}

			this.popup = BX.PopupMenu.create(
				this.id,
				this.DOM.selectButton || this.DOM.blocksWrap,
				menuItems,
				{
					className: 'popup-window-resource-select',
					closeByEsc : true,
					autoHide : false,
					offsetTop: 0,
					offsetLeft: 0
				}
			);

			this.popup.show(true);
			this.popupContainer = this.popup.popupWindow.popupContainer;
			if (!this.editMode)
			{
				this.popupContainer.style.width = parseInt(this.DOM.blocksWrap.offsetWidth) + 'px';
			}

			BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function(){BX.PopupMenu.destroy(this.id);}, this));

			this.popup.menuItems.forEach(function(menuItem)
			{
				var checked;
				if (menuItem.dataset && menuItem.dataset.type)
				{
					checked = this.selectedValues.find(function(item){return item.id == menuItem.dataset.id && item.type == menuItem.dataset.type});
					menuItem.layout.item.className = 'menu-popup-item';
					menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
						'<div class="menu-popup-item-resource">' +
							'<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
							'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + BX.util.htmlspecialchars(menuItem.dataset.title) + '</label>' +
					'</div>' +
					(this.editMode ? '<div class="calendar-resourcebook-content-block-control-delete"></div>' : '') +
					'</div>';
				}
				else
				{
					this.selectAllChecked = !this.resourceList.find(function(resource){
						return !this.selectedValues.find(function(item){return item.id == resource.id && item.type == resource.type});
					},this);

					menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
					menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
						'<div class="menu-popup-item-resource">' +
						'<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
						'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + BX.message('USER_TYPE_RESOURCE_SELECT_ALL') + '</label>' +
						'</div>' +
						'</div>';
				}
			}, this);

			setTimeout(BX.delegate(function(){
				BX.bind(document, 'click', BX.proxy(this.handleClick, this));
			}, this), 50);
		},

		addResourceBlock: function(params)
		{
			if (this.checkLimit && !this.checkLimit())
			{
				return BX.Calendar.UserField.ResourceBooking.showLimitationPopup();
			}

			if (!BX.type.isPlainObject(params))
			{
				params = {};
			}

			var
				_this = this,
				blockEntry;

			if (this.editMode)
			{
				if (params.resource && this.selectedValues.find(function(val){return val.id && val.id == params.resource.id && val.type == params.resource.type;}))
				{
					return;
				}

				if (!params.value)
				{
					params.value = '';
				}

				blockEntry = {
					value: params.value,
					wrap : this.DOM.listWrap
						.appendChild(BX.create("div", {props:{className: "calendar-resourcebook-content-block-detail calendar-resourcebook-outer-resource-wrap"}}))
						.appendChild(BX.create("div", {props:{className: "calendar-resourcebook-content-block-detail-resource"}}))
						.appendChild(BX.create("div", {props:{className: "calendar-resourcebook-content-block-detail-resource-inner calendar-resourcebook-content-block-detail-resource-inner-wide"}}))
				};

				blockEntry.input = blockEntry.wrap.appendChild(BX.create("input", {
					props:{
						className: "calendar-resourcebook-content-input",
						value: params.value,
						type: 'text',
						placeholder: BX.message('USER_TYPE_RESOURCE_NAME')
					},
					dataset: {
						resourceType: params.resource ? params.resource.type : '',
						resourceId: params.resource ? params.resource.id : ''
					}
				}));
				blockEntry.delButton = blockEntry.wrap.appendChild(BX.create("div", {
					props:{className: "calendar-resourcebook-content-block-control-delete"},
					events: {click: function(){
						BX.remove(BX.findParent(this, {className: 'calendar-resourcebook-outer-resource-wrap'}));
						_this.selectedValues = _this.getSelectedValues();
						_this.checkResourceInputs();
					}}
				}));

				if (params.focusInput !== false)
				{
					BX.focus(blockEntry.input);
				}
			}
			else
			{
				if (params.value && this.selectedBlocks.find(function(val){return val.value && val.value == params.value;}))
				{
					return;
				}

				blockEntry = {
					value: params.value,
					resource: params.resource || false,
					wrap : this.DOM.blocksWrap.appendChild(BX.create("div", {
						props:{
							className: "calendar-resourcebook-content-block-control-inner"
							+ (params.animation ? '' : ' shown')
							+ (params.transparent ? ' transparent' : '')
						},
						children: [
							BX.create("div", {
								props: {className: "calendar-resourcebook-content-block-control-text"},
								text: params.value || ''
							}),
							BX.create("div", {
								attrs: {'data-bx-remove-block': params.value},
								props: {className: "calendar-resourcebook-content-block-control-delete"}
							})
						]
					}))
				};

				this.selectedBlocks.push(blockEntry);

				// Show it with animation
				if (params.animation)
				{
					setTimeout(BX.delegate(function ()
					{
						BX.addClass(blockEntry.wrap, 'shown');
					}, this), 1);
				}

				if (params.trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback))
				{
					setTimeout(BX.proxy(this.onChangeCallback, this), 100);
				}

				this.checkBlockWrapState();
			}

			return blockEntry;
		},

		removeResourceBlock: function(params)
		{
			if (this.editMode)
			{
				var
					resourceType, resourceId,
					i, inputs = this.DOM.listWrap.querySelectorAll('.calendar-resourcebook-content-input');

				for (i = 0; i < inputs.length; i++)
				{
					resourceType = inputs[i].getAttribute('data-resource-type');
					resourceId = inputs[i].getAttribute('data-resource-id');
					if (resourceType == params.resource.type && resourceId == params.resource.id)
					{
						BX.remove(BX.findParent(inputs[i], {className: 'calendar-resourcebook-outer-resource-wrap'}));
					}
				}
			}
			else
			{
				if (params.resource)
				{
					this.selectedBlocks.find(function(element, index)
					{
						if (element.value == params.resource.title)
						{
							BX.removeClass(element.wrap, 'shown');
							setTimeout(BX.delegate(function ()
							{
								BX.remove(element.wrap)
							}, this), 300);

							this.selectedBlocks = BX.util.deleteFromArray(this.selectedBlocks, index);
						}
					}, this);
				}
				this.checkBlockWrapState();

				if (params.trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback))
				{
					setTimeout(BX.proxy(this.onChangeCallback, this), 100);
				}
			}
		},

		checkResourceInputs: function()
		{
			if (this.editMode)
			{
				if (!this.selectedValues.length)
				{
					this.addResourceBlock({animation: true});
				}
			}
		},

		checkBlockWrapState: function()
		{
			if (!this.editMode)
			{
				if (!this.selectedBlocks.length)
				{
					if (!this.DOM.emptyPlaceholder)
					{
						this.DOM.emptyPlaceholder = this.DOM.blocksWrap.appendChild(
							BX.create("DIV", {
								props : {className : "calendar-resourcebook-content-block-control-empty"},
								html: '<span class="calendar-resourcebook-content-block-control-text">' + BX.message('USER_TYPE_RESOURCE_LIST_PLACEHOLDER') + '</span>'
							})
						);
					}
					else
					{
						this.DOM.emptyPlaceholder.className = "calendar-resourcebook-content-block-control-empty";
						this.DOM.blocksWrap.appendChild(this.DOM.emptyPlaceholder);
					}

					setTimeout(BX.delegate(function(){
						if (BX.isNodeInDom(this.DOM.emptyPlaceholder))
						{
							BX.addClass(this.DOM.emptyPlaceholder, 'show');
						}
					}, this), 50);
				}
				else if (this.DOM.emptyPlaceholder)
				{
					BX.remove(this.DOM.emptyPlaceholder);
				}
			}
		},

		handleClick: function(e)
		{
			var target = e.target || e.srcElement;
			if (this.isResourcesPopupShown() && !BX.isParentForNode(this.popupContainer, target)
			)
			{
				this.closeResourcesPopup({animation: true});
			}
		},

		isResourcesPopupShown: function()
		{
			return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown();
		},

		closeResourcesPopup: function(params)
		{
			if (this.popup)
			{
				//if (params && params.animation)
				//{
				//	BX.removeClass(this.popupContainer, 'shown');
				//	this.popupContainer.style.maxHeight = '';
				//	setTimeout(BX.delegate(function()
				//	{
				//		params.animation = false;
				//		this.closeResourcesPopup(params);
				//	}, this), 300);
				//}
				//else
				//{
					this.popup.close();
					this.popupContainer.style.maxHeight = '';
					BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
				//}
			}
		},

		getValues: function()
		{
			return this.resourceList;
		},

		addToSelectedValues: function(value)
		{
			if (!this.selectedValues.find(function(val){return val.id && val.id == value.id && val.type == value.type;}))
			{
				this.selectedValues.push(value);
			}
		},

		getSelectedValues: function()
		{
			this.selectedValues = [];
			if (this.editMode)
			{
				var
					resourceType, resourceId, i,
					inputs = this.DOM.listWrap.querySelectorAll('.calendar-resourcebook-content-input');

				for (i = 0; i < inputs.length; i++)
				{
					resourceType = inputs[i].getAttribute('data-resource-type');
					resourceId = inputs[i].getAttribute('data-resource-id');
					if (resourceType && resourceId)
					{
						this.selectedValues.push({type: resourceType, id: resourceId, title: inputs[i].value});
					}
					else
					{
						this.selectedValues.push({type: 'resource', title: inputs[i].value});
					}
				}
			}
			else
			{
				this.selectedBlocks.forEach(function(element){
					this.selectedValues.push({type: element.resource.type, id: element.resource.id});
				}, this);
			}

			return this.selectedValues;
		},

		getDeletedValues: function()
		{
			return this.resourceList.filter(function(resource){return resource.deleted;});
		},

		setValues: function(values, trigerOnChange)
		{
			this.selectedBlocks.forEach(function(element){BX.remove(element.wrap);});
			this.selectedBlocks = [];
			trigerOnChange = trigerOnChange !== false;

			if (BX.type.isArray(values))
			{
				values.forEach(function(value)
				{
					var foundResource = this.resourceList.find(function(resource)
					{
						return resource.id == value.id && resource.type == value.type;
					}, this);

					if (foundResource)
					{
						this.addResourceBlock({
							resource: foundResource,
							value: foundResource.title,
							trigerOnChange: trigerOnChange
						});
						this.addToSelectedValues(foundResource);
					}
				}, this);
			}

			if (this.editMode)
			{
				this.selectedValues = this.getSelectedValues();
				this.checkResourceInputs();
			}
			else
			{
				if (this.DOM.arrowNode)
				{
					this.DOM.blocksWrap.appendChild(this.DOM.arrowNode);
				}
			}

			this.checkBlockWrapState();
		}
	};

	function SelectInput(params)
	{
		this.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);

		if (BX.type.isFunction(params.getValues))
		{
			this.getValues = params.getValues;
			this.values = this.getValues();
		}
		else
		{
			this.values = params.values || false;
		}

		this.input = params.input;
		this.defaultValue = params.defaultValue || '';
		this.openTitle = params.openTitle || '';
		this.className = params.className || '';
		this.currentValue = params.value;
		this.currentValueIndex = params.valueIndex;
		this.onChangeCallback = params.onChangeCallback || null;
		this.onAfterMenuOpen = params.onAfterMenuOpen || null;
		this.zIndex = params.zIndex || 1200;
		this.disabled = params.disabled;
		if (this.onChangeCallback)
		{
			BX.bind(this.input, 'change', this.onChangeCallback);
			BX.bind(this.input, 'keyup', this.onChangeCallback);
		}

		this.curInd = false;

		if (BX.type.isArray(this.values))
		{
			BX.bind(this.input, 'click', BX.proxy(this.onClick, this));
			BX.bind(this.input, 'focus', BX.proxy(this.onFocus, this));
			BX.bind(this.input, 'blur', BX.proxy(this.onBlur, this));
			BX.bind(this.input, 'keyup', BX.proxy(this.onKeyup, this));

			if (this.currentValueIndex === undefined && this.currentValue !== undefined)
			{
				this.currentValueIndex = -1;
				for (var i = 0; i < this.values.length; i++)
				{
					if (this.values[i].value == this.currentValue)
					{
						this.currentValueIndex = i;
						break;
					}
				}

				if (this.currentValueIndex == -1)
				{
					this.currentValueIndex = undefined;
				}
			}
		}

		if (this.currentValueIndex !== undefined && this.values[this.currentValueIndex])
		{
			this.input.value = this.values[this.currentValueIndex].label;
		}
	}

	SelectInput.prototype = {
		showPopup: function()
		{
			if (this.getValues)
			{
				this.values = this.getValues();
			}

			if (this.shown || this.disabled || !this.values.length)
			{
				return;
			}

			var
				ind = 0,
				j = 0,
				menuItems = [],
				i, _this = this;

			for (i = 0; i < this.values.length; i++)
			{
				if (this.values[i].delimiter)
				{
					menuItems.push(this.values[i]);
				}
				else
				{
					if ((this.currentValue && this.values[i] && this.values[i].value == this.currentValue.value)
					|| this.input.value == this.values[i].label)
					{
						ind = j;
					}

					menuItems.push({
						id: this.values[i].value + '_' + i,
						text: this.values[i].label,
						onclick: this.values[i].callback || (function (value, label)
						{
							return function ()
							{
								_this.input.value = label;
								_this.popupMenu.close();
								_this.onChange(value, label);
							}
						})(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
					});
					j++;
				}
			}

			this.popupMenu = BX.PopupMenu.create(
				this.id,
				this.input,
				menuItems,
				{
					closeByEsc : true,
					autoHide : true,
					zIndex: this.zIndex,
					offsetTop: 0,
					offsetLeft: 0
				}
			);
			this.popupMenu.popupWindow.setWidth(this.input.offsetWidth - 2);

			var menuContainer = this.popupMenu.layout.menuContainer;
			BX.addClass(this.popupMenu.layout.menuContainer, 'calendar-resourcebook-select-popup');
			this.popupMenu.show();

			var menuItem = this.popupMenu.menuItems[ind];
			if (menuItem && menuItem.layout)
			{
				menuContainer.scrollTop = menuItem.layout.item.offsetTop - 2;
			}

			BX.addCustomEvent(this.popupMenu.popupWindow, 'onPopupClose', function()
			{
				BX.PopupMenu.destroy(_this.id);
				_this.shown = false;
			});

			this.input.select();

			if (BX.type.isFunction(this.onAfterMenuOpen))
			{
				this.onAfterMenuOpen(ind, this.popupMenu);
			}

			this.shown = true;
		},

		closePopup: function()
		{
			BX.PopupMenu.destroy(this.id);
			this.shown = false;
		},

		onFocus: function()
		{
			setTimeout(BX.delegate(function(){
				if (!this.shown)
				{
					this.showPopup();
				}
			}, this), 200);
		},

		onClick: function()
		{
			if (this.shown)
			{
				this.closePopup();
			}
			else
			{
				this.showPopup();
			}
		},

		onBlur: function()
		{
			setTimeout(BX.delegate(this.closePopup, this), 200);
		},

		onKeyup: function()
		{
			setTimeout(BX.delegate(this.closePopup, this), 50);
		},

		onChange: function(value, label)
		{
			var val = this.input.value;
			BX.onCustomEvent(this, 'onSelectInputChanged', [this, val, value]);
			if (this.onChangeCallback && typeof this.onChangeCallback == 'function')
			{
				this.onChangeCallback({value: val, realValue: value});
			}
		},

		destroy: function()
		{
			if (this.onChangeCallback)
			{
				BX.unbind(this.input, 'change', this.onChangeCallback);
				BX.unbind(this.input, 'keyup', this.onChangeCallback);
			}

			BX.unbind(this.input, 'click', BX.proxy(this.onClick, this));
			BX.unbind(this.input, 'focus', BX.proxy(this.onFocus, this));
			BX.unbind(this.input, 'blur', BX.proxy(this.onBlur, this));
			BX.unbind(this.input, 'keyup', BX.proxy(this.onKeyup, this));

			if (this.popupMenu)
				this.popupMenu.close();
			BX.PopupMenu.destroy(this.id);
			this.shown = false;
		},

		setValue: function(value)
		{
			this.input.value = value;
			if (BX.type.isArray(this.values))
			{
				var currentValueIndex = -1;
				for (var i = 0; i < this.values.length; i++)
				{
					if (this.values[i].value == value)
					{
						currentValueIndex = i;
						break;
					}
				}

				if (currentValueIndex !== -1)
				{
					this.input.value = this.values[currentValueIndex].label;
					this.currentValueIndex = currentValueIndex;
				}
			}
		}
	};

	function ModeSelector(params)
	{
		this.params = params;
		this.outerWrap = this.create();
	}

	ModeSelector.prototype = {
		create: function()
		{
			var
				wrapNode = BX.create("span",
					{
						props:{className: "calendar-resourcebook-content-block-select calendar-resourcebook-mode-selector"}
					}
				),
				menuItems = [
					{
						text: BX.message('USER_TYPE_RESOURCE_CHOOSE_RESOURCES'),
						onclick: BX.delegate(function(e, item){
							if (BX.type.isFunction(this.params.showResources))
							{
								this.params.showResources();
							}
							wrapNode.innerHTML = item.text;
							this.modeSwitcherPopup.close();
						}, this)
					},
					{
						text: BX.message('USER_TYPE_RESOURCE_CHOOSE_USERS'),
						onclick: BX.delegate(function(e, item){
							if (BX.type.isFunction(this.params.showUsers))
							{
								this.params.showUsers();
							}
							wrapNode.innerHTML = item.text;
							this.modeSwitcherPopup.close();
						}, this)
					},
					{
						text: BX.message('USER_TYPE_RESOURCE_CHOOSE_RESOURCES_AND_USERS'),
						onclick: BX.delegate(function(e, item){
							if (BX.type.isFunction(this.params.showResourcesAndUsers))
							{
								this.params.showResourcesAndUsers();
							}
							wrapNode.innerHTML = item.text;
							this.modeSwitcherPopup.close();
						}, this)
					}
				],
				switcherId = 'mode-switcher-' + Math.round(Math.random() * 100000);


			BX.bind(wrapNode, 'click', BX.proxy(function(){
				if (this.modeSwitcherPopup && this.modeSwitcherPopup.popupWindow && this.modeSwitcherPopup.popupWindow.isShown())
				{
					return this.modeSwitcherPopup.close();
				}

				this.modeSwitcherPopup = BX.PopupMenu.create(
					switcherId,
					wrapNode,
					menuItems,
					{
						closeByEsc : true,
						autoHide : true,
						offsetTop: 0,
						offsetLeft: 20,
						angle: true
					}
				);

				this.modeSwitcherPopup.show();

				BX.addCustomEvent(this.modeSwitcherPopup.popupWindow, 'onPopupClose', BX.delegate(function()
				{
					BX.PopupMenu.destroy(switcherId);
				}, this));
			}, this));

			if (this.params.useUsers && !this.params.useResources)
			{
				wrapNode.innerHTML = BX.message('USER_TYPE_RESOURCE_CHOOSE_USERS');
			}
			else if (this.params.useUsers && this.params.useResources)
			{
				wrapNode.innerHTML = BX.message('USER_TYPE_RESOURCE_CHOOSE_RESOURCES_AND_USERS');
			}
			else
			{
				wrapNode.innerHTML = BX.message('USER_TYPE_RESOURCE_CHOOSE_RESOURCES');
			}

			return wrapNode;
		},

		getOuterWrap: function()
		{
			return this.outerWrap;
		}
	};


	function ServiceList(params)
	{
		this.params = BX.type.isPlainObject(params) ? params : {};
		this.outerCont = this.params.outerCont;
		this.fieldSettings = this.params.fieldSettings || {};
		this.create();
	}

	ServiceList.prototype = {
		create: function()
		{
			this.serviceListOuterWrap = this.outerCont.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-service-list-wrap"}}));

			this.show(this.fieldSettings.USE_SERVICES == 'Y');
			this.durationTitleId = 'duration-title-wrap-' + Math.round(Math.random() * 100000);
			this.servicesTitleWrap = this.serviceListOuterWrap
				.appendChild(BX.create("div", {
					props: {className: "calendar-resourcebook-content-block-detail-inner"},
					html: '<div class="calendar-resourcebook-content-block-detail-resource">' +
					'<div class="calendar-resourcebook-content-block-title">' +
					'<span class="calendar-resourcebook-content-block-title-text">' + BX.message('USER_TYPE_RESOURCE_SERVICE_LABEL') + '</span>' +
					'</div>' +
					'<div id="' + this.durationTitleId + '" class="calendar-resourcebook-content-block-title calendar-resourcebook-content-block-duration-title">' +
					'<span class="calendar-resourcebook-content-block-title-text">' + BX.message('USER_TYPE_RESOURCE_DURATION_LABEL') + '</span>' +
					'</div>' +
					'</div>'
				}));

			this.serviceListRowsWrap = this.serviceListOuterWrap
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-detail-inner"}}))
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-detail"}}));

			BX.bind(this.serviceListRowsWrap, 'click', BX.delegate(this.handlePopupClick, this));
			if (BX.type.isArray(this.fieldSettings.SERVICE_LIST) && this.fieldSettings.SERVICE_LIST.length > 0)
			{
				this.fieldSettings.SERVICE_LIST.forEach(function(service)
				{
					this.addRow(service, false);
				}, this);
			}
			else
			{
				this.addRow(false, false);
			}

			this.serviceListAddWrap = this.serviceListOuterWrap.appendChild(BX.create("div", {props: {className: "calendar-resource-content-block-add-field"}}));

			this.serviceAddButton = this.serviceListAddWrap.appendChild(BX.create("span", {
				props: {className: "calendar-resource-content-block-add-link calendar-resource-content-block-add-link-icon"},
				text: BX.message('USER_TYPE_RESOURCE_ADD_SERVICE'),
				events: {click: BX.delegate(this.addRow, this)}
			}));

			BX.bind(window, 'resize', BX.proxy(this.checkDurationTitlePosition, this));
			this.checkDurationTitlePosition();
		},

		show: function(show)
		{
			if (show)
			{
				this.serviceListOuterWrap.style.display = '';
				BX.addClass(this.serviceListOuterWrap, 'show');
			}
			else
			{
				this.serviceListOuterWrap.style.display = 'none';
				BX.removeClass(this.serviceListOuterWrap, 'show');
			}
		},

		addRow: function(row, animation)
		{
			animation = animation !== false;

			if (!BX.type.isPlainObject(row))
			{
				row = {name: '', duration: this.getDefaultDuration()}
			}

			var service = {
				outerWrap: this.serviceListRowsWrap
					.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-detail-resource calendar-resourcebook-service-row"}}))
			};

			if (animation)
			{
				setTimeout(function(){
					BX.addClass(service.outerWrap, 'show');
				}, 1);
			}
			else
			{
				BX.addClass(service.outerWrap, 'show');
			}

			service.wrap = service.outerWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-detail-resource-inner"}}));

			service.nameInput = service.wrap.appendChild(BX.create("input", {
				props: {
					className: "calendar-resourcebook-content-input calendar-resourcebook-service-input",
					placeholder: BX.message('USER_TYPE_RESOURCE_SERVICE_PLACEHOLDER'),
					type: "text",
					value: row.name
				},
				attrs: {}
			}));

			service.durationInput = service.wrap.appendChild(BX.create("input", {
				props: {
					className: "calendar-resbook-duration-input calendar-resbook-field-datetime-menu",
					type: "text",
					value: row.duration
				},
				attrs: {}
			}));

			service.durationList = new BX.Calendar.UserField.ResourceBooking.SelectInput({
				input: service.durationInput,
				getValues: BX.proxy(function(){
					var fullday = false;
					if (BX.type.isFunction(this.params.getFullDayValue))
					{
						fullday = this.params.getFullDayValue();
					}
					return BX.Calendar.UserField.ResourceBooking.getDurationList(fullday);
				}, this),
				value: row.duration
			});

			service.deleteWrap = service.wrap.appendChild(BX.create("DIV", {
				props: {className: "calendar-resourcebook-content-block-detail-delete"},
				html: '<span class="calendar-resourcebook-content-block-control-delete calendar-resourcebook-content-block-control-delete-detail"></span>'
			}));
		},

		checkDurationTitlePosition: function(timeout)
		{
			if (timeout !== false)
			{
				if (this.checkDurationTitlePositionTimeout)
				{
					clearTimeout(this.checkDurationTitlePositionTimeout);
				}

				this.checkDurationTitlePositionTimeout = setTimeout(BX.delegate(function(){

					this.checkDurationTitlePosition(false);
				}, this), 100);
				return;
			}

			var durationInput = this.serviceListOuterWrap.querySelector('input.calendar-resbook-duration-input');
			if (this.durationTitleId && durationInput)
			{
				BX(this.durationTitleId).style.left = (durationInput.offsetLeft + 15) + 'px';
			}
		},

		getDefaultDuration: function()
		{
			var fullday = false;
			if (BX.type.isFunction(this.params.getFullDayValue))
			{
				fullday = this.params.getFullDayValue();
			}
			return fullday ? 1440 : 30;
		},

		clickHandler: function(e)
		{
			var target = e.target || e.srcElement;
			if (BX.hasClass(target, 'calendar-resourcebook-content-block-control-delete')
				|| BX.hasClass(target, 'calendar-resourcebook-content-block-detail-delete')) // Delete button
			{
				var resWrap = BX.findParent(target, {className: 'calendar-resourcebook-service-row'});
				if (resWrap)
				{
					BX.removeClass(resWrap, 'show');
					setTimeout(function(){BX.remove(resWrap);}, 500);
					this.checkServiceRows();
				}
			}
		},

		getValues: function(e)
		{
			var
				serviceList = [],
				nameInput, durationInput,
				i, rows = this.serviceListRowsWrap.querySelectorAll('.calendar-resourcebook-service-row');

			for (i = 0; i < rows.length; i++)
			{
				if (BX.hasClass(rows[i], 'show'))
				{
					nameInput = rows[i].querySelector('input.calendar-resourcebook-service-input');
					durationInput = rows[i].querySelector('input.calendar-resbook-duration-input');

					if (nameInput && durationInput)
					{
						serviceList.push({
							name: nameInput.value,
							duration: BX.Calendar.UserField.ResourceBooking.parseDuration(durationInput.value)
						});
					}
				}
			}

			return serviceList;
		},

		checkRows: function()
		{
			var serviceList = this.getValues();
			if (!serviceList.length)
			{
				this.show(false);
				if (BX.type.isFunction(this.params.onFullClearHandler))
				{
					this.params.onFullClearHandler();
				}
				this.addRow(false, false);
			}
		},


		handlePopupClick: function(e)
		{
			var target = e.target || e.srcElement;
			if (BX.hasClass(target, 'calendar-resourcebook-content-block-control-delete')
				|| BX.hasClass(target, 'calendar-resourcebook-content-block-detail-delete')) // Delete button
			{
				var resWrap = BX.findParent(target, {className: 'calendar-resourcebook-service-row'});
				if (resWrap)
				{
					BX.removeClass(resWrap, 'show');
					setTimeout(function(){BX.remove(resWrap);}, 500);
					this.checkRows();
				}
			}
		}
	};


	function AdminSettingsViewer(params)
	{
		this.params = BX.type.isPlainObject(params) ? params : {};

		this.DOM = {
			outerWrap: BX(this.params.outerWrapId),
			form: document.forms[this.params.formName]
		};
	}
	AdminSettingsViewer.prototype = {
		showLayout: function()
		{
			if (!this.DOM.outerWrap || !this.DOM.form)
				return;

			BX.bind(this.DOM.form, 'submit', BX.proxy(this.onSubmit, this));

			BX.addClass(this.DOM.outerWrap, 'calendar-resourcebook-content calendar-resourcebook-content-admin-settings');

			this.DOM.innerWrap = this.DOM.outerWrap
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-wrap"}}))
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-inner"}}));

			var
				fieldSettings = BX.type.isPlainObject(this.params.settings) ? this.params.settings : {},
				resourceList = [],
				selectedResourceList = [];
			// region Users&Resources Mode selector
			this.DOM.innerWrap.appendChild(
				BX.create(
					"div",
					{
						props: { className: "calendar-resourcebook-content-block" },
						children:
							[
								BX.create(
									"span",
									{
										props: {className: "calendar-resourcebook-content-block-title-text"},
										text: BX.message('USER_TYPE_RESOURCE_CHOOSE')
									}
								),
								new ModeSelector({
									useResources: fieldSettings.USE_RESOURCES == 'Y',
									useUsers: fieldSettings.USE_USERS == 'Y',
									showUsers: BX.delegate(function(){
										this.resourceList.hide();
										this.userList.show();
									}, this),
									showResources: BX.delegate(function(){
										this.resourceList.show();
										this.userList.hide();
									}, this),
									showResourcesAndUsers: BX.delegate(function(){
										this.resourceList.show();
										this.userList.show();
									}, this)
								}).getOuterWrap()
							]
					}
				)
			);
			// endregion

			this.DOM.optionWrap = this.DOM.innerWrap.appendChild(BX.create(
				"div",
				{
					props: { className: "calendar-resourcebook-content-block" }
				}
			));

			// region Use Resources Option
			this.resourcesWrap = this.DOM.optionWrap.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"}}));

			this.resourcesTitleWrap = this.resourcesWrap
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: BX.message('USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME') + ':'}));

			this.resourcesListWrap = this.resourcesWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-new-entries-wrap calendar-resourcebook-content-block-detail-inner"}}));

			this.resourcesListLowControls = this.resourcesWrap.appendChild(BX.create("div", {props: {className: "calendar-resource-content-block-add-field"}}));

			if (fieldSettings.RESOURCES
				&& BX.type.isPlainObject(fieldSettings.RESOURCES['resource'])
				&& BX.type.isArray(fieldSettings.RESOURCES['resource'].SECTIONS))
			{
				fieldSettings.RESOURCES['resource'].SECTIONS.forEach(function(resource)
				{
					resourceList.push({
						id: resource.ID,
						title: resource.NAME,
						type: resource.CAL_TYPE
					});
				});
			}

			if (BX.type.isArray(fieldSettings.SELECTED_RESOURCES))
			{
				fieldSettings.SELECTED_RESOURCES.forEach(function(resource)
				{
					selectedResourceList.push({
						id: resource.id,
						type: resource.type
					});
				});
			}

			this.resourceList = new BX.Calendar.UserField.ResourceBooking.ResourceListSelector({
				shown: fieldSettings.USE_RESOURCES == 'Y',
				editMode: true,
				outerWrap: this.resourcesWrap,
				listWrap: this.resourcesListWrap,
				controlsWrap: this.resourcesListLowControls,
				values: selectedResourceList,
				resourceList: resourceList,
				checkLimitCallback: BX.proxy(this.checkResourceCountLimit, this)
			});
			// endregion

			// region Users Selector
			this.userSelectorWrap = this.DOM.optionWrap.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"}}));

			this.usersTitleWrap = this.userSelectorWrap
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
				.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: BX.message('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME') + ':'}));

			this.usersListWrap = this.userSelectorWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-control custom-field-item"}}));

			var itemsSelected = [];
			if (BX.type.isArray(fieldSettings.SELECTED_USERS))
			{
				fieldSettings.SELECTED_USERS.forEach(function(user)
				{
					itemsSelected.push('U' + parseInt(user));
				});
			}

			this.userList = new BX.Calendar.UserField.ResourceBooking.UserSelector({
				shown: fieldSettings.USE_USERS == 'Y',
				outerWrap: this.userSelectorWrap,
				wrapNode: this.usersListWrap,
				socnetDestination: this.params.socnetDestination,
				itemsSelected: itemsSelected
			});
			// endregion

			// Region Data, Time and services
			this.DOM.optionWrap.appendChild(
				BX.create("hr", { props: { className: "calendar-resbook-hr"}})
			);

			this.datetimeOptionsWrap = this.DOM.optionWrap.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add" }}));

			this.datetimeOptionsWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title"}})).appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: BX.message('USER_TYPE_RESOURCE_DATETIME_BLOCK_TITLE') + ':'}));

			this.datetimeOptionsInnerWrap = this.datetimeOptionsWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-options"}}));
			// endregion

			//region Checkbox "Full day"
			this.DOM.fulldayCheckBox = BX.create(
				"input",
				{
					props: { type: "checkbox", checked: fieldSettings.FULL_DAY == 'Y'}
				}
			);

			this.datetimeOptionsInnerWrap.appendChild(
				BX.create(
					"label",
					{
						props: {className: 'calendar-resourcebook-content-block-option'},
						children:
							[
								this.DOM.fulldayCheckBox,
								BX.create("span", { text: BX.message('USER_TYPE_RESOURCE_FULL_DAY') })
							]
					}
				)
			);
			//endregion

			//region Checkbox "Add services"
			this.DOM.useServicedayCheckBox = BX.create(
				"input",
				{
					props: {
						type: "checkbox",
						checked: fieldSettings.USE_SERVICES == 'Y'
					},
					events: {
						'click' : BX.delegate(function(){
							if (this.serviceList)
							{
								this.serviceList.show(this.DOM.useServicedayCheckBox.checked);
							}
						}, this)
					}
				}
			);

			this.datetimeOptionsInnerWrap.appendChild(
				BX.create(
					"label",
					{
						props: {className: 'calendar-resourcebook-content-block-option'},
						children:
							[
								this.DOM.useServicedayCheckBox,
								BX.create("span", { text: BX.message('USER_TYPE_RESOURCE_ADD_SERVICES') })
							]
					}
				)
			);

			this.serviceList = new ServiceList({
				outerCont: this.datetimeOptionsInnerWrap,
				fieldSettings: fieldSettings,
				getFullDayValue: BX.proxy(function(){return this.DOM.fulldayCheckBox.checked}, this)
			});
			//endregion

			this.DOM.optionWrap.appendChild(
				BX.create("hr", { props: { className: "calendar-resbook-hr"}})
			);
			//region Checkbox "Overbooking"
			this.DOM.overbookingCheckbox = BX.create("input", {props: {type: "checkbox", checked: fieldSettings.ALLOW_OVERBOOKING == 'Y'}});

			this.DOM.optionWrap.appendChild(
				BX.create(
					"label",
					{
						props: {className: 'calendar-resourcebook-content-block-option'},
						children:
							[
								this.DOM.overbookingCheckbox,
								BX.create("span", { text: BX.message('USER_TYPE_RESOURCE_OVERBOOKING') })
							]
					}
				)
			);
			//endregion
		},

		onSubmit: function(e)
		{
			if (!this.DOM.inputsWrap)
			{
				this.DOM.inputsWrap = this.DOM.outerWrap.appendChild(BX.create("DIV"));
			}
			else
			{
				BX.cleanNode(this.DOM.inputsWrap);
			}

			var inputName = this.params.htmlControl.NAME;
			this.DOM.inputsWrap.appendChild(BX.create('INPUT', {
				attrs:{
					name: inputName + '[USE_USERS]',
					value: this.userList.isShown() ? 'Y' : 'N',
					type: 'hidden'
				}}));

			this.DOM.inputsWrap.appendChild(BX.create('INPUT', {
				attrs:{
					name: inputName + '[USE_RESOURCES]',
					value: this.resourceList.isShown() ? 'Y' : 'N',
					type: 'hidden'
				}}));

			this.DOM.inputsWrap.appendChild(BX.create('INPUT', {
				attrs:{
					name: inputName + '[USE_SERVICES]',
					value: this.DOM.useServicedayCheckBox.checked ? 'Y' : 'N',
					type: 'hidden'
				}}));

			this.DOM.inputsWrap.appendChild(BX.create('INPUT', {
				attrs:{
					name: inputName + '[FULL_DAY]',
					value: this.DOM.fulldayCheckBox.checked ? 'Y' : 'N',
					type: 'hidden'
				}}));

			this.DOM.inputsWrap.appendChild(BX.create('INPUT', {
				attrs:{
					name: inputName + '[ALLOW_OVERBOOKING]',
					value: this.DOM.overbookingCheckbox.checked ? 'Y' : 'N',
					type: 'hidden'
				}}));

			// Selected resources
			if (this.resourceList)
			{
				this.prepareFormDataInputs(this.DOM.inputsWrap, this.resourceList.getSelectedValues().concat(this.resourceList.getDeletedValues()), inputName + '[SELECTED_RESOURCES]');
			}

			// // Selected users
			if (this.userList)
			{
				var SELECTED_USERS = [];
				this.userList.getAttendeesCodesList().forEach(function(code)
				{
					if (code.substr(0, 1) == 'U')
					{
						SELECTED_USERS.push(parseInt(code.substr(1)));
					}
				}, this);

				this.prepareFormDataInputs(this.DOM.inputsWrap, SELECTED_USERS, inputName + '[SELECTED_USERS]');
			}

			if (this.DOM.useServicedayCheckBox.checked && this.serviceList)
			{
				this.prepareFormDataInputs(this.DOM.inputsWrap, this.serviceList.getValues(), inputName + '[SERVICE_LIST]');
			}
		},

		prepareFormDataInputs: function(wrap, data, inputName)
		{
			data.forEach(function(value, ind)
			{
				if (BX.type.isPlainObject(value))
				{
					var k;
					for (k in value)
					{
						if (value.hasOwnProperty(k))
						{
							wrap.appendChild(BX.create('INPUT', {
								attrs:{
									name: inputName + '[' + ind + '][' + k + ']',
									value: value[k],
									type: 'hidden'
								}}));
						}
					}
				}
				else
				{
					wrap.appendChild(BX.create('INPUT', {
						attrs:{
							name: inputName + '[' + ind + ']',
							value: value,
							type: 'hidden'
						}}));
				}
			}, this);
		}
	};

	BX.Calendar.UserField.ResourceBooking.SelectInput = SelectInput;
	BX.Calendar.UserField.ResourceBooking.ResourceListSelector = ResourceListSelector;
	BX.Calendar.UserField.ResourceBooking.UserSelector = UserSelector;
	BX.Calendar.UserField.ResourceBooking.ServiceList = ServiceList;
	BX.Calendar.UserField.ResourceBooking.ModeSelector = ModeSelector;
	BX.Calendar.UserField.ResourceBooking.AdminSettingsViewer = AdminSettingsViewer;

	BX.Calendar.UserField.ResourceBooking.plannerPopup = new PlannerPopup();

	if (!Array.prototype.find) {
		Object.defineProperty(Array.prototype, 'find', {
			value: function(predicate) {
				if (this == null) {
					throw new TypeError('"this" is null or not defined');
				}
				var o = Object(this);
				var len = o.length >>> 0;
				if (typeof predicate !== 'function') {
					throw new TypeError('predicate must be a function');
				}
				var thisArg = arguments[1];
				var k = 0;
				while (k < len) {
					var kValue = o[k];
					if (predicate.call(thisArg, kValue, k, o)) {
						return kValue;
					}
					k++;
				}
				return undefined;
			}
		});
	}
})();