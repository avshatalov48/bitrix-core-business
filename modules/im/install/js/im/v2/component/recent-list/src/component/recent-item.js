import {BitrixVue} from 'ui.vue3';
import 'main.date';
import {PopupManager} from 'main.popup';
import {EventEmitter} from 'main.core.events';
import {MessageStatus, ChatTypes, RecentSettings, EventType, AvatarSize, OpenTarget} from 'im.v2.const';
import {Avatar, ChatTitle} from 'im.v2.component.elements';

import {NewUserPopup} from './new-user-popup';

type RecentListItem = {
	dialogId: number,
	message: {
		id: number,
		text: string,
		date: string,
		senderId: number,
		status: MessageStatus.received | MessageStatus.delivered | MessageStatus.error
	},
	pinned: boolean,
	invitation: {
		isActive: boolean,
		originator: number,
		canResend: boolean
	}
}

// @vue/component
export const RecentItem = {
	name: 'RecentItem',
	components: {Avatar, ChatTitle, NewUserPopup},
	props: {
		item: {
			type: Object,
			required: true
		},
		compactMode: {
			type: Boolean,
			default: false
		},
		isVisibleOnScreen: {
			type: Boolean,
			required: true
		}
	},
	data()
	{
		return {
			showNewUserPopup: false
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		formattedDate(): string
		{
			if (this.needsBirthdayPlaceholder)
			{
				return this.$Bitrix.Loc.getMessage('IM_RECENT_BIRTHDAY_DATE');
			}

			return this.formatDate(this.item.message.date);
		},

		messageText(): string
		{
			if (this.isUser && (!this.item.message || !this.item.message.text))
			{
				return this.$store.getters['users/getPosition'](this.item.dialogId);
			}

			return this.$store.getters['recent/getItemText'](this.item.dialogId);
		},

		hiddenMessageText(): string
		{
			if (this.isUser)
			{
				return this.$store.getters['users/getPosition'](this.item.dialogId);
			}

			if (this.dialog.type === ChatTypes.open)
			{
				return this.$Bitrix.Loc.getMessage('IM_RECENT_CHAT_TYPE_OPEN');
			}

			return this.$Bitrix.Loc.getMessage('IM_RECENT_CHAT_TYPE_GROUP');
		},

		statusIcon(): string
		{
			if (!this.isLastMessageAuthor || this.isBot || this.needsBirthdayPlaceholder || !this.item.message)
			{
				return '';
			}

			if (this.isSelfChat)
			{
				return '';
			}

			if (this.item.message.status === MessageStatus.error)
			{
				return 'error';
			}

			if (this.item.liked)
			{
				return 'like';
			}

			if (this.item.message.status === MessageStatus.delivered)
			{
				return 'read';
			}

			return 'unread';
		},

		formattedCounter()
		{
			return this.dialog.counter > 99 ? '99+' : this.dialog.counter;
		},

		user()
		{
			return this.$store.getters['users/get'](this.item.dialogId, true);
		},

		dialog()
		{
			return this.$store.getters['dialogues/get'](this.item.dialogId, true);
		},

		currentUserId()
		{
			return this.$store.state.application.common.userId;
		},

		isUser()
		{
			return this.dialog.type === ChatTypes.user;
		},

		isChat()
		{
			return !this.isUser;
		},

		isSelfChat()
		{
			return this.isUser && this.user.id === this.currentUserId;
		},

		isBot()
		{
			if (this.isUser)
			{
				return this.user.bot;
			}

			return false;
		},

		isLastMessageAuthor()
		{
			if (!this.item.message)
			{
				return false;
			}

			return this.currentUserId === this.item.message.senderId;
		},

		lastMessageAuthorAvatar()
		{
			const authorDialog = this.$store.getters['dialogues/get'](this.item.message.senderId);

			if (!authorDialog)
			{
				return '';
			}

			return authorDialog.avatar;
		},

		lastMessageAuthorAvatarStyle()
		{
			return {backgroundImage: `url('${this.lastMessageAuthorAvatar}')`};
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

		needsBirthdayPlaceholder()
		{
			if (!this.isUser)
			{
				return false;
			}

			return this.$store.getters['recent/needsBirthdayPlaceholder'](this.item.dialogId);
		},

		showBirthdays()
		{
			return this.$store.getters['recent/getOption'](RecentSettings.showBirthday);
		},

		showLastMessage()
		{
			return this.$store.getters['recent/getOption'](RecentSettings.showLastMessage);
		},

		invitation()
		{
			return this.item.invitation;
		},

		newUserPopupContainer()
		{
			return `#popup-window-content-bx-im-recent-welcome-${this.item.dialogId}`;
		},
	},
	watch:
	{
		invitation(newValue, oldValue)
		{
			if (!this.compactMode)
			{
				return false;
			}

			// invitation accepted, user logged in
			if (oldValue.isActive === true && newValue.isActive === false)
			{
				this.openNewUserPopup();
			}
		}
	},
	methods:
	{
		openNewUserPopup()
		{
			if (!this.isVisibleOnScreen || BX.MessengerProxy.isSliderOpened())
			{
				return false;
			}

			this.newUserPopup = this.getNewUserPopup();
			this.newUserPopup.show();
			this.showNewUserPopup = true;
			this.$nextTick(() => {
				this.newUserPopup.setOffset({
					offsetTop: -this.newUserPopup.popupContainer.offsetHeight + 1,
					offsetLeft: -this.newUserPopup.popupContainer.offsetWidth + 13
				});
				this.newUserPopup.adjustPosition();
			});
		},
		getNewUserPopup()
		{
			return PopupManager.create({
				id: `bx-im-recent-welcome-${this.item.dialogId}`,
				bindElement: this.$refs.container,
				bindOptions: {forceBindPosition: true},
				className: 'bx-im-recent-welcome',
				cacheable: false,
				animation: {
					showClassName: 'bx-im-recent-new-user-popup-show',
					closeClassName: 'bx-im-recent-new-user-popup-hide',
					closeAnimationType: 'animation'
				}
			});
		},
		onNewUserPopupClick(event)
		{
			const target = !this.compactMode || event.altKey? OpenTarget.current: OpenTarget.auto;

			EventEmitter.emit(EventType.dialog.open, {
				...this.item,
				target
			});
		},
		onNewUserPopupClose()
		{
			this.newUserPopup.close();
			this.newUserPopup = null;
			this.showNewUserPopup = false;
		},
		formatDate(date)
		{
			const format = [
				['today', 'H:i'],
				['d7', 'D'],
				['', 'd.m.Y']
			];

			return BX.date.format(format, date);
		},
	},
	// language=Vue
	template: `
		<div :data-id="item.dialogId" class="bx-im-recent-item-wrap">
		<div v-if="!compactMode" :class="{'bx-im-recent-item-no-text': !showLastMessage, 'bx-im-recent-item-pinned': item.pinned}" class="bx-im-recent-item">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar :dialogId="item.dialogId" :size="AvatarSize.L" :withTyping="true"/>
			</div>
			<div class="bx-im-recent-item-content">
				<div class="bx-im-recent-item-content-header">
					<ChatTitle :dialogId="item.dialogId" :withMute="true" />
					<div class="bx-im-recent-date">
						<div v-if="statusIcon" :class="'bx-im-recent-status-icon bx-im-recent-status-icon-' + statusIcon"></div>
						{{ formattedDate }}
					</div>
				</div>
				<div class="bx-im-recent-item-content-bottom">
					<div class="bx-im-recent-message-text-wrap">
						<!-- Message text -->
						<span class="bx-im-recent-message-text">
							<template v-if="item.draft.text && dialog.counter === 0">
								<span class="bx-im-recent-draft-prefix">{{ $Bitrix.Loc.getMessage('IM_RECENT_MESSAGE_DRAFT') }}</span>
								<span>{{ item.draft.text }}</span>
							</template>
							<template v-else-if="item.invitation.isActive">
								<span class="bx-im-recent-message-text-invitation">{{ $Bitrix.Loc.getMessage('IM_RECENT_INVITATION_NOT_ACCEPTED') }}</span>
							</template>
							<template v-else-if="needsBirthdayPlaceholder">
								{{ $Bitrix.Loc.getMessage('IM_RECENT_BIRTHDAY') }}
							</template>
							<template v-else-if="!showLastMessage">
								{{ hiddenMessageText }}
							</template>
							<template v-else>
								<span v-if="isLastMessageAuthor" class="bx-im-recent-last-message-author-icon-self"></span>
								<template v-else-if="isChat && item.message.senderId">
									<span v-if="lastMessageAuthorAvatar" :style="lastMessageAuthorAvatarStyle" class="bx-im-recent-last-message-author-icon-user"></span>
									<span v-else class="bx-im-recent-last-message-author-icon-user bx-im-recent-last-message-author-icon-user-default"></span>
								</template>
								<span>{{ messageText }}</span>
							</template>
						</span>
						<!-- End message text -->
					</div>
					<div :class="{'bx-im-recent-counter-static-wrap-extended': dialog.counter > 99}" class="bx-im-recent-counter-static-wrap">
						<div v-if="item.pinned || dialog.counter > 0" class="bx-im-recent-counter-wrap">
							<div v-if="item.pinned && dialog.counter === 0" class="bx-im-recent-pinned-icon"></div>
							<div v-if="dialog.counter > 0 && !isSelfChat" :class="{'bx-im-recent-counter-muted': isChatMuted}" class="bx-im-recent-counter">
								{{ formattedCounter }}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-if="compactMode" class="bx-im-recent-item" :class="{'bx-im-recent-item-pinned': item.pinned, 'bx-im-recent-item-no-counter': dialog.counter === 0}" ref="container">
			<div class="bx-im-recent-avatar-wrap">
				<Avatar
					:dialogId="item.dialogId"
					:size="AvatarSize.M"
					:withStatus="false"
					:withCounter="true"
				/>
			</div>
			<template v-if="showNewUserPopup">
				<Teleport :to="newUserPopupContainer">
					<NewUserPopup :title="dialog.name" :text="$Bitrix.Loc.getMessage('IM_RECENT_NEW_USER_POPUP_TEXT')" @click="onNewUserPopupClick" @close="onNewUserPopupClose"/>
				</Teleport>
			</template>
		</div>
		</div>
	`
};