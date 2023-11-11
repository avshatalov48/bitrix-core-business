import { Text } from 'main.core';

import { DialogType } from 'im.v2.const';
import { Avatar, AvatarSize, ChatTitleWithHighlighting } from 'im.v2.component.elements';
import { highlightText } from 'im.v2.lib.text-highlighter';

import '../css/mention-item.css';

import type { ImModelDialog, ImModelUser } from 'im.v2.model';

// @vue/component
export const MentionItem = {
	name: 'MentionItem',
	components: { Avatar, ChatTitleWithHighlighting },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		query: {
			type: String,
			default: '',
		},
	},
	emits: ['itemClick'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		isUser(): boolean
		{
			return this.dialog.type === DialogType.user;
		},
		position(): string
		{
			if (!this.isUser)
			{
				return '';
			}

			return this.user.workPosition;
		},
		userItemText(): string
		{
			if (!this.position)
			{
				return this.$Bitrix.Loc.getMessage('IM_SEARCH_EXPERIMENTAL_ITEM_USER_TYPE_GROUP_V2');
			}

			return highlightText(Text.encode(this.position), this.query);
		},
		chatItemText(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SEARCH_EXPERIMENTAL_ITEM_CHAT_TYPE_GROUP_V2');
		},
	},
	methods:
	{
		onClick()
		{
			this.$emit('itemClick', { dialogId: this.dialogId });
		},
	},
	template: `
		<div @click="onClick" class="bx-im-mention-item__container bx-im-mention-item__scope">
			<Avatar :dialogId="dialogId" :size="AvatarSize.M" />
			<div class="bx-im-mention-item__content-container">
				<ChatTitleWithHighlighting class="bx-im-mention-item__title" :dialogId="dialogId" :textToHighlight="query" />
				<div v-if="isUser" class="bx-im-mention-item__position" :title="position" v-html="userItemText"></div>
				<div v-else class="bx-im-mention-item__position" :title="chatItemText">{{ chatItemText }}</div>
			</div>
		</div>
	`,
};
