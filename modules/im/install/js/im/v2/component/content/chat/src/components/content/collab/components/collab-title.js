import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { EventType, SidebarDetailBlock } from 'im.v2.const';
import { ChatTitle, LineLoader } from 'im.v2.component.elements';
import { FadeAnimation } from 'im.v2.component.animation';

import '../css/collab-title.css';

import type { ImModelChat, ImModelCollabInfo } from 'im.v2.model';

// @vue/component
export const CollabTitle = {
	name: 'CollabTitle',
	components: { ChatTitle, LineLoader, FadeAnimation },
	inject: ['currentSidebarPanel'],
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		collabInfo(): ImModelCollabInfo
		{
			return this.$store.getters['chats/collabs/getByChatId'](this.dialog.chatId);
		},
		guestCounter(): number
		{
			return this.collabInfo.guestCount;
		},
		userCounterText(): string
		{
			return Loc.getMessagePlural('IM_CONTENT_CHAT_HEADER_USER_COUNT', this.dialog.userCounter, {
				'#COUNT#': this.dialog.userCounter,
			});
		},
		guestCounterText(): string
		{
			return Loc.getMessagePlural('IM_CONTENT_COLLAB_HEADER_GUEST_COUNT', this.guestCounter, {
				'#COUNT#': this.guestCounter,
			});
		},
	},
	methods:
	{
		onMembersClick()
		{
			if (this.currentSidebarPanel === SidebarDetailBlock.members)
			{
				EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.members });

				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.members,
				dialogId: this.dialogId,
			});
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-collab-header-title__container">
			<div class="bx-im-collab-header-title__title-container --ellipsis">
				<ChatTitle :dialogId="dialogId" />
			</div>
			<LineLoader v-if="!dialog.inited" :width="50" :height="16" />
			<FadeAnimation :duration="100">
				<div v-if="dialog.inited" class="bx-im-collab-header-title__subtitle_container">
					<div @click="onMembersClick" class="bx-im-collab-header-title__subtitle_content --ellipsis">
						<span
							:title="loc('IM_CONTENT_CHAT_HEADER_OPEN_MEMBERS')"
							class="bx-im-collab-header-title__user-counter"
						>
							{{ userCounterText }}
						</span>
						<span v-if="guestCounter > 0" class="bx-im-collab-header-title__guest-counter">
							{{ guestCounterText }}
						</span>
					</div>
				</div>
			</FadeAnimation>
		</div>
	`,
};
