import { Loc } from 'main.core';

import { EditableChatTitle } from 'im.v2.component.elements';

import { EntityLink } from './entity-link/entity-link';

import type { JsonObject } from 'main.core';
import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const GroupChatTitle = {
	name: 'GroupChatTitle',
	components: { EditableChatTitle, EntityLink },
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
		userCounter(): string
		{
			return Loc.getMessagePlural('IM_CONTENT_CHAT_HEADER_USER_COUNT', this.dialog.userCounter, {
				'#COUNT#': this.dialog.userCounter,
			});
		},
		hasEntityLink(): boolean
		{
			return Boolean(this.dialog.entityLink?.url);
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
			<div class="bx-im-chat-header__subtitle_container">
				<div
					:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')"
					@click="$emit('membersClick')"
					class="bx-im-chat-header__subtitle_content --click"
				>
					{{ userCounter }}
				</div>
				<EntityLink v-if="hasEntityLink" :dialogId="dialogId" />
			</div>
		</div>
	`,
};
