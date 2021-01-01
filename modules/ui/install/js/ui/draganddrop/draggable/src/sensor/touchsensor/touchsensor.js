import Sensor from '../sensor';
import {DragStartSensorEvent} from '../events/drag.start.sensor.event';
import {DragMoveSensorEvent} from '../events/drag.move.sensor.event';
import {DragEndSensorEvent} from '../events/drag.end.sensor.event';
import {DragDropSensorEvent} from '../events/drag.drop.sensor.event';

let preventScrolling = false;
window.addEventListener(
	'touchmove',
	(event) => {
		if (preventScrolling)
		{
			event.preventDefault();
		}
	},
	{passive: false},
);

export default class TouchSensor extends Sensor
{
	constructor(container = [], options = {})
	{
		super(container, options);

		this.tapTimeoutId = null;
		this.touchMoved = false;

		this.onTouchStart = this.onTouchStart.bind(this);
		this.onTouchEnd = this.onTouchEnd.bind(this);
		this.onTouchMove = this.onTouchMove.bind(this);
		this.onDragStart = this.onDragStart.bind(this);
	}

	enable()
	{
		this.getDocument().addEventListener('touchstart', this.onTouchStart);
	}

	disable()
	{
		this.getDocument().removeEventListener('touchstart', this.onTouchStart);
	}

	isTouchMoved(): boolean
	{
		return this.touchMoved;
	}

	// eslint-disable-next-line class-methods-use-this
	startPreventScrolling()
	{
		preventScrolling = true;
	}

	// eslint-disable-next-line class-methods-use-this
	stopPreventScrolling()
	{
		preventScrolling = false;
	}

	startPreventContextMenu()
	{
		this.getDocument().addEventListener('contextmenu', this.preventDefaultEventAction, true);
	}

	stopPreventContextMenu()
	{
		this.getDocument().removeEventListener('contextmenu', this.preventDefaultEventAction, true);
	}

	startHandleTouchEvents()
	{
		this.getDocument().addEventListener('touchmove', this.onTouchMove);
		this.getDocument().addEventListener('touchend', this.onTouchEnd);
		this.getDocument().addEventListener('touchcancel', this.onTouchEnd);
	}

	stopHandleTouchEvents()
	{
		this.getDocument().removeEventListener('touchmove', this.onTouchMove);
		this.getDocument().removeEventListener('touchend', this.onTouchEnd);
		this.getDocument().removeEventListener('touchcancel', this.onTouchEnd);
	}

	onTouchStart(event)
	{
		const container = this.getContainerByChild(event.target);
		if (container)
		{
			const dragElement = this.getDragElementByChild(event.target);
			if (dragElement)
			{
				this.originalDragStartEvent = event;

				this.startHandleTouchEvents();
				this.startPreventContextMenu();
				this.startPreventScrolling();

				this.tapTimeoutId = setTimeout(() => {
					if (!this.isTouchMoved())
					{
						this.onDragStart();
					}
				}, this.options.delay);
			}
		}
	}

	onDragStart()
	{
		const touch = (
			this.originalDragStartEvent.touches[0]
			|| this.originalDragStartEvent.changedTouches[0]
		);

		const sourceContainer = this.getContainerByChild(this.originalDragStartEvent.target);

		this.dragStartEvent = new DragStartSensorEvent({
			clientX: touch.clientX,
			clientY: touch.clientY,
			originalSource: this.originalDragStartEvent.target,
			originalEvent: this.originalDragStartEvent,
			sourceContainer,
		});

		this.emit('drag:start', this.dragStartEvent);
	}

	onTouchMove(originalEvent)
	{
		this.touchMoved = true;

		if (this.isDragging())
		{
			const touch = originalEvent.touches[0] || originalEvent.changedTouches[0];
			const {clientX, clientY} = touch;
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

	onTouchEnd(originalEvent)
	{
		clearTimeout(this.tapTimeoutId);

		this.stopPreventScrolling();
		this.stopPreventContextMenu();
		this.stopHandleTouchEvents();

		if (this.isDragging())
		{
			const touch = originalEvent.touches[0] || originalEvent.changedTouches[0];
			const {clientX, clientY} = touch;
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
		}

		this.originalDragStartEvent = null;
		this.dragStartEvent = null;
		this.touchMoved = false;
	}
}