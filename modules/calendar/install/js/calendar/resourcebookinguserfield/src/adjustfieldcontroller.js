import {BookingUtil, FieldViewControllerEdit, FieldViewControllerPreview, Dom, Loc, Type, BaseEvent, EventEmitter, Runtime} from "calendar.resourcebooking";
import {ResourcebookingUserfield} from "./resourcebookinguserfield";
import {UserSelectorFieldTunner} from "./controls/userselectorfieldtunner";
import {ResourceSelectorFieldTunner} from "./controls/resourceselectorfieldtunner";
import {ServiceSelectorFieldTunner} from "./controls/serviceselectorfieldtunner";
import {DurationSelectorFieldTunner} from "./controls/durationselectorfieldtunner";
import {DateSelectorFieldTunner} from "./controls/dateselectorfieldtunner";
import {TimeSelectorFieldTunner} from "./controls/timeselectorfieldtunner";
import 'helper';

export class AdjustFieldController extends EventEmitter
{
	constructor(params)
	{
		super();
		this.setEventNamespace('BX.Calendar.ResourcebookingUserfield.AdjustFieldController');

		this.params = params;
		this.complexFields = {};
		this.userFieldParams = null;
		this.id = 'resbook-settings-popup-' + Math.round(Math.random() * 100000);

		this.settingsData = AdjustFieldController.getSettingsData(this.params.settings.data);
		this.params.settings.data = this.settingsData;

		this.DOM = {
			innerWrap: this.params.innerWrap,
			settingsWrap: this.params.innerWrap.appendChild(Dom.create("div", {attrs: {'data-bx-resource-field-settings': 'Y'}})),
			captionNode: this.params.captionNode,
			settingsInputs: {}
		};
	}

	init()
	{
		// Request field params
		this.showFieldLoader();

		ResourcebookingUserfield.getUserFieldParams({
			fieldName: this.params.entityName,
			selectedUsers: this.getSelectedUsers()
		}).then(
			(fieldParams) => {
				this.hideFieldLoader();
				this.userFieldParams = fieldParams;

				this.fieldLayout = new FieldViewControllerEdit({
					wrap: this.DOM.innerWrap,
					displayTitle: false,
					title: this.getCaption(),
					settings: this.getSettings()
				});
				this.fieldLayout.build();
				this.updateSettingsDataInputs();

				this.emit('afterInit', new BaseEvent({
					data: {
						fieldName: this.params.entityName,
						settings: this.getSettings()
					}
				}));
			}
		);
	}

	showSettingsPopup()
	{
		ResourcebookingUserfield.getUserFieldParams(
		{
			fieldName: this.params.entityName,
			selectedUsers: this.getSelectedUsers()
		}).then(
			function(fieldParams)
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
						titleBar: Loc.getMessage('WEBF_RES_SETTINGS'),
						closeIcon: true,
						buttons : [new BX.PopupWindowButton({})]
					});

				let buttonNodeWrap = this.settingsPopup.buttons[0].buttonNode.parentNode;
				Dom.remove(this.settingsPopup.buttons[0].buttonNode);
				this.settingsPopup.buttons[0].buttonNode = buttonNodeWrap.appendChild(Dom.create(
					"button",
					{
						props : { className : 'ui-btn ui-btn-success'},
						events: {click: function(){this.settingsPopup.close();}.bind(this)},
						text : Loc.getMessage('WEBF_RES_CLOSE_SETTINGS_POPUP')
					}
				));

				BX.removeClass(this.settingsPopup.buttons[0].buttonNode, 'popup-window-button');
				this.settingsPopup.show();

