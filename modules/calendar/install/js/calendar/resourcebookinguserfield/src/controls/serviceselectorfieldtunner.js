import {FormFieldTunnerAbstract} from "../formfieldtunnerabstract";
import {FormFieldTunnerMultipleChecknoxPopupAbstract} from "../formfieldtunnervaluepopupabstract";
import {ResourcebookingUserfield} from "../resourcebookinguserfield";
import {BookingUtil} from "calendar.resourcebooking";
import {Loc, Type, Dom} from "main.core";

export class ServiceSelectorFieldTunner extends FormFieldTunnerAbstract {
	constructor()
	{
		super();
		this.label = Loc.getMessage('WEBF_RES_SERVICES');
		this.formLabel = Loc.getMessage('WEBF_RES_SERVICE_LABEL');
		this.displayed = true;
	}

	buildStatePopup(params)
	{
		if (params && Type.isDomNode(params.wrap))
		{
			params.wrap.appendChild(Dom.create("div", {
				props: {className:'calendar-resbook-webform-settings-popup-select disabled'},
				html: '<span class="calendar-resbook-webform-settings-popup-select-value">' + Loc.getMessage('WEBF_RES_FROM_LIST') + '</span>'
			}));
		}
	}

	buildValuePopup (params)
	{
		this.valuePopup = new ServiceValuePopup(params);
	}

	getValue ()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			value: this.valuePopup.getSelectedValues()
		};
	}
}


class ServiceValuePopup extends FormFieldTunnerMultipleChecknoxPopupAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'ServiceValuePopup';
		this.selectAllMessage = Loc.getMessage('WEBF_RES_SELECT_ALL_SERVICES');

		let selectAll = params.config.selected === null || params.config.selected === '' || params.config.selected === undefined;
		this.values = [];
		this.selectedValues = [];

		let selectedItems, selectedIndex = {};
		if (Type.isArray(params.config.selected))
		{
			selectedItems = params.config.selected;
		}
		else if (Type.isString(params.config.selected))
		{
			selectedItems = params.config.selected.split('|');
		}

		if (Type.isArray(selectedItems))
		{
			for(let i = 0; i < selectedItems.length; i++)
			{
				selectedIndex[BookingUtil.translit(selectedItems[i])] = true;
			}
		}

		if (Type.isArray(params.config.services))
		{
			params.config.services.forEach(function(service)
			{
				service.id = BookingUtil.translit(service.name);
				if (service.id !== '')
				{
					this.values.push({
						id: service.id,
						title: service.name + ' - ' + BookingUtil.getDurationLabel(service.duration),
						dataset: service
					});

					if (selectAll || selectedIndex[BookingUtil.translit(service.name)])
					{
						this.selectedValues.push(service.id);
					}
				}
			}, this);
		}

		this.config = {};
		this.build();
	}

	handleControlChanges()
	{
		super.handleControlChanges();
		Dom.adjust(this.DOM.valueLink, {text: this.getCurrentValueState()});
	}

	getSelectedValues()
	{
		return this.selectedValues.length ? this.selectedValues : '#EMPTY-SERVICE-LIST#';
	}

	getCurrentValueState()
	{
		let count = this.selectedValues.length;
		return count ? (count + ' ' + ResourcebookingUserfield.getPluralMessage('WEBF_RES_SERVICE', count)) : Loc.getMessage('WEBF_RES_NO_VALUE');
	}
}


