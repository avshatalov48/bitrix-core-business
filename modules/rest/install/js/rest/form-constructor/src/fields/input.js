import { Dom, Tag, Event, Type } from 'main.core';
import { BaseField } from './base-field';

export class Input extends BaseField
{
	#errorBlock: HTMLElement;

	getContent(): HTMLElement
	{
		const wrapper = Tag.render`
			<div class="ui-ctl-container"/>
		`;

		if (this.options.label)
		{
			const inputTitle = Tag.render`
				<div class="ui-ctl-top">
					<div class="ui-ctl-title">${this.options.label}</div>
				</div>
			`;
			Dom.append(inputTitle, wrapper);
		}

		const input = Tag.render`
				<div class="ui-ctl ui-ctl-textbox">
					<input type="text" id="${this.getId()}" class="ui-ctl-element">
				</div>
		`;
		const inputElement = input.querySelector('input');

		if (this.options.placeholder)
		{
			Dom.attr(inputElement, {
				placeholder: this.options.placeholder,
			});
		}

		if (this.options.value)
		{
			Dom.attr(inputElement, {
				value: this.options.value,
			});
		}

		Event.bind(inputElement, 'input', (event) => {
			Dom.hide(this.#getErrorBlock());

			if (Dom.hasClass(wrapper, 'ui-ctl-warning'))
			{
				Dom.removeClass(wrapper, 'ui-ctl-warning');
			}

			if (Type.isNil(event.target.value) || event.target.value === '')
			{
				this.emit('onUnreadySave');
				this.readySave = false;
			}
			else
			{
				this.emit('onReadySave');
				this.readySave = true;
			}

			this.value = event.target.value;
		});

		Dom.append(input, wrapper);
		Dom.append(this.#getErrorBlock(), wrapper);
		Dom.hide(this.#getErrorBlock());
		this.subscribe('error', (event) => {
			const messages = event.data.messages;
			this.#getErrorBlock().innerHTML = Type.isArray(messages) ? messages.join('<br>') : messages;
			Dom.show(this.#getErrorBlock());

			if (!Dom.hasClass(wrapper, 'ui-ctl-warning'))
			{
				Dom.addClass(wrapper, 'ui-ctl-warning');
			}
		});

		return wrapper;
	}

	#getErrorBlock(): HTMLElement
	{
		if (!this.#errorBlock)
		{
			this.#errorBlock = Tag.render`
				<div class="ui-ctl-bottom bitrix-einvoice-error-block"></div>
			`;
		}

		return this.#errorBlock;
	}
}
