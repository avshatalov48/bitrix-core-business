import BaseEvent from './base.event';

export class DragMoveEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		offsetX: number,
		offsetY: number,
		source: HTMLElement,
		sourceContainer: HTMLElement,
		originalSource: HTMLElement,
	};
}
