// @flow

import {Type, Tag} from 'main.core';
import type { OptionsField } from '../process-types';
import { BaseField } from './base-field';
import { DialogStyle } from '../dialog';

export class TextField extends BaseField
{
	type: string = 'text';
	className: string = DialogStyle.ProcessOptionText;
	rows: number = 10;
	cols: number = 50;

	constructor(options: OptionsField)
	{
		super(options);

		if (options.textSize)
		{
			this.cols = options.textSize;
		}
		if (options.textLine)
		{
			this.rows = options.textLine;
		}
	}

	setValue(value: string)
	{
		this.value = value;
		if (this.field)
		{
			this.field.value = this.value;
		}
		return this;
	}
	getValue(): string
	{
		if (this.field && this.disabled !== true)
		{
			if (typeof(this.field.value) !== 'undefined')
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
			if (typeof(this.field.value) !== 'undefined')
			{
				return Type.isStringFilled(this.field.value);
			}
		}
		return false;
	}

	render(): HTMLElement
	{
		if (!this.field)
		{
			this.field = Tag.render`<textarea id="${this.id}" name="${this.name}" cols="${this.cols}" rows="${this.rows}"></textarea>`;
		}
		if (this.value)
		{
			this.field.value = this.value;
		}
		return this.field;
	}

	lock(flag: boolean = true)
	{
		this.disabled = flag;
		this.field.disabled = !!flag;
		return this;
	}
}
