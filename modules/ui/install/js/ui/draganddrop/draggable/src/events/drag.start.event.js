import BaseEvent from './base.event';

export class DragStartEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		pointerOffsetX: number,
		pointerOffsetY: number,
		source: HTMLElement,
		sourceContainer: HTMLElement,
		originalSource: HTMLElement,
	};
}