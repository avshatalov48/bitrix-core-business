import {Tag, Text, Type, Dom, Event, Loc} from 'main.core';
import RobotLog from "../tracker/robot-log";
import Automation from "../automation";
import {Robot, TrackingEntry, TrackingEntryBuilder, ViewMode} from 'bizproc.automation';
import {Helper} from "../helper";
import {BaseEvent} from "main.core.events";
import TriggerLog from "../tracker/trigger-log";
import 'ui.fonts.robotomono';
import {TrackingType} from "../tracker/types";

export default class AutomationLogView
{
	#debuggerInstance: Automation = null;
	#workflowId: string;

	#activityRenderer = {};

	#documentStatus: string = '';
	#categoryName: string = '';
	#statusSettings: object;

	#node: HTMLDivElement = null;
	#index: number = 0;
	#trackId: number = 0;

	#poolTrack: Array<TrackingEntry> = [];
	#poolWorkflowRobots: object = {};
	#isRendering: boolean = true;

	static #NUMBER_OF_LINES_TO_SHOW_IN_PIXELS: number = 50; // 3 lines
	#shouldScrollToBottom: boolean = false;
	#shouldLoadPreviousLog: boolean = false;

	constructor(debuggerInstance: Automation)
	{
		this.#debuggerInstance = debuggerInstance;
		this.#workflowId = this.debugger.workflowId;

		const template = this.debugger.getTemplate();
		this.initializeWorkflowRobotsRenderer(template ? template['ROBOTS'] : []);

		if (this.debugger.session.isActive())
		{
			this.debugger.subscribe('onWorkflowTrackAdded', this.onTrackAdded.bind(this));
			this.debugger.getMainView().subscribe('onChangeTab', this.#onChangeTab.bind(this));

			this.debugger.session.subscribe('onFinished', this.#onSessionFinished.bind(this));
		}
	}

	get debugger(): Automation | null
	{
		return this.#debuggerInstance;
	}

	get index(): number
	{
		return this.#index;
	}

	get logNode(): HTMLDivElement | null
	{
		if (!this.#node)
		{
			this.#node = Tag.render`<div data-role="log" class="bizproc-debugger-tab__log"></div>`;
		}

		return this.#node;
	}

	initializeWorkflowRobotsRenderer(workflowRobots: Array = [], workflowId: string = null)
	{
		if (!workflowId && ! this.#workflowId)
		{
			return;
		}

		if (Type.isUndefined(this.#activityRenderer[workflowId ?? this.#workflowId]))
		{
			this.#activityRenderer[workflowId ?? this.#workflowId] = {};
		}

		if (Type.isArrayFilled(workflowRobots))
		{
			let prevRobotTitle = null;

			for (let i = 0; i < workflowRobots.length; ++i)
			{
				const robot = new Robot({});
				robot.init(workflowRobots[i], ViewMode.none());

				const conditionNames = [];
				if (Type.isPlainObject(robot.data.Condition))
				{
					conditionNames.push(robot.data.Condition['activityNames']['Activity']);
					conditionNames.push(robot.data.Condition['activityNames']['Branch1']);
					conditionNames.push(robot.data.Condition['activityNames']['Branch2']);
				}

				const robotLogger = new RobotLog(this, {
					name: robot.getId(),
					title: robot.getTitle(),
					delayName: robot.data.DelayName ?? null,
					conditionNames: conditionNames
				});

				if (robot.isExecuteAfterPrevious() && prevRobotTitle)
				{
					robotLogger.previousRobotTitle = prevRobotTitle;
				}

				prevRobotTitle = robot.getTitle();

				robotLogger.getActivitiesName().forEach((activityName) => {
					this.#activityRenderer[workflowId ?? this.#workflowId][activityName] = robotLogger;
				});
			}
		}
	}

	// region LOAD LOG

	shouldLoadPreviousLog(should: boolean): this
	{
		this.#shouldLoadPreviousLog = should;

		return this;
	}

	#loadPreviousLog(): Promise<this>
	{
		return new Promise((resolve) => {
			this.debugger.loadAllLog()
				.then(
					(data) => {
						this.#onAfterGetLog(data);

						resolve(this)
					},
					() => {resolve(this)}
				)
			;
		});
	}

	#loadWorkflowRobotsByWorkflowId(track)
	{
		this.debugger.loadRobotsByWorkflowId(track.workflowId)
			.then(
				(data) => {
					this.#poolWorkflowRobots[track.workflowId] = data.workflowRobots;
					this.initializeWorkflowRobotsRenderer(this.#poolWorkflowRobots[track.workflowId], track.workflowId);
					this.startRendering();
				},
				() => {
					this.#poolWorkflowRobots[track.workflowId] = [];
					console.info('session has no workflowId from track:', track);
					this.startRendering();
				}
			)
		;
	}

	setPreviousLog(data = {logs: [], workflowRobots: {}}): this
	{
		this.#onAfterGetLog(data);

		return this;
	}

	#onAfterGetLog(data)
	{
		const logFromDB = [];
		const builder = new TrackingEntryBuilder();

		if (Type.isArrayFilled(data['logs']))
		{
			data['logs'].forEach((item) => {
				logFromDB.push(builder.setLogEntry(item).build());
			});
		}

		this.#poolTrack = logFromDB.concat(this.#poolTrack);
		this.#poolWorkflowRobots = Object.assign(data['workflowRobots'], this.#poolWorkflowRobots);
	}

	// endregion

	// region RENDER LOG

	render(): HTMLDivElement
	{
		if (this.logNode.children.length <= 0)
		{
			this.#isRendering = true;

			if (this.#shouldLoadPreviousLog)
			{
				this.#loadPreviousLog().then(() => {
					this.startRendering();
				});
			}
			else
			{
				this.startRendering();
			}
		}

		return this.logNode;
	}

	renderTo(element: HTMLElement): this
	{
		Dom.append(this.logNode, element);
		this.#isRendering = true;
		this.startRendering();

		return this;
	}

	startRendering()
	{
		const track = this.#poolTrack.shift();
		if (Type.isUndefined(track))
		{
			this.#isRendering = false;

			return;
		}

		if (this.#workflowId !== track.workflowId)
		{
			if (Type.isUndefined(this.#activityRenderer[track.workflowId]))
			{
				if (Type.isUndefined(this.#poolWorkflowRobots[track.workflowId]))
				{
					this.#loadWorkflowRobotsByWorkflowId(track);

					this.renderTrack(track);
					this.#workflowId = track.workflowId;

					return;
				}

				this.initializeWorkflowRobotsRenderer(this.#poolWorkflowRobots[track.workflowId], track.workflowId);
			}
		}

		this.renderTrack(track);
		this.startRendering();
	}

	#renderStartDebugLog(track: TrackingEntry)
	{
		this.#renderStartedDate(track);
		this.#renderLegend(track);
	}

	#renderStartedDate(track: TrackingEntry)
	{
		const startedDate = Helper.toDate(track.datetime);

		const dateNode = Tag.render`
			<div class="bizproc-debugger-automation__log--date">
				<div class="bizproc-debugger-automation__log--date-text">${Text.encode(Helper.formatDate('j F Y', startedDate))}</div>
			</div>
		`;

		Dom.append(dateNode, this.logNode);
	}

	#renderLegend(track: TrackingEntry)
	{
		const description = JSON.parse(track.note)['propertyValue'];

		// separator <div class="bizproc-debugger-automation__log-separator"></div>

		const descriptionNode = Tag.render`
			<div class="bizproc-debugger-automation__log-section">
				<div class="bizproc-debugger-automation__log-section--row">
					${this.renderIndex()}
					${AutomationLogView.renderTime(track.datetime)}
					<div>${Text.encode(description)}</div>
				</div>
			</div>
		`;

		Dom.append(descriptionNode, this.logNode);
	}

	renderIndex(): HTMLDivElement
	{
		this.#index++;

		return Tag.render`
			<div class="bizproc-debugger-automation__log--index" data-role="index">${String(this.#index).padStart(3, '0')}</div>
		`;
	}

	static renderTime(datetime: string): HTMLDivElement
	{
		datetime = Helper.toDate(datetime);

		return Tag.render`
			<div class="bizproc-debugger-automation__log--time">
				[${Text.encode(Helper.formatDate('H:i:s', datetime))}]
			</div>
		`;
	}

	// endregion

	// region status log

	renderStatusChange(track: TrackingEntry)
	{
		const parsedTrackNote = JSON.parse(track.note);
		if (!Type.isStringFilled(this.#documentStatus))
		{
			this.#documentStatus = parsedTrackNote['STATUS_ID'];
			this.#statusSettings = parsedTrackNote;

			return;
		}

		const sourceStage = this.getStatusSettings(this.#documentStatus);
		const destinationStage = parsedTrackNote;

		const node = Tag.render`
			<div class="bizproc-debugger-automation__log-section">
				<div class="bizproc-debugger-automation__log-section--row">
					${this.renderIndex()}
					${AutomationLogView.renderTime(track.datetime)}
					<div class="bizproc-debugger-automation__status--change-info">
						<div class="bizproc-debugger-automation__status --log-status ${Helper.getBgColorAdditionalClass(sourceStage['COLOR'])}" title="${Text.encode(sourceStage['NAME'])}"> 
							<div class="bizproc-debugger-automation__status--title">${Text.encode(sourceStage['NAME'])}</div>
							<div class="bizproc-debugger-automation__status--bg" style="background-color: ${sourceStage['COLOR']}; border-color: ${sourceStage['COLOR']};">
								<span class="bizproc-debugger-automation__status--bg-arrow"></span>
							</div>
						</div>
						<div class="bizproc-debugger-automation__status--robot-change-arrow"></div>
						<div class="bizproc-debugger-automation__status --log-status ${Helper.getBgColorAdditionalClass(destinationStage['COLOR'])}" title="${Text.encode(destinationStage['NAME'])}"> 
							<div class="bizproc-debugger-automation__status--title">${Text.encode(destinationStage['NAME'])}</div>
							<div class="bizproc-debugger-automation__status--bg" style="background-color: ${destinationStage.COLOR}; border-color: ${destinationStage.COLOR};">
								<span class="bizproc-debugger-automation__status--bg-arrow"></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		`;

		Dom.append(node, this.logNode);

		this.#documentStatus = parsedTrackNote['STATUS_ID'];
		this.#statusSettings = parsedTrackNote;
	}

	getStatusSettings(): object
	{
		if (Type.isUndefined(this.#statusSettings))
		{
			return {
				NAME: '',
				COLOR: 'AEF2F9'
			};
		}

		return this.#statusSettings;
	}

	#renderCategoryChange(track: TrackingEntry)
	{
		const categoryName = JSON.parse(track.note)['propertyValue'];
		if (!Type.isStringFilled(this.#categoryName))
		{
			this.#categoryName = categoryName;

			return;
		}

		const descriptionNode = Tag.render`
			<div>
				<div class="bizproc-debugger-automation__log-separator"></div>
				<div class="bizproc-debugger-automation__log-section">
					<div class="bizproc-debugger-automation__log-section--row">
						${this.renderIndex()}
						${AutomationLogView.renderTime(track.datetime)}
						<div>
							${Text.encode(Loc.getMessage(
								'BIZPROC_JS_DEBUGGER_CATEGORY_CHANGE',
								{
									'#SOURCE_CATEGORY#': this.#categoryName,
									'#DESTINATION_CATEGORY#': categoryName
								}
							))}
						</div>
					</div>
				</div>
			</div>
		`;

		Dom.append(descriptionNode, this.logNode);

		this.#categoryName = categoryName;
	}

	// endregion

	// region TRACK
	onTrackAdded(event: BaseEvent)
	{
		const entryBuilder = new TrackingEntryBuilder();
		entryBuilder.setLogEntry(event.getData().row);

		this.addTrack(entryBuilder.build());
	}

	addTrack(track: TrackingEntry): void {
		if (!this.#isRendering)
		{
			return this.renderTrack(track);
		}

		this.#poolTrack.push(track);
	}

	renderTrack(track: TrackingEntry)
	{
		if ((track.id <= this.#trackId))
		{
			return;
		}

		if (!Object.keys(this.#activityRenderer[track.workflowId] ?? {}).includes(track.name))
		{
			if (track.name === 'SESSION_LEGEND')
			{
				this.#renderStartDebugLog(track);
				this.#trackId = track.id;
			}
			else if (track.name === 'STATUS_CHANGED')
			{
				this.renderStatusChange(track);
				this.#trackId = track.id;
			}
			else if (track.name === 'CATEGORY_CHANGED')
			{
				this.#renderCategoryChange(track);
				this.#trackId = track.id;
			}
			else if (track.name === 'TRIGGER_LOG')
			{
				(new TriggerLog(this)).addTrack(track).render();
				this.#trackId = track.id;
			}
			else if (track.name === 'Template' && track.type === TrackingType.ExecuteActivity)
			{
				if (Type.isUndefined(this.#poolWorkflowRobots[track.workflowId]) && this.#isRendering === false)
				{
					this.#isRendering = true;

					this.#loadWorkflowRobotsByWorkflowId(track);
				}
			}
			else if (track.name === 'Template' && track.type === TrackingType.CloseActivity)
			{
				this.#clearWorkflowRobots(track.workflowId);
			}

			return;
		}

		this.#activityRenderer[track.workflowId][track.name].renderTrack(track);
		this.#trackId = track.id;
	}
	//endregion

	// region ON CHANGE TAB: scrollToBottom, collapseInfoResults

	#onChangeTab(event: BaseEvent)
	{
		if (event.getData().tab === 'log')
		{
			this.collapseInfoResults();

			if (this.#shouldScrollToBottom)
			{
				this.#scrollToBottom();
				this.#shouldScrollToBottom = false; // scroll once
			}
		}
	}

	collapseInfoResults(node?: HTMLElement): this
	{
		if (!node)
		{
			node = this.logNode;
		}

		const infoResults = node.querySelectorAll('[data-role="info-result"]');
		infoResults.forEach((infoNode) => {
			if (infoNode.firstElementChild.clientHeight > this.constructor.#NUMBER_OF_LINES_TO_SHOW_IN_PIXELS)
			{
				const moreInfoNode = infoNode.parentNode.querySelector('[data-role="more-info-result"]');

				Event.bind(moreInfoNode, 'click', () => {
					Dom.style(infoNode, 'height', infoNode.firstElementChild.clientHeight + 'px');
					Dom.style(moreInfoNode, 'display', 'none');
				});

				Event.bind(infoNode, 'transitionend', () => {
					Dom.style(infoNode, 'height', null);
				});

				Dom.style(infoNode, 'height', this.constructor.#NUMBER_OF_LINES_TO_SHOW_IN_PIXELS + 'px');
				Dom.style(moreInfoNode, 'display', 'block');
			}
		});

		return this;
	}

	shouldScrollToBottom(should: boolean): this
	{
		this.#shouldScrollToBottom = should;

		return this;
	}

	#scrollToBottom()
	{
		this.logNode.parentNode.scrollTop = this.logNode.parentNode?.scrollHeight;
	}

	// endregion

	#clearWorkflowRobots(workflowId: string)
	{
		delete this.#poolWorkflowRobots[workflowId];
		delete this.#activityRenderer[workflowId];
	}

	#onSessionFinished()
	{
		this.debugger.unsubscribe('onWorkflowTrackAdded', this.onTrackAdded.bind(this));
		this.debugger.getMainView().unsubscribe('onChangeTab', this.#onChangeTab.bind(this));
	}
}