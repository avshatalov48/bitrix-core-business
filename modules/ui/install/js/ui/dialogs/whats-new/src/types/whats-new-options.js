import type { PopupOptions } from 'main.popup';
import type { BaseEvent } from 'main.core.events';
import type { SlideOptions } from './slide-options';

export type WhatsNewOptions = {
	slides: SlideOptions[],
	popupOptions?: PopupOptions,
	infinityLoop?: boolean,
	events?: { [eventName: string]: (event: BaseEvent) => void },
};