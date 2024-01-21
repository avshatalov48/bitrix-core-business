import { ajax } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { MenuItem } from 'main.popup';
import { ContextItem } from './context-item';
import {EventTypes} from "../../../const/event";

export class Pin extends ContextItem
{
	static ID = 'pinner';

	create(): JSON
	{
		return {
			text: this.message,
			onclick: (event, menuItem: MenuItem) => {
				this.#switch().then(result => {
					menuItem.getMenuWindow().close();
					const resultMessage = result.data.message;
					const resultMode = result.data.mode;
					setTimeout(() => {
						menuItem.setText(resultMessage);
					}, 800);
					this.#flush(resultMode);
				});
			},
		};
	}

	#switch(): Promise
	{
		return ajax.runAction('socialnetwork.api.livefeed.spaces.switcher.pin', {
			data: {
				switcher: {
					type: Pin.ID,
					spaceId: this.spaceId,
				},
				space: this.spaceId,
			},
		});
	}

	#flush(resultMode: string): void
	{
		this.getEmitter().emit(EventTypes.pinChanged, {
			spaceId: this.spaceId,
			isPinned: resultMode === 'Y',
		});
	}
}