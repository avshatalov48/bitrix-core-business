import 'ui.design-tokens.air';
import { Extension } from 'main.core';

import { NavigationMenuItem } from 'im.v2.const';

import { CompactNavigationItem } from './compact-navigation-item';

import '../css/compact-navigation.css';

const CompactNavigationItems = [
	NavigationMenuItem.notification,
	NavigationMenuItem.copilot,
	NavigationMenuItem.openlines,
	NavigationMenuItem.openlinesV2,
];

type NavigationItem = {
	id: string,
	text: string,
	entityId: number | null,
}

// @vue/component
export const CompactNavigation = {
	name: 'CompactNavigation',
	components: { CompactNavigationItem },
	computed: {
		availableNavigationItems(): string[]
		{
			const settings = Extension.getSettings('im.v2.component.list.items.recent-compact');
			const items: NavigationItem[] = settings.get('navigationItems', []);

			return items.map((item) => item.id);
		},
		preparedNavigationItems(): string[]
		{
			return CompactNavigationItems.filter((item) => this.availableNavigationItems.includes(item));
		},
	},
	template: `
		<div class="bx-im-compact-navigation__container">
			<div class="bx-im-compact-navigation__items">
				<CompactNavigationItem
					v-for="navigationItemId in preparedNavigationItems"
					:id="navigationItemId"
					:key="navigationItemId"
				/>
			</div>
			<div class="bx-im-compact-navigation__delimiter"></div>
		</div>
	`,
};
