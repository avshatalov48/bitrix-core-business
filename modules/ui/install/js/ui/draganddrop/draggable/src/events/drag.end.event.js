import BaseEvent from './base.event';

export class DragEndEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		source: HTMLElement,
		sourceContainer: HTMLElement,
		originalSource: HTMLElement,
	};
}