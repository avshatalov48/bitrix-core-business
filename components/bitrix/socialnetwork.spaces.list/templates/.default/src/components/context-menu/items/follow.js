import { ajax } from 'main.core';
import { MenuItem } from 'main.popup';
import { ContextItem } from './context-item';

export class Follow extends ContextItem
{
	static ID = 'follow';
	create(): Object
	{
		return {
			text: this.message,
			onclick: (event, menuItem: MenuItem) => {
				this.#switch().then((result) => {
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
		return ajax.runAction('socialnetwork.api.livefeed.spaces.switcher.follow', {
			data: {
				switcher: {
					type: Follow.ID,
					spaceId: this.spaceId,
				},
				space: this.spaceId,
			},
		});
	}

	#flush(resultMode: string): void
	{
		this.emit('followChanged', {
			spaceId: this.spaceId,
			isFollowed: resultMode === 'Y',
		});
	}
}
