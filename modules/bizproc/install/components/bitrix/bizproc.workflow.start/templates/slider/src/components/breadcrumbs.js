import { Tag, Text, Type, Dom } from 'main.core';

import '../css/components/breadcrumbs.css';

type StepId = string;

export type BreadcrumbsData = {
	items: Array<BreadcrumbsItemData>
};

export type BreadcrumbsItemData = {
	id: string,
	text: string,
	active: boolean,
};

export class Breadcrumbs
{
	#items: Map<StepId, BreadcrumbsItemData> = new Map();
	#itemsNode: Map<StepId, HTMLElement> = new Map();

	#sequenceSteps: [] = [];
	#currentStepId: ?StepId = null;

	constructor(config: BreadcrumbsData = {})
	{
		if (!Type.isArrayFilled(config.items))
		{
			throw new TypeError('BX.Bizproc.Workflow.SingleStart.Breadcrumbs: items must be filled array');
		}

		config.items.forEach((item) => {
			this.#items.set(item.id, item);
			this.#sequenceSteps.push(item.id);
			if (item.active)
			{
				this.#currentStepId = item.id;
			}
		});

		if (!Type.isStringFilled(this.#currentStepId) && Type.isStringFilled(this.#sequenceSteps.at(0)))
		{
			this.#currentStepId = this.#sequenceSteps.at(0);
		}
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc__ws_start__breadcrumbs">
				${[...this.#items.entries()]
					.map(([key, item]) => this.#renderItem(item, key))
				}
			</div>
		`;
	}

	#renderItem(item: BreadcrumbsItemData, stepId: StepId): HTMLElement
	{
		if (!this.#itemsNode.has(stepId))
		{
			this.#itemsNode.set(
				stepId,
				Tag.render`
					<div class="bizproc__ws_start__breadcrumbs-item${item.active ? ' --active' : ''}">
						<span>${Text.encode(item.text)}</span>
						<span class="ui-icon-set --chevron-right"></span>
					</div>
				`,
			);
		}

		return this.#itemsNode.get(stepId);
	}

	next()
	{
		if (this.#currentStepId)
		{
			const index = this.#sequenceSteps.indexOf(this.#currentStepId);
			if (index !== -1 && Type.isStringFilled(this.#sequenceSteps.at(index + 1)))
			{
				this.#markNotActive(this.#currentStepId);
				this.#markComplete(this.#currentStepId);
				this.#currentStepId = this.#sequenceSteps.at(index + 1);
				this.#markActive(this.#currentStepId);
			}
		}
	}

	back()
	{
		if (this.#currentStepId)
		{
			const index = this.#sequenceSteps.indexOf(this.#currentStepId);
			if (index !== -1 && index - 1 >= 0 && Type.isStringFilled(this.#sequenceSteps.at(index - 1)))
			{
				this.#markNotActive(this.#currentStepId);
				this.#currentStepId = this.#sequenceSteps.at(index - 1);
				this.#markNotComplete(this.#currentStepId);
				this.#markActive(this.#currentStepId);
			}
		}
	}

	#markNotActive(stepId: StepId)
	{
		if (this.#items.has(stepId))
		{
			this.#items.get(stepId).active = false;
			Dom.removeClass(this.#itemsNode.get(stepId), '--active');
		}
	}

	#markActive(stepId: StepId)
	{
		if (this.#items.has(stepId))
		{
			this.#items.get(stepId).active = true;
			Dom.addClass(this.#itemsNode.get(stepId), '--active');
		}
	}

	#markComplete(stepId: StepId)
	{
		if (this.#items.has(stepId))
		{
			Dom.addClass(this.#itemsNode.get(stepId), '--complete');
		}
	}

	#markNotComplete(stepId: StepId)
	{
		if (this.#items.has(stepId))
		{
			Dom.removeClass(this.#itemsNode.get(stepId), '--complete');
		}
	}
}
