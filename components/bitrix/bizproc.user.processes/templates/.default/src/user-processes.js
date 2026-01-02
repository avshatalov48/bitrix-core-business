import { ajax, Type, Dom, Tag, Text, Loc, Event, Runtime } from 'main.core';
import { Alert, AlertColor } from 'ui.alerts';
import { TagSelector } from 'ui.entity-selector';
import { Menu } from 'main.popup';
import type { WorkflowData } from './workflow-loader';
import { WorkflowLoader, LoadWorkflowsResponseData } from './workflow-loader';
import { WorkflowRenderer } from './workflow-renderer';
import { CounterPanel } from './counter-panel.js';
import { UI } from 'ui.notification';

import './style.css';

import 'ui.design-tokens';

type WorkflowId = string;
type TaskId = number;

export class UserProcesses
{
	gridId: string;
	delegateToSelector: TagSelector;
	delegateToUserId: number = 0;
	actionPanel: {
		wrapperElementId: string,
		actionButtonName: string,
		userWrapperElement: ?HTMLElement,
	};

	#workflowTasks: Map<WorkflowId, TaskId> = new Map();
	#workflowRenderer: Object<WorkflowId, WorkflowRenderer> = {};
	currentUserId: number;
	#targetUserId: number;
	#shownMobilePopup: boolean;
	#appLink: string;

	loader: WorkflowLoader;

	constructor(options: {
		gridId: string,
		actionPanelUserWrapperId: string,
		errors: Array<{ message: string }>,
		currentUserId: number,
		targetUserId: number;
		mustSubscribeToPushes: boolean,
		shownMobilePopup: boolean,
		appLink: string,
	})
	{
		let mustSubscribeToPushes = false;

		if (Type.isPlainObject(options))
		{
			this.gridId = options.gridId;

			if (Type.isArray(options.errors))
			{
				this.showErrors(options.errors);
			}
			this.actionPanel = {
				wrapperElementId: options.actionPanelUserWrapperId,
				actionButtonName: `${this.gridId}_action_button`,
			};

			this.currentUserId = options.currentUserId;
			this.#targetUserId = options.targetUserId;
			this.#shownMobilePopup = options.shownMobilePopup;
			this.#appLink = options.appLink;

			mustSubscribeToPushes = options.mustSubscribeToPushes === true;
		}

		this.loader = new WorkflowLoader();

		if (mustSubscribeToPushes)
		{
			this.#subscribeToPushes();
		}
		this.#subscribeToTaskDo();

		this.init();
		this.initCounterPanel(options.counters, options.filterId);
	}

