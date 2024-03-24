import { ContextItem } from './context-item';
import { EventTypes } from '../../../const/event';
import { MenuItem } from 'main.popup';

export class Open extends ContextItem
{
	path: string;

	create(): Object
	{
		return {
			text: this.message,
			onclick: (event, menuItem: MenuItem) => {
				this.emitter.emit(EventTypes.openSpaceFromContextMenu, {
					spaceId: this.spaceId,
				});
				menuItem.getMenuWindow().close();
			},
		};
	}

	setPath(path: string): Open
	{
		this.path = path;

		return this;
	}
}
