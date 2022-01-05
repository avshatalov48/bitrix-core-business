import {FormFieldTunnerAbstract} from "../formfieldtunnerabstract";
import {FormFieldTunnerPopupAbstract} from "../formfieldtunnerpopupabstract";
import {BookingUtil} from "calendar.resourcebooking";
import {Loc, Type} from "main.core";
import {MenuItem} from "main.popup";

export class TimeSelectorFieldTunner extends FormFieldTunnerAbstract {
	constructor()
	{
		super();
		this.label = Loc.getMessage('WEBF_RES_TIME');
		this.formLabel = Loc.getMessage('WEBF_RES_TIME_LABEL');
		this.displayed = true;
		this.displayCheckboxDisabled = true;
	}

	updateConfig(params)
	{
		super.updateConfig();
		this.style = params.style;
		this.showOnlyFree = params.showOnlyFree === 'Y';
		this.showFinishTime = params.showFinishTime === 'Y';
		this.scale = parseInt(params.scale);
	}

	buildStatePopup(params)
	{
		params.style = params.style || this.style;
		params.showOnlyFree = this.showOnlyFree;
		params.showFinishTime = this.showFinishTime;
		params.scale = this.scale;
		this.statePopup = new TimeStatePopup(params);
	}

	getValue()
	{
		return {
			label: this.getFormLabel(),
			style: this.statePopup.getStyle(),
			showFinishTime: this.statePopup.getShowFinishTime(),
			showOnlyFree: this.statePopup.getShowOnlyFree(),
			scale: this.statePopup.getScale()
		};
	}
}


class TimeStatePopup extends FormFieldTunnerPopupAbstract
{
	constructor(params)
	{
		super(params);
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

	build()
	{
		super.build();
		this.handleControlChanges();
	}

	getMenuItems()
	{
		return [
			new MenuItem({
				text: Loc.getMessage('WEBF_RES_TIME_STYLE'),
				delimiter: true
			}),
			{
				id: 'time-state-style-select',
				text: Loc.getMessage('WEBF_RES_TIME_STYLE_SELECT'),
				dataset: {
					type: 'radio',
					value: 'select',
					inputName: this.styleInputName,
					checked: this.style === 'select'
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				id: 'time-state-style-slots',
				text: Loc.getMessage('WEBF_RES_TIME_STYLE_SLOT'),
				dataset: {
					type: 'radio',
					value: 'slots',
					inputName: this.styleInputName,
					checked: this.style === 'slots'
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				delimiter: true
			},
			{
				id: 'time-state-scale',
				text: Loc.getMessage('WEBF_RES_TIME_BOOKING_SIZE'),
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
				text: Loc.getMessage('WEBF_RES_TIME_SHOW_FREE_ONLY'),
				dataset: {
					type: 'checkbox',
					value: 'Y',
					checked: this.showOnlyFree
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				id: this.stateShowFinishId,
				text: Loc.getMessage('WEBF_RES_TIME_SHOW_FINISH_TIME'),
				dataset: {
					type: 'checkbox',
					value: 'Y',
					checked: this.showFinishTime
				},
				onclick: this.menuItemClick.bind(this)
			}
		];
	}

	getCurrentModeState()
	{
		return (this.style === 'select'
			? Loc.getMessage('WEBF_RES_TIME_STYLE_SELECT')
			: Loc.getMessage('WEBF_RES_TIME_STYLE_SLOT'))
			+ ',<br>'
			+ Loc.getMessage('WEBF_RES_TIME_BOOKING_SIZE') + ': '
			+ this.getDurationLabelByValue(this.scale);
	}

	handleControlChanges()
	{
		super.handleControlChanges();
		this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
	}

	menuItemClick(e, menuItem)
	{
		let target = e.target || e.srcElement;
		if (Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input'
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
	}


	getDurationMenuItems()
	{
		let
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
				onclick: this.menuItemClick.bind(this)
			});
		}, this);

		return menuItems;
	}


	getDurationList()
	{
		if (!this.durationList)
		{
			this.durationList = BookingUtil.getDurationList(false);
			this.durationList = this.durationList.filter(function(duration)
			{
				return duration.value && duration.value >= 15 && duration.value <= 240;
			});
		}
		return this.durationList;
	}

	getDurationLabelByValue(duration)
	{
		let foundDuration = this.getDurationList().find(function(item){return item.value === duration});
		return foundDuration ? foundDuration.label : null;
	}

	getStyle()
	{
		return this.style;
	}

	getScale()
	{
		return this.scale;
	}

	getShowOnlyFree()
	{
		return this.showOnlyFree ? 'Y' : 'N';
	}

	getShowFinishTime()
	{
		return this.showFinishTime ? 'Y' : 'N';
	}
}


