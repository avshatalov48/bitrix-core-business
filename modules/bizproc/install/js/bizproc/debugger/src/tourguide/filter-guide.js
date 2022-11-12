import {Loc} from 'main.core';
import {Guide} from 'ui.tour';
import {Helper} from "../helper";

export class FilterGuide
{
	constructor(options)
	{
		this.guide = new Guide({
			steps: [
				{
					target: options.target,
					title: Loc.getMessage('BIZPROC_JS_DEBUGGER_FILTER_TOUR_TITLE'),
					text: FilterGuide.#getText(),
					article: '16087180',
					events: options.events ?? {},
					condition: {
						top: true,
						bottom: false,
						color: 'primary',
					},
				},
			],
			onEvents: true
		});

		this.bindEvents();
	}

	bindEvents()
	{
		// EventEmitter.subscribe('UI.Tour.Guide:onFinish'....
	}

	start()
	{
		this.guide.getPopup().setWidth(365);
		this.guide.showNextStep();
	}

	static #getText(): Element
	{
		return `
			<ul class="bizproc-debugger-filter-guide-list">
				<li class="bizproc-debugger-filter-guide-list-item">
					${Helper.toHtml(Loc.getMessage('BIZPROC_JS_DEBUGGER_FILTER_TOUR_TEXT_LINE_1'))}
				</li>
				<li class="bizproc-debugger-filter-guide-list-item">
					${Helper.toHtml(Loc.getMessage('BIZPROC_JS_DEBUGGER_FILTER_TOUR_TEXT_LINE_2'))}
				</li>
			</ul>
		`;
	}
}
