import {NotificationTypesCodes} from 'im.v2.const';
import {NotificationItemAvatar} from './item/notification-item-avatar';
import {NotificationItemContent} from './item/notification-item-content';
import {NotificationItemHeader} from './item/notification-item-header';
import '../css/notification-item.css';
import type {ImModelNotification, ImModelUser} from 'im.v2.model';

// @vue/component
export const NotificationItem = {
	components: {NotificationItemAvatar, NotificationItemContent, NotificationItemHeader},
	props: {
		notification: {
			type: Object,
			required: true,
		},
		searchMode: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['dblclick', 'buttonsClick', 'confirmButtonsClick', 'deleteClick', 'sendQuickAnswer', 'moreUsersClick'],
	computed:
	{
		NotificationTypesCodes: () => NotificationTypesCodes,
		notificationItem(): ImModelNotification
		{
			return this.notification;
		},
		type(): number
		{
			return this.notification.sectionCode;
		},
		isUnread(): boolean
		{
			return !this.notificationItem.read && !this.searchMode;
		},
		userData(): ImModelUser
		{
			return this.$store.getters['users/get'](this.notificationItem.authorId, true);
		},
	},
	methods:
	{
		onDoubleClick()
		{
			if (this.searchMode)
			{
				return;
			}

			this.$emit('dblclick', this.notificationItem.id);
		},
		onConfirmButtonsClick(event)
		{
			this.$emit('confirmButtonsClick', event);
		},
		onMoreUsersClick(event)
		{
			this.$emit('moreUsersClick', event);
		},
		onSendQuickAnswer(event)
		{
			this.$emit('sendQuickAnswer', event);
		},
		onDeleteClick(event)
		{
			this.$emit('deleteClick', event);
		}
	},
	template: `
		<div
			class="bx-im-content-notification-item__container"
			:class="{'--unread': isUnread}"
			@dblclick="onDoubleClick"
		>
			<NotificationItemAvatar :userId="notificationItem.authorId" />
			<div class="bx-im-content-notification-item__content-container">
				<NotificationItemHeader 
					:notification="notificationItem"
					@deleteClick="onDeleteClick"
					@moreUsersClick="onMoreUsersClick"
				/>
				<NotificationItemContent 
					:notification="notificationItem" 
					@confirmButtonsClick="onConfirmButtonsClick"
					@sendQuickAnswer="onSendQuickAnswer"
				/>
			</div>
		</div>
	`
};
