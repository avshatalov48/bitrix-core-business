import {Attach} from 'im.v2.component.elements';
import {Utils} from 'im.v2.lib.utils';
import {Parser} from 'im.v2.lib.parser';
import {NotificationQuickAnswer} from '../notification-quick-answer';
import {NotificationItemConfirmButtons} from './notification-item-confirm-buttons';
import '../../css/notification-item-content.css';

// @vue/component
export const NotificationItemContent = {
	name: 'NotificationItemContent',
	components: {NotificationQuickAnswer, Attach, NotificationItemConfirmButtons},
	props: {
		notification: {
			type: Object,
			required: true
		}
	},
	emits: ['confirmButtonsClick', 'sendQuickAnswer'],
	computed:
	{
		notificationItem(): Object
		{
			return this.notification;
		},
		hasQuickAnswer(): boolean
		{
			return !!this.notification.params?.CAN_ANSWER && this.notification.params.CAN_ANSWER === 'Y';
		},
		content(): string
		{
			return Parser.decodeNotification(this.notification);
		},
		attachList(): ?Array
		{
			return this.notification.params?.ATTACH;
		},
	},
	methods:
	{
		onContentClick(event)
		{
			Parser.executeClickEvent(event);
		},
		onConfirmButtonsClick(event)
		{
			this.$emit('confirmButtonsClick', event);
		},
		onSendQuickAnswer(event)
		{
			this.$emit('sendQuickAnswer', event);
		}
	},
	template: `
		<div class="bx-im-content-notification-item-content__container" @click="onContentClick">
			<div 
				v-if="content.length > 0" 
				class="bx-im-content-notification-item-content__content-text"
				v-html="content"
			></div>
			<NotificationQuickAnswer 
				v-if="hasQuickAnswer" 
				:notification="notificationItem" 
				@sendQuickAnswer="onSendQuickAnswer"
			/>
			<template v-if="attachList">
				<template v-for="attachItem in attachList">
					<Attach :config="attachItem"/>
				</template>
			</template>
			<NotificationItemConfirmButtons 
				v-if="notificationItem.notifyButtons.length > 0" 
				@confirmButtonsClick="onConfirmButtonsClick" 
				:buttons="notificationItem.notifyButtons"
			/>
		</div>
	`
};