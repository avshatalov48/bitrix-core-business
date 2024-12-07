import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { MessageMenu } from 'im.v2.component.message-list';
import { EventType } from 'im.v2.const';

import type { MenuItem } from 'im.v2.lib.menu';
import type { ImModelChat } from 'im.v2.model';

export class CommentsMessageMenu extends MessageMenu
{
	getMenuItems(): MenuItem[]
	{
		if (this.isPostMessage())
		{
			return [
				this.getCopyItem(),
				this.getCopyFileItem(),
				this.getDelimiter(),
				this.getDownloadFileItem(),
				this.getSaveToDisk(),
				this.getDelimiter(),
				this.getOpenInChannelItem(),
			];
		}

		return [
			this.getReplyItem(),
			this.getCopyItem(),
			this.getCopyFileItem(),
			// this.getPinItem(),
			// this.getForwardItem(),
			this.getDelimiter(),
			// this.getMarkItem(),
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

	getOpenInChannelItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_CONTENT_COMMENTS_MESSAGE_MENU_OPEN_IN_CHANNEL'),
			onclick: () => {
				EventEmitter.emit(EventType.dialog.closeComments);

				this.menuInstance.close();
			},
		};
	}

	isPostMessage(): boolean
	{
		const { dialogId }: ImModelChat = this.store.getters['chats/getByChatId'](this.context.chatId);

		return dialogId !== this.context.dialogId;
	}
}
