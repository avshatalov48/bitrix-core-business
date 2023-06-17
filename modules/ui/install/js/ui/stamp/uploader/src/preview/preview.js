import {BaseEvent, EventEmitter} from 'main.core.events';
import {Dom, Tag, Cache, Loc, Type, Event, Text} from 'main.core';
import {DragEndEvent, Draggable, DragMoveEvent} from 'ui.draganddrop.draggable';

import './css/style.css';

type PreviewOptions = {
	events: {
		[key: string]: (event: BaseEvent) => void,
	},
};

type DrawOptions = {
	sX: number,
	sY: number,
	sWidth: number,
	sHeight: number,
	dX?: number,
	dY?: number,
	dWidth?: number,
	dHeight?: number,
};

export default class Preview extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: PreviewOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Stamp.Uploader');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);

		const draggable = this.cache.remember('draggable', () => {
			return new Draggable({
				container: this.getLayout(),
				draggable: '.ui-stamp-uploader-preview-crop > div',
				type: Draggable.HEADLESS,
				context: window.top,
			});
		});

		draggable.subscribe('start', this.onDragStart.bind(this));
		draggable.subscribe('move', this.onDragMove.bind(this));
		draggable.subscribe('end', this.onDragEnd.bind(this));
	}

	static #loadImage(file: File | Blob): Promise<HTMLImageElement>
	{
		const fileReader = new FileReader();

		return new Promise((resolve) => {
			fileReader.readAsDataURL(file);
			Event.bindOnce(fileReader, 'loadend', () => {
				const image = new Image();
				image.src = fileReader.result;
				Event.bindOnce(image, 'load', () => {
					resolve(image);
				});
			});
		});
	}

	setOptions(options: PreviewOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): PreviewOptions
	{
		return this.cache.get('options', {});
	}

	getDraggable(): Draggable
	{
		return this.cache.get('draggable');
	}

	getDevicePixelRatio(): number
	{
		return window.devicePixelRatio;
	}

	getCanvas(): HTMLCanvasElement
	{
		const canvas = this.cache.remember('canvas', () => {
			return Tag.render`
				<canvas class="ui-stamp-uploader-preview-canvas"></canvas>
			`;
		});

		const timeoutId = setTimeout(() => {
			if (Type.isDomNode(canvas.parentElement) && !this.cache.has('adjustCanvas'))
			{
				const parentRect = {
					width: canvas.parentElement.clientWidth,
					height: canvas.parentElement.clientHeight,
				};

				if (parentRect.width > 0 && parentRect.height > 0)
				{
					void this.cache.remember('adjustCanvas', () => {
						const ratio = this.getDevicePixelRatio();

						canvas.width = parentRect.width * ratio;
						canvas.height = parentRect.height * ratio;

						Dom.style(canvas, {
							width: `${parentRect.width}px`,
							height: `${parentRect.height}px`,
						});

						const context2d = canvas.getContext('2d');

						const {context2d: context2dOptions = {}} = this.getOptions();
						if (Type.isPlainObject(context2dOptions))
						{
							Object.assign(context2d, context2dOptions);
						}

						context2d.scale(ratio, ratio);
					});
				}
			}

			clearTimeout(timeoutId);
		});

		return canvas;
	}

	getImagePreviewLayout(): HTMLDivElement
	{
		return this.cache.remember('imagePreviewLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-preview-image">
					${this.getCanvas()}
				</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div 
					class="ui-stamp-uploader-preview" 
					title="${Loc.getMessage('UI_STAMP_UPLOADER_PREVIEW_TITLE')}"
				>
					${this.getImagePreviewLayout()}
					${this.getCropControl()}
				</div>
			`;
		});
	}

	clear()
	{
		const canvas = this.getCanvas();
		const context = canvas.getContext('2d');
		context.clearRect(0, 0, canvas.width, canvas.height);
	}

	setSourceImage(image: File | Blob)
	{
		this.cache.set('sourceImage', image);
	}

	getSourceImage(): File | Blob
	{
		return this.cache.get('sourceImage', null);
	}

	setSourceImageRect(rect: DOMRect | {width: number, height: number})
	{
		this.cache.set('sourceImageRect', rect);
	}

	getSourceImageRect(): DOMRect | {width: number, height: number}
	{
		return this.cache.get('sourceImageRect', {});
	}

	setCurrentDrawOptions(drawOptions: DrawOptions)
	{
		this.cache.set('currentDrawOptions', drawOptions);
	}

	getCurrentDrawOptions(): DrawOptions
	{
		return this.cache.get('currentDrawOptions', {});
	}

	applyCrop(): Promise<any>
	{
		const cropRect = this.getCropRect();
		const drawOptions = this.getCurrentDrawOptions();
		const sourceImageRect = this.getSourceImageRect();
		const imageScaleRatio = (sourceImageRect.width / drawOptions.dWidth);
		const canvas = this.getCanvas();

		const cropOptions = {
			sX: (cropRect.left - drawOptions.dX) * imageScaleRatio,
			sY: (cropRect.top - drawOptions.dY) * imageScaleRatio,
			sWidth: cropRect.width * imageScaleRatio,
			sHeight: cropRect.height * imageScaleRatio,
			dWidth: cropRect.width,
			dHeight: cropRect.height,
			dX: (canvas.clientWidth - cropRect.width) / 2,
			dY: (canvas.clientHeight - cropRect.height) / 2,
		};

		return this.renderImage(this.getSourceImage(), cropOptions);
	}

	renderImage(file: File | Blob, drawOptions: DrawOptions = {}): Promise<any>
	{
		const canvas: HTMLCanvasElement = this.getCanvas();
		const context2d: CanvasRenderingContext2D = canvas.getContext('2d');

		return Preview
			.#loadImage(file)
			.then((sourceImage: HTMLImageElement) => {
				const sourceImageRect = {
					width: sourceImage.width,
					height: sourceImage.height,
				};

				const scaleRatio = Math.min(
					canvas.clientWidth / sourceImageRect.width,
					canvas.clientHeight / sourceImageRect.height,
				);

				const preparedDrawOptions = {
					sX: 0,
					sY: 0,
					sWidth: sourceImageRect.width,
					sHeight: sourceImageRect.height,
					dX: (canvas.clientWidth - (sourceImageRect.width * scaleRatio)) / 2,
					dY: (canvas.clientHeight - (sourceImageRect.height * scaleRatio)) / 2,
					dWidth: sourceImageRect.width * scaleRatio,
					dHeight: sourceImageRect.height * scaleRatio,
					...drawOptions,
				};

				this.setSourceImageRect(sourceImageRect);
				this.setCurrentDrawOptions(preparedDrawOptions);

				this.clear();

				context2d.drawImage(
					sourceImage,
					preparedDrawOptions.sX,
					preparedDrawOptions.sY,
					preparedDrawOptions.sWidth,
					preparedDrawOptions.sHeight,
					preparedDrawOptions.dX,
					preparedDrawOptions.dY,
					preparedDrawOptions.dWidth,
					preparedDrawOptions.dHeight,
				);
			});
	}

	setInitialCropRect(rect: {} | DOMRect)
	{
		this.cache.set('initialCropRect', rect);
	}

	getInitialCropRect(): {} | DOMRect
	{
		return this.cache.get('initialCropRect');
	}

	getCropControl(): HTMLDivElement
	{
		return this.cache.remember('cropControl', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-preview-crop">
					<div class="ui-stamp-uploader-preview-crop-top"></div>
					<div class="ui-stamp-uploader-preview-crop-right"></div>
					<div class="ui-stamp-uploader-preview-crop-bottom"></div>
					<div class="ui-stamp-uploader-preview-crop-left"></div>
					<div class="ui-stamp-uploader-preview-crop-rotate"></div>
				</div>
			`;
		});
	}

	#setIsCropEnabled(value: boolean)
	{
		this.cache.set('isCropEnabled', value);
	}

	isCropEnabled(): boolean
	{
		return this.cache.get('isCropEnabled', false);
	}

	enableCrop()
	{
		this.renderImage(this.getSourceImage())
			.then(() => {
				const control = this.getCropControl();
				const drawOptions = this.getCurrentDrawOptions();

				Dom.style(control, {
					top: `${drawOptions.dY}px`,
					bottom: `${drawOptions.dY}px`,
					left: `${drawOptions.dX}px`,
					right: `${drawOptions.dX}px`,
				});

				Dom.addClass(control, 'ui-stamp-uploader-preview-crop-show');

				this.#setIsCropEnabled(true);
			});
	}

	disableCrop()
	{
		Dom.removeClass(this.getCropControl(), 'ui-stamp-uploader-preview-crop-show');
		this.#setIsCropEnabled(false);
	}

	onDragStart()
	{
		const cropControl = this.getCropControl();

		this.setInitialCropRect({
			top: Text.toNumber(Dom.style(cropControl, 'top')),
			left: Text.toNumber(Dom.style(cropControl, 'left')),
			right: Text.toNumber(Dom.style(cropControl, 'right')),
			bottom: Text.toNumber(Dom.style(cropControl, 'bottom')),
		});
	}

	onDragMove(event: DragMoveEvent)
	{
		const data = event.getData();
		const initialRect = this.getInitialCropRect();
		const drawOptions = this.getCurrentDrawOptions();
		const requiredOffset = 20;
		const canvasWidth = drawOptions.dX + drawOptions.dWidth + drawOptions.dX;
		const canvasHeight = drawOptions.dY + drawOptions.dHeight + drawOptions.dY;

		if (data.source.matches('.ui-stamp-uploader-preview-crop-right'))
		{
			const position = Math.max(
				Math.min(
					initialRect.right - data.offsetX,
					(canvasWidth - initialRect.left) - requiredOffset,
				),
				drawOptions.dX,
			);

			Dom.style(this.getCropControl(), 'right', `${position}px`);
		}

		if (data.source.matches('.ui-stamp-uploader-preview-crop-left'))
		{
			const position = Math.max(
				Math.min(
					initialRect.left + data.offsetX,
					canvasWidth - initialRect.right - requiredOffset,
				),
				drawOptions.dX,
			);

			Dom.style(this.getCropControl(), 'left', `${position}px`);
		}

		if (data.source.matches('.ui-stamp-uploader-preview-crop-top'))
		{
			const position = Math.max(
				drawOptions.dY,
				Math.min(
					initialRect.top + data.offsetY,
					canvasHeight - initialRect.bottom - requiredOffset,
				),
			);

			Dom.style(this.getCropControl(), 'top', `${position}px`);
		}

		if (data.source.matches('.ui-stamp-uploader-preview-crop-bottom'))
		{
			const position = Math.max(
				Math.min(
					canvasHeight - initialRect.top - requiredOffset,
					initialRect.bottom - data.offsetY,
				),
				drawOptions.dY,
			);

			Dom.style(this.getCropControl(), 'bottom', `${position}px`);
		}
	}

	getCropRect(): DOMRect | {}
	{
		const cropControl = this.getCropControl();
		const width = cropControl.clientWidth;
		const height = cropControl.clientHeight;
		const left = Math.round(Text.toNumber(Dom.style(cropControl, 'left')));
		const top = Math.round(Text.toNumber(Dom.style(cropControl, 'top')));
		const canvas = this.getCanvas();
		const canvasRect = canvas.getBoundingClientRect();
		const right = canvasRect.width - (left + width);
		const bottom = canvasRect.height - (top + height);

		return {
			width,
			height,
			top,
			left,
			right,
			bottom,
		};
	}

	async getValue(): Promise<Blob>
	{
		const canvas = this.getCanvas();
		return await new Promise((resolve) => {
			canvas.toBlob(resolve, 'image/png');
		});
	}

	onDragEnd(event: DragEndEvent)
	{

	}

	show(file: File | Blob)
	{
		this.setSourceImage(file);
		void this.renderImage(file);
		Dom.addClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	}

	hide()
	{
		Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-preview-show');
	}

	getFile(): Promise<Blob | File>
	{
		const drawOptions = this.getCurrentDrawOptions();
		const canvas = document.createElement('canvas');
		const context2d = canvas.getContext('2d');

		return new Promise((resolve) => {
			this.getCanvas().toBlob((blob) => {
				void Preview
					.#loadImage(blob)
					.then((image) => {
						const ratio = this.getDevicePixelRatio();

						canvas.width = drawOptions.dWidth * ratio;
						canvas.height = drawOptions.dHeight * ratio;

						context2d.drawImage(
							image,
							0,
							0,
							image.width,
							image.height,
							-((image.width - canvas.width) / 2),
							-((image.height - canvas.height) / 2),
							image.width,
							image.height,
						);

						canvas.toBlob((resultBlob) => {
							resolve(resultBlob);
						});
					});
			});
		});
	}
}