// @flow

import {Type, Tag} from 'main.core';
import type { OptionsField } from '../process-types';
import { BaseField } from './base-field';
import { DialogStyle } from '../dialog';

export class CheckboxField extends BaseField
{
	type: string = 'checkbox';
	list: Array = [];
	multiple: boolean = false;
	className: string = DialogStyle.ProcessOptionCheckbox;

	constructor(options: OptionsField)
	{
		super(options);

		if ('list' in options)
		{
			this.list = options.list;
		}
		this.multiple = (this.list.length > 1);
		if (this.multiple)
		{
			this.class = DialogStyle.ProcessOptionMultiple;
		}
	}

	setValue(value: any)
	{
		if (this.multiple)
		{
			this.value = Type.isArray(value) ? value : [value];
		}
		else
		{
			if (value === 'Y' || value === 'N' || value === null || value === undefined)
			{
				value = (value === 'Y');//Boolean
			}
			this.value = value;
		}
		if (this.field)
		{
			if (this.multiple)
			{
				const optionElements = this.field.querySelectorAll("input[type=checkbox]");
				if (optionElements)
				{
					for (let k = 0; k < optionElements.length; k++)
					{
						optionElements[k].checked = (this.value.indexOf(optionElements[k].value) !== -1);
					}
				}
			}
			else
			{
				const optionElement = this.field.querySelector("input[type=checkbox]");
				if (optionElement)
				{
					optionElement.checked =
						Type.isBoolean(this.value) ? this.value : (optionElement.value === this.value);
				}
			}
		}
		return this;
	}
	getValue(): any
	{
		if (this.field && this.disabled !== true)
		{
			if (this.multiple)
			{
				this.value = [];
				const optionElements = this.field.querySelectorAll("input[type=checkbox]");
				if (optionElements)
				{
					for (let k = 0; k < optionElements.length; k++)
					{
						if (optionElements[k].checked)
						{
							this.value.push(optionElements[k].value);
						}
					}
				}
			}
			else
			{
				const optionElement = this.field.querySelector("input[type=checkbox]");
				if (optionElement)
				{
					if (optionElement.value && optionElement.value !== 'Y')
					{
						this.value = optionElement.checked ? optionElement.value : '';
					}
					else
					{
						this.value = optionElement.checked;
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
			const optionElements = this.field.querySelectorAll("input[type=checkbox]");
			if (optionElements)
			{
				return true;
			}
		}
		return false;
	}

	getInput(): ?HTMLElement | ?HTMLElement[]
	{
		if (this.field)
		{
			if (this.multiple)
			{
				const optionElements = this.field.querySelectorAll("input[type=checkbox]");
				if (optionElements)
				{
					return optionElements;
				}
			}
			else
			{
				const optionElement = this.field.querySelector("input[type=checkbox]");
				if (optionElement)
				{
					return optionElement;
				}
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
		if (this.multiple)
		{
			Object.keys(this.list).forEach(itemId => {
				if (this.value.indexOf(itemId) !== -1)
				{
					this.field.appendChild(Tag.render`<label><input type="checkbox" name="${this.name}[]" value="${itemId}" checked>${this.list[itemId]}</label>`);
				}
				else
				{
					this.field.appendChild(Tag.render`<label><input type="checkbox" name="${this.name}[]" value="${itemId}">${this.list[itemId]}</label>`);
				}
			});
		}
		else
		{
			if (Type.isBoolean(this.value))
			{
				if (this.value)
				{
					this.field.appendChild(Tag.render`<input type="checkbox" id="${this.id}_inp" name="${this.name}" value="Y" checked>`);
				}
				else
				{
					this.field.appendChild(Tag.render`<input type="checkbox" id="${this.id}_inp" name="${this.name}" value="Y">`);
				}
			}
			else
			{
				if (this.value !== '')
				{
					this.field.appendChild(Tag.render`<input type="checkbox" id="${this.id}_inp" name="${this.name}" value="${this.value}" checked>`);
				}
				else
				{
					this.field.appendChild(Tag.render`<input type="checkbox" id="${this.id}_inp" name="${this.name}" value="${this.value}>"`);
				}
			}
		}

		return this.field;
	}

	lock(flag: boolean = true)
	{
		this.disabled = flag;
		if (this.field)
		{
			const optionElements = this.field.querySelectorAll("input[type=checkbox]");
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
