import { Reflection, ajax, Type, Dom, Tag, Loc, Runtime, Text } from 'main.core';
import { Alert, AlertColor } from 'ui.alerts';
import { Counter, CounterColor } from 'ui.cnt';
import { TagSelector } from 'ui.entity-selector';
import { Menu, MenuItemOptions, MenuManager } from 'main.popup';

const namespace = Reflection.namespace('BX.Bizproc.Component');

type WorkflowId = string;
type TaskId = number;

type Task = {
	id: number,
	name: string,
	canComplete: boolean,
	renderedName: string,
};

class UserProcesses
{
	gridId: string;
	delegateToSelector: TagSelector;
	delegateToUserId: number = 0;
	actionPanel: {
		wrapperElementId: string,
		actionButtonName: string,
		userWrapperElement: ?HTMLElement,
	};

	workflowTasks: Object<WorkflowId, Array<Task>> = {};
	tasksWorkflowsMap: Object<TaskId, WorkflowId> = {};
	currentUserId: number;

	constructor(options: {
		gridId: string,
		actionPanelUserWrapperId: string,
		errors: Array<{message: string}>,
		currentUserId: number,
	})
	{
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
		}

		this.bindAnchors();
		this.init();
	}

	init(): void
	{
		this.actionPanel.userWrapperElement = document.getElementById(this.actionPanel.wrapperElementId);

		for (const workflowTasksWrapper of document.querySelectorAll('[data-role="workflow-tasks-data"]'))
		{
			const workflowId = workflowTasksWrapper.dataset.workflowId;
			const tasks = JSON.parse(workflowTasksWrapper.dataset.tasks);

			if (Type.isStringFilled(workflowId) && Type.isArray(tasks))
			{
				this.workflowTasks[workflowId] = tasks;

				for (const task of tasks)
				{
					this.tasksWorkflowsMap[task.id] = workflowId;
				}
			}
		}

		this.initUserSelector();
		this.initTasksColumn();

		this.onActionPanelChanged();
	}

	bindAnchors()
	{
		BX.SidePanel.Instance.bindAnchors({
			rules:
				[
					{
						condition: [
							'/rpa/task/',
						],
						options: {
							width: 580,
							cacheable: false,
							allowChangeHistory: false,
							events: {
								onClose: () => this.reloadGrid(),
							},
						},
					},
				],
		});
	}

	initTasksColumn(): void
	{
		const taskNameContainers = document.querySelectorAll('.bp-task-container');
		for (const container of taskNameContainers)
		{
			this.renderParallelTasksTo(container);
		}
	}

	renderParallelTasksTo(container: HTMLElement): void
	{
		const taskId = parseInt(container.dataset.taskId, 10);
		const workflowId = this.tasksWorkflowsMap[taskId];

		if (Type.isNumber(taskId) && Type.isStringFilled(workflowId))
		{
			const tasks: Task[] = this.workflowTasks[workflowId] ?? [];

			if (tasks.length > 1)
			{
				// eslint-disable-next-line unicorn/no-this-assignment
				const self = this;
				const menuTasks = tasks.filter((task) => task.id !== taskId).map((task) => ({
					id: task.id,
					text: task.name,
					onclick()
					{
						this.close();
						const bindElement = this.getPopupWindow().bindElement;
						const currentTaskContainer = bindElement.parentElement.parentElement.parentElement;
						const gridCell = currentTaskContainer.parentElement;

						Dom.clean(gridCell);
						Runtime.html(gridCell, task.renderedName).catch((err) => console.error(err));

						const newContainer = gridCell.querySelector('.bp-task-container');
						if (newContainer)
						{
							self.renderParallelTasksTo(newContainer);
						}
					},
				}));

				if (!Type.isNil(container.lastElementChild))
				{
					const activeTasks = tasks.filter((task) => task.canComplete);

					if (activeTasks.length > 0)
					{
						Dom.prepend(this.renderParallelTasksCounter(activeTasks.length), container);
					}
					Dom.append(this.renderParallelTasksLabel(workflowId, taskId, menuTasks), container.lastElementChild);
				}
			}
		}
	}

	renderParallelTasksCounter(count: number): HTMLElement
	{
		const counter = new Counter({
			value: count,
			color: CounterColor.DANGER,
		});

		return Dom.create('div', {
			style: { paddingRight: '5px' },
			children: [counter.createContainer()],
		});
	}

	renderParallelTasksLabel(workflowId: string, showTaskId: number, tasks: MenuItemOptions[]): HTMLElement
	{
		const { label, root } = Tag.render`
			<span class="bp-task-label-container">
				<a ref="label" class="bp-task-label">
					${Loc.getMessage(
						'BIZPROC_USER_PROCESSES_TEMPLATE_TASKS_LABEL',
						{ '#COUNT#': tasks.length },
					)}
				</a>
			</span>
		`;

		label.onclick = (event) => {
			event.preventDefault();

			const menuId = `bp-workflow-${workflowId}-parallel-tasks-with-task-id-${showTaskId}`;
			const menu = MenuManager.create({
				id: menuId,
				angle: true,
				items: tasks,
			});
			menu.getPopupWindow().setBindElement(label);
			menu.show();
		};

		return root;
	}

	initStartWorkflowButton(buttonId: ?string)
	{
		const button = Type.isStringFilled(buttonId) && document.getElementById(buttonId);
		const lists = Type.isStringFilled(button?.dataset.lists) && JSON.parse(button.dataset.lists);

		if (lists)
		{
			const popupMenu = new Menu({
				angle: true,
				offsetLeft: Dom.getPosition(button).width / 2,
				autoHide: true,
				bindElement: button,
				closeByEsc: true,
				items: Object.values(lists).map((list) => ({
					text: list.name,
					href: list.url,
					className: 'feed-add-post-form-link-lists',
					dataset: {
						iconUrl: list.icon,
					},
				})),
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
				popupMenu.show();
			};
		}
	}

	initUserSelector(): void
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

		if (Type.isDomNode(this.actionPanel.userWrapperElement))
		{
			this.delegateToSelector.renderTo(this.actionPanel.userWrapperElement);
		}
	}

	showErrors(errors: Array<{ message: string }>): void
	{
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
				.map((workflowId) => this.workflowTasks[workflowId][0]?.id)
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
			.then(() => this.reloadGrid())
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
			.then(() => this.reloadGrid())
		;
	}

	reloadGrid(): void
	{
		const grid = this.getGrid();
		if (grid)
		{
			grid.reload();
		}
		else
		{
			console.warn('Grid not found');
		}
	}

	updateTaskData(taskId: number): void
	{
		location.reload();
	}

	getGrid(): ?BX.Main.grid
	{
		if (this.gridId)
		{
			return BX.Main.gridManager?.getInstanceById(this.gridId);
		}

		return null;
	}
}

namespace.UserProcesses = UserProcesses;
