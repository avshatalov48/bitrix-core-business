import { Browser, Type } from 'main.core';

import Filter from './filter';
import resizeImage from '../helpers/resize-image';
import isResizableImage from '../helpers/is-resizable-image';
import isVideo from '../helpers/is-video';
import createVideoPreview from '../helpers/create-video-preview';

import type UploaderFile from '../uploader-file';
import type Uploader from '../uploader';
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

	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		super(uploader);

		const options = Type.isPlainObject(filterOptions) ? filterOptions : {};

		this.setImagePreviewWidth(options['imagePreviewWidth']);
		this.setImagePreviewHeight(options['imagePreviewHeight']);
		this.setImagePreviewQuality(options['imagePreviewQuality']);
		this.setImagePreviewUpscale(options['imagePreviewUpscale']);
		this.setImagePreviewResizeMode(options['imagePreviewResizeMode']);
		this.setImagePreviewMimeType(options['imagePreviewMimeType'])
		this.setImagePreviewMimeTypeMode(options['imagePreviewMimeTypeMode']);
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject) => {

			if (isResizableImage(file.getBinary()))
			{
				resizeImage(file.getBinary(), this.#getResizeImageOptions())
					.then(({ preview, width, height }) => {
						file.setClientPreview(preview, width, height);
						resolve();
					})
					.catch((error) => {
						if (error)
						{
							console.log('Uploader: image resize error', error);
						}

						resolve();
					})
				;
			}
			else if (isVideo(file.getBinary()) && !Browser.isSafari())
			{
				createVideoPreview(file.getBinary(), this.#getResizeImageOptions())
					.then(({ preview, width, height }) => {
						file.setClientPreview(preview, width, height);
						resolve();
					})
					.catch((error) => {
						if (error)
						{
							console.log('Uploader: video preview error', error);
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

	setImagePreviewWidth(value: number)
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

	setImagePreviewHeight(value: number)
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

	setImagePreviewQuality(value: number)
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

	setImagePreviewUpscale(value: boolean)
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

	setImagePreviewResizeMode(value: ResizeImageMode)
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

	setImagePreviewMimeType(value: ResizeImageMimeType)
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

	setImagePreviewMimeTypeMode(value: ResizeImageMimeTypeMode)
	{
		if (['auto', 'force'].includes(value))
		{
			this.#imagePreviewMimeTypeMode = value;
		}
	}

	#getResizeImageOptions(): ResizeImageOptions
	{
		return {
			width: this.getImagePreviewWidth(),
			height: this.getImagePreviewHeight(),
			mode: this.getImagePreviewResizeMode(),
			upscale: this.getImagePreviewUpscale(),
			quality: this.getImagePreviewQuality(),
			mimeType: this.getImagePreviewMimeType(),
			mimeTypeMode: this.getImagePreviewMimeTypeMode(),
		};
	}
}
