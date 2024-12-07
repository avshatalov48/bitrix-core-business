import { Type } from 'main.core';

export class Errors
{
	errorsMessage: HTMLElement;
	errorsWrapper: HTMLElement;

	constructor(container)
	{
		this.errorsWrapper = container.querySelector('#iblock-property-details-errors');
		this.errorsMessage = this.errorsWrapper.querySelector('.ui-alert-message');
	}

	show(errors: Array): void
	{
		if (Type.isArray(errors))
		{
			this.errorsMessage.innerHTML = errors.map((i) => i.message).join("\n");
		}
		else
		{
			this.errorsMessage.innerHTML = 'Unknown error';
		}
		this.errorsWrapper.style.display = 'block';
	}

	hide(): void
	{
		this.errorsMessage.innerHTML = '';
		this.errorsWrapper.style.display = 'none';
	}
}
