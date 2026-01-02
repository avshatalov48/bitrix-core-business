import 'ui.notification';
import { Loc } from 'main.core';

import { SidebarMenu } from '../sidebar-base-menu';
import { TaskManager } from './task-manager';

import type { ImModelSidebarTaskItem } from 'im.v2.model';
import type { MenuItemOptions } from 'ui.system.menu';

type TaskMenuContext = {
	task: ImModelSidebarTaskItem,
	messageId: number,
	dialogId: string,
	source: string,
}

export class TaskMenu extends SidebarMenu
{
	context: TaskMenuContext;

	constructor()
	{
		super();

		this.id = 'im-sidebar-context-menu';
		this.taskManager = new TaskManager();
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getCopyLinkItem(Loc.getMessage('IM_SIDEBAR_MENU_COPY_TASK_LINK')),
			this.getDeleteItem(),
		];
	}

	getDeleteItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_TASK_CONNECTION'),
			onClick: function() {
				this.taskManager.delete(this.context.task);
				this.menuInstance.close();
			}.bind(this),
		};
	}
}
