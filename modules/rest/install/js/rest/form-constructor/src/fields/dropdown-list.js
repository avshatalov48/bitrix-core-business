import { Dom, Tag, Type, Event } from 'main.core';
import type { FieldConfig } from '../types';
import { BaseField } from './base-field';

export class DropdownList extends BaseField
{
	constructor(options: FieldConfig)
	{
		super(options);
		this.readySave = true;
	}

	renderFieldContainer(): HTMLElement
	{
		const wrapper = Tag.render`
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
			</div>
		`;

		if (Type.isArray(this.options.items))
		{
			const itemsWrapper = Tag.render`
				<select class="ui-ctl-element" id="${this.getId()}" />
			`;
			this.options.items.forEach((item) => {
				const itemElement = Tag.render`
					<option value="${item.value}">${item.name}</option>
				`;

				if (this.options.value === item.value)
				{
					Dom.attr(itemElement, {
						selected: true,
					});
				}

				Dom.append(itemElement, itemsWrapper);
			});
			Dom.append(itemsWrapper, wrapper);

			Event.bind(itemsWrapper, 'change', (event) => {
				this.value = event.target.value;
				if (Object.prototype.hasOwnProperty.call(this.options, 'updateForm') && this.options.updateForm)
				{
					this.emit('onFieldChange', {
						target: event.target,
						field: this,
					});
				}
			});
		}

		return wrapper;
	}
}
