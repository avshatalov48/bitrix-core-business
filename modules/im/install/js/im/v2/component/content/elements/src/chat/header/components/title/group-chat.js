import { LineLoader } from 'im.v2.component.elements.loader';
import { EditableChatTitle } from 'im.v2.component.elements.chat-title';
import { FadeAnimation } from 'im.v2.component.animation';

import { EntityLink } from '../entity-link/entity-link';
import { UserCounter } from './user-counter';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const GroupChatTitle = {
	name: 'GroupChatTitle',
	components: { EditableChatTitle, EntityLink, LineLoader, FadeAnimation, UserCounter },
	inject: ['withSidebar'],
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['newTitle'],
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
					<UserCounter :dialogId="dialogId" />
					<EntityLink v-if="hasEntityLink" :dialogId="dialogId" />
				</div>
			</FadeAnimation>
		</div>
	`,
};
