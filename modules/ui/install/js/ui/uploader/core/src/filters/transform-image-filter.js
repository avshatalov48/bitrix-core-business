import { Type } from 'main.core';
import Filter from './filter';

import isResizableImage from '../helpers/is-resizable-image';
import resizeImage from '../helpers/resize-image';

import type Uploader from '../uploader';

export default class TransformImageFilter extends Filter
{
	resizeWidth: number = null;
	resizeHeight: number = null;
	resizeMethod: string = 'contain';
	resizeMimeType: string = 'image/jpeg';
	resizeQuality: number = 0.92;

	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		super(uploader);

		const options = Type.isPlainObject(filterOptions) ? filterOptions : {};
		if (Type.isNumber(options['imageResizeWidth']) && options['imageResizeWidth'] > 0)
		{
			this.resizeWidth = options['imageResizeWidth'];
		}

		if (Type.isNumber(options['imageResizeHeight']) && options['imageResizeHeight'] > 0)
		{
			this.resizeHeight = options['imageResizeHeight'];
		}

		if (['contain', 'force', 'cover'].includes(options['imageResizeMethod']))
		{
			this.resizeMethod = options['imageResizeMethod'];
		}

		if (Type.isNumber(options['imageResizeQuality']))
		{
			this.resizeQuality = Math.min(Math.max(0.1, options['imageResizeQuality']), 1);
		}

		if (['image/jpeg', 'image/png'].includes(options['imageResizeMimeType']))
		{
			this.resizeMimeType = options['imageResizeMimeType'];
		}
	}

	apply(file: File): Promise
	{
		return new Promise((resolve, reject) => {
			if (!isResizableImage(file))
			{
				return resolve(file);
			}

			if (this.resizeWidth === null && this.resizeHeight === null)
			{
				return resolve(file);
			}

			const options = {
				width: this.resizeWidth,
				height: this.resizeHeight,
				mode: this.resizeMethod,
				quality: this.resizeQuality,
				mimeType: this.resizeMimeType,
			};

			resizeImage(file, options)
				.then(({ preview }) => {
					resolve(preview);
				})
				.catch(() => {
					resolve(file);
				})
			;
		});
	}
}
