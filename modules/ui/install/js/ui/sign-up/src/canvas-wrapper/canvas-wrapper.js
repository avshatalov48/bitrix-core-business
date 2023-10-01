import {Cache, Dom, Tag, Type, Event, Text} from 'main.core';
import type {CanvasWrapperOptions} from '../types/canvas-wrapper-options';

export class CanvasWrapper
{
	cache = new Cache.MemoryCache();

	constructor(options: CanvasWrapperOptions)
	{
		this.setOptions(options);
	}

	setOptions(options: CanvasWrapperOptions)
	{
		this.cache.set('options', options);
	}

	getOptions(): CanvasWrapperOptions
	{
		return this.cache.get('options');
	}

	getDevicePixelRatio(): number
	{
		return window.devicePixelRatio;
	}

	getLayout(): HTMLCanvasElement
	{
		const canvas = this.cache.remember('layout', () => {
			return Tag.render`
				<canvas class="ui-sign-up-canvas"></canvas>
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
						const canvas = this.getLayout();
						const ratio = this.getDevicePixelRatio();

						canvas.width = parentRect.width * ratio;
						canvas.height = parentRect.height * ratio;

						Dom.style(canvas, {
							width: `${parentRect.width}px`,
							height: `${parentRect.height}px`,
						});

						const context2d = this.getLayout().getContext('2d');

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
		const canvas = this.getLayout();
		const context = canvas.getContext('2d');
		context.clearRect(0, 0, (canvas.width * 2), (canvas.height * 2));
	}

	renderText(text: string, textColor: null | string = null)
	{
		const preparedText = String(text).trim();

		const canvas = this.getLayout();
		const context = canvas.getContext('2d');
		context.font = '34px Comforter Brush';

		this.clear();

		const ratio = this.getDevicePixelRatio();
		const maxTextWidth = canvas.width - 20;
		let fontSize = Text.toNumber(context.font);
		while (fontSize > 1 && context.measureText(preparedText).width * ratio > maxTextWidth)
		{
			fontSize -= 1;
			context.font = `${fontSize}px Comforter Brush`;
		}

		const textWidth = context.measureText(preparedText).width * ratio;
		if (textColor !== null && textColor !== '')
		{
			context.fillStyle = textColor;
		}

		context.fillText(preparedText, (canvas.width - textWidth) / (2 * ratio), 34);
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

	renderImage(file: File | Blob): Promise<any>
	{
		return CanvasWrapper
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
}