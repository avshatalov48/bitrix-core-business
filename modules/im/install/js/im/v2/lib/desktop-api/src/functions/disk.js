import { EventType } from 'im.v2.const';
import { EventEmitter, BaseEvent } from 'main.core.events';

export const diskFunctions = {
	startDiskSync(): void
	{
		BXFileStorage?.SyncPause(false);
		const event = new BaseEvent({ compatData: [true] });
		EventEmitter.emit(window, EventType.desktop.onSyncPause, event);
	},
	stopDiskSync(): void
	{
		BXFileStorage?.SyncPause(true);
		const event = new BaseEvent({ compatData: [false] });
		EventEmitter.emit(window, EventType.desktop.onSyncPause, event);
	},
};
