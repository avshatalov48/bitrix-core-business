import type { BaseEvent } from 'main.core.events';

export type UploaderFileOptions = {
	id?: string,
	name?: string,
	type?: string,
	size?: number,
	events?: { [eventName: string]: (event: BaseEvent) => void },
};