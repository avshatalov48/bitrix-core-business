import { Type, Text, Dom, Tag } from 'main.core';
import type { FieldConfig } from '../types';
import { EventEmitter } from 'main.core.events';

export class BaseField extends EventEmitter
{
	options: FieldConfig;
	readySave: boolean;
	value: any;
	#errorBlock: HTMLElement;

	constructor(options: FieldConfig)
	{
		super();
		this.setEventNamespace('BX.Rest.EInvoice.Field');
		this.options = options;
		this.value = this.options.value ?? null;
		this.readySave = !(Type.isNil(this.value) || this.value === '');
		this.options.id = Type.isStringFilled(this.options.id) ? this.options.id : Text.getRandom(8);
	}

	getId(): string
	{
		return this.options.id;
	}

	getName(): string
	{
		return this.options.name;
	}

	getContent(): HTMLElement
	{
		const wrapper = Tag.render`
			<div class="container"></div>
		`;
		Dom.append(this.renderFieldContainer(), wrapper);
		Dom.append(this.renderErrorsContainer(), wrapper);
		Dom.hide(this.renderErrorsContainer());
		this.subscribe('error', (event) => {
			const messages = event.data.messages;
			this.renderErrorsContainer().innerHTML = Type.isArray(messages) ? messages.join('<br>') : messages;
			Dom.show(this.renderErrorsContainer());

			if (!Dom.hasClass(wrapper, 'ui-ctl-warning'))
			{
				Dom.addClass(wrapper, 'ui-ctl-warning');
			}
		});

		return wrapper;
	}

	renderFieldContainer(): HTMLElement
	{
		throw new Error('Must be implemented in a child class');
	}

	isReadySave(): boolean
	{
		return this.readySave;
	}

	renderErrorsContainer(): HTMLElement
	{
		if (!this.#errorBlock)
		{
			this.#errorBlock = Tag.render`
				<div class="ui-ctl-bottom bitrix-einvoice-error-block"></div>
			`;
		}

		return this.#errorBlock;
	}

	getValue(): any
	{
		return this.value;
	}
}
