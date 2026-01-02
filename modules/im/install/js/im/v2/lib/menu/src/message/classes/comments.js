import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Outline as OutlineIcons } from 'ui.icon-set.api.core';

import { EventType } from 'im.v2.const';
import { ChannelManager } from 'im.v2.lib.channel';

import { MessageMenu } from './message-base';

import type { MenuItemOptions, MenuSectionOptions } from 'ui.system.menu';

const MenuSectionCode = Object.freeze({
	main: 'main',
	select: 'select',
	file: 'file',
	open: 'open',
	create: 'create',
});

export class CommentsMessageMenu extends MessageMenu
{
	getMenuItems(): MenuItemOptions[]
	{
		const message = this.context;
		const contextDialogId = this.context.dialogId;
		if (ChannelManager.isCommentsPostMessage(message, contextDialogId))
		{
			const mainGroupItems = [
				this.getCopyItem(),
				this.getCopyFileItem(),
			];
			const fileGroupItems = [
				this.getDownloadFileItem(),
				this.getSaveToDiskItem(),
			];

			return [
				...this.groupItems(mainGroupItems, MenuSectionCode.main),
				...this.groupItems(fileGroupItems, MenuSectionCode.file),
				...this.groupItems([this.getOpenInChannelItem()], MenuSectionCode.open),
			];
		}

		return [
			this.getReplyItem(),
			this.getCopyItem(),
			this.getEditItem(),
			this.getDownloadFileItem(),
			...this.getAdditionalItems(),
			this.getDeleteItem(),
		];
	}

	getMenuGroups(): MenuSectionOptions[]
	{
		return [
			{ code: MenuSectionCode.main },
			{ code: MenuSectionCode.file },
			{ code: MenuSectionCode.open },
		];
	}

	getNestedMenuGroups(): MenuSectionOptions[]
	{
		return [
			{ code: MenuSectionCode.main },
			{ code: MenuSectionCode.create },
		];
	}

	getNestedItems(): MenuItemOptions[]
	{
		const mainGroupItems = [
			this.getCopyFileItem(),
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
		];
	}

	getOpenInChannelItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_MENU_COMMENTS_OPEN_IN_CHANNEL'),
			icon: OutlineIcons.GO_TO_MESSAGE,
			onClick: () => {
				EventEmitter.emit(EventType.dialog.closeComments);

				this.menuInstance.close();
			},
		};
	}
}
