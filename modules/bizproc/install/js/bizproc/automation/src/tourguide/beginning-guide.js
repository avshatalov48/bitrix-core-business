import {Loc} from 'main.core';
import {Guide} from 'ui.tour';

export class BeginningGuide
{
	#guide: Guide;

	constructor(options)
	{
		this.#guide = new Guide({
			steps: [
				{
					target: options.target,
					title: Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_BEGINNING_TITLE'),
					text: Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_BEGINNING_SUBTITLE'),
					article: '16547606',
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