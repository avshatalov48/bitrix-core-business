import BaseEvent from '../../events/base.event';

export class DragMoveSensorEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		originalSource: HTMLElement,
		sourceContainer: HTMLElement,
		over: HTMLElement,
		overContainer: HTMLElement,
		originalEvent: MouseEvent | TouchEvent,
	};
}
