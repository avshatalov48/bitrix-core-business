import { Event } from 'main.core';

export class RcEditor
{
	constructor(options = {elementId: string, conditionElementId: string})
	{
		this.element = document.getElementById(options.elementId);
		this.conditionElement = document.getElementById(options.conditionElementId);

		if(!this.element || !this.conditionElement)
		{
			return;
		}
		this.element.disabled = !this.conditionElement.checked;
		this.bindEvents();
	}

	bindEvents()
	{
		Event.bind(this.conditionElement, 'change', (event) => {
			this.element.disabled = !event.target.checked;
		});
	}


}