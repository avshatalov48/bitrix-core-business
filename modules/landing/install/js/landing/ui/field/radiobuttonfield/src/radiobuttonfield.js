import {Dom, Tag, Text, Type} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {Button} from 'ui.buttons';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

import './css/style.css';
import {Loc} from 'landing.loc';

type ItemOptions = {
	id: string,
	title: string,
	icon: string,
	button?: {
		text: string,
		onClick: () => void,
	},
	soon?: boolean,
	disabled?: boolean,
};

type RadioButtonFieldOptions = {
	selector?: string,
	selectable?: boolean,
	items: Array<ItemOptions>,
	value?: any,
};

/**
 * @memberOf BX.Landing.UI.Field
 */
export class RadioButtonField extends BaseField
{
	options: RadioButtonFieldOptions;

	constructor(options: RadioButtonFieldOptions)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.RadioButtonField');
		this.subscribeFromOptions(fetchEventsFromOptions(options));

		Dom.addClass(this.layout, 'landing-ui-field-radio-button');
		Dom.replace(this.input, this.getLayout());

		if (Type.isBoolean(this.options.selectable))
		{
			this.setSelectable(this.options.selectable);
		}
		else
		{
			this.setSelectable(true);
		}

		this.options.items.forEach((item) => {
			this.appendItem(item);
		});

		if (this.options.value)
		{
			this.setValue(this.options.value, true);
		}
		else
		{
			this.setValue(this.options.items[0].id, true);
		}
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('remember', () => {
			return Tag.render`
				<div class="landing-ui-field-radio-button-layout" data-selector="${this.selector}"></div>
			`;
		});
	}

	appendItem(options: ItemOptions): HTMLDivElement
	{
		const element = Tag.render`
			<div 
				class="landing-ui-field-radio-button-item${options.disabled ? ' landing-ui-disabled' : ''}" 
				data-value="${options.id}"
				onclick="${this.onItemClick.bind(this, options)}"
			>
				<div class="landing-ui-field-radio-button-item-icon ${options.icon}"></div>
				<div class="landing-ui-field-radio-button-item-text">
					<span>${options.title}</span>
				</div>
				${options.soon ? this.createSoonLabel() : ''}
			</div>
		`;

		if (Type.isPlainObject(options.button))
		{
			const button = new Button({
				color: Button.Color.PRIMARY,
				size: Button.Size.EXTRA_SMALL,
				text: options.button.text,
				round: true,
				events: {
					click: options.button.onClick,
				},
			});

			button.renderTo(element);
		}

		Dom.append(element, this.getLayout());
	}

	onItemClick(item: ItemOptions, event: MouseEvent)
	{
		event.preventDefault();

		if (this.options.selectable !== false)
		{
			[...this.getLayout().children].forEach((element) => {
				Dom.removeClass(element, 'landing-ui-field-radio-button-item-active');
			});

			Dom.addClass(event.currentTarget, 'landing-ui-field-radio-button-item-active');
		}

		this.emit('onChange', {item});
	}

	getValue(): string
	{
		const activeElement = [...this.getLayout().children].find((item) => {
			return Dom.hasClass(item, 'landing-ui-field-radio-button-item-active');
		});

		if (activeElement)
		{
			return Dom.attr(activeElement, 'data-value');
		}

		return '';
	}

	setValue(value: string, preventEvent: boolean)
	{
		const items = [...this.getLayout().children];

		items.forEach((element) => {
			Dom.removeClass(element, 'landing-ui-field-radio-button-item-active');
		});

		const item = items.find((currentItem) => {
			return String(Dom.attr(currentItem, 'data-value')) === String(value);
		});

		if (item)
		{
			if (this.options.selectable !== false)
			{
				Dom.addClass(item, 'landing-ui-field-radio-button-item-active');
			}

			if (!preventEvent)
			{
				this.emit('onChange', {item});
			}
		}
	}

	getSelectable(): boolean
	{
		return Text.toBoolean(this.cache.get('selectable'));
	}

	setSelectable(value: ?boolean)
	{
		this.cache.set('selectable', Text.toBoolean(value));
	}

	isSelectable(): boolean
	{
		return Text.toBoolean(this.cache.get('selectable'));
	}

	createSoonLabel(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-radio-button-item-soon-label">
				${Loc.getMessage('LANDING_UI_BASE_PRESET_PANEL_SOON_LABEL')}
			</div>
		`;
	}
}