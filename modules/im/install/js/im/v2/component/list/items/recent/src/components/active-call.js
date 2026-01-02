import { ConferenceChannel } from 'call.application.conference';
import { Messenger } from 'im.public';
import { Text } from 'main.core';

import { EventEmitter } from 'main.core.events';

import { ChatType, RecentCallStatus, EventType } from 'im.v2.const';
import { ChatTitle } from 'im.v2.component.elements.chat-title';
import { ChatButton, ButtonSize, ButtonColor, ButtonIcon, type CustomColorScheme } from 'im.v2.component.elements.button';
import { ChatAvatar, AvatarSize } from 'im.v2.component.elements.avatar';
import { CallManager } from 'im.v2.lib.call';
import { Analytics as CallAnalytics } from 'call.lib.analytics';

import '../css/active-call.css';

import type { ImModelCallItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const ActiveCall = {
	name: 'ActiveCall',
	components: { ChatAvatar, ChatTitle, ChatButton },
	props: {
		item: {
			type: Object,
			required: true,
		},
	},
	emits: ['click'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		ButtonIcon: () => ButtonIcon,
		activeCall(): ImModelCallItem
		{
			return this.item;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.activeCall.dialogId, true);
		},
		isConference(): boolean
		{
			return this.dialog.type === ChatType.videoconf;
		},
		preparedName(): string
		{
			return Text.decode(this.activeCall.name);
		},
		anotherDeviceColorScheme(): CustomColorScheme
		{
			return {
				backgroundColor: 'transparent',
				borderColor: '#bbde4d',
				iconColor: '#525c69',
				textColor: '#525c69',
				hoverColor: 'transparent',
			};
		},
		isTabWithActiveCall(): boolean
		{
			return (
				this.$store.getters['recent/calls/hasActiveCall']()
				&& Boolean(this.getCallManager().hasCurrentCall())
			);
		},
		hasJoined(): boolean
		{
			return this.activeCall.state === RecentCallStatus.joined;
		},
	},
	methods:
	{
		async onJoinClick()
		{
			EventEmitter.emit(EventType.call.onJoinFromRecentItem);

			if (this.isConference)
			{
				CallAnalytics.getInstance().onJoinConferenceClick({
					callId: this.activeCall.call.id,
				});

				const hasThisActiveConference = await ConferenceChannel.getInstance().sendRequest(this.dialog.public.code);
				if (hasThisActiveConference.some(call => call))
				{
					return;
				}

				Messenger.openConference({ code: this.dialog.public.code });
				return;
			}

			const hasThisActiveCall = await this.getCallManager().sendBroadcastRequest(this.activeCall.call.uuid);
			if (hasThisActiveCall.some(call => call))
			{
				return;
			}

			this.getCallManager().joinCall(this.activeCall.call.id, this.activeCall.call.uuid, this.activeCall.dialogId);
		},
		onBackToCallClick() {
			if (this.isConference)
			{
				ConferenceChannel.getInstance().sendRequest(this.dialog.public.code);
				return;
			}
			this.getCallManager().sendBroadcastRequest(this.activeCall.call.uuid);
		},
		onLeaveCallClick()
		{
			this.getCallManager().leaveCurrentCall();
		},
		onClick(event)
		{
			const recentItem = this.$store.getters['recent/get'](this.activeCall.dialogId);
			if (!recentItem)
			{
				return;
			}
			this.$emit('click', { item: recentItem, $event: event });
		},
		returnToCall()
		{
			if (this.activeCall.state !== RecentCallStatus.joined)
			{
				return;
			}

			this.getCallManager().unfoldCurrentCall();
		},
		getCallManager(): CallManager
		{
			return CallManager.getInstance();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div :data-id="activeCall.dialogId" class="bx-im-list-recent-item__wrap bx-im-list-recent-active-call-item__wrap">
			<div @click="onClick" class="bx-im-list-recent-item__container bx-im-list-recent-active-call__container">
				<div class="bx-im-list-recent-item__avatar_container">
					<ChatAvatar 
						:avatarDialogId="activeCall.dialogId" 
						:contextDialogId="activeCall.dialogId" 
						:size="AvatarSize.XL" 
					/>
				</div>
				<div class="bx-im-list-recent-item__content_container">
					<div class="bx-im-list-recent-active-call__title_container">
						<ChatTitle :text="preparedName" />
						<div class="bx-im-list-recent-active-call__title_icon"></div>
					</div>
					<div v-if="!hasJoined" class="bx-im-list-recent-active-call__actions_container">
						<div class="bx-im-list-recent-active-call__actions_item --join">
							<ChatButton @click.stop="onJoinClick" :size="ButtonSize.M" :color="ButtonColor.Success" :isRounded="true" :text="loc('IM_LIST_RECENT_ACTIVE_CALL_JOIN')" />
						</div>
					</div>
					<div v-else-if="hasJoined && isTabWithActiveCall" class="bx-im-list-recent-active-call__actions_container">
						<div class="bx-im-list-recent-active-call__actions_item --return">
							<ChatButton @click.stop="returnToCall" :size="ButtonSize.M" :color="ButtonColor.Success" :isRounded="true" :text="loc('IM_LIST_RECENT_ACTIVE_CALL_RETURN')" />
						</div>
					</div>
					<div v-else-if="hasJoined && !isTabWithActiveCall" class="bx-im-list-recent-active-call__actions_container">
						<div class="bx-im-list-recent-active-call__actions_item --another-device">
							<ChatButton :size="ButtonSize.M" @click.stop="onBackToCallClick" :customColorScheme="anotherDeviceColorScheme" :isRounded="true" :text="loc('IM_LIST_RECENT_ACTIVE_CALL_ANOTHER_DEVICE')" />
						</div>
					</div>
				</div>
			</div>
		</div>
	`,
};