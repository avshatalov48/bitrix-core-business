import {BookingUtil} from "calendar.resourcebooking";
import {ServiceSelector} from "./controls/serviceselector"
import {UserSelectorFieldEditControl} from "./controls/userselectorfieldeditcontrol";
import {ResourceSelectorFieldEditControl} from "./controls/resourceselectorfieldeditcontrol";
import {TimezoneSelector} from "./controls/timezoneselector";
import {ModeSelector} from "./controls/modeselector";
import {ResourcebookingUserfield} from "calendar.resourcebookinguserfield";

export let customizeCrmEntityEditor = function(CrmConfigurator)
{
	let Configurator = function()
	{
		Configurator.superclass.constructor.apply(this);
	};
	BX.extend(Configurator, CrmConfigurator);

	Configurator.create = function(id, settings)
	{
		let self = new Configurator();
		self.initialize(id, settings);
		return self;
	};

	Configurator.prototype.layout = function(options, params)
	{
		if(this._hasLayout)
		{
			return;
		}
		if(!BX.type.isPlainObject(params))
		{
			params = {}
		}

		if(this._mode === BX.Crm.EntityEditorMode.view)
		{
			throw "EntityEditorUserFieldConfigurator. View mode is not supported by this control type.";
		}

		this.getBitrix24Limitation({
			callback: BX.delegate(function(limit)
			{
				this.RESOURCE_LIMIT = limit;
			}, this)
		});

		if(this._field)
		{
			this.fieldInfo = this._field.getFieldInfo();
		}
		else if (!params.settings)
		{
			return this.getDefaultUserfieldSettings({
				displayCallback: BX.delegate(function(settings)
				{
					this.layout(options, {settings: settings});
				}, this)
			});
		}

		this._wrapper = BX.create("div", {props: {className: "calendar-resourcebook-content"}});
		this._innerWrapper = this._wrapper
			.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-wrap"}}))
			.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-inner"}}));

		var
			fieldSettings = this.fieldInfo ? this.fieldInfo.SETTINGS : params.settings,
			resourceList = [],
			selectedResourceList = [],
			isNew = this._field === null,
			title = this.getMessage("labelField"),
			manager = this._editor.getUserFieldManager(),
			label = this._field ? this._field.getTitle() : manager.getDefaultFieldLabel(this._typeId);

		this.RESOURCE_LIMIT = fieldSettings.RESOURCE_LIMIT || 0;

		// region Field Title
		this._labelInput = BX.create("input",
			{
				attrs:
					{
						className: "crm-entity-widget-content-input",
						type: "text",
						value: label
					}
			}
		);

		this._innerWrapper.appendChild(
			BX.create(
				"div",
				{
					props: { className: "calendar-resourcebook-content-block" },
					children:
						[
							// Title
							BX.create(
								"div",
								{
									props: { className: "crm-entity-widget-content-block-title" },
									children: [
										BX.create(
											"span",
											{
												attrs: { className: "crm-entity-widget-content-block-title-text" },
												text: title
											}
										)
									]
								}
							),
							// Input
							BX.create(
								"div",
								{
									props: { className: "calendar-resourcebook-content-block-field" },
									children: [ this._labelInput ]
								}
							),
							// Hr
							BX.create("hr", { props: { className: "crm-entity-widget-hr" } })
						]
				}
			)
		);
		// endregion

		// region Users&Resources Mode selector
		this._innerWrapper.appendChild(
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
								useResources: fieldSettings.USE_RESOURCES === 'Y',
								useUsers: fieldSettings.USE_USERS === 'Y',
								showUsers: function()
								{
									this.resourceList.hide();
									this.userList.show();
								}.bind(this),
								showResources: function()
								{
									this.resourceList.show();
									this.userList.hide();
								}.bind(this),
								showResourcesAndUsers: function()
								{
									this.resourceList.show();
									this.userList.show();
								}.bind(this)
							}).getOuterWrap()
						]
				}
			)
		);
		// endregion

		var optionWrapper = this._innerWrapper.appendChild(BX.create(
			"div",
			{
				props: { className: "calendar-resourcebook-content-block" }
			}
		));

		// region Use Resources Option
		this.resourcesWrap = optionWrapper.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"}}));

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

		this.resourceList = new ResourceSelectorFieldEditControl({
			shown: fieldSettings.USE_RESOURCES === 'Y',
			editMode: true,
			outerWrap: this.resourcesWrap,
			listWrap: this.resourcesListWrap,
			controlsWrap: this.resourcesListLowControls,
			values: selectedResourceList,
			resourceList: resourceList,
			checkLimitCallback: this.checkResourceCountLimit.bind(this),
			checkLimitCallbackForNew: this.checkResourceCountLimitForNewEntries.bind(this)
		});
		// endregion

		// region Users Selector
		this.userSelectorWrap = optionWrapper.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"}}));

		this.usersTitleWrap = this.userSelectorWrap
			.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
			.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: BX.message('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME') + ':'}));

		this.usersListWrap = this.userSelectorWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-control"}}));

		var itemsSelected = [];
		if (BX.type.isArray(fieldSettings.SELECTED_USERS))
		{
			fieldSettings.SELECTED_USERS.forEach(function(user)
			{
				itemsSelected.push('U' + parseInt(user));
			});
		}

		this.userList = new UserSelectorFieldEditControl({
			shown: fieldSettings.USE_USERS === 'Y',
			outerWrap: this.userSelectorWrap,
			wrapNode: this.usersListWrap,
			socnetDestination: ResourcebookingUserfield.getSocnetDestination(),
			itemsSelected: itemsSelected,
			checkLimitCallback: this.checkResourceCountLimit.bind(this)
		});
		// endregion

		// Region Data, Time and services
		optionWrapper.appendChild(
			BX.create("hr", { props: { className: "crm-entity-widget-hr" } })
		);

		this.datetimeOptionsWrap = optionWrapper.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add" }}));

		this.datetimeOptionsWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title"}})).appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: BX.message('USER_TYPE_RESOURCE_DATETIME_BLOCK_TITLE') + ':'}));

		this.datetimeOptionsInnerWrap = this.datetimeOptionsWrap.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-options"}}));

		this.timezoneSettingsWrap = optionWrapper.appendChild(
			BX.create("div", {props: {
					className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-options" },
				style: {display: fieldSettings.FULL_DAY === 'Y' ? 'none' : ''}
			}));

		this.timezoneSettingsWrap.appendChild(BX.create("hr", {props: {className: "crm-entity-widget-hr"}}));
		this.timezoneSettingsWrap
			.appendChild(BX.create("div", {props: {className: "calendar-resourcebook-content-block-title"}}))
			.appendChild(BX.create("span", {props: {className: "calendar-resourcebook-content-block-title-text"}, text: BX.message('USER_TYPE_RESOURCE_TIMEZONE_SETTINGS_TITLE') + ':'}));

		this.timezoneSelectorWrap = this.timezoneSettingsWrap.appendChild(BX.create("div", {
			style: {display: fieldSettings.USE_USER_TIMEZONE === 'Y' ? 'none' : ''}
		}));

		this.timezoneSelectWrap = this.timezoneSelectorWrap
			.appendChild(BX.create(
				"div",
				{
					props: { className: "calendar-resourcebook-content-block-field" }
				}
			));


		this.timezoneSelector = new TimezoneSelector({
			outerWrap: this.timezoneSelectWrap,
			selectedValue: fieldSettings.TIMEZONE
		});

		this.useUserTimezoneCheckBox = BX.create(
			"input",
			{
				props: {
					type: "checkbox",
					checked: fieldSettings.USE_USER_TIMEZONE === 'Y'
				}
			}
		);
		this.timezoneSettingsWrap.appendChild(
			BX.create(
				"label",
				{
					props: {className: 'calendar-resourcebook-content-block-option'},
					children:
						[
							this.useUserTimezoneCheckBox,
							BX.create("span", { text: BX.message('USER_TYPE_RESOURCE_USE_USER_TIMEZONE') })
						],
					events: {
						click: BX.proxy(this.handleUserTimezoneCheckbox, this)
					}

				}
			)
		);

		// endregion

		//region Checkbox "Full day"
		this._fulldayCheckBox = BX.create(
			"input",
			{
				props: {
					type: "checkbox",
					checked: fieldSettings.FULL_DAY === 'Y'
				},
				events: {
					click: BX.proxy(this.handleFullDayMode, this)
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
							this._fulldayCheckBox,
							BX.create("span", { text: BX.message('USER_TYPE_RESOURCE_FULL_DAY') })
						]
				}
			)
		);
		//endregion

		//region Checkbox "Add services"
		this._servicesCheckBox = BX.create(
			"input",
			{
				props: {
					type: "checkbox",
					checked: fieldSettings.USE_SERVICES === 'Y'
				},
				events: {
					click : BX.delegate(function(){
						if (this.serviceList)
						{
							this.serviceList.show(this._servicesCheckBox.checked);
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
							this._servicesCheckBox,
							BX.create("span", { text: BX.message('USER_TYPE_RESOURCE_ADD_SERVICES') })
						]
				}
			)
		);

		this.serviceList = new ServiceSelector({
			outerCont: this.datetimeOptionsInnerWrap,
			onFullClearHandler: function()
			{
				this._servicesCheckBox.checked = false;
			}.bind(this),
			fieldSettings: fieldSettings,
			getFullDayValue: function(){return this._fulldayCheckBox.checked}.bind(this)
		});

		optionWrapper.appendChild(
			BX.create("hr", { props: { className: "crm-entity-widget-hr" } })
		);

		//region Checkbox "Is Required"
		this.additionaOptionsWrap = optionWrapper.appendChild(BX.create("div", {props: { className: "calendar-resourcebook-content-block-options"}}));

		this._isRequiredCheckBox = BX.create(
			"input",
			{ props: { type: "checkbox", checked: this._field && this._field.isRequired() } }
		);

		this.additionaOptionsWrap.appendChild(
			BX.create(
				"label",
				{
					props: {className: 'calendar-resourcebook-content-block-option'},
					children:
						[
							this._isRequiredCheckBox,
							BX.create("span", { text: this.getMessage("isRequiredField") })
						]
				}
			)
		);
		//endregion

		//region Checkbox "Show Always"
		this._showAlwaysCheckBox = BX.create("input", { props: { type: "checkbox" } });
		if(isNew)
		{
			this._showAlwaysCheckBox.checked = BX.prop.getBoolean(this._settings, "showAlways", true);
		}
		else
		{
			this._showAlwaysCheckBox.checked = this._field.checkOptionFlag(
				BX.Crm.EntityEditorControlOptions.showAlways
			);
		}
		this.additionaOptionsWrap.appendChild(
			BX.create(
				"label",
				{
					props: {className: 'calendar-resourcebook-content-block-option'},
					children:
						[
							this._showAlwaysCheckBox,
							BX.create("span", { text: this.getMessage("showAlways") })
						]
				}
			)
		);
		//endregion

		//region Checkbox "Overbooking"
		this._overbookingCheckBox = BX.create(
			"input",
			{ props: { type: "checkbox", checked: fieldSettings.ALLOW_OVERBOOKING === 'Y'} }
		);

		this.additionaOptionsWrap.appendChild(
			BX.create(
				"label",
				{
					props: {className: 'calendar-resourcebook-content-block-option'},
					children:
						[
							this._overbookingCheckBox,
							BX.create("span", { text: BX.message('USER_TYPE_RESOURCE_OVERBOOKING') })
						]
				}
			)
		);
		//endregion

		this._innerWrapper.appendChild(
			BX.create(
				"div",
				{
					props: {
						className: "calendar-resourcebook-content-block-btn-container"
					},
					children: [
						BX.create("hr", { props: { className: "crm-entity-widget-hr" } }),
						BX.create(
							"button",
							{
								props: {type: "button", className: "ui-btn ui-btn-sm ui-btn-primary"},
								text: BX.message("CRM_EDITOR_SAVE"),
								events: {  click: BX.delegate(this.onSaveButtonClick, this) }
							}
						),
						BX.create(
							"button",
							{
								props: {type: "button", className: "ui-btn ui-btn-sm ui-btn-light-border" },
								text: BX.message("CRM_EDITOR_CANCEL"),
								events: {  click: BX.delegate(this.onCancelButtonClick, this) }
							}
						)
					]
				}
			)
		);

		this.fieldSettings = fieldSettings;
		this.registerLayout(options);
		this._hasLayout = true;
	};

	Configurator.prototype.getDefaultUserfieldSettings = function(params)
	{
		BX.ajax.runAction('calendar.api.resourcebookingajax.getdefaultuserfieldsettings', {
			data: {}
		}).then(function (response)
			{
				if (params && BX.type.isFunction(params.displayCallback))
				{
					params.displayCallback(response.data);
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
	};

	Configurator.prototype.getBitrix24Limitation = function(params)
	{
		BX.ajax.runAction('calendar.api.resourcebookingajax.getbitrix24limitation', {
			data: {}
		}).then(function (response)
			{
				if (params && BX.type.isFunction(params.callback))
				{
					params.callback(response.data);
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
	};

	Configurator.prototype.onSaveButtonClick = function()
	{
		if(this._isLocked)
		{
			return;
		}

		if (this.RESOURCE_LIMIT > 0 && this.getTotalResourceCount() > this.RESOURCE_LIMIT)
		{
			BookingUtil.showLimitationPopup();
			return;
		}

		var params =
			{
				typeId: this._typeId,
				label: this._labelInput.value,
				mandatory: this._isRequiredCheckBox.checked,
				showAlways: this._showAlwaysCheckBox.checked,
				multiple: true
			};

		if(this._field)
		{
			params["field"] = this._field;
		}

		this.fieldSettings.USE_RESOURCES = this.resourceList.isShown() ? 'Y' : 'N';
		this.fieldSettings.USE_USERS = this.userList.isShown() ? 'Y' : 'N';

		if (this.fieldSettings
			&& BX.type.isPlainObject(this.fieldSettings.RESOURCES)
			&& BX.type.isPlainObject(this.fieldSettings.RESOURCES['resource']))
		{
			this.fieldSettings.SELECTED_RESOURCES = [];

			this.resourceList.getSelectedValues().forEach(function(value)
			{
				this.fieldSettings.SELECTED_RESOURCES.push(value);
			}, this);

			this.resourceList.getDeletedValues().forEach(function(value)
			{
				this.fieldSettings.SELECTED_RESOURCES.push(value);
			}, this);
		}


		if (this.fieldSettings && this.userList)
		{
			this.fieldSettings.SELECTED_USERS = [0];
			this.userList.getAttendeesCodesList().forEach(function(code)
			{
				if (code.substr(0, 1) === 'U')
				{
					this.fieldSettings.SELECTED_USERS.push(parseInt(code.substr(1)));
				}
			}, this);
		}

		this.fieldSettings.USE_SERVICES = this._servicesCheckBox.checked ? 'Y' : 'N';
		this.fieldSettings.SERVICE_LIST = [];
		if (this._servicesCheckBox.checked && this.serviceList)
		{
			this.fieldSettings.SERVICE_LIST = this.serviceList.getValues();
		}

		this.fieldSettings.FULL_DAY = this._fulldayCheckBox.checked ? 'Y' : 'N';
		this.fieldSettings.ALLOW_OVERBOOKING = this._overbookingCheckBox.checked ? 'Y' : 'N';

		if (this.fieldSettings.FULL_DAY === 'N')
		{
			this.fieldSettings.TIMEZONE = this.timezoneSelector.getValue();
			this.fieldSettings.USE_USER_TIMEZONE = this.useUserTimezoneCheckBox.checked ? 'Y' : 'N';
		}
		else
		{
			this.fieldSettings.TIMEZONE = '';
			this.fieldSettings.USE_USER_TIMEZONE = 'N';
		}

		params["settings"] = this.fieldSettings;

		BX.onCustomEvent(this, "onSave", [ this, params]);
	};

	Configurator.prototype.getTotalResourceCount = function()
	{
		var result = 0;

		if (this.fieldSettings)
		{
			if (BX.type.isPlainObject(this.fieldSettings.RESOURCES)
				&& BX.type.isPlainObject(this.fieldSettings.RESOURCES.resource)
				&& BX.type.isArray(this.fieldSettings.RESOURCES.resource.SECTIONS)
			)
			{
				result += this.fieldSettings.RESOURCES.resource.SECTIONS.length;
			}

			result -= this.resourceList.getDeletedValues().length;

			this.resourceList.getSelectedValues().forEach(function(value)
			{
				if (!value.id && value.title !== '')
				{
					result++;
				}
			}, this);

			if (this.userList)
			{
				result += this.userList.getAttendeesCodesList().length;
			}
		}
		return result;
	};

	Configurator.prototype.checkResourceCountLimitForNewEntries = function()
	{
		return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() < this.RESOURCE_LIMIT;
	};

	Configurator.prototype.checkResourceCountLimit = function()
	{
		return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() <= this.RESOURCE_LIMIT;
	};

	Configurator.prototype.handleFullDayMode = function()
	{
		this.timezoneSettingsWrap.style.display = this._fulldayCheckBox.checked ? 'none' : '';
	};

	Configurator.prototype.handleUserTimezoneCheckbox = function()
	{
		this.timezoneSelectorWrap.style.display = this.useUserTimezoneCheckBox.checked ? 'none' : '';
	};


	return Configurator;
};