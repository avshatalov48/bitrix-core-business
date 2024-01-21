import { Loc, Dom } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { MenuItem } from 'main.popup';
import { Label, LabelColor } from 'ui.label';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { TasksSortManager } from '../tasks-sort-manager';

type TaskSort = {
	field: string,
	direction: string,
}

type Params = {
	sortFields: string[],
	taskSort: TaskSort,
	sortManager: TasksSortManager,
}

const classSelected = 'menu-popup-item-accept';
const classSortField = 'menu-popup-item-sort-field';
const classSortDir = 'menu-popup-item-sort-dir';

class SortItem
{
	#params: Params;
	#view: TasksSettingsMenu;
	#menuItems: MenuItem[];

	constructor(view: TasksSettingsMenu, params: Params)
	{
		this.#view = view;
		this.#params = params;
	}

	getItem(): MenuItem
	{
		return {
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-sort` },
			text: Loc.getMessage('SN_SPACES_TASKS_SORT'),
			className: 'menu-popup-item-none menu-popup-sort',
			events: {
				onSubMenuShow: (event: BaseEvent) => {
					this.#menuItems = event.target.getSubMenu().getMenuItems();
					this.#updateStyles(this.#menuItems);
				},
			},
			items: this.#getMenuItems(),
		};
	}

	#getMenuItems(): MenuItem[]
	{
		return [
			...this.#params.sortFields.flatMap(this.#getFieldItem.bind(this)),
			new MenuItem({
				id: 'delimiterDir',
				delimiter: true,
			}),
			this.#getDirectionItem('asc'),
			this.#getDirectionItem('desc'),
		];
	}

	#getFieldItem(field: string): MenuItem
	{
		const isActivityField = field === 'ACTIVITY_DATE';

		const menuItem = new MenuItem({
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-sort-${field}` },
			html: `
				${Loc.getMessage(`SN_SPACES_TASKS_SORT_${field}`)}
				${isActivityField ? `<span style="margin-left: 5px">${this.#getRecommendedLabel()}</span>` : ''}
			`,
			value: field,
			className: `${classSortField} menu-popup-item-none`,
			onclick: (event, item) => {
				this.#onMenuItemClick('field', item);
			},
		});

		if (isActivityField)
		{
			return [new MenuItem({ delimiter: true }), menuItem, new MenuItem({ delimiter: true })];
		}

		return menuItem;
	}

	#getDirectionItem(direction: string): MenuItem
	{
		return new MenuItem({
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-sort-${direction}` },
			text: Loc.getMessage(`SN_SPACES_TASKS_SORT_BY_${direction.toUpperCase()}`),
			className: `${classSortDir} menu-popup-item-none`,
			value: direction,
			onclick: (event, item) => {
				this.#onMenuItemClick('dir', item);
			},
		});
	}

	#getRecommendedLabel(): string
	{
		return new Label(
			{
				text: Loc.getMessage('SN_SPACES_TASKS_SORT_RECOMMENDED_LABEL').toUpperCase(),
				color: LabelColor.LIGHT_BLUE,
				fill: true,
				size: 'ui-label-xs',
			},
		).render().outerHTML;
	}

	#onMenuItemClick(selectedItemType, selectedItem)
	{
		if (selectedItemType === 'field')
		{
			this.#params.taskSort.field = selectedItem.value;
		}
		else if (selectedItemType === 'dir')
		{
			this.#params.taskSort.direction = selectedItem.value;
		}

		this.#params.sortManager.setSort(this.#params.taskSort);

		this.#updateStyles(this.#menuItems);
	}

	#updateStyles(menuItems: MenuItem[])
	{
		menuItems.forEach((item: MenuItem) => {
			this.#updateStyle(item, this.#params.taskSort);
		});
	}

	#updateStyle(item: MenuItem, taskSort: TaskSort)
	{
		const itemNode = item.getContainer();

		const isFieldItem = Dom.hasClass(itemNode, classSortField);
		const isDirectionItem = Dom.hasClass(itemNode, classSortDir) || item.getId() === 'delimiterDir';

		const isFieldSelected = isFieldItem && taskSort.field === item.value;
		const isDirectionSelected = isDirectionItem && taskSort.direction === item.value;
		if (isFieldSelected || isDirectionSelected)
		{
			Dom.addClass(itemNode, classSelected);
		}
		else
		{
			Dom.removeClass(itemNode, classSelected);
		}

		if (taskSort.field === 'SORTING' && isDirectionItem)
		{
			Dom.style(itemNode, 'display', 'none');
		}
		else
		{
			Dom.style(itemNode, 'display', '');
		}
	}
}

export { SortItem };
