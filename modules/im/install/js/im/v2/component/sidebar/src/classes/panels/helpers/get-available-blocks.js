import { Core } from 'im.v2.application.core';
import { MainPanelBlock, PlacementType } from 'im.v2.const';
import { MarketManager } from 'im.v2.lib.market';
import { SettingsManager } from '../../settings-manager';

export const MainPanelBlockOrder = Object.freeze({
	[MainPanelBlock.chat]: 10,
	[MainPanelBlock.user]: 10,
	[MainPanelBlock.info]: 20,
	[MainPanelBlock.file]: 30,
	[MainPanelBlock.fileUnsorted]: 30,
	[MainPanelBlock.task]: 40,
	[MainPanelBlock.meeting]: 50,
	[MainPanelBlock.market]: 60,
});

const settingsManager = new SettingsManager();

export function getAvailableBlocks(dialogId: string): $Keys<typeof MainPanelBlock>[]
{
	const blocks = Object.values(MainPanelBlock);
	const availableBlocks = filterUnavailableBlocks(dialogId, blocks);

	return sortBlocks(availableBlocks);
}

function filterUnavailableBlocks(dialogId: string, blocks: string[]): string[]
{
	const blocksSet = new Set(blocks);

	if (isChat(dialogId))
	{
		blocksSet.delete(MainPanelBlock.user);
	}
	else
	{
		blocksSet.delete(MainPanelBlock.chat);
	}

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

function sortBlocks(availableBlocks: string[]): string[]
{
	return [...availableBlocks].sort((block1, block2) => {
		return MainPanelBlockOrder[block1] - MainPanelBlockOrder[block2];
	});
}

function isBot(dialogId: string): boolean
{
	const user = Core.getStore().getters['users/get'](dialogId);

	return user?.bot === true;
}

function isChat(dialogId: string): boolean
{
	return dialogId.startsWith('chat');
}

function isFileMigrationFinished(): boolean
{
	return settingsManager.isFileMigrationFinished();
}

function hasMarketApps(dialogId: string): boolean
{
	return MarketManager.getInstance().getAvailablePlacementsByType(PlacementType.sidebar, dialogId).length > 0;
}
