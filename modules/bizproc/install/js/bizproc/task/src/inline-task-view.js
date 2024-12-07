import { Tag, Dom, Event, Type, Text, Loc } from 'main.core';
import { Task, UserStatus, TaskButton } from './task';
import { UserId } from 'bizproc.types';

export class InlineTaskView
{
	#task: Task;
	#responsibleUser: ?{ id: number, status: UserStatus };

	constructor(options: {
		task: Task,
		responsibleUser?: UserId;
	})
	{
		this.#task = options.task;
		this.setResponsibleUser(options.responsibleUser);
	}

	setResponsibleUser(userId: UserId): InlineTaskView
	{
		if (Type.isNumber(userId))
		{
			this.#responsibleUser = this.#task.users.find((user) => user.id === userId);
		}

		return this;
	}

	render(): ?HTMLElement
	{
		if (!this.#task.isInline())
		{
			return this.#renderDefaultTaskButton();
		}

		if (Type.isArrayFilled(this.#task.controls.buttons))
		{
			return this.#renderTaskButtons();
		}

		return null;
	}

	renderTaskAnchor(): HTMLElement
	{
		return Tag.render`
			<a href="${Text.encode(this.#task.url || '#')}"></a>
		`;
	}

	#renderTaskButtons(): HTMLElement
	{
		const buttonsPanel = Tag.render`<div class="bp-btn-panel-block"></div>`;

		const taskButtons = this.#task.controls.buttons;

		if (!Type.isArray(taskButtons))
		{
			return buttonsPanel;
		}

		for (const button of taskButtons)
		{
			let renderedButton = null;
			if (!Object.hasOwn(button, 'default'))
			{
				renderedButton = this.#renderTaskButton(button);
			}
			else if (button.default === true)
			{
				renderedButton = this.#renderDefaultTaskButton();
			}

			if (Type.isDomNode(renderedButton))
			{
				Dom.append(renderedButton, buttonsPanel);
			}
		}

		return buttonsPanel;
	}

	#renderTaskButton(button: TaskButton): HTMLElement
	{
		const targetStatus = new UserStatus(button.TARGET_USER_STATUS);
		const isDecline = targetStatus.isNo() || targetStatus.isCancel();

		const className = isDecline ? 'light-border' : 'success';
		const encodedText = Text.encode(button.TEXT);

		const renderedButton = Tag.render`
			<div
				class="ui-btn ui-btn-round ui-btn-xs ui-btn-no-caps ui-btn-${className}"
				title="${encodedText}"
			>
				<div class="ui-btn-text">${encodedText}</div>
			</div>
		`;

		if (Type.isFunction(button.onclick))
		{
			Event.bind(renderedButton, 'click', button.onclick.bind(renderedButton));
		}

		return renderedButton;
	}

	#renderDefaultTaskButton(): ?HTMLElement
	{
		const anchor = this.renderTaskAnchor();

		if (Type.isDomNode(anchor))
		{
			Dom.addClass(anchor, ['ui-btn', 'ui-btn-primary', 'ui-btn-round', 'ui-btn-xs', 'ui-btn-no-caps']);
			const buttonText = Loc.getMessage('BIZPROC_TASK_DEFAULT_TASK_BUTTON');
			anchor.innerText = buttonText;

			return Tag.render`
				<div class="bp-btn-panel-block" title="${Text.encode(buttonText)}">
					${anchor}
				</div>
			`;
		}

		return null;
	}
}
