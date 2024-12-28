import 'main.date';

import { Core } from 'im.v2.application.core';
import { ChatType, Settings, Layout } from 'im.v2.const';
import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';

import { MessageText } from './components/message-text';
import { ItemCounter } from './components/item-counter';
import { MessageStatus } from './components/message-status';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';
import { ChannelManager } from 'im.v2.lib.channel';

import './css/recent-item.css';

import type { ImModelRecentItem, ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const RecentItem = {
	name: 'RecentItem',
	components: { ChatAvatar, ChatTitle, MessageText, MessageStatus, ItemCounter },
	props: {
		item: {
			type: Object,
			required: true,
		},
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

			return this.formatDate(this.itemDate);
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
		itemDate(): Date
		{
			return this.$store.getters['recent/getSortDate'](this.recentItem.dialogId);
		},
		isUser(): boolean
		{
			return this.dialog.type === ChatType.user;
		},
		isChat(): boolean
		{
			return !this.isUser;
		},
		isChannel(): boolean
		{
			return ChannelManager.isChannel(this.recentItem.dialogId);
		},
		isChatSelected(): boolean
		{
			const canBeSelected = [Layout.chat.name, Layout.updateChat.name, Layout.collab.name];
			if (!canBeSelected.includes(this.layout.name))
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
				'--pinned': this.recentItem.pinned,
				'--selected': this.isChatSelected,
			};
		},
		itemClasses(): { [string]: boolean }
		{
			return {
				'--no-text': !this.showLastMessage,
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
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-recent-item__wrap">
			<div :class="itemClasses" class="bx-im-list-recent-item__container">
				<div class="bx-im-list-recent-item__avatar_container">
					<div v-if="invitation.isActive" class="bx-im-list-recent-item__avatar_invitation"></div>
					<div v-else class="bx-im-list-recent-item__avatar_content">
						<ChatAvatar 
							:avatarDialogId="recentItem.dialogId" 
							:contextDialogId="recentItem.dialogId" 
							:size="AvatarSize.XL" 
							:withSpecialTypeIcon="!isSomeoneTyping" 
						/>
						<div v-if="isSomeoneTyping" class="bx-im-list-recent-item__avatar_typing"></div>
					</div>
				</div>
				<div class="bx-im-list-recent-item__content_container">
					<div class="bx-im-list-recent-item__content_header">
						<ChatTitle :dialogId="recentItem.dialogId" :withMute="true" />
						<div class="bx-im-list-recent-item__date">
							<MessageStatus :item="item" />
							<span>{{ formattedDate }}</span>
						</div>
					</div>
					<div class="bx-im-list-recent-item__content_bottom">
						<MessageText :item="recentItem" />
						<ItemCounter :item="recentItem" :isChatMuted="isChatMuted" />
					</div>
				</div>
			</div>
		</div>
	`,
};
