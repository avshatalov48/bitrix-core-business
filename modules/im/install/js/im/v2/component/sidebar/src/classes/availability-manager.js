import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {PlacementType, SidebarBlock} from 'im.v2.const';
import {MarketManager} from 'im.v2.lib.market';

import {SettingsManager} from './settings-manager';

const BLOCKS_ORDER = {
	[SidebarBlock.main]: 10,
	[SidebarBlock.info]: 20,
	[SidebarBlock.file]: 30,
	[SidebarBlock.brief]: 40,
	[SidebarBlock.sign]: 50,
	[SidebarBlock.task]: 60,
	[SidebarBlock.meeting]: 70,
};

export class AvailabilityManager
{
	#settingsManager: SettingsManager = null;
	#store: Store;
	#dialogId: string;

	constructor(settingsManager: SettingsManager, dialogId: string)
	{
		this.#settingsManager = settingsManager;
		this.#store = Core.getStore();
		this.#dialogId = dialogId;
	}

	getBlocks(): string[]
	{
		const blocksFromSetting = this.#settingsManager.getBlocks();
		const availableBlocks = this.#filterUnavailableBlocks(blocksFromSetting);

		return this.#sortBlocks(availableBlocks);
	}

	#filterUnavailableBlocks(blocks: string[]): string[]
	{
		const blocksSet = new Set(blocks);

		if (this.#isFileMigrationFinished())
		{
			blocksSet.delete(SidebarBlock.fileUnsorted);
		}
		else
		{
			blocksSet.delete(SidebarBlock.brief);
			blocksSet.delete(SidebarBlock.file);
		}

		if (!this.#canShowBriefs())
		{
			blocksSet.delete(SidebarBlock.brief);
		}

		if (!this.#hasMarketApps())
		{
			blocksSet.delete(SidebarBlock.market);
		}

		return [...blocksSet];
	}

	#isFileMigrationFinished(): boolean
	{
		return this.#settingsManager.isFileMigrationFinished();
	}

	#canShowBriefs(): boolean
	{
		return this.#settingsManager.canShowBriefs();
	}

	#hasMarketApps(): boolean
	{
		return MarketManager.getInstance().getAvailablePlacementsByType(PlacementType.sidebar, this.#dialogId).length > 0;
	}

	#sortBlocks(availableBlocks: string[]): string[]
	{
		return [...availableBlocks].sort((block1, block2) => {
			return BLOCKS_ORDER[block1] - BLOCKS_ORDER[block2];
		});
	}
}