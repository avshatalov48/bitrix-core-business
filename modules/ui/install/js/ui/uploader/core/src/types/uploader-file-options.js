import type { BaseEvent } from 'main.core.events';
import { FileEvent } from '../enums/file-event';

export type UploaderFileOptions = {
	id?: string,
	name?: string,
	type?: string,
	size?: number,
	events?: { [eventName: $Values<FileEvent>]: (event: BaseEvent) => void },
};