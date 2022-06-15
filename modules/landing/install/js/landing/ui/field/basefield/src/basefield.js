import {Type, Event, Tag, Text, Dom, Runtime, Cache} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class BaseField extends EventEmitter
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
		this.subscribeFromOptions(fetchEventsFromOptions(options));

		this.data = {...options};
		this.options = this.data;
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
		this.cache = new Cache.MemoryCache();
		this.contentRoot = Reflect.has(this.data, 'contentRoot') ? this.data.contentRoot : null;
		this.readyToSave = true;    // false - if data not loaded yet

		const {onValueChange} = this.data;
		this.onValueChangeHandler = Type.isFunction(onValueChange) ? onValueChange : (() => {});
		this.onPaste = this.onPaste.bind(this);

		this.layout = BaseField.createLayout();
		this.header = BaseField.createHeader();
		this.input = this.createInput();
		this.setTitle(this.title);

		Dom.append(this.header, this.layout);
		Dom.append(this.input, this.layout);

		Dom.attr(this.layout, 'data-selector', this.selector);
		Dom.attr(this.input, 'data-placeholder', this.placeholder);

		if (Type.isArrayLike(this.className))
		{
			Dom.addClass(this.layout, this.className);
		}

		this.setDescription(this.descriptionText);

		if (this.data.disabled === true)
		{
			this.disable();
		}

		Event.bind(this.input, 'paste', this.onPaste);

		this.init();

		if (this.data.help)
		{
			BX.Dom.append(top.BX.UI.Hint.createNode(this.data.help), this.header);
			top.BX.UI.Hint.init(BX.Landing.UI.Panel.StylePanel.getInstance().layout);
		}
	}

	setTitle(title: string)
	{
		this.header.innerHTML = Text.encode(title);
	}

	getDescription(): ?HTMLDivElement
	{
		return this.layout.querySelector('.landing-ui-field-description');
	}

	setDescription(description: string)
	{
		if (
			Type.isString(description)
			&& description !== ''
		)
		{
			this.descriptionText = description;
			this.description = BaseField.createDescription(this.descriptionText);
			Dom.remove(this.getDescription());
			Dom.append(this.description, this.layout);
		}
	}

	removeDescription()
	{
		Dom.remove(this.getDescription());
		this.description = null;
		this.descriptionText = '';
	}

	createInput(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-input">${this.content}</div>
		`;
	}

	// eslint-disable-next-line class-methods-use-this
	init() {}

	getContext(): Window
	{
		if (this.input.ownerDocument)
		{
			return this.input.ownerDocument.defaultView;
		}

		return window;
	}

	// eslint-disable-next-line class-methods-use-this
	onPaste(event)
	{
		event.preventDefault();
		event.stopPropagation();

		if (event.clipboardData && event.clipboardData.getData)
		{
			const sourceText = event.clipboardData.getData('text/plain');
			const encodedText = BX.Text.encode(sourceText);
			const formattedHtml = encodedText.replace(new RegExp('\n', 'g'), '<br>');
			this.getContext().document.execCommand('insertHTML', false, formattedHtml);
		}
		else
		{
			// ie11
			const text = window.clipboardData.getData('text');
			this.getContext().document.execCommand('paste', true, BX.Text.encode(text));
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
		this.emit('onChange', event);
	}

	enable()
	{
		Dom.attr(this.layout, 'disabled', null);
		Dom.removeClass(this.layout, 'landing-ui-disabled');
	}

	disable()
	{
		Dom.attr(this.layout, 'disabled', true);
		Dom.addClass(this.layout, 'landing-ui-disabled');
	}

	// eslint-disable-next-line class-methods-use-this
	reset() {}
	onFrameLoad() {}

	clone(data): BaseField
	{
		return new this.constructor(
			Runtime.clone(data || this.data),
		);
	}

	getLayout(): HTMLElement
	{
		return this.layout;
	}

	setLayoutClass(className: string)
	{
		Dom.addClass(this.layout, className);
	}

	/**
	 * If field has inline style-properties (f.e. css variables) - get name of them
 	 * @returns {string[]}
	 */
	getInlineProperties(): [string]
	{
		return [];
	}

	/**
	 * If field need match computed styles by node - get name of style properties
	 * @returns {string[]}
	 */
	getComputedProperties(): [string]
	{
		// todo: get from typeSetting
		return [];
	}

	/**
	 * If field work with pseudo element - return them (f.e. :after)
	 * @returns {?string}
	 */
	getPseudoElement(): ?string
	{
		// todo: from type settings
		return null;
	}
}