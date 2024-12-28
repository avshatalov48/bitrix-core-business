import { Text, Loc, Type, Dom } from 'main.core';
import { Button, ButtonColor } from 'ui.buttons';

type ButtonId = string;
type StepId = string;

export type ButtonsData = {
	buttons: Object<StepId, Array<Button>>,
	wrapper: HTMLElement,
};

export class Buttons
{
	#buttons: Map<string, Array<Button>> = new Map();
	#sequenceSteps: [] = [];
	#currentStepId: ?StepId = null;

	#wrapper: HTMLElement;

	static createNextButton(action: Function): Button
	{
		return new Button({
			id: 'next',
			text: Text.encode(Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_NEXT_BUTTON')),
			onclick: action,
			color: ButtonColor.PRIMARY,
		});
	}

	static createBackButton(action: Function): Button
	{
		return new Button({
			id: 'back',
			text: Text.encode(Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_BACK_BUTTON')),
			onclick: action,
			color: ButtonColor.LIGHT_BORDER,
		});
	}

	static createStartButton(action: Function): Button
	{
		return new Button({
			id: 'start',
			text: Text.encode(Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_START_BUTTON')),
			onclick: action,
			color: ButtonColor.PRIMARY,
		});
	}

	constructor(config: ButtonsData)
	{
		if (Type.isPlainObject(config.buttons))
		{
			Object.entries(config.buttons).forEach(([stepId, buttons]) => {
				this.#buttons.set(stepId, buttons);
				this.#sequenceSteps.push(stepId);
			});

			if (Type.isArrayFilled(this.#sequenceSteps))
			{
				this.#currentStepId = this.#sequenceSteps.at(0);
			}
		}

		this.#wrapper = config.wrapper;
	}

	next()
	{
		const index = this.#sequenceSteps.indexOf(this.#currentStepId);
		if (index !== -1 && Type.isStringFilled(this.#sequenceSteps.at(index + 1)))
		{
			this.#currentStepId = this.#sequenceSteps.at(index + 1);
			this.show();
		}
	}

	back()
	{
		const index = this.#sequenceSteps.indexOf(this.#currentStepId);
		if (index !== -1 && index - 1 >= 0 && Type.isStringFilled(this.#sequenceSteps.at(index - 1)))
		{
			this.#currentStepId = this.#sequenceSteps.at(index - 1);
			this.show();
		}
	}

	show(): void
	{
		Dom.clean(this.#wrapper);

		const buttons = this.#currentStepButtons;
		if (Type.isArrayFilled(this.#currentStepButtons))
		{
			Dom.show(this.#wrapper);
			buttons.forEach((button) => {
				button.renderTo(this.#wrapper);
			});
		}
		else
		{
			Dom.hide(this.#wrapper);
		}
	}

	get #currentStepButtons(): Array<Button>
	{
		return this.#buttons.has(this.#currentStepId) ? this.#buttons.get(this.#currentStepId) : [];
	}

	resolveEnableState(enable: Object<ButtonId, boolean>): void
	{
		this.#currentStepButtons.forEach((button) => {
			if (Type.isBoolean(enable[button.getId()]))
			{
				button.setDisabled(!enable[button.getId()]);
			}
		});
	}

	resolveWaitingState(waiting: Object<ButtonId, boolean>): void
	{
		this.#currentStepButtons.forEach((button) => {
			if (Type.isBoolean(waiting[button.getId()]))
			{
				button.setWaiting(waiting[button.getId()]);
			}
		});
	}
}
