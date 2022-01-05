
;(function()
{
	'use strict';
	BX.namespace('BX.Calendar.UserField');

	// region 'CrmFormResourceBookingField'
	function CrmFormResourceBookingField(params)
	{
		this.params = params;
		this.complexFields = {};
		this.userFieldParams = null;
		this.id = 'resbook-settings-popup-' + Math.round(Math.random() * 100000);
		this.params.settings.data = this.settingsData = this.getSettingsData(this.params.settings.data);

		this.DOM = {
			innerWrap: this.params.innerWrap,
			settingsWrap: this.params.innerWrap.appendChild(BX.create("div", {attrs: {'data-bx-resource-field-settings': 'Y'}})),
			captionNode: this.params.captionNode,
			settingsInputs: {}
		};
	}
	BX.Calendar.UserField.CrmFormResourceBookingField = CrmFormResourceBookingField;

	CrmFormResourceBookingField.prototype = {
		init: function(){
			// Request field params
			this.showFieldLoader();
			BX.Calendar.UserField.getUserFieldParams({fieldName: this.params.entityName, selectedUsers: this.getSelectedUsers()}).then(
				BX.delegate(function(fieldParams)
				{
					this.hideFieldLoader();
					this.userFieldParams = fieldParams;

					this.fieldLayout = new BX.Calendar.UserField.ResourceBookingFieldViewLayout({
						wrap: this.DOM.innerWrap,
						displayTitle: false,
						title: this.getCaption(),
						settings: this.getSettings()
					});
					this.fieldLayout.build();
					this.updateSettingsDataInputs();
				}, this)
			);
		},

		showSettingsPopup: function()
		{
			BX.Calendar.UserField.getUserFieldParams({fieldName: this.params.entityName, selectedUsers: this.getSelectedUsers()}).then(
				BX.delegate(function(fieldParams)
				{
					this.userFieldParams = fieldParams;
					this.settingsPopupId = 'calendar-resourcebooking-settings-popup-' + Math.round(Math.random() * 100000);
					this.settingsPopup = new BX.PopupWindow(
						this.settingsPopupId,
						null,
						{
							content: this.getSettingsContentNode(),
							className: 'calendar-resbook-webform-settings-popup-window',
							autoHide: false,
							lightShadow: true,
							closeByEsc: true,
							overlay: {backgroundColor: 'black', opacity: 500},
							zIndex: -400,
							titleBar: BX.message('WEBF_RES_SETTINGS'),
							closeIcon: true,
							buttons : [new BX.PopupWindowButton({})]
						});

					var buttonNodeWrap = this.settingsPopup.buttons[0].buttonNode.parentNode;
					BX.remove(this.settingsPopup.buttons[0].buttonNode);
					this.settingsPopup.buttons[0].buttonNode = buttonNodeWrap.appendChild(BX.create(
						"button",
						{
							props : { className : 'ui-btn ui-btn-success'},
							events: {click: BX.proxy(function(){this.settingsPopup.close();}, this)},
							text : BX.message('WEBF_RES_CLOSE_SETTINGS_POPUP')
						}
					));

					BX.removeClass(this.settingsPopup.buttons[0].buttonNode, 'popup-window-button');
					this.settingsPopup.show();

					BX.addCustomEvent(this.settingsPopup, 'onPopupClose', BX.delegate(function(popup)
					{
						this.destroyControls();
						this.settingsPopup.destroy(this.id);
						this.settingsPopup = null;
						if (this.previewFieldLayout)
						{
							this.previewFieldLayout.destroy();
						}
					}, this));
				}, this)
			);
		},

		getSettingsContentNode: function()
		{
			var outerWrap = BX.create("div", {props : { className : 'calendar-resbook-webform-settings-popup'}});

			var leftWrap = outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-settings-popup-inner'}}));
			this.buildSettingsForm({wrap: leftWrap});

			var previewWrap = outerWrap.appendChild(BX.create("div", {props : { className : 'calendar-resbook-webform-settings-popup-preview'}}));

			this.previewFieldLayout = new BX.Calendar.UserField.ResourceBookingFieldPreviewLayout({
				wrap: previewWrap,
				title: this.getCaption(),
				settings: this.getSettings()
			});
			this.previewFieldLayout.build();

			BX.addCustomEvent('ResourceBooking.webformSettings:onChanged', BX.proxy(this.handleWebformSettingsChanges, this));

			return outerWrap;
		},

		buildSettingsForm: function(params)
		{
			var
				settings = this.getSettings(),
				wrap = params.wrap,
				titleId = 'title-' + this.id;

			this.DOM.captionWrap = wrap.appendChild(BX.create("div", {
				props : { className : 'calendar-resbook-webform-settings-popup-title'},
				html: '<label for="' + titleId + '" class="calendar-resbook-webform-settings-popup-label">' + BX.message('WEBF_RES_NAME_LABEL') + '</label>'
			}));
			this.DOM.captionInput = this.DOM.captionWrap.appendChild(BX.create("input", {
				attrs: {
					id: titleId,
					className: "calendar-resbook-webform-settings-popup-input",
					type: "text",
					value: this.getCaption()
				},
				events: {
					change: BX.proxy(this.updateCaption, this),
					blur: BX.proxy(this.updateCaption, this),
					keyup: BX.proxy(this.updateCaption, this)
				}
			}));
			this.updateCaption();

			this.DOM.fieldsOuterWrap = wrap.appendChild(BX.create("div", {
				props : { className : 'calendar-resbook-webform-settings-popup-content'},
				html: '<div class="calendar-resbook-webform-settings-popup-head">' +
						'<div class="calendar-resbook-webform-settings-popup-head-inner">' +
							'<span class="calendar-resbook-webform-settings-popup-head-text">' + BX.message('WEBF_RES_FIELD_NAME') + '</span>' +
							'<span class="calendar-resbook-webform-settings-popup-head-decs">' + BX.message('WEBF_RES_FIELD_NAME_IN_FORM') + '</span>' +
						'</div>' +
						'<div class="calendar-resbook-webform-settings-popup-head-inner">' +
							'<span class="calendar-resbook-webform-settings-popup-head-text">' + BX.message('WEBF_RES_FIELD_SHOW_IN_FORM') + '</span>' +
						'</div>' +
					'</div>'
			}));

			this.DOM.fieldsWrap = this.DOM.fieldsOuterWrap.appendChild(BX.create("div", {
				props : { className : 'calendar-resbook-webform-settings-popup-list'}
			}));

			if (settings.userfieldSettings.useUsers)
			{
				this.buildComplexField('users', {
					wrap: this.DOM.fieldsWrap,
					changeSettingsCallback: BX.delegate(this.updateSettings, this),
					params: settings.data.users,
					config: {
						users: settings.userfieldSettings.users,
						selected: settings.data.users.value
					}
				});

				BX.addCustomEvent('ResourceBooking.settingsUserSelector:onChanged', BX.proxy(this.checkBitrix24Limitation, this));
			}

			if (settings.userfieldSettings.useResources)
			{
				this.buildComplexField('resources', {
					wrap: this.DOM.fieldsWrap,
					changeSettingsCallback: BX.delegate(this.updateSettings, this),
					params: settings.data.resources,
					config: {
						resources: settings.userfieldSettings.resources,
						selected: settings.data.resources.value
					}
				});
			}

			if (settings.userfieldSettings.useServices)
			{
				this.buildComplexField('services', {
					wrap: this.DOM.fieldsWrap,
					changeSettingsCallback: BX.delegate(this.updateSettings, this),
					params: settings.data.services,
					config: {
						services: settings.userfieldSettings.services,
						selected: settings.data.services.value
					}
				});
			}
			else
			{
				this.buildComplexField('duration', {
					wrap: this.DOM.fieldsWrap,
					changeSettingsCallback: BX.delegate(this.updateSettings, this),
					params: settings.data.duration
				});
			}

			this.buildComplexField('date', {
				wrap: this.DOM.fieldsWrap,
				changeSettingsCallback: BX.delegate(this.updateSettings, this),
				params: settings.data.date
			});

			if (!settings.userfieldSettings.fullDay)
			{
				this.buildComplexField('time', {
					wrap: this.DOM.fieldsWrap,
					changeSettingsCallback: BX.delegate(this.updateSettings, this),
					params: settings.data.time
				});
			}

			this.DOM.fieldsWrap.appendChild(BX.create("div", {
				props : { className : 'calendar-resbook-webform-settings-popup-item'},
				html: '<div class="calendar-resbook-webform-settings-popup-decs">' +
					(BX.message('WEBF_RES_BOOKING_SETTINGS_HELP')
						.replace('#START_LINK#', '<a href="javascript:void(0);"' +
							' onclick="if (top.BX.Helper){top.BX.Helper.show(\'redirect=detail&code=8366733\');}">')
						.replace('#END_LINK#', '</a>')) +
					'</div>'
			}));
		},

		destroyControls: function()
		{
			for (var k in this.complexFields)
			{
				if (this.complexFields.hasOwnProperty(k) && BX.type.isFunction(this.complexFields[k].destroy))
				{
					this.complexFields[k].destroy();
				}
			}
		},

		handleWebformSettingsChanges: function()
		{
			if (this.refreshLayoutTimeout)
			{
				this.refreshLayoutTimeout = clearTimeout(this.refreshLayoutTimeout);
			}

			this.refreshLayoutTimeout = setTimeout(BX.delegate(function()
			{
				// Update settings and inputs
				for (var k in this.complexFields)
				{
					if (this.complexFields.hasOwnProperty(k) && BX.type.isFunction(this.complexFields[k].getValue))
					{
						this.settingsData[k] = this.complexFields[k].getValue();
					}
				}
				this.updateSettingsDataInputs();

				// Refresh preview
				this.previewFieldLayout.refreshLayout(this.settingsData);
				// Refresh form layout (behind the settings popup)
				this.fieldLayout.refreshLayout(this.settingsData);

				// Small Hack to make form look better - height adjusment
				this.previewFieldLayout.getOuterWrap().style.maxHeight = Math.round(this.previewFieldLayout.getInnerWrap().offsetHeight * 0.73) + 'px';
			}, this), 100);
		},

		buildComplexField: function(type, params)
		{
			switch(type)
			{
				case 'users':
					this.complexFields[type] = new UsersComplexTuneFormField();
					break;
				case 'resources':
					this.complexFields[type] = new ResourcesComplexTuneFormField();
					break;
				case 'services':
					this.complexFields[type] = new ServicesComplexTuneFormField();
					break;
				case 'duration':
					this.complexFields[type] = new DurationComplexTuneFormField();
					break;
				case 'date':
					this.complexFields[type] = new DateComplexTuneFormField();
					break;
				case 'time':
					this.complexFields[type] = new TimeComplexTuneFormField();
					break;
			}

			this.complexFields[type].build(params);
		},

		getSettingsData: function(data)
		{
			var
				field, option,
				settingsData = BX.clone(this.getDefaultSettingsData(), true);

			if (BX.type.isPlainObject(data))
			{
				for (field in data)
				{
					if (data.hasOwnProperty(field) && settingsData[field])
					{
						if (BX.type.isPlainObject(data[field]))
						{
							for (option in data[field])
							{
								if (data[field].hasOwnProperty(option))
								{
									settingsData[field][option] = data[field][option];
								}
							}
						}
						else
						{
							settingsData[field] = data[field];
						}
					}
				}
			}

			return settingsData;
		},

		getDefaultSettingsData: function()
		{
			return {
				users : {
					show: 'Y',
					label: BX.message('WEBF_RES_USERS_LABEL'),
					defaultMode: 'auto', // none|auto
					value: null
				},
				resources: {
					show: 'Y',
					label: BX.message('WEBF_RES_RESOURCES_LABEL'),
					defaultMode: 'auto', // none|auto
					multiple: 'N',
					value: null
				},
				services: {
					show: 'Y',
					label: BX.message('WEBF_RES_SERVICE_LABEL'),
					value: null
				},
				duration: {
					show: 'Y',
					label: BX.message('WEBF_RES_DURATION_LABEL'),
					defaultValue: 60,
					manualInput: 'N'
				},
				date: {
					label: BX.message('WEBF_RES_DATE_LABEL'),
					style: 'line', // line|popup
					start: 'today'
				},
				time: {
					label: BX.message('WEBF_RES_TIME_LABEL'),
					style: 'slots',
					showOnlyFree: 'Y',
					showFinishTime: 'N',
					scale: 60
				}
			}
		},

		getSelectedUsers: function()
		{
			return this.settingsData && this.settingsData.users && BX.type.isString(this.settingsData.users.value) ? this.settingsData.users.value.split('|') : [];
		},

		updateSettingsDataInputs: function()
		{
			var field, option;
			for (field in this.settingsData)
			{
				if (this.settingsData.hasOwnProperty(field))
				{
					if (BX.type.isPlainObject(this.settingsData[field]))
					{
						for (option in this.settingsData[field])
						{
							if (this.settingsData[field].hasOwnProperty(option))
							{
								this.updateSettingsInputValue([field, option], this.settingsData[field][option]);
							}
						}
					}
					else
					{
						this.updateSettingsInputValue([field], this.settingsData[field]);
					}
				}
			}
		},

		updateSettingsInputValue: function(key, value)
		{
			var uniKey = key.join('-');
			if (!this.DOM.settingsInputs[uniKey])
			{
				this.DOM.settingsInputs[uniKey] = this.DOM.settingsWrap.appendChild(BX.create("input", {
					attrs: {
						type: "hidden",
						name: this.params.formName + '[SETTINGS_DATA][' + key.join('][') + ']'
					}
				}));
			}

			if (BX.type.isArray(value))
			{
				value = value.join('|');
			}

			this.DOM.settingsInputs[uniKey].value = value;
		},

		showFieldLoader: function ()
		{
			if (this.DOM.innerWrap)
			{
				this.hideFieldLoader();
				this.DOM.fieldLoader = this.DOM.innerWrap.appendChild(BX.Calendar.UserField.ResourceBooking.getLoader(100));
			}
		},

		hideFieldLoader: function ()
		{
			BX.remove(this.DOM.fieldLoader);
		},

		getSettings: function()
		{
			if (!this.params.settings.userfieldSettings)
			{
				this.params.settings.userfieldSettings = {
					resources: this.userFieldParams.SETTINGS.SELECTED_RESOURCES,
					users: this.userFieldParams.SETTINGS.SELECTED_USERS,
					services: this.userFieldParams.SETTINGS.SERVICE_LIST,
					fullDay: this.userFieldParams.SETTINGS.FULL_DAY === 'Y',
					useResources: this.userFieldParams.SETTINGS.USE_RESOURCES === 'Y'
						&& this.userFieldParams.SETTINGS.SELECTED_RESOURCES.length,
					useUsers: this.userFieldParams.SETTINGS.USE_USERS === 'Y',
					useServices: this.userFieldParams.SETTINGS.USE_SERVICES === 'Y',
					resourceLimit: this.userFieldParams.SETTINGS.RESOURCE_LIMIT,
					userIndex: this.userFieldParams.SETTINGS.USER_INDEX
				}
			}

			return this.params.settings;
		},

		updateSettings: function(settings)
		{
		},

		getCaption: function()
		{
			return this.params.settings.caption;
		},

		updateCaption: function()
		{
			var caption = this.DOM.captionInput.value;
			if (this.params.settings.caption !== caption || !this.DOM.settingsInputs.caption)
			{
				this.params.settings.caption = caption;
				if (this.previewFieldLayout)
				{
					this.previewFieldLayout.updateTitle(this.params.settings.caption);
				}

				// Update title
				if (!this.DOM.settingsInputs.caption)
				{
					this.DOM.settingsInputs.caption = this.DOM.settingsWrap.appendChild(BX.create("input", {
						attrs: {
							type: "hidden",
							name: this.params.formName + '[CAPTION]'
						}
					}));
				}
				this.DOM.settingsInputs.caption.value = this.params.settings.caption;

				if (this.DOM.captionNode)
				{
					BX.adjust(this.DOM.captionNode, {text: this.params.settings.caption});
				}
			}
		},

		isRequired: function()
		{
			return this.params.settings.required === 'Y';
		},

		updateRequiredValue: function()
		{
			this.params.settings.required = this.DOM.requiredCheckbox.checked ? 'Y' : 'N';
			if (!this.DOM.settingsInputs.required)
			{
				this.DOM.settingsInputs.required = this.DOM.settingsWrap.appendChild(BX.create("input", {
					attrs: {
						type: "hidden",
						name: this.params.formName + '[REQUIRED]'
					}
				}));
			}
			this.DOM.settingsInputs.required.value = this.params.settings.required;
		},

		checkBitrix24Limitation: function()
		{
			var
				count = 0,
				settings = this.getSettings();

			if (BX.type.isArray(this.params.settings.userfieldSettings.resources))
			{
				count += this.params.settings.userfieldSettings.resources.length;
			}

			if (settings.userfieldSettings.useUsers && this.complexFields.users)
			{
				var usersValue = this.complexFields.users.getValue();
				if (usersValue && BX.type.isArray(usersValue.value))
				{
					count += usersValue.value.length;
				}
			}

			if (settings.userfieldSettings.resourceLimit > 0 && count > settings.userfieldSettings.resourceLimit)
			{
				BX.Calendar.UserField.ResourceBooking.showLimitationPopup();
			}
		}
	};
	// endregion


	// region 'ComplexTuneFormFieldAbstract'
	function ComplexTuneFormFieldAbstract()
	{
		this.label = '';
		this.formLabel = '';
		this.displayed = false;
		this.displayCheckboxDisabled = false;
		this.DOM = {};
	}


	ComplexTuneFormFieldAbstract.prototype = {
		build: function (params)
		{
			this.updateConfig(params.params);

			this.DOM.fieldWrap = BX.create("div", {props: {className: 'calendar-resbook-webform-settings-popup-item'}});

			this.DOM.labelWrap = this.DOM.fieldWrap.appendChild(BX.create("div", {
				props: {className: 'calendar-resbook-webform-settings-popup-field'}
			}));
			this.DOM.labelNode = this.DOM.labelWrap.appendChild(BX.create("span", {
				props: {className: 'calendar-resbook-webform-settings-popup-field-title'}, text: this.getLabel()
			}));

			// Label in form
			this.DOM.formTitleWrap = this.DOM.labelWrap.appendChild(BX.create("span", {
				props: {
					className: 'calendar-resbook-webform-settings-popup-field-subtitle' + (this.isDisplayed() ? ' show' : '')
				}
			}));
			this.DOM.formTitleLabel = this.DOM.formTitleWrap.appendChild(BX.create("span", {
				props: {className: 'calendar-resbook-webform-settings-popup-field-subtitle-text'},
				text: this.getFormLabel(),
				events: {click: BX.proxy(this.enableFormTitleEditMode, this)}
			}));
			this.DOM.formTitleEditIcon = this.DOM.formTitleWrap.appendChild(BX.create("span", {
				props: {className: 'calendar-resbook-webform-settings-popup-field-edit'},
				events: {click: BX.proxy(this.enableFormTitleEditMode, this)}
			}));

			// Display checkbox
			this.DOM.checkboxNode = this.DOM.fieldWrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-settings-popup-checkbox-container'}})).appendChild(BX.create("input", {
				attrs: {
					type: "checkbox", value: 'Y', checked: this.isDisplayed(), disabled: this.displayCheckboxDisabled
				}, events: {
					click: BX.delegate(this.checkDisplayMode, this)
				}
			}));

			// State popup
			this.buildStatePopup({
				wrap: this.DOM.fieldWrap, config: params.config || {}
			});

			// Value popup
			this.buildValuePopup({
				wrap: this.DOM.fieldWrap, config: params.config || {}
			});

			if (BX.type.isFunction(params.changeSettingsCallback))
			{
				this.changeSettingsCallback = params.changeSettingsCallback;
			}

			params.wrap.appendChild(this.DOM.fieldWrap);
		},

		destroy: function()
		{
			if (this.valuePopup && BX.type.isFunction(this.valuePopup.closePopup))
			{
				this.valuePopup.closePopup();
			}
			if (this.statePopup && BX.type.isFunction(this.statePopup.closePopup))
			{
				this.statePopup.closePopup();
			}
		},

		updateConfig: function (params)
		{
			this.setFormLabel(params.label || this.formLabel);
			if (params.show)
			{
				this.displayed = params.show !== 'N';
			}
		},

		buildStatePopup: function (params)
		{
		},

		buildValuePopup: function (params)
		{
		},

		getLabel: function ()
		{
			return this.label;
		},

		getFormLabel: function ()
		{
			return this.formLabel;
		},

		setFormLabel: function (formLabel)
		{
			this.formLabel = formLabel || '';
		},

		isDisplayed: function ()
		{
			return this.displayed;
		},

		checkDisplayMode: function ()
		{
			this.displayed = !!this.DOM.checkboxNode.checked;
			if (this.displayed)
			{
				this.displayInForm();
			}
			else
			{
				this.hideInForm();
			}
		},

		displayInForm: function ()
		{
			BX.addClass(this.DOM.formTitleWrap, 'show');
			this.triggerChangeRefresh();
		},

		hideInForm: function ()
		{
			BX.removeClass(this.DOM.formTitleWrap, 'show');
			this.triggerChangeRefresh();
		},

		enableFormTitleEditMode: function()
		{
			if (!this.DOM.formTitleInputNode)
			{
				this.DOM.formTitleInputNode = this.DOM.formTitleWrap.appendChild(BX.create("input", {
					attrs: {
						type: 'text',
						className: 'calendar-resbook-webform-settings-popup-field-subtitle-text'
					},
					events: {blur: BX.proxy(this.finishFormTitleEditMode, this)}
				}));
			}

			this.DOM.formTitleInputNode.value = this.getFormLabel();
			this.DOM.formTitleInputNode.style.display = '';
			this.DOM.formTitleLabel.style.display = 'none';
			this.DOM.formTitleEditIcon.style.display = 'none';
			this.DOM.formTitleInputNode.focus();
		},

		finishFormTitleEditMode: function()
		{
			this.setFormLabel(this.DOM.formTitleInputNode.value);
			BX.adjust(this.DOM.formTitleLabel, {text: this.getFormLabel()});
			this.DOM.formTitleLabel.style.display = '';
			this.DOM.formTitleEditIcon.style.display = '';
			this.DOM.formTitleInputNode.style.display = 'none';
			this.triggerChangeRefresh();
		},

		getSettingsValue: function() {},

		triggerChangeRefresh: function()
		{
			setTimeout(BX.delegate(function(){BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');}, this), 50);
		}
	};
	//endregion


	// region 'UsersComplexTuneFormField'
	function UsersComplexTuneFormField()
	{
		UsersComplexTuneFormField.superclass.constructor.apply(this, arguments);
		this.label = BX.message('WEBF_RES_USERS');
		this.formLabel = BX.message('WEBF_RES_USERS_LABEL');
		this.displayed = true;
	}
	BX.extend(UsersComplexTuneFormField, ComplexTuneFormFieldAbstract);

	UsersComplexTuneFormField.prototype.updateConfig = function(params)
	{
		UsersComplexTuneFormField.superclass.updateConfig.apply(this, arguments);
		this.defaultMode = params.defaultMode;
	};

	UsersComplexTuneFormField.prototype.buildStatePopup = function(params)
	{
		params.isDisplayed = BX.proxy(this.isDisplayed, this);
		params.defaultMode = params.defaultMode || this.defaultMode;
		this.statePopup = new UsersStatePopup(params);
	};

	UsersComplexTuneFormField.prototype.buildValuePopup = function (params)
	{
		this.valuePopup = new UsersValuePopup(params);
	};

	UsersComplexTuneFormField.prototype.displayInForm = function ()
	{
		UsersComplexTuneFormField.superclass.displayInForm.apply(this, arguments);
		this.statePopup.handleControlChanges();
		this.statePopup.setEnabled();
	};

	UsersComplexTuneFormField.prototype.hideInForm = function ()
	{
		UsersComplexTuneFormField.superclass.hideInForm.apply(this, arguments);
		this.statePopup.handleControlChanges();
		this.statePopup.setDisabled();
	};
	UsersComplexTuneFormField.prototype.getValue = function ()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			defaultMode: this.statePopup.getDefaultMode(),
			value: this.valuePopup.getSelectedValues()
		};
	};
	//endregion

	// region 'ResourcesComplexTuneFormField'
	function ResourcesComplexTuneFormField()
	{
		ResourcesComplexTuneFormField.superclass.constructor.apply(this, arguments);
		this.label = BX.message('WEBF_RES_RESOURCES');
		this.formLabel = BX.message('WEBF_RES_RESOURCES_LABEL');
		this.displayed = true;
	}
	BX.extend(ResourcesComplexTuneFormField, ComplexTuneFormFieldAbstract);

	ResourcesComplexTuneFormField.prototype.updateConfig = function(params)
	{
		ResourcesComplexTuneFormField.superclass.updateConfig.apply(this, arguments);
		this.defaultMode = params.defaultMode;
		this.multiple = params.multiple === 'Y';
	};

	ResourcesComplexTuneFormField.prototype.buildStatePopup = function(params)
	{
		params.isDisplayed = BX.proxy(this.isDisplayed, this);
		params.defaultMode = params.defaultMode || this.defaultMode;
		params.multiple = params.multiple == null ? this.multiple : params.multiple;
		this.statePopup = new ResourcesStatePopup(params);
	};
	ResourcesComplexTuneFormField.prototype.buildValuePopup = function (params)
	{
		this.valuePopup = new ResourcesValuePopup(params);
	};
	ResourcesComplexTuneFormField.prototype.displayInForm = function ()
	{
		ResourcesComplexTuneFormField.superclass.displayInForm.apply(this, arguments);
		this.statePopup.handleControlChanges();
		this.statePopup.setEnabled();
	};

	ResourcesComplexTuneFormField.prototype.hideInForm = function ()
	{
		ResourcesComplexTuneFormField.superclass.hideInForm.apply(this, arguments);
		this.statePopup.handleControlChanges();
		this.statePopup.setDisabled();
	};

	ResourcesComplexTuneFormField.prototype.getValue = function ()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			defaultMode: this.statePopup.getDefaultMode(),
			multiple: this.statePopup.getMultiple() ? 'Y' : 'N',
			value: this.valuePopup.getSelectedId()
		};
	};
	// endregion


	// region 'ServicesComplexTuneFormField'
	function ServicesComplexTuneFormField()
	{
		ServicesComplexTuneFormField.superclass.constructor.apply(this, arguments);
		this.label = BX.message('WEBF_RES_SERVICES');
		this.formLabel = BX.message('WEBF_RES_SERVICE_LABEL');
		this.displayed = true;
	}
	BX.extend(ServicesComplexTuneFormField, ComplexTuneFormFieldAbstract);

	ServicesComplexTuneFormField.prototype.buildStatePopup = function(params)
	{
		if (params && BX.type.isDomNode(params.wrap))
		{
			params.wrap.appendChild(BX.create("div", {
				props: {className:'calendar-resbook-webform-settings-popup-select disabled'},
				html: '<span class="calendar-resbook-webform-settings-popup-select-value">' + BX.message('WEBF_RES_FROM_LIST') + '</span>'
			}));
		}
	};

	ServicesComplexTuneFormField.prototype.buildValuePopup = function (params)
	{
		this.valuePopup = new ServiceValuePopup(params);
	};

	ServicesComplexTuneFormField.prototype.getValue = function ()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			value: this.valuePopup.getSelectedValues()
		};
	};
	// endregion


	// region 'DurationComplexTuneFormField'
	function DurationComplexTuneFormField()
	{
		DurationComplexTuneFormField.superclass.constructor.apply(this, arguments);
		this.label = BX.message('WEBF_RES_DURATION');
		this.formLabel = BX.message('WEBF_RES_DURATION_LABEL');
	}
	BX.extend(DurationComplexTuneFormField, ComplexTuneFormFieldAbstract);

	DurationComplexTuneFormField.prototype.updateConfig = function(params)
	{
		DurationComplexTuneFormField.superclass.updateConfig.apply(this, arguments);
		this.defaultValue = params.defaultValue;
		this.manualInput = params.manualInput === 'Y';
	};

	DurationComplexTuneFormField.prototype.buildStatePopup = function(params)
	{
		params.isDisplayed = BX.proxy(this.isDisplayed, this);
		params.defaultValue = this.defaultValue;
		params.manualInput = this.manualInput;
		this.statePopup = new DurationStatePopup(params);
	};

	DurationComplexTuneFormField.prototype.displayInForm = function ()
	{
		DurationComplexTuneFormField.superclass.displayInForm.apply(this, arguments);
		this.statePopup.handleControlChanges();
	};

	DurationComplexTuneFormField.prototype.hideInForm = function ()
	{
		DurationComplexTuneFormField.superclass.hideInForm.apply(this, arguments);
		this.statePopup.handleControlChanges();
	};

	DurationComplexTuneFormField.prototype.getValue = function ()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			defaultValue: this.statePopup.getDefaultValue(),
			manualInput: this.statePopup.getManualInput() ? 'Y' : 'N'
		};
	};
	// endregion


	// region 'DateComplexTuneFormField'
	function DateComplexTuneFormField()
	{
		DateComplexTuneFormField.superclass.constructor.apply(this, arguments);
		this.label = BX.message('WEBF_RES_DATE');
		this.formLabel = BX.message('WEBF_RES_DATE_LABEL');
		this.displayed = true;
		this.displayCheckboxDisabled = true;
	}
	BX.extend(DateComplexTuneFormField, ComplexTuneFormFieldAbstract);

	DateComplexTuneFormField.prototype.updateConfig = function(params)
	{
		DateComplexTuneFormField.superclass.updateConfig.apply(this, arguments);
		this.style = params.style;
		this.start = params.start;
	};

	DateComplexTuneFormField.prototype.buildStatePopup = function(params)
	{
		params.style = params.style || this.style;
		params.start = params.start || this.start;
		this.statePopup = new DateStatePopup(params);
	};

	DateComplexTuneFormField.prototype.getValue = function ()
	{
		return {
			label: this.getFormLabel(),
			style: this.statePopup.getStyle(),
			start: this.statePopup.getStart()
		};
	};
	// endregion


	// region 'TimeComplexTuneFormField'
	function TimeComplexTuneFormField()
	{
		TimeComplexTuneFormField.superclass.constructor.apply(this, arguments);
		this.label = BX.message('WEBF_RES_TIME');
		this.formLabel = BX.message('WEBF_RES_TIME_LABEL');
		this.displayed = true;
		this.displayCheckboxDisabled = true;
	}
	BX.extend(TimeComplexTuneFormField, ComplexTuneFormFieldAbstract);


	TimeComplexTuneFormField.prototype.updateConfig = function(params)
	{
		TimeComplexTuneFormField.superclass.updateConfig.apply(this, arguments);
		this.style = params.style;
		this.showOnlyFree = params.showOnlyFree === 'Y';
		this.showFinishTime = params.showFinishTime === 'Y';
		this.scale = parseInt(params.scale);
	};

	TimeComplexTuneFormField.prototype.buildStatePopup = function(params)
	{
		params.style = params.style || this.style;
		params.showOnlyFree = this.showOnlyFree;
		params.showFinishTime = this.showFinishTime;
		params.scale = this.scale;
		this.statePopup = new TimeStatePopup(params);
	};

	TimeComplexTuneFormField.prototype.getValue = function ()
	{
		return {
			label: this.getFormLabel(),
			style: this.statePopup.getStyle(),
			showFinishTime: this.statePopup.getShowFinishTime(),
			showOnlyFree: this.statePopup.getShowOnlyFree(),
			scale: this.statePopup.getScale()
		};
	};
	// endregion


	// region 'ComplexTunePopup'
	function ComplexTunePopup(params)
	{
		this.id = 'resourcebooking-settings-popup-' + Math.round(Math.random() * 100000);
		this.menuItems = [];
		this.DOM = {
			outerWrap: params.wrap
		};
	}

	ComplexTunePopup.prototype = {
		build: function()
		{
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(BX.create("div", {props:{className:'calendar-resbook-webform-settings-popup-select'}}));

			this.DOM.currentStateLink = this.DOM.innerWrap.appendChild(
				BX.create("span",
					{
						props : { className : 'calendar-resbook-webform-settings-popup-select-value'},
						text: this.getCurrentModeState(),
						events: {click: BX.delegate(this.showPopup, this)}
					}
				)
			);
		},

		showPopup: function()
		{
			if (this.isPopupShown() || this.disabled)
			{
				return this.closePopup();
			}

			this.menuItems = this.getMenuItems();

			this.popup = BX.PopupMenu.create(
				this.id,
				this.DOM.currentStateLink,
				this.menuItems,
				{
					className: 'popup-window-resource-select',
					closeByEsc : true,
					autoHide : false,
					offsetTop: 0,
					offsetLeft: 0
				}
			);

			this.popup.popupWindow.setAngle({offset: 30, position: 'top'});
			this.popup.show(true);
			this.popupContainer = this.popup.popupWindow.popupContainer;
			//this.popupContainer.style.width = parseInt(this.DOM.blocksWrap.offsetWidth) + 'px';

			BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function()
			{
				BX.PopupMenu.destroy(this.id);
				this.popup = null;
			}, this));

			this.popup.menuItems.forEach(function(menuItem)
			{
				var inputType = false, className, checked, inputNameStr = '';
				if (menuItem.dataset && menuItem.dataset.type)
				{
					checked = menuItem.dataset.checked;

					var menuItemClassName = 'menu-popup-item';
					if (menuItem.dataset.type === 'radio')
					{
						inputType = 'radio';
						className = 'menu-popup-item-resource-radio';
						if (menuItem.dataset.inputName)
						{
							inputNameStr = ' name="' + menuItem.dataset.inputName + '" ';
						}
					}
					else if (menuItem.dataset.type === 'checkbox')
					{
						inputType = 'checkbox';
						className = 'menu-popup-item-resource-checkbox';
					}

					var innerHtml = '<div class="menu-popup-item-inner">';
					if (menuItem.dataset.type === 'submenu-list')
					{
						menuItemClassName += ' menu-popup-item-submenu';
						innerHtml += '<div class="menu-popup-item-resource menu-popup-item-resource-wide">' +
						'<span class="menu-popup-item-text">' +
							'<span>' + menuItem.text + '</span>' +
								'<span class="menu-popup-item-resource-subvalue">' + (menuItem.dataset.textValue || menuItem.dataset.value) + '</span>' +
						'</span>' +
						'</div>';
					}
					else if (inputType)
					{
						innerHtml += '<div class="menu-popup-item-resource">';
						if (inputType)
						{
							innerHtml += '<input class="' + className + '" type="' + inputType + '"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '" ' + inputNameStr + '>' +
								'<label class="menu-popup-item-text"  for="' + menuItem.id + '">' + menuItem.text + '</label>';
						}
						innerHtml += '</div>';
					}

					innerHtml += '</div>';

					menuItem.layout.item.className = menuItemClassName;
					menuItem.layout.item.innerHTML = innerHtml;
				}
			}, this);

			setTimeout(BX.delegate(function(){
				BX.bind(document, 'click', BX.proxy(this.handleClick, this));
			}, this), 50);
		},

		closePopup: function ()
		{
			if (this.isPopupShown())
			{
				this.popup.close();
				this.popupContainer.style.maxHeight = '';
				BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
			}
		},

		isPopupShown: function ()
		{
			return this.popup && this.popup.popupWindow &&
				this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() &&
				this.popup.popupWindow.popupContainer &&
				BX.isNodeInDom(this.popup.popupWindow.popupContainer)
		},

		getCurrentModeState: function()
		{
			return '';
		},

		getPopupContent: function()
		{
			this.DOM.innerWrap = BX.create("div", {props : {className : ''}});
			return this.DOM.innerWrap;
		},

		handlePopupClick: function(e)
		{
			var target = e.target || e.srcElement;
			if (target.hasAttribute('data-bx-resbook-control-node') ||
				BX.findParent(target, {attribute: 'data-bx-resbook-control-node'}, this.DOM.innerWrap)
			)
			{
				this.handleControlChanges();
			}
		},

		handleControlChanges: function() {
			if (this.changesTimeout)
			{
				this.changesTimeout = clearTimeout(this.changesTimeout);
			}
			this.changesTimeout = setTimeout(BX.delegate(function(){BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');}, this), 50);
		},
		menuItemClick: function(e, menuItem) {},

		handleClick: function(e)
		{
			var target = e.target || e.srcElement;
			if (this.isPopupShown() && !BX.isParentForNode(this.popupContainer, target))
			{
				return this.closePopup({animation: true});
			}
		},

		setDisabled: function()
		{
			this.disabled = true;
			if (this.isPopupShown())
			{
				this.closePopup();
			}
			BX.addClass(this.DOM.innerWrap, 'disabled');
		},

		setEnabled: function()
		{
			this.disabled = false;
			BX.removeClass(this.DOM.innerWrap, 'disabled');
		}
	};
	// endregion


	// region 'UsersStatePopup'
	function UsersStatePopup(params)
	{
		UsersStatePopup.superclass.constructor.apply(this, arguments);
		this.name = 'usersStatePopup';
		this.inputName = 'user-select-mode';
		this.id = 'users-state-' + Math.round(Math.random() * 1000);
		this.defaultMode = params.defaultMode === 'none' ? 'none' : 'auto';
		this.isDisplayed = BX.type.isFunction(params.isDisplayed) ? params.isDisplayed : BX.DoNothing;
		this.build();
	}
	BX.extend(UsersStatePopup, ComplexTunePopup);

	UsersStatePopup.prototype.build = function()
	{
		UsersStatePopup.superclass.build.apply(this, arguments);
		this.handleControlChanges();
	};

	UsersStatePopup.prototype.getMenuItems = function()
	{
		return [
			new BX.Main.Popup.MenuItem({
				text: BX.message('WEBF_RES_SELECT_DEFAULT_TITLE'),
				delimiter: true
			}),
			{
				id: 'users-state-list',
				text: BX.message('WEBF_RES_SELECT_DEFAULT_EMPTY'),
				dataset: {
					type: 'radio',
					value: 'none',
					inputName: this.inputName,
					checked: this.defaultMode === 'none'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				id: 'users-state-auto',
				text: BX.message('WEBF_RES_SELECT_DEFAULT_FREE_USER'),
				dataset: {
					type: 'radio',
					value: 'auto',
					inputName: this.inputName,
					checked: this.defaultMode === 'auto'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			}
		];
	};

	UsersStatePopup.prototype.menuItemClick = function(e, menuItem)
	{
		var target = e.target || e.srcElement;
		if (BX.type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
			&& menuItem.dataset && menuItem.dataset.inputName === this.inputName
		)
		{
			this.defaultMode = menuItem.dataset.value;
		}
		this.handleControlChanges();
		setTimeout(BX.delegate(this.closePopup, this), 50);
	};

	UsersStatePopup.prototype.getCurrentModeState = function()
	{
		return this.isDisplayed()
			?
			(BX.message('WEBF_RES_SELECT_USER_FROM_LIST_SHORT') +
			(this.defaultMode === 'none'
				? ''
				: (',<br>' + BX.message('WEBF_RES_AUTO_SELECT_USER_SHORT'))
			))
			:
			BX.message('WEBF_RES_SELECT_USER_FROM_LIST_AUTO');
	};


	UsersStatePopup.prototype.handleControlChanges = function()
	{
		UsersStatePopup.superclass.handleControlChanges.apply(this, arguments);
		this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
		BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);
	};

	UsersStatePopup.prototype.getDefaultMode = function()
	{
		return this.defaultMode;
	};
	// endregion


	// region 'ResourcesStatePopup'
	function ResourcesStatePopup(params)
	{
		ResourcesStatePopup.superclass.constructor.apply(this, arguments);
		this.name = 'resourcesStatePopup';
		this.inputName = 'resource-select-mode';
		this.defaultMode = params.defaultMode === 'none' ? 'none' : 'auto';
		this.multiple = !!params.multiple;
		this.isDisplayed = BX.type.isFunction(params.isDisplayed) ? params.isDisplayed : BX.DoNothing;
		this.build();
	}
	BX.extend(ResourcesStatePopup, ComplexTunePopup);

	ResourcesStatePopup.prototype.build = function()
	{
		UsersStatePopup.superclass.build.apply(this, arguments);
		this.handleControlChanges();
	};

	ResourcesStatePopup.prototype.getMenuItems = function()
	{
		return [
			new BX.Main.Popup.MenuItem({
				text: BX.message('WEBF_RES_SELECT_DEFAULT_TITLE'),
				delimiter: true
			}),
			{
				id: 'resources-state-list',
				text: BX.message('WEBF_RES_SELECT_DEFAULT_EMPTY'),
				dataset: {
					type: 'radio',
					value: 'none',
					inputName: this.inputName,
					checked: this.defaultMode === 'none'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				id: 'resources-state-auto',
				text: BX.message('WEBF_RES_AUTO_SELECT_RES'),
				dataset: {
					type: 'radio',
					value: 'auto',
					inputName: this.inputName,
					checked: this.defaultMode === 'auto'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				delimiter: true
			},
			{
				id: 'resources-state-multiple',
				text: BX.message('WEBF_RES_MULTIPLE'),
				dataset: {
					type: 'checkbox',
					value: 'Y',
					checked: this.multiple
				},
				onclick: BX.proxy(this.menuItemClick, this)
			}
		];
	};

	ResourcesStatePopup.prototype.getCurrentModeState = function()
	{
		return this.isDisplayed()
			?
			(BX.message('WEBF_RES_SELECT_RES_FROM_LIST_SHORT') +
			(this.defaultMode === 'none'
					? ''
					: (',<br>' + BX.message('WEBF_RES_AUTO_SELECT_RES_SHORT'))
			))
			:
			BX.message('WEBF_RES_SELECT_RES_FROM_LIST_AUTO');
	};

	ResourcesStatePopup.prototype.handleControlChanges = function()
	{
		ResourcesStatePopup.superclass.handleControlChanges.apply(this, arguments);
		this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
		BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);

		//BX.adjust(this.DOM.currentStateLink, {text: this.getCurrentModeState()});
	};

	ResourcesStatePopup.prototype.menuItemClick = function(e, menuItem)
	{
		var target = e.target || e.srcElement;
		if (BX.type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
			&& menuItem.dataset)
		{
			if (menuItem.dataset.inputName === this.inputName)
			{
				this.defaultMode = menuItem.dataset.value;
			}
			else if (menuItem.id === 'resources-state-multiple')
			{
				this.multiple = !!target.checked;
			}
		}
		this.handleControlChanges();
	};

	ResourcesStatePopup.prototype.getDefaultMode = function()
	{
		return this.defaultMode;
	};
	ResourcesStatePopup.prototype.getMultiple = function()
	{
		return this.multiple;
	};
	// endregion


	// region 'DurationStatePopup'
	function DurationStatePopup(params)
	{
		DurationStatePopup.superclass.constructor.apply(this, arguments);
		this.name = 'durationStatePopup';
		this.inputName = 'duration-select-mode';
		this.manualInput = !!params.manualInput;
		this.defaultValue = params.defaultValue || 60;
		this.isDisplayed = BX.type.isFunction(params.isDisplayed) ? params.isDisplayed : BX.DoNothing;
		this.durationList = BX.Calendar.UserField.ResourceBooking.getDurationList(params.fullDay);
		this.build();
	}
	BX.extend(DurationStatePopup, ComplexTunePopup);

	DurationStatePopup.prototype.build = function()
	{
		UsersStatePopup.superclass.build.apply(this, arguments);
		this.handleControlChanges();
	};

	DurationStatePopup.prototype.getMenuItems = function()
	{
		return [
			{
				id: 'duration-default-value',
				text: BX.message('WEBF_RES_SELECT_DURATION_AUTO'),
				dataset: {
					type: 'submenu-list',
					value: this.defaultValue,
					textValue: this.getDurationLabelByValue(this.defaultValue)
				},
				items: this.getDefaultMenuItems()
			}].concat((this.isDisplayed()
			? [
				{
				delimiter: true
				},
				{
					id: 'duration-manual-input',
					text: BX.message('WEBF_RES_SELECT_MANUAL_INPUT'),
					dataset: {
						type: 'checkbox',
						value: 'Y',
						checked: this.manualInput
					},
					onclick: BX.proxy(this.menuItemClick, this)
				}
			]
			: []));
	};

	DurationStatePopup.prototype.getDefaultMenuItems = function()
	{
		var menuItems = [];

		if (BX.type.isArray(this.durationList))
		{
			this.durationList.forEach(function(item)
			{
				menuItems.push({
					id: 'duration-' + item.value,
					dataset: {
						type: 'duration',
						value: item.value
					},
					text: item.label,
					onclick: BX.proxy(this.menuItemClick, this)
				});
			}, this);
		}

		return menuItems;
	};

	DurationStatePopup.prototype.getDurationLabelByValue = function(duration)
	{
		var foundDuration = this.durationList.find(function(item){return parseInt(item.value) === parseInt(duration)});
		return foundDuration ? foundDuration.label : null;
	};

	DurationStatePopup.prototype.getCurrentModeState = function()
	{
		return this.isDisplayed()
			?
			(BX.message('WEBF_RES_SELECT_DURATION_FROM_LIST_SHORT')
			+ (',<br>' + BX.message('WEBF_RES_SELECT_DURATION_BY_DEFAULT') + ' ' + this.getDurationLabelByValue(this.defaultValue)))
			:
			BX.message('WEBF_RES_SELECT_DURATION_AUTO') + ' ' + this.getDurationLabelByValue(this.defaultValue);
	};

	DurationStatePopup.prototype.handleControlChanges = function()
	{
		DurationStatePopup.superclass.handleControlChanges.apply(this, arguments);
		this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
		BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);
	};

	DurationStatePopup.prototype.menuItemClick = function(e, menuItem)
	{
		var target = e.target || e.srcElement;
		if (BX.type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
			&& menuItem.dataset)
		{
			if (menuItem.id === 'duration-manual-input')
			{
				this.manualInput = !!target.checked;
			}
		}
		else if (menuItem.dataset && menuItem.dataset.type === 'duration')
		{
			this.defaultValue = parseInt(menuItem.dataset.value);
		}

		this.handleControlChanges();
	};

	DurationStatePopup.prototype.getManualInput = function()
	{
		return this.manualInput;
	};

	DurationStatePopup.prototype.getDefaultValue = function()
	{
		return this.defaultValue;
	};
	// endregion


	// region 'DateStatePopup'
	function DateStatePopup(params)
	{
		DateStatePopup.superclass.constructor.apply(this, arguments);
		this.name = 'dateStatePopup';

		this.styleInputName = 'date-select-style';
		this.startInputName = 'date-select-start';

		this.style = params.style === 'popup' ? 'popup' : 'line'; // popup|line
		this.start = params.start === 'today' ? 'today' : 'free'; // today|free
		this.build();
	}
	BX.extend(DateStatePopup, ComplexTunePopup);

	DateStatePopup.prototype.getMenuItems = function()
	{
		return [
			new BX.Main.Popup.MenuItem({
				text: BX.message('WEBF_RES_CALENDAR_STYLE'),
				delimiter: true
			}),
			{
				id: 'date-state-style-popup',
				text: BX.message('WEBF_RES_CALENDAR_STYLE_POPUP'),
				dataset: {
					type: 'radio',
					value: 'popup',
					inputName: this.styleInputName,
					checked: this.style === 'popup'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				id: 'date-state-style-line',
				text: BX.message('WEBF_RES_CALENDAR_STYLE_LINE'),
				dataset: {
					type: 'radio',
					value: 'line',
					inputName: this.styleInputName,
					checked: this.style === 'line'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			new BX.Main.Popup.MenuItem({
				text: BX.message('WEBF_RES_CALENDAR_START_FROM'),
				delimiter: true
			}),
			{
				id: 'date-state-start-from-today',
				text: BX.message('WEBF_RES_CALENDAR_START_FROM_TODAY'),
				dataset: {
					type: 'radio',
					value: 'today',
					inputName: this.startInputName,
					checked: this.start === 'today'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				id: 'date-state-start-from-free',
				text: BX.message('WEBF_RES_CALENDAR_START_FROM_FREE'),
				dataset: {
					type: 'radio',
					value: 'free',
					inputName: this.startInputName,
					checked: this.start === 'free'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			}
		];
	};


	DateStatePopup.prototype.getCurrentModeState = function()
	{
		return (this.style === 'popup'
				? BX.message('WEBF_RES_CALENDAR_STYLE_POPUP')
				: BX.message('WEBF_RES_CALENDAR_STYLE_LINE'))
			+ ', '
			+ (this.start === 'today'
				? BX.message('WEBF_RES_CALENDAR_START_FROM_TODAY_SHORT')
				: BX.message('WEBF_RES_CALENDAR_START_FROM_FREE_SHORT'));
	};

	DateStatePopup.prototype.handleControlChanges = function()
	{
		DateStatePopup.superclass.handleControlChanges.apply(this, arguments);
		BX.adjust(this.DOM.currentStateLink, {text: this.getCurrentModeState()});
	};

	DateStatePopup.prototype.menuItemClick = function(e, menuItem)
	{
		var target = e.target || e.srcElement;
		if (BX.type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
			&& menuItem.dataset)
		{
			if (menuItem.dataset.inputName === this.styleInputName)
			{
				this.style = menuItem.dataset.value;
			}
			else if (menuItem.dataset.inputName === this.startInputName)
			{
				this.start = menuItem.dataset.value;
			}
		}
		this.handleControlChanges();
	};

	DateStatePopup.prototype.getStyle = function()
	{
		return this.style;
	};
	DateStatePopup.prototype.getStart = function()
	{
		return this.start;
	};
	// endregion



	function TimeStatePopup(params)
	{
		TimeStatePopup.superclass.constructor.apply(this, arguments);
		this.name = 'timeStatePopup';
		this.styleInputName = 'date-select-style';

		this.showOnlyFree = params.showOnlyFree;
		this.showFinishTime = params.showFinishTime;
		this.scale = params.scale;
		this.stateShowFreeId = 'time-state-show-free';
		this.stateShowFinishId = 'time-state-show-finish';
		this.style = params.style === 'select' ? 'select' : 'slots'; // select|slots

		this.build();
	}
	BX.extend(TimeStatePopup, ComplexTunePopup);


	TimeStatePopup.prototype.build = function()
	{
		TimeStatePopup.superclass.build.apply(this, arguments);
		this.handleControlChanges();
	};

	TimeStatePopup.prototype.getMenuItems = function()
	{
		return [
			new BX.Main.Popup.MenuItem({
				text: BX.message('WEBF_RES_TIME_STYLE'),
				delimiter: true
			}),
			{
				id: 'time-state-style-select',
				text: BX.message('WEBF_RES_TIME_STYLE_SELECT'),
				dataset: {
					type: 'radio',
					value: 'select',
					inputName: this.styleInputName,
					checked: this.style === 'select'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				id: 'time-state-style-slots',
				text: BX.message('WEBF_RES_TIME_STYLE_SLOT'),
				dataset: {
					type: 'radio',
					value: 'slots',
					inputName: this.styleInputName,
					checked: this.style === 'slots'
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				delimiter: true
			},
			{
				id: 'time-state-scale',
				text: BX.message('WEBF_RES_TIME_BOOKING_SIZE'),
				dataset: {
					type: 'submenu-list',
					value: this.scale,
					textValue: this.getDurationLabelByValue(this.scale)
				},
				items: this.getDurationMenuItems()
			},
			{
				delimiter: true
			},
			{
				id: this.stateShowFreeId,
				text: BX.message('WEBF_RES_TIME_SHOW_FREE_ONLY'),
				dataset: {
					type: 'checkbox',
					value: 'Y',
					checked: this.showOnlyFree
				},
				onclick: BX.proxy(this.menuItemClick, this)
			},
			{
				id: this.stateShowFinishId,
				text: BX.message('WEBF_RES_TIME_SHOW_FINISH_TIME'),
				dataset: {
					type: 'checkbox',
					value: 'Y',
					checked: this.showFinishTime
				},
				onclick: BX.proxy(this.menuItemClick, this)
			}
		];
	};

	TimeStatePopup.prototype.getCurrentModeState = function()
	{
		return (this.style === 'select'
				? BX.message('WEBF_RES_TIME_STYLE_SELECT')
				: BX.message('WEBF_RES_TIME_STYLE_SLOT'))
			+ ',<br>'
			+ BX.message('WEBF_RES_TIME_BOOKING_SIZE') + ': '
			+ this.getDurationLabelByValue(this.scale);
	};

	TimeStatePopup.prototype.handleControlChanges = function()
	{
		TimeStatePopup.superclass.handleControlChanges.apply(this, arguments);
		this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
	};

	TimeStatePopup.prototype.menuItemClick = function(e, menuItem)
	{
		var target = e.target || e.srcElement;
		if (BX.type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
			&& menuItem.dataset)
		{
			if (menuItem.dataset.inputName === this.styleInputName)
			{
				this.style = menuItem.dataset.value;
			}
			else if (menuItem.id === this.stateShowFreeId)
			{
				this.showOnlyFree = !!target.checked;
			}
			else if (menuItem.id === this.stateShowFinishId)
			{
				this.showFinishTime = !!target.checked;
			}
		}
		else if (menuItem.dataset && menuItem.dataset.type === 'scale')
		{
			this.scale = parseInt(menuItem.dataset.value);
		}

		this.handleControlChanges();
	};


	TimeStatePopup.prototype.getDurationMenuItems = function()
	{
		var
			durationList = this.getDurationList(),
			menuItems = [];

		durationList.forEach(function(duration){
			menuItems.push({
				id: 'duration-' + duration.value,
				dataset: {
					type: 'scale',
					value: duration.value
				},
				text: duration.label,
				onclick: BX.proxy(this.menuItemClick, this)
			});
		}, this);

		return menuItems;
	};


	TimeStatePopup.prototype.getDurationList = function()
	{
		if (!this.durationList)
		{
			this.durationList = BX.Calendar.UserField.ResourceBooking.getDurationList(false);
			this.durationList = this.durationList.filter(function(duration)
			{
				return duration.value && duration.value >= 15 && duration.value <= 240;
			});
		}
		return this.durationList;
	};

	TimeStatePopup.prototype.getDurationLabelByValue = function(duration)
	{
		var foundDuration = this.getDurationList().find(function(item){return item.value === duration});
		return foundDuration ? foundDuration.label : null;
	};

	TimeStatePopup.prototype.getStyle = function()
	{
		return this.style;
	};
	TimeStatePopup.prototype.getScale = function()
	{
		return this.scale;
	};
	TimeStatePopup.prototype.getShowOnlyFree = function()
	{
		return this.showOnlyFree ? 'Y' : 'N';
	};
	TimeStatePopup.prototype.getShowFinishTime = function()
	{
		return this.showFinishTime ? 'Y' : 'N';
	};


	function ComplexValuePopup(params)
	{
		this.id = 'resourcebooking-settings-value-popup-' + Math.round(Math.random() * 100000);
		this.DOM = {
			outerWrap: params.wrap
		};
	}

	ComplexValuePopup.prototype = {
		build: function ()
		{
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(BX.create("div", {props: {className: 'calendar-resbook-webform-settings-popup-select-result'}}));

			this.DOM.valueLink = this.DOM.innerWrap.appendChild(BX.create("span", {
				props: {className: 'calendar-resbook-webform-settings-popup-select-value'},
				text: this.getCurrentValueState(),
				events: {
					click: BX.delegate(this.showPopup, this),
					mouseover: BX.delegate(this.showHoverPopup, this),
					mouseout: BX.delegate(this.hideHoverPopup, this)
				}
			}));
		},

		showPopup: function ()
		{
			if (this.popup && this.popup.isShown())
			{
				return this.popup.close();
			}

			this.popup = new BX.PopupWindow(
				this.id,
				this.DOM.valueLink,
				{
					autoHide: true,
					loseByEsc: true,
					offsetTop: 0,
					offsetLeft: 0,
					width: this.getPopupWidth(),
					lightShadow: true,
					content: this.getPopupContent()
			});
			this.popup.setAngle({offset: 60, position: 'top'});
			this.popup.show(true);


			BX.unbind(this.DOM.innerWrap, 'click', BX.proxy(this.handlePopupClick, this));
			BX.bind(this.DOM.innerWrap, 'click', BX.proxy(this.handlePopupClick, this));

			BX.addCustomEvent(this.popup, 'onPopupClose', BX.delegate(function ()
			{
				this.handlePopupCloose();
				this.popup.destroy(this.id);
				this.popup = null;
			}, this));
		},

		closePopup: function ()
		{
			if (this.isPopupShown())
			{
				this.popup.close();
			}
		},

		isPopupShown: function ()
		{
			return this.popup && this.popup.popupWindow &&
				this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() &&
				this.popup.popupWindow.popupContainer &&
				BX.isNodeInDom(this.popup.popupWindow.popupContainer)
		},

		showHoverPopup: function()
		{
		},

		hideHoverPopup: function ()
		{
		},

		handlePopupCloose: function ()
		{
		},

		getCurrentValueState: function ()
		{
			return BX.message('WEBF_RES_NO_VALUE');
		},

		getPopupContent: function ()
		{
			this.DOM.innerWrap = BX.create("div", {props: {className: ''}});

			this.DOM.innerWrap.style.minWidth = '500px';
			this.DOM.innerWrap.style.minHeight = '30px';
			return this.DOM.innerWrap;
		},

		getPopupWidth: function ()
		{
			return null;
		},

		handlePopupClick: function (e)
		{
			var target = e.target || e.srcElement;
			if (target.hasAttribute('data-bx-resbook-control-node') || BX.findParent(target, {attribute: 'data-bx-resbook-control-node'}, this.DOM.innerWrap))
			{
				this.handleControlChanges();
			}
		},

		handleControlChanges: function ()
		{
			setTimeout(BX.delegate(function(){BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');}, this), 50);
		},

		showPopupLoader: function ()
		{
			if (this.DOM.innerWrap)
			{
				this.hidePopupLoader();
				this.DOM.popupLoader = this.DOM.innerWrap.appendChild(BX.Calendar.UserField.ResourceBooking.getLoader(50));
			}
		},

		hidePopupLoader: function ()
		{
			BX.remove(this.DOM.popupLoader);
		}
	};

	function ComplexValueMultipleChecknoxPopup(params)
	{
		ComplexValueMultipleChecknoxPopup.superclass.constructor.apply(this, arguments);
		this.id = 'resourcebooking-settings-multiple-checknox-' + Math.round(Math.random() * 100000);
	}
	BX.extend(ComplexValueMultipleChecknoxPopup, ComplexValuePopup);

	ComplexValueMultipleChecknoxPopup.prototype.showPopup = function ()
	{
		if (this.isPopupShown())
		{
			return this.closePopup();
		}

		var menuItems = [];

		this.values.forEach(function(item)
		{
			menuItems.push({
				id: item.id,
				text: BX.util.htmlspecialchars(item.title),
				dataset: item.dataset,
				onclick: BX.proxy(this.menuItemClick, this)
			});
		}, this);

		if (menuItems.length > 1)
		{
			this.selectAllMessage = this.selectAllMessage || 'select all';
			menuItems.push({
				text: this.selectAllMessage,
				onclick: BX.proxy(this.selectAllItemClick, this)
			});
		}

		this.popup = BX.PopupMenu.create(
			this.id,
			this.DOM.valueLink,
			menuItems,
			{
				className: 'popup-window-resource-select',
				closeByEsc : true,
				autoHide : false,
				offsetTop: 0,
				offsetLeft: 0
			}
		);

		this.popup.popupWindow.setAngle({offset: 60, position: 'top'});
		this.popup.show(true);
		this.popupContainer = this.popup.popupWindow.popupContainer;

		BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function()
		{
			this.handlePopupCloose();
			BX.PopupMenu.destroy(this.id);
			this.popup = null;
		}, this));

		this.popup.menuItems.forEach(function(menuItem)
		{
			var checked;
			if (menuItem.dataset && menuItem.dataset.id)
			{
				checked = this.selectedValues.find(function(itemId){return itemId === menuItem.id});

				menuItem.layout.item.className = 'menu-popup-item';
				menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
					'<div class="menu-popup-item-resource">' +
					'<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
					'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
					'</div>' +
					'</div>';
			}
			else
			{
				this.selectAllChecked = !this.values.find(function(value){
					return !this.selectedValues.find(function(itemId){return itemId === value.id});
				},this);

				menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
				menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
					'<div class="menu-popup-item-resource">' +
					'<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
					'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' +
					'</div>' +
					'</div>';
			}
		}, this);

		setTimeout(BX.delegate(function(){
			BX.bind(document, 'click', BX.proxy(this.handleClick, this));
		}, this), 50);
	};

	ComplexValueMultipleChecknoxPopup.prototype.menuItemClick = function(e, menuItem)
	{
		var
			selectAllcheckbox,
			target = e.target || e.srcElement,
			checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox'),
			foundValue = this.values.find(function(value){return value.id === menuItem.id;});

		if (foundValue)
		{
			if (target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox") || BX.hasClass(target, "menu-popup-item-inner") ))
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
			}
			this.handleControlChanges();
		}
	};

	ComplexValueMultipleChecknoxPopup.prototype.selectItem = function(value)
	{
		if (!BX.util.in_array(value.id, this.selectedValues))
		{
			this.selectedValues.push(value.id);
		}
	};
	ComplexValueMultipleChecknoxPopup.prototype.deselectItem = function(value)
	{
		var index = BX.util.array_search(value.id, this.selectedValues);
		if (index >= 0)
		{
			this.selectedValues = BX.util.deleteFromArray(this.selectedValues, index);
		}
	};

	ComplexValueMultipleChecknoxPopup.prototype.selectAllItemClick = function(e, menuItem)
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
			this.selectedValues = [];
			if (this.selectAllChecked)
			{
				this.values.forEach(function(value){this.selectedValues.push(value.id);}, this);
			}
			this.handleControlChanges();
		}
	};

	ComplexValueMultipleChecknoxPopup.prototype.handleClick = function(e)
	{
		var target = e.target || e.srcElement;
		if (this.isPopupShown() && !BX.isParentForNode(this.popupContainer, target))
		{
			this.closePopup({animation: true});
		}

		this.handleControlChanges();
	};

	ComplexValueMultipleChecknoxPopup.prototype.closePopup = function()
	{
		if (this.isPopupShown())
		{
			this.popup.close();
			this.popupContainer.style.maxHeight = '';
			BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
		}
	};

	ComplexValueMultipleChecknoxPopup.prototype.getSelectedValues = function()
	{
		return this.selectedValues;
	};


	// region 'UsersValuePopup'
	function UsersValuePopup(params)
	{
		UsersValuePopup.superclass.constructor.apply(this, arguments);
		this.name = 'usersValuePopup';

		this.values = [];
		this.selectedValues = [];
		this.selectedCodes = [];
		var
			selectedItems, selectedIndex = {},
			selectAll = params.config.selected === null;

		selectedItems = BX.type.isArray(params.config.selected) ? params.config.selected : params.config.selected.split('|');
		if (BX.type.isArray(selectedItems))
		{
			for(var i = 0; i < selectedItems.length; i++)
			{
				selectedIndex[selectedItems[i]] = true;
				this.selectedValues.push(selectedItems[i]);
				this.selectedCodes.push('U' + selectedItems[i]);
			}
		}

		if (BX.type.isArray(params.config.users) && selectAll)
		{
			params.config.users.forEach(function(userId)
			{
				if (!selectedIndex[userId])
				{
					this.selectedValues.push(userId);
					this.selectedCodes.push('U' + userId);
				}
			}, this);
		}

		this.config = {};
		this.build();
	}

	BX.extend(UsersValuePopup, ComplexValuePopup);

	UsersValuePopup.prototype.getPopupContent = function()
	{
		UsersValuePopup.superclass.getPopupContent.apply(this);

		var promise = new BX.Promise();
		promise.then(BX.delegate(this.buildUserSelector, this));

		if (!this.config.socnetDestination)
		{
			this.showPopupLoader();
			BX.ajax.runAction('calendar.api.resourcebookingajax.getuserselectordata', {
				data: {
					selectedUserList: this.selectedValues
				}
			}).then(BX.delegate(function (response)
				{
					this.hidePopupLoader();
					this.config.socnetDestination = response.data;
					promise.fulfill();
				}, this),
				function (response) {
					/**
					 {
						 "status": "error",
						 "errors": [...]
					 }
					 **/
				});
		}
		else
		{
			promise.fulfill();
		}

		return this.DOM.innerWrap;
	};

	UsersValuePopup.prototype.showPopupLoader = function ()
	{
		if (this.DOM.innerWrap)
		{
			this.hidePopupLoader();
			this.DOM.popupLoader = this.DOM.innerWrap.appendChild(BX.create("div", {props: {className: 'calendar-resourcebook-popup-loader-wrap'}}));
			this.DOM.popupLoader.appendChild(BX.Calendar.UserField.ResourceBooking.getLoader(38));
		}
	};

	UsersValuePopup.prototype.getPopupWidth = function()
	{
		return 680;
	};

	UsersValuePopup.prototype.buildUserSelector = function()
	{
		this.DOM.userCurrentvalueWrap = this.DOM.innerWrap.appendChild(BX.create("div", {
			props: {
				className: 'calendar-resourcebook-content-block-control custom-field-item'
			}
		}));
		this.DOM.userSelectorWrap = this.DOM.innerWrap.appendChild(BX.create("div", {
			props: {
				className: 'calendar-resourcebook-pseudo-popup-wrap'
			}
		}));

		this.userSelector = new WebformUserSelector({
			wrapNode: this.DOM.userCurrentvalueWrap,
			socnetDestination: this.config.socnetDestination,
			itemsSelected: this.selectedCodes,
			addMessage: BX.message('USER_TYPE_RESOURCE_SELECT_USER'),
			externalWrap: this.DOM.userSelectorWrap
		});

		this.userSelectorId = this.userSelector.getId();

		BX.addCustomEvent('OnResourceBookDestinationAddNewItem', BX.proxy(this.triggerUserSelectorUpdate, this));
		BX.addCustomEvent('OnResourceBookDestinationUnselect', BX.proxy(this.triggerUserSelectorUpdate, this));
	};

	UsersValuePopup.prototype.getSelectedValues = function()
	{
		return this.selectedValues;
	};

	UsersValuePopup.prototype.triggerUserSelectorUpdate = function(item, selectroId, delayExecution)
	{
		if (selectroId === this.userSelectorId)
		{
			if (this.selectorUpdateTimeout)
			{
				this.selectorUpdateTimeout = clearTimeout(this.selectorUpdateTimeout);
			}

			if (delayExecution !== false)
			{
				this.selectorUpdateTimeout = setTimeout(BX.proxy(function(){
					this.triggerUserSelectorUpdate(item, selectroId, false);
				}, this), 300);
				return;
			}

			this.selectedValues = [];
			this.selectedCodes = this.userSelector.getAttendeesCodesList();

			this.selectedCodes.forEach(function(code)
			{
				if (code.substr(0, 1) === 'U')
				{
					this.selectedValues.push(parseInt(code.substr(1)));
				}
			}, this);

			this.handleControlChanges();
		}
	};

	UsersValuePopup.prototype.getCurrentValueState = function()
	{
		var count = this.selectedValues.length;
		return count ? (count + ' ' + BX.Calendar.UserField.getPluralMessage('WEBF_RES_USER', count)) : BX.message('WEBF_RES_NO_VALUE');
	};

	UsersValuePopup.prototype.handleControlChanges = function()
	{
		BX.onCustomEvent('ResourceBooking.settingsUserSelector:onChanged');
		UsersValuePopup.superclass.handleControlChanges.apply(this);
		BX.adjust(this.DOM.valueLink, {text: this.getCurrentValueState()});
	};
	// endregion


	function ResourcesValuePopup(params)
	{
		ResourcesValuePopup.superclass.constructor.apply(this, arguments);
		this.name = 'resourcesValuePopup';
		this.selectAllMessage = BX.message('USER_TYPE_RESOURCE_SELECT_ALL');

		var
			selectedItems, selectedIndex = {},
			selectAll = params.config.selected === null;

		if (BX.type.isArray(params.config.selected))
		{
			selectedItems = params.config.selected;
		}
		else if (BX.type.isString(params.config.selected))
		{
			selectedItems = params.config.selected.split('|');
		}

		if (BX.type.isArray(selectedItems))
		{
			for(var i = 0; i < selectedItems.length; i++)
			{
				selectedIndex[selectedItems[i]] = true;
			}
		}

		this.values = [];
		this.selectedValues = [];
		if (BX.type.isArray(params.config.resources))
		{
			params.config.resources.forEach(function(resource)
			{
				var valueId = this.prepareValueId(resource);
				this.values.push({
					id: valueId,
					title: resource.title,
					dataset: resource
				});

				if (selectAll || selectedIndex[resource.id])
				{
					this.selectedValues.push(valueId);
				}
			}, this);
		}


		this.build();
	}
	BX.extend(ResourcesValuePopup, ComplexValueMultipleChecknoxPopup);

	ResourcesValuePopup.prototype.handleControlChanges = function()
	{
		ResourcesValuePopup.superclass.handleControlChanges.apply(this);
		BX.adjust(this.DOM.valueLink, {text: this.getCurrentValueState()});
	};

	ResourcesValuePopup.prototype.getCurrentValueState = function()
	{
		var count = this.selectedValues.length;
		return count ? (count + ' ' + BX.Calendar.UserField.getPluralMessage('WEBF_RES_RESOURCE', count)) : BX.message('WEBF_RES_NO_VALUE');
	};

	ResourcesValuePopup.prototype.prepareValueId = function(resource)
	{
		return resource.type + '|' + resource.id;
	};

	ResourcesValuePopup.prototype.getSelectedId = function()
	{
		var result = [];
		this.getSelectedValues().forEach(function(value)
		{
			var val = value.split('|');
			if (val && val[1])
			{
				result.push(parseInt(val[1]));
			}
		});
		return result;
	};



	function ServiceValuePopup(params)
	{
		ServiceValuePopup.superclass.constructor.apply(this, arguments);
		this.name = 'ServiceValuePopup';
		this.selectAllMessage = BX.message('WEBF_RES_SELECT_ALL_SERVICES');

		var selectAll = params.config.selected === null || params.config.selected === '' || params.config.selected === undefined;
		this.values = [];
		this.selectedValues = [];

		var selectedItems, selectedIndex = {};
		if (BX.type.isArray(params.config.selected))
		{
			selectedItems = params.config.selected;
		}
		else if (BX.type.isString(params.config.selected))
		{
			selectedItems = params.config.selected.split('|');
		}

		if (BX.type.isArray(selectedItems))
		{
			for(var i = 0; i < selectedItems.length; i++)
			{
				selectedIndex[this.prepareServiceId(selectedItems[i])] = true;
			}
		}

		if (BX.type.isArray(params.config.services))
		{
			params.config.services.forEach(function(service)
			{
				service.id = this.prepareServiceId(service.name);
				this.values.push({
					id: service.id,
					title: service.name + ' - ' + BX.Calendar.UserField.ResourceBooking.getDurationLabel(service.duration),
					dataset: service
				});

				if (selectAll || selectedIndex[this.prepareServiceId(service.name)])
				{
					this.selectedValues.push(service.id);
				}
			}, this);
		}

		this.config = {};
		this.build();
	}
	BX.extend(ServiceValuePopup, ComplexValueMultipleChecknoxPopup);

	ServiceValuePopup.prototype.handleControlChanges = function()
	{
		ServiceValuePopup.superclass.handleControlChanges.apply(this);
		BX.adjust(this.DOM.valueLink, {text: this.getCurrentValueState()});
	};

	ServiceValuePopup.prototype.getSelectedValues = function()
	{
		return this.selectedValues.length ? this.selectedValues : '#EMPTY-SERVICE-LIST#';
	};

	ServiceValuePopup.prototype.getCurrentValueState = function()
	{
		var count = this.selectedValues.length;
		return count ? (count + ' ' + BX.Calendar.UserField.getPluralMessage('WEBF_RES_SERVICE', count)) : BX.message('WEBF_RES_NO_VALUE');
	};

	ServiceValuePopup.prototype.prepareServiceId = function(str)
	{
		return BX.translit(str).replace(/[^a-z0-9_]/ig, "_");
	};


	function WebformUserSelector(params)
	{
		WebformUserSelector.superclass.constructor.apply(this, arguments);
		this.DOM.externalWrap = params.externalWrap;
		this.closeDialogDelayFlag = false;
		this.finalShowClassName = 'calendar-resbook-socnet-dest-custom-wrap-appearing';
	}

	BX.extend(WebformUserSelector, BX.Calendar.UserField.ResourceBooking.UserSelector);

	WebformUserSelector.prototype.openDialogCallback = function()
	{
		WebformUserSelector.superclass.openDialogCallback.apply(this, arguments);
		BX.cleanNode(this.DOM.externalWrap);

		var useAnimation = !this.closeDialogDelayFlag;
		if ((this.popupContent && BX.hasClass(this.popupContent, this.finalShowClassName))
			||
			(this.popupSearchContent && BX.hasClass(this.popupSearchContent, this.finalShowClassName))
		)
		{
			useAnimation = false;
		}

		if (BX.SocNetLogDestination.popupWindow)
		{
			this.popupContent = this.DOM.externalWrap.appendChild(BX.SocNetLogDestination.popupWindow.contentContainer);
			BX.addClass(BX.SocNetLogDestination.popupWindow.popupContainer, 'calendar-resbook-socnet-dest-popup-hide');

			if (useAnimation)
			{
				BX.addClass(this.popupContent, 'calendar-resbook-socnet-dest-custom-wrap');
				BX.defer(function(){
					BX.addClass(this.popupContent, 'calendar-resbook-socnet-dest-custom-wrap-show');

					setTimeout(BX.delegate(function(){
						BX.addClass(this.popupContent, this.finalShowClassName);
					}, this), 200);
				}, this)();
			}
			else
			{
				BX.addClass(this.popupContent, this.finalShowClassName);
			}
		}

		if (BX.SocNetLogDestination.popupSearchWindow)
		{
			this.popupSearchContent = this.DOM.externalWrap.appendChild(BX.SocNetLogDestination.popupSearchWindow.contentContainer);
			BX.addClass(this.popupSearchContent, this.finalShowClassName);
			BX.addClass(BX.SocNetLogDestination.popupSearchWindow.popupContainer, 'calendar-resbook-socnet-dest-popup-hide');
		}
	};

	WebformUserSelector.prototype.closeDialogCallback = function()
	{
		WebformUserSelector.superclass.closeDialogCallback.apply(this, arguments);
		this.closeDialogDelayFlag = true;
		setTimeout(BX.delegate(function()
		{
			this.closeDialogDelayFlag = false;
		}, this), 10);
		if (this.popupContent)
		{
			BX.removeClass(this.popupContent, 'calendar-resbook-socnet-dest-custom-wrap');
			BX.removeClass(this.popupContent, 'calendar-resbook-socnet-dest-custom-wrap-show');
			BX.removeClass(this.popupContent, this.finalShowClassName);
		}
	};


})();