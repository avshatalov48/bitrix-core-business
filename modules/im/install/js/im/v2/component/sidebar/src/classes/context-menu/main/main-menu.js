import { Loc } from 'main.core';

import { ChannelManager } from 'im.v2.lib.channel';
import { ChatService } from 'im.v2.provider.service';
import { Utils } from 'im.v2.lib.utils';
import { RecentMenu } from 'im.v2.lib.menu';
import { LayoutManager } from 'im.v2.lib.layout';
import { ChatActionType, Layout } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { Analytics } from 'im.v2.lib.analytics';
import { showDeleteChatConfirm, showDeleteChannelConfirm } from 'im.v2.lib.confirm';

import type { MenuItem } from 'im.v2.lib.menu';

export class MainMenu extends RecentMenu
{
	permissionManager: PermissionManager;

	static events = {
		onAddToChatShow: 'onAddToChatShow',
	};

	constructor()
	{
		super();
		this.id = 'im-sidebar-context-menu';
		this.permissionManager = PermissionManager.getInstance();
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
			this.getPinMessageItem(),
			this.getEditItem(),
			this.getAddMembersToChatItem(),
			this.getOpenProfileItem(),
			this.getOpenUserCalendarItem(),
			this.getChatsWithUserItem(),
			this.getHideItem(),
			this.getLeaveItem(),
			this.getDeleteItem(),
		];
	}

	getEditItem(): ?MenuItem
	{
		if (!this.permissionManager.canPerformAction(ChatActionType.update, this.context.dialogId))
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_UPDATE_CHAT'),
			onclick: () => {
				Analytics.getInstance().onOpenChatEditForm(this.context.dialogId);

				void LayoutManager.getInstance().setLayout({
					name: Layout.updateChat.name,
					entityId: this.context.dialogId,
				});
			},
		};
	}

	getDeleteItem(): ?MenuItem
	{
		if (!this.permissionManager.canPerformAction(ChatActionType.delete, this.context.dialogId))
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_CHAT'),
			className: 'menu-popup-no-icon bx-im-sidebar__context-menu_delete',
			onclick: async () => {
				Analytics.getInstance().chatDelete.onClick(this.context.dialogId);
				if (await this.#isDeletionCancelled())
				{
					return;
				}
				Analytics.getInstance().chatDelete.onConfirm(this.context.dialogId);

				try
				{
					await (new ChatService()).deleteChat(this.context.dialogId);
					void LayoutManager.getInstance().clearLayoutEntityId();
				}
				catch
				{
					this.#showDeleteChatError();
				}
			},
		};
	}

	#getDeleteConfirmFunction(dialogId: string): Promise<boolean>
	{
		const isChannel = ChannelManager.isChannel(dialogId);

		return isChannel ? showDeleteChannelConfirm() : showDeleteChatConfirm();
	}

	getOpenUserCalendarItem(): ?MenuItem
	{
		if (!this.isUser())
		{
			return null;
		}

		if (this.isBot())
		{
			return null;
		}

		const profileUri = Utils.user.getCalendarLink(this.context.dialogId);

		return {
			text: Loc.getMessage('IM_LIB_MENU_OPEN_CALENDAR_V2'),
			onclick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			},
		};
	}

	getAddMembersToChatItem(): MenuItem
	{
		if (this.isBot())
		{
			return null;
		}
		const canExtend = this.permissionManager.canPerformAction(ChatActionType.extend, this.context.dialogId);
		if (!canExtend)
		{
			return null;
		}

		const text = this.isChannel()
			? Loc.getMessage('IM_SIDEBAR_MENU_INVITE_SUBSCRIBERS')
			: Loc.getMessage('IM_SIDEBAR_MENU_INVITE_MEMBERS_V2');

		return {
			text,
			onclick: () => {
				this.emit(MainMenu.events.onAddToChatShow);
				this.menuInstance.close();
			},
		};
	}

	async #isDeletionCancelled(): Promise<boolean>
	{
		const { dialogId } = this.context;

		const confirmResult = await this.#getDeleteConfirmFunction(dialogId);
		if (!confirmResult)
		{
			Analytics.getInstance().chatDelete.onCancel(dialogId);

			return true;
		}

		return false;
	}

	#showDeleteChatError(): void
	{
		BX.UI.Notification.Center.notify({
			content: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_CHAT_ERROR'),
		});
	}
}
