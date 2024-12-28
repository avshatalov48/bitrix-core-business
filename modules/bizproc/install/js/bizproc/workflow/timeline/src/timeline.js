// eslint-disable-next-line max-classes-per-file
import { ajax, Type, Tag, Text, Dom, Runtime, Uri, Loc } from 'main.core';
import { DocumentId } from 'bizproc.document';
import { Timestamp, UserId } from 'bizproc.types';
import 'ui.icons.b24';
import 'ui.hint';
import { TextCrop } from 'ui.textcrop';
import { Popup, Menu } from 'main.popup';
import { TaskStatus, TaskUserData, TaskData } from 'bizproc.task';
import { DateTimeFormat } from 'main.date';
import { ErrorsView } from './views/errors-view';

import { TimelineTask } from './timeline-task';

import './css/style.css';
import { TimelineTaskView } from './views/timeline-task-view';

export type TimelineUserData = {
	id: UserId,
	login: string,
	name: string,
	lastName: string,
	secondName: string,
	fullName: string,
	workPosition: string,
	link: string,
	avatarSize100: string,
	status: number,
};

export type TimelineStatsData = {
	averageDuration: ?number,
	efficiency: string,
};

export type TimelineData = {
	document: DocumentId,
	isWorkflowRunning: boolean,
	timeToStart: ?Timestamp,
	executionTime: ?Timestamp,
	started: ?Timestamp,
	startedBy: UserId,
	tasks: TimelineTask[],
	users: Map<UserId, TimelineUserData>,
	userStatuses: Map<UserId, TaskStatus>,
	stats: TimelineStatsData,
	biMenu?: Array<BiMenuItem>,
};

type BiMenuItem = {
	ID: string,
	TEXT: string,
	URL: string,
}

