import { Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Analytics } from 'im.v2.lib.analytics';
import { ChannelManager } from 'im.v2.lib.channel';
import { Core } from 'im.v2.application.core';
import { BaseMenu } from 'im.v2.lib.menu';
import { Parser } from 'im.v2.lib.parser';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { MessageService, DiskService } from 'im.v2.provider.service';
import { EventType, PlacementType, ChatActionType } from 'im.v2.const';
import { MarketManager } from 'im.v2.lib.market';
import { Utils } from 'im.v2.lib.utils';
import { PermissionManager } from 'im.v2.lib.permission';
import { showDeleteChannelPostConfirm } from 'im.v2.lib.confirm';

import '../css/message-menu.css';

import 'ui.notification';

import type { MenuItem } from 'im.v2.lib.menu';
import type { ImModelMessage, ImModelChat, ImModelFile } from 'im.v2.model';

export class MessageMenu extends BaseMenu
{
	context: ImModelMessage & {dialogId: string};
	diskService: DiskService;

	constructor()
	{
		super();

		this.id = 'bx-im-message-context-menu';
		this.diskService = new DiskService();
		this.marketManager = MarketManager.getInstance();
	}

	getMenuOptions(): Object
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: true,
			offsetLeft: 11,
		};
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.getReplyItem(),
			this.getCopyItem(),
			this.getCopyLinkItem(),
			this.getCopyFileItem(),
			this.getPinItem(),
			this.getForwardItem(),
			this.getDelimiter(),
			this.getMarkItem(),
			this.getFavoriteItem(),
			this.getDelimiter(),
			this.getCreateItem(),
			this.getDelimiter(),
			this.getDownloadFileItem(),
			this.getSaveToDisk(),
			this.getDelimiter(),
			this.getEditItem(),
			this.getDeleteItem(),
		];
	}

	getReplyItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_REPLY'),
			onclick: () => {
				EventEmitter.emit(EventType.textarea.replyMessage, {
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}

	getForwardItem(): ?MenuItem
	{
		if (this.#isDeletedMessage() || Type.isString(this.context.id))
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_FORWARD'),
			onclick: () => {
				EventEmitter.emit(EventType.dialog.showForwardPopup, {
					messageId: this.context.id,
				});
				this.menuInstance.close();
			},
		};
	}

	getCopyItem(): ?MenuItem
	{
		if (this.context.text.trim().length === 0)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY'),
			onclick: async () => {
				const textToCopy = Parser.prepareCopy(this.context);

				await Utils.text.copyToClipboard(textToCopy);
				BX.UI.Notification.Center.notify({
					content: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_SUCCESS'),
				});

				this.menuInstance.close();
			},
		};
	}

	getCopyLinkItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_LINK'),
			onclick: () => {
				const textToCopy = Utils.text.getMessageLink(this.context.dialogId, this.context.id);
				if (BX.clipboard?.copy(textToCopy))
				{
					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_LINK_SUCCESS'),
					});
				}
				this.menuInstance.close();
			},
		};
	}

	getCopyFileItem(): ?MenuItem
	{
		if (this.context.files.length !== 1)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE'),
			onclick: () => {
				const textToCopy = Parser.prepareCopyFile(this.context);
				if (BX.clipboard?.copy(textToCopy))
				{
					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE_SUCCESS'),
					});
				}
				this.menuInstance.close();
			},
		};
	}

	getPinItem(): ?MenuItem
	{
		const canPin = PermissionManager.getInstance().canPerformAction(ChatActionType.pinMessage, this.context.dialogId);
		if (this.#isDeletedMessage() || !canPin)
		{
			return null;
		}

		const isPinned = this.store.getters['messages/pin/isPinned']({
			chatId: this.context.chatId,
			messageId: this.context.id,
		});

		return {
			text: isPinned ? Loc.getMessage('IM_DIALOG_CHAT_MENU_UNPIN') : Loc.getMessage('IM_DIALOG_CHAT_MENU_PIN'),
			onclick: () => {
				const messageService = new MessageService({ chatId: this.context.chatId });
				if (isPinned)
				{
					messageService.unpinMessage(this.context.chatId, this.context.id);
				}
				else
				{
					messageService.pinMessage(this.context.chatId, this.context.id);
				}
				this.menuInstance.close();
			},
		};
	}

	getFavoriteItem(): MenuItem
	{
		if (this.#isDeletedMessage())
		{
			return null;
		}

		const isInFavorite = this.store.getters['sidebar/favorites/isFavoriteMessage'](this.context.chatId, this.context.id);

		const menuItemText = isInFavorite
			? Loc.getMessage('IM_DIALOG_CHAT_MENU_REMOVE_FROM_SAVED')
			: Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE')
		;

		return {
			text: menuItemText,
			onclick: () => {
				const messageService = new MessageService({ chatId: this.context.chatId });
				if (isInFavorite)
				{
					messageService.removeMessageFromFavorite(this.context.id);
				}
				else
				{
					messageService.addMessageToFavorite(this.context.id);
				}

				this.menuInstance.close();
			},
		};
	}

	getMarkItem(): ?MenuItem
	{
		const canUnread = this.context.viewed && !this.#isOwnMessage();

		const dialog: ImModelChat = this.store.getters['chats/getByChatId'](this.context.chatId);
		const isMarked = this.context.id === dialog.markedId;
		if (!canUnread || isMarked)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_MARK'),
			onclick: () => {
				const messageService = new MessageService({ chatId: this.context.chatId });
				messageService.markMessage(this.context.id);
				this.menuInstance.close();
			},
		};
	}

	getCreateItem(): MenuItem
	{
		if (this.#isDeletedMessage())
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE'),
			items: [
				this.getCreateTaskItem(),
				this.getCreateMeetingItem(),
				...this.getMarketItems(),
			],
		};
	}

	getCreateTaskItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_TASK'),
			onclick: () => {
				const entityCreator = new EntityCreator(this.context.chatId);
				void entityCreator.createTaskForMessage(this.context.id);
				this.menuInstance.close();
			},
		};
	}

	getCreateMeetingItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_MEETING'),
			onclick: () => {
				const entityCreator = new EntityCreator(this.context.chatId);
				void entityCreator.createMeetingForMessage(this.context.id);
				this.menuInstance.close();
			},
		};
	}

	getEditItem(): ?MenuItem
	{
		if (!this.#isOwnMessage() || this.#isDeletedMessage() || this.#isForwardedMessage())
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_EDIT'),
			onclick: () => {
				EventEmitter.emit(EventType.textarea.editMessage, {
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}

	getDeleteItem(): ?MenuItem
	{
		if (this.#isDeletedMessage())
		{
			return null;
		}

		const permissionManager = PermissionManager.getInstance();
		const canDeleteOthersMessage = permissionManager.canPerformAction(
			ChatActionType.deleteOthersMessage,
			this.context.dialogId,
		);
		if (!this.#isOwnMessage() && !canDeleteOthersMessage)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_DELETE'),
			className: 'menu-popup-no-icon bx-im-dialog-chat__message-menu_delete',
			onclick: this.#onDelete.bind(this),
		};
	}

	getMarketItems(): MenuItem[]
	{
		const { dialogId, id } = this.context;
		const placements = this.marketManager.getAvailablePlacementsByType(PlacementType.contextMenu, dialogId);
		const marketMenuItem = [];
		if (placements.length > 0)
		{
			marketMenuItem.push(this.getDelimiter());
		}

		const context = { messageId: id, dialogId };

		placements.forEach((placement) => {
			marketMenuItem.push({
				text: placement.title,
				onclick: () => {
					MarketManager.openSlider(placement, context);
					this.menuInstance.close();
				},
			});
		});

		// (10 items + 1 delimiter), because we don't want to show long context menu.
		const itemLimit = 11;

		return marketMenuItem.slice(0, itemLimit);
	}

	getDownloadFileItem(): ?MenuItem
	{
		const file = this.#getMessageFile();
		if (!file)
		{
			return null;
		}

		return {
			html: Utils.file.createDownloadLink(
				Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILE'),
				file.urlDownload,
				file.name,
			),
			onclick: function() {
				this.menuInstance.close();
			}.bind(this),
		};
	}

	getSaveToDisk(): ?MenuItem
	{
		const file = this.#getMessageFile();
		if (!file)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK'),
			onclick: function() {
				void this.diskService.save(file.id).then(() => {
					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK_SUCCESS'),
					});
				});
				this.menuInstance.close();
			}.bind(this),
		};
	}

	getDelimiter(): MenuItem
	{
		return {
			delimiter: true,
		};
	}

	#isOwnMessage(): boolean
	{
		return this.context.authorId === Core.getUserId();
	}

	#isDeletedMessage(): boolean
	{
		return this.context.isDeleted;
	}

	#getMessageFile(): ?ImModelFile
	{
		if (this.context.files.length !== 1)
		{
			return null;
		}

		// for now, we have only one file in one message. In the future we need to change this logic.
		return this.store.getters['files/get'](this.context.files[0]);
	}

	#isForwardedMessage(): boolean
	{
		return Type.isStringFilled(this.context.forward.id);
	}

	async #onDelete()
	{
		const { id: messageId, dialogId, chatId } = this.context;
		Analytics.getInstance().messageDelete.onClickDelete({ messageId, dialogId });
		this.menuInstance.close();

		if (await this.#isDeletionCancelled())
		{
			return;
		}

		const messageService = new MessageService({ chatId });
		void messageService.deleteMessage(messageId);
	}

	async #isDeletionCancelled(): Promise<boolean>
	{
		const { id: messageId, dialogId } = this.context;
		if (!ChannelManager.isChannel(dialogId))
		{
			return false;
		}

		const confirmResult = await showDeleteChannelPostConfirm();
		if (!confirmResult)
		{
			Analytics.getInstance().messageDelete.onCancel({ messageId, dialogId });

			return true;
		}

		return false;
	}
}
