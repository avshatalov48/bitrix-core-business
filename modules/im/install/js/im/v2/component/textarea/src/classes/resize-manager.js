import {EventEmitter} from 'main.core.events';

export class ResizeManager extends EventEmitter
{
	static eventNamespace = 'BX.Messenger.v2.Textarea.ResizeManager';
	static minHeight: number = 22;
	static maxHeight: number = 400;
	static events = {
		onHeightChange: 'onHeightChange',
		onResizeStop: 'onResizeStop'
	};

	isDragging: boolean = false;

	constructor()
	{
		super();
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

		const maxPoint = Math.min(this.resizeHeightStartPoint + this.resizeCursorStartPoint - this.resizeCursorControlPoint, ResizeManager.maxHeight);
		const newHeight = Math.max(maxPoint, ResizeManager.minHeight);

		this.emit(ResizeManager.events.onHeightChange, {newHeight: newHeight});
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
