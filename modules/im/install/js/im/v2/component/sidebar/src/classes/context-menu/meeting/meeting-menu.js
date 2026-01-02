import 'ui.notification';
import { Loc } from 'main.core';

import { SidebarMenu } from '../sidebar-base-menu';
import { MeetingManager } from './meeting-manager';

import type { MenuItemOptions } from 'ui.system.menu';
import type { ImModelSidebarMeetingItem } from 'im.v2.model';

type MeetingMenuContext = {
	meeting: ImModelSidebarMeetingItem,
	messageId: number,
	dialogId: string,
	source: string,
}

export class MeetingMenu extends SidebarMenu
{
	context: MeetingMenuContext;

	constructor()
	{
		super();

		this.id = 'im-sidebar-context-menu';
		this.meetingManager = new MeetingManager();
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getCopyLinkItem(Loc.getMessage('IM_SIDEBAR_MENU_COPY_MEETING_LINK')),
			this.getDeleteItem(),
		];
	}

	getDeleteItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_MEETING_CONNECTION'),
			onClick: function() {
				this.meetingManager.delete(this.context.meeting);
				this.menuInstance.close();
			}.bind(this),
		};
	}
}
