import { Utils } from 'im.v2.lib.utils';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';
import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';

import './css/chat-item.css';

// @vue/component
export const ChatItem = {
	name: 'ChatItem',
	components: { ChatAvatar, ChatTitle },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		dateMessage: {
			type: String,
			default: '',
		},
	},
	emits: ['clickItem'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		chatItemText(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_CHAT_TYPE_GROUP_V2');
		},
		formattedDate(): string
		{
			if (!this.dateMessage)
			{
				return '';
			}

			const date = Utils.date.cast(this.dateMessage);

			return this.formatDate(date);
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
				<ChatAvatar 
					:avatarDialogId="dialogId" 
					:contextDialogId="dialogId" 
					:size="AvatarSize.XL" 
				/>
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
