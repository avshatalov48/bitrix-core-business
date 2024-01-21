import { NotificationSettingsBlock } from 'im.v2.const';

import { NotificationBlock } from './components/notification-block';

import './css/expert-notification-list.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const ExpertNotificationList = {
	name: 'ExpertNotificationList',
	components: { NotificationBlock },
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		notificationSettings(): NotificationSettingsBlock[]
		{
			const settings = this.$store.getters['application/settings/get']('notifications');

			return Object.values(settings);
		},
	},
	template: `
		<NotificationBlock
			v-for="block in notificationSettings"
			:item="block"
			:key="block.id"
			class="bx-im-settings-expert-notifications__container"
		/>
	`,
};
