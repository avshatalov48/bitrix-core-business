// @flow

import {Type, Tag} from 'main.core';
import type { OptionsField } from '../process-types';
import { BaseField } from './base-field';
import { DialogStyle } from '../dialog';

export class SelectField extends BaseField
{
	type: string = 'select';
	multiple: boolean = false;
	size: number;
	list: Array = [];
	className: string = DialogStyle.ProcessOptionSelect;

	constructor(options: OptionsField)
	{
		super(options);

		if ('multiple' in options)
		{
			this.multiple = Type.isBoolean(options.multiple) ? options.multiple === true : options.multiple === 'Y';
		}
		if (this.multiple)
		{
			if ('size' in options)
			{
				this.size = options.size;
			}
		}
		if ('list' in options)
		{
			this.list = options.list;
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
			this.value = value;
		}
		if (this.field)
		{
			if (this.multiple)
			{
				for (let k = 0; k < this.field.options.length; k++)
				{
					this.field.options[k].selected = (this.value.indexOf(this.field.options[k].value) !== -1);
				}
			}
			else
			{
				this.field.value = this.value;
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
				for (let k = 0; k < this.field.options.length; k++)
				{
					if (this.field.options[k].selected)
					{
						this.value.push(this.field.options[k].value);
					}
				}
			}
			else
			{
				this.value = this.field.value;
			}
		}
		return this.value;
	}

	isFilled(): boolean
	{
		if (this.field)
		{
			for (let k = 0; k < this.field.options.length; k++)
			{
				if (this.field.options[k].selected)
				{
					return true;
				}
			}
		}
		return false;
	}

	render(): HTMLElement
	{
		if (!this.field)
		{
			this.field = Tag.render`<select id="${this.id}" name="${this.name}"></select>`;
		}
		if (this.multiple)
		{
			this.field.multiple = 'multiple';
			if (this.size)
			{
				this.field.size = this.size;
			}
		}

		Object.keys(this.list).forEach(itemId => {
			let selected;
			if (this.multiple === true)
			{
				selected = (this.value.indexOf(itemId) !== -1);
			}
			else
			{
				selected = (itemId === this.value);
			}

			let option = this.field.appendChild(Tag.render`<option value="${itemId}">${this.list[itemId]}</option>`);
			if (selected)
			{
				option.selected = 'selected';
			}
		});

		return this.field;
	}

	lock(flag: boolean = true)
	{
		this.disabled = flag;
		this.field.disabled = !!flag;
		return this;
	}
}
