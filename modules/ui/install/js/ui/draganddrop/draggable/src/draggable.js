import { Type, Dom, Cache, Runtime, Tag, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';

import typeof Sensor from './sensor/sensor';
import MouseSensor from './sensor/mousesensor/mousesensor';
import TouchSensor from './sensor/touchsensor/touchsensor';

import { DragBeforeStartEvent } from './events/drag.before.start.event';
import { DragStartEvent } from './events/drag.start.event';
import { DragMoveEvent } from './events/drag.move.event';
import { DragOverEvent } from './events/drag.over.event';
import { DragOverContainerEvent } from './events/drag.over.container.event';
import { DragEnterEvent } from './events/drag.enter.event';
import { DragEnterContainerEvent } from './events/drag.enter.container.event';
import { DragOutEvent } from './events/drag.out.event';
import { DragOutContainerEvent } from './events/drag.out.container.event';
import { DragEndEvent } from './events/drag.end.event';
import { DragOverDropzoneEvent } from './events/drag.over.dropzone.event';
import { DragEnterDropzoneEvent } from './events/drag.enter.dropzone.event';
import { DragOutDropzoneEvent } from './events/drag.out.dropzone.event';
import { DragDropEvent } from './events/drag.drop.event';

import typeof { DragStartSensorEvent } from './sensor/events/drag.start.sensor.event';
import typeof { DragMoveSensorEvent } from './sensor/events/drag.move.sensor.event';
import typeof { DragEndSensorEvent } from './sensor/events/drag.end.sensor.event';
import typeof { DragDropSensorEvent } from './sensor/events/drag.drop.sensor.event';

import './css/style.css';

type DraggableOptions = {
	container: HTMLElement | Array<HTMLElement> | NodeList,
	dropzone: HTMLElement | Array<HTMLElement> | NodeList,
	draggable?: string,
	dragElement?: string,
	elementsPreventingDrag?: string[],
	delay?: number,
	sensors?: Array<Sensor>,
	transitionDuration: number,
	context?: Window,
	offset?: {
		x?: number,
		y?: number,
	},
	type?: string,
};

const defaultSensors = [
	MouseSensor,
	TouchSensor,
];

const optionsKey = Symbol('options');
const sensorsKey = Symbol('sensors');
const containersKey = Symbol('containers');
const dropzonesKey = Symbol('dropzones');

/**
 * @namespace BX.UI.DragAndDrop
 */
export class Draggable extends EventEmitter
{
	static MOVE = 'move';
	static CLONE = 'clone';
	static DROP_PREVIEW = 'drop-preview';
	static HEADLESS = 'headless';

	[optionsKey]: DraggableOptions = {
		delay: 0,
		sensors: [],
		draggable: '.ui-draggable--item',
		type: 'move',
		transitionDuration: 150,
		dropzone: [],
		context: window,
		offset: {
			x: 0,
			y: 0,
		},
	};

	[containersKey] = [];
	[dropzonesKey] = [];
	[sensorsKey] = [];

	dragStartEvent: ?DragStartEvent = null;

	constructor(options: DraggableOptions = {})
	{
		super(options);
		this.setEventNamespace('BX.UI.DragAndDrop.Draggable');

		this.cache = new Cache.MemoryCache();
		this.onDragStart = this.onDragStart.bind(this);
		this.onDragMove = this.onDragMove.bind(this);
		this.onDragEnd = this.onDragEnd.bind(this);
		this.onDragDrop = this.onDragDrop.bind(this);

		if (
			Type.isArray(options.container)
			|| Type.isDomNode(options.container)
			|| options.container instanceof NodeList
		)
		{
			if (options.container instanceof NodeList)
			{
				this.addContainer(...options.container);
			}
			else
			{
				this.addContainer(...[options.container].flat());
			}
		}
		else
		{
			throw new Error('Option container not a HTMLElement, Array of HTMLElement or NodeList');
		}

		if (
			!Type.isNil(options.dropzone)
			&& (
				Type.isArray(options.dropzone)
				|| Type.isDomNode(options.dropzone)
				|| options.dropzone instanceof NodeList
			)
		)
		{
			if (options.dropzone instanceof NodeList)
			{
				this.addDropzone(...options.dropzone);
			}
			else
			{
				this.addDropzone(...[options.dropzone].flat());
			}
		}

		this.setOptions({
			...this.getOptions(),
			...options,
		});

		const { sensors } = this.getOptions();
		this.addSensor(
			...defaultSensors,
			...sensors,
		);
	}

	getDocument(): HTMLDocument
	{
		return this.getOptions().context.document;
	}

	getOptions(): DraggableOptions
	{
		return this[optionsKey];
	}

	setOptions(options: {[key: string]: any})
	{
		this[optionsKey] = { ...options };

		if (!Type.isString(this[optionsKey].dragElement))
		{
			this[optionsKey].dragElement = this[optionsKey].draggable;
		}

		if (!Type.isPlainObject(this[optionsKey].offset))
		{
			this[optionsKey].offset = {
				x: 0,
				y: 0,
			};
		}

		if (!Type.isNumber(this[optionsKey].offset.x))
		{
			this[optionsKey].offset.x = 0;
		}

		if (!Type.isNumber(this[optionsKey].offset.y))
		{
			this[optionsKey].offset.y = 0;
		}

		this.invalidateCache();
	}

	isDragging(): boolean
	{
		return this.dragStartEvent && !this.dragStartEvent.isDefaultPrevented();
	}

	getSensors(): Array<Sensor>
	{
		return this[sensorsKey];
	}

	addSensor(...sensors: Array<Sensor>)
	{
		const initializedSensors = sensors.map((CurrentSensor) => {
			const instance = new CurrentSensor(
				this.getContainers(),
				this.getOptions(),
			);

			instance.subscribe('drag:start', this.onDragStart);
			instance.subscribe('drag:move', this.onDragMove);
			instance.subscribe('drag:end', this.onDragEnd);
			instance.subscribe('drag:drop', this.onDragDrop);

			instance.enable();

			return instance;
		});

		this[sensorsKey] = [
			...this.getSensors(),
			...initializedSensors,
		];
	}

	destroy(): void
	{
		this.removeSensor(...this.getSensors());
	}

	removeSensor(...sensors: Array<Sensor>)
	{
		const removedSensors = this.getSensors().filter((sensor) => {
			return sensors.includes(sensor);
		});

		removedSensors.forEach((sensor: Sensor) => {
			sensor.unsubscribe('drag:start', this.onDragStart);
			sensor.unsubscribe('drag:move', this.onDragMove);
			sensor.unsubscribe('drag:end', this.onDragEnd);
			sensor.unsubscribe('drag:drop', this.onDragDrop);
			sensor.disable();
		});

		this[sensorsKey] = this.getSensors().filter((sensor) => {
			return !removedSensors.includes(sensor);
		});
	}

	getContainers(): Array<HTMLElement>
	{
		return this[containersKey];
	}

	getContainerByChild(childElement: HTMLElement): ?HTMLElement
	{
		return this.getContainers().find((container) => {
			return container.contains(childElement);
		});
	}

	addContainer(...containers: Array<HTMLElement>)
	{
		this[containersKey] = [
			...this.getContainers(),
			...containers,
		];

		this[containersKey].forEach((container) => {
			Dom.addClass(container, 'ui-draggable--container');
		});

		this.getSensors().forEach((sensor) => {
			sensor.addContainer(...containers);
		});

		this.invalidateContainersCache();
	}

	removeContainer(...containers: Array<HTMLElement>)
	{
		this[containersKey] = this.getContainers().filter((container) => {
			return !containers.includes(container);
		});

		this.getSensors().forEach((sensor) => {
			sensor.removeContainer(...containers);
		});

		this.invalidateContainersCache();
	}

	getDropzones(): Array<HTMLElement>
	{
		return this[dropzonesKey];
	}

	getDropzoneByChild(childElement: HTMLElement): ?HTMLElement
	{
		return this.getDropzones().find((dropzone) => {
			return dropzone.contains(childElement);
		});
	}

	addDropzone(...dropzones: Array<HTMLElement>)
	{
		this[dropzonesKey] = [
			...this.getDropzones(),
			...dropzones,
		];

		this[dropzonesKey].forEach((dropzone) => {
			Dom.addClass(dropzone, 'ui-draggable--dropzone');
		});

		this.getSensors().forEach((sensor) => {
			sensor.addDropzone(...dropzones);
		});
	}

	removeDropzone(...dropzones: Array<HTMLElement>)
	{
		this[dropzonesKey] = this.getContainers().filter((dropzone) => {
			return !dropzones.includes(dropzone);
		});

		this.getSensors().forEach((sensor) => {
			sensor.removeDropzone(...dropzones);
		});
	}

	getDraggableElements(): Array<HTMLElement>
	{
		return this.cache.remember('draggableElements', () => {
			return this.getContainers().reduce((acc, container) => {
				return [...acc, ...this.getDraggableElementsOfContainer(container)];
			}, []);
		});
	}

	getDraggableElementsOfContainer(container: HTMLElement): Array<HTMLElement>
	{
		return this.cache.remember(container, () => {
			const draggableSelector = this.getOptions().draggable;
			const notDraggable = ':not(.ui-draggable--draggable)';
			const notDropPreview = ':not(.ui-draggable--drop-preview)';

			const filter = `${notDraggable}${notDropPreview}`;
			const selector = `${draggableSelector}${filter}`;

			const elements = [...container.querySelectorAll(selector)];

			return elements.filter((element) => element.parentElement === container);
		});
	}

	getLastDraggableElementOfContainer(container): ?HTMLElement
	{
		const draggableElements = this.getDraggableElementsOfContainer(container);

		return draggableElements[draggableElements.length - 1] || null;
	}

	getElementIndex(element: HTMLElement): number
	{
		return this.getDraggableElements().indexOf(element);
	}

	getDropPreview()
	{
		return this.cache.remember('dropPreview', () => {
			const { type } = this.getOptions();
			const source = this.getSource();
			if (source === null)
			{
				return Tag.render`<div></div>`;
			}
			const sourceRect = this.getSourceClientRect();
			let dropPreview = null;

			if (type === Draggable.CLONE)
			{
				dropPreview = Runtime.clone(source);
				Dom.addClass(dropPreview, 'ui-draggable--drop-preview-clone');
			}
			else
			{
				dropPreview = Tag.render`<div></div>`;
			}

			Dom.addClass(dropPreview, 'ui-draggable--drop-preview');
			Dom.style(dropPreview, {
				width: `${sourceRect.width}px`,
				height: `${sourceRect.height}px`,
			});

			return dropPreview;
		});
	}

	move(element, { x = 0, y = 0 })
	{
		const { transitionDuration } = this.getOptions();

		requestAnimationFrame(() => {
			Dom.style(element, {
				transform: `translate3d(${x}px, ${y}px, 0px)`,
				transition: `all ${transitionDuration}ms ease 0s`,
			});
		});
	}

	/**
	 * @private
	 */
	setSource(element: ?HTMLElement)
	{
		this.cache.set('source', element || null);
	}

	/**
	 * @private
	 */
	getSource(): ?HTMLElement
	{
		return this.cache.get('source') || null;
	}

	/**
	 * @private
	 */
	getSourceClientRect(): DOMRect
	{
		return this.cache.remember('sourceClientRect', () => {
			return this.cache.get('source').getBoundingClientRect();
		});
	}

	/**
	 * @private
	 */
	adjustDropPreview(target: HTMLElement, options = {})
	{
		const { x = false, y = false, force = true, skipOffset = false, transition = true } = options;
		const dropPreview = this.getDropPreview();
		const targetRect = Dom.getRelativePosition(target, target.parentElement);
		const dropPreviewRect = Dom.getRelativePosition(dropPreview, dropPreview.parentElement);

		let offset = 0;

		if (dropPreviewRect.height !== 0 && !skipOffset)
		{
			if (targetRect.height > dropPreviewRect.height)
			{
				if (targetRect.top > dropPreviewRect.top)
				{
					offset = targetRect.height - dropPreviewRect.height;
				}
			}
			else if (targetRect.top > dropPreviewRect.top)
			{
				offset = -Math.abs(targetRect.height - dropPreviewRect.height);
			}
		}

		const { transitionDuration } = this.getOptions();
		const adjustPosition = () => {
			const style = {
				transition: transition ? `all ${transitionDuration}ms ease 0ms` : 'null',
			};

			if (y)
			{
				style.top = `${targetRect.top + offset}px`;
			}

			if (x)
			{
				style.left = `${targetRect.left}px`;
			}

			Dom.style(dropPreview, style);
		};

		if (force)
		{
			adjustPosition();
		}
		else
		{
			requestAnimationFrame(adjustPosition);
		}
	}

	showDropPreviewAfter(element: HTMLElement)
	{
		const elementRect = Dom.getRelativePosition(element, element.parentElement);
		const marginBottom = Text.toNumber(Dom.style(element, 'margin-bottom'));
		const marginTop = Text.toNumber(Dom.style(element, 'margin-top'));
		const bottom = elementRect.bottom + marginBottom + marginTop;

		const { transitionDuration } = this.getOptions();

		requestAnimationFrame(() => {
			Dom.style(this.getDropPreview(), {
				top: `${bottom}px`,
				transition: `all ${transitionDuration}ms ease 0s`,
			});
		});
	}

	pushDraggableElementToContainer(element: HTMLElement, container: HTMLElement)
	{
		const lastDraggableElement = this.getLastDraggableElementOfContainer(container);

		if (lastDraggableElement)
		{
			Dom.insertAfter(element, lastDraggableElement);
		}
		else
		{
			Dom.append(element, container);
		}

		this.invalidateContainersCache();
	}

	resetDraggableElementsPosition(container: ?HTMLElement, { transition = true } = {})
	{
		const draggableElements = (() => {
			if (container)
			{
				return this.getDraggableElementsOfContainer(container);
			}

			return this.getDraggableElements();
		})();

		draggableElements.forEach((element) => {
			Dom.style(element, {
				transform: null,
				transition: transition ? undefined : 'none',
			});
		});
	}

	resetDraggableElementsTransition(container: ?HTMLElement)
	{
		const draggableElements = (() => {
			if (container)
			{
				return this.getDraggableElementsOfContainer(container);
			}

			return this.getDraggableElements();
		})();

		draggableElements.forEach((element) => {
			Dom.style(element, {
				transition: null,
			});
		});
	}

	getSortOffsetY(): number
	{
		return this.cache.remember('sortOffsetY', () => {
			const source = this.getSource();
			const sourceRect = this.getSourceClientRect();
			const marginTop = Text.toNumber(Dom.style(source, 'margin-top'));
			const marginBottom = Text.toNumber(Dom.style(source, 'margin-bottom'));

			return Math.round(sourceRect.height + (marginTop + marginBottom));
		});
	}

	getSortOffsetX(): number
	{
		return this.cache.remember('sortOffsetX', () => {
			const source = this.getSource();
			const sourceRect = this.getSourceClientRect();
			const marginLeft = Text.toNumber(Dom.style(source, 'margin-left'));
			const marginRight = Text.toNumber(Dom.style(source, 'margin-right'));

			return sourceRect.width + (marginLeft + marginRight);
		});
	}

	// eslint-disable-next-line class-methods-use-this
	getElementMiddlePoint(element: HTMLElement): {x: number, y: number}
	{
		const elementRect = element.getBoundingClientRect();

		return {
			x: elementRect.left + (elementRect.width / 2),
			y: elementRect.top + (elementRect.height / 2),
		};
	}

	getDraggableElementByChild(child: HTMLElement): ?HTMLElement
	{
		return child.closest(this.getOptions().draggable);
	}

	splitDraggableElementsListByPoint(
		container: HTMLElement,
		point: {x: number, y: number},
	): [Array<HTMLElement>, Array<HTMLElement>]
	{
		let useRect = true;

		return this.getDraggableElementsOfContainer(container)
			.reduce((acc, element) => {
				if (useRect)
				{
					const elementMiddlePoint = this.getElementMiddlePoint(element);

					if (elementMiddlePoint.y < point.y)
					{
						acc[0].push(element);
					}
					else
					{
						acc[1].push(element);
						useRect = false;
					}
				}
				else
				{
					acc[1].push(element);
				}

				return acc;
			}, [[], []]);
	}

	invalidateContainersCache()
	{
		this.cache.delete('draggableElements');
		this.getContainers().forEach((container) => this.cache.delete(container));
	}

	invalidateCache()
	{
		this.cache.delete('source');
		this.cache.delete('sourceClientRect');
		this.cache.delete('dropPreview');
		this.cache.delete('sortOffsetY');
		this.cache.delete('sortOffsetX');
		this.cache.delete('sourceLeftOffset');
		this.cache.delete('sourceLeftMargin');
		this.invalidateContainersCache();
	}

	isDepthEditorEnabled(): boolean
	{
		const { depth, type } = this.getOptions();

		return (
			Type.isPlainObject(depth)
			&& (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE)
		);
	}

	getDepthProperty(): string
	{
		const { depth } = this.getOptions();

		return depth.property || 'margin-left';
	}

	getDepthMargin(): number
	{
		const { depth } = this.getOptions();

		return Text.toNumber(depth.margin) || 20;
	}

	getElementDepth(element: HTMLElement): number
	{
		return Text.toNumber(Dom.attr(element, 'data-depth'));
	}

	setElementDepth(element: HTMLElement, depth: number)
	{
		Dom.attr(element, 'data-depth', depth);
		const depthMargin = this.getDepthMargin();
		const sourceMargin = this.getSourceLeftMargin();
		const margin = (depthMargin * depth) + sourceMargin;
		Dom.style(element, this.getDepthProperty(), `${margin}px`);
	}

	getStartSourceDepth(): number
	{
		return this.dragStartEvent.data.sourceDepth;
	}

	getSourceWidth(): number
	{
		return this.getSourceClientRect().width;
	}

	getSourceLeftOffset(): number
	{
		return this.cache.remember('sourceLeftOffset', () => {
			const source = this.getSource();
			const sourceRect = Dom.getRelativePosition(source, source.parentElement);
			const sourceMargin = this.getStartSourceDepth() * this.getDepthMargin();

			return sourceRect.left - sourceMargin;
		});
	}

	getSourceLeftMargin(): number
	{
		return this.cache.remember('sourceLeftMargin', () => {
			const source = this.getSource();
			const sourceDepth = this.getStartSourceDepth();
			const depthMargin = this.getDepthMargin();
			const sourceDepthMargin = sourceDepth * depthMargin;
			const sourceMargin = Text.toNumber(Dom.style(source, this.getDepthProperty()));

			return sourceMargin - sourceDepthMargin;
		});
	}

	setDropPreviewDepth(depth: number)
	{
		const sourceDepth = this.getStartSourceDepth();
		const sourceWidth = this.getSourceWidth();
		const depthMargin = this.getDepthMargin();
		const sourceLeftOffset = this.getSourceLeftOffset();

		const dropPreviewWidth = (() => {
			const depthDiff = Math.abs(sourceDepth - depth);
			if (depth > sourceDepth)
			{
				return sourceWidth - (depthDiff * depthMargin);
			}

			if (depth < sourceDepth)
			{
				return sourceWidth + (depthDiff * depthMargin);
			}

			return sourceWidth;
		})();

		Dom.style(this.getDropPreview(), {
			left: `${(depth * depthMargin) + sourceLeftOffset}px`,
			width: `${dropPreviewWidth}px`,
		});
	}

	calcDepthByOffset(offsetX): number
	{
		const startSourceDepth = this.getStartSourceDepth();
		const depthMargin = this.getDepthMargin();
		const sourceDepthMargin = startSourceDepth * depthMargin;

		return Math.max(0, Math.floor((offsetX + sourceDepthMargin) / depthMargin));
	}

	getChildren(parent: HTMLElement): Array<HTMLElement>
	{
		const parentDepth = this.getElementDepth(parent);
		const parentRect = parent.getBoundingClientRect();
		const container = this.getContainerByChild(parent);
		const [, nextElements] = this.splitDraggableElementsListByPoint(
			container,
			{ x: parentRect.left, y: parentRect.bottom },
		);

		let stop = false;

		return nextElements.reduce((acc, element) => {
			if (!stop)
			{
				const currentDepth = this.getElementDepth(element);
				if (currentDepth > parentDepth)
				{
					return [...acc, element];
				}

				stop = true;
			}

			return acc;
		}, []);
	}

	getPreviousElement(element: HTMLElement): ?HTMLElement
	{
		const elementRect = element.getBoundingClientRect();
		const container = this.getContainerByChild(element);
		const [prevElements] = this.splitDraggableElementsListByPoint(
			container,
			{ x: elementRect.left, y: elementRect.top },
		);

		if (Type.isArrayFilled(prevElements))
		{
			return prevElements.pop();
		}

		return null;
	}

	onDragStart(event: DragStartSensorEvent)
	{
		const { originalSource, sourceContainer, clientX, clientY } = event.data;

		const source = this.getDraggableElementByChild(originalSource);

		const dragBeforeStartEvent = new DragBeforeStartEvent({
			clientX,
			clientY,
			source,
			sourceContainer,
			originalSource,
		});

		this.emit('beforeStart', dragBeforeStartEvent);

		if (dragBeforeStartEvent.isDefaultPrevented())
		{
			event.preventDefault();

			return;
		}

		this.setSource(source);

		const sourceDepth = this.getElementDepth(source);
		const sourceRect = this.getSourceClientRect();
		const pointerOffsetX = clientX - sourceRect.left;
		const pointerOffsetY = clientY - sourceRect.top;
		const { type } = this.getOptions();

		let draggable = source;
		if (type !== Draggable.HEADLESS)
		{
			const clone = Runtime.clone(source);

			Dom.style(clone, 'margin', 0);

			draggable = Tag.render`<div>${clone}</div>`;

			Dom.style(draggable, {
				width: `${sourceRect.width}px`,
				height: `${sourceRect.height}px`,
				top: `${(clientY - pointerOffsetY) + this.getOptions().offset.y}px`,
				left: `${(clientX - pointerOffsetX) + this.getOptions().offset.x}px`,
			});

			Dom.addClass(draggable, 'ui-draggable--draggable');
			this.pushDraggableElementToContainer(draggable, sourceContainer);

			if (this.isDepthEditorEnabled())
			{
				const children = this.getChildren(source);

				this.childrenElements = children;

				if (children.length > 0)
				{
					Dom.append(Runtime.clone(clone), draggable);

					children.forEach((element) => {
						Dom.style(element, 'display', 'none');
					});
				}
			}
		}

		const dropPreview = this.getDropPreview();

		if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE)
		{
			this.pushDraggableElementToContainer(dropPreview, sourceContainer);
			this.adjustDropPreview(source, { force: true, x: true, y: true, transition: false });
		}

		Dom.addClass(source, 'ui-draggable--source');
		Dom.addClass(this.getDocument().body, 'ui-draggable--disable-user-select');
		Dom.addClass(this.getDocument().body, `ui-draggable--type-${this.getOptions().type}`);

		const sourceIndex = this.getElementIndex(source);
		this.dragStartEvent = new DragStartEvent({
			clientX,
			clientY,
			pointerOffsetX,
			pointerOffsetY,
			draggable,
			dropPreview,
			source,
			sourceIndex,
			sourceContainer,
			sourceDepth,
			originalSource,
		});

		this.emit('start', this.dragStartEvent);

		if (this.dragStartEvent.isDefaultPrevented())
		{
			event.preventDefault();
		}
	}

	// eslint-disable-next-line max-lines-per-function
	onDragMove(event: DragMoveSensorEvent): void
	{
		if (!this.isDragging())
		{
			return;
		}

		const { clientX, clientY, sourceContainer, originalSource } = event.data;
		const {
			clientX: startClientX,
			clientY: startClientY,
			pointerOffsetX,
			pointerOffsetY,
			source,
			sourceIndex,
			draggable,
			dropPreview,
		} = this.dragStartEvent.data;
		const offsetX = clientX - startClientX;
		const offsetY = clientY - startClientY;

		const dragMoveEvent = new DragMoveEvent({
			clientX,
			clientY,
			offsetX,
			offsetY,
			pointerOffsetX,
			pointerOffsetY,
			draggable,
			dropPreview,
			source,
			sourceIndex,
			sourceContainer,
			originalSource,
		});

		this.emit('move', dragMoveEvent);

		if (dragMoveEvent.isDefaultPrevented())
		{
			event.preventDefault();
		}

		if (!Type.isDomNode(event.data.over))
		{
			return;
		}

		const originalOver = event.data.over;
		const over = this.getDraggableElementByChild(originalOver);
		const overContainer = this.getContainerByChild(originalOver);

		const { type } = this.getOptions();
		if (type !== Draggable.HEADLESS)
		{
			Dom.style(draggable, {
				top: `${(clientY - pointerOffsetY) + this.getOptions().offset.y}px`,
				left: `${(clientX - pointerOffsetX) + this.getOptions().offset.x}px`,
			});

			if (overContainer && overContainer.contains(source) && !this.stopMove)
			{
				const sortOffsetY = this.getSortOffsetY();
				const draggableElements = this.getDraggableElementsOfContainer(overContainer);
				const localSourceIndex = draggableElements.indexOf(source);

				draggableElements.forEach((element, index) => {
					if (element !== source)
					{
						// eslint-disable-next-line @bitrix24/bitrix24-rules/no-style
						const currentTransform = element.style.transform;
						const elementMiddlePoint = this.getElementMiddlePoint(element);

						if (elementMiddlePoint.y === 0)
						{
							return;
						}

						if (
							index > localSourceIndex
							&& clientY > elementMiddlePoint.y
							&& currentTransform !== `translate3d(0px, ${-sortOffsetY}px, 0px)`
						)
						{
							this.adjustDropPreview(element, { y: true });
							this.move(element, { y: -sortOffsetY });
							this.insertType = 'after';
							this.insertElement = element;
						}

						if (
							index < localSourceIndex
							&& clientY < elementMiddlePoint.y
							&& currentTransform !== `translate3d(0px, ${sortOffsetY}px, 0px)`
						)
						{
							this.adjustDropPreview(element, { y: true });
							this.move(element, { y: sortOffsetY });
							this.insertType = 'before';
							this.insertElement = element;
						}

						if (
							((index < localSourceIndex && clientY > elementMiddlePoint.y)
							|| (index > localSourceIndex && clientY < elementMiddlePoint.y))
							&& currentTransform !== 'translate3d(0px, 0px, 0px)'
							&& currentTransform !== ''
						)
						{
							this.adjustDropPreview(element, { y: true });
							this.move(element, { y: 0 });

							this.insertElement = element;

							if (index < localSourceIndex && clientY > elementMiddlePoint.y)
							{
								this.insertType = 'after';
							}

							if (index > localSourceIndex && clientY < elementMiddlePoint.y)
							{
								this.insertType = 'before';
							}
						}
					}
				});
			}
		}

		if (this.isDepthEditorEnabled())
		{
			let currentDepth = this.calcDepthByOffset(offsetX);
			const parentElement = this.getPreviousElement(dropPreview);

			if (parentElement)
			{
				const prevDepth = this.getElementDepth(parentElement);
				const minDepth = 0;
				const maxDepth = Math.max(minDepth, prevDepth + 1);
				currentDepth = Math.max(minDepth, Math.min(currentDepth, maxDepth));
			}
			else
			{
				currentDepth = 0;
			}

			this.setDropPreviewDepth(currentDepth);
			this.currentDepth = currentDepth;
		}

		if (Type.isDomNode(over) && source !== over)
		{
			const dragOverEvent = new DragOverEvent({
				...dragMoveEvent.data,
				over,
				originalOver,
				overContainer,
			});

			this.emit('over', dragOverEvent);

			if (!dragOverEvent.isDefaultPrevented())
			{
				Dom.addClass(over, 'ui-draggable--over');
			}

			if (over !== this.lastOver)
			{
				const dragEnterEvent = new DragEnterEvent({
					...dragMoveEvent.data,
					enter: over,
					enterContainer: overContainer,
				});

				this.emit('enter', dragEnterEvent);
			}
		}

		this.lastOver = this.lastOver || over;

		if (!over || over !== this.lastOver)
		{
			if (this.lastOver)
			{
				const outContainer = this.getContainerByChild(this.lastOver);
				const dragOutEvent = new DragOutEvent({
					...dragMoveEvent,
					out: this.lastOver,
					outContainer,
				});

				this.emit('out', dragOutEvent);

				Dom.removeClass(this.lastOver, 'ui-draggable--over');
			}

			this.lastOver = over;
		}

		const sourceOver = this.getDocument().elementFromPoint(clientX, clientY);
		const dropzoneOver = this.getDropzoneByChild(sourceOver);

		if (dropzoneOver)
		{
			const dragOverDropzoneEvent = new DragOverDropzoneEvent({
				...dragMoveEvent.data,
				dropzone: dropzoneOver,
			});

			this.emit('dropzone:over', dragOverDropzoneEvent);

			if (dropzoneOver !== this.lastOverDropzone)
			{
				const dragEnterDropzoneEvent = new DragEnterDropzoneEvent({
					...dragMoveEvent.data,
					dropzone: dropzoneOver,
				});

				this.emit('dropzone:enter', dragEnterDropzoneEvent);
			}
		}

		this.lastOverDropzone = this.lastOverDropzone || dropzoneOver;

		if (dropzoneOver !== this.lastOverDropzone)
		{
			const dragOutDropzoneEvent = new DragOutDropzoneEvent({
				...dragMoveEvent.data,
				dropzone: this.lastOverDropzone,
			});

			this.emit('dropzone:out', dragOutDropzoneEvent);

			this.lastOverDropzone = dropzoneOver;
		}

		if (overContainer)
		{
			const dragOverContainerEvent = new DragOverContainerEvent({
				...dragMoveEvent.data,
				over: overContainer,
			});

			this.emit('container:over', dragOverContainerEvent);

			if (overContainer !== this.lastOverContainer)
			{
				const dragEnterContainerEvent = new DragEnterContainerEvent({
					...dragMoveEvent.data,
					enter: overContainer,
				});

				this.emit('container:enter', dragEnterContainerEvent);

				if (!overContainer.contains(source))
				{
					const lastContainer = this.getContainerByChild(source);
					const [beforeElements, afterElements] = this.splitDraggableElementsListByPoint(
						overContainer,
						{ x: clientX, y: clientY },
					);

					if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE)
					{
						this.stopMove = true;
						setTimeout(() => {
							this.stopMove = false;
						}, 300);

						this.pushDraggableElementToContainer(this.getDropPreview(), overContainer);
					}

					if (type !== Draggable.HEADLESS)
					{
						this.pushDraggableElementToContainer(source, overContainer);
					}

					if (Type.isArrayFilled(beforeElements))
					{
						const lastElement = beforeElements[beforeElements.length - 1];

						if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE)
						{
							this.showDropPreviewAfter(lastElement);
						}

						this.insertType = 'after';
						this.insertElement = lastElement;
					}
					else if (Type.isArrayFilled(afterElements))
					{
						const [firstElement] = afterElements;

						if (type === Draggable.DROP_PREVIEW || type === Draggable.CLONE)
						{
							this.adjustDropPreview(afterElements);
						}

						this.insertType = 'before';
						this.insertElement = firstElement;
					}

					this.resetDraggableElementsTransition(lastContainer);
					this.resetDraggableElementsPosition(lastContainer);

					if (type !== Draggable.HEADLESS && Type.isArrayFilled(afterElements))
					{
						const sortOffsetY = this.getSortOffsetY();
						afterElements.forEach((element) => {
							this.move(element, { y: sortOffsetY });
						});
					}
				}
			}
		}

		this.lastOverContainer = this.lastOverContainer || overContainer;

		if (overContainer !== this.lastOverContainer)
		{
			const dragOutContainerEvent = new DragOutContainerEvent({
				...dragMoveEvent.data,
				out: this.lastOverContainer,
			});

			this.emit('container:out', dragOutContainerEvent);

			this.lastOverContainer = overContainer;
		}
	}

	onDragEnd(event: DragEndSensorEvent)
	{
		const dragEndEvent = new DragEndEvent({
			...this.dragStartEvent.data,
			clientX: event.data.clientX,
			clientY: event.data.clientY,
			end: this.lastOver,
			endContainer: this.lastOverContainer,
		});

		const { source, draggable } = this.dragStartEvent.data;

		if (this.getOptions().type !== Draggable.HEADLESS)
		{
			Dom.remove(draggable);
		}

		Dom.removeClass(source, 'ui-draggable--source');

		this.getDraggableElements().forEach((element) => {
			Dom.removeClass(element, 'ui-draggable--draggable');
			Dom.removeClass(element, 'ui-draggable--over');
		});

		Dom.remove(this.getDropPreview());

		this.resetDraggableElementsPosition();
		this.resetDraggableElementsTransition();

		if (this.getOptions().type !== Draggable.HEADLESS && Type.isString(this.insertType))
		{
			if (this.insertType === 'after')
			{
				Dom.insertAfter(source, this.insertElement);
			}
			else
			{
				Dom.insertBefore(source, this.insertElement);
			}
		}

		if (this.isDepthEditorEnabled())
		{
			const startSourceDepth = this.getStartSourceDepth();
			const depthDiff = (() => {
				if (Type.isNumber(this.currentDepth))
				{
					return this.currentDepth - startSourceDepth;
				}

				return 0;
			})();

			let lastElement = source;
			this.childrenElements.forEach((element) => {
				const currentDepth = this.getElementDepth(element);
				this.setElementDepth(element, currentDepth + depthDiff);
				Dom.insertAfter(element, lastElement);
				Dom.style(element, 'display', null);
				lastElement = element;
			});

			if (Type.isNumber(this.currentDepth))
			{
				this.setElementDepth(source, this.currentDepth);
			}
		}

		this.lastOver = null;
		this.lastOverContainer = null;
		this.insertType = null;
		this.lastOverDropzone = null;
		this.childrenElements = [];
		this.currentDepth = null;
		this.invalidateCache();
		Dom.removeClass(this.getDocument().body, 'ui-draggable--disable-user-select');
		Dom.removeClass(this.getDocument().body, `ui-draggable--type-${this.getOptions().type}`);

		this.emit('end', dragEndEvent); // todo test in default
	}

	onDragDrop(event: DragDropSensorEvent)
	{
		const dragDropEvent = new DragDropEvent({
			...this.dragStartEvent.data,
			clientX: event.data.clientX,
			clientY: event.data.clientY,
			dropzone: event.data.dropzone,
		});

		this.emit('drop', dragDropEvent);
	}
}

export {
	DragStartEvent,
	DragMoveEvent,
	DragOutEvent,
	DragOutContainerEvent,
	DragEndEvent,
	DragOverEvent,
	DragOverContainerEvent,
	DragEnterEvent,
	DragEnterContainerEvent,
};
