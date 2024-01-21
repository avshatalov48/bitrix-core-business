import type { JsonObject } from 'main.core';

import { NotificationSettingsBlock } from 'im.v2.const';

import { NotificationItem } from './notification-item';

// @vue/component
export const NotificationBlock = {
	name: 'NotificationBlock',
	components: { NotificationItem },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		block(): NotificationSettingsBlock
		{
			return this.item;
		},
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-settings-section-content__body">
			<div class="bx-im-settings-section-content__block">
				<div class="bx-im-settings-section-content__block_title">
					{{ block.label }}
				</div>
				<div class="bx-im-settings-expert-notifications__header">
					<div class="bx-im-settings-expert-notifications__header_title"></div>
					<div class="bx-im-settings-expert-notifications__header_type">
						{{ loc('IM_CONTENT_SETTINGS_EXPERT_NOTIFICATIONS_TYPE_WEB') }}
					</div>
					<div class="bx-im-settings-expert-notifications__header_type">
						{{ loc('IM_CONTENT_SETTINGS_EXPERT_NOTIFICATIONS_TYPE_MAIL') }}
					</div>
					<div class="bx-im-settings-expert-notifications__header_type">
						{{ loc('IM_CONTENT_SETTINGS_EXPERT_NOTIFICATIONS_TYPE_PUSH') }}
					</div>
				</div>
				<NotificationItem v-for="item in block.items" :item="item" :blockId="block.id" :key="item.id" />
			</div>
		</div>
	`,
};
