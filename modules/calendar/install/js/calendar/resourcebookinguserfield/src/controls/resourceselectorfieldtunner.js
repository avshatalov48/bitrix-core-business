import {FormFieldTunnerAbstract} from "../formfieldtunnerabstract";
import {FormFieldTunnerPopupAbstract} from "../formfieldtunnerpopupabstract";
import {FormFieldTunnerMultipleChecknoxPopupAbstract} from "../formfieldtunnervaluepopupabstract";
import {ResourcebookingUserfield} from "../resourcebookinguserfield";
import {Loc, Type, Dom} from "main.core";
import {MenuItem} from "main.popup";

export class ResourceSelectorFieldTunner extends FormFieldTunnerAbstract {
	constructor()
	{
		super();
		this.label = Loc.getMessage('WEBF_RES_RESOURCES');
		this.formLabel = Loc.getMessage('WEBF_RES_RESOURCES_LABEL');
		this.displayed = true;
	}

	updateConfig(params)
	{
		super.updateConfig(params);
		this.defaultMode = params.defaultMode;
		this.multiple = params.multiple === 'Y';
	}

	buildStatePopup(params)
	{
		params.isDisplayed = this.isDisplayed.bind(this);
		params.defaultMode = params.defaultMode || this.defaultMode;
		params.multiple = params.multiple == null ? this.multiple : params.multiple;
		this.statePopup = new ResourcesStatePopup(params);
	}

	buildValuePopup (params)
	{
		this.valuePopup = new ResourcesValuePopup(params);
	}

	displayInForm ()
	{
		super.displayInForm();
		this.statePopup.handleControlChanges();
		this.statePopup.setEnabled();
	}

	hideInForm ()
	{
		super.hideInForm();
		this.statePopup.handleControlChanges();
		this.statePopup.setDisabled();
	}

	getValue ()
	{
		return {
			show: this.isDisplayed() ? 'Y' : 'N',
			label: this.getFormLabel(),
			defaultMode: this.statePopup.getDefaultMode(),
			multiple: this.statePopup.getMultiple() ? 'Y' : 'N',
			value: this.valuePopup.getSelectedId()
		};
	}
}


class ResourcesStatePopup extends FormFieldTunnerPopupAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'resourcesStatePopup';
		this.inputName = 'resource-select-mode';
		this.defaultMode = params.defaultMode === 'none' ? 'none' : 'auto';
		this.multiple = !!params.multiple;
		this.isDisplayed = Type.isFunction(params.isDisplayed) ? params.isDisplayed : function(){return false;};
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
			new MenuItem({
				text: Loc.getMessage('WEBF_RES_SELECT_DEFAULT_TITLE'),
				delimiter: true
			}),
			{
				id: 'resources-state-list',
				text: Loc.getMessage('WEBF_RES_SELECT_DEFAULT_EMPTY'),
				dataset: {
					type: 'radio',
					value: 'none',
					inputName: this.inputName,
					checked: this.defaultMode === 'none'
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				id: 'resources-state-auto',
				text: Loc.getMessage('WEBF_RES_AUTO_SELECT_RES'),
				dataset: {
					type: 'radio',
					value: 'auto',
					inputName: this.inputName,
					checked: this.defaultMode === 'auto'
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				delimiter: true
			},
			{
				id: 'resources-state-multiple',
				text: Loc.getMessage('WEBF_RES_MULTIPLE'),
				dataset: {
					type: 'checkbox',
					value: 'Y',
					checked: this.multiple
				},
				onclick: this.menuItemClick.bind(this)
			}
		];
	}

	getCurrentModeState()
	{
		return this.isDisplayed()
			?
			(Loc.getMessage('WEBF_RES_SELECT_RES_FROM_LIST_SHORT') +
				(this.defaultMode === 'none'
						? ''
						: (',<br>' + Loc.getMessage('WEBF_RES_AUTO_SELECT_RES_SHORT'))
				))
			:
			Loc.getMessage('WEBF_RES_SELECT_RES_FROM_LIST_AUTO');
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
	}

	getDefaultMode()
	{
		return this.defaultMode;
	}

	getMultiple()
	{
		return this.multiple;
	}
}


class ResourcesValuePopup extends FormFieldTunnerMultipleChecknoxPopupAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'resourcesValuePopup';
		this.selectAllMessage = Loc.getMessage('USER_TYPE_RESOURCE_SELECT_ALL');

		let
			selectedItems, selectedIndex = {},
			selectAll = params.config.selected === null;

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
				selectedIndex[selectedItems[i]] = true;
			}
		}

		this.values = [];
		this.selectedValues = [];
		if (Type.isArray(params.config.resources))
		{
			params.config.resources.forEach(function(resource)
			{
				let valueId = this.prepareValueId(resource);
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

	handleControlChanges()
	{
		super.handleControlChanges();
		Dom.adjust(this.DOM.valueLink, {text: this.getCurrentValueState()});
	}

	getCurrentValueState()
	{
		let count = this.selectedValues.length;
		return count ? (count + ' ' + ResourcebookingUserfield.getPluralMessage('WEBF_RES_RESOURCE', count)) : Loc.getMessage('WEBF_RES_NO_VALUE');
	}

	prepareValueId(resource)
	{
		return resource.type + '|' + resource.id;
	}

	getSelectedId()
	{
		let result = [];
		this.getSelectedValues().forEach(function(value)
		{
			let val = value.split('|');
			if (val && val[1])
			{
				result.push(parseInt(val[1]));
			}
		});
		return result;
	}
}




