import BaseEvent from './base.event';

export class DragOutDropzoneEvent extends BaseEvent
{
	data: {
		clientX: number,
		clientY: number,
		source: HTMLElement,
		sourceContainer: HTMLElement,
		originalSource: HTMLElement,
		dropzone: HTMLElement,
	};
}