	#subscribeToPushes()
	{
		BX.PULL.subscribe({
			moduleId: 'bizproc',
			command: 'workflow',
			callback: (params) => {
				if (params.eventName === 'DELETED' || params.eventName === 'UPDATED')
				{
					params.items.forEach((workflow) => this.removeWorkflow(workflow.id));
				}

				if (params.eventName === 'ADDED' || params.eventName === 'UPDATED')
				{
					const rowsCollectionWrapper: BX.Grid.Rows = this.getGrid().getRows();
					let ids = params.items.map((workflow) => workflow.id);
					if (params.eventName === 'ADDED')
					{
						ids = ids.filter((id) => !rowsCollectionWrapper.getById(id));
					}

					if (ids.length > 0)
					{
						this.loader
							.loadWorkflows(ids)
							.then(this.#updateWorkflows.bind(this))
							.catch((response) => this.showErrors(response))
						;
					}
				}
			},
		});
	}

	#subscribeToTaskDo()
	{
		BX.addCustomEvent(
			'SidePanel.Slider:onMessage',
			(event) => {
				if (event.getEventId() === 'try-do-bp-task-event')
				{
					this.#hideRow(event.data.workflowId);
				}
				else if (event.getEventId() === 'error-do-bp-task-event')
				{
					this.#showRow(event.data.workflowId);
				}
				else if (event.getEventId() === 'success-do-bp-task-event')
				{
					UI.Notification.Center.notify({
						content: Loc.getMessage(
							'BIZPROC_USER_PROCESSES_TEMPLATE_TASK_TOUCHED',
							{ '#TASK_NAME#': Text.encode(event.data.taskName) },
						),
					});
				}
			},
		);
	}

	#updateWorkflows(response: LoadWorkflowsResponseData): void
	{
		const { workflows } = response.data;
		if (!Type.isArray(workflows))
		{
			// eslint-disable-next-line no-console
			console.warn('Unexpected response from server. Expected workflow.data to be an array');

			return;
		}

		const gridRealtime = this.getGrid()?.getRealtime();

		if (gridRealtime)
		{
			let lastWorkflowId = null;
			workflows.forEach((workflow) => {
				const isActual = Boolean(
					workflow.taskCnt > 0
					|| workflow.commentCnt > 0
					|| (
						workflow.startedById === this.currentUserId
						&& workflow.isCompleted === false
					),
				);

				if (isActual)
				{
					this.#appendWorkflow({
						workflow,
						renderer: this.#createWorkflowRenderer(workflow.workflowId, workflow),
						insertAfter: lastWorkflowId,
					});
					lastWorkflowId = workflow.workflowId;
				}
			});
		}
	}

	#appendWorkflow({ workflow, renderer, insertAfter }): void
	{
		if (workflow.task)
		{
			this.#workflowTasks.set(workflow.workflowId, workflow.task.id);
		}

		const gridRealtime = this.getGrid()?.getRealtime();
		if (!gridRealtime)
		{
			return;
		}

		const addRowOptions = this.getDefaultAddRowOptions(workflow, renderer);

		if (Type.isStringFilled(insertAfter))
		{
			addRowOptions.insertAfter = insertAfter;
		}
		else
		{
			addRowOptions.prepend = true;
		}

		gridRealtime.addRow(addRowOptions);

		// temporary crutches for the GRID :-)
		const row: BX.Grid.Row = this.getGrid()?.getRows().getById(workflow.workflowId);
		if (row)
		{
			if (addRowOptions.columnClasses)
			{
				for (const [columnId, columnClass] of Object.entries(addRowOptions.columnClasses))
				{
					if (columnClass)
					{
						Dom.addClass(row.getCellById(columnId), columnClass);
					}
				}
			}
			Dom.addClass(row.getNode(), 'main-ui-grid-show-new-row');
			Event.bind(row.getNode(), 'animationend', (event: AnimationEvent) => {
				if (event.animationName === 'showNewRow')
				{
					Dom.removeClass(row.getNode(), 'main-ui-grid-show-new-row');
				}
			});
		}
	}

	getDefaultAddRowOptions(workflow: WorkflowData, renderer: WorkflowRenderer): Object
	{
		const actions = [
			{
				text: Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_ROW_ACTION_DOCUMENT'),
				href: workflow.document.url || '#',
			},
		];

		if (workflow.task)
		{
			actions.push({
				text: Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_ROW_ACTION_TASK'),
				href: workflow.task.url,
			});
		}

		return {
			id: workflow.workflowId,
			animation: false,
			columns: {
				ID: workflow.workflowId,
				PROCESS: renderer.renderProcess(),
				TASK_PROGRESS: renderer.renderWorkflowFaces(),
				TASK: renderer.renderTask(),
				WORKFLOW_STATE: Text.encode(workflow.statusText),
				DOCUMENT_NAME: renderer.renderDocumentName(),
				WORKFLOW_TEMPLATE_NAME: Text.encode(workflow.templateName),
				TASK_DESCRIPTION: Dom.create('span', { html: workflow.description || '' }),
				MODIFIED: Text.encode(workflow.modified),
				WORKFLOW_STARTED: Text.encode(workflow.workflowStarted),
				WORKFLOW_STARTED_BY: Text.encode(workflow.startedBy),
				OVERDUE_DATE: Text.encode(workflow.overdueDate),
				SUMMARY: renderer.renderSummary(),
			},
			actions,
			columnClasses: {
				TASK_PROGRESS: 'bp-task-progress-cell',
				SUMMARY: 'bp-summary-cell',
				TASK: workflow.isCompleted ? 'bp-status-completed-cell' : '',
				TASK_DESCRIPTION: 'bp-description-cell',
			},
			counters: this.#getCountersOption(workflow),
			editable: Boolean(workflow.task),
		};
	}

	#getCountersOption(workflow: WorkflowData): Object
	{
		const counters = {};
		if (this.#targetUserId === this.currentUserId && (workflow.taskCnt > 0 || workflow.commentCnt > 0))
		{
			const primaryColor = workflow.taskCnt === 0 && workflow.commentCnt > 0
				? BX.Grid.Counters.Color.SUCCESS
				: BX.Grid.Counters.Color.DANGER
			;

			counters.MODIFIED = {
				type: BX.Grid.Counters.Type.LEFT,
				color: primaryColor,
				secondaryColor: BX.Grid.Counters.Color.SUCCESS,
				value: (workflow.taskCnt || 0) + (workflow.commentCnt || 0),
				isDouble: workflow.taskCnt > 0 && workflow.commentCnt > 0,
			};
		}

		return counters;
	}

	init(): void
	{
		this.actionPanel.userWrapperElement = document.getElementById(this.actionPanel.wrapperElementId);
		this.initUserSelector();
		this.renderCells();

		this.onActionPanelChanged();
	}

	initCounterPanel(counters, filterId): void
	{
		const panelWrapperNode = document.querySelector('[data-role="bizproc-counterpanel"]');
		if (!panelWrapperNode)
		{
			return;
		}

		(new CounterPanel({ counters, filterId })).renderTo(panelWrapperNode);
	}

	renderCells()
	{
		const updated = new Map();

		document.querySelectorAll('[data-role="bp-render-cell"]').forEach(
			(target) => {
				const workflow = Dom.attr(target, 'data-workflow');
				const columnId = Dom.attr(target, 'data-column');

				if (workflow)
				{
					if (!updated.has(workflow.workflowId))
					{
						this.#deleteWorkflowRendererById(workflow.workflowId);
						updated.set(workflow.workflowId);
					}

					if (workflow.task)
					{
						// set workflow task map
						this.#workflowTasks.set(workflow.workflowId, workflow.task.id);
					}

					this.renderColumnCell(target, columnId, workflow);
				}
			},
		);
	}

	renderColumnCell(target, columnId, workflow)
	{
		const renderer = (
			this.#getWorkflowRendererById(String(workflow.workflowId))
			?? this.#createWorkflowRenderer(String(workflow.workflowId), workflow)
		);

		let childNode = null;
		switch (columnId)
		{
			case 'DOCUMENT_NAME':
				childNode = renderer.renderDocumentName();
				break;

			case 'PROCESS':
				childNode = renderer.renderProcess();
				break;

			case 'TASK_PROGRESS':
				childNode = renderer.renderWorkflowFaces();
				break;

			case 'TASK':
				childNode = renderer.renderTask();
				break;

			case 'SUMMARY':
				childNode = renderer.renderSummary();
				break;

			case 'MODIFIED':
				childNode = renderer.renderModified();
				break;

			default:
				// do nothing
		}

		if (childNode)
		{
			Dom.replace(
				target,
				childNode,
			);
		}
	}

	clickStartWorkflowButton(): void
	{
		Runtime
			.loadExtension('bizproc.router')
			.then(({ Router }) => {
				Router.openUserProcessesStart();
			})
			.catch((e) => console.error(e));

		Runtime
			.loadExtension('ui.analytics')
			.then(({ sendData }) => {
				sendData({
					tool: 'automation',
					category: 'bizproc_operations',
					event: 'drawer_open',
					c_section: 'bizproc',
					c_element: 'button',
				});
			})
			.catch(() => {})
		;
	}

	creationGuideOpen(params: Object): void
	{
		Runtime.loadExtension('lists.element.creation-guide')
			.then(({ CreationGuide }) => {
				CreationGuide?.open(params);
			})
			.catch(() => {})
		;
	}

	initUserSelector(): void
	{
		if (!this.delegateToSelector)
		{
			this.delegateToSelector = new TagSelector({
				multiple: false,
				tagMaxWidth: 180,
				events: {
					onTagAdd: (event) => {
						this.delegateToUserId = parseInt(event.getData().tag.getId(), 10);

						if (!Type.isInteger(this.delegateToUserId))
						{
							this.delegateToUserId = 0;
						}
					},
					onTagRemove: () => {
						this.delegateToUserId = 0;
					},
				},
				dialogOptions: {
					entities: [
						{
							id: 'user',
							options: {
								intranetUsersOnly: true,
								inviteEmployeeLink: false,
							},
						},
					],
				},
			});
		}

		if (Type.isDomNode(this.actionPanel.userWrapperElement))
		{
			Dom.clean(this.actionPanel.userWrapperElement);
			this.delegateToSelector.renderTo(this.actionPanel.userWrapperElement);
		}
	}

	showErrors(errors: Array<{ message: string }>): void
	{
		if (!Type.isArrayFilled(errors))
		{
			if (!Type.isArray(errors))
			{
				console.error(errors);
			}

			return;
		}

		const errorsContainer = document.getElementById('bp-user-processes-errors-container');

		if (errorsContainer)
		{
			let errorCounter = 0;
			const fixStyles = () => {
				if (errorCounter > 0)
				{
					Dom.style(errorsContainer, { margin: '10px' });
				}
				else
				{
					Dom.style(errorsContainer, { margin: '0px' });
				}
			};

			for (const error of errors)
			{
				errorCounter += 1;

				const alert = new Alert({
					text: Text.encode(error.message),
					color: AlertColor.DANGER,
					closeBtn: true,
					animated: true,
				});

				alert.renderTo(errorsContainer);

				if (alert.getCloseBtn())
				{
					// eslint-disable-next-line no-loop-func
					alert.getCloseBtn().onclick = () => {
						errorCounter -= 1;
						fixStyles();
					};
				}
			}

			fixStyles();
		}
	}

	onActionPanelChanged(): void
	{
		const grid = this.getGrid();
		const actionPanel = grid?.getActionsPanel();

		if (actionPanel)
		{
			const action = actionPanel.getValues()[this.actionPanel.actionButtonName];
			if (!Type.isString(action) || action.includes('set_status'))
			{
				Dom.hide(this.actionPanel.userWrapperElement);
			}
			else
			{
				Dom.show(this.actionPanel.userWrapperElement);
			}
		}
	}

	applyActionPanelValues(): void
	{
		const grid = this.getGrid();
		const actionsPanel = grid?.getActionsPanel();

		if (grid && actionsPanel)
		{
			const isApplyingForAll = actionsPanel.getForAllCheckbox()?.checked === true;
			// TODO - implement doing all tasks
			if (isApplyingForAll)
			{
				this.showErrors([{ message: 'Not implemented currently' }]);
			}

			const action: string = actionsPanel.getValues()[this.actionPanel.actionButtonName];

			if (Type.isString(action))
			{
				const selectedTasks = this.getSelectedTaskIds(grid.getRows().getSelectedIds());

				if (selectedTasks.length === 0)
				{
					// todo: show error?

					return;
				}

				if (action.includes('set_status_'))
				{
					const status = parseInt(action.split('_').pop(), 10);

					if (Type.isNumber(status))
					{
						this.setTasksStatuses(selectedTasks, status);
					}
				}
				else if (action.startsWith('delegate_to'))
				{
					this.delegateTasks(selectedTasks, this.delegateToUserId);
				}
			}
		}
	}

	getSelectedTaskIds(selectedWorkflowIds: Array<WorkflowId>): Array<TaskId>
	{
		return (
			selectedWorkflowIds
				.map((workflowId) => this.#workflowTasks.get(workflowId))
				.filter((taskId) => Type.isNumber(taskId))
		);
	}

	setTasksStatuses(taskIds: Array<TaskId>, newStatus: number): void
	{
		// eslint-disable-next-line promise/catch-or-return
		ajax
			.runAction('bizproc.task.doInlineTasks', {
				data: {
					taskIds,
					newStatus,
				},
			})
			.catch((response) => {
				this.showErrors(response.errors);
				this.reloadGrid();
			})
			// .then(() => this.reloadGrid())
		;
	}

	delegateTasks(taskIds: Array<TaskId>, toUserId: number): void
	{
		// eslint-disable-next-line promise/catch-or-return
		ajax
			.runComponentAction('bitrix:bizproc.user.processes', 'delegateTasks', {
				mode: 'class',
				data: {
					taskIds,
					toUserId,
				},
			})
			.catch((response) => {
				this.showErrors(response.errors);
				this.reloadGrid();
			})
			// .then(() => this.reloadGrid())
		;
	}

	reloadGrid(): void
	{
		this.getGrid()?.reload();
	}

	doTask(props: {
		taskId: TaskId,
		workflowId: WorkflowId,
		taskName: string,
		taskRequest: Object,
	}): void
	{
		this.#hideRow(props.workflowId);

		ajax.runAction('bizproc.task.do', {
			data: props,
		}).then(() => {
			if (props.taskName)
			{
				UI.Notification.Center.notify({
					content: Loc.getMessage(
						'BIZPROC_USER_PROCESSES_TEMPLATE_TASK_TOUCHED',
						{ '#TASK_NAME#': Text.encode(props.taskName) },
					),
				});
			}
		}).catch((response) => {
			this.showErrors(response.errors);
			this.#showRow(props.workflowId);
		});
	}

	removeWorkflow(workflowId: string): void
	{
		if (!this.#shownMobilePopup && this.#workflowTasks.has(workflowId))
		{
			const mobile = new BX.UI.MobilePromoter({
				title: Loc.getMessage('BIZPROC_USER_PROCESSES_POPUP_PUSH_TITLE'),
				content: this.getPopupContent(),
				position: {
					right: 30,
					bottom: 30,
				},
				qrContent: this.#appLink,
				analytics: { c_section: 'bizproc' },
			});
			mobile.show();
			BX.userOptions.save('bizproc.user.processes', 'mobile_promotion_popup', 'shown_popup', 'Y', false);
			this.#shownMobilePopup = true;
		}

		this.#hideRow(workflowId, true);
		this.#deleteWorkflowRendererById(workflowId);
		this.#workflowTasks.delete(workflowId);
	}

	getPopupContent(): HTMLElement
	{
		return Tag.render`
			<div class="ui-mobile-promoter__content-wrapper">
				<ul class="ui-mobile-promoter__popup-list">
					<li class="ui-mobile-promoter__popup-list-item">
						${Loc.getMessage('BIZPROC_USER_PROCESSES_POPUP_PUSH_DO_PROCESS')}
					</li>
					<li class="ui-mobile-promoter__popup-list-item">
						${Loc.getMessage('BIZPROC_USER_PROCESSES_POPUP_PUSH_REACT')}
					</li>
					<li class="ui-mobile-promoter__popup-list-item">
						${Loc.getMessage('BIZPROC_USER_PROCESSES_POPUP_PUSH_CONTROL')}
					</li>
				</ul>
				<div class="ui-mobile-promoter__popup-desc">${Loc.getMessage('UI_MOBILE_PROMOTER_DESC')}</div>
				<div class="ui-mobile-promoter__popup-info">${Loc.getMessage('UI_MOBILE_PROMOTER_INFO')}</div>
			</div>
		`;
	}

	getGrid(): ?BX.Main.grid
	{
		if (this.gridId)
		{
			return BX.Main.gridManager?.getInstanceById(this.gridId);
		}

		// eslint-disable-next-line no-console
		console.warn('Grid not found');

		return null;
	}

	#createWorkflowRenderer(workflowId: WorkflowId, workflow): WorkflowRenderer
	{
		this.#workflowRenderer[workflowId] = new WorkflowRenderer({
			userProcesses: this,
			currentUserId: this.currentUserId,
			workflow,
		});

		return this.#workflowRenderer[workflowId];
	}

	#getWorkflowRendererById(workflowId: WorkflowId): ?WorkflowRenderer
	{
		return Type.isNil(this.#workflowRenderer[workflowId]) ? null : this.#workflowRenderer[workflowId];
	}

	#deleteWorkflowRendererById(workflowId: WorkflowId)
	{
		const renderer = this.#getWorkflowRendererById(workflowId);
		if (renderer)
		{
			renderer.destroy();
			delete this.#workflowRenderer[workflowId];
		}
	}

	#hideRow(id: string, remove: boolean = false): void
	{
		const grid = this.getGrid();
		const row = grid?.getRows().getById(id);

		if (row)
		{
			row.hide();
			if (remove)
			{
				Dom.remove(row.getNode());
			}

			if (grid.getRows().getCountDisplayed() === 0)
			{
				grid.getRealtime().showStub();
			}
		}
	}

	#showRow(id: string): void
	{
		const row = this.getGrid()?.getRows().getById(id);
		if (row)
		{
			row.show();
			Dom.addClass(row.getNode(), 'main-ui-grid-show-new-row');

			Event.bind(row.getNode(), 'animationend', (event: AnimationEvent) => {
				if (event.animationName === 'showNewRow')
				{
					Dom.removeClass(row.getNode(), 'main-ui-grid-show-new-row');
				}
			});
		}
	}
}
