import { Core } from 'im.v2.application.core';
import { ChatType, PlacementType, SidebarMainPanelBlock } from 'im.v2.const';
import { ChannelManager } from 'im.v2.lib.channel';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { MarketManager } from 'im.v2.lib.market';

import type { ImModelChat } from 'im.v2.model';
import type { SidebarMainPanelBlockType } from 'im.v2.const';

export class BlockFilter
{
	#dialogId: string;
	#chat: ImModelChat;
	#blocks: SidebarMainPanelBlockType[];

	constructor(dialogId: string, blocks: SidebarMainPanelBlockType[])
	{
		this.#dialogId = dialogId;
		this.#chat = Core.getStore().getters['chats/get'](dialogId, true);
		this.#blocks = blocks;
	}

	run(): SidebarMainPanelBlockType[]
	{
		const blocksSet = new Set(this.#blocks);

		if (this.#isFileMigrationFinished())
		{
			blocksSet.delete(SidebarMainPanelBlock.fileUnsortedList);
		}
		else
		{
			blocksSet.delete(SidebarMainPanelBlock.fileList);
		}

		if (!this.#hasMarketApps())
		{
			blocksSet.delete(SidebarMainPanelBlock.marketAppList);
		}

		if (!this.#hasHistoryLimit())
		{
			blocksSet.delete(SidebarMainPanelBlock.tariffLimit);
		}

		return [...blocksSet];
	}

	#isFileMigrationFinished(): boolean
	{
		return FeatureManager.isFeatureAvailable(Feature.sidebarFiles);
	}

	#hasMarketApps(): boolean
	{
		return MarketManager.getInstance().getAvailablePlacementsByType(PlacementType.sidebar, this.#dialogId).length > 0;
	}

	#hasHistoryLimit(): boolean
	{
		const isChannelCommentsChat = ChatType.comment === this.#chat.type;
		const isChannelChat = ChannelManager.isChannel(this.#dialogId);

		if (isChannelChat || isChannelCommentsChat || FeatureManager.chatHistory.isAvailable())
		{
			return false;
		}

		return Core.getStore().getters['sidebar/hasHistoryLimit'](this.#chat.chatId);
	}
}
