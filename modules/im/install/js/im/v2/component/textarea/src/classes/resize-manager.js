import { EventEmitter } from 'main.core.events';

type Options = {
	direction: $Values<typeof ResizeDirection>,
	maxHeight: number,
	minHeight: number,
};

export const ResizeDirection = {
	up: 'up',
	down: 'down',
};

export class ResizeManager extends EventEmitter
{
	static eventNamespace = 'BX.Messenger.v2.Textarea.ResizeManager';
	static events = {
		onHeightChange: 'onHeightChange',
		onResizeStop: 'onResizeStop',
	};

	isDragging: boolean = false;
	direction: $Values<typeof ResizeDirection>;
	maxHeight: number;
	minHeight: number;

	constructor(options: Options = {})
	{
		super();
		const { direction, maxHeight, minHeight } = options;
		this.direction = direction;
		this.maxHeight = maxHeight;
		this.minHeight = minHeight;

		this.setEventNamespace(ResizeManager.eventNamespace);
	}

	onResizeStart(event, currentHeight)
	{
		if (this.isDragging)
		{
			return;
		}

		this.isDragging = true;

		this.resizeCursorStartPoint = event.clientY;
		this.resizeHeightStartPoint = currentHeight;

		this.addResizeEvents();
	}

	onResizeContinue(event)
	{
		if (!this.isDragging)
		{
			return;
		}

		this.resizeCursorControlPoint = event.clientY;

		const maxPoint = this.#calculateNewMaxPoint();
		const newHeight = Math.max(maxPoint, this.minHeight);

		this.emit(ResizeManager.events.onHeightChange, { newHeight });
	}

	onResizeStop()
	{
		if (!this.isDragging)
		{
			return;
		}

		this.isDragging = false;
		this.removeResizeEvents();

		this.emit(ResizeManager.events.onResizeStop);
	}

	#calculateNewMaxPoint(): number
	{
		const distance = this.direction === ResizeDirection.up
			? this.resizeCursorStartPoint - this.resizeCursorControlPoint
			: this.resizeCursorControlPoint - this.resizeCursorStartPoint;

		return Math.min(this.resizeHeightStartPoint + distance, this.maxHeight);
	}

	addResizeEvents()
	{
		this.onContinueDragHandler = this.onResizeContinue.bind(this);
		this.onStopDragHandler = this.onResizeStop.bind(this);
		document.addEventListener('mousemove', this.onContinueDragHandler);
		document.addEventListener('touchmove', this.onContinueDragHandler);
		document.addEventListener('touchend', this.onStopDragHandler);
		document.addEventListener('mouseup', this.onStopDragHandler);
		document.addEventListener('mouseleave', this.onStopDragHandler);
	}

	removeResizeEvents()
	{
		document.removeEventListener('mousemove', this.onContinueDragHandler);
		document.removeEventListener('touchmove', this.onContinueDragHandler);
		document.removeEventListener('touchend', this.onStopDragHandler);
		document.removeEventListener('mouseup', this.onStopDragHandler);
		document.removeEventListener('mouseleave', this.onStopDragHandler);
	}

	destroy()
	{
		this.removeResizeEvents();
	}
}
