import Sensor from '../sensor';
import {DragStartSensorEvent} from '../events/drag.start.sensor.event';
import {DragMoveSensorEvent} from '../events/drag.move.sensor.event';
import {DragEndSensorEvent} from '../events/drag.end.sensor.event';
import {DragDropSensorEvent} from '../events/drag.drop.sensor.event';

export default class MouseSensor extends Sensor
{
	constructor(container = [], options = {})
	{
		super(container, options);

		this.mousedownTimeoutId = null;

		this.onMouseDown = this.onMouseDown.bind(this);
		this.onMouseMove = this.onMouseMove.bind(this);
		this.onMouseUp = this.onMouseUp.bind(this);
		this.onDragStart = this.onDragStart.bind(this);
	}

	enable()
	{
		this.getDocument().addEventListener('mousedown', this.onMouseDown, true);
	}

	disable()
	{
		this.getDocument().removeEventListener('mousedown', this.onMouseDown, true);
	}

	startHandleMouseUp()
	{
		this.getDocument().addEventListener('mouseup', this.onMouseUp);
	}

	stopHandleMouseUp()
	{
		this.getDocument().removeEventListener('mouseup', this.onMouseUp);
	}

	startHandleMouseMove()
	{
		this.getDocument().addEventListener('mousemove', this.onMouseMove);
	}

	stopHandleMouseMove()
	{
		this.getDocument().removeEventListener('mousemove', this.onMouseMove);
	}

	startPreventContextMenu()
	{
		this.getDocument().addEventListener('contextmenu', this.preventDefaultEventAction, true);
	}

	stopPreventContextMenu()
	{
		this.getDocument().removeEventListener('contextmenu', this.preventDefaultEventAction, true);
	}

	startPreventNativeDragAndDrop()
	{
		this.getDocument().addEventListener('dragstart', this.preventDefaultEventAction);
	}

	stopPreventNativeDragAndDrop()
	{
		this.getDocument().removeEventListener('dragstart', this.preventDefaultEventAction);
	}

	onMouseDown(event: MouseEvent)
	{
		if (!event.ctrlKey && !event.metaKey && !event.button)
		{
			this.originalDragStartEvent = event;
			const container = this.getContainerByChild(event.target);

			if (container)
			{
				const dragElement = this.getDragElementByChild(event.target);
				if (dragElement)
				{
					this.startHandleMouseUp();
					this.startPreventNativeDragAndDrop();

					this.mousedownTimeoutId = setTimeout(() => {
						this.onDragStart();
					}, this.options.delay);
				}
			}
		}
	}

	onDragStart()
	{
		const sourceContainer = this.getContainerByChild(
			this.originalDragStartEvent.target,
		);

		this.dragStartEvent = new DragStartSensorEvent({
			clientX: this.originalDragStartEvent.clientX,
			clientY: this.originalDragStartEvent.clientY,
			originalSource: this.originalDragStartEvent.target,
			originalEvent: this.originalDragStartEvent,
			sourceContainer,
		});

		this.emit('drag:start', this.dragStartEvent);

		if (this.isDragging())
		{
			this.startPreventContextMenu();
			this.startHandleMouseMove();
		}
	}

	onMouseMove(originalEvent)
	{
		if (this.isDragging())
		{
			const {clientX, clientY} = originalEvent;
			const over = this.getElementFromPoint(clientX, clientY);
			const overContainer = this.getContainerByChild(over);
			const {originalSource, sourceContainer} = this.dragStartEvent.data;

			const dragMoveEvent = new DragMoveSensorEvent({
				clientX,
				clientY,
				originalSource,
				sourceContainer,
				over,
				overContainer,
				originalEvent,
			});

			this.emit('drag:move', dragMoveEvent);
		}
	}

	onMouseUp(originalEvent)
	{
		clearTimeout(this.mousedownTimeoutId);

		this.stopHandleMouseUp();
		this.stopPreventNativeDragAndDrop();

		if (this.isDragging())
		{
			const {clientX, clientY} = originalEvent;
			const over = this.getElementFromPoint(clientX, clientY);
			const overContainer = this.getContainerByChild(over);
			const {originalSource, sourceContainer} = this.dragStartEvent.data;

			const dragEndEvent = new DragEndSensorEvent({
				clientX,
				clientY,
				originalSource,
				sourceContainer,
				over,
				overContainer,
				originalEvent,
			});

			this.emit('drag:end', dragEndEvent);

			if (!dragEndEvent.isDefaultPrevented())
			{
				const dropzone = this.getDropzoneByChild(over);

				if (dropzone)
				{
					const dragDropEvent = new DragDropSensorEvent({
						clientX,
						clientY,
						originalSource,
						sourceContainer,
						over,
						overContainer,
						originalEvent,
						dropzone,
					});

					this.emit('drag:drop', dragDropEvent);
				}
			}

			this.stopPreventContextMenu();
			this.stopHandleMouseMove();
		}

		this.originalDragStartEvent = null;
	}
}