import {BaseField} from 'landing.ui.field.basefield';
import {Text, Type, Event, Dom, Runtime} from 'main.core';
import {BaseEvent} from 'main.core.events';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

export class TextField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.TextField');
		this.subscribeFromOptions(fetchEventsFromOptions(options));

		this.bind = this.options.bind;
		this.changeTagButton = this.options.changeTagButton;
		this.onInputHandler = Type.isFunction(this.options.onInput) ? this.options.onInput : () => {};
		this.onValueChangeHandler = Type.isFunction(this.options.onValueChange) ? this.options.onValueChange : () => {};
		this.textOnly = Type.isBoolean(this.options.textOnly) ? this.options.textOnly : false;
		this.content = this.textOnly ? Text.encode(this.content) : this.content;
		this.input.innerHTML = this.content;

		this.onInputClick = this.onInputClick.bind(this);
		this.onInputMousedown = this.onInputMousedown.bind(this);
		this.onDocumentMouseup = this.onDocumentMouseup.bind(this);
		this.onInputInput = this.onInputInput.bind(this);
		this.onDocumentClick = this.onDocumentClick.bind(this);
		this.onDocumentKeydown = this.onDocumentKeydown.bind(this);
		this.onInputKeydown = this.onInputKeydown.bind(this);

		Event.bind(this.input, 'click', this.onInputClick);
		Event.bind(this.input, 'mousedown', this.onInputMousedown);
		Event.bind(this.input, 'input', this.onInputInput);
		Event.bind(this.input, 'keydown', this.onInputKeydown);

		Event.bind(document, 'click', this.onDocumentClick);
		Event.bind(document, 'keydown', this.onDocumentKeydown);
		Event.bind(document, 'mouseup', this.onDocumentMouseup);
	}

	onInputInput()
	{
		this.onInputHandler(this.input.innerText);
		this.onValueChangeHandler(this);

		const event = new BaseEvent({
			data: {value: this.getValue()},
			compatData: [this.getValue()],
		});

		this.emit('onChange', event);
	}

	onDocumentKeydown(event)
	{
		if (event.keyCode === 27)
		{
			if (this.isEditable())
			{
				if (this === BX.Landing.UI.Field.BaseField.currentField)
				{
					BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				}

				this.disableEdit();
			}
		}
	}

	onInputKeydown(event)
	{
		if (event.keyCode === 13)
		{
			if (this.isTextOnly())
			{
				event.preventDefault();
			}
		}
	}

	enableTextOnly()
	{
		this.textOnly = true;
		this.input.innerHTML = `${this.input.innerText}`.trim();
	}

	disableTextOnly()
	{
		this.textOnly = false;
	}

	isTextOnly()
	{
		return this.textOnly;
	}

	isContentEditable()
	{
		return this.contentEditable !== false;
	}

	onDocumentClick()
	{
		if (this.isEditable() && !this.fromInput)
		{
			if (this === BX.Landing.UI.Field.BaseField.currentField)
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			}

			this.disableEdit();
		}

		this.fromInput = false;
	}

	onDocumentMouseup()
	{
		setTimeout(() => {
			this.fromInput = false;
		}, 10);
	}

	onInputClick(event)
	{
		event.preventDefault();
		event.stopPropagation();
		this.fromInput = false;
	}

	onInputMousedown(event)
	{
		this.enableEdit();

		BX.Landing.UI.Tool.ColorPicker.hideAll();

		requestAnimationFrame(() => {
			if (event.target.nodeName === 'A')
			{
				const range = document.createRange();
				range.selectNode(event.target);
				window.getSelection().removeAllRanges();
				window.getSelection().addRange(range);
			}
		});

		this.fromInput = true;

		event.stopPropagation();
	}

	enableEdit()
	{
		if (!this.isEditable())
		{
			if (this !== BX.Landing.UI.Field.BaseField.currentField && BX.Landing.UI.Field.BaseField.currentField !== null)
			{
				BX.Landing.UI.Field.BaseField.currentField.disableEdit();
			}

			BX.Landing.UI.Field.BaseField.currentField = this;

			if (!this.isTextOnly())
			{
				if (this.changeTagButton)
				{
					this.changeTagButton.onChangeHandler = this.onChangeTag.bind(this);
				}

				BX.Landing.UI.Panel.EditorPanel.getInstance().show(this.layout, null, this.changeTagButton ? [this.changeTagButton] : null);
				this.input.contentEditable = true;
			}
			else
			{
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
				this.input.contentEditable = true;
			}

			if (!this.isContentEditable())
			{
				this.input.contentEditable = false;
			}
		}
	}

	onChangeTag(value)
	{
		this.tag = value;
	}

	disableEdit()
	{
		this.input.contentEditable = false;
	}

	isEditable()
	{
		return this.input.isContentEditable;
	}

	reset()
	{
		this.setValue('');
	}

	adjustTags(element)
	{
		if (element.lastChild && element.lastChild.nodeName === 'BR')
		{
			Dom.remove(element.lastChild);
			this.adjustTags(element);
		}

		return element;
	}

	getValue()
	{
		if (this.textOnly)
		{
			return this.input.innerText;
		}

		return this.adjustTags(Runtime.clone(this.input)).innerHTML.replace(/&nbsp;/g, '');
	}
}

export {
	TextField as Text,
};