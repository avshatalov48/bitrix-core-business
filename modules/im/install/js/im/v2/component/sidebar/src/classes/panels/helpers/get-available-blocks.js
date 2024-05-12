import { Core } from 'im.v2.application.core';
import { PlacementType } from 'im.v2.const';
import { MarketManager } from 'im.v2.lib.market';

import { MainPanelBlock } from '../../panel-config';
import { SettingsManager } from '../../settings-manager';
import { getMainBlocksForChat } from './get-main-blocks-for-chat';

const settingsManager = new SettingsManager();

export function getAvailableBlocks(dialogId: string): $Keys<typeof MainPanelBlock>[]
{
	const blocks = getMainBlocksForChat(dialogId);

	return filterUnavailableBlocks(dialogId, blocks);
}

function filterUnavailableBlocks(dialogId: string, blocks: string[]): string[]
{
	const blocksSet = new Set(blocks);

	if (isFileMigrationFinished())
	{
		blocksSet.delete(MainPanelBlock.fileUnsorted);
	}
	else
	{
		blocksSet.delete(MainPanelBlock.file);
	}

	if (!hasMarketApps(dialogId))
	{
		blocksSet.delete(MainPanelBlock.market);
	}

	if (isBot(dialogId))
	{
		blocksSet.delete(MainPanelBlock.task);
		blocksSet.delete(MainPanelBlock.meeting);
	}

	return [...blocksSet];
}

function isBot(dialogId: string): boolean
{
	const user = Core.getStore().getters['users/get'](dialogId);

	return user?.bot === true;
}

function isFileMigrationFinished(): boolean
{
	return settingsManager.isFileMigrationFinished();
}

function hasMarketApps(dialogId: string): boolean
{
	return MarketManager.getInstance().getAvailablePlacementsByType(PlacementType.sidebar, dialogId).length > 0;
}
