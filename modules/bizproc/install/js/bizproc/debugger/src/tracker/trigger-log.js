import AutomationLogView from "../views/automation-log";
import {TrackingEntry} from "bizproc.automation";
import {Dom, Loc, Tag, Text} from 'main.core';
import {Helper} from "../helper";
import {Operator} from 'bizproc.condition';

export default class TriggerLog
{
	#view: AutomationLogView;

	#condition: {} = {};
	#title: string = '';
	#track: TrackingEntry = null;

	constructor(view: AutomationLogView)
	{
		this.#view = view;
	}

	addTrack(track: TrackingEntry): TriggerLog
	{
		if (track.type === TrackingEntry.DEBUG_AUTOMATION_TYPE && track.name === 'TRIGGER_LOG')
		{
			this.#condition = JSON.parse(track.note);
			this.#title = track.title;
			this.#track = track;
		}

		return this;
	}

	render()
	{
		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section">
				${this.#renderTitle()}
				${this.#renderCondition()}
				<div class="bizproc-debugger-automation__log-section--row">
					${this.#view.renderIndex()}
					<div class="bizproc-debugger-automation__log-info--value --first">
						<span>
							${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_TRIGGER_FINISH'))}
						</span>
					</div>
				</div>
			</div>
		`;

		Dom.append(node, this.#view.logNode);
	}

	#renderTitle(): HTMLDivElement | string
	{
		const message = Loc.getMessage(
			'BIZPROC_JS_DEBUGGER_LOG_TRIGGER_TITLE',
			{'#TITLE#' : this.#title}
		);

		return Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				${AutomationLogView.renderTime(this.#track.datetime)}
				<div class="bizproc-debugger-automation__log-section--title">${Text.encode(message)}</div>
			</div>
		`;
	}

	#renderCondition(): HTMLDivElement | string
	{
		if (!this.#condition || Object.keys(this.#condition).length <= 0)
		{
			return '';
		}

		const note = this.#condition;

		const conditionNode = Tag.render`
			<div class="bizproc-debugger-automation__log-info">
				<div class="bizproc-debugger-automation__log-info--name">
					<span class="bizproc-debugger-automation__log-info--name-text">
						${Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION')}
					</span>
					<span>:</span> 
				</div> 
			</div>
		`;

		Object.keys(note).forEach((key) => {
			const colorCondition = (note[key]['result'] === 'Y') ? 'bizproc-debugger-automation__log-color-box --green' : '';

			const condition = note[key]['condition'];
			//const object = Helper.getFieldObjectLabel(condition['object']);
			const field = condition['field'];
			const fieldValue =
				(note[key]['fieldValue'])
					? String(note[key]['fieldValue'])
					: ''
			;

			const operator = Operator.getOperatorLabel(condition['operator']);
			const value = condition['value'];
			const joiner = Helper.getJoinerLabel(note[key]['joiner']);

			Dom.append(
				Tag.render`
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="${colorCondition}" >
							${(key === '0') ? '' : (Text.encode(joiner) + ' ')}
							${Text.encode(field) + ' '}
							${'[' + Text.encode(fieldValue) + '] '}
							${Text.encode(operator) + ' '}
							${Text.encode(value)}
						</span>
					</div>
				`,
				conditionNode
			);
		});

		return Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				${conditionNode}
			</div>
		`;
	}
}