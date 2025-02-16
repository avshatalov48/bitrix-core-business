// @flow

import {Type, Tag, Loc} from 'main.core';
import type { OptionsField } from '../process-types';
import { DialogStyle } from '../dialog';

export class BaseField
{
	id: string;
	type: 'checkbox' | 'select' | 'radio' | 'text' | 'file';
	name: string;
	title: string;
	obligatory: boolean = false;
	emptyMessage: string = '';
	className: string = '';
	disabled: boolean = false;

	value: any = null;
	container: HTMLElement;
	field: HTMLElement;

	constructor(options: OptionsField)
	{
		this.id = ('id' in options) ? options.id : 'ProcessDialogField_' + Math.random().toString().substring(2);
		this.name = options.name;
		this.type = options.type;
		this.title = options.title;
		this.obligatory = !!options.obligatory;
		if ('value' in options)
		{
			this.setValue(options.value);
		}
		if (('emptyMessage' in options) && Type.isStringFilled(options.emptyMessage))
		{
			this.emptyMessage = options.emptyMessage;
		}
		else
		{
			this.emptyMessage = Loc.getMessage('UI_STEP_PROCESSING_EMPTY_ERROR') || '';
		}
	}

	setValue(value: any)
	{
		throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
		//this.value = value;
		//return this;
	}
	getValue(): any
	{
		throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
		//return this.value;
	}

	render(): HTMLElement
	{
		throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
		//return this.field;
	}

	lock(flag: boolean = true)
	{
		throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
		//this.disabled = flag;
		//this.field.disabled = !!flag;
		//return this;
	}

	isFilled(): boolean
	{
		throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
		//return this.field;
	}

	getInput(): ?HTMLElement | ?HTMLElement[]
	{
		return this.field ? this.field : null;
	}

	getContainer(): HTMLElement
	{
		if (!this.container)
		{
			this.container = Tag.render`<div class="${DialogStyle.ProcessOptionContainer} ${this.className}"></div>`;

			if (this.title)
			{
				this.container
					.appendChild(Tag.render`<div class="${DialogStyle.ProcessOptionsTitle}"></div>`)
						.appendChild(Tag.render`<label for="${this.id}_inp">${this.title}</label>`)
				;
			}

			this.container
				.appendChild(Tag.render`<div class="${DialogStyle.ProcessOptionsInput}"></div>`)
					.appendChild(this.render())
			;

			if (this.obligatory)
			{
				const alertId = this.id + '_alert';
				this.container
					.appendChild(Tag.render`<div id="${alertId}" class="${DialogStyle.ProcessOptionsObligatory}" style="display:none"></div>`)
						.appendChild(Tag.render`<span class="ui-alert-message">${this.emptyMessage}</span>`)
				;
			}
		}

		return this.container;
	}

	showWarning(message?: string)
	{
		const messageText = message ?? this.emptyMessage;
		const alertId = this.id + '_alert';
		const optionElement = this.container.querySelector('#' + alertId);
		if (optionElement)
		{
			if (Type.isStringFilled(messageText))
			{
				const messageElement = optionElement.querySelector('.ui-alert-message');
				messageElement.innerHTML = messageText;
			}
			optionElement.style.display = 'block';
		}
		else if (Type.isStringFilled(messageText))
		{
			this.container
				.appendChild(Tag.render`<div id="${alertId}" class="${DialogStyle.ProcessOptionsObligatory}"></div>`)
					.appendChild(Tag.render`<span class="ui-alert-message">${messageText}</span>`)
			;
		}
		return this;
	}
	hideWarning()
	{
		const alertId = this.id + '_alert';
		const optionElement = this.container.querySelector('#' + alertId);
		if (optionElement)
		{
			optionElement.style.display = 'none';
		}
		return this;
	}
}
