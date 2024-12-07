import 'main.date';

import { Core } from 'im.v2.application.core';
import { Settings, Layout } from 'im.v2.const';
import { ChatAvatar, AvatarSize, ChatTitle } from 'im.v2.component.elements';

import { MessageText } from './message-text';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import '../css/copilot-item.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem, ImModelChat, ImModelMessage } from 'im.v2.model';

// @vue/component
export const CopilotItem = {
	name: 'CopilotItem',
	components: { ChatAvatar, ChatTitle, MessageText },
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
			if (this.layout.name !== Layout.copilot.name)
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
		isSomeoneTyping(): boolean
		{
			return this.dialog.writingList.length > 0;
		},
		showLastMessage(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showLastMessage);
		},
		showPinnedIcon(): boolean
		{
			return this.recentItem.pinned && this.dialog.counter === 0 && !this.recentItem.unread;
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
						<div v-if="isSomeoneTyping" class="bx-im-list-copilot-item__avatar_typing"></div>
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
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`,
};
