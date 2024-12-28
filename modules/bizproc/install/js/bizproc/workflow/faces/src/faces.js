import { Summary } from 'bizproc.workflow.faces.summary';
import { ajax, Dom, Tag, Text, Type } from 'main.core';
import type { FooterType, StackType, StepType } from 'ui.image-stack-steps';
import { footerTypeEnum, headerTypeEnum, ImageStackSteps, imageTypeEnum, stackStatusEnum } from 'ui.image-stack-steps';
import { validateFacesData, workflowFacesDataValidator } from './helpers/validators';

import 'ui.design-tokens';
import 'ui.icons';
import 'ui.icon-set.main';

import type { Avatar, FacesData, StepData, WorkflowFacesData } from './types/workflow-faces';

import './css/style.css';

export type { FacesData, WorkflowFacesData, Avatar };

export class WorkflowFaces
{
	#target: HTMLElement;
	#data: FacesData = {};
	#showArrow: boolean = false;
	#showTimeStep: boolean = false;
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
			throw new TypeError('Bizproc.Workflow.Faces: data must be correct plain object');
		}

		this.#workflowId = data.workflowId;
		this.#target = data.target;
		this.#targetUserId = data.targetUserId || 0;
		this.#data = data.data;

		if (Type.isBoolean(data.showArrow))
		{
			this.#showArrow = data.showArrow;
		}

		if (Type.isBoolean(data.showTimeStep))
		{
			this.#showTimeStep = data.showTimeStep;
		}

		this.#initStack();
		if (data.subscribeToPushes && !data.isWorkflowFinished)
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
		const steps = [];
		this.#data.steps.forEach((stepData) => {
			steps.push(this.#createStep(stepData));
		});

		if (steps.length < 3)
		{
			for (let i = steps.length; i < 3; i++)
			{
				steps.push(this.#getStubStep());
			}
		}

		if (this.#data.progressBox && this.#data.progressBox.progressTasksCount > 0)
		{
			steps[0].progressBox = { title: this.#data.progressBox.text };
		}

		return steps.map((step, index) => ({ ...step, id: `step-${index}` }));
	}

	#createStep(data: StepData): StepType
	{
		return {
			id: data.id,
			header: { type: headerTypeEnum.TEXT, data: { text: data.name } },
			stack: this.#getStack(data),
			footer: this.#getFooter(data),
			styles: { minWidth: 75 },
		};
	}

	#getStack(data: StepData): StackType
	{
		const userStack = this.#getUserStack(data);
		if (userStack)
		{
			return userStack;
		}

		return this.#getIconStack(data);
	}

	#getUserStack(data: StepData): ?StackType
	{
		const images = this.#getStackUserImages(data.avatarsData);
		if (Type.isArrayFilled(images))
		{
			const stack = { images };

			let status = null;
			switch (data.status)
			{
				case 'wait':
					status = stackStatusEnum.WAIT;
					break;
				case 'success':
					status = stackStatusEnum.OK;
					break;
				case 'not-success':
					status = stackStatusEnum.CANCEL;
					break;
				default:
					status = null;
			}

			if (status)
			{
				stack.status = { type: status };
			}

			return stack;
		}

		return null;
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

	#getIconStack(data: StepData): StackType
	{
		let icon = null;
		let color = null;
		switch (data.id)
		{
			case 'completed':
				icon = data.success ? 'circle-check' : 'cross-circle-60';
				color = data.success ? 'var(--ui-color-primary-alt)' : 'var(--ui-color-base-35)';
				break;
			case 'running':
				icon = 'black-clock';
				color = 'var(--ui-color-palette-blue-60)';
				break;
			case 'done':
				icon = 'circle-check';
				color = 'var(--ui-color-primary-alt)';
				break;
			default:
				icon = 'bp';
				color = 'var(--ui-color-palette-gray-20)';
		}

		return { images: [{ type: imageTypeEnum.ICON, data: { icon, color } }] };
	}

	#getFooter(data: StepData): FooterType
	{
		if (
			(Type.isNumber(data.duration) && data.duration > 0)
			|| (data.id === 'running')
		)
		{
			return {
				type: footerTypeEnum.DURATION,
				data: { duration: Text.toInteger(data.duration), realtime: data.id === 'running' },
			};
		}

		return {
			type: footerTypeEnum.TEXT,
			data: { text: String(data.duration) },
		};
	}

	#getStubStep(): StepType
	{
		return {
			id: 'stub',
			header: { type: headerTypeEnum.STUB },
			stack: { images: [{ type: imageTypeEnum.USER_STUB }] },
			footer: { type: footerTypeEnum.STUB },
			styles: { minWidth: 75 },
		};
	}

	#subscribeToPushes()
	{
		if (BX.PULL)
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
					runningTaskId: this.#getRunningTaskId(),
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

	#getRunningTaskId(): number
	{
		const runningStep = this.#data.steps.find((step) => step.id === 'running');
		if (runningStep)
		{
			return runningStep.taskId;
		}

		return 0;
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

		if (this.#showTimeStep && this.#data.timeStep)
		{
			Dom.append(this.#renderTimeline(), this.#node);
		}
	}

	updateData(data)
	{
		const facesData = {
			steps: data.steps,
			progressBox: data.progressBox,
			timeStep: data.timeStep,
		};

		if (!validateFacesData(facesData))
		{
			return;
		}

		this.#data = facesData;

		this.#getStackSteps().forEach((step) => {
			this.#stack.updateStep(step, step.id);
		});

		if (data.isWorkflowFinished)
		{
			this.#unsubscribeToPushes();
			if (this.#showTimeStep)
			{
				Dom.replace(this.#timelineNode, this.#renderTimeline());
			}
		}
	}

	#renderTimeline(): HTMLElement
	{
		const timeline = new Summary({
			workflowId: this.#workflowId,
			data: this.#data.timeStep,
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
