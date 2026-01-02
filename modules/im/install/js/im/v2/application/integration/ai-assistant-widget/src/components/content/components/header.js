import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';
import { EventEmitter } from 'main.core.events';

import { ChatHeader } from 'im.v2.component.content.elements';
import { Color } from 'im.v2.const';

import '../css/header.css';

const MINIMIZE_EVENT_NAME = 'IM.AiAssistantWidget:minimize';

// @vue/component
export const AiAssistantWidgetChatHeader = {
	name: 'AiAssistantWidgetChatHeader',
	components: { ChatHeader, BIcon },
	props: {
		dialogId: {
			type: String,
			default: '',
		},
	},
	computed: {
		OutlineIcons: () => OutlineIcons,
		Color: () => Color,
	},
	methods: {
		onMinimize()
		{
			EventEmitter.emit(MINIMIZE_EVENT_NAME);
		},
		loc(phrase: string): string
		{
			return this.$Bitrix.Loc.getMessage(phrase);
		},
	},
	template: `
		<ChatHeader
			:dialogId="dialogId"
			:withCallButton="false"
			:withSearchButton="false"
			:withAddToChatButton="false"
		>
			<template #before-actions>
				<BIcon
					:name="OutlineIcons.CROSS_L"
					:hoverable="true"
					:color="Color.base4"
					:title="loc('IM_AI_ASSISTANT_WIDGET_MINIMIZE')"
					class="bx-im-ai-assistant-chat-header__icon"
					@click="onMinimize"
				/>
			</template>
		</ChatHeader>
	`,
};
