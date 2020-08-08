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

	}
	BX.Calendar.UserField.CrmFormResourceBookingFieldLiveController = CrmFormResourceBookingFieldLiveController;
	CrmFormResourceBookingFieldLiveController.prototype = {
		init: function()
		{
			this.previewFieldLayout = new ResourceBookingFieldLiveViewLayout({
				wrap: this.params.wrap,
				title: this.getCaption(),
				settings: this.getSettings()
			});
			this.previewFieldLayout.build();
		},

		getCaption: function()
		{
			return this.params.field.caption;
		},

		getSettings: function()
		{
			return {
				caption: this.getCaption(),
				data: this.params.field.settings_data,
				userfieldSettings: {
					useUsers: true,
					useResources: true
				}
			};
		}
	};
	// endregion

	// region *ResourceBookingFieldLayoutAbstract*
	function ResourceBookingFieldLayoutAbstract(config)
	{
		this.settings = config.settings || {};
		this.showTitle = config.displayTitle !== false;
		this.title = config.title || '';
		this.DOM = {
			wrap: config.wrap // outer wrap of the form
		};
	}
	BX.Calendar.UserField.ResourceBookingFieldLayoutAbstract = ResourceBookingFieldLayoutAbstract;

	ResourceBookingFieldLayoutAbstract.prototype = {
		build: function()
		{
			this.controls = {};
			// inner wrap
			this.DOM.outerWrap = this.DOM.wrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-wrapper calendar-resbook-webform-wrapper-form'}}));
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-inner'}}));

			if (this.settings.userfieldSettings.useUsers || this.settings.userfieldSettings.useResources)
			{
				this.displayTitle();
				this.displayUsersControl();
				this.displayResourcesControl();
				this.displayServicesControl();
				this.displayDurationControl();
				this.displayDateControl();
				this.displayTimeControl();
			}
			else
			{
				this.displayWarning(BX.message('WEBF_RES_BOOKING_WARNING'));
			}
		},

		destroy: function()
		{
			BX.remove(this.DOM.outerWrap);
		},

		displayTitle: function()
		{
			if (this.showTitle)
			{
				this.DOM.titleWrap = this.DOM.innerWrap
					.appendChild(BX.create("div", {props:{className:'calendar-resbook-webform-title'}}))
					.appendChild(BX.create("div", {props:{className:'calendar-resbook-webform-title-text'}}));
				this.updateTitle(this.title);
			}
		},

		updateTitle: function(title)
		{
			if (this.showTitle)
			{
				this.title = title;
				BX.adjust(this.DOM.titleWrap, {text: this.title});
			}
		},

		displayWarning: function(message)
		{
			this.DOM.warningWrap = this.DOM.innerWrap
				.appendChild(BX.create("div", {
					props:{className:'ui-alert ui-alert-warning ui-alert-text-center ui-alert-icon-warning'},
					style: {marginBottom: 0},
					html: '<span class="ui-alert-message">' + message + '</span>'
				}));
		},

		displayUsersControl: function()
		{
			if (this.settings.userfieldSettings.useUsers)
			{
				if (this.settings.data.users.value === null
					&& BX.type.isArray(this.settings.userfieldSettings.users))
				{
					this.settings.data.users.value = this.settings.userfieldSettings.users;
				}

				this.controls.users = new ViewFormUsersControl({
					outerWrap: this.DOM.innerWrap,
					data: this.settings.data.users,
					userIndex: this.settings.userfieldSettings.userIndex
				});
				this.controls.users.display();
			}
		},

		displayResourcesControl: function()
		{
			if (this.settings.userfieldSettings.useResources)
			{
				if (this.settings.data.resources.value === null
					&& BX.type.isArray(this.settings.userfieldSettings.resources))
				{
					this.settings.data.resources.value = [];
					this.settings.userfieldSettings.resources.forEach(function(res)
					{
						this.settings.data.resources.value.push(parseInt(res.id));
					}, this);
				}

				this.controls.resources = new ViewFormResourcesControl({
					outerWrap: this.DOM.innerWrap,
					data: this.settings.data.resources,
					resourceList: this.settings.userfieldSettings.resources
				});
				this.controls.resources.display();
			}
		},

		displayServicesControl: function()
		{
			if (this.settings.userfieldSettings.useServices)
			{
				if (this.settings.data.services.value === null
					&& BX.type.isArray(this.settings.userfieldSettings.services))
				{
					this.settings.data.services.value = [];
					this.settings.userfieldSettings.services.forEach(function(serv)
					{
						this.settings.data.services.value.push(serv.name);
					}, this);
				}

				this.controls.services = new ViewFormServicesControl({
					outerWrap: this.DOM.innerWrap,
					data: this.settings.data.services,
					serviceList: this.settings.userfieldSettings.services
				});
				this.controls.services.display();
			}
		},

		displayDurationControl: function()
		{
			if (!this.settings.userfieldSettings.useServices)
			{
				this.controls.duration = new ViewFormDurationControl({
					outerWrap: this.DOM.innerWrap,
					data: this.settings.data.duration,
					fullDay: this.settings.userfieldSettings.fullDay
				});
				this.controls.duration.display();
			}
		},

		displayDateControl: function()
		{
			this.controls.date = new DateSelector({
				outerWrap: this.DOM.innerWrap,
				data: this.settings.data.date
			});
			this.controls.date.display();
		},

		displayTimeControl: function()
		{
			if (!this.settings.userfieldSettings.fullDay)
			{
				this.controls.time = new TimeSelector({
					outerWrap: this.DOM.innerWrap,
					data: this.settings.data.time
				});
				this.controls.time.display();
			}
		},

		refreshLayout: function(settingsData)
		{
			for (var k in this.controls)
			{
				if (this.controls.hasOwnProperty(k) && BX.type.isFunction(this.controls[k].refresh))
				{
					this.controls[k].refresh(settingsData[k] || this.settings.data[k]);
				}
			}
		},

		getInnerWrap: function()
		{
			return this.DOM.innerWrap;
		},

		getOuterWrap: function()
		{
			return this.DOM.outerWrap;
		}
	};
	//endregion

	function ResourceBookingFieldViewLayout()
	{
		ResourceBookingFieldViewLayout.superclass.constructor.apply(this, arguments);
	}

	BX.Calendar.UserField.ResourceBookingFieldViewLayout = ResourceBookingFieldViewLayout;
	BX.extend(ResourceBookingFieldViewLayout, ResourceBookingFieldLayoutAbstract);


	function ResourceBookingFieldPreviewLayout()
	{
		ResourceBookingFieldPreviewLayout.superclass.constructor.apply(this, arguments);
	}
	BX.Calendar.UserField.ResourceBookingFieldPreviewLayout = ResourceBookingFieldPreviewLayout;
	BX.extend(ResourceBookingFieldPreviewLayout, ResourceBookingFieldLayoutAbstract);

	ResourceBookingFieldPreviewLayout.prototype.build = function()
	{
		ResourceBookingFieldViewLayout.superclass.build.apply(this);
		this.DOM.outerWrap.className = 'calendar-resbook-webform-wrapper calendar-resbook-webform-wrapper-preview calendar-resbook-webform-wrapper-dark';
	};


	function ResourceBookingFieldLiveViewLayout()
	{
		ResourceBookingFieldLiveViewLayout.superclass.constructor.apply(this, arguments);
	}
	BX.extend(ResourceBookingFieldLiveViewLayout, ResourceBookingFieldLayoutAbstract);

	ResourceBookingFieldLiveViewLayout.prototype.build = function()
	{
		ResourceBookingFieldLiveViewLayout.superclass.build.apply(this);
		this.DOM.outerWrap.className = 'calendar-resbook-webform-wrapper';
	};

	// region *+ResourceBookingViewControlAbstract*
	function ResourceBookingViewControlAbstract(params)
	{
		this.name = null;
		this.classNames = {
			wrap: params.wrapClassName || 'calendar-resbook-webform-block',
			innerWrap: 'calendar-resbook-webform-block-inner',
			title: 'calendar-resbook-webform-block-title',
			field: 'calendar-resbook-webform-block-field'
		};

		this.DOM = {
			outerWrap: params.outerWrap,
			wrap: null,
			dataWrap: null,
			innerWrap: null,
			labelWrap: null
		};
		this.data = params.data;
		this.shown = false;
	}

	ResourceBookingViewControlAbstract.prototype = {
		isDisplayed: function()
		{
			return this.data.show !== 'N';
		},

		isShown: function()
		{
			return this.shown;
		},

		display: function()
		{
			this.DOM.wrap = this.DOM.outerWrap.appendChild(BX.create("div", {props : { className : this.classNames.wrap}}));

			this.DOM.dataWrap = this.DOM.wrap.appendChild(BX.create("div", {attrs : {'data-bx-resource-data-wrap' : 'Y'}}));

			if (this.isDisplayed())
			{
				this.show({animation: false});
			}
		},

		refresh: function(data)
		{
			this.refreshLabel(data);
			this.data = data;

			if (this.setDataConfig())
			{
				if (this.isDisplayed())
				{
					this.show({animation: true});
				}
				else
				{
					this.hide({animation: true});
				}
			}
			this.data = data;
		},

		setDataConfig: function()
		{
			return true;
		},

		refreshLabel: function(data)
		{
			if (this.data.label !== data.label)
			{
				BX.adjust(this.DOM.labelWrap, {text: data.label});
			}
		},

		show: function()
		{
			if (this.DOM.innerWrap)
			{
				this.hide();
			}

			this.DOM.innerWrap = this.DOM.wrap.appendChild(BX.create("div", {props : { className : this.classNames.innerWrap}}));

			if (this.data.label || this.label)
			{
				this.DOM.labelWrap = this.DOM.innerWrap.appendChild(BX.create("div", {props : { className : this.classNames.title}, text: this.data.label || this.label}));
			}
			this.DOM.controlWrap = this.DOM.innerWrap.appendChild(BX.create("div", {props : { className : this.classNames.field}}));
			this.displayControl();
			this.shown = true;
		},

		hide: function()
		{
			BX.remove(this.DOM.innerWrap);
			this.DOM.innerWrap = null;
			this.shown = false;
		},

		displayControl: function()
		{
		},

		showWarning: function(errorMessage)
		{
			if (this.shown && this.DOM.wrap && this.DOM.innerWrap)
			{
				BX.addClass(this.DOM.wrap, "calendar-resbook-webform-block-error");
				this.displayErrorText(errorMessage || BX.message('WEBF_RES_BOOKING_REQUIRED_WARNING'));
			}
		},

		hideWarning: function()
		{
			if (this.DOM.wrap)
			{
				BX.removeClass(this.DOM.wrap, "calendar-resbook-webform-block-error");
				if (this.DOM.errorTextWrap)
				{
					BX.remove(this.DOM.errorTextWrap);
				}
			}
		},

		displayErrorText: function(errorMessage)
		{
			if (this.DOM.errorTextWrap)
			{
				BX.remove(this.DOM.errorTextWrap);
			}
			this.DOM.errorTextWrap = this.DOM.innerWrap.appendChild(BX.create("span", {props : { className : 'calendar-resbook-webform-block-error-text'}, text: errorMessage}));
		}
	};
	//endregion

	// region '+UserSelector'
	function ViewFormUsersControl(params)
	{
		ViewFormUsersControl.superclass.constructor.apply(this, arguments);
		this.name = 'ViewFormUsersControl';
		this.data = params.data;
		this.userList = [];
		this.userIndex = {};

		this.values = [];
		this.defaultMode = 'auto';
		this.previewMode = params.previewMode === undefined;
		this.autoSelectDefaultValue = params.autoSelectDefaultValue;
		this.changeValueCallback = params.changeValueCallback;

		this.handleSettingsData(this.data, params.userIndex);
	}
	BX.extend(ViewFormUsersControl, ResourceBookingViewControlAbstract);
	BX.Calendar.UserField.ViewFormUsersControl = ViewFormUsersControl;

	ViewFormUsersControl.prototype.displayControl = function()
	{
		this.selectedValue = this.getSelectedUser();

		this.dropdownSelect = new ViewFormDropDownSelect({
			wrap: this.DOM.controlWrap,
			values: this.userList,
			selected: this.selectedValue,
			handleChangesCallback: BX.proxy(this.handleChanges, this)
		});
		this.dropdownSelect.build();
	};

	ViewFormUsersControl.prototype.refresh = function(data, userIndex)
	{
		this.refreshLabel(data);
		this.data = data;
		this.handleSettingsData(this.data, userIndex);
		this.selectedValue = this.getSelectedUser();

		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSettings({
				values: this.userList,
				selected: this.selectedValue
			});
		}

		if (this.setDataConfig())
		{
			if (this.isDisplayed())
			{
				this.show({animation: true});
			}
			else
			{
				this.hide({animation: true});
			}
		}
	};

	ViewFormUsersControl.prototype.handleSettingsData = function(data, userIndex)
	{
		if (BX.type.isPlainObject(userIndex))
		{
			for (var id in userIndex)
			{
				if (userIndex.hasOwnProperty(id))
				{
					this.userIndex[id] = userIndex[id];
				}
			}
		}

		this.defaultMode = this.data.defaultMode === 'none' ? 'none' : 'auto';
		var dataValue = [];
		this.userList = [];
		if (this.data.value)
		{
			var dataValueRaw = BX.type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
			dataValueRaw.forEach(function(id)
			{
				id = parseInt(id);
				if (id > 0)
				{
					dataValue.push(id);
					if (this.userIndex[id])
					{
						this.userList.push({
							id: id,
							title: this.userIndex[id].displayName
						});
					}
				}
			}, this);
		}
		this.values = dataValue;
	};

	ViewFormUsersControl.prototype.getSelectedUser = function()
	{
		var selected = null;
		if (this.dropdownSelect)
		{
			selected = this.dropdownSelect.getSelectedValues();
			selected = (BX.type.isArray(selected) && selected.length) ? selected[0] : null;
		}

		if (!selected && this.previewMode
			&& this.data.defaultMode === 'auto'
			&& this.userList && this.userList[0])
		{
			selected = this.userList[0].id;
		}

		if (!selected && this.autoSelectDefaultValue)
		{
			selected = this.autoSelectDefaultValue;
		}

		return selected;
	};

	ViewFormUsersControl.prototype.setSelectedUser = function(userId)
	{
		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSelectedValues([userId]);
		}
		else
		{
			this.autoSelectDefaultValue = parseInt(userId);
		}
	};

	ViewFormUsersControl.prototype.handleChanges = function(selectedValues)
	{
		if (!this.previewMode && BX.type.isFunction(this.changeValueCallback))
		{
			this.changeValueCallback(selectedValues[0] || null);
		}
	};
	// endregion


	// region '+ResourceSelector'
	function ViewFormResourcesControl(params)
	{
		ViewFormResourcesControl.superclass.constructor.apply(this, arguments);
		this.name = 'ViewFormResourcesControl';
		this.data = params.data;
		this.allResourceList = params.resourceList;
		this.autoSelectDefaultValue = params.autoSelectDefaultValue;
		this.changeValueCallback = params.changeValueCallback;
		this.handleSettingsData(params.data);
	}
	BX.extend(ViewFormResourcesControl, ResourceBookingViewControlAbstract);
	BX.Calendar.UserField.ViewFormResourcesControl = ViewFormResourcesControl;

	ViewFormResourcesControl.prototype.handleSettingsData = function(data)
	{
		if (!BX.type.isArray(data.value))
		{
			var dataValue = [];
			if (data.value)
			{
				data.value.split('|').forEach(function(id)
				{
					if (parseInt(id) > 0)
					{
						dataValue.push(parseInt(id))
					}
				});
			}
			this.data.value = dataValue;
		}

		this.resourceList = [];
		if (BX.type.isArray(this.allResourceList))
		{
			this.allResourceList.forEach(function(item)
			{
				if (BX.util.in_array(parseInt(item.id), this.data.value))
				{
					this.resourceList.push(item);
				}
			}, this);
		}

		this.setSelectedValues(this.getSelectedValues());
	};

	ViewFormResourcesControl.prototype.displayControl = function()
	{
		this.dropdownSelect = new ViewFormDropDownSelect({
			wrap: this.DOM.controlWrap,
			values: this.resourceList,
			selected: this.selectedValues,
			multiple: this.data.multiple === 'Y',
			handleChangesCallback: this.changeValueCallback
		});
		this.dropdownSelect.build();
	};

	ViewFormResourcesControl.prototype.refresh = function(data)
	{
		this.refreshLabel(data);
		this.data = data;
		this.handleSettingsData(this.data);
		this.setSelectedValues(this.getSelectedValues());

		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSettings({
				values: this.resourceList,
				selected: this.selectedValues,
				multiple: this.data.multiple === 'Y'
			});
		}

		if (this.setDataConfig())
		{
			if (this.isDisplayed())
			{
				this.show({animation: true});
			}
			else
			{
				this.hide({animation: true});
			}
		}
	};

	ViewFormResourcesControl.prototype.getSelectedValues = function()
	{
		var selected = null;

		if (this.dropdownSelect)
		{
			selected = this.dropdownSelect.getSelectedValues();
		}

		if (!selected && this.autoSelectDefaultValue)
		{
			selected = [this.autoSelectDefaultValue];
		}

		if (!selected && this.data.defaultMode === 'auto')
		{
			if (this.resourceList && this.resourceList[0])
			{
				selected = [this.resourceList[0].id];
			}
		}

		return selected;
	};
	ViewFormResourcesControl.prototype.setSelectedValues = function(selectedValues)
	{
		this.selectedValues = selectedValues;
	};

	ViewFormResourcesControl.prototype.setSelectedResource = function(id)
	{
		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSelectedValues([id]);
		}
		else
		{
			this.autoSelectDefaultValue = parseInt(id);
			this.selectedValues = [id];
		}
	};
	// endregion


	// region 'ServiceSelector'
	function ViewFormServicesControl(params)
	{
		ViewFormServicesControl.superclass.constructor.apply(this, arguments);
		this.name = 'ViewFormServicesControl';

		this.data = params.data;
		this.serviceList = [];
		this.allServiceList = params.serviceList || [];
		this.values = [];
		this.changeValueCallback = params.changeValueCallback;

		if (params.selectedValue)
		{
			this.setSelectedService(params.selectedValue);
		}

		this.handleSettingsData(this.data);
	}
	BX.extend(ViewFormServicesControl, ResourceBookingViewControlAbstract);
	BX.Calendar.UserField.ViewFormServicesControl = ViewFormServicesControl;

	ViewFormServicesControl.prototype.displayControl = function()
	{
		this.dropdownSelect = new ViewFormDropDownSelect({
			wrap: this.DOM.controlWrap,
			values: this.serviceList,
			selected: this.getSelectedService(),
			handleChangesCallback: BX.proxy(function (selectedValues)
			{
				if (selectedValues && selectedValues[0])
				{
					this.setSelectedService(selectedValues[0]);
					this.changeValueCallback();
				}
			}, this)
		});
		this.dropdownSelect.build();
	};

	ViewFormServicesControl.prototype.refresh = function(data)
	{
		this.refreshLabel(data);
		this.data = data;

		this.handleSettingsData(this.data);

		if (this.dropdownSelect)
		{
			this.dropdownSelect.setSettings({
				values: this.serviceList,
				selected: this.getSelectedService()
			});
		}

		if (this.setDataConfig())
		{
			if (this.isDisplayed())
			{
				this.show({animation: true});
			}
			else
			{
				this.hide({animation: true});
			}
		}
	};

	ViewFormServicesControl.prototype.handleSettingsData = function()
	{
		this.serviceIndex = {};
		if (BX.type.isArray(this.allServiceList))
		{
			this.allServiceList.forEach(function(service)
			{
				if (BX.type.isPlainObject(service)
					&& BX.type.isString(service.name)
					&& BX.util.trim(service.name) !== '')
				{
					this.serviceIndex[this.prepareServiceId(service.name)] = service;
				}
			}, this);
		}

		this.serviceList = [];
		if (this.data.value)
		{
			var dataValueRaw = BX.type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
			dataValueRaw.forEach(function(id)
			{
				var service = this.serviceIndex[this.prepareServiceId(id)];
				if (BX.type.isPlainObject(service)
					&& BX.type.isString(service.name)
					&& BX.util.trim(service.name) !== '')
				{
					this.serviceList.push({
						id: this.prepareServiceId(service.name),
						title: service.name + ' - ' + BX.Calendar.UserField.ResourceBooking.getDurationLabel(service.duration)
					});
				}
			}, this);
		}
	};

	ViewFormServicesControl.prototype.setSelectedService = function(serviceName)
	{
		this.selectedService = serviceName;
	};
	ViewFormServicesControl.prototype.getSelectedService = function(getMeta)
	{
		return getMeta !== true ? (this.selectedService || null) : (this.serviceIndex[this.prepareServiceId(this.selectedService)] || null);
	};

	ViewFormServicesControl.prototype.prepareServiceId = function(str)
	{
		return BX.type.isString(str) ? BX.translit(str).replace(/[^a-z0-9_]/ig, "_") : str;
	};
	//endregion

	// region 'DurationSelector'
	function ViewFormDurationControl(params)
	{
		ViewFormDurationControl.superclass.constructor.apply(this, arguments);
		this.name = 'ViewFormDurationControl';
		this.data = params.data;

		this.durationList = BX.Calendar.UserField.ResourceBooking.getDurationList(params.fullDay);
		this.changeValueCallback = params.changeValueCallback;
		this.defaultValue = params.defaultValue || this.data.defaultValue;

		this.handleSettingsData(params.data);
	}
	BX.extend(ViewFormDurationControl, ResourceBookingViewControlAbstract);
	BX.Calendar.UserField.ViewFormDurationControl = ViewFormDurationControl;

	ViewFormDurationControl.prototype.handleSettingsData = function()
	{
		this.durationItems = [];
		if (BX.type.isArray(this.durationList))
		{
			this.durationList.forEach(function(item)
			{
				this.durationItems.push({
					id: item.value,
					title: item.label
				});
			}, this);
		}
	};

	ViewFormDurationControl.prototype.displayControl = function()
	{
		this.DOM.durationInput = this.DOM.controlWrap.appendChild(BX.create('INPUT', {
			attrs: {
				value: this.data.defaultValue || null,
				type: 'text'
			},
			props: {className: 'calendar-resbook-webform-block-input calendar-resbook-webform-block-input-dropdown'}
		}));

		this.durationControl = new BX.Calendar.UserField.ResourceBooking.SelectInput({
			input: this.DOM.durationInput,
			values: this.durationList,
			value: this.data.defaultValue || null,
			editable: this.data.manualInput === 'Y',
			defaultValue: this.defaultValue,
			setFirstIfNotFound: true,
			onChangeCallback: this.changeValueCallback
		});
	};

	ViewFormDurationControl.prototype.refresh = function(data)
	{
		this.refreshLabel(data);
		this.data = data;
		this.handleSettingsData(this.data);

		if (this.setDataConfig())
		{
			if (this.isDisplayed())
			{
				this.show({animation: true});
				if (this.durationControl)
				{
					this.durationControl.setValue(this.data.defaultValue || null);
				}
			}
			else
			{
				this.hide({animation: true});
			}
		}
	};

	ViewFormDurationControl.prototype.getSelectedValue = function()
	{
		var duration = null;
		if (this.durationControl)
		{
			duration = BX.Calendar.UserField.ResourceBooking.parseDuration(this.durationControl.getValue());
		}
		else
		{
			duration = parseInt(this.defaultValue);
		}
		return duration;
	};
	// endregion


	// region 'Date Selector'
	function DateSelector(params)
	{
		this.DOM = {
			outerWrap: params.outerWrap,
			wrap: null
		};

		this.DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"));
		this.data = params.data || {};
		this.changeValueCallback = params.changeValueCallback;
		this.requestDataCallback = params.requestDataCallback;
		this.previewMode = params.previewMode === undefined;
		this.allowOverbooking = params.allowOverbooking;
		this.setDataConfig();
		this.displayed = true;
	}
	BX.extend(DateSelector, ResourceBookingViewControlAbstract);
	BX.Calendar.UserField.DateSelector = DateSelector;

	DateSelector.prototype.display = function(params)
	{
		params = params || {};
		this.setDateIndex(params.availableDateIndex);
		this.setCurrentDate(params.selectedValue);

		this.DOM.wrap = this.DOM.outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-block'}}));
		this.DOM.innerWrap = this.DOM.wrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-block-inner'}}));
		if (this.data.label)
		{
			this.DOM.labelWrap = this.DOM.innerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-block-title'}, text: this.data.label + '*'}));
		}
		this.displayControl();
		this.shown = true;
	};

	DateSelector.prototype.refresh = function(data, params)
	{
		params = params || {};
		this.setDateIndex(params.availableDateIndex);
		this.setCurrentDate(params.selectedValue);

		this.data = data;
		BX.adjust(this.DOM.labelWrap, {text: this.data.label + '*'});

		if (this.setDataConfig())
		{
			BX.remove(this.DOM.controlWrap);
			this.displayControl();
		}

		if (this.style === 'line')
		{
			this.lineDateControl.refreshDateAvailability();
		}
	};

	DateSelector.prototype.setDataConfig = function ()
	{
		var
			style = this.data.style === 'line' ? 'line' : 'popup', // line|popup
			start = this.data.start === 'today' ? 'today' : 'free',
			configWasChanged = this.style !== style || this.start !== start;

		this.style = style;
		this.start = start;

		return configWasChanged;
	};

	DateSelector.prototype.hide = function()
	{
		BX.remove(this.DOM.innerWrap);
		this.DOM.innerWrap = null;
	};

	DateSelector.prototype.displayControl = function()
	{
		this.DOM.controlWrap = this.DOM.innerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-block-date'}}));

		if (this.style === 'popup')
		{
			this.DOM.controlWrap.className = 'calendar-resbook-webform-block-calendar';
			this.popupSateControl = new PopupDateSelector(
			{
				wrap: this.DOM.controlWrap,
				isDateAvailable: BX.proxy(this.isDateAvailable, this),
				onChange: BX.proxy(function(value)
				{
					this.onChange(value);
				}, this)
			});
			this.popupSateControl.build();
			this.popupSateControl.setValue(this.getValue());
		}
		else if (this.style === 'line')
		{
			this.DOM.controlWrap.className = 'calendar-resbook-webform-block-date';
			this.lineDateControl = new LineDateSelector(
				{
					wrap: this.DOM.controlWrap,
					isDateAvailable: BX.proxy(this.isDateAvailable, this),
					onChange: BX.proxy(this.onChange, this)
				}
			);
			this.lineDateControl.build();
			this.lineDateControl.setValue(this.getValue());
		}
	};

	DateSelector.prototype.setCurrentDate = function(date)
	{
		if (BX.type.isDate(date))
		{
			this.currentDate = date;
		}
	};

	DateSelector.prototype.setDateIndex = function(availableDateIndex)
	{
		if (BX.type.isPlainObject(availableDateIndex))
		{
			this.availableDateIndex = availableDateIndex;
		}
	};

	DateSelector.prototype.isDateLoaded = function(date)
	{
		if (BX.type.isDate(date) && !this.isItPastDate(date) && this.availableDateIndex)
		{
			if (this.availableDateIndex[BX.date.format(this.DATE_FORMAT, date)] !== undefined)
			{
				return true;
			}

			if (BX.type.isFunction(this.requestDataCallback))
			{
				this.requestDataCallback({date: date});
			}
		}
		return false;
	};

	DateSelector.prototype.isDateAvailable = function(date)
	{
		if (this.previewMode || this.allowOverbooking)
		{
			return true;
		}

		if (BX.type.isDate(date) && !this.isItPastDate(date) && this.availableDateIndex)
		{
			var dateKey = BX.date.format(this.DATE_FORMAT, date);
			if (this.availableDateIndex[dateKey] === undefined)
			{
				if (BX.type.isFunction(this.requestDataCallback))
				{
					this.requestDataCallback({date: date});
				}
				return false;
			}
			else
			{
				return this.availableDateIndex[dateKey];
			}
		}
		return false;
	};

	DateSelector.prototype.isItPastDate = function(date)
	{
		if (BX.type.isDate(date))
		{
			var
				nowDate = new Date(),
				checkDate = new Date(date.getTime());

			nowDate.setHours(0, 0, 0, 0);
			checkDate.setHours(0, 0, 0, 0);

			return checkDate.getTime() < nowDate.getTime();
		}
		return false;
	};

	DateSelector.prototype.refreshCurrentValue = function()
	{
		this.onChange(this.getDisplayedValue());
	};

	DateSelector.prototype.getDisplayedValue = function()
	{
		return this.style === 'popup' ? this.popupSateControl.getValue() : this.lineDateControl.getValue();;
	};

	DateSelector.prototype.onChange = function(date)
	{
		if (BX.type.isFunction(this.changeValueCallback))
		{
			// if (value === false)
			// {
			// 	this.showWarning(BX.message('WEBF_RES_BOOKING_STATUS_DATE_IS_NOT_AVAILABLE'));
			// }
			// else
			// {
			// 	this.hideWarning();
			// }
			var realDate = date;
			if (!BX.type.isDate(realDate))
			{
				realDate = this.getDisplayedValue();
			}
			this.setCurrentDate(date);
			this.changeValueCallback(date, realDate, this.isDateAvailable(realDate));
		}
	};

	DateSelector.prototype.getValue = function()
	{
		if (!this.currentDate)
		{
			this.currentDate = new Date();
		}
		return this.currentDate;
	};


	function PopupDateSelector(params)
	{
		this.DOM = {
			outerWrap: params.wrap,
			wrap: null
		};
		this.value = null;
		this.isDateAvailable = BX.type.isFunction(params.isDateAvailable) ? params.isDateAvailable : function(){return true};
		this.onChange = BX.type.isFunction(params.onChange) ? params.onChange : function(){};
	}

	PopupDateSelector.prototype = {
		build: function()
		{
			this.DOM.wrap = this.DOM.outerWrap.appendChild(BX.create("div", {
					props : { className : 'calendar-resbook-webform-block-strip'},
					events: {click: BX.proxy(this.handleClick, this)}
			}));
			this.DOM.valueInput = this.DOM.wrap.appendChild(BX.create("input", {
				attrs : {
					type : 'hidden',
					value: ''
				}
			}));

			this.DOM.previousArrow = this.DOM.wrap.appendChild(BX.create("span", {
				attrs : {
					className : 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-prev',
					'data-bx-resbook-date-meta' : 'previous'
				}
			}));

			this.DOM.stateWrap = this.DOM.wrap.appendChild(BX.create("span", {
				attrs : {
					className : 'calendar-resbook-webform-block-strip-text',
					'data-bx-resbook-date-meta' : 'calendar'
				}
			}));
			this.DOM.stateWrapDate = this.DOM.stateWrap.appendChild(BX.create("span", {props : { className : 'calendar-resbook-webform-block-strip-date'}}));
			this.DOM.stateWrapDay = this.DOM.stateWrap.appendChild(BX.create("span", {props : { className : 'calendar-resbook-webform-block-strip-day'}}));

			this.DOM.nextArrow = this.DOM.wrap.appendChild(BX.create("span", {
				attrs : {
					className : 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-next',
					'data-bx-resbook-date-meta' : 'next'
				}}));
		},

		getValue: function()
		{
			return this.value;
		},

		setValue: function(dateValue)
		{
			this.value = dateValue;
			BX.adjust(this.DOM.stateWrapDate, {text: BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_DATE_LINE'), dateValue)});
			BX.adjust(this.DOM.stateWrapDay, {text: BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_DAY_LINE'), dateValue)});

			if (!this.isDateAvailable(dateValue) || !BX.type.isDate(dateValue))
			{
				this.onChange(false);
			}
			else
			{
				this.onChange(this.value);
			}
		},

		handleClick: function(e)
		{
			var
				dateValue,
				target = e.target || e.srcElement;

			if (target.hasAttribute('data-bx-resbook-date-meta') ||
				(target = BX.findParent(target, {attribute: 'data-bx-resbook-date-meta'}, this.DOM.wrap))
			)
			{
				var dateMeta = target.getAttribute('data-bx-resbook-date-meta');
				if (dateMeta === 'previous')
				{
					dateValue = this.getValue();
					dateValue.setDate(dateValue.getDate() - 1);
					this.setValue(dateValue);
				}
				else if (dateMeta === 'next')
				{
					dateValue = this.getValue();
					dateValue.setDate(dateValue.getDate() + 1);
					this.setValue(dateValue);
				}
				else if (dateMeta === 'calendar')
				{
					this.openCalendarPopup();
				}
			}
		},

		openCalendarPopup: function()
		{
			this.DOM.valueInput.value = BX.date.format(BX.date.convertBitrixFormat(BX.message("FORMAT_DATE")), this.getValue().getTime() / 1000);

			BX.calendar({node: this.DOM.stateWrap, field: this.DOM.valueInput, bTime: false});
			if (BX.calendar.get().popup)
			{
				BX.removeCustomEvent(BX.calendar.get().popup, 'onPopupClose', BX.proxy(this.handleCalendarClose, this));
				BX.addCustomEvent(BX.calendar.get().popup, 'onPopupClose', BX.proxy(this.handleCalendarClose, this));
			}
		},

		handleCalendarClose: function()
		{
			this.setValue(BX.parseDate(this.DOM.valueInput.value));
		}
	};


	function LineDateSelector(params)
	{
		params = params || {};
		this.DOM = {
			outerWrap: params.wrap,
			wrap: null
		};
		this.value = null;
		this.isDateAvailable = BX.type.isFunction(params.isDateAvailable) ? params.isDateAvailable : function(){return true};
		this.onChange = BX.type.isFunction(params.onChange) ? params.onChange : function(){};
		this.DAYS_DISPLAY_SIZE = 30;
		this.DOM.dayNodes = {};
		this.dayNodeIndex = {};
	}

	LineDateSelector.prototype = {
		build: function()
		{
			this.DOM.monthTitle = this.DOM.outerWrap.appendChild(BX.create("span", {
				props : { className : 'calendar-resbook-webform-block-date-month'}
			}));

			this.DOM.wrap = this.DOM.outerWrap.appendChild(BX.create("div", {
				props : { className : 'calendar-resbook-webform-block-date-range'},
				events: {click: BX.proxy(this.handleClick, this)}
			}));

			this.DOM.controlStaticWrap = this.DOM.wrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block-date-range-static-wrap'}}));
			this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block-date-range-inner-wrap'}}));

			this.DOM.valueInput = this.DOM.wrap.appendChild(BX.create("input", {
				attrs : {
					type : 'hidden',
					value: ''
				}
			}));

			this.fillDays();
			this.initCustomScroll();
		},

		fillDays: function()
		{
			var
				i,
				startDate = this.getStartLoadDate(),
				date = new Date(startDate.getTime());

			for (i = 0; i < this.DAYS_DISPLAY_SIZE; i++)
			{
				this.addDateSlot(date);
				date.setDate(date.getDate() + 1);
			}

			this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);
		},

		addDateSlot: function(date)
		{
			var dateCode = BX.date.format('Y-m-d', date.getTime() / 1000);
			this.dayNodeIndex[dateCode] = new Date(date.getTime());
			this.DOM.dayNodes[dateCode] = this.DOM.controlInnerWrap.appendChild(BX.create("div", {
				attrs : {
					className : 'calendar-resbook-webform-block-date-item' + (this.isDateAvailable(date) ? '' : ' calendar-resbook-webform-block-date-item-off'),
					'data-bx-resbook-date-meta' : dateCode
				},
				html: '<div class="calendar-resbook-webform-block-date-item-inner">' +
					'<span class="calendar-resbook-webform-block-date-number">' +
						BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_DATE'), date) +
					'</span>' +
					'<span class="calendar-resbook-webform-block-date-day">' +
						BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_DAY_OF_THE_WEEK'), date) +
					'</span>' +
				'</div>'
			}));
		},

		refreshDateAvailability: function()
		{
			for (var dateCode in this.DOM.dayNodes)
			{
				if (this.DOM.dayNodes.hasOwnProperty(dateCode))
				{
					if (this.isDateAvailable(this.dayNodeIndex[dateCode]))
					{
						BX.removeClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
					}
					else
					{
						BX.addClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
					}
				}
			}
		},

		handleClick: function(e)
		{
			var
				dateValue,
				target = e.target || e.srcElement;

			if (target.hasAttribute('data-bx-resbook-date-meta') ||
				(target = BX.findParent(target, {attribute: 'data-bx-resbook-date-meta'}, this.DOM.wrap))
			)
			{
				var dateMeta = target.getAttribute('data-bx-resbook-date-meta');
				if (dateMeta && (dateValue = BX.parseDate(dateMeta, false, 'YYYY-MM-DD')))
				{
					this.setValue(dateValue);
				}
			}
		},

		setValue: function(dateValue)
		{
			if (BX.type.isDate(dateValue))
			{
				this.value = dateValue;
				var dayNode = this.getDayNode(dateValue);
				if (dayNode)
				{
					this.setSelected(dayNode);
				}
				this.onChange(this.value);
			}
		},

		getValue: function()
		{
			return this.value;
		},

		getDayNode: function(dateValue)
		{
			var dateCode = BX.date.format('Y-m-d', dateValue.getTime() / 1000);
			if (this.DOM.dayNodes[dateCode])
			{
				return this.DOM.dayNodes[dateCode];
			}
			else
			{
				this.fillDays(dateValue);
				if (this.DOM.dayNodes[dateCode])
				{
					return this.DOM.dayNodes[dateCode];
				}
			}
			return null;
		},

		setSelected: function(dayNode)
		{
			if (this.currentSelected)
			{
				BX.removeClass(this.currentSelected, 'calendar-resbook-webform-block-date-item-select');
			}
			this.currentSelected = dayNode;
			BX.addClass(dayNode, 'calendar-resbook-webform-block-date-item-select');
		},

		getStartLoadDate: function()
		{
			if (!this.startLoadDate)
			{
				this.startLoadDate = new Date();
			}
			else
			{
				this.startLoadDate.setDate(this.startLoadDate.getDate() + this.DAYS_DISPLAY_SIZE);
			}
			return this.startLoadDate;
		},

		initCustomScroll: function()
		{
			var arrowWrap = this.DOM.wrap.appendChild(BX.create("div", {
				props : { className : 'calendar-resbook-webform-block-arrow-container'}
			}));

			this.DOM.leftArrow = arrowWrap.appendChild(BX.create("span",
				{
					props : {className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-prev'},
					events: {click: BX.proxy(this.handlePrevArrowClick, this)}
				}));
			this.DOM.rightArrow = arrowWrap.appendChild(BX.create("span",
				{
					props : { className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-next'},
					events: {click: BX.proxy(this.handleNextArrowClick, this)}
				}));

			this.outerWidth = parseInt(this.DOM.controlStaticWrap.offsetWidth);
			this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);

			if ('onwheel' in document)
			{
				BX.bind(this.DOM.controlStaticWrap, "wheel", BX.proxy(this.mousewheelScrollHandler, this));
			}
			else
			{
				BX.bind(this.DOM.controlStaticWrap, "mousewheel", BX.proxy(this.mousewheelScrollHandler, this));
			}

			this.checkScrollPosition();
		},

		handleNextArrowClick: function()
		{
			this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
			this.checkScrollPosition();
		},

		handlePrevArrowClick: function()
		{
			this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
			this.checkScrollPosition();
		},

		mousewheelScrollHandler: function(e)
		{
			e = e || window.event;
			var delta = e.deltaY || e.detail || e.wheelDelta;
			if (Math.abs(delta) > 0)
			{
				if (!BX.browser.IsMac())
				{
					delta = delta * 3;
				}
				this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft + delta, 0);
				this.checkScrollPosition();
				return BX.PreventDefault(e);
			}
		},

		checkScrollPosition: function()
		{
			if (this.outerWidth <= this.innerWidth)
			{
				this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft === 0 ? 'none' : '';
				//this.DOM.rightArrow.style.display = (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) ? 'none' : '';
				if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft)
				{
					this.fillDays();
				}
			}

			this.updateMonthTitle();
		},

		updateMonthTitle: function()
		{
			if (!this.dayNodeOuterWidth)
			{
				this.dayNodeOuterWidth = this.DOM.controlInnerWrap.childNodes[1].offsetLeft - this.DOM.controlInnerWrap.childNodes[0].offsetLeft;
				if (!this.dayNodeOuterWidth)
				{
					return setTimeout(BX.delegate(this.updateMonthTitle, this), 100);
				}
			}

			var
				monthFrom, monthTo, dateMeta, dateValue,
				firstDayNodeIndex = Math.floor(this.DOM.controlStaticWrap.scrollLeft / this.dayNodeOuterWidth),
				lastDayNodeIndex = Math.floor((this.DOM.controlStaticWrap.scrollLeft + this.outerWidth) / this.dayNodeOuterWidth);

			if (this.DOM.controlInnerWrap.childNodes[firstDayNodeIndex])
			{
				dateMeta = this.DOM.controlInnerWrap.childNodes[firstDayNodeIndex].getAttribute('data-bx-resbook-date-meta');
				if (dateMeta && (dateValue = BX.parseDate(dateMeta, false, 'YYYY-MM-DD')))
				{
					monthFrom = monthTo = BX.date.format('f', dateValue);
				}
			}

			if (this.DOM.controlInnerWrap.childNodes[lastDayNodeIndex])
			{
				dateMeta = this.DOM.controlInnerWrap.childNodes[lastDayNodeIndex].getAttribute('data-bx-resbook-date-meta');
				if (dateMeta && (dateValue = BX.parseDate(dateMeta, false, 'YYYY-MM-DD')))
				{
					monthTo = BX.date.format('f', dateValue);
				}
			}

			if (monthFrom && monthTo)
			{
				BX.adjust(this.DOM.monthTitle, {text: monthTo === monthFrom ? monthFrom : monthFrom + ' - ' + monthTo});
			}
		}
	};
	// endregion


	//region 'TimeSelector'
	function TimeSelector(params)
	{
		this.DOM = {
			outerWrap: params.outerWrap,
			wrap: null
		};

		this.data = params.data || {};
		this.setDataConfig();

		this.timeFrom = this.data.timeFrom || params.timeFrom || 7;
		if (params.timeFrom !== undefined)
		{
			this.timeFrom = params.timeFrom;
		}
		this.timeTo = this.data.timeTo || 20;
		if (params.timeTo !== undefined)
		{
			this.timeTo = params.timeTo;
		}
		this.SLOTS_ROW_AMOUNT = 6;
		this.id = 'time-selector-' + Math.round(Math.random() * 1000);
		this.popupSelectId = this.id + '-select-popup';

		this.previewMode = params.previewMode === undefined;
		this.changeValueCallback = params.changeValueCallback;
		this.timezone = params.timezone;
		this.timezoneOffset = params.timezoneOffset;
		this.timezoneOffsetLabel = params.timezoneOffsetLabel;
		this.timeMidday = 12;
		this.timeEvening = 17;
		this.displayed = true;
	}
	BX.extend(TimeSelector, ResourceBookingViewControlAbstract);
	BX.Calendar.UserField.TimeSelector = TimeSelector;

	TimeSelector.prototype.setDataConfig = function ()
	{
		var
			style = this.data.style === 'select' ? 'select' : 'slots', // select|slots
			showOnlyFree = this.data.showOnlyFree !== 'N',
			showFinishTime = this.data.showFinishTime === 'Y',
			scale = parseInt(this.data.scale || 30),
			configWasChanged = this.style !== style || this.showOnlyFree !== showOnlyFree || this.showFinishTime !== showFinishTime || this.scale !== scale;

		this.style = style;
		this.showOnlyFree = showOnlyFree;
		this.showFinishTime = showFinishTime;
		this.scale = scale;

		return configWasChanged;
	};

	TimeSelector.prototype.display = function ()
	{
		this.DOM.wrap = this.DOM.outerWrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block'}}));
		this.DOM.innerWrap = this.DOM.wrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block-inner'}}));
		if (this.data.label)
		{
			this.DOM.labelWrap = this.DOM.innerWrap.appendChild(BX.create("div", {
				props: {className: 'calendar-resbook-webform-block-title'},
				text: this.data.label + '*'
			}));

			if (this.timezone)
			{
				this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(BX.create("div", {
					props: {className: 'calendar-resbook-webform-block-title-timezone'}
				}));
				BX.adjust(this.DOM.timezoneLabelWrap, {html: BX.message('USER_TYPE_RESOURCE_TIMEZONE').replace('#TIMEZONE#', this.timezone + ' ' + this.timezoneOffsetLabel)});
			}
		}

		this.displayControl();
		this.setValue(this.getValue());
		this.shown = true;
	};

	TimeSelector.prototype.refresh = function (data, params)
	{
		params = params || {};
		this.setSlotIndex(params.slotIndex);
		this.currentDate = params.currentDate || new Date();
		this.data = data;

		if (!this.isShown())
		{
			this.setDataConfig();
			this.display();
		}
		else
		{
			if (this.DOM.labelWrap && this.data.label)
			{
				BX.adjust(this.DOM.labelWrap, {text: this.data.label + '*'});
			}

			if (this.timezone)
			{
				if (!this.DOM.timezoneLabelWrap || !BX.isNodeInDom(this.DOM.timezoneLabelWrap))
				{
					this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(BX.create("div", {
						props: {className: 'calendar-resbook-webform-block-title-timezone'}
					}));
				}
				BX.adjust(this.DOM.timezoneLabelWrap, {html: BX.message('USER_TYPE_RESOURCE_TIMEZONE').replace('#TIMEZONE#', this.timezone + ' ' + this.timezoneOffsetLabel)});
			}

			if (this.setDataConfig() || params.slotIndex || params.selectedValue)
			{
				BX.remove(this.DOM.controlWrap);
				this.displayControl();
			}
		}

		this.setCurrentValue(params.selectedValue || this.getValue());
	};

	TimeSelector.prototype.setSlotIndex = function(slotIndex)
	{
		if (BX.type.isPlainObject(slotIndex))
		{
			this.availableSlotIndex = slotIndex;
		}
	};
	TimeSelector.prototype.setCurrentValue = function (timeValue)
	{
		if (timeValue && (this.previewMode || this.availableSlotIndex[timeValue]))
		{
			this.setValue(timeValue);
		}
		else
		{
			this.setValue(null);
		}
	};

	TimeSelector.prototype.showEmptyWarning = function ()
	{
		if (this.DOM.labelWrap)
		{
			this.DOM.labelWrap.style.display = 'none';
		}

		if (!this.DOM.warningWrap)
		{
			this.DOM.warningTextNode = BX.create("span", {props: {className: 'calendar-resbook-webform-block-notice-date'}});
			this.DOM.warningWrap = this.DOM.innerWrap.appendChild(BX.create("div", {
				props: {className: 'calendar-resbook-webform-block-notice'},
				children: [
					BX.create("span", {props: {className: 'calendar-resbook-webform-block-notice-icon'}}),
					this.DOM.warningTextNode,
					BX.create("span", {
						props: {className: 'calendar-resbook-webform-block-notice-detail'},
						text: BX.message('WEBF_RES_BOOKING_BUSY_DAY_WARNING')
					})
				]
			}));
		}

		if (this.DOM.warningWrap)
		{
			BX.adjust(this.DOM.warningTextNode, {text: BX.date.format(BX.message('WEBF_RES_BUSY_DAY_DATE_FORMAT'), this.currentDate)});
			this.DOM.warningWrap.style.display = '';

			this.noSlotsAvailable = true;
		}
	};

	TimeSelector.prototype.hideEmptyWarning = function ()
	{
		this.noSlotsAvailable = false;
		if (this.DOM.labelWrap)
		{
			this.DOM.labelWrap.style.display = '';
		}
		if (this.DOM.warningWrap)
		{
			this.DOM.warningWrap.style.display = 'none';
		}
	};

	TimeSelector.prototype.displayControl = function ()
	{
		var slotsInfo = this.getSlotsInfo();
		this.slots = slotsInfo.slots;

		if (!slotsInfo.freeSlotsCount)
		{
			this.showEmptyWarning();
		}
		else
		{
			this.hideEmptyWarning();
			if (this.style === 'select')
			{
				this.createSelectControl();
			}
			else if (this.style === 'slots')
			{
				this.createSlotsControl();
			}
		}
	};

	TimeSelector.prototype.hide = function ()
	{
		if (this.DOM.innerWrap)
		{
			this.DOM.innerWrap.style.display = 'none';
		}
	};
	TimeSelector.prototype.show = function ()
	{
		if (this.DOM.innerWrap)
		{
			this.DOM.innerWrap.style.display = '';
		}
	};

	TimeSelector.prototype.createSlotsControl = function ()
	{
		if (this.DOM.controlWrap)
		{
			BX.remove(this.DOM.controlWrap);
		}

		this.DOM.controlWrap = this.DOM.innerWrap.appendChild(
			BX.create("div", {
				props: {className: 'calendar-resbook-webform-block-time'},
				events: {click: BX.proxy(this.handleClick, this)}
			}));

		if (!this.showFinishTime && !BX.isAmPmMode())
		{
			BX.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-sm');
		}
		else if (!this.showFinishTime && BX.isAmPmMode())
		{
			BX.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-md');
		}
		else if (BX.isAmPmMode())
		{
			BX.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-lg');
		}

		this.DOM.controlStaticWrap = this.DOM.controlWrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block-time-static-wrap'}}));
		this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block-time-inner-wrap'}}));

		var
			itemsInColumn,
			maxColumnNumber = 3,
			parts = {},
			itemNumber = 0,
			innerWrap;

		// FilterSlots
		this.slots.forEach(function(slot)
		{
			if (!parts[slot.partOfTheDay])
			{
				parts[slot.partOfTheDay] = {
					items: []
				};
			}

			parts[slot.partOfTheDay].items.push(slot);
		});

		this.slots.forEach(function(slot)
		{
			if (!parts[slot.partOfTheDay].wrap)
			{
				itemNumber = 0;
				itemsInColumn = 6;
				parts[slot.partOfTheDay].wrap = BX.create("div", {
					props: {className: 'calendar-resbook-webform-block-col'},
					html: '<span class="calendar-resbook-webform-block-col-title">'
					+ BX.message('WEBF_RES_PART_OF_THE_DAY_' + slot.partOfTheDay.toUpperCase())
					+ '</span>'
				});

				parts[slot.partOfTheDay].itemsWrap = parts[slot.partOfTheDay].wrap
					.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block-col-list'}}));

				if (parts[slot.partOfTheDay].items.length > maxColumnNumber * itemsInColumn)
				{
					itemsInColumn = Math.ceil(parts[slot.partOfTheDay].items.length / maxColumnNumber);
				}
			}

			if (itemNumber % itemsInColumn === 0)
			{
				innerWrap = parts[slot.partOfTheDay].itemsWrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-block-col-list-inner'}}))
			}

			if (innerWrap && (!slot.booked || !this.showOnlyFree))
			{
				innerWrap.appendChild(BX.create("div", {
					attrs: {
						'data-bx-resbook-time-meta': 'slot' + (slot.booked ? '-off' : ''),
						'data-bx-resbook-slot': slot.time.toString(),
						className: 'calendar-resbook-webform-block-col-item'
						+ (slot.selected ? ' calendar-resbook-webform-block-col-item-select' : '')
						+ (slot.booked ? ' calendar-resbook-webform-block-col-item-off' : '')
					},
					html: '<div class="calendar-resbook-webform-block-col-item-inner">' + '<span class="calendar-resbook-webform-block-col-time">' + slot.fromTime + '</span>' + (this.showFinishTime ? '- <span class="calendar-resbook-webform-block-col-time calendar-resbook-webform-block-col-time-end">' + slot.toTime + '</span>' : ''
					) + '</div>'
				}));
				itemNumber++;
			}

			parts[slot.partOfTheDay].itemsAmount = itemNumber;
		}, this);

		var k;
		for (k in parts)
		{
			if (parts.hasOwnProperty(k) && parts[k].itemsAmount > 0)
			{
				this.DOM.controlInnerWrap.appendChild(parts[k].wrap);
			}
		}

		this.initCustomScrollForSlots();
	};

	TimeSelector.prototype.createSelectControl = function ()
	{
		if (this.DOM.controlWrap)
		{
			BX.remove(this.DOM.controlWrap);
		}

		this.DOM.controlWrap = this.DOM.innerWrap.appendChild(BX.create("div", {
			props: {className: 'calendar-resbook-webform-block-field'},
			events: {click: BX.proxy(this.handleClick, this)}
		}));

		this.DOM.timeSelectWrap = this.DOM.controlWrap.appendChild(BX.create("div", {
			props: {className: 'calendar-resbook-webform-block-strip'}
		}));
		this.DOM.valueInput = this.DOM.timeSelectWrap.appendChild(BX.create("input", {
			attrs: {
				type: 'hidden',
				value: ''
			}
		}));

		this.DOM.previousArrow = this.DOM.timeSelectWrap.appendChild(BX.create("span", {
			attrs: {
				className: 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-prev',
				'data-bx-resbook-time-meta': 'previous'
			}
		}));

		this.DOM.stateWrap = this.DOM.timeSelectWrap.appendChild(BX.create("span", {
			attrs: {
				className: 'calendar-resbook-webform-block-strip-text',
				'data-bx-resbook-time-meta': 'select'
			}
		}));
		this.DOM.stateWrap = this.DOM.stateWrap.appendChild(BX.create("span", {props: {className: 'calendar-resbook-webform-block-strip-date'}}));

		this.DOM.nextArrow = this.DOM.timeSelectWrap.appendChild(BX.create("span", {
			attrs: {
				className: 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-next',
				'data-bx-resbook-time-meta': 'next'
			}
		}));

		this.setValue(this.getValue());
	};

	TimeSelector.prototype.setValue = function (value)
	{
		var slot = this.getSlotByTime(value);
		if (slot)
		{
			if (this.style === 'select')
			{
				BX.adjust(this.DOM.stateWrap, {text: this.getTimeTextBySlot(slot)});
			}
			else if (this.style === 'slots')
			{
				this.setSelected(this.getSlotNode(slot.time));
			}
			this.value = slot.time;
		}
		else
		{
			this.value = null;
		}

		if (!this.previewMode && BX.type.isFunction(this.changeValueCallback))
		{
			this.changeValueCallback(this.value);
		}
	};

	TimeSelector.prototype.getValue = function ()
	{
		if (!this.value && (this.previewMode || this.style === 'select'))
		{
			this.value = this.slots[0].time;
		}
		return this.value;
	};

	TimeSelector.prototype.hasAvailableSlots = function ()
	{
		return !this.noSlotsAvailable;
	};

	TimeSelector.prototype.getTimeTextBySlot = function (slot)
	{
		return slot.fromTime + (this.showFinishTime ? ' - ' + slot.toTime : '');
	};

	TimeSelector.prototype.getSlotByTime = function(time)
	{
		return BX.type.isArray(this.slots) ? this.slots.find(function(slot){return parseInt(slot.time) === parseInt(time);}) : null;
	};

	TimeSelector.prototype.handleClick = function(e)
	{
		var target = e.target || e.srcElement;
		if (target.hasAttribute('data-bx-resbook-time-meta') ||
			(target = BX.findParent(target, {attribute: 'data-bx-resbook-time-meta'}, this.DOM.wrap))
		)
		{
			var meta = target.getAttribute('data-bx-resbook-time-meta');
			if (this.style === 'select')
			{
				if (meta === 'previous')
				{
					this.setValue(this.getValue() - this.scale);
				}
				else if (meta === 'next')
				{
					this.setValue(this.getValue() + this.scale);
				}
				else if (meta === 'select')
				{
					this.openSelectPopup();
				}
			}
			else if (meta === 'slot')
			{
				this.setValue(parseInt(target.getAttribute('data-bx-resbook-slot')));
			}
		}
	};

	TimeSelector.prototype.getSlotsInfo = function()
	{
		var
			slots = [], slot,
			freeSlotsCount = 0,
			finishTime, hourFrom, minFrom,
			hourTo, minTo,
			part = 'morning',
			num = 0,
			time = this.timeFrom * 60;

		while (time < this.timeTo * 60)
		{
			if (time >= this.timeEvening * 60)
			{
				part = 'evening';
			}
			else if (time >= this.timeMidday * 60)
			{
				part = 'afternoon';
			}

			hourFrom = Math.floor(time / 60);
			minFrom = (time) - hourFrom * 60;
			finishTime = time + this.scale;
			hourTo = Math.floor(finishTime / 60);
			minTo = (finishTime) - hourTo * 60;

			slot = {
				time: time,
				fromTime: BX.Calendar.UserField.ResourceBooking.formatTime(hourFrom, minFrom),
				toTime: BX.Calendar.UserField.ResourceBooking.formatTime(hourTo, minTo),
				partOfTheDay: part
			};

			if (this.previewMode)
			{
				if (!num)
				{
					slot.selected = true;
				}
				else if (Math.round(Math.random() * 10) <= 3)
				{
					slot.booked = true;
				}
			}
			else if(this.availableSlotIndex)
			{
				slot.booked = !this.availableSlotIndex[time];
			}

			if (!slot.booked)
			{
				freeSlotsCount++;
			}

			slots.push(slot);
			time += this.scale;
			num++;
		}

		return {
			slots: slots,
			freeSlotsCount: freeSlotsCount
		};
	};

	TimeSelector.prototype.initCustomScrollForSlots = function()
	{
		var arrowWrap = this.DOM.controlWrap.appendChild(BX.create("div", {
			props : { className : 'calendar-resbook-webform-block-arrow-container'}
		}));

		this.DOM.leftArrow = arrowWrap.appendChild(BX.create("span",
			{
				props : {className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-prev'},
				events: {click: BX.proxy(this.handlePrevArrowClick, this)}
			}));
		this.DOM.rightArrow = arrowWrap.appendChild(BX.create("span",
			{
				props : { className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-next'},
				events: {click: BX.proxy(this.handleNextArrowClick, this)}
			}));

		this.outerWidth = parseInt(this.DOM.controlStaticWrap.offsetWidth);
		this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);

		if ('onwheel' in document)
			BX.bind(this.DOM.controlStaticWrap, "wheel", BX.proxy(this.mousewheelScrollHandler, this));
		else
			BX.bind(this.DOM.controlStaticWrap, "mousewheel", BX.proxy(this.mousewheelScrollHandler, this));

		this.checkSlotsScroll();
	};

	TimeSelector.prototype.handleNextArrowClick = function()
	{
		this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
		this.checkSlotsScroll();
	};

	TimeSelector.prototype.handlePrevArrowClick = function()
	{
		this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
		this.checkSlotsScroll();
	};

	TimeSelector.prototype.mousewheelScrollHandler = function(e)
	{
		e = e || window.event;
		var delta = e.deltaY || e.detail || e.wheelDelta;
		if (Math.abs(delta) > 0)
		{
			if (!BX.browser.IsMac())
			{
				delta = delta * 5;
			}
			this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft + delta, 0);
			this.checkSlotsScroll();
			return BX.PreventDefault(e);
		}
	};

	TimeSelector.prototype.checkSlotsScroll = function()
	{
		if (this.outerWidth <= this.innerWidth)
		{
			this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft ? '' : 'none';
			if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft)
			{
				this.DOM.rightArrow.style.display = 'none';
			}
			else
			{
				this.DOM.rightArrow.style.display = '';
			}
		}
	};

	TimeSelector.prototype.openSelectPopup = function()
	{
		if (this.isSelectPopupShown())
		{
			return this.closeSelectPopup();
		}

		this.popup = BX.PopupMenu.create(
			this.popupSelectId,
			this.DOM.stateWrap,
			this.getTimeSelectItems(),
			{
				className: "calendar-resbook-time-select-popup"	,
				angle: true,
				closeByEsc : true,
				autoHide : true,
				offsetTop: 5,
				offsetLeft: 10
			}
		);

		this.popup.show(true);

		BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function()
		{
			BX.PopupMenu.destroy(this.popupSelectId);
			this.popup = null;
		}, this));
	};

	TimeSelector.prototype.closeSelectPopup = function ()
	{
		if (this.isSelectPopupShown())
		{
			this.popup.close();
			BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
		}
	};

	TimeSelector.prototype.isSelectPopupShown = function ()
	{
		return this.popup && this.popup.popupWindow &&
			this.popup.popupWindow.isShown && this.popup.popupWindow.isShown();
	};

	TimeSelector.prototype.getTimeSelectItems = function()
	{
		var menuItems = [];
		this.slots.forEach(function(slot)
		{
			if (this.showOnlyFree && slot.booked)
			{
				return;
			}
			var className = 'menu-popup-no-icon';
			if (slot.booked)
			{
				className += ' menu-item-booked';
			}
			if (slot.selected)
			{
				className += ' menu-item-selected';
			}

			menuItems.push(
				{
					className: className,
					text: this.getTimeTextBySlot(slot),
					dataset: {
						value: slot.time,
						booked: !!slot.booked
					},
					onclick: BX.proxy(this.menuItemClick, this)
				}
			);
		}, this);
		return menuItems;
	};

	TimeSelector.prototype.menuItemClick = function(e, menuItem)
	{
		if (menuItem && menuItem.dataset && menuItem.dataset.value)
		{
			if (!menuItem.dataset.booked)
			{
				this.setValue(menuItem.dataset.value);
			}
		}
		this.closeSelectPopup();
	};

	TimeSelector.prototype.getSlotNode = function(time)
	{
		var i, slotNodes = this.DOM.controlInnerWrap.querySelectorAll('.calendar-resbook-webform-block-col-item');
		for (i = 0; i < slotNodes.length; i++)
		{
			if (parseInt(slotNodes[i].getAttribute('data-bx-resbook-slot')) === parseInt(time))
			{
				return slotNodes[i];
			}
		}
		return null;
	};

	TimeSelector.prototype.setSelected = function(slotNode)
	{
		if (BX.type.isDomNode(slotNode))
		{
			if (this.currentSelected)
			{
				BX.removeClass(this.currentSelected, 'calendar-resbook-webform-block-col-item-select');
			}
			this.currentSelected = slotNode;
			BX.addClass(slotNode, 'calendar-resbook-webform-block-col-item-select');
		}
	};
	// endregion


	// region 'ViewFormDropDownSelect'
	// Select User, Resource, Duration, Service
	function ViewFormDropDownSelect(params)
	{
		this.id = 'viewform-dropdown-select-' + Math.round(Math.random() * 100000);
		this.DOM = {
			wrap: params.wrap
		};
		this.maxHeight = params.maxHeight;
		this.selectAllMessage = BX.message('WEBF_RES_SELECT_ALL');
		this.setSettings(params);
	}

	ViewFormDropDownSelect.prototype = {
		build: function()
		{
			this.DOM.select = this.DOM.wrap.appendChild(BX.create("div", {
				attrs: {
					className: "calendar-resbook-webform-block-input calendar-resbook-webform-block-input-dropdown"
				},
				events: {click: BX.delegate(this.openPopup, this)}
			}));

			this.setSelectedValues(this.selected);
		},

		setSettings: function(params)
		{
			this.handleChangesCallback = BX.type.isFunction(params.handleChangesCallback) ? params.handleChangesCallback : null;
			this.values = params.values;
			this.selected = !BX.type.isArray(params.selected) ? [params.selected] : params.selected;
			this.multiple = params.multiple;
		},

		openPopup: function ()
		{
			if (this.isPopupShown())
			{
				return this.closePopup();
			}

			var menuItems = [];
			this.values.forEach(function(item)
			{
				var className = 'menu-popup-no-icon';
				if (BX.util.in_array(parseInt(item.id), this.selected))
				{
					className += ' menu-item-selected';
				}

				menuItems.push({
					id: item.id,
					className: className,
					text: BX.util.htmlspecialchars(item.title),
					onclick: BX.proxy(this.menuItemClick, this)
				});
			}, this);

			if (this.multiple && menuItems.length <= 1)
			{
				this.multiple = false;
			}

			if (this.multiple)
			{
				menuItems.push({
					id: 'select-all',
					text: this.selectAllMessage,
					onclick: BX.proxy(this.selectAllItemClick, this)
				});
			}

			this.popup = BX.PopupMenu.create(
				this.id,
				this.DOM.select,
				menuItems,
				{
					className: 'calendar-resbook-form-popup' + (this.multiple ? ' popup-window-resource-select' : ''),
					closeByEsc : true,
					autoHide : !this.multiple,
					offsetTop: 0,
					offsetLeft: 0
				}
			);

			this.popup.show(true);
			this.popupContainer = this.popup.popupWindow.popupContainer;
			this.popupContainer.style.width = parseInt(this.DOM.select.offsetWidth) + 'px';

			BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function()
			{
				BX.PopupMenu.destroy(this.id);
				this.popup = null;
			}, this));

			if (this.multiple)
			{
				this.popup.menuItems.forEach(function(menuItem)
				{
					var checked;
					if (menuItem.id === 'select-all')
					{
						this.selectAllChecked = !this.values.find(function(value){
							return !this.selected.find(function(itemId){return itemId === value.id});
						},this);

						menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
						menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
								'<div class="menu-popup-item-resource">' +
								'<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
								'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
								'</div>' +
							'</div>';
					}
					else
					{
						checked = this.selected.find(function(itemId){return itemId === menuItem.id});

						menuItem.layout.item.className = 'menu-popup-item';
						menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
							'<div class="menu-popup-item-resource">' +
							'<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
							'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
							'</div>' +
							'</div>';
					}
				}, this);

				BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
				setTimeout(BX.delegate(function(){
					BX.bind(document, 'click', BX.proxy(this.handleClick, this));
				}, this), 50);
			}
		},

		closePopup: function ()
		{
			if (this.isPopupShown())
			{
				this.popup.close();
				if (this.multiple)
				{
					BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
				}
			}
		},

		isPopupShown: function ()
		{
			return this.popup && this.popup.popupWindow &&
				this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() &&
				this.popup.popupWindow.popupContainer &&
				BX.isNodeInDom(this.popup.popupWindow.popupContainer)
		},


		menuItemClick: function(e, menuItem)
		{
			var
				selectAllcheckbox,
				target = e.target || e.srcElement,
				foundValue, checkbox;


			if (this.multiple)
			{
				foundValue = this.values.find(function(value){return value.id == menuItem.id;});
				checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

				if (foundValue && target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox") || BX.hasClass(target, "menu-popup-item-inner")))
				{
					if (!BX.hasClass(target, "menu-popup-item-resource-checkbox"))
					{
						checkbox.checked = !checkbox.checked;
					}

					if (checkbox.checked)
					{
						this.selectItem(foundValue);
					}
					else
					{
						this.deselectItem(foundValue);
						selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
						this.selectAllChecked = false;
						if (selectAllcheckbox)
						{
							selectAllcheckbox.checked = false;
						}
					}
					this.setSelectedValues(this.selected);
					this.handleControlChanges();
				}
			}
			else
			{
				this.setSelectedValues([menuItem.id]);
				this.handleControlChanges();
				this.closePopup();
			}


		},
		selectItem: function(value)
		{
			if (!BX.util.in_array(value.id, this.selected))
			{
				this.selected.push(value.id);
			}
		},
		deselectItem: function(value)
		{
			var index = BX.util.array_search(value.id, this.selected);
			if (index >= 0)
			{
				this.selected = BX.util.deleteFromArray(this.selected, index);
			}
		},

		selectAllItemClick: function(e, menuItem)
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
				this.selected = [];
				if (this.selectAllChecked)
				{
					this.values.forEach(function(value){this.selected.push(value.id);}, this);
				}
				this.setSelectedValues(this.selected);
				this.handleControlChanges();
			}
		},
		handleClick: function(e)
		{
			var target = e.target || e.srcElement;
			if (this.isPopupShown() && !BX.isParentForNode(this.popupContainer, target))
			{
				this.closePopup({animation: true});
			}

			this.handleControlChanges();
		},
		getSelectedValues: function()
		{
			return this.selected;
		},
		setSelectedValues: function(values)
		{
			var i,
				foundValue,
				textValues = [],
				selectedValues = [];

			for (i = 0; i < values.length; i++)
			{
				foundValue = this.values.find(function(value){return value.id === values[i];});
				if (foundValue)
				{
					textValues.push(foundValue.title);
					selectedValues.push(foundValue.id);
				}
			}

			this.selected = selectedValues;
			BX.adjust(this.DOM.select, {text: textValues.length ? textValues.join(', ') : BX.message('USER_TYPE_RESOURCE_LIST_PLACEHOLDER')});
		},

		handleControlChanges: function()
		{
			if (this.handleChangesCallback)
			{
				this.handleChangesCallback(this.getSelectedValues());
			}
		}
	};
	// endregion

	// region *ResourceBookingStatusControl*
	function ResourceBookingStatusControl(params)
	{
		this.DOM = {
			outerWrap: params.outerWrap
		};
		this.timezone = params.timezone;
		this.timezoneOffsetLabel = params.timezoneOffsetLabel;
		this.shown = false;
		this.built = false;
	}
	BX.Calendar.UserField.ResourceBookingStatusControl = ResourceBookingStatusControl;

	ResourceBookingStatusControl.prototype = {
		isShown: function()
		{
			return this.shown;
		},

		build: function()
		{
			this.DOM.wrap = this.DOM.outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-block-result'}, style: {display: 'none'}}));
			this.DOM.innerWrap = this.DOM.wrap.appendChild(BX.create("span", {props : { className : 'calendar-resbook-webform-block-result-inner'}}));

			this.DOM.labelWrap = this.DOM.innerWrap.appendChild(BX.create("span", {props : { className : 'calendar-resbook-webform-block-result-text'}, text: BX.message('WEBF_RES_BOOKING_STATUS_LABEL')}));
			this.DOM.statusWrap = this.DOM.innerWrap.appendChild(BX.create("span", {props : { className : 'calendar-resbook-webform-block-result-value'}}));
			this.DOM.statusTimezone = this.DOM.innerWrap.appendChild(BX.create("span", {props: {className: 'calendar-resbook-webform-block-result-timezone'}, text: this.timezoneOffsetLabel || '', style: {display: 'none'}}));

			this.built = true;
		},

		refresh: function(params)
		{
			if (!this.built)
			{
				this.build();
			}

			if (!this.isShown())
			{
				this.show();
			}

			if (params.dateFrom)
			{
				this.DOM.labelWrap.style.display = '';
				BX.removeClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
				if (this.timezone)
				{
					this.DOM.statusTimezone.style.display = '';
				}
				BX.adjust(this.DOM.statusWrap, {text: this.getStatusText(params)});
			}
			else if (!params.dateFrom && params.fullDay)
			{
				this.DOM.labelWrap.style.display = 'none';
				this.DOM.statusTimezone.style.display = 'none';
				BX.addClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
				BX.adjust(this.DOM.statusWrap, {text: BX.message('WEBF_RES_BOOKING_STATUS_DATE_IS_NOT_AVAILABLE')});
			}
			else
			{
				this.DOM.labelWrap.style.display = 'none';
				this.DOM.statusTimezone.style.display = 'none';
				BX.removeClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
				BX.adjust(this.DOM.statusWrap, {text: BX.message('WEBF_RES_BOOKING_STATUS_NO_TIME_SELECTED')});
			}
		},

		getStatusText: function(params)
		{
			var
				dateFrom = params.dateFrom,
				dateTo = new Date(dateFrom.getTime() + params.duration * 60 * 1000 + (params.fullDay ? -1 : 0)),
				text = '';

			if (params.fullDay)
			{
				if (BX.date.format('Y-m-d', dateFrom.getTime() / 1000) === BX.date.format('Y-m-d', dateTo.getTime() / 1000))
				{
					text = BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_STATUS'), dateFrom);
				}
				else
				{
					text = BX.message('WEBF_RES_DATE_FORMAT_FROM_TO')
						.replace('#DATE_FROM#', BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateFrom))
						.replace('#DATE_TO#', BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateTo));
				}
			}
			else
			{
				if (BX.date.format('Y-m-d', dateFrom.getTime() / 1000) === BX.date.format('Y-m-d', dateTo.getTime() / 1000))
				{
					text = BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_STATUS'), dateFrom)
						+ ' '
						+ BX.message('WEBF_RES_TIME_FORMAT_FROM_TO')
							.replace('#TIME_FROM#', BX.Calendar.UserField.ResourceBooking.formatTime(dateFrom.getHours(), dateFrom.getMinutes()))
							.replace('#TIME_TO#', BX.Calendar.UserField.ResourceBooking.formatTime(dateTo.getHours(), dateTo.getMinutes()));
				}
				else
				{
					text = BX.message('WEBF_RES_DATE_FORMAT_FROM_TO')
							.replace('#DATE_FROM#', BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateFrom) + ' '+ BX.Calendar.UserField.ResourceBooking.formatTime(dateFrom.getHours(), dateFrom.getMinutes()))
							.replace('#DATE_TO#', BX.date.format(BX.message('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateTo) + ' '+ BX.Calendar.UserField.ResourceBooking.formatTime(dateTo.getHours(), dateTo.getMinutes()));
				}
			}

			return text;
		},

		hide: function()
		{
			if (this.built && this.shown)
			{
				this.DOM.wrap.style.display = 'none';
				this.shown = false;
			}
		},

		show: function()
		{
			if (this.built && !this.shown)
			{
				this.DOM.wrap.style.display = '';
				this.shown = true;
			}
		},

		setError: function(message)
		{
			if (this.DOM.labelWrap)
			{
				this.DOM.labelWrap.style.display = 'none';
			}
			BX.addClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
			BX.adjust(this.DOM.statusWrap, {text: message});
		}
	};
	//endregion

})();