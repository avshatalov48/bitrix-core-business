import { Type } from 'main.core';
import Filter from './filter';
import UploaderError from '../uploader-error';
import formatFileSize from '../helpers/format-file-size';

import type Uploader from '../uploader';
import type UploaderFile from '../uploader-file';

export default class FileSizeFilter extends Filter
{
	maxFileSize: number = null;
	minFileSize: number = null;
	maxTotalFileSize: number = null;
	imageMaxFileSize: number = null;
	imageMinFileSize: number = null;

	constructor(uploader: Uploader, filterOptions: { [key: string]: any } = {})
	{
		super(uploader);

		const options = Type.isPlainObject(filterOptions) ? filterOptions : {};

		const integerOptions = [
			'maxFileSize',
			'minFileSize',
			'maxTotalFileSize',
			'imageMaxFileSize',
			'imageMinFileSize',
		];

		integerOptions.forEach(option => {
			this[option] = Type.isNumber(options[option]) && options[option] >= 0 ? options[option] : this[option];
		});
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject) => {

			if (this.maxFileSize !== null && file.getSize() > this.maxFileSize)
			{
				reject(
					new UploaderError(
						'MAX_FILE_SIZE_EXCEEDED',
						{
							maxFileSize: formatFileSize(this.maxFileSize),
							maxFileSizeInBytes: this.maxFileSize,
						},
					),
				);

				return;
			}

			if (this.minFileSize !== null && file.getSize() < this.minFileSize)
			{
				reject(
					new UploaderError(
						'MIN_FILE_SIZE_EXCEEDED',
						{
							minFileSize: formatFileSize(this.minFileSize),
							minFileSizeInBytes: this.minFileSize,
						},
					),
				);

				return;
			}

			if (file.isImage())
			{
				if (this.imageMaxFileSize !== null && file.getSize() > this.imageMaxFileSize)
				{
					reject(
						new UploaderError(
							'IMAGE_MAX_FILE_SIZE_EXCEEDED',
							{
								imageMaxFileSize: formatFileSize(this.imageMaxFileSize),
								imageMaxFileSizeInBytes: this.imageMaxFileSize,
							},
						),
					);

					return;
				}

				if (this.imageMinFileSize !== null && file.getSize() < this.imageMinFileSize)
				{
					reject(
						new UploaderError(
							'IMAGE_MIN_FILE_SIZE_EXCEEDED',
							{
								imageMinFileSize: formatFileSize(this.imageMinFileSize),
								imageMinFileSizeInBytes: this.imageMinFileSize,
							},
						),
					);

					return;
				}
			}

			if (this.maxTotalFileSize !== null)
			{
				if (this.getUploader().getTotalSize() > this.maxTotalFileSize)
				{
					reject(
						new UploaderError(
							'MAX_TOTAL_FILE_SIZE_EXCEEDED',
							{
								maxTotalFileSize: formatFileSize(this.maxTotalFileSize),
								maxTotalFileSizeInBytes: this.maxTotalFileSize,
							},
						),
					);

					return;
				}
			}

			resolve();
		});

	}
}
