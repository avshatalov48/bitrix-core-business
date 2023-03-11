import {Loc, Tag, Text, Type, Dom} from 'main.core';
import {DelayInterval, DelayIntervalSelector, TrackingEntry} from "bizproc.automation";
import AutomationLogView from "../views/automation-log";
import { Helper } from "../helper";
import { Operator } from 'bizproc.condition';

export type RobotInfo = {
	name: string,
	title: string,
	delayName: string | null;
	conditionNames: Array<string>
};

export default class RobotLog
{
	#robot: RobotInfo;
	#view: AutomationLogView;

	#currentNode: HTMLDivElement = null;
	#currentIndex: number = null;

	#isAfterPreviousRendered: boolean = false;
	#isPauseRendered: boolean = false;
	#isActivityBodyRendered: boolean = false;

	#prevRobotTitle: string = null;

	constructor(view: AutomationLogView, robotInfo: RobotInfo)
	{
		this.#view = view;
		this.#robot = robotInfo;
	}

	get name(): string
	{
		return this.#robot.name;
	}

	get title(): string
	{
		return this.#robot.title;
	}

	get delayName(): string | null
	{
		return this.#robot.delayName;
	}

	get conditionNames(): Array<string>
	{
		return this.#robot.conditionNames;
	}

	getActivitiesName(): Array<string>
	{
		let names = [];

		if (this.name)
		{
			names.push(this.name);
		}

		if (this.delayName)
		{
			names.push(this.delayName);
		}

		names = names.concat(this.conditionNames);

		return names;
	}

	set previousRobotTitle(title: string)
	{
		this.#prevRobotTitle = title;
	}

