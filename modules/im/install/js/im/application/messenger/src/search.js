import { BaseEvent, EventEmitter } from 'main.core.events';
import { Item, Dialog } from "ui.entity-selector";
import { EventType } from 'im.const';

export class Search
{
	constructor(params = {}): void
	{
		if (typeof params.store === 'object' && params.store)
		{
			this.store = params.store;
		}

		this.dialog = new BX.UI.EntitySelector.Dialog({
			targetNode: params.targetNode,
			enableSearch: true,
			context: 'IM_CHAT_SEARCH',
			multiple: false,
			entities: [
				{
					id: 'user',
					filters: [
						{
							id: 'im.userDataFilter',
						},
					],
				},
				{
					id: 'department',
				},
				{
					id: 'im-chat',
					options: {
						searchableChatTypes: ['C', 'L', 'O',]
					}
				},
				{
					id: 'im-bot',
					options: {
						searchableBotTypes: ['H', 'B', 'S', 'N',]
					}
				},
			],
			events: {
				'Item:onSelect': (event: BaseEvent) => this.onItemSelect(event),
				'onLoad': (event: BaseEvent) => this.fillStore(event),
			}
		});
	}

	onItemSelect(event: BaseEvent): void
	{
		this.dialog.deselectAll();

		const item: Item = event.getData().item;

		const dialogId = this.getDialogIdByItem(item);
		if (!dialogId)
		{
			return;
		}

		EventEmitter.emit(EventType.dialog.open, { id: dialogId, $event: event });
	}

	fillStore(event: BaseEvent): void
	{
		const dialog: Dialog = event.getTarget();
		const items: Item[] = dialog.getItems();

		let users = [];
		let dialogues = [];

		items.forEach((item) => {
			const customData = item.getCustomData();
			const entityId = item.getEntityId();

			if (entityId === 'user' || entityId === 'im-bot')
			{
				const dialogId = customData.get('imUser')['ID'];
				if (!dialogId)
				{
					return;
				}

				users.push({
					dialogId,
					...customData.get('imUser'),
				});
			}
			else if (entityId === 'im-chat')
			{
				const dialogId = 'chat' + customData.get('imChat')['ID'];
				if (!dialogId)
				{
					return;
				}

				dialogues.push({
					dialogId,
					...customData.get('imChat'),
				});
			}
		});

		this.store.dispatch('users/set', users);
		this.store.dispatch('dialogues/set', dialogues);
	}

	getDialogIdByItem(item: Item): ?string
	{
		switch (item.getEntityId())
		{
			case 'user':
			case 'im-bot':
				return item.getCustomData().get('imUser')['ID'];
			case 'im-chat':
				return 'chat' + item.getCustomData().get('imChat')['ID'];
		}

		return null;
	}

	open(): void
	{
		this.dialog.show();
	}
}
