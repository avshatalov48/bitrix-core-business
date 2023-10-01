import {Loc, Tag, Event} from 'main.core';
import {Content} from '../content';
import {CanvasWrapper} from '../../canvas-wrapper/canvas-wrapper';
import {getPoint} from './internal/get-point';

import './css/style.css';

let preventScrolling = false;
Event.bind(window, 'touchmove', (event) => {
	if (preventScrolling)
	{
		event.preventDefault();
	}
}, {passive: false});

export class TouchContent extends Content
{
	static LineWidth = 3;

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.UI.SignUp.Content.TouchContent');
		this.subscribeFromOptions(options?.events);

		const canvasLayout = this.getCanvas().getLayout();
		Event.bind(canvasLayout, 'mousedown', this.onCanvasMouseDown.bind(this));
		Event.bind(document, 'mouseup', this.onCanvasMouseUp.bind(this));
		Event.bind(canvasLayout, 'mousemove', this.onCanvasMouseMove.bind(this));
		Event.bind(canvasLayout, 'touchstart', this.onCanvasMouseDown.bind(this));
		Event.bind(canvasLayout, 'touchend', this.onCanvasMouseUp.bind(this));
		Event.bind(canvasLayout, 'touchmove', this.onCanvasMouseMove.bind(this));
	}

	setIsDrawing(value: boolean)
	{
		this.cache.set('isDrawing', value);
	}

	getIsDrawing(): boolean
	{
		return this.cache.get('isDrawing', false);
	}

	setStartEvent(event: MouseEvent)
	{
		this.cache.set('startEvent', event);
	}

	getStartEvent(): MouseEvent
	{
		return this.cache.get('startEvent');
	}

	onCanvasMouseDown(event: MouseEvent)
	{
		this.setIsDrawing(true);
		preventScrolling = true;

		const context2d = this.getCanvas().getLayout().getContext('2d');
		context2d.beginPath();

		const point = getPoint(event);
		context2d.moveTo(point.x, point.y);
		this.setStartEvent(event);
		this.emit('onChange');
	}

	onCanvasMouseUp(event: MouseEvent)
	{
		this.setIsDrawing(false);
		preventScrolling = false;

		const canvasLayout = this.getCanvas().getLayout();
		const context2d = canvasLayout.getContext('2d');
		context2d.closePath();

		if (event.currentTarget === canvasLayout)
		{
			const startEvent = this.getStartEvent();
			const startPoint = getPoint(startEvent);
			const currentPoint = getPoint(event);
			if (
				startPoint.x === currentPoint.x
				&& startPoint.y === currentPoint.y
			)
			{
				context2d.lineTo(currentPoint.x, currentPoint.y);
				context2d.stroke();
			}
		}

		this.emit('onChange');
	}

	onCanvasMouseMove(event: MouseEvent)
	{
		if (this.getIsDrawing())
		{
			const context2d = this.getCanvas().getLayout().getContext('2d');
			const point = getPoint(event);

			const strokeColor = this.getColor();
			if (strokeColor !== null && strokeColor !== '')
			{
				context2d.strokeStyle = strokeColor;
			}

			context2d.lineTo(point.x, point.y);
			context2d.stroke();
		}

		this.emit('onChange');
	}

	onCanvasMouseOut()
	{
		this.setIsDrawing(false);
		preventScrolling = false;

		const context2d = this.getCanvas().getLayout().getContext('2d');
		context2d.closePath();

		this.emit('onChange');
	}

	getCanvas(): CanvasWrapper
	{
		return this.cache.remember('canvas', () => {
			return new CanvasWrapper({
				context2d: {
					lineWidth: TouchContent.LineWidth,
					strokeStyle: '000000',
					lineJoin: 'round',
					lineCap: 'round',
				},
			});
		});
	}

	getClearButton(): HTMLDivElement
	{
		return this.cache.remember('clearButton', () => {
			return Tag.render`
				<div class="ui-sign-up-touch-clear-button" onclick="${this.onClearClick.bind(this)}">
					${Loc.getMessage('UI_SIGN_UP_TOUCH_CLEAR_BUTTON')}
				</div>
			`;
		});
	}

	onClearClick(event: MouseEvent)
	{
		event.preventDefault();
		this.getCanvas().clear();
		this.emit('onChange');
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			const onTouchMove = (event: TouchEvent) => {
				event.preventDefault();
				event.stopPropagation();
			};

			return Tag.render`
				<div class="ui-sign-up-content" ontouchmove="${onTouchMove}">
					<div class="ui-sign-up-touch-form-label">
						${(() => {
							if (this.getOptions().mode === 'mobile')
							{
								return Loc.getMessage('UI_SIGN_UP_TOUCH_LAYOUT_MOBILE_LABEL');
							}
				
							return Loc.getMessage('UI_SIGN_UP_TOUCH_LAYOUT_LABEL');
						})()}
					</div>
					<div class="ui-sign-up-content-touch-preview">
						${this.getClearButton()}
						${this.getCanvas().getLayout()}
					</div>
				</div>
			`;
		});
	}
}