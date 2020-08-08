import {FormFieldTunnerAbstract} from "../formfieldtunnerabstract";
import {FormFieldTunnerPopupAbstract} from "../formfieldtunnerpopupabstract";
import {BookingUtil} from "calendar.resourcebooking";
import {Loc, Type} from "main.core";

export class DurationSelectorFieldTunner extends FormFieldTunnerAbstract {
	constructor()
	{
		super();
		this.label = Loc.getMessage('WEBF_RES_DURATION');
		this.formLabel = Loc.getMessage('WEBF_RES_DURATION_LABEL');
	}

	updateConfig(params)
	{
		super.updateConfig();
		this.defaultValue = params.defaultValue;
		this.manualInput = params.manualInput === 'Y';
	}

	buildStatePopup(params)
	{
		params.isDisplayed = this.isDisplayed.bind(this);
		params.defaultValue = this.defaultValue;
		params.manualInput = this.manualInput;
		this.statePopup = new DurationStatePopup(params);
	}

	displayInForm ()
	{
		super.displayInForm();
		this.statePopup.handleControlChanges();
	}

	hideInForm ()
	{
		super.hideInForm();
		this.statePopup.handleControlChanges();
	}

	getValue ()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			defaultValue: this.statePopup.getDefaultValue(),
			manualInput: this.statePopup.getManualInput() ? 'Y' : 'N'
		};
	}
}


class DurationStatePopup extends FormFieldTunnerPopupAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'durationStatePopup';
		this.inputName = 'duration-select-mode';
		this.manualInput = !!params.manualInput;
		this.defaultValue = params.defaultValue || 60;
		this.isDisplayed = Type.isFunction(params.isDisplayed) ? params.isDisplayed : function(){return false;};
		this.durationList = BookingUtil.getDurationList(params.fullDay);
		this.build();
	}

	build()
	{
		super.build();
		this.handleControlChanges();
	}

	getMenuItems()
	{
		return [
			{
				id: 'duration-default-value',
				text: Loc.getMessage('WEBF_RES_SELECT_DURATION_AUTO'),
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
					text: Loc.getMessage('WEBF_RES_SELECT_MANUAL_INPUT'),
					dataset: {
						type: 'checkbox',
						value: 'Y',
						checked: this.manualInput
					},
					onclick: this.menuItemClick.bind(this)
				}
			]
			: []));
	}

	getDefaultMenuItems()
	{
		let menuItems = [];

		if (Type.isArray(this.durationList))
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
					onclick: this.menuItemClick.bind(this)
				});
			}, this);
		}

		return menuItems;
	}

	getDurationLabelByValue(duration)
	{
		let foundDuration = this.durationList.find(function(item){return parseInt(item.value) === parseInt(duration)});
		return foundDuration ? foundDuration.label : null;
	}

	getCurrentModeState()
	{
		return this.isDisplayed()
			?
			(Loc.getMessage('WEBF_RES_SELECT_DURATION_FROM_LIST_SHORT')
				+ (',<br>' + Loc.getMessage('WEBF_RES_SELECT_DURATION_BY_DEFAULT') + ' ' + this.getDurationLabelByValue(this.defaultValue)))
			:
			Loc.getMessage('WEBF_RES_SELECT_DURATION_AUTO') + ' ' + this.getDurationLabelByValue(this.defaultValue);
	}

	handleControlChanges()
	{
		super.handleControlChanges();
		this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
		BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);
	}

	menuItemClick(e, menuItem)
	{
		let target = e.target || e.srcElement;
		if (Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
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
	}

	getManualInput()
	{
		return this.manualInput;
	}

	getDefaultValue()
	{
		return this.defaultValue;
	}
}