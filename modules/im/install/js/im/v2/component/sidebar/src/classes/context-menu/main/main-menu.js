import { Loc } from 'main.core';
import { MenuItemDesign } from 'ui.system.menu';

import { ChatService } from 'im.v2.provider.service.chat';
import { Utils } from 'im.v2.lib.utils';
import { RecentMenu } from 'im.v2.lib.menu';
import { LayoutManager } from 'im.v2.lib.layout';
import { ActionByRole, ActionByUserType, Layout } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { Analytics } from 'im.v2.lib.analytics';
import { showDeleteChatConfirm } from 'im.v2.lib.confirm';
import { Notifier } from 'im.v2.lib.notifier';
import { ChatManager } from 'im.v2.lib.chat';
import { CopilotManager } from 'im.v2.lib.copilot';

import type { MenuItemOptions, MenuOptions } from 'ui.system.menu';

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

	getMenuOptions(): MenuOptions
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: false,
		};
	}

	getMenuItems(): MenuItemOptions[]
	{
		return [
			this.getPinMessageItem(),
			this.getEditItem(),
			this.getAddMembersToChatItem(),
			this.getOpenProfileItem(),
			this.getOpenUserCalendarItem(),
			this.getChatsWithUserItem(),
			this.getCopyInviteLinkItem(),
			this.getHideItem(),
			this.getLeaveItem(),
			this.getDeleteItem(),
		];
	}

	getCopyInviteLinkItem(): ?MenuItemOptions
	{
		if (!BX.clipboard.isCopySupported())
		{
			return null;
		}

		if (this.isUser() || this.isCollabChat())
		{
			return null;
		}

		const isGroupCopilotChat = (new CopilotManager()).isGroupCopilotChat(this.context.dialogId);
		const isCopilotChat = (new CopilotManager()).isCopilotChat(this.context.dialogId);
		if (isCopilotChat && !isGroupCopilotChat)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_COPY_INVITE_LINK'),
			onClick: () => {
				const chatLink = ChatManager.buildChatLink(this.context.dialogId);
				if (BX.clipboard.copy(chatLink))
				{
					Notifier.onCopyLinkComplete();
				}

				Analytics.getInstance().chatInviteLink.onCopyContextMenu(this.context.dialogId);
			},
		};
	}

	getEditItem(): ?MenuItemOptions
	{
		if (!this.permissionManager.canPerformActionByRole(ActionByRole.update, this.context.dialogId))
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_UPDATE_CHAT'),
			onClick: () => {
				Analytics.getInstance().chatEdit.onOpenForm(this.context.dialogId);

				void LayoutManager.getInstance().setLayout({
					name: Layout.updateChat,
					entityId: this.context.dialogId,
				});
			},
		};
	}

	getDeleteItem(): ?MenuItemOptions
	{
		if (!this.permissionManager.canPerformActionByRole(ActionByRole.delete, this.context.dialogId))
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_CHAT'),
			design: MenuItemDesign.Alert,
			onClick: async () => {
				Analytics.getInstance().chatDelete.onClick(this.context.dialogId);
				if (await this.#isDeletionCancelled())
				{
					return;
				}
				Analytics.getInstance().chatDelete.onConfirm(this.context.dialogId);

				if (this.isCollabChat())
				{
					this.#deleteCollab();

					return;
				}

				this.#deleteChat();
			},
		};
	}

	getOpenUserCalendarItem(): ?MenuItemOptions
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
			title: Loc.getMessage('IM_LIB_MENU_OPEN_CALENDAR_V2'),
			onClick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			},
		};
	}

	getAddMembersToChatItem(): ?MenuItemOptions
	{
		if (this.isBot() || this.isChatWithCurrentUser())
		{
			return null;
		}

		const hasCreateChatAccess = this.permissionManager.canPerformActionByUserType(ActionByUserType.createChat);
		if (this.isUser() && !hasCreateChatAccess)
		{
			return null;
		}

		const hasAccessByRole = this.permissionManager.canPerformActionByRole(ActionByRole.extend, this.context.dialogId);
		if (!hasAccessByRole)
		{
			return null;
		}

		const title = this.isChannel()
			? Loc.getMessage('IM_SIDEBAR_MENU_INVITE_SUBSCRIBERS')
			: Loc.getMessage('IM_SIDEBAR_MENU_INVITE_MEMBERS_V2');

		return {
			title,
			onClick: () => {
				Analytics.getInstance().userAdd.onChatSidebarClick(this.context.dialogId);
				this.emit(MainMenu.events.onAddToChatShow);
				this.menuInstance.close();
			},
		};
	}

	async #deleteChat(): Promise<void>
	{
		await (new ChatService()).deleteChat(this.context.dialogId);
		void LayoutManager.getInstance().clearCurrentLayoutEntityId();
	}

	async #deleteCollab(): Promise<void>
	{
		Notifier.collab.onBeforeDelete();
		await (new ChatService()).deleteCollab(this.context.dialogId);
		void LayoutManager.getInstance().clearCurrentLayoutEntityId();
		void LayoutManager.getInstance().deleteLastOpenedElementById(this.context.dialogId);
	}

	async #isDeletionCancelled(): Promise<boolean>
	{
		const confirmResult = await showDeleteChatConfirm(this.context.dialogId);
		if (!confirmResult)
		{
			Analytics.getInstance().chatDelete.onCancel(this.context.dialogId);

			return true;
		}

		return false;
	}
}
