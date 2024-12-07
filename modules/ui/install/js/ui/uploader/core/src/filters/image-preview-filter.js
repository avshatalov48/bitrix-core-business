import { Browser, Type } from 'main.core';

import Filter from './filter';
import resizeImage from '../helpers/resize-image';
import isResizableImage from '../helpers/is-resizable-image';
import isVideo from '../helpers/is-video';
import createVideoPreview from '../helpers/create-video-preview';

import type UploaderFile from '../uploader-file';
import type Uploader from '../uploader';
import type { UploaderOptions } from '../types/uploader-options';
import type {
	ResizeImageOptions,
	ResizeImageMimeTypeMode,
	ResizeImageMimeType,
	ResizeImageMode,
} from '../types/resize-image-options';

export default class ImagePreviewFilter extends Filter
{
	#imagePreviewWidth: number = 300;
	#imagePreviewHeight: number = 300;
	#imagePreviewQuality: number = 0.92;
	#imagePreviewMimeType: ResizeImageMimeType = 'image/jpeg';
	#imagePreviewMimeTypeMode: ResizeImageMimeTypeMode = 'auto';
	#imagePreviewUpscale: boolean = false;
	#imagePreviewResizeMode: ResizeImageMode = 'contain';
	#imagePreviewFilter: Function = null;

	constructor(uploader: Uploader, filterOptions: UploaderOptions = {})
	{
		super(uploader);

		const options: UploaderOptions = Type.isPlainObject(filterOptions) ? filterOptions : {};

		this.setImagePreviewWidth(options.imagePreviewWidth);
		this.setImagePreviewHeight(options.imagePreviewHeight);
		this.setImagePreviewQuality(options.imagePreviewQuality);
		this.setImagePreviewUpscale(options.imagePreviewUpscale);
		this.setImagePreviewResizeMode(options.imagePreviewResizeMode);
		this.setImagePreviewMimeType(options.imagePreviewMimeType);
		this.setImagePreviewMimeTypeMode(options.imagePreviewMimeTypeMode);
		this.setImagePreviewFilter(options.imagePreviewFilter);
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve): void => {
			if (!file.shouldTreatImageAsFile() && isResizableImage(file.getBinary()))
			{
				const result: boolean | ResizeImageOptions = this.invokeFilter(file);
				if (result === false)
				{
					resolve();

					return;
				}

				const resizeOptions = Type.isPlainObject(result) ? result : {};

				// const start = performance.now();
				resizeImage(file.getBinary(), this.#getResizeImageOptions(resizeOptions))
					.then(({ preview, width, height }): void => {
						// console.log(`resizeImage took ${performance.now() - start} milliseconds.`);
						file.setClientPreview(preview, width, height);
						resolve();
					})
					.catch((error): void => {
						if (error)
						{
							// eslint-disable-next-line no-console
							console.warn('Uploader: image resize error', error);
						}

						resolve();
					})
				;
			}
			else if (isVideo(file) && !Browser.isSafari())
			{
				createVideoPreview(file.getBinary(), this.#getResizeImageOptions())
					.then(({ preview, width, height }): void => {
						file.setClientPreview(preview, width, height);
						resolve();
					})
					.catch((error): void => {
						if (error)
						{
							// eslint-disable-next-line no-console
							console.warn('Uploader: video preview error', error);
						}

						resolve();
					})
				;
			}
			else
			{
				resolve();
			}
		});
	}

	getImagePreviewWidth(): number
	{
		return this.#imagePreviewWidth;
	}

	setImagePreviewWidth(value: number): void
	{
		if (Type.isNumber(value) && value > 0)
		{
			this.#imagePreviewWidth = value;
		}
	}

	getImagePreviewHeight(): number
	{
		return this.#imagePreviewHeight;
	}

	setImagePreviewHeight(value: number): void
	{
		if (Type.isNumber(value) && value > 0)
		{
			this.#imagePreviewHeight = value;
		}
	}

	getImagePreviewQuality(): number
	{
		return this.#imagePreviewQuality;
	}

	setImagePreviewQuality(value: number): void
	{
		if (Type.isNumber(value) && value > 0.1 && value <= 1)
		{
			this.#imagePreviewQuality = value;
		}
	}

	getImagePreviewUpscale(): boolean
	{
		return this.#imagePreviewUpscale;
	}

	setImagePreviewUpscale(value: boolean): void
	{
		if (Type.isBoolean(value))
		{
			this.#imagePreviewUpscale = value;
		}
	}

	getImagePreviewResizeMode(): ResizeImageMode
	{
		return this.#imagePreviewResizeMode;
	}

	setImagePreviewResizeMode(value: ResizeImageMode): void
	{
		if (['contain', 'force', 'cover'].includes(value))
		{
			this.#imagePreviewResizeMode = value;
		}
	}

	getImagePreviewMimeType(): ResizeImageMimeType
	{
		return this.#imagePreviewMimeType;
	}

	setImagePreviewMimeType(value: ResizeImageMimeType): void
	{
		if (['image/jpeg', 'image/png', 'image/webp'].includes(value))
		{
			this.#imagePreviewMimeType = value;
		}
	}

	getImagePreviewMimeTypeMode(): ResizeImageMimeTypeMode
	{
		return this.#imagePreviewMimeTypeMode;
	}

	setImagePreviewMimeTypeMode(value: ResizeImageMimeTypeMode): void
	{
		if (['auto', 'force'].includes(value))
		{
			this.#imagePreviewMimeTypeMode = value;
		}
	}

	setImagePreviewFilter(fn: Function): void
	{
		if (Type.isFunction(fn))
		{
			this.#imagePreviewFilter = fn;
		}
	}

	invokeFilter(file: UploaderFile): boolean | ResizeImageOptions
	{
		if (this.#imagePreviewFilter !== null)
		{
			const result = this.#imagePreviewFilter(file);
			if (Type.isBoolean(result) || Type.isPlainObject(result))
			{
				return result;
			}
		}

		return true;
	}

	#getResizeImageOptions(overrides: ResizeImageOptions = {}): ResizeImageOptions
	{
		return {
			width: Type.isNumber(overrides.width) ? overrides.width : this.getImagePreviewWidth(),
			height: Type.isNumber(overrides.height) ? overrides.height : this.getImagePreviewHeight(),
			mode: Type.isStringFilled(overrides.mode) ? overrides.mode : this.getImagePreviewResizeMode(),
			upscale: Type.isBoolean(overrides.upscale) ? overrides.upscale : this.getImagePreviewUpscale(),
			quality: Type.isNumber(overrides.quality) ? overrides.quality : this.getImagePreviewQuality(),
			mimeType: Type.isStringFilled(overrides.mimeType) ? overrides.mimeType : this.getImagePreviewMimeType(),
			mimeTypeMode:
				Type.isStringFilled(overrides.mimeTypeMode)
					? overrides.mimeTypeMode
					: this.getImagePreviewMimeTypeMode()
			,
		};
	}
}
