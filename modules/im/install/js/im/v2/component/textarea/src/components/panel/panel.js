import { TextareaPanelType as PanelType } from 'im.v2.const';

import { EditPanel } from './components/edit-panel';
import { ReplyPanel } from './components/reply-panel';
import { ForwardPanel } from './components/forward-panel';
import { ForwardEntityPanel } from './components/forward-entity-panel';
import { MarketAppsPanel } from './components/market-apps-panel/market-apps-panel';

import type { PanelContext } from 'im.v2.provider.service';

// @vue/component
export const TextareaPanel = {
	name: 'TextareaPanel',
	components: { EditPanel, ReplyPanel, ForwardPanel, ForwardEntityPanel, MarketAppsPanel },
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
		context: {
			type: Object,
			required: true,
		},
	},
	emits: ['close'],
	computed:
	{
		PanelType: () => PanelType,
		configContext(): PanelContext
		{
			return this.context;
		},
	},
	template: `
		<EditPanel v-if="type === PanelType.edit" :messageId="configContext.messageId" @close="$emit('close')" />
		<ReplyPanel v-if="type === PanelType.reply" :messageId="configContext.messageId" @close="$emit('close')" />
		<ForwardPanel v-if="type === PanelType.forward" :context="configContext" @close="$emit('close')" />
		<ForwardEntityPanel v-if="type === PanelType.forwardEntity" :context="configContext" @close="$emit('close')" />
		<MarketAppsPanel v-if="type === PanelType.market" :dialogId="dialogId" />
	`,
};
