import { Tag, Type, Dom, Event } from 'main.core';

export default class Checkbox
{
	constructor(options)
	{
		this.link = options.link;
		this.checked = this.link.active;
		this.create();
	}

	create()
	{
		this.container = this.createContainer();
		this.checkbox = this.createCheckbox();

		Event.bind(this.checkbox, 'click', this.saveCheckBoxState.bind(this));
		Dom.append(this.checkbox, this.container);
	}

	createContainer()
	{
		return Tag.render`
			<div class="calendar-sharing-dialog-controls-checkbox-container"></div>
		`;
	}

	createCheckbox()
	{
		return Tag.render`
			<input type="checkbox" ${this.checked ? 'checked' : ''}>
		`;
	}

	saveCheckBoxState()
	{
		this.link.active = this.checkbox.checked;
		BX.ajax.runAction('calendar.api.sharingajax.toggleLink', {
			data: {
				userLinkId: this.link.id,
				isActive: this.link.active,
			}
		});
		// BX.userOptions.save('calendar', 'sharing-dialog-checkbox', this.link, this.checkbox.checked);
	}

	getContainer()
	{
		return this.container;
	}

	renderTo(node: HTMLElement): HTMLElement | null
	{
		if (Type.isDomNode(node))
		{
			return node.appendChild(this.getContainer());
		}

		return null;
	}
}