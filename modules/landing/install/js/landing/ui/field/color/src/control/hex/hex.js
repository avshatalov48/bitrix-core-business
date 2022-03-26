import {Dom, Event, Runtime, Tag} from 'main.core';
import isHex from '../../internal/is-hex';
import ColorValue from "../../color_value";

import './css/hex.css';
import BaseControl from "../base_control/base_control";
import {BaseEvent} from 'main.core.events';
import {PageObject} from 'landing.pageobject';

export default class Hex extends BaseControl
{
	static +DEFAULT_TEXT: string = '#HEX';
	static +DEFAULT_COLOR: string = '#000000';
	static +DEFAULT_BG: string = '#eeeeee';

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Hex');
		this.previewMode = false;

		this.onInput = Runtime.debounce(this.onInput.bind(this), 300);
		this.onButtonClick = this.onButtonClick.bind(this);
	}

	setPreviewMode(preview: boolean)
	{
		this.previewMode = !!preview;
	}

	buildLayout(): HTMLElement
	{
		if (!this.previewMode)
		{
			// todo: add Enter click handler
			Event.bind(this.getInput(), 'input', this.onInput);
			Event.bind(this.getButton(), 'click', this.onButtonClick);
		}

		this.adjustColors(Hex.DEFAULT_COLOR, Hex.DEFAULT_BG);

		return Tag.render`
			<div class="landing-ui-field-color-hex">
				${this.getInput()}
				${this.getButton()}
			</div>
		`;
	}

	getInput(): HTMLInputElement
	{
		return this.cache.remember('input', () => {
			return this.previewMode
				? Tag.render`<div class="landing-ui-field-color-hex-preview">${Hex.DEFAULT_TEXT}</div>`
				: Tag.render`<input type="text" name="hexInput" value="${Hex.DEFAULT_TEXT}" class="landing-ui-field-color-hex-input">`;
		});
	}

	getButton(): SVGElement
	{
		return this.cache.remember('editButton', () => {
			return this.previewMode
				? Tag.render`
					<svg class="landing-ui-field-color-hex-preview-btn" width="9" height="9" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M7.108 0l1.588 1.604L2.486 7.8.896 6.194 7.108 0zM.006 8.49a.166.166 0 00.041.158.161.161 0 00.16.042l1.774-.478L.484 6.715.006 8.49z"
							fill="#525C69"
							fill-rule="evenodd"/>
					</svg>`
				: Tag.render`
					<svg class="landing-ui-field-color-hex-preview-btn" width="12" height="9" xmlns="http://www.w3.org/2000/svg">
						<path
							d="M4.27 8.551L.763 5.304 2.2 3.902l2.07 1.846L9.836.533l1.439 1.402z"
							fill="#525C69"
							fill-rule="evenodd"/>
					</svg>`;
		});
	}

	onInput(): void
	{
		let value = this.getInput().value.replace(/[^\da-f]/gi, '');
		value = value.substring(0, 6);
		this.getInput().value = '#' + value.toLowerCase();

		this.onChange();
	}

	onButtonClick(): void
	{
		this.onChange();
		this.emit('onButtonClick', {color: this.getValue()});
	}

	onChange(event: ?BaseEvent)
	{
		const color = (this.getInput().value.length === 7 && isHex(this.getInput().value))
			? new ColorValue(this.getInput().value)
			: null;
		this.setValue(color);

		this.cache.delete('value');
		this.emit('onChange', {color: color});
	}

	adjustColors(textColor: string, bgColor: string)
	{
		Dom.style(this.getInput(), 'background-color', bgColor);
		Dom.style(this.getInput(), 'color', textColor);
		Dom.style(this.getButton().querySelector('path'), 'fill', textColor);
	}

	focus(): void
	{
		if (!this.previewMode)
		{
			if (this.getValue() === null)
			{
				this.getInput().value = '#';
			}
			this.getInput().focus();
		}
	}

	unFocus(): void
	{
		if (!this.previewMode)
		{
			this.getInput().blur();
		}
	}

	getValue(): ?ColorValue
	{
		return this.cache.remember('value', () => {
			return (this.getInput().value === Hex.DEFAULT_TEXT)
				? null
				: new ColorValue(this.getInput().value);
		});
	}

	setValue(value: ?ColorValue)
	{
		// todo: set checking in always controls?
		if (this.isNeedSetValue(value))
		{
			super.setValue(value);

			if (value !== null)
			{
				this.adjustColors(value.getContrast().getHex(), value.getHex());
				this.setActive();
			}
			else
			{
				this.adjustColors(Hex.DEFAULT_COLOR, Hex.DEFAULT_BG);
				this.unsetActive();
			}

			if (this.previewMode)
			{
				this.getInput().innerText = (value !== null) ? value.getHex() : Hex.DEFAULT_TEXT;
			}
			else if (PageObject.getRootWindow().document.activeElement !== this.getInput())
			{
				this.getInput().value = (value !== null) ? value.getHex() : Hex.DEFAULT_TEXT;
			}
		}
	}

	setActive(): void
	{
		Dom.addClass(this.getInput(), Hex.ACTIVE_CLASS);
	}

	unsetActive(): void
	{
		Dom.removeClass(this.getInput(), Hex.ACTIVE_CLASS);
	}

	isActive(): boolean
	{
		return Dom.hasClass(this.getInput(), Hex.ACTIVE_CLASS);
	}
}
