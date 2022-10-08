import { BaseEvent } from 'main.core.events';

export default class NotificationEvent extends BaseEvent
{
	static CLICK: string = 'click';
	static ACTION: string = 'action';
	static CLOSE: string = 'close';

	static getTypes(): Array<string>
	{
		return [
			NotificationEvent.CLICK,
			NotificationEvent.ACTION,
			NotificationEvent.CLOSE,
		];
	}

	static isSupported(eventType: string): boolean
	{
		return NotificationEvent.getTypes().includes(eventType);
	}
}
