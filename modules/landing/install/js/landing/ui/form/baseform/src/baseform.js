import {Type, Event, Text, Tag, Dom, Cache, Runtime} from 'main.core';
import {Env} from 'landing.env';
import typeof {BaseField} from 'landing.ui.field.basefield';
import type BaseFormOptions from './internal/type';
import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class BaseForm extends Event.EventEmitter
{
	constructor(data: BaseFormOptions = {})
	{
		super(data);
		this.setEventNamespace('BX.Landing.UI.Form.BaseForm');

		this.data = {...data};
		this.id = Reflect.has(this.data, 'id') ? this.data.id : Text.getRandom();
		this.selector = Reflect.has(this.data, 'selector') ? this.data.selector : '';
		this.title = Reflect.has(this.data, 'title') ? this.data.title : '';
		this.label = Reflect.has(this.data, 'label') ? this.data.label : '';
		this.type = Reflect.has(this.data, 'type') ? this.data.type : 'content';
		this.code = Reflect.has(this.data, 'code') ? this.data.code : '';
		this.descriptionText = Reflect.has(this.data, 'description') ? this.data.description : '';
		this.headerCheckbox = this.data.headerCheckbox;
		this.cache = new Cache.MemoryCache();

		this.fields = new BX.Landing.Collection.BaseCollection();
		this.cards = new BX.Landing.Collection.BaseCollection();

		this.layout = BaseForm.createLayout();
		this.description = BaseForm.createDescription();
		this.header = BaseForm.createHeader();
		this.body = BaseForm.createBody();
		this.footer = BaseForm.createFooter();

		Dom.append(this.header, this.layout);
		Dom.append(this.description, this.layout);
		Dom.append(this.body, this.layout);
		Dom.append(this.footer, this.layout);

		if (Type.isString(this.title) && this.title !== '')
		{
			Dom.append(document.createTextNode(this.title), this.header);
		}

		if (Type.isString(this.descriptionText) && this.descriptionText !== '')
		{
			this.description.innerHTML = this.descriptionText;
		}

		if (Type.isArray(this.data.fields) && this.data.fields.length > 0)
		{
			this.data.fields.forEach((field) => {
				this.addField(field);
			});
		}

		const {sources} = Env.getInstance().getOptions();
		if (
			Type.isPlainObject(this.headerCheckbox)
			&& Type.isArray(sources)
			&& sources.length > 0
		)
		{
			Dom.append(this.getHeaderCheckbox(), this.header);
		}
	}

	static createLayout(): HTMLDivElement
	{
		return Tag.render`<div class="landing-ui-form"></div>`;
	}

	static createHeader(): HTMLDivElement
	{
		return Tag.render`<div class="landing-ui-form-header"></div>`;
	}

	static createDescription(): HTMLDivElement
	{
		return Tag.render`<div class="landing-ui-form-description"></div>`;
	}

	static createBody(): HTMLDivElement
	{
		return Tag.render`<div class="landing-ui-form-body"></div>`;
	}

	static createFooter(): HTMLDivElement
	{
		return Tag.render`<div class="landing-ui-form-footer"></div>`;
	}

	getHeaderCheckbox(): HTMLDivElement
	{
		return this.cache.remember('headerCheckbox', () => {
			const checkboxId = Text.getRandom();
			const {text, help, state, onChange} = this.headerCheckbox;

			const input = Tag.render`
				<input type="checkbox" id="${checkboxId}" class="landing-ui-form-header-checkbox-input">
			`;
			const label = Tag.render`
				<label for="${checkboxId}" class="landing-ui-form-header-checkbox-label">${text}</label>
			`;
			const layout = Tag.render`
				<div class="landing-ui-form-header-checkbox">${input}${label}</div>
			`;

			if (Text.toBoolean(state))
			{
				input.setAttribute('checked', true);
			}

			if (Type.isFunction(onChange))
			{
				Event.bind(input, 'change', () => {
					onChange({
						state: input.checked === true,
						form: this,
					});
				});
			}

			if (Type.isString(help) && help !== '')
			{
				const helpButton = Tag.render`
					<a href="${help}" class="landing-ui-form-header-checkbox-help" target="_blank"> </a>
				`;

				Dom.append(helpButton, layout);
			}

			return layout;
		});
	}

	getHeader(): HTMLDivElement
	{
		return this.header;
	}

	getBody(): HTMLDivElement
	{
		return this.body;
	}

	getFooter(): HTMLDivElement
	{
		return this.footer;
	}

	getNode(): HTMLDivElement
	{
		return this.layout;
	}

	addField(field: BaseField)
	{
		if (Type.isObject(field))
		{
			this.fields.add(field);
			Dom.append(field.getNode(), this.getBody());
		}
	}

	addCard(card: BX.Landing.UI.Card.BaseCard)
	{
		if (Type.isObject(card))
		{
			this.cards.add(card);
			card.fields.forEach((field) => {
				this.fields.add(field);
			});
			Dom.append(card.getNode(), this.getBody());
		}
	}

	removeCard(card: BX.Landing.UI.Card.BaseCard)
	{
		if (Type.isObject(card))
		{
			card.fields.forEach((field) => {
				this.fields.remove(field);
			});

			this.cards.remove(card);
			Dom.remove(card.layout);
		}
	}

	replaceCard(oldCard: BX.Landing.UI.Card.BaseCard, newCard: BX.Landing.UI.Card.BaseCard)
	{
		this.removeCard(oldCard);
		this.addCard(newCard);
	}

	isCheckboxChecked(): boolean
	{
		const checkbox = this.header.querySelector('input');
		return Type.isDomNode(checkbox) && checkbox.checked;
	}

	clone(options: BaseFormOptions): BaseForm
	{
		const instance = new this.constructor(
			Runtime.clone(options || this.data),
		);

		this.fields.forEach((field) => {
			if (field instanceof BX.Landing.UI.Field.Date)
			{
				const newFieldData = Runtime.clone(field.data);
				newFieldData.selector = instance.selector;
				instance.addField(field.clone(newFieldData));
				return;
			}

			instance.addField(field.clone());
		});

		return instance;
	}

	serialize(): {[key: string]: any}
	{
		return this.fields.reduce((acc, field) => {
			acc[field.selector] = field.getValue();
			return acc;
		}, {});
	}

	removeField(field: BaseField)
	{
		this.fields.remove(field);
		Dom.remove(field.layout);
	}
}