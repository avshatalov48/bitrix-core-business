import { Loc } from 'main.core';
import { MenuItem } from 'main.popup';
import { TasksSettingsMenu } from '../tasks-settings-menu';

const PriorityItem: MenuItem = (view: TasksSettingsMenu, priority: string, isActive: boolean) => {
	const menuItem = new MenuItem({
		dataset: { id: `spaces-tasks-${view.getViewId()}-settings-priority-${priority}` },
		className: isActive ? 'menu-popup-item-accept' : 'menu-popup-item-none',
		text: Loc.getMessage(`SN_SPACES_TASKS_SCRUM_SETTINGS_${priority.toUpperCase()}`),
		onclick: () => BX.Tasks.Scrum.Entry.setDisplayPriority(document.querySelector(`[data-id=${menuItem.dataset.id}`), priority),
	});

	return menuItem;
};

export const PriorityItems: MenuItem[] = (view: TasksSettingsMenu, priority: string) => [
	PriorityItem(view, 'backlog', priority === 'backlog'),
	PriorityItem(view, 'sprint', priority === 'sprint'),
];
