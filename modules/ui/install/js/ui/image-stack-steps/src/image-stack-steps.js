import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { BitrixVue, VueCreateAppResult } from 'ui.vue3';

import type { ImageStackStepsOptions, StepType, FooterType, HeaderType, StackType } from './image-stack-steps-options';
import { validateStep } from './helpers/validate-helpers';

import { Application } from './components/application';

import 'ui.design-tokens';

import { headerTypeEnum, footerTypeEnum, imageTypeEnum, stackStatusEnum } from './image-stack-steps-options';
export {
	headerTypeEnum,
	imageTypeEnum,
	footerTypeEnum,
	stackStatusEnum,
};

export type {
	StepType,
	HeaderType,
	StackType,
	FooterType,
};

export class ImageStackSteps
{
	#steps: Array<StepType> = [];
	#application: VueCreateAppResult;

	constructor(options: ImageStackStepsOptions)
	{
		if (!Type.isArrayFilled(options.steps))
		{
			throw new TypeError('options.steps must be filled array');
		}

		this.#setSteps(options.steps);
		if (!Type.isArrayFilled(this.#steps))
		{
			throw new TypeError('options.steps must be contain correct steps data, see warnings');
		}

		this.#initApplication();
	}

	#setSteps(stepsData: Array<StepType>)
	{
		this.#steps = [];

		stepsData.forEach((step) => {
			if (validateStep(step))
			{
				this.#steps.push(step);
			}
			else
			{
				// eslint-disable-next-line no-console
				console.warn('UI.Image-Stack-Steps: Step was skipped due to incorrect stepData', step);
			}
		});
	}

	#initApplication()
	{
		// eslint-disable-next-line unicorn/no-this-assignment
		const context = this;

		this.#application = BitrixVue.createApp(
			{
				name: 'ui-image-stack-steps',
				components: {
					Application,
				},
				props: {
					steps: Array,
				},
				created()
				{
					this.$app = context;
				},
				template: `
					<Application
						:initialSteps="steps"
					></Application>
				`,
			},
			{
				steps: this.#steps,
			},
		);
	}

	renderTo(node: HTMLElement)
	{
		this.#application.mount(node);
	}

	getSteps(): Array<StepType>
	{
		return this.#steps.map((step) => ({ ...step }));
	}

	addStep(stepData: StepType): boolean
	{
		if (validateStep(stepData))
		{
			this.#steps.push(stepData);

			EventEmitter.emit(this, 'UI.ImageStackSteps.onUpdateSteps');

			return true;
		}

		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: Step was skipped due to incorrect stepData', stepData);

		return false;
	}

	updateStep(stepData: StepType, stepId: string): boolean
	{
		const index = this.#steps.findIndex((step) => step.id === stepId);
		if (index === -1)
		{
			// eslint-disable-next-line no-console
			console.warn(`UI.Image-Stack-Steps: Step with id ${stepId} not find`);

			return false;
		}

		const oldStepData = this.#steps[index];

		const modifiedData = Object.assign(oldStepData, stepData);
		modifiedData.id = oldStepData.id;

		if (validateStep(modifiedData))
		{
			this.#steps[index] = modifiedData;

			EventEmitter.emit(this, 'UI.ImageStackSteps.onUpdateSteps');

			return true;
		}

		// eslint-disable-next-line no-console
		console.warn('UI.Image-Stack-Steps: Step was not updated due to incorrect stepData', modifiedData);

		return false;
	}

	deleteStep(stepId: string): boolean
	{
		const index = this.#steps.findIndex((step) => step.id === stepId);
		if (index === -1)
		{
			return true;
		}

		this.#steps.splice(index, 1);

		EventEmitter.emit(this, 'UI.ImageStackSteps.onUpdateSteps');

		return true;
	}

	destroy()
	{
		this.#application.unmount();
		this.#application = null;
		this.#steps = null;
	}
}
