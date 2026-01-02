import { ChatManager } from 'im.v2.lib.chat';
import { Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MenuItemDesign } from 'ui.system.menu';
import { Outline as OutlineIcons } from 'ui.icon-set.api.core';

import { PromoManager } from 'im.v2.lib.promo';
import { Analytics } from 'im.v2.lib.analytics';
import { ChannelManager } from 'im.v2.lib.channel';
import { Core } from 'im.v2.application.core';
import { Parser } from 'im.v2.lib.parser';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { MessageService } from 'im.v2.provider.service.message';
import { DiskService } from 'im.v2.provider.service.disk';
import { EventType, PlacementType, ActionByRole, PromoId, GetParameter } from 'im.v2.const';
import { MarketManager } from 'im.v2.lib.market';
import { Utils } from 'im.v2.lib.utils';
import { PermissionManager } from 'im.v2.lib.permission';
import { showDeleteChannelPostConfirm, showDownloadAllFilesConfirm } from 'im.v2.lib.confirm';
import { Notifier } from 'im.v2.lib.notifier';

// noinspection ES6PreferShortImport
import { BaseMenu } from '../../base/base';

import type { ImModelMessage, ImModelChat, ImModelFile } from 'im.v2.model';
import type { MenuItemOptions, MenuOptions, MenuSectionOptions } from 'ui.system.menu';
export type MessageMenuContext = ImModelMessage & { dialogId: string };

const MenuSectionCode = Object.freeze({
	main: 'main',
	select: 'select',
	create: 'create',
	market: 'market',
});

export class MessageMenu extends BaseMenu
{
	context: MessageMenuContext;
	diskService: DiskService;

	maxPins: number = 20;

	constructor()
	{
		super();

		this.id = 'bx-im-message-context-menu';
		this.diskService = new DiskService();
		this.marketManager = MarketManager.getInstance();
	}

