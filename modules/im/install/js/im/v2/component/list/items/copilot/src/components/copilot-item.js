import 'main.date';

import { Core } from 'im.v2.application.core';
import { Settings, Layout } from 'im.v2.const';
import { InputActionIndicator } from 'im.v2.component.list.items.elements.input-action-indicator';
import { ChatTitle } from 'im.v2.component.elements.chat-title';
import { ChatAvatar, AvatarSize } from 'im.v2.component.elements.avatar';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import { MessageText } from './message-text';

import '../css/copilot-item.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem, ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const CopilotItem = {
	name: 'CopilotItem',
	components: { ChatAvatar, ChatTitle, MessageText, InputActionIndicator },
	props:
	{
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
		message(): ImModelMessage
		{
			return this.$store.getters['recent/getMessage'](this.recentItem.dialogId);
		},
		layout(): { name: string, entityId: string }
		{
			return this.$store.getters['application/getLayout'];
		},
		isChatSelected(): boolean
		{
			if (this.layout.name !== Layout.copilot)
			{
				return false;
			}

			return this.layout.entityId === this.recentItem.dialogId;
		},
		isChatMuted(): boolean
		{
			const isMuted = this.dialog.muteList.find((element) => {
				return element === Core.getUserId();
			});

			return Boolean(isMuted);
		},
		hasActiveInputAction(): boolean
		{
			return this.$store.getters['chats/inputActions/isChatActive'](this.recentItem.dialogId);
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
		showPinnedIcon(): boolean
		{
			return this.recentItem.pinned && this.dialog.counter === 0 && !this.recentItem.unread;
		},
		showUnreadWithoutCounter(): boolean
		{
			return this.recentItem.unread && this.dialog.counter === 0;
		},
		showCounter(): boolean
		{
			return this.dialog.counter > 0;
		},
		wrapClasses(): Object
		{
			return {
				'--pinned': this.recentItem.pinned,
				'--selected': this.isChatSelected,
			};
		},
		itemClasses(): Object
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
	// language=Vue
	template: `
		<div :data-id="recentItem.dialogId" :class="wrapClasses" class="bx-im-list-copilot-item__wrap">
			<div :class="itemClasses" class="bx-im-list-copilot-item__container">
				<div class="bx-im-list-copilot-item__avatar_container">
					<div class="bx-im-list-copilot-item__avatar_content">
						<ChatAvatar
							:avatarDialogId="recentItem.dialogId"
							:contextDialogId="recentItem.dialogId"
							:withSpecialTypes="false"
							:size="AvatarSize.XL"
						/>
						<InputActionIndicator v-if="hasActiveInputAction" />
					</div>
				</div>
				<div class="bx-im-list-copilot-item__content_container">
					<div class="bx-im-list-copilot-item__content_header">
						<ChatTitle :dialogId="recentItem.dialogId" :withMute="true" />
						<div class="bx-im-list-copilot-item__date">
							<span>{{ formattedDate }}</span>
						</div>
					</div>
					<div class="bx-im-list-copilot-item__content_bottom">
						<MessageText :item="recentItem" />
						<div :class="{'--extended': dialog.counter > 99}" class="bx-im-list-copilot-item__counter_wrap">
							<div class="bx-im-list-copilot-item__counter_container">
								<div v-if="showPinnedIcon" class="bx-im-list-copilot-item__pinned-icon"></div>
								<div v-else-if="showCounter" :class="{'--muted': isChatMuted}" class="bx-im-list-copilot-item__counter_number">
									{{ formattedCounter }}
								</div>
								<div v-else-if="showUnreadWithoutCounter" class="bx-im-list-copilot-item__counter_number --no-counter"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`,
};
