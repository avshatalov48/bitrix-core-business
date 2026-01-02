import { Loc } from 'main.core';

import { baseBlocks } from '../configs/base';

import type { SidebarMainPanelBlockType } from 'im.v2.const';

type SidebarPresetOptions = {
	blocks: SidebarMainPanelBlockType[],
	getHeaderTitle?: () => string,
	getCustomDescription?: () => string,
	areChatMembersEnabled?: () => boolean,
	isHeaderMenuEnabled?: () => boolean,
	areSharedChatsEnabled?: () => boolean,
	isAutoDeleteEnabled?: () => boolean,
};

export class SidebarPreset
{
	#configTemplate: SidebarPresetOptions;

	constructor(rawConfig: SidebarPresetOptions = {})
	{
		this.#configTemplate = { ...this.#getDefaultConfig(), ...rawConfig };
	}

	get(): SidebarPresetOptions
	{
		return this.#configTemplate;
	}

	#getDefaultConfig(): SidebarPresetOptions
	{
		return {
			blocks: baseBlocks,
			getHeaderTitle: () => Loc.getMessage('IM_SIDEBAR_HEADER_TITLE'),
			isHeaderMenuEnabled: () => true,
			areSharedChatsEnabled: () => true,
			areChatMembersEnabled: () => true,
			isAutoDeleteEnabled: () => true,
			getCustomDescription: () => '',
		};
	}
}
