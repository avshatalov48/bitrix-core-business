import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';

type Params = {
	bindElement: HTMLElement,
	viewList: Array<ViewItem>,
}

export type ViewItem = {
	id: number,
	key: string,
	title: string,
	urlParam: string,
	urlValue: string,
	selected: boolean,
}

export class TasksViewList extends EventEmitter
{
	#menu: Menu;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.TasksViewList');

		this.#menu = this.#createMenu(params.bindElement, params.viewList);
	}

	show(): void
	{
		this.#menu.toggle();
	}

	#createMenu(bindElement: HTMLElement, viewList: Array<ViewItem>): Menu
	{
		const menu = new Menu({
			id: 'spaces-tasks-view-list',
			className: 'sn-spaces-tasks-view-list-menu',
			bindElement,
			closeByEsc: true,
		});

		viewList.forEach((viewItem: ViewItem) => {
			menu.addMenuItem({
				dataset: { id: `spaces-tasks-${viewItem.key}` },
				text: viewItem.title,
				className: `sn-spaces-tasks-${viewItem.key}-icon ${viewItem.selected ? '--selected' : ''}`,
				onclick: () => {
					if (viewItem.selected)
					{
						menu.close();

						return;
					}

					this.emit('click', {
						urlParam: viewItem.urlParam,
						urlValue: viewItem.urlValue,
					});
				},
			});
		});

		return menu;
	}
}
