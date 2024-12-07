import { Type, Loc, Text, Dom, Tag, ajax } from 'main.core';
import { Summary } from 'bizproc.workflow.faces.summary';
import { ImageStackSteps, headerTypeEnum, footerTypeEnum, imageTypeEnum, stackStatusEnum } from 'ui.image-stack-steps';
import type { StepType, FooterType } from 'ui.image-stack-steps';

import type { FacesData, WorkflowFacesData, Avatar } from './types/workflow-faces';
export type { FacesData, WorkflowFacesData, Avatar };
import { workflowFacesDataValidator, validateFacesData } from './helpers/validators';

import 'ui.design-tokens';
import 'ui.icons';
import 'ui.icon-set.main';

import './css/style.css';

export class WorkflowFaces
{
	#target: HTMLElement;
	#data: FacesData = {};
	#showArrow: boolean = false;
	#showTimeline: boolean = false;
	#workflowId: string;
	#targetUserId: number;

	#stack: ImageStackSteps;
	#unsubscribePushCallback = null;
	#node: null;
	#timelineNode: null;
	#errorNode: null;

	constructor(data: WorkflowFacesData)
	{
		if (!workflowFacesDataValidator(data))
		{
			throw new TypeError('Bizproc.Workflow.Faces: data must be correct plain object', data);
		}

		this.#workflowId = data.workflowId;
		this.#target = data.target;
		this.#targetUserId = data.targetUserId;
		this.#data = data.data;

		if (Type.isBoolean(data.showArrow))
		{
			this.#showArrow = data.showArrow;
		}

		if (Type.isBoolean(data.showTimeline))
		{
			this.#showTimeline = data.showTimeline;
		}

		this.#initStack();
		if (data.subscribeToPushes)
		{
			this.#subscribeToPushes();
		}
	}

	#initStack()
	{
		this.#stack = new ImageStackSteps({ steps: this.#getStackSteps() });
	}

