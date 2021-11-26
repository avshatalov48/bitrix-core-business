import { Event } from 'main.core';

export class SecretBlock
{
	constructor(options = {elementId: string, conditionElementId: string})
	{
		this.element = document.getElementById(options.elementId);
		this.conditionElement = document.getElementById(options.conditionElementId);

		if(!this.element || !this.conditionElement)
		{
			return;
		}

		this.element = this.element.parentElement.parentElement;

		this.element.style.display = this.conditionElement.checked ? 'block' : 'none';

		this.bindEvents();
	}

	bindEvents()
	{
		Event.bind(this.conditionElement, 'change', (event) => {
			this.element.style.display = event.target.checked ? 'block' : 'none';
		});
	}


}