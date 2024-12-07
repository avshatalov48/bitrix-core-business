import { Loc, Tag, Type, Text } from 'main.core';
import { DateTimeFormat } from 'main.date';
import { Timeline } from 'bizproc.workflow.timeline';

import 'ui.design-tokens';
import 'ui.icons';
import 'ui.icon-set.main';

import './css/style.css';

export type SummaryData = {
	workflowId: string,
	time: ?number,
	workflowIsCompleted: boolean,
};

export class Summary
{
	#isFinal: boolean = false;
	#workflowId: string;

	constructor(props: SummaryData = {})
	{
		if (!Type.isStringFilled(props.workflowId))
		{
			throw new TypeError('workflowId must be filled string');
		}
		this.#workflowId = props.workflowId;

		if (Type.isBoolean(props.workflowIsCompleted))
		{
			this.#isFinal = props.workflowIsCompleted;
		}

		this.#calculateDurationTexts(props.time);
	}

	#calculateDurationTexts(time)
	{
		const duration = (
			Type.isNumber(time)
				? DateTimeFormat.format(
					[['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']],
					0,
					time,
				)
				: null
		);

		this.durationTexts = { nameBefore: '', value: '', nameAfter: '' };

		if (duration)
		{
			const pattern = /\d+/;
			const match = duration.match(pattern);
			if (match)
			{
				this.durationTexts.value = String(match[0]);

				const index = duration.indexOf(this.durationTexts.value);
				if (index !== -1)
				{
					this.durationTexts.nameBefore = duration.slice(0, index).trim();
					this.durationTexts.nameAfter = duration.slice(index + this.durationTexts.value.length).trim();
				}
			}
			else
			{
				this.durationTexts.nameAfter = duration;
			}
		}
	}

	render(): HTMLElement
	{
		const title = Text.encode(Loc.getMessage(
			this.#isFinal
				? 'BIZPROC_JS_WORKFLOW_FACES_SUMMARY_TITLE_FINAL'
				: 'BIZPROC_JS_WORKFLOW_FACES_SUMMARY_TITLE'
			,
		));
		const footerTitle = Text.encode(Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_SUMMARY_TIMELINE_MSGVER_1'));

		return Tag.render`
			<div class="bp-workflow-faces-summary-item">
				<div class="bp-workflow-faces-summary-name">
					<div class="bp-workflow-faces-summary__text-area" title="${title}">${title}</div>
				</div>
				${this.#renderContent()}
				<div class="bp-workflow-faces-summary__duration" onclick="${this.#openTimeline.bind(this)}">
					<div class="bp-workflow-faces-summary__text-area" title="${footerTitle}">${footerTitle}</div>
				</div>
			</div>
		`;
	}

	#renderContent(): HTMLElement
	{
		if (this.#isFinal)
		{
			return Tag.render`
				<div class="bp-workflow-faces-summary__summary">
					<div class="bp-workflow-faces-summary__summary-name">${Text.encode(this.durationTexts.nameBefore)}</div>
					<div class="bp-workflow-faces-summary__summary-value">${Text.encode(this.durationTexts.value)}</div>
					<div class="bp-workflow-faces-summary__summary-name">${Text.encode(this.durationTexts.nameAfter)}</div>
				</div>
			`;
		}

		return Tag.render`
			<div class="bp-workflow-faces-summary__icon-wrapper">
				<div class="ui-icon-set --clock-2 bp-workflow-faces-summary__icon"></div>
			</div>
		`;
	}

	#openTimeline(event)
	{
		event.stopPropagation();
		event.preventDefault();
		Timeline.open({ workflowId: this.#workflowId });
	}
}
