import type { BaseEvent } from 'main.core.events';
import type { BitrixVueComponentProps } from 'ui.vue3';

import type { TileWidgetSlot } from './tile-widget-slot';

export type TileWidgetOptions = {
	slots?: Object<$Values<TileWidgetSlot>, BitrixVueComponentProps>,
	showSettingsButton?: boolean,
	showItemMenuButton?: boolean,
	autoCollapse?: boolean,
	events?: Object<string, (event: BaseEvent) => {}>,
};
