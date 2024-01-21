import BaseEvent from '../../events/base.event';

export class DragStartSensorEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		originalSource: HTMLElement,
		originalEvent: MouseEvent | TouchEvent,
		sourceContainer: HTMLElement,
	};
}
