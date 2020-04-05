import BaseEvent from '../../events/base.event';

export class DragDropSensorEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		originalSource: HTMLElement,
		sourceContainer: HTMLElement,
		over: HTMLElement,
		overContainer: HTMLElement,
		originalEvent: HTMLElement,
		dropzone: HTMLElement,
	};
}