import {Messenger} from 'im.public';
import {Layout, NotificationTypesCodes} from 'im.v2.const';
import {Avatar, AvatarSize, ChatTitle} from 'im.v2.component.elements';
import {DateFormatter, DateTemplate} from 'im.v2.lib.date-formatter';

import '../../css/notification-item-header.css';

import type {ImModelUser, ImModelNotification} from 'im.v2.model';

// @vue/component
export const NotificationItemHeader = {
	name: 'NotificationItemHeader',
	components: {Avatar, AvatarSize, ChatTitle},
	props: {
		notification: {
			type: Object,
			required: true
		}
	},
	computed:
	{
		notificationItem(): ImModelNotification
		{
			return this.notification;
		},
		date(): Date
		{
			return this.notificationItem.date;
		},
		type(): NotificationTypesCodes
		{
			return this.notificationItem.sectionCode;
		},
		user(): ImModelUser
		{
			return this.$store.getters['users/get'](this.notificationItem.authorId, true);
		},
		hasName(): boolean
		{
			return this.notificationItem.authorId > 0 && this.user.name.length > 0;
		},
		title(): string
		{
			if (this.notificationItem.title.length > 0)
			{
				return this.notificationItem.title;
			}

			return this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_ITEM_SYSTEM');
		},
		isSystem(): boolean
		{
			return this.notification.authorId === 0;
		},
		userDialogId(): string
		{
			return this.notification.authorId.toString();
		},
		titleClasses()
		{
			return {
				'bx-im-content-notification-item-header__title-text': true,
				'bx-im-content-notification-item-header__title-user-text': !this.isSystem,
				'--extranet': this.user.extranet,
				'--short': !this.hasMoreUsers
			};
		},
		hasMoreUsers(): boolean
		{
			if (this.isSystem)
			{
				return false;
			}

			return !!this.notificationItem.params?.USERS && this.notificationItem.params.USERS.length > 0;
		},
		moreUsers(): string
		{
			const phrase = this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_MORE_USERS').split('#COUNT#');

			return {
				start: phrase[0],
				end: this.notificationItem.params.USERS.length + phrase[1]
			};
		},
		canDelete()
		{
			return this.type === NotificationTypesCodes.simple;
		},
		itemDate(): string
		{
			return DateFormatter.formatByTemplate(this.date, DateTemplate.notification);
		}
	},
	methods:
	{
		onUserTitleClick()
		{
			if (this.isSystem)
			{
				return;
			}

			Messenger.openChat(this.userDialogId);
		},
		onMoreUsersClick(event)
		{
			if (event.users)
			{
				this.$emit('moreUsersClick', {
					event: event.event,
					users: event.users
				});
			}
		},
		onDeleteClick()
		{
			this.$emit('deleteClick', this.notificationItem.id);
		},
	},
	template: `
		<div class="bx-im-content-notification-item-header__container">
			<div class="bx-im-content-notification-item-header__title-container">
				<ChatTitle
					v-if="hasName"
					:dialogId="userDialogId"
					:showItsYou="false"
					:class="titleClasses"
					@click.prevent="onUserTitleClick"
				/>
				<span v-else @click.prevent="onUserTitleClick" :class="titleClasses">{{ title }}</span>
				<span v-if="hasMoreUsers" class="bx-im-content-notification-item-header__more-users">
					<span class="bx-im-content-notification-item-header__more-users-start">{{ moreUsers.start }}</span>
					<span
						class="bx-im-content-notification-item-header__more-users-dropdown"
						@click="onMoreUsersClick({users: notificationItem.params.USERS, event: $event})"
					>
						{{ moreUsers.end }}
					</span>
				</span>
			</div>
			<div class="bx-im-content-notification-item-header__date-container">
				<div class="bx-im-content-notification-item-header__date">{{ itemDate }}</div>
				<div
					v-if="canDelete"
					class="bx-im-content-notification-item-header__delete-button"
					@click="onDeleteClick()"
				>
				</div>
			</div>
		</div>
	`
};