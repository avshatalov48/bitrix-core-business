import { ajax as Ajax } from 'main.core';

export class EnterpriceWestV2 extends BX.Landing.Widget.Base
{
	constructor(element)
	{
		super(element);
		this.initialize(element);
	}

	initialize(element)
	{
		if (element)
		{
			const trialButtonElement = element.querySelector('#trialButton');
			if (trialButtonElement)
			{
				BX.Event.bind(trialButtonElement, 'click', this.trialButtonClick);
			}
		}
	}

	trialButtonClick()
	{
		Ajax.runAction('bitrix24.license.demolicense.activateExtended')
			.then(() => {
				window.location.reload();
			})
			.catch((err) => {
				console.error(err);
			})
		;
	}
}
