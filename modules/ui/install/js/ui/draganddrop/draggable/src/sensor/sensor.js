import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { DragStartSensorEvent } from './events/drag.start.sensor.event';

export default class Sensor extends EventEmitter
{
	containers: Array<HTMLElement>;
	options: Object;
	originalDragStartEvent: null | MouseEvent | TouchEvent = null;
	dragStartEvent: ?DragStartSensorEvent = null;

	constructor(container: HTMLElement | Array<HTMLElement> = [], options: Object = {})
	{
		super();
		this.setEventNamespace('BX.UI.DragAndDrop.Draggable.Sensor');

		const { dropzone } = options;
		this.containers = Type.isArray(container) ? [...container] : [container];
		this.dropzones = Type.isArrayLike(dropzone) ? [...dropzone] : [dropzone];
		this.options = { delay: 0, ...options };
	}

	getDocument(): HTMLDocument
	{
		return this.options.context.document;
	}

	addContainer(...containers: Array<HTMLElement>)
	{
		this.containers = [...this.containers, ...containers];
	}

	removeContainer(...containers: Array<HTMLElement>)
	{
		this.containers = this.containers.filter((container) => {
			return !containers.includes(container);
		});
	}

	getContainerByChild(childElement: HTMLElement): ?HTMLElement
	{
		return this.containers.find((container) => {
			return container.contains(childElement);
		});
	}

	addDropzone(...dropzones: Array<HTMLElement>)
	{
		this.dropzones = [...this.dropzones, ...dropzones];
	}

	removeDropzone(...dropzones: Array<HTMLElement>)
	{
		this.dropzones = this.dropzones.filter((dropzone) => {
			return !dropzones.includes(dropzone);
		});
	}

	getDropzoneByChild(childElement: HTMLElement): ?HTMLElement
	{
		return this.dropzones.find((dropzone) => {
			return dropzone.contains(childElement);
		});
	}

	// eslint-disable-next-line class-methods-use-this
	getElementFromPoint(x: number, y: number): HTMLElement
	{
		return this.getDocument().elementFromPoint(x, y);
	}

	// eslint-disable-next-line class-methods-use-this
	preventDefaultEventAction(event)
	{
		if (event.cancelable)
		{
			event.preventDefault();
		}
	}

	isDragging(): boolean
	{
		return this.dragStartEvent && !this.dragStartEvent.isDefaultPrevented();
	}

	enable()
	{
		return this;
	}

	disable()
	{
		return this;
	}

	getDragElementByChild(child: HTMLElement): ?HTMLElement
	{
		if (child)
		{
			const { dragElement } = this.options;

			return child.closest(dragElement) || null;
		}

		return null;
	}
}
