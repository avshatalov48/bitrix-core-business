import { Loc } from 'main.core';
import { MenuItem } from 'main.popup';
import { Label, LabelColor } from 'ui.label';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { KanbanSort } from 'tasks.kanban-sort';
import { KanbanOrder } from './kanban-order';

class KanbanSortItems
{
	#view: TasksSettingsMenu;
	#order: KanbanOrder;

	constructor(view: TasksSettingsMenu, order: KanbanOrder)
	{
		this.#view = view;
		this.#order = order;
	}

	getItems(): MenuItem[]
	{
		const hiddenItems = [
			this.#getOrderTitleItem(),
			this.#getOrderDescItem(),
			this.#getOrderAscItem(),
		];

		const items = [
			this.#getTitleItem(),
			this.#getActivitySortItem(),
			this.#getMySortItem(hiddenItems),
		];

		if (this.#order !== KanbanOrder.SORT_ACTUAL)
		{
			items.push(...hiddenItems);
		}

		return items;
	}

	#getTitleItem(): MenuItem
	{
		return new MenuItem({
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-sort-title-item` },
			html: `<b>${Loc.getMessage('SN_SPACES_TASKS_SORT_TITLE_ITEM')}</b>`,
			className: 'menu-popup-item menu-popup-no-icon',
		});
	}

	#getActivitySortItem(): MenuItem
	{
		const menuItem = new MenuItem({
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-activity-sort-item` },
			html: `
				${Loc.getMessage('SN_SPACES_TASKS_SORT_ACTIVITY_DATE_MSGVER_1')}
				<span style="margin-left: 5px">${this.#getRecommendedLabel()}</span>
			`,
			className: `menu-popup-item-sort-field ${this.#getSelectedClass([KanbanOrder.SORT_ACTUAL])}`,
			onclick: KanbanSort.getInstance().disableCustomSort,
		});

		menuItem.params = {
			order: KanbanOrder.SORT_ACTUAL,
		};

		return menuItem;
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

	#getMySortItem(hiddenItems: MenuItem[]): MenuItem
	{
		const menuItem = new MenuItem({
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-my-sort-item` },
			text: Loc.getMessage('SN_SPACES_TASKS_SORT_SORTING'),
			className: `menu-popup-item-sort-field ${this.#getSelectedClass([KanbanOrder.SORT_ASC, KanbanOrder.SORT_DESC])}`,
			onclick: KanbanSort.getInstance().enableCustomSort,
		});

		menuItem.params = hiddenItems;

		return menuItem;
	}

	#getOrderTitleItem(): MenuItem
	{
		const menuItem = new MenuItem({
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-sort-order-title-item` },
			html: `<b>${Loc.getMessage('SN_SPACES_TASKS_SORT_ORDER_TITLE')}</b>`,
			className: 'menu-popup-item menu-popup-no-icon',
		});

		menuItem.params = {
			type: 'sub',
		};

		return menuItem;
	}

	#getOrderDescItem(): MenuItem
	{
		return this.#getOrderItem(KanbanOrder.SORT_DESC);
	}

	#getOrderAscItem(): MenuItem
	{
		return this.#getOrderItem(KanbanOrder.SORT_ASC);
	}

	#getOrderItem(order: string): MenuItem
	{
		const menuItem = new MenuItem({
			dataset: { id: `spaces-tasks-${this.#view.getViewId()}-settings-order-${order}-item` },
			text: Loc.getMessage(`SN_SPACES_TASKS_SORT_${order.toUpperCase()}`),
			className: `menu-popup-item-sort-field ${this.#getSelectedClass([order])}`,
			onclick: KanbanSort.getInstance().selectCustomOrder,
		});

		menuItem.params = {
			type: 'sub',
			order,
		};

		return menuItem;
	}

	#getSelectedClass(order: string[]): string
	{
		const classSelected = 'menu-popup-item-accept';
		const classDeselected = 'menu-popup-item-none';

		return order.includes(this.#order) ? classSelected : classDeselected;
	}
}

export { KanbanOrder, KanbanSortItems };
