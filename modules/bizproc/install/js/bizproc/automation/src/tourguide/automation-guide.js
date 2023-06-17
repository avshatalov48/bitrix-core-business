import { Loc, Type, Text } from 'main.core';
import { Guide } from 'ui.tour';

import '../css/tourguide.css';

export class AutomationGuide
{
	#isShownRobotGuide: boolean = true;
	#isShownTriggerGuide: boolean = true;

	#isShownSupportingRobotGuide: boolean = false;

	#showRobotGuide: boolean = false;
	#showTriggerGuide: boolean = false;
	#showSupportingRobotGuide: boolean = false;

	#guideTargets: {
		trigger?: Element,
		supportingRobot?: Element,
		robot?: Element,
	} = {};

	constructor(options: {
		isShownRobotGuide: boolean,
		isShownTriggerGuide: boolean,
	})
	{
		if (Type.isBoolean(options.isShownRobotGuide))
		{
			this.#isShownRobotGuide = options.isShownRobotGuide;
		}
		if (Type.isBoolean(options.isShownTriggerGuide))
		{
			this.#isShownTriggerGuide = options.isShownTriggerGuide;
		}
	}

	get isShownRobotGuide(): boolean
	{
		return this.#isShownRobotGuide;
	}

	get isShownTriggerGuide(): boolean
	{
		return this.#isShownTriggerGuide;
	}

	setShowRobotGuide(show: boolean, target?: Element)
	{
		this.#showRobotGuide = show;

		if (show)
		{
			this.#guideTargets['robot'] = target ?? null;
		}
	}

	setShowTriggerGuide(show: boolean, target?: Element)
	{
		this.#showTriggerGuide = show;

		if (show)
		{
			this.#guideTargets['trigger'] = target ?? null;
		}
	}

	setShowSupportingRobotGuide(show: boolean, target?: Element)
	{
		this.#showSupportingRobotGuide = show;

		if (show)
		{
			this.#guideTargets['supportingRobot'] = target ?? null;
		}
	}

	#resolveShowGuides()
	{
		// settings
		if (this.#isShownTriggerGuide)
		{
			this.#showTriggerGuide = false;
		}

		if (this.#isShownSupportingRobotGuide)
		{
			this.#showSupportingRobotGuide = false;
			this.#isShownRobotGuide = true;
		}

		if (this.#isShownRobotGuide)
		{
			this.#showRobotGuide = false;
		}

		// logic
		if (this.#showSupportingRobotGuide)
		{
			this.#isShownRobotGuide = true;
		}
	}

	#getGuide(): ?Guide
	{
		let guide = null;

		if (this.#showSupportingRobotGuide)
		{
			if (Type.isDomNode(this.#guideTargets['supportingRobot']))
			{
				guide = this.#getSupportingRobotGuide();
				guide.getPopup().setAutoHide(true);
			}

			return guide;
		}

		if (this.#showTriggerGuide)
		{
			if (Type.isDomNode(this.#guideTargets['trigger']))
			{
				guide = this.#getTriggerGuide();
				guide.getPopup().setAutoHide(true);
			}

			return guide;
		}

		if (this.#showRobotGuide)
		{
			if (Type.isDomNode(this.#guideTargets['robot']))
			{
				guide = this.#getRobotGuide();
				guide.getPopup().setAutoHide(true);
			}

			return guide;
		}

		return guide;
	}

	start()
	{
		this.#resolveShowGuides();
		const guide = this.#getGuide();
		if (guide)
		{
			const bindElement = guide.getCurrentStep().target;
			if (Type.isDomNode(bindElement) && document.body.contains(bindElement))
			{
				guide.showNextStep();
			}
		}
	}

	#getRobotGuide(): Guide
	{
		return new Guide({
			steps: [
				{
					target: this.#guideTargets['robot'],
					title: Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_ROBOT_TITLE_1'),
					text: this.constructor.#getText([
						Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_ROBOT_SUBTITLE_1'),
						Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_ROBOT_SUBTITLE_2')
					]),
					article: '16547618',
					condition: {
						top: false,
						bottom: true,
						color: 'primary',
					},
					position: 'top',
					events: {
						'onShow': () => {
							this.#isShownRobotGuide = true;
						},
					},
				},
			],
			onEvents: true,
		});
	}

	#getTriggerGuide(): Guide
	{
		return new Guide({
			steps: [
				{
					target: this.#guideTargets['trigger'],
					title: Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_TRIGGER_TITLE_1'),
					text: this.constructor.#getText([
						Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_TRIGGER_SUBTITLE_1'),
						Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_TRIGGER_SUBTITLE_2')
					]),
					article: '16547632',
					condition: {
						top: false,
						bottom: true,
						color: 'primary',
					},
					position: 'top',
					events: {
						'onShow': () => {
							this.#isShownTriggerGuide = true;
						},
					},
				},
			],
			onEvents: true,
		});
	}

	#getSupportingRobotGuide(): Guide
	{
		return new Guide({
			steps: [
				{
					target: this.#guideTargets['supportingRobot'],
					title: Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_SUPPORTING_ROBOT_TITLE'),
					text: this.constructor.#getText([
						Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_SUPPORTING_ROBOT_SUBTITLE_1'),
						Loc.getMessage('BIZPROC_AUTOMATION_TOUR_GUIDE_SUPPORTING_ROBOT_SUBTITLE_2'),
					]),
					article: '16547644',
					condition: {
						top: false,
						bottom: true,
						color: 'primary',
					},
					position: 'top',
					events: {
						'onShow': () => {
							this.#isShownSupportingRobotGuide = true;
						},
					},
				},
			],
			onEvents: true,
		});
	}

	static #getText(subtitles: Array): Element
	{
		let text = `<ul class="bizproc-automation-tour-guide-list">`;

		for (const subtitle of subtitles)
		{
			text += `<li class="bizproc-automation-tour-guide-list-item"> ${Text.encode(subtitle)} </li>`;
		}

		text += `</ul>`;

		return text;
	}
}