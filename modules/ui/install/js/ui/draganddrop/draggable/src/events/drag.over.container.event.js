import BaseEvent from './base.event';

export class DragOverContainerEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		source: HTMLElement,
		sourceContainer: HTMLElement,
		originalSource: HTMLElement,
		over: HTMLElement,
	};
}