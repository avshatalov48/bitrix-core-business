import { Loc } from 'main.core';

import { ChatType } from 'im.v2.const';
import { EditableChatTitle, LineLoader } from 'im.v2.component.elements';
import { FadeAnimation } from 'im.v2.component.animation';

import { EntityLink } from '../entity-link/entity-link';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

const UserCounterPhraseCodeByChatType = {
	[ChatType.openChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	[ChatType.channel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	[ChatType.generalChannel]: 'IM_CONTENT_CHAT_HEADER_CHANNEL_USER_COUNT',
	default: 'IM_CONTENT_CHAT_HEADER_USER_COUNT',
};

// @vue/component
export const GroupChatTitle = {
	name: 'GroupChatTitle',
	components: { EditableChatTitle, EntityLink, LineLoader, FadeAnimation },
	props:
		{
			dialogId: {
				type: String,
				required: true,
			},
		},
	emits: ['membersClick', 'newTitle'],
	data(): JsonObject
	{
		return {};
	},
	computed:
		{
			dialog(): ImModelChat
			{
				return this.$store.getters['chats/get'](this.dialogId, true);
			},
			hasEntityLink(): boolean
			{
				return Boolean(this.dialog.entityLink?.url);
			},
			userCounterPhraseCode(): string
			{
				return UserCounterPhraseCodeByChatType[this.dialog.type] ?? UserCounterPhraseCodeByChatType.default;
			},
			userCounterText(): string
			{
				return Loc.getMessagePlural(this.userCounterPhraseCode, this.dialog.userCounter, {
					'#COUNT#': this.dialog.userCounter,
				});
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
		<div class="bx-im-chat-header__info">
			<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="$emit('newTitle', $event)" />
			<LineLoader v-if="!dialog.inited" :width="50" :height="16" />
			<FadeAnimation :duration="100">
				<div v-if="dialog.inited" class="bx-im-chat-header__subtitle_container">
					<div
						:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')"
						@click="$emit('membersClick')"
						class="bx-im-chat-header__subtitle_content --click"
					>
						{{ userCounterText }}
					</div>
					<EntityLink v-if="hasEntityLink" :dialogId="dialogId" />
				</div>
			</FadeAnimation>
		</div>
	`,
};
