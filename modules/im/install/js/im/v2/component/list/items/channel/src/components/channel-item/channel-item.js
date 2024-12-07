import 'main.date';

import { Core } from 'im.v2.application.core';
import { ChatType, Settings, Layout } from 'im.v2.const';
import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';

import { MessageText } from './components/message-text';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import './css/channel-item.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem, ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const ChannelItem = {
	name: 'ChannelItem',
	components: { ChatAvatar, ChatTitle, MessageText },
	props: {
		item: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		recentItem(): ImModelRecentItem
		{
			return this.item;
		},
		formattedDate(): string
		{
			if (this.needsBirthdayPlaceholder)
			{
				return this.loc('IM_LIST_RECENT_BIRTHDAY_DATE');
			}

			return this.formatDate(this.message.date);
		},
		formattedCounter(): string
		{
			return this.dialog.counter > 99 ? '99+' : this.dialog.counter.toString();
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.recentItem.dialogId, true);
		},
		layout(): { name: string, entityId: string }
		{
			return this.$store.getters['application/getLayout'];
		},
		message(): ImModelMessage
		{
			return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		isChatSelected(): boolean
		{
			if (this.layout.name !== Layout.channel.name)
			{
				return false;
			}

			return this.layout.entityId === this.recentItem.dialogId;
		},
		isChatMuted(): boolean
		{
			if (this.isUser)
			{
				return false;
			}

			const isMuted = this.dialog.muteList.find((element) => {
				return element === Core.getUserId();
			});

			return Boolean(isMuted);
		},
		isSomeoneTyping(): boolean
		{
			return this.dialog.writingList.length > 0;
		},
		needsBirthdayPlaceholder(): boolean
		{
			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.recentItem.dialogId);
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
		invitation(): { isActive: boolean, originator: number, canResend: boolean }
		{
			return this.recentItem.invitation;
		},
		wrapClasses(): { [string]: boolean }
		{
			return {
				'--selected': this.isChatSelected,
			};
		},
	},
	methods:
	{
		formatDate(date): string
		{
			return DateFormatter.formatByTemplate(date, DateTemplate.recent);
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-channel-item__wrap">
			<div class="bx-im-list-channel-item__container">
				<div class="bx-im-list-channel-item__avatar_container">
					<div class="bx-im-list-channel-item__avatar_content">
						<ChatAvatar 
							:avatarDialogId="recentItem.dialogId" 
							:contextDialogId="recentItem.dialogId"
							:size="AvatarSize.XL" 
						/>
					</div>
				</div>
				<div class="bx-im-list-channel-item__content_container">
					<div class="bx-im-list-channel-item__content_header">
						<ChatTitle :dialogId="recentItem.dialogId" />
						<div class="bx-im-list-channel-item__date">
							<span>{{ formattedDate }}</span>
						</div>
					</div>
					<div class="bx-im-list-channel-item__content_bottom">
						<MessageText :item="recentItem" />
					</div>
				</div>
			</div>
		</div>
	`,
};
