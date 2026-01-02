import { Layout } from 'im.v2.const';
import { ChatContent } from 'im.v2.component.content.chat';
import { CreateChatContent, UpdateChatContent } from 'im.v2.component.content.chat-forms.forms';
import { MarketContent } from 'im.v2.component.content.market';
import { NotificationContent } from 'im.v2.component.content.notification';
import { OpenlinesContent } from 'im.v2.component.content.openlines';
import { OpenlinesV2Content } from 'im.v2.component.content.openlinesV2';
import { SettingsContent } from 'im.v2.component.content.settings';
import { ChannelListContainer } from 'im.v2.component.list.container.channel';
import { CollabListContainer } from 'im.v2.component.list.container.collab';
import { CopilotListContainer } from 'im.v2.component.list.container.copilot';
import { OpenlineListContainer } from 'im.v2.component.list.container.openline';
import { RecentListContainer } from 'im.v2.component.list.container.recent';
import { TaskListContainer } from 'im.v2.component.list.container.task';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { LayoutType } from 'im.v2.const';

type ComponentMap = Record<LayoutType, { list: BitrixVueComponentProps, content: BitrixVueComponentProps }>

export const LayoutComponentMap: ComponentMap = {
	[Layout.chat]: {
		list: RecentListContainer,
		content: ChatContent,
	},
	[Layout.createChat]: {
		list: RecentListContainer,
		content: CreateChatContent,
	},
	[Layout.updateChat]: {
		list: RecentListContainer,
		content: UpdateChatContent,
	},
	[Layout.channel]: {
		list: ChannelListContainer,
		content: ChatContent,
	},
	[Layout.notification]: {
		list: RecentListContainer,
		content: NotificationContent,
	},
	[Layout.openlines]: {
		content: OpenlinesContent,
	},
	[Layout.openlinesV2]: {
		list: OpenlineListContainer,
		content: OpenlinesV2Content,
	},
	[Layout.conference]: {
		list: RecentListContainer,
		content: ChatContent,
	},
	[Layout.settings]: {
		content: SettingsContent,
	},
	[Layout.copilot]: {
		list: CopilotListContainer,
		content: ChatContent,
	},
	[Layout.collab]: {
		list: CollabListContainer,
		content: ChatContent,
	},
	[Layout.market]: {
		content: MarketContent,
	},
	[Layout.taskComments]: {
		list: TaskListContainer,
		content: ChatContent,
	},
};
