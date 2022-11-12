import {Loc} from 'main.core';
import {Guide} from 'ui.tour';

export class StageGuide
{
	constructor(options)
	{
		this.guide = new Guide({
			steps: [
				{
					target: options.target,
					title: Loc.getMessage('BIZPROC_JS_DEBUGGER_STAGE_TOUR_TITLE'),
					text: StageGuide.#getText(),
					article: '16483018',
					events: options.events ?? {},
					condition: {
						top: true,
						bottom: false,
						color: 'primary',
					},
				}
			],
			onEvents: true
		});
	}

	start()
	{
		this.guide.getPopup().setWidth(330);
		this.guide.showNextStep();
	}

	static #getText(): Element
	{
		return `
			<ul class="bizproc-debugger-filter-guide-list">
				<li class="bizproc-debugger-filter-guide-list-item">
					${Loc.getMessage('BIZPROC_JS_DEBUGGER_STAGE_TOUR_TEXT_LINE_1')}
				</li>
				<li class="bizproc-debugger-filter-guide-list-item">
					${Loc.getMessage('BIZPROC_JS_DEBUGGER_STAGE_TOUR_TEXT_LINE_2')}
				</li>
			</ul>
		`;
	}
}