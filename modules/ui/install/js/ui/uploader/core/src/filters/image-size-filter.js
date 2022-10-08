import { Type } from 'main.core';

import Filter from './filter';
import UploaderError from '../uploader-error';
import getImageSize from '../helpers/image-size/get-image-size';

import type Uploader from '../uploader';
import type UploaderFile from '../uploader-file';

export default class ImageSizeFilter extends Filter
{
	imageMinWidth: number = 1;
	imageMinHeight: number = 1;
	imageMaxWidth: number = 10000;
	imageMaxHeight: number = 10000;
	ignoreUnknownImageTypes: boolean = false;

	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		super(uploader);

		const options = Type.isPlainObject(filterOptions) ? filterOptions : {};

		['imageMinWidth', 'imageMinHeight', 'imageMaxWidth', 'imageMaxHeight'].forEach(option => {
			this[option] = Type.isNumber(options[option]) && options[option] > 0 ? options[option] : this[option];
		});

		if (Type.isBoolean(options['ignoreUnknownImageTypes']))
		{
			this.ignoreUnknownImageTypes = options['ignoreUnknownImageTypes'];
		}
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject) => {

			if (!file.isImage())
			{
				resolve();
				return;
			}

			getImageSize(file.getFile())
				.then(({ width, height }) => {
					file.setWidth(width);
					file.setHeight(height);

					if (width < this.imageMinWidth || height < this.imageMinHeight)
					{
						reject(new UploaderError(
							'IMAGE_IS_TOO_SMALL',
							{
								minWidth: this.imageMinWidth,
								minHeight: this.imageMinHeight,
							},
						));
					}
					else if (width > this.imageMaxWidth || height > this.imageMaxHeight)
					{
						reject(new UploaderError(
							'IMAGE_IS_TOO_BIG',
							{
								maxWidth: this.imageMaxWidth,
								maxHeight: this.imageMaxHeight,
							},
						));
					}
					else
					{
						resolve();
					}
				})
				.catch(() => {
					if (this.ignoreUnknownImageTypes)
					{
						resolve();
					}
					else
					{
						reject(new UploaderError('IMAGE_TYPE_NOT_SUPPORTED'));
					}
				})
			;
		});
	}
}
