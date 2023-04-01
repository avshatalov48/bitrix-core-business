import {BaseEvent, EventEmitter} from 'main.core.events';
import {Dom, Tag, Cache, Loc, Type, Event} from 'main.core';

import './css/style.css';

type PreviewOptions = {
	events: {
		[key: string]: (event: BaseEvent) => void,
	},
};

export default class Crop extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: PreviewOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.Stamp.Uploader.Crop');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);
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

	getDevicePixelRatio(): number
	{
		return window.devicePixelRatio;
	}

	getCanvas(): HTMLCanvasElement
	{
		const canvas = this.cache.remember('canvas', () => {
			return Tag.render`
				<canvas class="ui-stamp-uploader-crop-canvas"></canvas>
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

	clear()
	{
		const canvas = this.getCanvas();
		const context = canvas.getContext('2d');
		context.clearRect(0, 0, canvas.width, canvas.height);
	}

	renderImage(file: File | Blob): Promise<any>
	{
		return Crop
			.#loadImage(file)
			.then((image: HTMLImageElement) => {
				const canvas: HTMLCanvasElement = this.getLayout();
				const context2d: CanvasRenderingContext2D = canvas.getContext('2d');

				const wRatio = canvas.clientWidth / image.width;
				const hRatio = canvas.clientHeight / image.height;
				const ratio = Math.min(wRatio, hRatio);
				const offsetX = (canvas.clientWidth - (image.width * ratio)) / 2;
				const offsetY = (canvas.clientHeight - (image.height * ratio)) / 2;

				this.clear();

				context2d.drawImage(
					image,
					0,
					0,
					image.width,
					image.height,
					offsetX,
					offsetY,
					(image.width * ratio),
					(image.height * ratio),
				);
			});
	}

	getImagePreviewLayout(): HTMLDivElement
	{
		return this.cache.remember('cropLayout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-crop-image">${this.getCanvas()}</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div 
					class="ui-stamp-uploader-crop" 
					title="${Loc.getMessage('UI_STAMP_UPLOADER_PREVIEW_TITLE')}"
				>
					${this.getImagePreviewLayout()}
					${this.getCropControl()}
				</div>
			`;
		});
	}

	getCropControl(): HTMLDivElement
	{
		return this.cache.remember('cropControl', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-crop-control">
					<div class="ui-stamp-uploader-crop-control-top"></div>
					<div class="ui-stamp-uploader-crop-control-right"></div>
					<div class="ui-stamp-uploader-crop-control-bottom"></div>
					<div class="ui-stamp-uploader-crop-control-left"></div>
					<div class="ui-stamp-uploader-crop-control-rotate"></div>
				</div>
			`;
		});
	}

	show()
	{
		Dom.addClass(this.getLayout(), 'ui-stamp-uploader-crop-show');
	}

	hide()
	{
		Dom.removeClass(this.getLayout(), 'ui-stamp-uploader-crop-show');
	}
}