	#getStackSteps(): []
	{
		const steps = [this.#getAuthorStep()];

		if (Type.isArrayFilled(this.#data.avatars.completed))
		{
			steps.push(this.#getCompletedStep());
		}

		if (this.#data.workflowIsCompleted)
		{
			steps.push(this.#getDoneStep());
		}
		else
		{
			steps.push(this.#getRunningStep());
		}

		if (steps.length === 2)
		{
			steps.push(this.#getStubStep());
		}

		return steps.map((step, index) => ({ ...step, id: `step-${index}` }));
	}

	#getAuthorStep(): StepType
	{
		const stack = {
			images: [{
				type: imageTypeEnum.ICON,
				data: { icon: 'bp', color: 'var(--ui-color-palette-gray-20)' },
			}],
		};

		const avatar = this.#data.avatars.author[0];
		const authorId = Text.toInteger(avatar.id);
		if (authorId > 0)
		{
			stack.images = [{
				type: imageTypeEnum.USER,
				data: { src: String(avatar.avatarUrl || ''), userId: authorId },
			}];
		}

		const step = {
			id: 'author',
			header: {
				type: headerTypeEnum.TEXT,
				data: { text: Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_AUTHOR') },
			},
			stack,
			footer: this.#getFooterDuration(this.#data.time.author),
		};

		const hiddenTaskCount = this.#getHiddenTaskCount();
		if (hiddenTaskCount > 0)
		{
			step.progressBox = {
				title: Loc.getMessage('BIZPROC_JS_WORKFLOW_COMPLETED_TASK_COUNT', { '#COUNT#': hiddenTaskCount }),
			};
		}

		return step;
	}

	#getHiddenTaskCount(): number
	{
		const completedTaskCount = Text.toInteger(this.#data.completedTaskCount);

		if (this.#data.workflowIsCompleted)
		{
			return completedTaskCount > 2 ? completedTaskCount - 2 : 0;
		}

		return completedTaskCount > 1 ? completedTaskCount - 1 : 0;
	}

	#getRunningStep(): StepType
	{
		const stack = {
			images: [{
				type: imageTypeEnum.ICON,
				data: {
					icon: 'black-clock',
					color: 'var(--ui-color-palette-blue-60)',
				},
			}],
		};

		const images = this.#getStackUserImages(this.#data.avatars.running);
		if (Type.isArrayFilled(images))
		{
			stack.images = images;
			stack.status = { type: stackStatusEnum.WAIT };
		}

		return {
			id: 'running',
			header: {
				type: headerTypeEnum.TEXT,
				data: { text: Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_RUNNING') },
			},
			stack,
			footer: {
				type: footerTypeEnum.DURATION,
				data: {
					duration: this.#data.time.running,
					realtime: true,
				},
			},
		};
	}

	#getCompletedStep(): StepType
	{
		const isSuccess = Text.toBoolean(this.#data.statuses.completedSuccess);

		const stack = {
			images: [{
				type: imageTypeEnum.ICON,
				data: {
					icon: isSuccess ? 'circle-check' : 'cross-circle-60',
					color: isSuccess ? 'var(--ui-color-primary-alt)' : 'var(--ui-color-base-35)',
				},
			}],
		};

		const images = this.#getStackUserImages(this.#data.avatars.completed);
		if (Type.isArrayFilled(images))
		{
			stack.images = images;
			stack.status = { type: isSuccess ? stackStatusEnum.OK : stackStatusEnum.CANCEL };
		}

		return {
			id: 'completed',
			header: {
				type: headerTypeEnum.TEXT,
				data: { text: Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_COMPLETED') },
			},
			stack,
			footer: this.#getFooterDuration(this.#data.time.completed),
		};
	}

	#getDoneStep(): StepType
	{
		const stack = {
			images: [{
				type: imageTypeEnum.ICON,
				data: { icon: 'circle-check', color: 'var(--ui-color-primary-alt)' },
			}],
		};

		const images = this.#getStackUserImages(this.#data.avatars.done);
		if (Type.isArrayFilled(images))
		{
			const isSuccess = Text.toBoolean(this.#data.statuses.doneSuccess);

			stack.images = images;
			stack.status = { type: isSuccess ? stackStatusEnum.OK : stackStatusEnum.CANCEL };
		}

		return {
			id: 'done',
			header: {
				type: headerTypeEnum.TEXT,
				data: { text: Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_DONE') },
			},
			stack,
			footer: this.#getFooterDuration(this.#data.time.done),
		};
	}

	#getStubStep(): StepType
	{
		return {
			id: 'stub',
			header: { type: headerTypeEnum.STUB },
			stack: { images: [{ type: imageTypeEnum.USER_STUB }] },
			footer: { type: footerTypeEnum.STUB },
		};
	}

	#getStackUserImages(avatars): []
	{
		const images = [];
		if (Type.isArrayFilled(avatars))
		{
			avatars.forEach((avatar) => {
				const userId = Text.toInteger(avatar.id);
				if (userId > 0)
				{
					images.push({
						type: imageTypeEnum.USER,
						data: { userId, src: String(avatar.avatarUrl || '') },
					});
				}
			});
		}

		return images;
	}

	#getFooterDuration(time): FooterType
	{
		if (Type.isNumber(time) && time > 0)
		{
			return {
				type: footerTypeEnum.DURATION,
				data: { duration: time, realtime: false },
			};
		}

		return {
			type: footerTypeEnum.TEXT,
			data: { text: Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_EMPTY_TIME') },
		};
	}

	#subscribeToPushes()
	{
		if (!this.#data.workflowIsCompleted && BX.PULL)
		{
			this.#unsubscribePushCallback = BX.PULL.subscribe({
				moduleId: 'bizproc',
				command: 'workflow',
				callback: this.#onWorkflowPush.bind(this),
			});
		}
	}

	#onWorkflowPush(params)
	{
		if (params && params.eventName === 'UPDATED' && Type.isArrayFilled(params.items))
		{
			for (const item of params.items)
			{
				if (String(item.id) === this.#workflowId)
				{
					this.#loadWorkflowFaces();

					return;
				}
			}
		}
	}

	#loadWorkflowFaces()
	{
		if (this.#target && Type.isDomNode(this.#target) && this.#target.clientHeight > 0)
		{
			ajax.runAction('bizproc.workflow.faces.load', {
				data: {
					workflowId: this.#workflowId,
					runningTaskId: this.#data.runningTaskId || 0,
					userId: this.#targetUserId,
				},
			}).then(({ data }) => {
				if (Type.isDomNode(this.#errorNode))
				{
					Dom.replace(this.#errorNode, this.#node);
					this.#errorNode = null;
				}

				this.updateData(data);
			}).catch(({ errors }) => {
				if (Type.isArrayFilled(errors))
				{
					const firstError = errors.pop();
					if (firstError.code === 'ACCESS_DENIED')
					{
						Dom.replace(
							this.#node,
							this.#renderError(firstError.message),
						);

						this.errorMessage = firstError.message;
					}
				}
			});
		}
	}

	#unsubscribeToPushes()
	{
		if (Type.isFunction(this.#unsubscribePushCallback))
		{
			this.#unsubscribePushCallback();
			this.#unsubscribePushCallback = null;
		}
	}

	render()
	{
		if (this.#node)
		{
			return;
		}

		this.#node = Tag.render`<div class="bp-workflow-faces"></div>`;
		Dom.append(this.#node, this.#target);
		this.#stack.renderTo(this.#node);

		if (this.#showArrow)
		{
			Dom.append(Tag.render`<div class="bp-workflow-faces-arrow"></div>`, this.#node);
		}

		if (this.#showTimeline)
		{
			Dom.append(this.#renderTimeline(), this.#node);
		}
	}

	updateData(data: FacesData)
	{
		if (!validateFacesData(data))
		{
			return;
		}

		this.#data = data;

		this.#getStackSteps().forEach((step) => {
			this.#stack.updateStep(step, step.id);
		});

		if (this.#data.workflowIsCompleted)
		{
			this.#unsubscribeToPushes();
			if (this.#showTimeline)
			{
				Dom.replace(this.#timelineNode, this.#renderTimeline());
			}
		}
	}

	#renderTimeline(): HTMLElement
	{
		const timeline = new Summary({
			workflowId: this.#workflowId,
			time: this.#data.time.total,
			workflowIsCompleted: this.#data.workflowIsCompleted,
			showArrow: false,
		});
		this.#timelineNode = timeline.render();

		return this.#timelineNode;
	}

	#renderError(message: string): HTMLElement
	{
		this.#errorNode = Tag.render`
			<div class="bp-workflow-faces">
				<span class="bp-workflow-faces-error-message">
					${Text.encode(message)}
				</span>
			</div>
		`;

		return this.#errorNode;
	}

	destroy()
	{
		this.#unsubscribeToPushes();

		this.#stack.destroy();
		this.#stack = null;

		this.#target = null;
		this.#data = null;
		this.#workflowId = null;

		Dom.clean(this.#timelineNode);
		this.#timelineNode = null;
	}
}
