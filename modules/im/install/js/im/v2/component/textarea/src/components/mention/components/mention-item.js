import { Text } from 'main.core';

import { ChatType } from 'im.v2.const';
import { highlightText } from 'im.v2.lib.text-highlighter';
import { Avatar, AvatarSize, ChatTitleWithHighlighting } from 'im.v2.component.elements';

import '../css/mention-item.css';

import type { ImModelChat, ImModelRecentItem, ImModelUser } from 'im.v2.model';

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
		selected: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['itemClick', 'itemHover'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		recentItem(): ImModelRecentItem
		{
			return this.$store.getters['recent/get'](this.dialogId);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
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
				return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_USER_TYPE');
			}

			return highlightText(Text.encode(this.position), this.query);
		},
		chatItemText(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MENTION_CHAT_TYPE');
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
		<div 
			@click="onClick" 
			class="bx-im-mention-item__container bx-im-mention-item__scope" 
			:class="{'--selected': selected}"
			@mouseover="$emit('itemHover')"
		>
			<Avatar :dialogId="dialogId" :size="AvatarSize.M" class="bx-im-mention-item__avatar-container" />
			<div class="bx-im-mention-item__content-container">
				<ChatTitleWithHighlighting 
					:dialogId="dialogId" 
					:textToHighlight="query" 
					class="bx-im-mention-item__title"
				/>
				<div v-if="isUser" class="bx-im-mention-item__position" :title="position" v-html="userItemText"></div>
				<div v-else class="bx-im-mention-item__position" :title="chatItemText">{{ chatItemText }}</div>
			</div>
		</div>
	`,
};
