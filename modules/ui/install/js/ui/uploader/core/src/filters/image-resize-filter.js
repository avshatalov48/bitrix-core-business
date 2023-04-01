import { Type } from 'main.core';
import Filter from './filter';

import isResizableImage from '../helpers/is-resizable-image';
import resizeImage from '../helpers/resize-image';

import type Uploader from '../uploader';
import type UploaderFile from '../uploader-file';
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

	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		super(uploader);

		const options = Type.isPlainObject(filterOptions) ? filterOptions : {};

		this.setResizeWidth(options['imageResizeWidth'])
		this.setResizeHeight(options['imageResizeHeight'])
		this.setResizeMode(options['imageResizeMode']);
		this.setResizeMimeType(options['imageResizeMimeType']);
		this.setResizeMimeTypeMode(options['imageResizeMimeTypeMode']);
		this.setResizeQuality(options['imageResizeQuality']);
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject) => {

			if (this.getResizeWidth() === null && this.getResizeHeight() === null)
			{
				return resolve();
			}

			if (!isResizableImage(file.getBinary()))
			{
				return resolve();
			}

			const options: ResizeImageOptions = {
				width: this.getResizeWidth(),
				height: this.getResizeHeight(),
				mode: this.getResizeMode(),
				quality: this.getResizeQuality(),
				mimeType: this.getResizeMimeType(),
				mimeTypeMode: this.getResizeMimeTypeMode(),
			};

			resizeImage(file.getBinary(), options)
				.then(({ preview, width, height }) => {
					file.setWidth(width);
					file.setHeight(height);
					file.setFile(preview);

					resolve();
				})
				.catch((error) => {
					if (error)
					{
						console.log('image resize error', error);
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

	setResizeWidth(value: ?number)
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

	setResizeHeight(value: ?number)
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

	setResizeQuality(value: number)
	{
		if (Type.isNumber(value) && value > 0.1 && value <= 1)
		{
			this.#resizeQuality = value;
		}
	}
}
