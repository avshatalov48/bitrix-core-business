import BaseEvent from './base.event';

export class DragOverDropzoneEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		source: HTMLElement,
		sourceContainer: HTMLElement,
		originalSource: HTMLElement,
		originalOver: HTMLElement,
		dropzone: HTMLElement,
	};
}