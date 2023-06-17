import 'ui.notification';
import type {MenuItem} from 'im.v2.lib.menu';
import {SidebarMenu} from '../sidebar-base-menu';
import {MeetingManager} from './meeting-manager';
import type {ImModelSidebarMeetingItem} from 'im.v2.model';
import {Loc} from 'main.core';

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

	getMenuItems(): MenuItem[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getCopyLinkItem(Loc.getMessage('IM_SIDEBAR_MENU_COPY_MEETING_LINK')),
			this.getDeleteItem(),
		];
	}

	getDeleteItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_MEETING_CONNECTION'),
			onclick: function() {
				this.meetingManager.delete(this.context.meeting);
				this.menuInstance.close();
			}.bind(this)
		};
	}
}