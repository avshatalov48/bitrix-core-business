import {DialogType, RecentSettings} from 'im.old-chat-embedding.const';
import {Utils} from 'im.old-chat-embedding.lib.utils';

import 'ui.fonts.opensans';
import './avatar.css';

export const AvatarSize = {
	XS: 'XS',
	S: 'S',
	M: 'M',
	L: 'L',
	XL: 'XL',
	XXL: 'XXL',
};

export const Avatar = {
	name: 'Avatar',
	props: {
		dialogId: {
			type: String,
			default: '0'
		},
		size: {
			type: String,
			default: AvatarSize.M
		},
		withAvatarLetters: {
			type: Boolean,
			default: true
		},
		withStatus: {
			type: Boolean,
			default: true
		},
		withCounter: {
			type: Boolean,
			default: false
		},
		withBirthday: {
			type: Boolean,
			default: false
		},
		withTyping: {
			type: Boolean,
			default: false
		}
	},
	computed:
	{
		dialog()
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		user()
		{
			return this.$store.getters['users/get'](this.dialogId, true);
		},
		isUser()
		{
			return this.dialog.type === DialogType.user;
		},
		isBot()
		{
			if (this.isUser)
			{
				return this.user.bot;
			}

			return false;
		},
		isActiveInvitation(): boolean
		{
			const recentItem = this.$store.getters['recent/get'](this.dialogId);
			if (!recentItem)
			{
				return false;
			}

			return recentItem.invitation.isActive;
		},
		chatAvatarStyle(): Object
		{
			return {backgroundImage: `url('${this.dialog.avatar}')`};
		},
		avatarText(): string
		{
			if (![DialogType.user, DialogType.open, DialogType.chat].includes(this.dialog.type))
			{
				return '';
			}

			return Utils.text.getFirstLetters(this.dialog.name);
		},
		chatTypeIconClasses(): string[]
		{
			const classes = [];
			if (DialogType[this.dialog.type])
			{
				classes.push(`bx-im-component-avatar-icon-${this.dialog.type}`);
			}
			else
			{
				classes.push('bx-im-component-avatar-icon-default');
			}

			return classes;
		},
		userStatusIcon(): string
		{
			if (!this.isUser || this.isBot || this.user.id === this.currentUserId)
			{
				return '';
			}

			const status = this.$store.getters['users/getStatus'](this.dialogId);
			if (status)
			{
				return status;
			}

			return '';
		},
		isSomeoneTyping()
		{
			return Object.keys(this.dialog.writingList).length > 0;
		},
		formattedCounter()
		{
			return this.dialog.counter > 99 ? '99+' : this.dialog.counter;
		},
		isChatMuted()
		{
			if (this.isUser)
			{
				return false;
			}

			const isMuted = this.dialog.muteList.find(element => {
				return element === this.currentUserId;
			});

			return !!isMuted;
		},
		showBirthdays()
		{
			return this.$store.getters['recent/getOption'](RecentSettings.showBirthday);
		},
		currentUserId()
		{
			return this.$store.state.application.common.userId;
		},
	},
	template: `
		<div :title="dialog.name" :class="'bx-im-component-avatar-size-' + size.toLowerCase()" class="bx-im-component-avatar-wrap">
			<div v-if="isActiveInvitation" class="bx-im-component-avatar-content bx-im-component-avatar-invitation"></div>
			<div v-else-if="dialog.avatar" :style="chatAvatarStyle" class="bx-im-component-avatar-content bx-im-component-avatar-image"></div>
			<div v-else-if="withAvatarLetters && avatarText" :style="{backgroundColor: dialog.color}" class="bx-im-component-avatar-content bx-im-component-avatar-text">
				{{ avatarText }}
			</div>
			<div v-else :style="{backgroundColor: dialog.color}" :class="chatTypeIconClasses" class="bx-im-component-avatar-content bx-im-component-avatar-icon"></div>
			<div v-if="withTyping && isSomeoneTyping" class="bx-im-component-avatar-user-status-icon bx-im-component-avatar-user-status-icon-typing"></div>
			<div v-else-if="withBirthday && isUser && showBirthdays && user.isBirthday" class="bx-im-component-avatar-user-status-icon bx-im-component-avatar-user-status-icon-birthday"></div>
			<div v-else-if="withStatus && userStatusIcon" :class="'bx-im-component-avatar-user-status-icon bx-im-component-avatar-user-status-icon-' + userStatusIcon"></div>
			<div v-if="withCounter && dialog.counter > 0" :class="{'bx-im-component-avatar-counter-muted': isChatMuted}" class="bx-im-component-avatar-counter">
				{{ formattedCounter }}
			</div>
		</div>
	`
};