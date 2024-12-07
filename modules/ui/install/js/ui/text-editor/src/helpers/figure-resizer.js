import { Type, Tag, Dom, Event } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { type TextEditor } from '../text-editor';

function clamp(value: number, min: number, max: number): number
{
	return Math.min(Math.max(value, min), max);
}

const Direction = {
	EAST: 1,
	SOUTH: 2,
	WEST: 4,
	NORTH: 8,
};

import './figure-resizer.css';

export default class FigureResizer extends EventEmitter
{
	#positioning = {
		currentHeight: 0,
		currentWidth: 0,
		direction: 0,
		isResizing: false,
		ratio: 0,
		startHeight: 0,
		startWidth: 0,
		startX: 0,
		startY: 0,
	};

	#freeTransform: boolean = false;

	#onPointerDownHandler: Function = null;
	#onPointerMoveHandler: Function = null;
	#onPointerUpHandler: Function = null;

	#container: HTMLElement = null;
	#target: HTMLElement = null;
	#editor: TextEditor = null;

	#maxWidth: 'none' | number = 'none';
	#maxHeight: 'none' | number = 'none';
	#minWidth: number = 16;
	#minHeight: number = 16;

	constructor({
		target,
		editor,
		originalWidth,
		originalHeight,
		minWidth,
		minHeight,
		maxWidth,
		maxHeight,
		events,
		freeTransform,
	})
	{
		super();
		this.setEventNamespace('BX.UI.TextEditor.FigureResizer');

		this.#target = target;
		this.#editor = editor;

		this.#minWidth = Math.min(
			Math.max(this.#minWidth, Type.isNumber(minWidth) ? minWidth : this.#minWidth),
			Type.isNumber(originalWidth) ? originalWidth : Infinity,
		);
		this.#minHeight = Math.min(
			Math.max(this.#minHeight, Type.isNumber(minHeight) ? minHeight : this.#minHeight),
			Type.isNumber(originalHeight) ? originalHeight : Infinity,
		);

		this.#maxWidth = Type.isNumber(maxWidth) ? maxWidth : 'none';
		this.#maxHeight = Type.isNumber(maxHeight) ? maxHeight : 'none';
		this.#freeTransform = freeTransform === true;

		this.#onPointerDownHandler = this.#handlePointerDown.bind(this);
		this.#onPointerMoveHandler = this.#handlePointerMove.bind(this);
		this.#onPointerUpHandler = this.#handlePointerUp.bind(this);

		this.subscribeFromOptions(events);
	}

	show(): void
	{
		Dom.addClass(this.getContainer(), '--shown');
	}

	hide(): void
	{
		Dom.removeClass(this.getContainer(), '--shown');
	}

	getContainer(): HTMLElement
	{
		if (this.#container === null)
		{
			const freeTransform = Tag.render`
				<div
					class="ui-text-editor-figure-resizer-handle --north"
					data-direction="${Direction.NORTH}"
					onpointerdown="${this.#onPointerDownHandler}"
					></div>
				<div
					class="ui-text-editor-figure-resizer-handle --east"
					data-direction="${Direction.EAST}"
					onpointerdown="${this.#onPointerDownHandler}"
					></div>
				<div
					class="ui-text-editor-figure-resizer-handle --south"
					data-direction="${Direction.SOUTH}"
					onpointerdown="${this.#onPointerDownHandler}"
					></div>
				<div
					class="ui-text-editor-figure-resizer-handle --west"
					data-direction="${Direction.WEST}"
					onpointerdown="${this.#onPointerDownHandler}"
					></div>
			`;

			this.#container = Tag.render`
				<div class="ui-text-editor-figure-resizer">
					<div
						class="ui-text-editor-figure-resizer-handle --north-east"
						data-direction="${Direction.NORTH | Direction.EAST}" 
						onpointerdown="${this.#onPointerDownHandler}"
					></div>
					<div
						class="ui-text-editor-figure-resizer-handle --south-east"
						data-direction="${Direction.SOUTH | Direction.EAST}" 
						onpointerdown="${this.#onPointerDownHandler}"
						></div>
					<div
						class="ui-text-editor-figure-resizer-handle --south-west"
						data-direction="${Direction.SOUTH | Direction.WEST}" 
						onpointerdown="${this.#onPointerDownHandler}"
						></div>
					<div 
						class="ui-text-editor-figure-resizer-handle --north-west"
						data-direction="${Direction.NORTH | Direction.WEST}" 
						onpointerdown="${this.#onPointerDownHandler}"
						></div>
					${this.#freeTransform ? freeTransform : null}
				</div>
			`;
		}

		return this.#container;
	}

	getTarget(): HTMLElement
	{
		return this.#target;
	}

	setTarget(target: HTMLElement): void
	{
		this.#target = target;
	}

	getEditor(): TextEditor
	{
		return this.#editor;
	}

	isResizing(): boolean
	{
		return this.#positioning.isResizing;
	}

	#handlePointerDown(event: PointerEvent)
	{
		if (!this.getEditor().isEditable())
		{
			return;
		}

		event.preventDefault();

		const direction: number = Number(event.target.dataset.direction);

		const target = this.getTarget();
		const { width, height } = target.getBoundingClientRect();

		this.#positioning.startWidth = width;
		this.#positioning.startHeight = height;
		this.#positioning.ratio = width / height;
		this.#positioning.currentWidth = width;
		this.#positioning.currentHeight = height;
		this.#positioning.startX = event.clientX;
		this.#positioning.startY = event.clientY;
		this.#positioning.isResizing = true;
		this.#positioning.direction = direction;

		// setStartCursor(direction);
		this.emit('onResizeStart');

		Dom.addClass(this.getContainer(), '--resizing');
		Dom.style(target, {
			width: `${width}px`,
			height: `${height}px`,
		});

		Event.bind(document, 'pointermove', this.#onPointerMoveHandler);
		Event.bind(document, 'pointerup', this.#onPointerUpHandler);
	}

	#handlePointerMove(event: PointerEvent)
	{
		const target = this.getTarget();
		const isHorizontal = this.#positioning.direction & (Direction.EAST | Direction.WEST);
		const isVertical = this.#positioning.direction & (Direction.SOUTH | Direction.NORTH);

		if (this.#positioning.isResizing)
		{
			// Corner cursor
			if (isHorizontal && isVertical)
			{
				let diff = Math.floor(this.#positioning.startX - event.clientX);
				diff = this.#positioning.direction & Direction.EAST ? -diff : diff;

				const width = Math.round(clamp(
					this.#positioning.startWidth + diff,
					this.#minWidth,
					this.#getMaxContainerWidth(),
				));

				const height = Math.ceil(width / this.#positioning.ratio);

				Dom.style(target, {
					width: `${width}px`,
					height: `${height}px`,
				});

				this.emit('onResize', { width, height });

				this.#positioning.currentHeight = height;
				this.#positioning.currentWidth = width;
			}
			else if (isVertical)
			{
				let diff = Math.floor(this.#positioning.startY - event.clientY);
				diff = this.#positioning.direction & Direction.SOUTH ? -diff : diff;

				const height = Math.round(Math.max(
					this.#positioning.startHeight + diff,
					this.#minHeight,
					// this.#getMaxContainerHeight(),
				));

				Dom.style(target, 'height', `${height}px`);
				this.emit('onResize', { width: this.#positioning.currentWidth, height });

				this.#positioning.currentHeight = height;
			}
			else
			{
				let diff = Math.floor(this.#positioning.startX - event.clientX);
				diff = this.#positioning.direction & Direction.EAST ? -diff : diff;

				const width = Math.round(clamp(
					this.#positioning.startWidth + diff,
					this.#minWidth,
					this.#getMaxContainerWidth(),
				));

				Dom.style(target, 'width', `${width}px`);
				this.emit('onResize', { width, height: this.#positioning.currentHeight });

				this.#positioning.currentWidth = width;
			}
		}
	}

	#handlePointerUp()
	{
		if (this.#positioning.isResizing)
		{
			setTimeout(() => {
				const width: number = this.#positioning.currentWidth;
				const height: number = this.#positioning.currentHeight;

				this.#positioning.startWidth = 0;
				this.#positioning.startHeight = 0;
				this.#positioning.ratio = 0;
				this.#positioning.startX = 0;
				this.#positioning.startY = 0;
				this.#positioning.currentWidth = 0;
				this.#positioning.currentHeight = 0;
				this.#positioning.isResizing = false;

				Dom.removeClass(this.getContainer(), '--resizing');

				this.emit('onResizeEnd', { width, height });
				// setEndCursor();

				Event.unbind(document, 'pointermove', this.#onPointerMoveHandler);
				Event.unbind(document, 'pointerup', this.#onPointerUpHandler);
			}, 200);
		}
	}

	#getMaxContainerWidth(): number
	{
		const maxWidth = Type.isNumber(this.#maxWidth) ? this.#maxWidth : Infinity;

		const editorRootElement = this.getEditor().getRootElement();
		if (editorRootElement !== null)
		{
			return Math.min(editorRootElement.getBoundingClientRect().width - 20, maxWidth);
		}

		return 100;
	}

	#getMaxContainerHeight(): number
	{
		if (Type.isNumber(this.#maxHeight))
		{
			return this.#maxHeight;
		}

		const editorRootElement = this.getEditor().getRootElement();
		if (editorRootElement !== null)
		{
			return editorRootElement.getBoundingClientRect().height - 20;
		}

		return 100;
	}
}
