import {Loc} from 'main.core';
import {RecentMenu} from 'im.v2.lib.menu';
import {Utils} from 'im.v2.lib.utils';
import {ChatOption, DialogType} from 'im.v2.const';

import type {MenuItem} from 'im.v2.lib.menu';
import type {ImModelRecentItem} from 'im.v2.model';

export class MainMenu extends RecentMenu
{
	static events = {
		onAddToChatShow: 'onAddToChatShow',
	};

	constructor()
	{
		super();
		this.id = 'im-sidebar-context-menu';
	}

	getMenuOptions(): Object
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: false,
		};
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.getUnreadMessageItem(),
			this.getPinMessageItem(),
			this.getCallItem(),
			this.getOpenProfileItem(),
			this.getOpenUserCalendarItem(),
			this.getAddMembersToChatItem(),
			this.getHideItem(),
			this.getLeaveItem(),
		];
	}

	getOpenUserCalendarItem(): ?MenuItem
	{
		const isUser = this.store.getters['dialogues/isUser'](this.context.dialogId);
		if (!isUser)
		{
			return null;
		}
		const user = this.store.getters['users/get'](this.context.dialogId, true);
		if (user.bot)
		{
			return null;
		}

		const profileUri = Utils.user.getCalendarLink(this.context.dialogId);

		return {
			text: Loc.getMessage('IM_LIB_MENU_OPEN_CALENDAR'),
			onclick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			}
		};
	}

	getAddMembersToChatItem(): MenuItem
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
		const canInviteMembers = this.store.getters['dialogues/getChatOption'](dialog.type, ChatOption.extend);
		if (!canInviteMembers)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_INVITE_MEMBERS'),
			onclick: () => {
				this.emit(MainMenu.events.onAddToChatShow);
				this.menuInstance.close();
			}
		};
	}

	getJoinChatItem(): ?MenuItem
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
		const isUser = dialog.type === DialogType.user;
		if (isUser)
		{
			return null;
		}

		//todo: check if user is in chat already

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_JOIN_CHAT'),
			onclick: () => {
				console.warn('sidebar menu: join chat is not implemented');
				this.menuInstance.close();
			}
		};
	}

	canShowFullMenu(dialogId: string): boolean
	{
		const recentItem: ImModelRecentItem = this.store.getters['recent/get'](dialogId);

		return Boolean(recentItem);
	}
}