	getMenuOptions(): MenuOptions
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: true,
			offsetLeft: 11,
			minWidth: 238,
		};
	}

	getMenuItems(): (MenuItemOptions | null)[]
	{
		const mainGroupItems = [
			this.getReplyItem(),
			this.getCopyItem(),
			this.getEditItem(),
			this.getDownloadFileItem(),
			this.getPinItem(),
			this.getForwardItem(),
			...this.getAdditionalItems(),
			this.getDeleteItem(),
		];

		return [
			...this.groupItems(mainGroupItems, MenuSectionCode.main),
			...this.groupItems([this.getSelectItem()], MenuSectionCode.select),
		];
	}

	getMenuGroups(): MenuSectionOptions[]
	{
		return [
			{ code: MenuSectionCode.main },
			{ code: MenuSectionCode.select },
		];
	}

	getNestedMenuGroups(): MenuSectionOptions[]
	{
		return [
			{ code: MenuSectionCode.main },
			{ code: MenuSectionCode.create },
			{ code: MenuSectionCode.market },
		];
	}

	getSelectItem(): ?MenuItemOptions
	{
		if (this.#isDeletedMessage() || !this.#isRealMessage())
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_SELECT'),
			icon: OutlineIcons.CIRCLE_CHECK,
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onSelect(this.context.dialogId);

				EventEmitter.emit(EventType.dialog.openBulkActionsMode, {
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}

	getReplyItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_REPLY'),
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onReply(this.context.dialogId);
				EventEmitter.emit(EventType.textarea.replyMessage, {
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
			icon: OutlineIcons.QUOTE,
		};
	}

	getForwardItem(): ?MenuItemOptions
	{
		if (this.#isDeletedMessage() || !this.#isRealMessage())
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_FORWARD'),
			icon: OutlineIcons.FORWARD,
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onForward(this.context.dialogId);
				EventEmitter.emit(EventType.dialog.showForwardPopup, {
					messagesIds: [this.context.id],
				});
				this.menuInstance.close();
			},
		};
	}

	getCopyItem(): ?MenuItemOptions
	{
		if (this.#isDeletedMessage() || this.context.text.trim().length === 0)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY'),
			onClick: async () => {
				Analytics.getInstance().messageContextMenu.onCopyText({
					dialogId: this.context.dialogId,
					messageId: this.context.id,
				});

				const textToCopy = Parser.prepareCopy(this.context);

				await Utils.text.copyToClipboard(textToCopy);
				Notifier.message.onCopyComplete();

				this.menuInstance.close();
			},
			icon: OutlineIcons.COPY,
		};
	}

	getCopyLinkItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_LINK_MSGVER_1'),
			icon: OutlineIcons.LINK,
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onCopyLink(this.context.dialogId);

				const textToCopy = ChatManager.buildMessageLink(this.context.dialogId, this.context.id);
				if (BX.clipboard?.copy(textToCopy))
				{
					Notifier.message.onCopyLinkComplete();
				}
				this.menuInstance.close();
			},
		};
	}

	getCopyFileItem(): ?MenuItemOptions
	{
		if (this.context.files.length !== 1)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE'),
			icon: OutlineIcons.COPY,
			onClick: () => {
				const fileId = this.context.files[0];
				Analytics.getInstance().messageContextMenu.onCopyFile({
					dialogId: this.context.dialogId,
					fileId,
				});

				const textToCopy = Parser.prepareCopyFile(this.context);
				if (BX.clipboard?.copy(textToCopy))
				{
					Notifier.file.onCopyComplete();
				}
				this.menuInstance.close();
			},
		};
	}

	getPinItem(): ?MenuItemOptions
	{
		const canPin = PermissionManager.getInstance().canPerformActionByRole(
			ActionByRole.pinMessage,
			this.context.dialogId,
		);

		if (this.#isDeletedMessage() || !canPin)
		{
			return null;
		}

		const isPinned = this.store.getters['messages/pin/isPinned']({
			chatId: this.context.chatId,
			messageId: this.context.id,
		});

		return {
			title: isPinned ? Loc.getMessage('IM_DIALOG_CHAT_MENU_UNPIN') : Loc.getMessage('IM_DIALOG_CHAT_MENU_PIN'),
			icon: OutlineIcons.PIN,
			onClick: () => {
				const messageService = new MessageService({ chatId: this.context.chatId });
				if (isPinned)
				{
					messageService.unpinMessage(this.context.chatId, this.context.id);
					Analytics.getInstance().messageContextMenu.onUnpin(this.context.dialogId);
				}
				else
				{
					if (this.#arePinsExceedLimit())
					{
						Notifier.chat.onMessagesPinLimitError(this.maxPins);
						Analytics.getInstance().messageContextMenu.onReachingPinsLimit(this.context.dialogId);
						this.menuInstance.close();

						return;
					}

					messageService.pinMessage(this.context.chatId, this.context.id);
					Analytics.getInstance().messageContextMenu.onPin(this.context.dialogId);
				}
				this.menuInstance.close();
			},
		};
	}

	getFavoriteItem(): MenuItemOptions
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
			title: menuItemText,
			icon: OutlineIcons.FAVORITE,
			onClick: () => {
				const messageService = new MessageService({ chatId: this.context.chatId });
				if (isInFavorite)
				{
					messageService.removeMessageFromFavorite(this.context.id);
				}
				else
				{
					Analytics.getInstance().messageContextMenu.onAddFavorite({
						dialogId: this.context.dialogId,
						messageId: this.context.id,
					});

					messageService.addMessageToFavorite(this.context.id);
				}

				this.menuInstance.close();
			},
		};
	}

	getMarkItem(): ?MenuItemOptions
	{
		const canUnread = this.context.viewed && !this.#isOwnMessage();

		const dialog: ImModelChat = this.store.getters['chats/getByChatId'](this.context.chatId);
		const isMarked = this.context.id === dialog.markedId;
		if (!canUnread || isMarked)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_MARK'),
			icon: OutlineIcons.NEW_MESSAGE,
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onMark(this.context.dialogId);

				const messageService = new MessageService({ chatId: this.context.chatId });
				messageService.markMessage(this.context.id);
				this.menuInstance.close();
			},
		};
	}

	getCreateTaskItem(): ?MenuItemOptions
	{
		if (this.#isDeletedMessage())
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_TASK_MSGVER_1'),
			icon: OutlineIcons.TASK,
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onCreateTask(this.context.dialogId);

				const entityCreator = new EntityCreator(this.context.chatId);
				void entityCreator.createTaskForMessage(this.context.id);
				this.menuInstance.close();
			},
		};
	}

	getCreateMeetingItem(): ?MenuItemOptions
	{
		if (this.#isDeletedMessage())
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_MEETING_MSGVER_1'),
			icon: OutlineIcons.CALENDAR_WITH_SLOTS,
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onCreateEvent(this.context.dialogId);

				const entityCreator = new EntityCreator(this.context.chatId);
				void entityCreator.createMeetingForMessage(this.context.id);
				this.menuInstance.close();
			},
		};
	}

	getEditItem(): ?MenuItemOptions
	{
		if (
			!this.#isOwnMessage()
			|| this.#isDeletedMessage()
			|| this.#isForwardedMessage()
		)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_EDIT'),
			icon: OutlineIcons.EDIT_L,
			onClick: () => {
				Analytics.getInstance().messageContextMenu.onEdit(this.context.dialogId);

				EventEmitter.emit(EventType.textarea.editMessage, {
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}

	getDeleteItem(): ?MenuItemOptions
	{
		if (this.#isDeletedMessage())
		{
			return null;
		}

		const permissionManager = PermissionManager.getInstance();
		const canDeleteOthersMessage = permissionManager.canPerformActionByRole(
			ActionByRole.deleteOthersMessage,
			this.context.dialogId,
		);
		if (!this.#isOwnMessage() && !canDeleteOthersMessage)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_DELETE'),
			design: MenuItemDesign.Alert,
			icon: OutlineIcons.TRASHCAN,
			onClick: this.#onDelete.bind(this),
		};
	}

	getMarketItems(): MenuItemOptions[]
	{
		const { dialogId, id } = this.context;
		const placements = this.marketManager.getAvailablePlacementsByType(PlacementType.contextMenu, dialogId);
		const marketMenuItem = [];

		const context = { messageId: id, dialogId };

		placements.forEach((placement) => {
			marketMenuItem.push({
				title: placement.title,
				icon: OutlineIcons.MARKET,
				onClick: () => {
					void MarketManager.openSlider(placement, context);
					this.menuInstance.close();
				},
			});
		});

		const MARKET_ITEMS_LIMIT = 10;

		return marketMenuItem.slice(0, MARKET_ITEMS_LIMIT);
	}

	getDownloadFileItem(): ?MenuItemOptions
	{
		if (!Type.isArrayFilled(this.context.files))
		{
			return null;
		}

		if (this.#isSingleFile())
		{
			return this.#getDownloadSingleFileItem();
		}

		return this.#getDownloadSeveralFilesItem();
	}

	getSaveToDiskItem(): ?MenuItemOptions
	{
		if (!Type.isArrayFilled(this.context.files))
		{
			return null;
		}

		const menuItemText = this.#isSingleFile()
			? Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK_MSGVER_1')
			: Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ALL_ON_DISK');

		return {
			title: menuItemText,
			icon: OutlineIcons.FOLDER_24,
			onClick: async function() {
				Analytics.getInstance().messageContextMenu.onSaveOnDisk({
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});

				this.menuInstance.close();
				await this.diskService.save(this.context.files);
				Notifier.file.onDiskSaveComplete(this.#isSingleFile());
			}.bind(this),
		};
	}

	getAdditionalItems(): MenuItemOptions[]
	{
		const items = this.getNestedItems();
		if (this.#needNestedMenu(items))
		{
			return [{
				title: Loc.getMessage('IM_DIALOG_CHAT_MENU_MORE'),
				subMenu: {
					items,
					sections: this.getNestedMenuGroups(),
				},
			}];
		}

		return items;
	}

	getNestedItems(): MenuItemOptions[]
	{
		const mainGroupItems = [
			this.getCopyLinkItem(),
			this.getCopyFileItem(),
			this.getMarkItem(),
			this.getFavoriteItem(),
			this.getSaveToDiskItem(),
		];

		const createGroupItems = [
			this.getCreateTaskItem(),
			this.getCreateMeetingItem(),
		];

		return [
			...this.groupItems(mainGroupItems, MenuSectionCode.main),
			...this.groupItems(createGroupItems, MenuSectionCode.create),
			...this.groupItems(this.getMarketItems(), MenuSectionCode.market),
		];
	}

	#needNestedMenu(additionalItems: MenuItemOptions[]): boolean
	{
		const NESTED_MENU_MIN_ITEMS = 3;
		const menuItems = additionalItems.filter((item) => item !== null);

		return menuItems.length >= NESTED_MENU_MIN_ITEMS;
	}

	#isOwnMessage(): boolean
	{
		return this.context.authorId === Core.getUserId();
	}

	#isDeletedMessage(): boolean
	{
		return this.context.isDeleted;
	}

	#getFirstFile(): ?ImModelFile
	{
		return this.store.getters['files/get'](this.context.files[0]);
	}

	#isSingleFile(): boolean
	{
		return this.context.files.length === 1;
	}

	#isForwardedMessage(): boolean
	{
		return Type.isStringFilled(this.context.forward.id);
	}

	#isRealMessage(): boolean
	{
		return this.store.getters['messages/isRealMessage'](this.context.id);
	}

	async #onDelete()
	{
		const { id: messageId, dialogId, chatId } = this.context;
		Analytics.getInstance().messageContextMenu.onDelete({ messageId, dialogId });
		this.menuInstance.close();

		if (await this.#isDeletionCancelled())
		{
			return;
		}

		const messageService = new MessageService({ chatId });
		messageService.deleteMessages([messageId]);
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
			Analytics.getInstance().messageContextMenu.onCancelDelete({ messageId, dialogId });

			return true;
		}

		return false;
	}

	#getDownloadSingleFileItem(): MenuItemOptions
	{
		const file = this.#getFirstFile();

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILE'),
			icon: OutlineIcons.DOWNLOAD,
			onClick: function() {
				Utils.file.downloadFiles([file]);

				Analytics.getInstance().messageContextMenu.onFileDownload({
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			}.bind(this),
		};
	}

	#getDownloadSeveralFilesItem(): MenuItemOptions
	{
		const files: ImModelFile[] = this.context.files.map((fileId) => {
			return this.store.getters['files/get'](fileId);
		});

		return {
			title: Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILES'),
			icon: OutlineIcons.DOWNLOAD,
			onClick: async () => {
				Analytics.getInstance().messageContextMenu.onFileDownload({
					messageId: this.context.id,
					dialogId: this.context.dialogId,
				});
				Utils.file.downloadFiles(files);

				const needToShowPopup = PromoManager.getInstance().needToShow(PromoId.downloadSeveralFiles);
				if (needToShowPopup && Utils.browser.isChrome() && !Utils.platform.isBitrixDesktop())
				{
					this.menuInstance?.close();
					await showDownloadAllFilesConfirm();
					void PromoManager.getInstance().markAsWatched(PromoId.downloadSeveralFiles);
				}
				this.menuInstance?.close();
			},
		};
	}

	#arePinsExceedLimit(): string
	{
		const pins = this.store.getters['messages/pin/getPinned'](this.context.chatId);

		return pins.length >= this.maxPins;
	}
}