				BX.addCustomEvent(this.settingsPopup, 'onPopupClose', function(popup)
				{
					this.destroyControls();
					this.settingsPopup.destroy(this.id);
					this.settingsPopup = null;
					if (this.previewFieldLayout)
					{
						this.previewFieldLayout.destroy();
					}
				}.bind(this));
			}.bind(this)
		);
	}

	getSettingsContentNode()
	{
		let outerWrap = Dom.create("div", {props : { className : 'calendar-resbook-webform-settings-popup'}});

		let leftWrap = outerWrap.appendChild(Dom.create("div", {props : { className : 'calendar-resbook-webform-settings-popup-inner'}}));
		this.buildSettingsForm({wrap: leftWrap});

		let previewWrap = outerWrap.appendChild(Dom.create("div", {props : { className : 'calendar-resbook-webform-settings-popup-preview'}}));

		this.previewFieldLayout = new FieldViewControllerPreview({
			wrap: previewWrap,
			title: this.getCaption(),
			settings: this.getSettings()
		});
		this.previewFieldLayout.build();

		BX.addCustomEvent('ResourceBooking.webformSettings:onChanged', this.handleWebformSettingsChanges.bind(this));

		return outerWrap;
	}

	buildSettingsForm(params)
	{
		let
			settings = this.getSettings(),
			wrap = params.wrap,
			titleId = 'title-' + this.id;

		this.DOM.captionWrap = wrap.appendChild(Dom.create("div", {
			props : { className : 'calendar-resbook-webform-settings-popup-title'},
			html: '<label for="' + titleId + '" class="calendar-resbook-webform-settings-popup-label">' + Loc.getMessage('WEBF_RES_NAME_LABEL') + '</label>'
		}));
		this.DOM.captionInput = this.DOM.captionWrap.appendChild(Dom.create("input", {
			attrs: {
				id: titleId,
				className: 'calendar-resbook-webform-settings-popup-input',
				type: 'text',
				value: this.getCaption()
			},
			events: {
				change: this.updateCaption.bind(this),
				blur: this.updateCaption.bind(this),
				keyup: this.updateCaption.bind(this)
			}
		}));
		this.updateCaption();

		this.DOM.fieldsOuterWrap = wrap.appendChild(Dom.create('div', {
			props : { className : 'calendar-resbook-webform-settings-popup-content'},
			html: '<div class="calendar-resbook-webform-settings-popup-head">' +
				'<div class="calendar-resbook-webform-settings-popup-head-inner">' +
				'<span class="calendar-resbook-webform-settings-popup-head-text">' + Loc.getMessage('WEBF_RES_FIELD_NAME') + '</span>' +
				'<span class="calendar-resbook-webform-settings-popup-head-decs">' + Loc.getMessage('WEBF_RES_FIELD_NAME_IN_FORM') + '</span>' +
				'</div>' +
				'<div class="calendar-resbook-webform-settings-popup-head-inner">' +
				'<span class="calendar-resbook-webform-settings-popup-head-text">' + Loc.getMessage('WEBF_RES_FIELD_SHOW_IN_FORM') + '</span>' +
				'</div>' +
				'</div>'
		}));

		this.DOM.fieldsWrap = this.DOM.fieldsOuterWrap.appendChild(Dom.create('div', {
			props : { className : 'calendar-resbook-webform-settings-popup-list'}
		}));

		if (settings.userfieldSettings.useUsers)
		{
			this.buildComplexField('users', {
				wrap: this.DOM.fieldsWrap,
				changeSettingsCallback: this.updateSettings.bind(this),
				params: settings.data.users,
				config: {
					users: settings.userfieldSettings.users,
					selected: settings.data.users.value
				}
			});

			BX.addCustomEvent('ResourceBooking.settingsUserSelector:onChanged', this.checkBitrix24Limitation.bind(this));
		}

		if (settings.userfieldSettings.useResources)
		{
			this.buildComplexField('resources', {
				wrap: this.DOM.fieldsWrap,
				changeSettingsCallback: this.updateSettings.bind(this),
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
				changeSettingsCallback: this.updateSettings.bind(this),
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
				changeSettingsCallback: this.updateSettings.bind(this),
				params: settings.data.duration
			});
		}

		this.buildComplexField('date', {
			wrap: this.DOM.fieldsWrap,
			changeSettingsCallback: this.updateSettings.bind(this),
			params: settings.data.date
		});

		if (!settings.userfieldSettings.fullDay)
		{
			this.buildComplexField('time', {
				wrap: this.DOM.fieldsWrap,
				changeSettingsCallback: this.updateSettings.bind(this),
				params: settings.data.time
			});
		}

		this.DOM.fieldsWrap.appendChild(Dom.create('div', {
			props : { className : 'calendar-resbook-webform-settings-popup-item'},
			html: '<div class="calendar-resbook-webform-settings-popup-decs">' +
				(Loc.getMessage('WEBF_RES_BOOKING_SETTINGS_HELP')
					.replace('#START_LINK#', '<a href="javascript:void(0);"' +
						' onclick="if (top.BX.Helper){top.BX.Helper.show(\'redirect=detail&code=8366733\');}">')
					.replace('#END_LINK#', '</a>')) +
				'</div>'
		}));
	}

	destroyControls()
	{
		for (let k in this.complexFields)
		{
			if (this.complexFields.hasOwnProperty(k) && Type.isFunction(this.complexFields[k].destroy))
			{
				this.complexFields[k].destroy();
			}
		}
	}

	handleWebformSettingsChanges()
	{
		if (this.refreshLayoutTimeout)
		{
			this.refreshLayoutTimeout = clearTimeout(this.refreshLayoutTimeout);
		}

		this.refreshLayoutTimeout = setTimeout(function()
		{
			// Update settings and inputs
			for (let k in this.complexFields)
			{
				if (this.complexFields.hasOwnProperty(k) && Type.isFunction(this.complexFields[k].getValue))
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
		}.bind(this), 100);
	}

	buildComplexField(type, params)
	{
		switch(type)
		{
			case 'users':
				this.complexFields[type] = new UserSelectorFieldTunner();
				break;
			case 'resources':
				this.complexFields[type] = new ResourceSelectorFieldTunner();
				break;
			case 'services':
				this.complexFields[type] = new ServiceSelectorFieldTunner();
				break;
			case 'duration':
				this.complexFields[type] = new DurationSelectorFieldTunner();
				break;
			case 'date':
			 	this.complexFields[type] = new DateSelectorFieldTunner();
			 	break;
			case 'time':
				this.complexFields[type] = new TimeSelectorFieldTunner();
				break;
		}

		if (Type.isObject(this.complexFields[type]))
		{
			this.complexFields[type].build(params);
		}
	}

	static getSettingsData(data)
	{
		let
			field, option,
			settingsData = BX.clone(AdjustFieldController.getDefaultSettingsData(), true);

		if (Type.isPlainObject(data))
		{
			for (field in data)
			{
				if (data.hasOwnProperty(field) && settingsData[field])
				{
					if (Type.isPlainObject(data[field]))
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
	}

	static getDefaultSettingsData()
	{
		return {
			users : {
				show: 'Y',
				label: Loc.getMessage('WEBF_RES_USERS_LABEL'),
				defaultMode: 'auto', // none|auto
				value: null
			},
			resources: {
				show: 'Y',
				label: Loc.getMessage('WEBF_RES_RESOURCES_LABEL'),
				defaultMode: 'auto', // none|auto
				multiple: 'N',
				value: null
			},
			services: {
				show: 'Y',
				label: Loc.getMessage('WEBF_RES_SERVICE_LABEL'),
				value: null
			},
			duration: {
				show: 'Y',
				label: Loc.getMessage('WEBF_RES_DURATION_LABEL'),
				defaultValue: 60,
				manualInput: 'N'
			},
			date: {
				label: Loc.getMessage('WEBF_RES_DATE_LABEL'),
				style: 'line', // line|popup
				start: 'today'
			},
			time: {
				label: Loc.getMessage('WEBF_RES_TIME_LABEL'),
				style: 'slots',
				showOnlyFree: 'Y',
				showFinishTime: 'N',
				scale: 60
			}
		}
	}

	getSelectedUsers()
	{
		return this.settingsData && this.settingsData.users && Type.isString(this.settingsData.users.value) ? this.settingsData.users.value.split('|') : [];
	}

	updateSettingsDataInputs()
	{
		let field, option;
		for (field in this.settingsData)
		{
			if (this.settingsData.hasOwnProperty(field))
			{
				if (Type.isPlainObject(this.settingsData[field]))
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
	}

	updateSettingsInputValue(key, value)
	{
		let uniKey = key.join('-');
		if (!this.DOM.settingsInputs[uniKey])
		{
			this.DOM.settingsInputs[uniKey] = this.DOM.settingsWrap.appendChild(Dom.create('input', {
				attrs: {
					type: 'hidden',
					name: this.params.formName + '[SETTINGS_DATA][' + key.join('][') + ']'
				}
			}));
		}

		if (Type.isArray(value))
		{
			value = value.join('|');
		}

		this.DOM.settingsInputs[uniKey].value = value;
	}

	showFieldLoader()
	{
		if (this.DOM.innerWrap)
		{
			this.hideFieldLoader();
			this.DOM.fieldLoader = this.DOM.innerWrap.appendChild(BookingUtil.getLoader(100));
		}
	}

	hideFieldLoader()
	{
		Dom.remove(this.DOM.fieldLoader);
	}

	getSettings()
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
	}

	updateSettings(settings)
	{
	}

	getCaption()
	{
		return this.params.settings.caption;
	}

	updateCaption()
	{
		let caption = this.DOM.captionInput.value;
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
				this.DOM.settingsInputs.caption = this.DOM.settingsWrap.appendChild(Dom.create("input", {
					attrs: {
						type: "hidden",
						name: this.params.formName + '[CAPTION]'
					}
				}));
			}
			this.DOM.settingsInputs.caption.value = this.params.settings.caption;

			if (this.DOM.captionNode)
			{
				Dom.adjust(this.DOM.captionNode, {text: this.params.settings.caption});
			}
		}
	}

	isRequired()
	{
		return this.params.settings.required === 'Y';
	}

	updateRequiredValue()
	{
		this.params.settings.required = this.DOM.requiredCheckbox.checked ? 'Y' : 'N';
		if (!this.DOM.settingsInputs.required)
		{
			this.DOM.settingsInputs.required = this.DOM.settingsWrap.appendChild(Dom.create("input", {
				attrs: {
					type: "hidden",
					name: this.params.formName + '[REQUIRED]'
				}
			}));
		}
		this.DOM.settingsInputs.required.value = this.params.settings.required;
	}

	checkBitrix24Limitation()
	{
		let
			count = 0,
			settings = this.getSettings();

		if (Type.isArray(this.params.settings.userfieldSettings.resources))
		{
			count += this.params.settings.userfieldSettings.resources.length;
		}

		if (settings.userfieldSettings.useUsers && this.complexFields.users)
		{
			let usersValue = this.complexFields.users.getValue();
			if (usersValue && Type.isArray(usersValue.value))
			{
				count += usersValue.value.length;
			}
		}

		if (settings.userfieldSettings.resourceLimit > 0 && count > settings.userfieldSettings.resourceLimit)
		{
			BookingUtil.showLimitationPopup();
		}
	}
}