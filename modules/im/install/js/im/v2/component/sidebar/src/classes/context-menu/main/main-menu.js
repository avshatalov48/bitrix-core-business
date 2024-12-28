import { Loc, Type } from 'main.core';

import { ChatService } from 'im.v2.provider.service';
import { Utils } from 'im.v2.lib.utils';
import { RecentMenu } from 'im.v2.lib.menu';
import { LayoutManager } from 'im.v2.lib.layout';
import { ActionByRole, ActionByUserType, ChatType, Layout } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { Analytics } from 'im.v2.lib.analytics';
import { showDeleteChatConfirm } from 'im.v2.lib.confirm';

import type { MenuItem } from 'im.v2.lib.menu';

const NotEmptyCollabErrorCodes = new Set(['TASKS_NOT_EMPTY', 'DISK_NOT_EMPTY', 'CALENDAR_NOT_EMPTY']);

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
		if (!this.permissionManager.canPerformActionByRole(ActionByRole.update, this.context.dialogId))
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_UPDATE_CHAT'),
			onclick: () => {
				Analytics.getInstance().chatEdit.onOpenForm(this.context.dialogId);

				void LayoutManager.getInstance().setLayout({
					name: Layout.updateChat.name,
					entityId: this.context.dialogId,
				});
			},
		};
	}

	getDeleteItem(): ?MenuItem
	{
		if (!this.permissionManager.canPerformActionByRole(ActionByRole.delete, this.context.dialogId))
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

				if (this.isCollabChat())
				{
					this.#deleteCollab();

					return;
				}

				this.#deleteChat();
			},
		};
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

		const hasCreateChatAccess = this.permissionManager.canPerformActionByUserType(ActionByUserType.createChat);
		if (this.#isPersonalChat() && !hasCreateChatAccess)
		{
			return null;
		}

		const hasAccessByRole = this.permissionManager.canPerformActionByRole(ActionByRole.extend, this.context.dialogId);
		if (!hasAccessByRole)
		{
			return null;
		}

		const text = this.isChannel()
			? Loc.getMessage('IM_SIDEBAR_MENU_INVITE_SUBSCRIBERS')
			: Loc.getMessage('IM_SIDEBAR_MENU_INVITE_MEMBERS_V2');

		return {
			text,
			onclick: () => {
				Analytics.getInstance().userAdd.onChatSidebarClick(this.dialogId);
				this.emit(MainMenu.events.onAddToChatShow);
				this.menuInstance.close();
			},
		};
	}

	async #deleteChat(): ?MenuItem
	{
		try
		{
			await (new ChatService()).deleteChat(this.context.dialogId);
			void LayoutManager.getInstance().clearCurrentLayoutEntityId();
		}
		catch
		{
			this.#showNotification(Loc.getMessage('IM_SIDEBAR_MENU_DELETE_CHAT_ERROR'));
		}
	}

	async #deleteCollab(): ?MenuItem
	{
		try
		{
			this.#showNotification(Loc.getMessage('IM_SIDEBAR_MENU_DELETE_COLLAB_NOTIFICATION'));
			await (new ChatService()).deleteCollab(this.context.dialogId);
			void LayoutManager.getInstance().clearCurrentLayoutEntityId();
			void LayoutManager.getInstance().deleteLastOpenedElementById(this.context.dialogId);
		}
		catch (errors)
		{
			this.#handleDeleteCollabError(errors);
		}
	}

	async #isDeletionCancelled(): Promise<boolean>
	{
		const { dialogId } = this.context;

		const confirmResult = await showDeleteChatConfirm(dialogId);
		if (!confirmResult)
		{
			Analytics.getInstance().chatDelete.onCancel(dialogId);

			return true;
		}

		return false;
	}

	#handleDeleteCollabError(errors): void
	{
		if (!Type.isArrayFilled(errors))
		{
			return;
		}

		const [firstError] = errors;
		if (NotEmptyCollabErrorCodes.has(firstError.code))
		{
			this.#showNotification(Loc.getMessage('IM_SIDEBAR_MENU_DELETE_COLLAB_WITH_ENTITIES_ERROR'));

			return;
		}

		this.#showNotification(Loc.getMessage('IM_SIDEBAR_MENU_DELETE_COLLAB_ERROR'));
	}

	#showNotification(content: string): void
	{
		BX.UI.Notification.Center.notify({ content });
	}

	#isPersonalChat(): boolean
	{
		const chat = this.getChat(this.context.dialogId);

		return chat.type === ChatType.user;
	}
}
