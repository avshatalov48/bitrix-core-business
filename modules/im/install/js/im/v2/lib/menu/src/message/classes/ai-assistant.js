import { Loc } from 'main.core';
import { Outline as OutlineIcons } from 'ui.icon-set.api.core';

import { Core } from 'im.v2.application.core';
import { FeedbackManager } from 'im.v2.lib.feedback';

import { MessageMenu } from './message-base';

import type { MenuItemOptions } from 'ui.system.menu';

const MenuSectionCode = Object.freeze({
	main: 'main',
	select: 'select',
	create: 'create',
	market: 'market',
});

export class AiAssistantMessageMenu extends MessageMenu
{
	getMenuItems(): MenuItemOptions[]
	{
		const mainGroupItems = [
			this.getCopyItem(),
			this.getDownloadFileItem(),
			this.getForwardItem(),
			...this.getAdditionalItems(),
		];

		return this.groupItems(mainGroupItems, MenuSectionCode.main);
	}

	getNestedItems(): MenuItemOptions[]
	{
		const mainGroupItems = [
			this.getCopyFileItem(),
			this.getMarkItem(),
			this.getFavoriteItem(),
			this.getSaveToDiskItem(),
		];

		const createGroupItems = [
			this.getSendFeedbackItem(),
			this.getCreateTaskItem(),
			this.getCreateMeetingItem(),
		];

		return [
			...this.groupItems(mainGroupItems, MenuSectionCode.main),
			...this.groupItems(createGroupItems, MenuSectionCode.create),
			...this.groupItems(this.getMarketItems(), MenuSectionCode.market),
		];
	}

	getSendFeedbackItem(): MenuItemOptions
	{
		const isAiAssistantBot = Core.getStore().getters['users/bots/isAiAssistant'](this.context.authorId);

		if (!isAiAssistantBot)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_LIB_MENU_AI_ASSISTANT_FEEDBACK'),
			icon: OutlineIcons.FEEDBACK,
			onClick: () => {
				void (new FeedbackManager()).openAiAssistantForm();
				this.menuInstance.close();
			},
		};
	}
}
