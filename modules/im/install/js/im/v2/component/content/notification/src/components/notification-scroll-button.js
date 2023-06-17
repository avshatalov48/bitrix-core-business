import {NotificationTypesCodes} from 'im.v2.const';
import {mapState} from 'ui.vue3.vuex';

import '../css/notification-scroll-button.css';

export const NotificationScrollButton = {
	name: 'NotificationScrollButton',
	props: {
		unreadCounter: {
			type: Number,
			default: 0
		},
		notificationsOnScreen: {
			type: Object,
			required: true
		},
	},
	emits: ['scrollButtonClick'],
	computed:
	{
		notificationCollection(): Array
		{
			return this.$store.getters['notifications/getSortedCollection'];
		},
		hasUnreadOnScreen(): boolean
		{
			return [...this.notificationsOnScreen].some(id => !this.notificationMapCollection.get(id)?.read);
		},
		firstUnreadId(): ?number
		{
			const item = this.notificationCollection.find(notification => !notification.read);
			if (!item)
			{
				return;
			}

			return item.id;
		},
		firstUnreadBelowVisible(): ?number
		{
			const minIdOnScreen = Math.min(...this.notificationsOnScreen);

			const item = this.notificationCollection.find(notification => {
				return !notification.read
					&& notification.sectionCode === NotificationTypesCodes.simple
					&& minIdOnScreen > notification.id
				;
			});

			if (!item)
			{
				return;
			}

			return item.id;
		},
		hasUnreadBelowVisible(): boolean
		{
			let unreadCounterBeforeVisible = 0;

			for (let i = 0; i <= this.notificationCollection.length - 1; i++)
			{
				if (!this.notificationCollection[i].read)
				{
					++unreadCounterBeforeVisible;
				}

				// In this case we decide that there is no more unread notifications below visible notifications,
				// so we show arrow up on scroll button.
				if (
					this.notificationsOnScreen.has(this.notificationCollection[i].id)
					&& this.unreadCounter === unreadCounterBeforeVisible
				)
				{
					return false;
				}
			}

			return true;
		},
		showScrollButton()
		{
			// todo: check BXIM.settings.notifyAutoRead
			if (this.unreadCounter === 0 || this.hasUnreadOnScreen)
			{
				return false;
			}

			return true;
		},
		arrowButtonClass()
		{
			const arrowDown = this.hasUnreadBelowVisible;

			return {
				'bx-im-notifications-scroll-button-arrow-down': arrowDown,
				'bx-im-notifications-scroll-button-arrow-up': !arrowDown,
			};
		},
		formattedCounter(): string
		{
			if (this.unreadCounter > 99)
			{
				return '99+';
			}

			return `${this.unreadCounter}`;
		},
		...mapState({
			notificationMapCollection: state => state.notifications.collection,
		})
	},
	methods:
	{
		onScrollButtonClick()
		{
			let idToScroll = null;
			if (this.firstUnreadBelowVisible)
			{
				idToScroll = this.firstUnreadBelowVisible;
			}
			else if (!this.hasUnreadBelowVisible)
			{
				idToScroll = this.firstUnreadId;
			}

			let firstUnreadNode = null;
			if (idToScroll !== null)
			{
				const selector = `.bx-im-content-notification-item__container[data-id="${idToScroll}"]`;
				firstUnreadNode = document.querySelector(selector);
			}

			if (firstUnreadNode)
			{
				this.$emit('scrollButtonClick', firstUnreadNode.offsetTop);
			}
			else
			{
				const latestNotification = this.notificationCollection[this.notificationCollection.length - 1];
				const selector = `.bx-im-content-notification-item__container[data-id="${latestNotification.id}"]`;
				const latestNotificationNode = document.querySelector(selector);

				this.$emit('scrollButtonClick', latestNotificationNode.offsetTop);
			}
		},
	},
	template: `
		<transition name="bx-im-notifications-scroll-button">
			<div 
				v-show="showScrollButton" 
				class="bx-im-content-notification-scroll-button__container" 
				@click="onScrollButtonClick"
			>
				<div class="bx-im-content-notification-scroll-button__button">
					<div class="bx-im-notifications-scroll-button-counter">
						{{ formattedCounter }}
					</div>
					<div :class="arrowButtonClass"></div>
				</div>
			</div>
		</transition>
	`
};