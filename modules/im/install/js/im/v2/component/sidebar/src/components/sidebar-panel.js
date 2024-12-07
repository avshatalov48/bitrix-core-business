import { Text } from 'main.core';

import { MainPanel } from './panels/main/main-panel';
import { TaskPanel } from './panels/task/task-panel';
import { FilePanel } from './panels/file/file-panel';
import { FileUnsortedPanel } from './panels/file-unsorted/file-unsorted-panel';
import { LinkPanel } from './panels/info/link-panel';
import { MarketPanel } from './panels/market/detail';
import { MeetingPanel } from './panels/meeting/meeting-panel';
import { MembersPanel } from './panels/members/members-panel';
import { FavoritePanel } from './panels/info/favorite-panel';
import { MessageSearchPanel } from './panels/message-search/message-search-panel';
import { ChatsWithUserPanel } from './panels/chats-with-user/chats-with-user-panel';
import { MultidialogPanel } from './panels/multidialog/multidialog-panel';

import '../css/sidebar-panel.css';
import '../css/detail.css';

// @vue/component
export const SidebarPanel = {
	name: 'SidebarPanel',
	components: {
		MainPanel,
		ChatsWithUserPanel,
		MembersPanel,
		FavoritePanel,
		LinkPanel,
		FilePanel,
		TaskPanel,
		MeetingPanel,
		MarketPanel,
		MessageSearchPanel,
		FileUnsortedPanel,
		MultidialogPanel,
	},
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		panel: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
		entityId: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		panelComponentName(): string
		{
			return `${Text.capitalize(this.panel)}Panel`;
		},
	},
	template: `
		<div class="bx-im-sidebar-panel__container" :class="{'--second-level': secondLevel}">
			<KeepAlive>
				<component
					:is="panelComponentName"
					:dialogId="dialogId"
					:entityId="entityId"
					:secondLevel="secondLevel"
					class="bx-im-sidebar-panel__component"
				/>
			</KeepAlive>
		</div>
	`,
};
