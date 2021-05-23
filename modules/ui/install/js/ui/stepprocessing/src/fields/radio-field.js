// @flow

import {Type, Tag} from 'main.core';
import type { OptionsField } from '../process-types';
import { BaseField } from './base-field';
import { DialogStyle } from '../dialog';

export class RadioField extends BaseField
{
	type: string = 'radio';
	list: Array = [];
	className: string = DialogStyle.ProcessOptionMultiple;

	constructor(options: OptionsField)
	{
		super(options);

		if ('list' in options)
		{
			this.list = options.list;
		}
	}

	setValue(value: any)
	{
		this.value = value;
		if (this.field)
		{
			const optionElements = this.field.querySelectorAll("input[type=radio]");
			if (optionElements)
			{
				for (let k = 0; k < optionElements.length; k++)
				{
					optionElements[k].checked = (optionElements[k].value === this.value);
				}
			}
		}
		return this;
	}
	getValue(): any
	{
		if (this.field)
		{
			const optionElements = this.field.querySelectorAll("input[type=radio]");
			if (optionElements)
			{
				for (let k = 0; k < optionElements.length; k++)
				{
					if (optionElements[k].checked)
					{
						this.value = optionElements[k].value;
						break;
					}
				}
			}
		}
		return this.value;
	}

	isFilled(): boolean
	{
		if (this.field)
		{
			const optionElements = this.field.querySelectorAll("input[type=radio]");
			if (optionElements)
			{
				for (let k = 0; k < optionElements.length; k++)
				{
					if (optionElements[k].checked)
					{
						return true
					}
				}
			}
		}
		return false;
	}

	getInput(): ?HTMLElement | ?HTMLElement[]
	{
		if (this.field && this.disabled !== true)
		{
			const optionElement = this.field.querySelector("input[type=radio]");
			if (optionElement)
			{
				return optionElement;
			}
		}
		return null;
	}

	render(): HTMLElement
	{
		if (!this.field)
		{
			this.field = Tag.render`<div id="${this.id}"></div>`;
		}

		Object.keys(this.list).forEach(itemId => {
			if (itemId === this.value)
			{
				this.field.appendChild(Tag.render`<label><input type="radio" name="${this.name}" value="${itemId}" checked>${this.list[itemId]}</label>`);
			}
			else
			{
				this.field.appendChild(Tag.render`<label><input type="radio" name="${this.name}" value="${itemId}">${this.list[itemId]}</label>`);
			}
		});

		return this.field;
	}

	lock(flag: boolean = true)
	{
		this.disabled = flag;
		if (this.field)
		{
			const optionElements = this.field.querySelectorAll("input[type=radio]");
			if (optionElements)
			{
				for (let k = 0; k < optionElements.length; k++)
				{
					optionElements[k].disabled = !!flag;
				}
			}
		}
		return this;
	}
}
