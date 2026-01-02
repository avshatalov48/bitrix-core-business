import { Form } from 'ui.feedback.form';

export class VibeAuto extends BX.Landing.Widget.Base
{
	constructor(element, option)
	{
		super(element);
		this.initialize(element, option);
	}

	initialize(element, option)
	{
		if (element && option)
		{
			const feedbackButtonElement = element.querySelector('#feedback-button');
			if (feedbackButtonElement)
			{
				BX.Event.bind(feedbackButtonElement, 'click', () => this.openForm(option));
			}
		}
	}

	openForm(params)
	{
		Form.open(
			{
				id: params.id,
				portalUri: params.portal,
				forms: params.forms,
				presets: params.presets,
			},
		);
	}
}
