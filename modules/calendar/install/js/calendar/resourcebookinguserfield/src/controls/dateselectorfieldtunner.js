import {FormFieldTunnerAbstract} from "../formfieldtunnerabstract";
import {FormFieldTunnerPopupAbstract} from "../formfieldtunnerpopupabstract";
import {Loc, Type, Dom} from "main.core";
import { MenuItem } from 'main.popup';

export class DateSelectorFieldTunner extends FormFieldTunnerAbstract {
	constructor()
	{
		super();
		this.label = Loc.getMessage('WEBF_RES_DATE');
		this.formLabel = Loc.getMessage('WEBF_RES_DATE_LABEL');
		this.displayed = true;
		this.displayCheckboxDisabled = true;
	}

	updateConfig(params)
	{
		super.updateConfig();
		this.style = params.style;
		this.start = params.start;
	}

	buildStatePopup(params)
	{
		params.style = params.style || this.style;
		params.start = params.start || this.start;
		this.statePopup = new DateStatePopup(params);
	}

	getValue ()
	{
		return {
			label: this.getFormLabel(),
			style: this.statePopup.getStyle(),
			start: this.statePopup.getStart()
		};
	}
}


class DateStatePopup extends FormFieldTunnerPopupAbstract
{
	constructor(params)
	{
		super(params);
		this.name = 'dateStatePopup';
		this.styleInputName = 'date-select-style';
		this.startInputName = 'date-select-start';
		this.style = params.style === 'popup' ? 'popup' : 'line'; // popup|line
		this.start = params.start === 'today' ? 'today' : 'free'; // today|free
		this.build();
	}

	getMenuItems()
	{
		return [
			new MenuItem({
				text: Loc.getMessage('WEBF_RES_CALENDAR_STYLE'),
				delimiter: true
			}),
			{
				id: 'date-state-style-popup',
				text: Loc.getMessage('WEBF_RES_CALENDAR_STYLE_POPUP'),
				dataset: {
					type: 'radio',
					value: 'popup',
					inputName: this.styleInputName,
					checked: this.style === 'popup'
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				id: 'date-state-style-line',
				text: Loc.getMessage('WEBF_RES_CALENDAR_STYLE_LINE'),
				dataset: {
					type: 'radio',
					value: 'line',
					inputName: this.styleInputName,
					checked: this.style === 'line'
				},
				onclick: this.menuItemClick.bind(this)
			},
			new MenuItem({
				text: Loc.getMessage('WEBF_RES_CALENDAR_START_FROM'),
				delimiter: true
			}),
			{
				id: 'date-state-start-from-today',
				text: Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_TODAY'),
				dataset: {
					type: 'radio',
					value: 'today',
					inputName: this.startInputName,
					checked: this.start === 'today'
				},
				onclick: this.menuItemClick.bind(this)
			},
			{
				id: 'date-state-start-from-free',
				text: Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_FREE'),
				dataset: {
					type: 'radio',
					value: 'free',
					inputName: this.startInputName,
					checked: this.start === 'free'
				},
				onclick: this.menuItemClick.bind(this)
			}
		];
	}

	getCurrentModeState()
	{
		return (this.style === 'popup'
			? Loc.getMessage('WEBF_RES_CALENDAR_STYLE_POPUP')
			: Loc.getMessage('WEBF_RES_CALENDAR_STYLE_LINE'))
			+ ', '
			+ (this.start === 'today'
				? Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_TODAY_SHORT')
				: Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_FREE_SHORT'));
	}

	handleControlChanges()
	{
		super.handleControlChanges();
		Dom.adjust(this.DOM.currentStateLink, {text: this.getCurrentModeState()});
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
			else if (menuItem.dataset.inputName === this.startInputName)
			{
				this.start = menuItem.dataset.value;
			}
		}
		this.handleControlChanges();
	}

	getStyle()
	{
		return this.style;
	}

	getStart()
	{
		return this.start;
	}
}

