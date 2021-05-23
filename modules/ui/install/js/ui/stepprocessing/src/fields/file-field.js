// @flow

import { Tag, Loc } from 'main.core';
import type { OptionsField } from '../process-types';
import { BaseField } from './base-field';
import { DialogStyle } from '../dialog';

export class FileField extends BaseField
{
	type: string = 'file';
	className: string = DialogStyle.ProcessOptionFile;

	constructor(options: OptionsField)
	{
		if (!('emptyMessage' in options))
		{
			options.emptyMessage = Loc.getMessage('UI_STEP_PROCESSING_FILE_EMPTY_ERROR');
		}
		super(options);
	}

	setValue(value: File | FileList)
	{
		this.value = value;
		if (this.field)
		{
			if (value instanceof FileList)
			{
				this.field.files = value;
			}
			else if (value instanceof File)
			{
				this.field.files[0] = value;
			}
		}
		return this;
	}
	getValue(): ?File
	{
		if (this.field && this.disabled !== true)
		{
			if (typeof(this.field.files[0]) != "undefined")
			{
				this.value = this.field.files[0];
			}
		}
		return this.value;
	}

	isFilled(): boolean
	{
		if (this.field)
		{
			if (typeof(this.field.files[0]) != "undefined")
			{
				return true;
			}
		}
		return false;
	}

	render(): HTMLElement
	{
		if (!this.field)
		{
			this.field = Tag.render`<input type="file" id="${this.id}" name="${this.name}">`;
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
