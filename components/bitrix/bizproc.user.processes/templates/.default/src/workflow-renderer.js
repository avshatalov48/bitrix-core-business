import { WorkflowResultStatus } from 'bizproc.types';
import { Text, Tag, Type, Dom, Uri, Loc } from 'main.core';
import { Task, InlineTaskView } from 'bizproc.task';
import type { UserProcesses } from './user-processes';
import 'ui.hint';
import { WorkflowData } from './workflow-loader';
import { WorkflowFaces } from 'bizproc.workflow.faces';
import { Summary } from 'bizproc.workflow.faces.summary';
import { Counter, CounterColor } from 'ui.cnt';

export class WorkflowRenderer
{
	#currentUserId: number;
	#targetUserId: ?number;
	#data: WorkflowData;
	#task: Task;
	#inlineTaskView: InlineTaskView;
	#faces: ?WorkflowFaces = null;

	constructor(data: {
		userProcesses: UserProcesses,
		currentUserId: number,
		workflow: WorkflowData,
	})
	{
		this.#data = data.workflow;
		this.#currentUserId = Type.isNumber(data.currentUserId) ? data.currentUserId : 0;
		this.#targetUserId = this.#data.userId;

		if (this.#data.task)
		{
			this.#task = new Task(this.#data.task);
			if (Type.isArrayFilled(this.#task.controls.buttons))
			{
				this.#task.setButtons(
					this.#task.buttons.map((button) => ({
						onclick: () => data.userProcesses.doTask({
							taskId: this.#task.id,
							workflowId: this.#data.workflowId,
							taskName: this.#task.name,
							taskRequest: {
								[button.NAME]: button.VALUE,
							},
						}),
						...button,
					})),
				);
			}
			this.#inlineTaskView = new InlineTaskView({
				task: this.#task,
				responsibleUser: this.#targetUserId,
			});
		}
	}

	renderProcess(): HTMLElement | string
	{
		const itemName = Type.isString(this.#data?.name) ? this.#data.name : '';
		const typeName = Type.isString(this.#data?.typeName) ? this.#data.typeName : '';
		const documentUrl = this.#data.task?.url || this.#data.workflowUrl || this.#getWorkflowInfoUrl();

		const description = Type.isString(this.#data?.description) ? this.#data.description : '';
		const lengthLimit = 80;
		const collapsedDescription = Dom.create('span', { html: description?.replace(/(<br \/>)+/gm, ' ') }).textContent.replace(/\n+/, ' ').slice(0, lengthLimit);
		const collapsed = description?.length > lengthLimit;

		const descriptionNode = Tag.render`
			<span class="bp-user-processes__description">
				${description}
			</span>
		`;

		BX.UI.Hint.init(descriptionNode);

		return Tag.render`
				<div class="bp-user-processes">
					<a class="bp-user-processes__title-link ui-typography-text-lg"
						href="${Text.encode(documentUrl)}">${Text.encode(itemName)}
					</a>
					<div class="bp-user-processes__appointment">${Text.encode(typeName.toUpperCase())}</div>
					<div class="bp-user-processes__description-box ${collapsed ? '' : '--expanded'}">
						<span class="bp-user-processes__short_description">
							${Text.encode(collapsedDescription)}
							...<a href="#" onclick="this.closest('div').classList.add('--expanded'); return false;" class="bp-user-processes__description-link">${Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_DESCRIPTION_MORE')}</a>
						</span>
						${descriptionNode}
					</div>
			</div>
		`;
	}

	#getWorkflowInfoUrl(): string
	{
		const idParam = Type.isNil(this.#data.task?.id) ? this.#data.workflowId : this.#data.task.id;
		const uri = new Uri(`/company/personal/bizproc/${idParam}/`);

		return uri.toString();
	}

	renderTaskName(): ?HTMLElement
	{
		return this.#inlineTaskView?.render();
	}

	// eslint-disable-next-line sonarjs/cognitive-complexity
	renderTask(): ?HTMLElement
	{
		if (!this.#data.task || this.#data.userId !== this.#currentUserId)
		{
			const completedClassName = this.#data.isCompleted ? '--success' : '';
			let result = '';
			let noRightsClass = '';
			if (this.#data.isCompleted && (this.#data.workflowResult !== null))
			{
				if (this.#data.workflowResult.status === WorkflowResultStatus.BB_CODE_RESULT)
				{
					result = `${Loc.getMessage('BIZPROC_RENDERED_RESULT_VALUE')}<br>${this.#data.workflowResult.text ?? ''}`;
				}

				if (this.#data.workflowResult.status === WorkflowResultStatus.USER_RESULT)
				{
					result = Loc.getMessage('BIZPROC_RENDERED_RESULT_POSITIVE_RESULT_FOR', { '#USER#': this.#data.workflowResult.text ?? '' });
				}

				if (this.#data.workflowResult.status === WorkflowResultStatus.NO_RIGHTS_RESULT)
				{
					noRightsClass = 'no-rights';
					result = `${Loc.getMessage('BIZPROC_RENDERED_RESULT_NO_RIGHTS_VIEW')} <span data-hint="${Loc.getMessage('BIZPROC_RENDERED_RESULT_NO_RIGHTS_TOOLTIP')}"></span>`;
				}
			}

			const panel = Tag.render`
				<div class="bp-status-panel ${completedClassName}">
						<div class="bp-status-item">
							<div class="bp-status-name">${Text.encode(this.#data.statusText.toUpperCase())}</div>
							<div class="bp-workflow-result ${noRightsClass}">${result}</div>
						</div>
				</div>
			`;
			if (
				(this.#data.workflowResult !== null)
				&& (this.#data.workflowResult.status === WorkflowResultStatus.NO_RIGHTS_RESULT)
			)
			{
				BX.UI.Hint.init(panel);
			}

			return panel;
		}

		return this.renderTaskName();
	}

	renderDocumentName(): HTMLElement
	{
		const documentName = Type.isString(this.#data?.document?.name) ? this.#data.document.name : '';

		if (Type.isString(this.#data?.document?.url))
		{
			const url = new Uri(this.#data.document.url);

			return Tag.render`
				<a href="${Text.encode(url.toString())}">
					${Text.encode(documentName)}
				</a>
			`;
		}

		return Text.encode(documentName);
	}

	renderWorkflowFaces(): HTMLElement
	{
		const target = Tag.render`<div></div>`;

		if (this.#data.workflowId && this.#data.taskProgress)
		{
			try
			{
				this.#faces = (new WorkflowFaces({
					workflowId: this.#data.workflowId,
					targetUserId: this.#targetUserId,
					target,
					data: {
						steps: this.#data.taskProgress.steps,
						progressBox: this.#data.taskProgress.progressBox,
					},
					showArrow: true,
				}));
				this.#faces.render();
			}
			catch (e)
			{
				console.error(e);
			}
		}

		return target;
	}

	renderSummary(): ?HTMLElement
	{
		if (!this.#data.workflowId || !this.#data.taskProgress?.timeStep)
		{
			return null;
		}

		return (
			(new Summary({
					workflowId: this.#data.workflowId,
					data: this.#data.taskProgress.timeStep,
				})
			).render()
		);
	}

	renderModified(): HTMLElement
	{
		let counter = null;
		if (this.#data.userId === this.#currentUserId && (this.#data.taskCnt > 0 || this.#data.commentCnt > 0))
		{
			const primaryColor = this.#data.taskCnt === 0 && this.#data.commentCnt > 0
				? CounterColor.SUCCESS
				: CounterColor.DANGER
			;

			counter = new Counter({
				value: (this.#data.taskCnt || 0) + (this.#data.commentCnt || 0),
				color: primaryColor,
				secondaryColor: CounterColor.SUCCESS,
				isDouble: this.#data.taskCnt > 0 && this.#data.commentCnt > 0,
			});
		}

		return Tag.render`
			<div class="bp-modified-cell">
				<span class="bp-row-counters">${counter?.getContainer()}</span>
				<span>${Text.encode(this.#data.modified)}</span>
			</div>
		`;
	}

	destroy()
	{
		this.#data = null;
		this.#task = null;
		this.#inlineTaskView = null;

		if (!Type.isNil(this.#faces))
		{
			this.#faces.destroy();
			this.#faces = null;
		}
	}
}
