import { Extension, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { SidebarFileTabTypes, SidebarDetailBlock, EventType } from 'im.v2.const';

import { DetailTabs } from './components/detail-tabs';
import { MediaTab } from './components/media-tab';
import { AudioTab } from './components/audio-tab';
import { BriefTab } from './components/brief-tab';
import { OtherTab } from './components/other-tab';
import { DocumentTab } from './components/document-tab';
import { DetailHeader } from '../../elements/detail-header/detail-header';

import type { JsonObject } from 'main.core';

// @vue/component
export const FilePanel = {
	name: 'FilePanel',
	components: { DetailHeader, DetailTabs, MediaTab, AudioTab, DocumentTab, BriefTab, OtherTab },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			tab: SidebarFileTabTypes.media,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		tabComponentName(): string
		{
			return `${Text.capitalize(this.tab)}Tab`;
		},
		tabs(): string[]
		{
			const tabTypes = Object.values(SidebarFileTabTypes);
			const settings = Extension.getSettings('im.v2.component.sidebar');
			const canShowBriefs = settings.get('canShowBriefs', false);
			if (!canShowBriefs)
			{
				return tabTypes.filter((tab) => tab !== SidebarDetailBlock.brief);
			}

			return tabTypes;
		},
	},
	methods:
	{
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.file });
		},
		onTabSelect(tabName: $Keys<typeof SidebarFileTabTypes>)
		{
			this.tab = tabName;
		},
	},
	template: `
		<div>
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEDIA_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<DetailTabs :tabs="tabs" @tabSelect="onTabSelect" />
			<KeepAlive>
				<component :is="tabComponentName" :dialogId="dialogId" />
			</KeepAlive>
		</div>
	`,
};
