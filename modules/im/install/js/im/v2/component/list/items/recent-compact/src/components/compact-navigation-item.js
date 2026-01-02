import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { Messenger } from 'im.public';
import { NavigationMenuItem, Color } from 'im.v2.const';

const NavigationItemToIcon = Object.freeze({
	[NavigationMenuItem.notification]: OutlineIcons.NOTIFICATION,
	[NavigationMenuItem.copilot]: OutlineIcons.COPILOT,
	[NavigationMenuItem.openlines]: OutlineIcons.OPEN_CHANNELS,
	[NavigationMenuItem.openlinesV2]: OutlineIcons.OPEN_CHANNELS,
});

const ICON_SIZE = 24;

// @vue/component
export const CompactNavigationItem = {
	name: 'CompactNavigationItem',
	components: { BIcon },
	props: {
		id: {
			type: String,
			required: true,
		},
	},
	computed: {
		ICON_SIZE: () => ICON_SIZE,
		Color: () => Color,
		NavigationItemToIcon: () => NavigationItemToIcon,
		counter(): number
		{
			const counterToItemId = {
				[NavigationMenuItem.notification]: 'notifications/getCounter',
				[NavigationMenuItem.copilot]: 'counters/getTotalCopilotCounter',
				[NavigationMenuItem.openlines]: 'counters/getTotalLinesCounter',
				[NavigationMenuItem.openlinesV2]: 'counters/getTotalLinesCounter',
			};

			return this.$store.getters[counterToItemId[this.id]] ?? 0;
		},
		hasCounter(): boolean
		{
			return this.counter > 0;
		},
		formattedCounter(): string
		{
			if (!this.hasCounter)
			{
				return '';
			}

			return this.counter > 99 ? '99+' : String(this.counter);
		},
		iconColorToken(): string
		{
			if (this.counter > 0)
			{
				return 'var(--ui-color-design-outline-content)';
			}

			return 'var(--ui-color-design-outline-na-content)';
		},
	},
	methods: {
		onNavigationItemClick(): void
		{
			void Messenger.openNavigationItem({ id: this.id });
		},
	},
	template: `
		<div class="bx-im-compact-navigation__icon --ui-hoverable" @click="onNavigationItemClick">
			<BIcon
				:key="id"
				:name="NavigationItemToIcon[id]"
				:hoverable-alt="true"
				:color="iconColorToken"
				:size="ICON_SIZE"
			/>
			<div
				v-if="hasCounter"
				class="bx-im-compact-navigation__icon-counter"
			>
				{{ formattedCounter }}
			</div>
		</div>
	`,
};