	renderTrack(track: TrackingEntry): void
	{
		const excludedTypes = [TrackingEntry.EXECUTE_ACTIVITY_TYPE, TrackingEntry.ATTACHED_ENTITY_TYPE];
		if (excludedTypes.includes(track.type))
		{
			return;
		}

		if (track.name === this.delayName)
		{
			return this.#renderPause(track);
		}

		if (this.conditionNames.includes(track.name))
		{
			if (this.#isPauseRendered === false)
			{
				const node = this.#getCurrentRobotNode(track);
				Dom.append(this.#renderDelayInterval(new DelayInterval()), node);
				this.#isPauseRendered = true;
			}

			return this.#renderCondition(track);
		}

		if (track.name === this.name)
		{
			if (this.#isPauseRendered === false)
			{
				const node = this.#getCurrentRobotNode(track);
				Dom.append(this.#renderDelayInterval(new DelayInterval()), node);
				this.#isPauseRendered = true;
			}

			return this.#renderActivity(track);
		}
	}

	#getCurrentRobotNode(track: TrackingEntry): HTMLDivElement
	{
		if (this.#view.index !== this.#currentIndex)
		{
			const node = Tag.render`
				<div class="bizproc-debugger-automation__log-section">
					${this.#renderRobotTitle(track.datetime)}
				</div>
			`;

			if (Type.isStringFilled(this.#prevRobotTitle) && this.#isAfterPreviousRendered === false)
			{
				Dom.append(this.#renderAfterPrevious(), node);
			}

			Dom.append(node, this.#view.logNode);

			this.#currentNode = node;
		}

		return this.#currentNode;
	}

	#renderRobotTitle(time: string): HTMLDivElement
	{
		const message = Loc.getMessage(
			'BIZPROC_JS_DEBUGGER_LOG_TITLE',
			{'#TITLE#' : this.title}
		);

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				${AutomationLogView.renderTime(time)}
				<div class="bizproc-debugger-automation__log-section--title">${Text.encode(message)}</div>
			</div>
		`;

		this.#currentIndex = this.#view.index;

		return node;
	}

	#renderAfterPrevious(): HTMLDivElement
	{
		this.#isAfterPreviousRendered = true;

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				<div class="bizproc-debugger-automation__log-info">
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text">
							${Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_AFTER_PREVIOUS')}
						</span>
						<span>:</span>
					</div>
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="bizproc-debugger-automation__log-color-box --blue">
							"${Text.encode(this.#prevRobotTitle)}"
						</span>
					</div>
				</div>
			</div>
		`;

		this.#currentIndex = this.#view.index;

		return node;
	}

	// region Pause
	#renderPause(track: TrackingEntry): void
	{
		this.#isPauseRendered = true;

		const excludedTypes = [TrackingEntry.CLOSE_ACTIVITY_TYPE];

		// ignore
		if (excludedTypes.includes(track.type))
		{
			return;
		}

		// delay Interval
		if (track.type === TrackingEntry.DEBUG_AUTOMATION_TYPE)
		{
			const node = this.#getCurrentRobotNode(track);
			const note = JSON.parse(track.note);

			return Dom.append(this.#renderDelayInterval(note), node);
		}

		const node = this.#getCurrentRobotNode(track);

		return Dom.append(this.#renderNote(track), node);
	}

	#renderDelayInterval(note ={}): HTMLDivElement
	{
		const delayInterval = new DelayInterval(note);
		let name = note.fieldName ?? (new DelayIntervalSelector()).getBasisField(delayInterval.basis, true).Name;
		name = name + ' [' + note.fieldValue + ']';

		const delay = delayInterval.format(
			Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_DELAY_INTERVAL_AT_ONCE'),
			[
				{
					SystemExpression: delayInterval.basis,
					Name: name
				}
			]
		);

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				<div class="bizproc-debugger-automation__log-info">
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text">
							${Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_DELAY_INTERVAL_RUN')}
						</span>
						<span>:</span>
					</div>
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="bizproc-debugger-automation__log-color-box --dark-blue">
							${Text.encode(delay)}
						</span>
					</div>
				</div>
			</div>
		`;

		this.#currentIndex = this.#view.index;

		return node;
	}
	// endregion

	// region Condition
	#renderCondition(track: TrackingEntry): void
	{
		const excludedTypes = [TrackingEntry.CLOSE_ACTIVITY_TYPE];

		// ignore
		if (excludedTypes.includes(track.type))
		{
			return;
		}

		if (track.type === TrackingEntry.DEBUG_AUTOMATION_TYPE)
		{
			const node = this.#getCurrentRobotNode(track);

			return Dom.append(this.#renderConditions(track), node);
		}

		const node = this.#getCurrentRobotNode(track);

		return Dom.append(this.#renderNote(track), node);
	}

	#renderConditions(track: TrackingEntry): HTMLDivElement
	{
		const note = JSON.parse(track.note);

		const conditionNode = Tag.render`
			<div class="bizproc-debugger-automation__log-info">
				<div class="bizproc-debugger-automation__log-info--name">
					<span class="bizproc-debugger-automation__log-info--name-text">
						${
							note.result === 'Y'
								? Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION')
								: Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION_FALSE')
						}
					</span>
					<span>:</span> 
				</div> 
			</div>
		`;

		Object.keys(note).forEach((key) => {
			if (key === 'result')
			{
				return;
			}

			let colorCondition = '';
			if (note.result === note[key]['result'])
			{
				colorCondition =
					(note.result === 'Y')
						? 'bizproc-debugger-automation__log-color-box --green'
						: 'bizproc-debugger-automation__log-color-box --orange'
				;
			}

			const condition = note[key]['condition'];
			const field = condition['field'];
			const fieldValue =
				(note[key]['fieldValue'])
					? String(note[key]['fieldValue'])
					: ''
			;
			const operator = Operator.getOperatorLabel(condition['operator'])
			const value = condition['value'];
			const joiner = Helper.getJoinerLabel(note[key]['joiner']);

			Dom.append(
				Tag.render`
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="${colorCondition}" >
							${(key === '0') ? '' : Text.encode(joiner) + ' '}
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

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				${conditionNode}
			</div>
		`;

		this.#currentIndex = this.#view.index;

		return node;
	}
	// endregion

	// region Activity
	#renderActivity(track: TrackingEntry): void
	{
		if (track.type === TrackingEntry.CLOSE_ACTIVITY_TYPE)
		{
			if (this.#isActivityBodyRendered === false)
			{
				const node = this.#getCurrentRobotNode(track);

				return Dom.append(this.#renderActivityFinish(), node);
			}

			return;
		}

		// fields
		this.#isActivityBodyRendered = true;
		const node = this.#getCurrentRobotNode(track);

		const renderedNote = this.#renderNote(track);

		Dom.append(renderedNote, node);

		this.#view.collapseInfoResults(renderedNote);
	}

	#renderActivityFinish(): HTMLDivElement
	{
		// tracking-track-2
		return Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				<div class="bizproc-debugger-automation-log-section-robot-activity">
					${Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_FINISH_WITHOUT_SETTINGS')}
				</div>
			</div>
		`;
	}
	// endregion

	#renderNote(track: TrackingEntry): HTMLDivElement
	{
		if ([TrackingEntry.DEBUG_AUTOMATION_TYPE, TrackingEntry.DEBUG_ACTIVITY_TYPE].includes(track.type))
		{
			return this.#renderDebugNote(track);
		}

		if ([TrackingEntry.DEBUG_LINK_TYPE].includes(track.type))
		{
			return this.#renderDebugLink(track);
		}

		const colorBox =
			[TrackingEntry.CANCEL_ACTIVITY_TYPE, TrackingEntry.FAULT_ACTIVITY_TYPE, TrackingEntry.ERROR_ACTIVITY_TYPE].includes(track.type)
				? 'bizproc-debugger-automation__log-color-box --red'
				: ''
		;

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				<div class="bizproc-debugger-automation__log-info--value --first">
					<span class="${colorBox}">
						${Text.encode(track.note).replace(/([^>])\n/g, '$1<br>')}
					</span>
				</div>
			</div>
		`;

		this.#currentIndex = this.#view.index;

		return node;
	}

	#renderDebugNote(track: TrackingEntry): HTMLDivElement
	{
		const note = JSON.parse(track.note);

		const infoNode = Tag.render`<div class="bizproc-debugger-automation__log-info"></div>`;

		if (note['propertyName'])
		{
			Dom.append(
				Tag.render`
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text" title="${Text.encode(note['propertyName'])}">
							${Text.encode(note['propertyName'])}
						</span>
						<span>:</span>
					</div>
				`,
				infoNode
			);
		}

		Dom.append(
			Tag.render`
				<div class="bizproc-debugger-automation__log-info--value ${note['propertyName'] ? '' : '--first'}">
					<div class="bizproc-debugger-automation__log--variable-height" data-role="info-result">
						<div>
							${note['propertyValue'] ? Text.encode(note['propertyValue']).replace(/([^>])\n/g, '$1<br>') : ''}
						</div>
					</div>
					<div data-role="more-info-result" style="display:none;">
						<span class="bizproc-debugger-automation__log-info--more">
							${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_MORE_INFORMATION'))}
						</span>
					</div>
				</div>
			`,
			infoNode
		);

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				${infoNode}
			</div>
		`;

		this.#currentIndex = this.#view.index;

		return node;
	}

	#renderDebugLink(track: TrackingEntry): HTMLDivElement
	{
		const note = JSON.parse(track.note);
		const infoNode = Tag.render`<div class="bizproc-debugger-automation__log-info"></div>`;
		const label = note['propertyLinkName'] || note['propertyValue'];
		const link = note['propertyValue'];

		if (note['propertyName'])
		{
			Dom.append(
				Tag.render`
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text" title="${Text.encode(note['propertyName'])}">
							${Text.encode(note['propertyName'])}
						</span>
						<span>:</span>
					</div>
				`,
				infoNode
			);
		}

		Dom.append(
			Tag.render`
				<div class="bizproc-debugger-automation__log-info--value ${note['propertyName'] ? '' : '--first'}">
					<div class="bizproc-debugger-automation__log--variable-height" data-role="info-result">
						<a href="${Text.encode(link)}" target="_blank">
							${label}
						</a>
					</div>
				</div>
			`,
			infoNode
		);

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section--row">
				${this.#view.renderIndex()}
				${infoNode}
			</div>
		`;

		this.#currentIndex = this.#view.index;

		return node;
	}
}