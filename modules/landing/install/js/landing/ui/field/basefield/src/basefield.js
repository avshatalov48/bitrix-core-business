import {Type, Event, Tag, Text, Dom, Runtime} from 'main.core';
import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class BaseField extends Event.EventEmitter
{
	static createLayout(): HTMLDivElement
	{
		return Tag.render`<div class="landing-ui-field"></div>`;
	}

	static createHeader(): HTMLDivElement
	{
		return Tag.render`<div class="landing-ui-field-header"></div>`;
	}

	static createDescription(text: string): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-description">
				<span class="fa fa-info-circle"> </span> ${text}
			</div>
		`;
	}

	static currentField: ?BaseField = null;

	constructor(options: {[key: string]: any} = {})
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field');

		this.data = {...options};
		this.id = Reflect.has(this.data, 'id') ? this.data.id : Text.getRandom();
		this.selector = Reflect.has(this.data, 'selector') ? this.data.selector : Text.getRandom();
		this.content = Reflect.has(this.data, 'content') ? this.data.content : '';
		this.title = Type.isString(this.data.title) ? this.data.title : '';
		this.placeholder = Type.isString(this.data.placeholder) ? this.data.placeholder : '';
		this.className = Type.isString(this.data.className) ? this.data.className : '';
		this.descriptionText = Type.isString(this.data.description) ? this.data.description : '';
		this.description = null;
		this.attribute = Type.isString(this.data.attribute) ? this.data.attribute : '';
		this.hidden = Text.toBoolean(this.data.hidden);
		this.property = Type.isString(this.data.property) ? this.data.property : '';
		this.style = Reflect.has(this.data, 'style') ? this.data.style : '';

		const {onValueChange} = this.data;
		this.onValueChangeHandler = Type.isFunction(onValueChange) ? onValueChange : (() => {});
		this.onPaste = this.onPaste.bind(this);

		this.layout = BaseField.createLayout();
		this.header = BaseField.createHeader();
		this.input = this.createInput();
		this.header.innerHTML = Text.encode(this.title);

		Dom.append(this.header, this.layout);
		Dom.append(this.input, this.layout);

		Dom.attr(this.layout, 'data-selector', this.selector);
		Dom.attr(this.input, 'data-placeholder', this.placeholder);

		if (Type.isArrayLike(this.className))
		{
			Dom.addClass(this.layout, this.className);
		}

		if (
			Type.isString(this.descriptionText)
			&& this.descriptionText !== ''
		)
		{
			this.description = BaseField.createDescription(this.descriptionText);
			Dom.append(this.description, this.layout);
		}

		if (this.data.disabled === true)
		{
			this.disable();
		}

		Event.bind(this.input, 'paste', this.onPaste);

		this.init();
	}

	createInput(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-input">${this.content}</div>
		`;
	}

	// eslint-disable-next-line class-methods-use-this
	init() {}

	// eslint-disable-next-line class-methods-use-this
	onPaste(event)
	{
		event.preventDefault();

		if (event.clipboardData && event.clipboardData.getData)
		{
			const text = event.clipboardData.getData('text/plain');
			document.execCommand('insertHTML', false, Text.encode(text));
		}
		else
		{
			const text = window.clipboardData.getData('text');
			document.execCommand('paste', true, Text.encode(text));
		}
	}

	getNode(): HTMLDivElement
	{
		return this.layout;
	}

	isChanged(): boolean
	{
		const content = (() => {
			if (Type.isNil(this.content))
			{
				return '';
			}

			if (Type.isString(this.content))
			{
				return this.content.trim();
			}

			return this.content;
		})();

		return content !== this.getValue();
	}

	getValue(): string
	{
		return this.input.innerHTML.trim();
	}

	setValue(value: any = '')
	{
		const preparedValue = this.textOnly ? Text.encode(value) : value;
		this.input.innerHTML = preparedValue.toString().trim();

		this.onValueChangeHandler(this);

		const event = new Event.BaseEvent({
			data: {value: this.getValue()},
			compatData: [this.getValue()],
		});

		this.emit('change', event);
	}

	enable()
	{
		Dom.attr(this.layout, 'disabled', false);
		Dom.removeClass(this.layout, 'landing-ui-disabled');
	}

	disable()
	{
		Dom.attr(this.layout, 'disabled', true);
		Dom.addClass(this.layout, 'landing-ui-disabled');
	}

	// eslint-disable-next-line class-methods-use-this
	reset() {}

	clone(data): BaseField
	{
		return new this.constructor(
			Runtime.clone(data || this.data),
		);
	}
}