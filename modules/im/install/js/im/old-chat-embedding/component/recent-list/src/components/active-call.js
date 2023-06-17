import {mapState} from 'ui.vue3.vuex';
import {MenuManager} from 'main.popup';
import {Loc} from 'main.core';

import {RecentCallStatus, DialogType} from 'im.old-chat-embedding.const';
import {Utils} from 'im.old-chat-embedding.lib.utils';
import {Avatar, AvatarSize} from 'im.old-chat-embedding.component.elements';

// @vue/component
export const ActiveCall = {
	name: 'ActiveCall',
	components: {Avatar},
	props: {
		item: {
			type: Object,
			required: true
		},
		compactMode: {
			type: Boolean,
			default: false
		}
	},
	emits: ['click', 'contextmenu'],
	computed: {
		RecentCallStatus: () => RecentCallStatus,
		AvatarSize: () => AvatarSize,
		chatData()
		{
			return this.item.call.associatedEntity;
		},
		isUser()
		{
			return this.chatData.advanced.chatType === DialogType.private;
		},
		isTabWithActiveCall()
		{
			return this.getCallController().hasActiveCall();
		},
		avatarStyle()
		{
			return {backgroundImage: `url(${this.chatData.avatar})`};
		},
		avatarText()
		{
			return Utils.text.getFirstLetters(this.item.name);
		},
		isDarkTheme()
		{
			return this.application.options.darkTheme;
		},
		formattedName()
		{
			return Utils.text.htmlspecialcharsback(this.item.name);
		},
		...mapState({
			application: state => state.application
		})
	},
	methods:
	{
		onJoinClick(event)
		{
			if (this.joinMenu)
			{
				this.joinMenu.destroy();
			}
			this.joinMenu = this.getJoinMenu(event);
			this.joinMenu.show();
		},
		onHangupClick()
		{
			this.getCallController().leaveCurrentCall();
		},
		onClick(event)
		{
			if (this.item.state === RecentCallStatus.joined)
			{
				this.getCallController().unfold();
				return;
			}

			const item = this.$store.getters['recent/get'](this.item.dialogId);
			if (!item)
			{
				return;
			}
			this.$emit('click', {item, $event: event});
		},
		onRightClick()
		{
			const item = this.$store.getters['recent/get'](this.item.dialogId);
			if (!item)
			{
				return;
			}
			this.$emit('contextmenu', {item, $event: event});
		},
		getJoinMenu(event)
		{
			return MenuManager.create({
				id: 'im-recent-active-call-join-menu',
				bindElement: event.target,
				darkMode: this.isDarkTheme,
				cacheable: false,
				items: [
					{
						text: Loc.getMessage('IM_RECENT_ACTIVE_CALL_JOIN_VIDEO'),
						onclick: function() {
							this.getCallController().joinCall(this.item.call.id, true);
							this.joinMenu.close();
						}.bind(this)
					},
					{
						text: Loc.getMessage('IM_RECENT_ACTIVE_CALL_JOIN_AUDIO'),
						onclick: function() {
							this.getCallController().joinCall(this.item.call.id, false);
							this.joinMenu.close();
						}.bind(this)
					}
				]
			});
		},
		getCallController()
		{
			return BX.MessengerProxy.getCallController();
		}
	},
	template: `
		<div :data-id="item.dialogId" class="bx-im-recent-item-wrap">
		<div v-if="!compactMode" @click="onClick" @click.right.prevent="onRightClick" class="bx-im-recent-item bx-im-recent-active-call-item">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar :dialogId="item.dialogId" :size="AvatarSize.L" />
			</div>
			<div class="bx-im-recent-item-content">
				<!-- Waiting status -->
				<template v-if="item.state === RecentCallStatus.waiting">
					<!-- 1-on-1 -->
					<div v-if="isUser"  class="bx-im-recent-active-call-waiting-content">
						<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-waiting-icon"></div>
						<div class="bx-im-recent-active-call-waiting-title">
							{{ formattedName }}
						</div>
					</div>
					<!-- Chat -->
					<div v-else>
						<div class="bx-im-recent-item-content-header">
							<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-waiting-icon"></div>
							<span class="bx-im-recent-active-call-waiting-title" :title="formattedName">
								{{ formattedName }}
							</span>
						</div>
						<div class="bx-im-recent-item-content-bottom">
							<div @click.stop="onJoinClick" class="bx-im-recent-active-call-button bx-im-recent-active-call-join-button">
								{{ $Bitrix.Loc.getMessage('IM_RECENT_ACTIVE_CALL_JOIN') }}
							</div>
						</div>
					</div>
				</template>
				<!-- Joined status -->
				<template v-else-if="item.state === RecentCallStatus.joined">
					<!-- 1-on-1 -->
					<div v-if="isUser || !isTabWithActiveCall" class="bx-im-recent-active-call-joined-content">
						<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-joined-icon"></div>
						<div class="bx-im-recent-active-call-joined-title">
							{{ formattedName }}
						</div>
					</div>
					<!-- Chat -->
					<div v-else-if="!isUser && isTabWithActiveCall">
						<div class="bx-im-recent-item-content-header">
							<div class="bx-im-recent-active-call-icon bx-im-recent-active-call-joined-icon"></div>
							<span class="bx-im-recent-active-call-joined-title" :title="formattedName">
								{{ formattedName }}
							</span>
						</div>
						<div class="bx-im-recent-item-content-bottom">
							<div @click.stop="onHangupClick" class="bx-im-recent-active-call-button bx-im-recent-active-call-hangup-button">
								{{ $Bitrix.Loc.getMessage('IM_RECENT_ACTIVE_CALL_HANGUP') }}
							</div>
						</div>
					</div>
				</template>
			</div>
		</div>
		<div v-if="compactMode" @click="onClick" @click.right.prevent="onRightClick" class="bx-im-recent-item bx-im-recent-active-call-item">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar :dialogId="item.dialogId" :size="AvatarSize.M" />
				<div class="bx-im-recent-active-call-compact-icon-container">
					<div v-if="item.state === RecentCallStatus.waiting" class="bx-im-recent-active-call-icon bx-im-recent-active-call-waiting-icon"></div>
					<div v-else-if="item.state === RecentCallStatus.joined" class="bx-im-recent-active-call-icon bx-im-recent-active-call-joined-icon"></div>
				</div>
			</div>
		</div>
		</div>
	`
};