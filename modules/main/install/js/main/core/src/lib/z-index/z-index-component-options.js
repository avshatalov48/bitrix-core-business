import type BaseEvent from '../event/base-event';

export type ZIndexComponentOptions = {
	alwaysOnTop?: boolean | number,
	overlay?: HTMLElement,
	overlayGap?: number,
	events?: { [eventName: string]: (event: BaseEvent) => void },
};