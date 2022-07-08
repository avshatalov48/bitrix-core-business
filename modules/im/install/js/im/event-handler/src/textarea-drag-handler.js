import {EventEmitter} from "main.core.events";
import {Utils} from "im.lib.utils";
import {Type} from "main.core";

export class TextareaDragHandler extends EventEmitter
{
	static events = {
		onHeightChange: 'onHeightChange',
		onStopDrag: 'onStopDrag'
	};

	isDragging: boolean = false;
	minimumHeight: number = 120;
	maximumHeight: number = 400;

	constructor(events)
	{
		super();
		this.setEventNamespace('BX.IM.TextareaDragHandler');
		this.subscribeToEvents(events);

		if (Utils.device.isMobile())
		{
			this.maximumHeight = 200;
		}
	}

	subscribeToEvents(configEvents)
	{
		const events = Type.isObject(configEvents) ? configEvents : {};
		Object.entries(events).forEach(([name, callback]) => {
			if (Type.isFunction(callback))
			{
				this.subscribe(name, callback);
			}
		});
	}

	onStartDrag(event, currentHeight)
	{
		if (this.isDragging)
		{
			return;
		}

		this.isDragging = true;

		event = event.changedTouches ? event.changedTouches[0] : event;

		this.textareaDragCursorStartPoint = event.clientY;
		this.textareaDragHeightStartPoint = currentHeight;

		this.addTextareaDragEvents();
	}

	onTextareaContinueDrag(event)
	{
		if (!this.isDragging)
		{
			return;
		}

		event = event.changedTouches ? event.changedTouches[0] : event;

		this.textareaDragCursorControlPoint = event.clientY;

		const maxPoint = Math.min(this.textareaDragHeightStartPoint + this.textareaDragCursorStartPoint - this.textareaDragCursorControlPoint, this.maximumHeight);
		const newTextareaHeight = Math.max(maxPoint, this.minimumHeight);

		this.emit(TextareaDragHandler.events.onHeightChange, {newHeight: newTextareaHeight});
	}

	onTextareaStopDrag()
	{
		if (!this.isDragging)
		{
			return;
		}

		this.isDragging = false;
		this.removeTextareaDragEvents();

		this.emit(TextareaDragHandler.events.onStopDrag);
	}

	addTextareaDragEvents()
	{
		this.onContinueDragHandler = this.onTextareaContinueDrag.bind(this);
		this.onStopDragHandler = this.onTextareaStopDrag.bind(this);
		document.addEventListener('mousemove', this.onContinueDragHandler);
		document.addEventListener('touchmove', this.onContinueDragHandler);
		document.addEventListener('touchend', this.onStopDragHandler);
		document.addEventListener('mouseup', this.onStopDragHandler);
		document.addEventListener('mouseleave', this.onStopDragHandler);
	}

	removeTextareaDragEvents()
	{
		document.removeEventListener('mousemove', this.onContinueDragHandler);
		document.removeEventListener('touchmove', this.onContinueDragHandler);
		document.removeEventListener('touchend', this.onStopDragHandler);
		document.removeEventListener('mouseup', this.onStopDragHandler);
		document.removeEventListener('mouseleave', this.onStopDragHandler);
	}

	destroy()
	{
		this.removeTextareaDragEvents();
	}
}