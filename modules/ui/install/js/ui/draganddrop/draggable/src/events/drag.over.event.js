import BaseEvent from './base.event';

export class DragOverEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		source: HTMLElement,
		sourceContainer: HTMLElement,
		originalSource: HTMLElement,
		over: HTMLElement,
		originalOver: HTMLElement,
		overContainer: HTMLElement,
	};
}