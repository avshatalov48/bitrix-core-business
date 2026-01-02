import { LineLoader } from 'im.v2.component.elements.loader';
import { FadeAnimation } from 'im.v2.component.animation';
import { ChatHeader, UserCounter } from 'im.v2.component.content.elements';

import '../css/header.css';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const TaskChatHeader = {
	name: 'TaskChatHeader',
	components: { ChatHeader, UserCounter, LineLoader, FadeAnimation },
	props:
	{
		dialogId: {
			type: String,
			default: '',
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		isInited(): boolean
		{
			return this.dialog.inited;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<ChatHeader
			:dialogId="dialogId"
			:withAddToChatButton="false"
		>
			<template #left>
				<div class="bx-im-task-chat-header__left_container">
					<div class="bx-im-task-chat-header__avatar"></div>
					<div class="bx-im-task-chat-header__info">
						<div class="bx-im-task-chat-header__title">{{ loc('IM_TASK_CHAT_HEADER_TITLE') }}</div>
						<LineLoader v-if="!isInited" :width="50" :height="16" />
						<FadeAnimation :duration="100">
							<UserCounter v-if="isInited" :dialogId="dialogId" />
						</FadeAnimation>
					</div>
				</div>
			</template>
		</ChatHeader>
	`,
};
