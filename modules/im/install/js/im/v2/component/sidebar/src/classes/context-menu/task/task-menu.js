import 'ui.notification';
import { Loc } from 'main.core';

import { SidebarMenu } from '../sidebar-base-menu';
import { TaskManager } from './task-manager';

import type { ImModelSidebarTaskItem } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';

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

	getMenuItems(): MenuItem[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getCopyLinkItem(Loc.getMessage('IM_SIDEBAR_MENU_COPY_TASK_LINK')),
			this.getDeleteItem(),
		];
	}

	getDeleteItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_TASK_CONNECTION'),
			onclick: function() {
				this.taskManager.delete(this.context.task);
				this.menuInstance.close();
			}.bind(this),
		};
	}
}
