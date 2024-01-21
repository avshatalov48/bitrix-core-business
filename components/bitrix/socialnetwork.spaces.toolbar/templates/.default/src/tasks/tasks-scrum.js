import { Event, Tag, Text } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Dialog } from 'ui.entity-selector';
import { TasksRouter } from './tasks-router';

type Params = {
	groupId: number,
	pathToScrumBurnDown: string,
	router: TasksRouter,
	currentCompletedSprint: CurrentCompletedSprint,
}

export type CurrentCompletedSprint = {
	id: number,
	selectorLabel: string,
}

export class TasksScrum
{
	#groupId: number;
	#pathToScrumBurnDown: string;
	#router: TasksRouter;

	#currentCompletedSprint: CurrentCompletedSprint;

	#layout: {
		selector: HTMLElement;
		selectorLabel: HTMLElement;
	};

	constructor(params: Params)
	{
		this.#groupId = params.groupId;
		this.#pathToScrumBurnDown = params.pathToScrumBurnDown;
		this.#router = params.router;
		this.#currentCompletedSprint = params.currentCompletedSprint;

		this.#layout = {};
	}

	renderSprintSelector(): HTMLElement
	{
		const { selector, selectorLabel } = Tag.render`
			<div
				ref="selector"
			
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-no-caps ui-btn-themes sn-spaces__toolbar-space_btn-options"
			>
				<span class="sn-spaces__toolbar-space_btn-text" ref="selectorLabel">
					${Text.encode(this.#currentCompletedSprint.selectorLabel)}
				</span>
				<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 19px;"></div>
			</div>
		`;

		this.#layout.selector = selector;
		this.#layout.selectorLabel = selectorLabel;

		Event.bind(selector, 'click', this.#showSprintSelector.bind(this, selector));

		return selector;
	}

	showCompletionForm()
	{
		const extensionName = 'Sprint-Completion-Form';
		// eslint-disable-next-line promise/catch-or-return
		this.#router.showByExtension(
			`tasks.scrum.${extensionName.toLowerCase()}`,
			extensionName,
			{ groupId: this.#groupId },
		)
			.then((extension) => {
				if (extension)
				{
					extension.subscribe('afterComplete', () => {
						this.#router.redirectToScrumView('plan');
					});
					extension.subscribe('taskClick', (baseEvent: BaseEvent) => {
						this.#router.showTask(baseEvent.getData());
					});
				}
			})
		;
	}

	showBurnDown()
	{
		this.#router.showSidePanel(
			this.#pathToScrumBurnDown.replace('#sprint_id#', this.#currentCompletedSprint.id),
		);
	}

	#showSprintSelector(selectorNode: HTMLElement)
	{
		const dialog = new Dialog({
			targetNode: selectorNode,
			width: 400,
			height: 300,
			multiple: false,
			dropdownMode: true,
			enableSearch: true,
			compactView: true,
			showAvatars: false,
			cacheable: false,
			preselectedItems: [['sprint-selector', this.#currentCompletedSprint.id]],
			entities: [
				{
					id: 'sprint-selector',
					options: {
						groupId: this.#groupId,
						onlyCompleted: true,
					},
					dynamicLoad: true,
					dynamicSearch: true,
				},
			],
			events: {
				'Item:onSelect': (event) => {
					var selectedItem = event.getData().item;

					this.#currentCompletedSprint.id = selectedItem.id;

					// todo change to EventEmitter
					// eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
					BX.onCustomEvent(selectorNode, 'onTasksGroupSelectorChange', [
						{
							id: this.#groupId,
							sprintId: selectedItem.id,
							name: selectedItem.customData.get('name'),
						},
					]);

					this.#layout.selectorLabel.textContent = selectedItem.customData.get('label');
				},
			},
		});

		dialog.show();
	}
}
