import {BaseEvent} from 'main.core.events';

export type TabOptions = {
	id: string | number,
	icon: string,
	activeIcon: string,
	header: string,
	active: boolean,
	content: HTMLElement,
	events: {
		onHeaderClick: (event: BaseEvent) => void,
	},
};

export type TabsOptions = {
	tabs: Array<TabOptions>,
	defaultState: 'initials' | 'touch' | 'image',
	events?: {
		onChange?: (event: BaseEvent) => void,
	},
};