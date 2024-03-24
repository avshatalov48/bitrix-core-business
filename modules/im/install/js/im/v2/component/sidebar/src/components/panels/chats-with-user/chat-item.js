import { Avatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import './css/chat-item.css';

import type { ImModelRecentItem } from 'im.v2.model';

// @vue/component
export const ChatItem = {
	name: 'ChatItem',
	components: { Avatar, ChatTitle },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['clickItem', 'rightClickItem'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		recentItem(): ImModelRecentItem
		{
			return this.$store.getters['recent/get'](this.dialogId);
		},
		chatItemText(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2');
		},
		formattedDate(): string
		{
			if (!this.recentItem.message.date)
			{
				return '';
			}

			return this.formatDate(this.recentItem.message.date);
		},
	},
	methods:
	{
		onClick(event)
		{
			this.$emit('clickItem', {
				dialogId: this.dialogId,
				nativeEvent: event,
			});
		},
		formatDate(date: Date): string
		{
			return DateFormatter.formatByTemplate(date, DateTemplate.recent);
		},
	},
	template: `
		<div 
			@click="onClick"
			class="bx-im-chat-with-user-item__container bx-im-chat-with-user-item__scope"
		>
			<div class="bx-im-chat-with-user-item__avatar-container">
				<Avatar :dialogId="dialogId" :size="AvatarSize.XL" />
			</div>
			<div class="bx-im-chat-with-user-item__content-container">
				<div class="bx-im-chat-with-user-item__content_header">
					<ChatTitle :dialogId="dialogId" />
					<div v-if="formattedDate.length > 0" class="bx-im-chat-with-user-item__date">
						<span>{{ formattedDate }}</span>
					</div>
				</div>
				<div class="bx-im-chat-with-user-item__item-text" :title="chatItemText">
					{{ chatItemText }}
				</div>
			</div>
		</div>
	`,
};
