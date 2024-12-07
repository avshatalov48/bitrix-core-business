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

	loader: WorkflowLoader;

	constructor(options: {
		gridId: string,
		actionPanelUserWrapperId: string,
		errors: Array<{ message: string }>,
		currentUserId: number,
		mustSubscribeToPushes: boolean,
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

			mustSubscribeToPushes = options.mustSubscribeToPushes === true;
		}

		this.loader = new WorkflowLoader();

		if (mustSubscribeToPushes)
		{
			this.#subscribeToPushes();
		}

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
				MODIFIED: renderer.renderModified(),
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
			},
			editable: Boolean(workflow.task),
		};
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

	async initStartWorkflowButton(buttonId: ?string)
	{
		const button = Type.isStringFilled(buttonId) && document.getElementById(buttonId);
		const lists = Type.isStringFilled(button?.dataset.lists) && JSON.parse(button.dataset.lists);
		let selectedIBlockSliderParams = null;

		if (lists)
		{
			const popupMenu = new Menu({
				angle: true,
				offsetLeft: Dom.getPosition(button).width / 2,
				autoHide: true,
				bindElement: button,
				closeByEsc: true,
				items: Object.values(lists).map((list) => {
					const item = {
						text: list.name,
						className: 'feed-add-post-form-link-lists',
						dataset: {
							iconUrl: list.icon,
						},
					};

					if (Type.isNil(list.url))
					{
						const params = {
							iBlockTypeId: list.iBlockTypeId,
							iBlockId: list.iBlockId,
							analyticsP1: list.name,
						};

						if (list.selected === true)
						{
							selectedIBlockSliderParams = params;
						}

						item.onclick = () => {
							popupMenu.close();
							Runtime.loadExtension('lists.element.creation-guide')
								.then(({ CreationGuide }) => {
									CreationGuide?.open(params);
								})
								.catch(() => {})
							;
						};
					}
					else
					{
						item.href = list.url;
					}

					return item;
				}),
			});
			const popupElement = popupMenu.getMenuContainer();
			for (const iconElement of popupElement.querySelectorAll('.menu-popup-item-icon'))
			{
				Dom.append(
					Tag.render`
						<img src = "${iconElement.parentElement.dataset.iconUrl}" alt="" width = "19" height = "16"/>
					`,
					iconElement,
				);
			}

			button.onclick = (event) => {
				event.preventDefault();
				if (!popupMenu.getPopupWindow().isShown())
				{
					Runtime.loadExtension('ui.analytics')
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
				popupMenu.toggle();
			};
		}

		if (selectedIBlockSliderParams)
		{
			Runtime.loadExtension('lists.element.creation-guide')
				.then(({ CreationGuide }) => {
					CreationGuide?.open(selectedIBlockSliderParams);
				})
				.catch(() => {})
			;
		}
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
		this.#hideRow(workflowId, true);
		this.#deleteWorkflowRendererById(workflowId);
		this.#workflowTasks.delete(workflowId);
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
		this.getGrid()?.getRows().getById(id)?.show();
	}
}
