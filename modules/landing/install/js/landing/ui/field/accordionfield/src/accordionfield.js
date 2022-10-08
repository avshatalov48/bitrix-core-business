import 'ui.design-tokens';

import {BaseField} from 'landing.ui.field.basefield';
import {Dom, Tag, Text} from 'main.core';
import {SmallSwitch} from 'landing.ui.field.smallswitch';

import './css/style.css';

type AccordionItemOption = {
	id: string,
	title: string,
	icon: string,
	checked: boolean,
	switcher?: boolean,
	content?: string | HTMLElement
};

export class AccordionField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.AccordionField');
		Dom.addClass(this.layout, 'landing-ui-field-accordion');
		Dom.replace(this.input, this.getItemsContainer());

		this.items = [];

		options.items.forEach((itemOptions) => {
			this.addItem(itemOptions);
		});
	}

	getItemsContainer(): HTMLDivElement
	{
		return this.cache.remember('itemsContainer', () => {
			return Tag.render`
				<div class="landing-ui-field-accordion-items-container"></div>
			`;
		});
	}

	onTitleClick(event: MouseEvent)
	{
		event.preventDefault();

		const item = event.currentTarget.closest('.landing-ui-field-accordion-item');
		if (Dom.hasClass(item, 'landing-ui-field-accordion-item-active'))
		{
			Dom.toggleClass(item, 'landing-ui-field-accordion-item-opened');
		}
	}

	createItem(options: AccordionItemOption): HTMLDivElement
	{
		const switcher = new SmallSwitch({
			value: Text.toBoolean(options.checked),
			onValueChange: () => {
				const item = switcher.layout.closest('.landing-ui-field-accordion-item');

				if (switcher.getValue())
				{
					Dom.addClass(item, 'landing-ui-field-accordion-item-active');
				}
				else
				{
					Dom.removeClass(item, 'landing-ui-field-accordion-item-active');
					Dom.removeClass(item, 'landing-ui-field-accordion-item-opened');
				}
			},
		});

		return Tag.render`
			<div class="landing-ui-field-accordion-item landing-ui-field-accordion-item-active" data-id="${options.id}">
				<div class="landing-ui-field-accordion-item-header">
					<div class="landing-ui-field-accordion-item-header-icon" style="background-image: url(${options.icon})"></div>
					<div 
						class="landing-ui-field-accordion-item-header-title"
						onclick="${this.onTitleClick.bind(this)}"
					>${options.title}</div>
					<div 
						class="landing-ui-field-accordion-item-header-switch"
						style="${options.switcher === false ? 'display: none;' : ''}"
					>
						<div class="landing-ui-field-accordion-item-header-switch-link"></div>
						${switcher.layout}
					</div>
				</div>
				<div class="landing-ui-field-accordion-item-body">
					${options.content}
				</div>
			</div>
		`;
	}

	addItem(options: AccordionItemOption)
	{
		const renderedItem = this.createItem(options);

		Dom.append(renderedItem, this.getItemsContainer());
	}
}