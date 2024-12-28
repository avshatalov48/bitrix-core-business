import { Tag, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';

import type { StepData } from '../types/step-data';

import '../../css/style.css';

export class Step extends EventEmitter
{
	name: string;

	constructor(config: StepData)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Component.WorkflowSingleStart.Step');

		if (this.constructor === Step)
		{
			throw new Error('Object of Abstract Class cannot be created');
		}

		this.name = config.name;
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__content">
				${this.renderHead()}
				${this.renderBody()}
				${this.renderFooter()}
			</div>
		`;
	}

	renderHead(): ?HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__content-head">
				<div class="bizproc__ws_start__content-title">
					${Text.encode(this.name)}
				</div>
			</div>
		`;
	}

	renderBody(): HTMLElement
	{
		throw new Error('Abstract Method has no implementation');
	}

	renderFooter(): ?HTMLElement
	{
		return null;
	}

	isNextEnabled(): boolean
	{
		return true;
	}

	onBeforeNextStep(): Promise
	{
		return Promise.resolve();
	}

	isBackEnabled(): boolean
	{
		return true;
	}

	onChangeStepAvailability()
	{
		this.emit('onChangeStepAvailability');
	}

	onAfterRender(): void
	{}

	canExit(): boolean
	{
		return true;
	}
}
