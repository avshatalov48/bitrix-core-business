import { Type } from 'main.core';

import Filter from './filter';
import resizeImage from '../helpers/resize-image';
import isResizableImage from '../helpers/is-resizable-image';

import type UploaderFile from '../uploader-file';
import type Uploader from '../uploader';

export default class ImagePreviewFilter extends Filter
{
	imagePreviewWidth: number = 300;
	imagePreviewHeight: number = 300;
	imagePreviewQuality: number = 0.92;
	imagePreviewMimeType: string = 'image/jpeg';
	imagePreviewUpscale: boolean = false;
	imagePreviewResizeMethod: string = 'contain';

	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		super(uploader);

		const options = Type.isPlainObject(filterOptions) ? filterOptions : {};
		const integerOptions = [
			'imagePreviewWidth',
			'imagePreviewHeight',
			'imagePreviewQuality',
		];

		integerOptions.forEach(option => {
			this[option] = Type.isNumber(options[option]) && options[option] > 0 ? options[option] : this[option];
		});

		if (Type.isBoolean(options['imagePreviewUpscale']))
		{
			this.imagePreviewUpscale = options['imagePreviewUpscale'];
		}

		if (['contain', 'force', 'cover'].includes(options['imagePreviewResizeMethod']))
		{
			this.imagePreviewResizeMethod = options['imagePreviewResizeMethod'];
		}

		if (['image/jpeg', 'image/png'].includes(options['imagePreviewMimeType']))
		{
			this.imagePreviewMimeType = options['imagePreviewMimeType'];
		}
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject) => {
			if (!isResizableImage(file.getFile()))
			{
				resolve();
				return;
			}

			const options = {
				width: this.imagePreviewWidth,
				height: this.imagePreviewHeight,
				mode: this.imagePreviewResizeMethod,
				upscale: this.imagePreviewUpscale,
				quality: this.imagePreviewQuality,
				mimeType: this.imagePreviewMimeType,
			};

			resizeImage(file.getFile(), options)
				.then(({ preview, width, height }) => {

					//setTimeout(() => {
						file.setClientPreview(preview, width, height);
						resolve();
					//}, 60000);

				})
				.catch((error) => {
					console.log('resize error', error);
					resolve();
				})
			;
		});
	}
}
