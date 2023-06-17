import {Loc, Type, Text} from 'main.core';
import {Guide} from 'ui.tour';

export class BeginningGuide
{
	#guide: Guide;

	constructor(options: {target: HTMLElement, text?: string, article?: string})
	{
		if (!Type.isElementNode(options.target))
		{
			throw 'options.target must be Node Element';
		}

		const text =
			Type.isStringFilled(options.text)
				? options.text
				: Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_BEGINNING_SUBTITLE_1')
		;
		const article = Type.isStringFilled(options.article) ? Text.toInteger(options.article) : '';

		this.#guide = new Guide({
			steps: [
				{
					target: options.target,
					title: Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_BEGINNING_TITLE'),
					text,
					article,
					condition: {
						top: true,
						bottom: false,
						color: 'primary',
					},
					position: 'bottom',
				},
			],
			onEvents: true,
		});

		this.#guide.getPopup().setAutoHide(true);
	}

	start()
	{
		this.#guide.showNextStep();
	}
}