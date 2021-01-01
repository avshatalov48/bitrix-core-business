import {Dom, Event, Tag, Text, Type} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';

import './css/style.css';

export class TextareaField extends BaseField
{
	constructor(options)
	{
		super(options);
		Dom.addClass(this.layout, 'landing-ui-field-textarea');

		this.onContentChange = this.onContentChange.bind(this);
		this.onMousewheel = this.onMousewheel.bind(this);

		Event.bind(this.input, 'input', this.onContentChange);
		Event.bind(this.input, 'keydown', this.onContentChange);
		Event.bind(this.input, 'mousewheel', this.onMousewheel);

		this.input.value = Text.encode(this.content);

		if (Type.isNumber(this.options.height))
		{
			Dom.style(this.input, 'min-height', `${this.options.height}px`);
		}

		setTimeout(() => {
			this.adjustHeight();
		}, 20);
	}

	createInput()
	{
		return Tag.render`
			<textarea class="landing-ui-field-input">${this.content}</textarea>
		`;
	}

	// eslint-disable-next-line class-methods-use-this
	onMousewheel(event)
	{
		event.stopPropagation();
	}

	// eslint-disable-next-line class-methods-use-this
	onPaste()
	{
		// Prevent BX.Landing.UI.Field.BaseField.onPaste
	}

	onContentChange()
	{
		this.adjustHeight();
		this.onValueChangeHandler(this);
	}

	adjustHeight()
	{
		this.input.style.height = '0px';
		this.input.style.height = `${Math.min(this.input.scrollHeight, 180)}px`;
	}

	getValue()
	{
		return this.input.value;
	}
}