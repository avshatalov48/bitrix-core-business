import {BaseEvent} from 'main.core.events';

export type FooterOptions = {
	mode: 'desktop' | 'mobile',
	messages?: {[key: string]: string},
	events: {
		onSaveClick?: (event: BaseEvent) => void,
		onSaveClickAsync?: (event: BaseEvent) => Promise<any>,
		onCancelClick?: (event: BaseEvent) => void,
	},
};