export class DurationFormatter
{
	static #limits = [
		[3600 * 24 * 365, 'Ydiff'],
		[3600 * 24 * 31, 'mdiff'],
		[3600 * 24, 'ddiff'],
		[3600, 'Hdiff'],
		[60, 'idiff'],
	];

	static #getFormatString(seconds: number): string
	{
		for (const limit of this.#limits)
		{
			if (seconds >= limit[0])
			{
				return limit[1];
			}
		}

		return 'sdiff';
	}

	static #getMultiplierByFormat(format: string): number
	{
		for (const limit of this.#limits)
		{
			if (format === limit[1])
			{
				return limit[0];
			}
		}

		return 0;
	}

	static formatTimestamp(timestamp: number): string
	{
		return DateTimeFormat.format(
			this.#getFormatString(Date.now() / 1000 - timestamp),
			timestamp,
		);
	}

	static formatTimeInterval(interval: ?number, values: number = 1): string
	{
		if (Type.isNil(interval))
		{
			return Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME_UNKNOWN');
		}

		if (interval === 0)
		{
			return Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_ZERO_SECOND_INTERVAL');
		}

		let result = '';
		let remainder = interval;

		for (let i = 0; i < values; i++)
		{
			const format = this.#getFormatString(remainder);
			// ignore seconds if we already have result
			if (result.length > 0 && format === 'sdiff')
			{
				return result;
			}
			const multiplier = this.#getMultiplierByFormat(format);
			result += DateTimeFormat.format(format, 0, remainder);
			result += ' ';
			if (multiplier > 0)
			{
				remainder %= multiplier;
				if (remainder === 0)
				{
					return result;
				}
			}
		}

		return result;
	}

	static formatDate(timestamp: number, format: string, formatShort: ?string): string
	{
		if (formatShort && (DateTimeFormat.format('Y', timestamp) === DateTimeFormat.format('Y', Date.now() / 1000)))
		{
			return DateTimeFormat.format(formatShort, timestamp);
		}

		return DateTimeFormat.format(format, timestamp);
	}
}

export class Timeline
{
	#workflowId: string;
	// #taskId: number;
	#data: ?TimelineData;
	#isLoaded: boolean;
	#errors: Array<{ message: string }> = [];
	#container: HTMLDivElement;
	#biPopup: HTMLDivElement;
	#dateFormat: string;
	#dateFormatShort: string;

	constructor(
		options: { workflowId: string, taskId?: number, },
		config: { dateFormat: ?string, dateFormatShort: ?string, timeFormat: ?string, },
	)
	{
		this.#container = this.#renderContainer();
		setTimeout(() => {
			this.#textCrop();
		}, 500);
		if (Type.isPlainObject(options))
		{
			this.#workflowId = options.workflowId;
			// this.#taskId = options.taskId;
			this.#isLoaded = false;

			this.#loadTimeline();
		}

		if (Type.isPlainObject(config))
		{
			this.#dateFormat = `${config.dateFormat || 'j F Y'} ${config.timeFormat || 'H:i'}`;
			this.#dateFormatShort = `${config.dateFormatShort || 'j F'} ${config.timeFormat || 'H:i'}`;
		}
	}

	static open(options: { workflowId: string, taskId?: number }): void
	{
		Runtime
			.loadExtension('sidepanel')
			.then(() => {
				BX.SidePanel.Instance.open(
					Uri.addParam(
						'/bitrix/components/bitrix/bizproc.workflow.timeline.slider/index.php',
						Type.isPlainObject(options) ? options : {},
					),
					{
						width: 950,
						allowChangeHistory: false,
						cacheable: false,
						loader: '/bitrix/js/bizproc/workflow/timeline/img/skeleton.svg',
					},
				);
			})
			.catch((response) => console.error(response.errors))
		;
	}

	#loadTimeline(): void
	{
		ajax.runAction('bizproc.workflow.getTimeline', {
			data: { workflowId: this.#workflowId },
		}).then((response) => {
			this.#setDataFromResponse(response);
			this.#isLoaded = true;
			this.render();
		}).catch((response) => {
			this.#setDataFromResponse(response);
			this.#isLoaded = true;
			this.render();
		});
	}

	#setDataFromResponse(response): void
	{
		if (Type.isPlainObject(response))
		{
			const getString = (value, defaultValue = '') => (Type.isString(value) ? value : defaultValue);
			const getArray = (value, defaultValue = []) => (Type.isArray(value) ? value : defaultValue);
			const getBool = (value, defaultValue = false) => (Type.isBoolean(value) ? value : defaultValue);
			const getInteger = (value, defaultValue = 0) => (Type.isInteger(value) ? value : defaultValue);

			if (Type.isPlainObject(response.data))
			{
				this.#data = {
					document: new DocumentId({
						documentId: getArray(response.data.documentType),
						entityName: getString(response.data.entityName),
						documentName: getString(response.data.documentName),
						documentUrl: getString(response.data.documentUrl),
						moduleName: getString(response.data.moduleName),
					}),
					isWorkflowRunning: getBool(response.data.isWorkflowRunning),
					timeToStart: getInteger(response.data.timeToStart, null),
					executionTime: getInteger(response.data.executionTime, null),
					started: getInteger(response.data.started, null),
					startedBy: getInteger(response.data.startedBy),
					tasks: getArray(response.data.tasks).map((taskData) => new TimelineTask(taskData)),
					users: new Map(),
					stats: {
						averageDuration: getInteger(response.data.stats.averageDuration, null),
						efficiency: getString(response.data.stats.efficiency),
					},
					biMenu: getArray(response.data.biMenu, null),
				};

				for (const user of getArray(response.data.users))
				{
					this.#data.users.set(Text.toInteger(user.id), user);
				}
			}
			this.#errors = getArray(response.errors);
		}
	}

	render(): HTMLElement
	{
		Dom.clean(this.#container);

		if (this.#hasErrors())
		{
			const errorsView = new ErrorsView({ errors: this.#errors });
			errorsView.renderTo(this.#container);

			return this.#container;
		}

		if (!this.#isLoaded)
		{
			Dom.append(this.#renderLoadingStub(), this.#container);
		}

		if (Type.isPlainObject(this.#data))
		{
			Dom.replace(this.#container, this.#renderContainer());
			this.#createEfficiencyPopup().show();
			if (this.#data.biMenu)
			{
				this.showBiMenus(this.#data.biMenu);
			}
		}

		return this.#container;
	}

	#renderItemTitle(title, iconClass, iconText, crop): HTMLElement
	{
		const iconClassValue = Type.isString(iconClass) ? (` ${iconClass}`) : '';
		const iconTextValue = Type.isString(iconText) ? iconText : '';
		const cropValue = crop ? ' data-crop="crop"' : '';

		return Tag.render`
			<div>
				<span class="bizproc-workflow-timeline-icon${iconClassValue}">${iconTextValue}</span>
				<div class="bizproc-workflow-timeline-title"${cropValue}>${Text.encode(title)}</div>
			</div>
		`;
	}

	#renderSubject(subject): HTMLElement
	{
		return Tag.render`
			<div class="bizproc-workflow-timeline-subject">${Text.encode(subject)}</div>
		`;
	}

	#renderProceedTaskButton(task: TaskData): HTMLElement
	{
		const uri = (
			task.url || new Uri(`/company/personal/bizproc/${task.id}/`).toString()
		);

		return Tag.render`
			<div class="task-button --hidden">
				<a class="ui-btn ui-btn-xs ui-btn-primary ui-btn-round" href="${Text.encode(uri)}">
					${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BUTTON_PROCEED')}
				</a>
			</div>
		`;
	}

	#renderUser(userId: UserId, userData: ?TaskUserData = null, task: ?TaskData = null): HTMLElement
	{
		const user = this.#data.users.get(userId || userData.id);
		let userClass = '';
		let isWaiting = false;
		if (userData)
		{
			const status = new TaskStatus(userData.status);
			if (status.isYes() || status.isOk())
			{
				userClass = ' --voted-up';
			}

			if (status.isNo())
			{
				userClass = ' --voted-down';
			}

			if (status.isWaiting())
			{
				isWaiting = true;
			}
		}
		const position = Type.isString(user.workPosition)
			? (`<div class="bizproc-workflow-timeline-user-pos">${Text.encode(user.workPosition)}</div>`)
			: '';
		let avatar = '<i></i>';
		if (Type.isString(user.avatarSize100))
		{
			avatar = `<i style="background-image: url('${encodeURI(user.avatarSize100)}')"></i>`;
		}
		const button = (task?.id && isWaiting) ? this.#renderProceedTaskButton(task) : '';

		return Tag.render`
			<div class="bizproc-workflow-timeline-user${userClass}">
				<div class="bizproc-workflow-timeline-userlogo ui-icon ui-icon-common-user">
					${avatar}
				</div>
				<div class="bizproc-workflow-timeline-user-block">
					<a class="bizproc-workflow-timeline-link" href="${user.link}">${Text.encode(user.fullName)}</a>
					${position}
				</div>
				${button}
			</div>
		`;
	}

	#renderDoc(name, link, type, iconClass): HTMLElement
	{
		return Tag.render`
			<div class="bizproc-workflow-timeline-doc">
				${this.#renderCaption(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_START_DOC'))}
				<div class="bizproc-workflow-timeline-type">
					<span class="ui-icon-set ${iconClass}"></span>
					<span class="bizproc-workflow-timeline-type-text">${type}</span>
				</div>
				<a class="bizproc-workflow-timeline-link" href="${link}" target="_top">${Text.encode(name)}</a>
			</div>
		`;
	}

	#renderCaption(caption): HTMLElement
	{
		return Tag.render`
			<div class="bizproc-workflow-timeline-caption">${caption}</div>
		`;
	}

	#renderNotice(subject, text): HTMLElement
	{
		return Tag.render`
			<div class="bizproc-workflow-timeline-notice">
				<div class="bizproc-workflow-timeline-subject">${Text.encode(subject)}</div>
				<span class="bizproc-workflow-timeline-text">${Text.encode(text)}</span>
			</div>
		`;
	}

	#renderStatus(text, statusClass): HTMLElement
	{
		const statusClassValue = Type.isString(statusClass) ? (` ${statusClass}`) : '';

		return Tag.render`
			<div class="bizproc-workflow-timeline-status${statusClassValue}">${Text.encode(text)}</div>
		`;
	}

	#renderMore(): HTMLElement
	{
		return Tag.render`
			<div class="bizproc-workflow-timeline-item --more">
				<div class="bizproc-workflow-timeline-item-inner">
					<span class="bizproc-workflow-timeline-icon"></span>
					<button class="ui-btn ui-btn-light-border ui-btn-xs" type="button" onclick="expandMore(event)">
						<span class="ui-btn-text">
							${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_MORE_TASKS')}
						</span>
					</button>
					<script type="text/javascript">
						function expandMore(event)
						{
							const moreItemBlock = event.target.closest('.--more');
							const hiddenBlocks = document.querySelectorAll('.bizproc-workflow-timeline-item.--hidden:not(.--efficiency)');
							BX.Dom.addClass(moreItemBlock, '--hidden');
							hiddenBlocks.forEach((hiddenBlock) => BX.Dom.removeClass(hiddenBlock, '--hidden'));
						}
					</script>
				</div>
			</div>
		`;
	}

	#renderContent(children): HTMLElement
	{
		return (Tag.render`
			<div class="bizproc-workflow-timeline-content">
				${children}
			</div>
		`);
	}

	#renderItem(children, itemClass, efficiencyClass): HTMLElement
	{
		const itemClassValue = Type.isString(itemClass) ? (` ${itemClass}`) : '';
		const efficiencyClassValue = (
			Type.isString(efficiencyClass) ? (` data-efficiency-class="${efficiencyClass}"`) : ''
		);

		return (Tag.render`
			<div class="bizproc-workflow-timeline-item${itemClassValue}"${efficiencyClassValue}>
				<div class="bizproc-workflow-timeline-item-inner">
					${children}
				</div>
			</div>
		`);
	}

	#renderItemsList(items): HTMLElement
	{
		return (Tag.render`
			<div class="bizproc-workflow-timeline-wrapper">
				<div class="bizproc-workflow-timeline-inner">
					<div class="bizproc-workflow-timeline-list">
						${items}
					</div>
					<script type="text/javascript">
						(function() {
							const buttons = document.querySelectorAll('.task-button.--hidden');
							const showButtons = buttons.length > 1;
							buttons.forEach(function (button) {
								BX.Dom.insertBefore(
									button.closest('.bizproc-workflow-timeline-user'),
									button.closest('.bizproc-workflow-timeline-user-list').firstChild
								);
								if (showButtons)
								{
									BX.Dom.removeClass(button, '--hidden')
								}
							});
						})();
					</script>
				</div>
			</div>
		`);
	}

	#renderFirstBlock(): HTMLElement
	{
		const content = [];
		if (this.#data.startedBy)
		{
			content.push(
				this.#renderCaption(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_FROM')),
				this.#renderUser(this.#data.startedBy),
			);
		}
		content.push(
			this.#renderDoc(
				this.#data.document.name,
				this.#data.document.url,
				this.#data.document.moduleName,
				'--file-2',
			),
		);

		if (!Type.isNil(this.#data.timeToStart))
		{
			content.push(this.#renderNotice(
				Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EXECUTION_TIME'),
				DurationFormatter.formatTimeInterval(this.#data.timeToStart, 2),
			));
		}

		return this.#renderItem([
			this.#renderItemTitle(
				this.#data.document.entityName,
				'--success',
				'1',
			),
			this.#data.started && this.#renderSubject(
				DurationFormatter.formatDate(this.#data.started, this.#dateFormat, this.#dateFormatShort),
			),
			this.#renderContent(content),
		], '--selected');
	}

	#renderContainer(): HTMLElement
	{
		const items = [];
		let efficiencyClass = '';
		let isWaiting = false;

		if (this.#data)
		{
			let task = null;

			items.push(this.#renderFirstBlock());

			let taskNumber = 1;
			let hasHidden = this.#data.tasks[0] ? !this.#data.tasks[0].status.isWaiting() : true;
			for (const taskIndex of Object.keys(this.#data.tasks))
			{
				task = this.#data.tasks[taskIndex];
				isWaiting = task.status.isWaiting();
				if (!isWaiting)
				{
					++taskNumber;
				}

				const taskView = new TimelineTaskView({
					task,
					userId: Text.toInteger(Loc.getMessage('USER_ID')),
					dateFormat: this.#dateFormat,
					dateFormatShort: this.#dateFormatShort,
					taskNumber: isWaiting ? null : taskNumber,
					users: this.#data.users,
				});

				const node = taskView.render();

				if (!isWaiting && hasHidden)
				{
					Dom.addClass(node, '--hidden');
				}

				if (isWaiting && hasHidden)
				{
					items.push(this.#renderMore());
					hasHidden = false;
				}

				items.push(node);
			}

			if (hasHidden && this.#data.tasks[0])
			{
				items.push(this.#renderMore());
			}

			if (this.#data.isWorkflowRunning)
			{
				if (isWaiting)
				{
					items.push(this.#renderItem([
						this.#renderItemTitle(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS')),
						this.#renderSubject(
							Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS_TIP'),
						),
					], '--tech --previous-item'));
				}
				else
				{
					items.push(this.#renderItem([
						this.#renderItemTitle(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS_INTERMEDIATE')),
						this.#renderSubject(
							Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_IN_PROGRESS_INTERMEDIATE_TIP'),
						),
					], '--tech --previous-item'));
				}
			}
			else
			{
				const isOk = !task || task.status.isOk() || task.status.isYes();
				const content = [
					this.#renderCaption(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PROCESS_FINISHED')),
				];
				if (this.#data.startedBy)
				{
					content.push(
						isOk
							? this.#renderStatus(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_APPROVED_FOR'))
							: this.#renderStatus(
								Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_DECLINED'),
								'--failure',
							),
						this.#renderUser(this.#data.startedBy),
					);
				}
				content.push(this.#renderNotice(
					Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PROCESS_EXECUTED'),
					DurationFormatter.formatTimeInterval(this.#data.executionTime),
				));
				efficiencyClass = isOk ? '--success' : '--declined';
				items.push(
					this.#renderItem(
						[
							this.#renderItemTitle(
								this.#data.document.entityName,
								isOk ? '--success' : null,
							),
							this.#data.started && this.#renderSubject(
								DurationFormatter.formatDate(
									this.#data.started + this.#data.executionTime,
									this.#dateFormat,
									this.#dateFormatShort,
								),
							),
							this.#renderContent(content),
						],
						isOk ? '--success --previous' : '--declined --selected --previous',
						efficiencyClass,
					),
					this.#renderEfficiencyInlineContent(),
				);
			}
		}

		return this.#renderItemsList(items);
	}

	#textCrop()
	{
		const textCropNodes = document.querySelectorAll('[data-crop="crop"]');
		for (const textCropNode of textCropNodes)
		{
			const text = new TextCrop({
				rows: 2,
				target: textCropNode,

			});
			text.init();
		}
	}

	#createEfficiencyPopup(): Popup
	{
		return new Popup({
			width: 403,
			minHeight: 345,
			closeIcon: true,
			content: this.#renderEfficiencyPopupContent(),
			bindElement: {
				left: 555,
				top: 130,
			},
			padding: 26,
			borderRadius: '18px',
			className: '--bizproc-timeline-popup',
			events: {
				onPopupClose: () => {
					let inlineEfficiencyPrev = document.querySelector('.--previous-item');
					if (!inlineEfficiencyPrev)
					{
						inlineEfficiencyPrev = document.querySelector('.bizproc-workflow-timeline-item.--processing');
					}

					if (!inlineEfficiencyPrev)
					{
						return;
					}
					BX.Dom.addClass(inlineEfficiencyPrev, '--previous');
					let efficiencyInlineClass = inlineEfficiencyPrev.getAttribute('data-efficiency-class');
					if (!efficiencyInlineClass)
					{
						efficiencyInlineClass = '';
					}
					inlineEfficiencyPrev.after(this.#renderEfficiencyInlineContent(efficiencyInlineClass));
				},
			},
		});
	}

	#getEfficiencyData()
	{
		let logoClass = '--first';
		let notice = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_NO_STATS');

		switch (this.#data.stats.efficiency)
		{
			case 'fast':
				if (
					DurationFormatter.formatTimeInterval(this.#data.stats.averageDuration)
					=== DurationFormatter.formatTimeInterval(this.#data.executionTime)
				)
				{
					logoClass = '--slow';
					notice = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_SLOWLY');
				}
				else
				{
					logoClass = '--fast';
					notice = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_QUICKLY');
				}
				break;
			case 'slow':
				logoClass = '--slow';
				notice = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_SLOWLY');
				break;
			case 'stopped':
				logoClass = '--stopped';
				notice = Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMED_NO_PROGRESS');
				break;
			default:
				break;
		}

		return [logoClass, notice];
	}

	#renderEfficiencyInlineContent(itemClass: string): HTMLElement
	{
		const [logoClass, notice] = this.#getEfficiencyData();

		const efficiencyInlineContent = Tag.render`
			<div class="bizproc-workflow-timeline-item --efficiency ${itemClass}">
				<div class="bizproc-workflow-timeline-item-inner">
					<div class="bizproc-workflow-timeline-title">
						${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EFFECTIVITY_MARK')}
					</div>
					<div class="bizproc-workflow-timeline-content">
						<div class="bizproc-workflow-timeline-eff-icon ${logoClass}"></div>
						<div class="bizproc-workflow-timeline-content-inner">
							<div class="bizproc-workflow-timeline-caption">${notice}</div>
							<div class="bizproc-workflow-timeline-notice">
								<div class="bizproc-workflow-timeline-subject">
									${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_CURRENT_PROCESS_TIME')}
								</div>
								<span class="bizproc-workflow-timeline-text">
									${DurationFormatter.formatTimeInterval(this.#data.executionTime)}
								</span>
								<span
									data-hint="${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_TIME_DIFFERENCE_MSGVER_1')}"
								></span>
							</div>
							<div class="bizproc-workflow-timeline-notice">
								<div class="bizproc-workflow-timeline-subject">
									${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME')}
								</div>
								<span class="bizproc-workflow-timeline-text">
									${
										(this.#data.stats.averageDuration === null)
											? Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME_UNKNOWN')
											: DurationFormatter.formatTimeInterval(this.#data.stats.averageDuration)
									}
								</span>
							</div>
						</div>
					</div>
				</div>	
			</div>
		`;
		BX.UI.Hint.init(efficiencyInlineContent);

		return efficiencyInlineContent;
	}

	#renderEfficiencyPopupContent()
	{
		const [logoClass, notice] = this.#getEfficiencyData();

		const popup = Tag.render`
			<div class="bizproc-timeline-popup">
				<div class="bizproc-timeline-popup-title">
					${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_EFFECTIVITY_MARK')}
				</div>
				<div class="bizproc-timeline-popup-main">
					<div class="bizproc-timeline-popup-status">
						<div class="bizproc-timeline-popup-logo ${logoClass}"></div>
						<div class="bizproc-timeline-popup-notice">${notice}</div>
					</div>
					<div class="bizproc-timeline-popup-content">
						<div class="bizproc-timeline-popup-block">
							<span class="bizproc-timeline-popup-val">
								${DurationFormatter.formatTimeInterval(this.#data.executionTime)}
							</span>
							<span
								data-hint="${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_TIME_DIFFERENCE_MSGVER_1')}"
							></span>
							<div class="bizproc-timeline-popup-prop">
								${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_CURRENT_PROCESS_TIME')}
							</div>
						</div>
						<div class="bizproc-timeline-popup-block">
							<span class="bizproc-timeline-popup-val">
								${DurationFormatter.formatTimeInterval(this.#data.stats.averageDuration)}
							</span>
							<div class="bizproc-timeline-popup-prop">
								${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_AVERAGE_PROCESS_TIME')}
							</div>
						</div>
					</div>
				</div>
				<div class="bizproc-timeline-popup-footer">
					<p class="bizproc-timeline-popup-text">
						${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMANCE_TUNING_TIP')}
					</p>
					<a class="bizproc-timeline-popup-text" href="javascript:top.BX.Helper.show('redirect=detail&code=18783714')">
						${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_PERFORMANCE_TUNING_LINK')}
					</a>
				</div>
			</div>
		`;
		BX.UI.Hint.init(popup);

		return popup;
	}

	showBiMenus(menu: Array<BiMenuItem>): void
	{
		this.#createBiButton(menu);
		this.#createBiPopup(menu).show();
	}

	#createBiButton(menu: Array<BiMenuItem>): void
	{
		const toolbarNode = document.querySelector('[data-role="page-toolbar"]');
		if (!toolbarNode)
		{
			return;
		}

		if (menu.length === 1)
		{
			const linkBtn = Tag.render`
				<a class="ui-btn ui-btn-light-border ui-btn-themes" href="${Text.encode(menu[0].URL)}">
					${Text.encode(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_BUTTON'))}
				</a>
			`;

			Dom.prepend(linkBtn, toolbarNode);

			return;
		}

		const clickHandler = this.#showBiMenu.bind(this, menu);
		const dropBtn = Tag.render`
			<button class="ui-btn ui-btn-light-border ui-btn-themes ui-btn-dropdown" onclick="${clickHandler}">
				${Text.encode(Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_BUTTON'))}
			</button>
		`;

		Dom.prepend(dropBtn, toolbarNode);
	}

	#createBiPopup(menu: Array<BiMenuItem>): Popup
	{
		this.#biPopup = new Popup({
			width: 403,
			minHeight: 183,
			closeIcon: true,
			content: this.#renderBiPopupContent(menu),
			bindElement: {
				left: 555,
				top: 502,
			},
			padding: 17,
			borderRadius: '18px',
			className: '--bizproc-timeline-popup --bi',
		});

		return this.#biPopup;
	}

	#showBiMenu(menu: Array<BiMenuItem>, event: Event): void
	{
		(new Menu({
			bindElement: event.target,
			items: menu.map((item: BiMenuItem) => {
				return {
					text: item.TEXT,
					href: item.URL,
				};
			}),
		})).show();
	}

	#renderBiPopupContent(menu: Array<BiMenuItem>): Element
	{
		let btn = null;
		if (menu.length === 1)
		{
			btn = Tag.render`
				<a class="ui-btn ui-btn-light-border ui-btn-round ui-btn-xs" href="${Text.encode(menu[0].URL)}">
					<span class="ui-btn-text">${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_LINK')}</span>
				</a>
			`;
		}
		else
		{
			const clickHandler = this.#showBiMenu.bind(this, menu);
			btn = Tag.render`
				<a 
					class="ui-btn ui-btn-light-border ui-btn-round ui-btn-xs ui-btn-dropdown"
					onclick="${clickHandler}"
				>
					<span class="ui-btn-text">${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_LINK')}</span>
				</a>
			`;
		}

		return Tag.render`
			<div class="bizproc-timeline-popup">
				<div class="bizproc-timeline-popup-title">${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_TITLE')}</div>
				<p class="bizproc-timeline-popup-info">${Loc.getMessage('BIZPROC_WORKFLOW_TIMELINE_SLIDER_BI_ANALYTICS_TIP')}</p>
				${btn}
			</div>
		`;
	}

	#renderLoadingStub(): HTMLElement
	{
		return Tag.render`
			<img src="/bitrix/js/bizproc/workflow/timeline/img/skeleton.svg"
				 style="width:100%; margin: 0; padding: 0;"/>
		`;
	}

	#hasErrors(): boolean
	{
		return this.#errors.length > 0;
	}
}
