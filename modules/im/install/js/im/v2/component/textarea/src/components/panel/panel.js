import { TextareaPanelType as PanelType } from 'im.v2.const';

import { EditPanel } from './components/edit-panel';
import { ReplyPanel } from './components/reply-panel';
import { ForwardPanel } from './components/forward-panel';
import { MarketAppsPanel } from './components/market-apps-panel/market-apps-panel';

import type { JsonObject } from 'main.core';

// @vue/component
export const TextareaPanel = {
	name: 'TextareaPanel',
	components: { EditPanel, ReplyPanel, ForwardPanel, MarketAppsPanel },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		type: {
			type: String,
			required: true,
		},
		messageId: {
			type: Number,
			required: true,
		},
	},
	emits: ['close'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		PanelType: () => PanelType,
	},
	template: `
		<EditPanel v-if="type === PanelType.edit" :messageId="messageId" @close="$emit('close')" />
		<ReplyPanel v-if="type === PanelType.reply" :messageId="messageId" @close="$emit('close')" />
		<ForwardPanel v-if="type === PanelType.forward" :messageId="messageId" @close="$emit('close')" />
		<MarketAppsPanel v-if="type === PanelType.market" :dialogId="dialogId"/>
	`,
};
