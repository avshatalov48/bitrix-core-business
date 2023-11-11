import { Type } from 'main.core';
import Filter from './filter';

import isResizableImage from '../helpers/is-resizable-image';
import resizeImage from '../helpers/resize-image';

import type Uploader from '../uploader';
import type UploaderFile from '../uploader-file';
import type { UploaderOptions } from '../types/uploader-options';
import type {
	ResizeImageOptions,
	ResizeImageMimeTypeMode,
	ResizeImageMimeType,
	ResizeImageMode,
} from '../types/resize-image-options';

export default class ImageResizeFilter extends Filter
{
	#resizeWidth: number = null;
	#resizeHeight: number = null;
	#resizeMethod: ResizeImageMode = 'contain';
	#resizeMimeType: ResizeImageMimeType = 'image/jpeg';
	#resizeMimeTypeMode: ResizeImageMimeTypeMode = 'auto';
	#resizeQuality: number = 0.92;
	#resizeFilter: Function = null;

	constructor(uploader: Uploader, filterOptions: UploaderOptions = {})
	{
		super(uploader);

		const options: UploaderOptions = Type.isPlainObject(filterOptions) ? filterOptions : {};

		this.setResizeWidth(options.imageResizeWidth);
		this.setResizeHeight(options.imageResizeHeight);
		this.setResizeMode(options.imageResizeMode);
		this.setResizeMimeType(options.imageResizeMimeType);
		this.setResizeMimeTypeMode(options.imageResizeMimeTypeMode);
		this.setResizeQuality(options.imageResizeQuality);
		this.setResizeFilter(options.imageResizeFilter);
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve): void => {
			if (this.getResizeWidth() === null && this.getResizeHeight() === null)
			{
				resolve();

				return;
			}

			if (file.shouldTreatImageAsFile() || !isResizableImage(file.getBinary()))
			{
				resolve();

				return;
			}

			const result: boolean | ResizeImageOptions = this.invokeFilter(file);
			if (result === false)
			{
				resolve();

				return;
			}

			const overrides = Type.isPlainObject(result) ? result : {};
			const options: ResizeImageOptions = {
				width: Type.isNumber(overrides.width) ? overrides.width : this.getResizeWidth(),
				height: Type.isNumber(overrides.height) ? overrides.height : this.getResizeHeight(),
				mode: Type.isStringFilled(overrides.mode) ? overrides.mode : this.getResizeMode(),
				quality: Type.isNumber(overrides.quality) ? overrides.quality : this.getResizeQuality(),
				mimeType: Type.isStringFilled(overrides.mimeType) ? overrides.mimeType : this.getResizeMimeType(),
				mimeTypeMode: (
					Type.isStringFilled(overrides.mimeTypeMode) ? overrides.mimeTypeMode : this.getResizeMimeTypeMode()
				),
			};

			resizeImage(file.getBinary(), options)
				.then(({ preview, width, height }): void => {
					file.setWidth(width);
					file.setHeight(height);
					file.setFile(preview);

					resolve();
				})
				.catch((error): void => {
					if (error)
					{
						// eslint-disable-next-line no-console
						console.warn('image resize error', error);
					}

					resolve();
				})
			;
		});
	}

	getResizeWidth(): ?number
	{
		return this.#resizeWidth;
	}

	setResizeWidth(value: ?number): void
	{
		if ((Type.isNumber(value) && value > 0) || Type.isNull(value))
		{
			this.#resizeWidth = value;
		}
	}

	getResizeHeight(): ?number
	{
		return this.#resizeHeight;
	}

	setResizeHeight(value: ?number): void
	{
		if ((Type.isNumber(value) && value > 0) || Type.isNull(value))
		{
			this.#resizeHeight = value;
		}
	}

	getResizeMode(): ResizeImageMode
	{
		return this.#resizeMethod;
	}

	setResizeMode(value): ResizeImageMode
	{
		if (['contain', 'force', 'cover'].includes(value))
		{
			this.#resizeMethod = value;
		}
	}

	getResizeMimeType(): ResizeImageMimeType
	{
		return this.#resizeMimeType;
	}

	setResizeMimeType(value): ResizeImageMimeType
	{
		if (['image/jpeg', 'image/png', 'image/webp'].includes(value))
		{
			this.#resizeMimeType = value;
		}
	}

	getResizeMimeTypeMode(): ResizeImageMimeTypeMode
	{
		return this.#resizeMimeTypeMode;
	}

	setResizeMimeTypeMode(value): ResizeImageMimeTypeMode
	{
		if (['auto', 'force'].includes(value))
		{
			this.#resizeMimeTypeMode = value;
		}
	}

	getResizeQuality(): number
	{
		return this.#resizeQuality;
	}

	setResizeQuality(value: number): void
	{
		if (Type.isNumber(value) && value > 0.1 && value <= 1)
		{
			this.#resizeQuality = value;
		}
	}

	setResizeFilter(fn: Function): void
	{
		if (Type.isFunction(fn))
		{
			this.#resizeFilter = fn;
		}
	}

	invokeFilter(file: UploaderFile): boolean | ResizeImageOptions
	{
		if (this.#resizeFilter !== null)
		{
			const result = this.#resizeFilter(file);
			if (Type.isBoolean(result) || Type.isPlainObject(result))
			{
				return result;
			}
		}

		return true;
	}
}
