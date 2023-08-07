import { Extension, Type } from 'main.core';
import Filter from './filter';
import UploaderError from '../uploader-error';
import formatFileSize from '../helpers/format-file-size';
import isResizableImage from '../helpers/is-resizable-image';

import type Uploader from '../uploader';
import type UploaderFile from '../uploader-file';
import type { UploaderOptions } from '../types/uploader-options';

export default class FileSizeFilter extends Filter
{
	#maxFileSize: ?number = 256 * 1024 * 1024;
	#minFileSize: number = 0;
	#maxTotalFileSize: ?number = null;
	#imageMaxFileSize: ?number = 48 * 1024 * 1024;
	#imageMinFileSize: number = 0;
	#treatOversizeImageAsFile: boolean = false;

	constructor(uploader: Uploader, filterOptions: UploaderOptions = {})
	{
		super(uploader);

		const settings = Extension.getSettings('ui.uploader.core');
		this.#maxFileSize = settings.get('maxFileSize', this.#maxFileSize);
		this.#minFileSize = settings.get('minFileSize', this.#minFileSize);
		this.#maxTotalFileSize = settings.get('maxTotalFileSize', this.#maxTotalFileSize);
		this.#imageMaxFileSize = settings.get('imageMaxFileSize', this.#imageMaxFileSize);
		this.#imageMinFileSize = settings.get('imageMinFileSize', this.#imageMinFileSize);

		const options: UploaderOptions = Type.isPlainObject(filterOptions) ? filterOptions : {};
		this.setMaxFileSize(options.maxFileSize);
		this.setMinFileSize(options.minFileSize);
		this.setMaxTotalFileSize(options.maxTotalFileSize);
		this.setImageMaxFileSize(options.imageMaxFileSize);
		this.setImageMinFileSize(options.imageMinFileSize);
		this.setTreatOversizeImageAsFile(options.treatOversizeImageAsFile);
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject): void => {
			if (this.getMaxFileSize() !== null && file.getSize() > this.getMaxFileSize())
			{
				reject(
					new UploaderError(
						'MAX_FILE_SIZE_EXCEEDED',
						{
							maxFileSize: formatFileSize(this.getMaxFileSize()),
							maxFileSizeInBytes: this.getMaxFileSize(),
						},
					),
				);

				return;
			}

			if (file.getSize() < this.getMinFileSize())
			{
				reject(
					new UploaderError(
						'MIN_FILE_SIZE_EXCEEDED',
						{
							minFileSize: formatFileSize(this.getMinFileSize()),
							minFileSizeInBytes: this.getMinFileSize(),
						},
					),
				);

				return;
			}

			if (isResizableImage(file.getName(), file.getType()))
			{
				if (this.getImageMaxFileSize() !== null && file.getSize() > this.getImageMaxFileSize())
				{
					if (this.shouldTreatOversizeImageAsFile())
					{
						file.setTreatImageAsFile(true);
					}
					else
					{
						reject(
							new UploaderError(
								'IMAGE_MAX_FILE_SIZE_EXCEEDED',
								{
									imageMaxFileSize: formatFileSize(this.getImageMaxFileSize()),
									imageMaxFileSizeInBytes: this.getImageMaxFileSize(),
								},
							),
						);

						return;
					}
				}

				if (file.getSize() < this.getImageMinFileSize())
				{
					if (this.shouldTreatOversizeImageAsFile())
					{
						file.setTreatImageAsFile(true);
					}
					else
					{
						reject(
							new UploaderError(
								'IMAGE_MIN_FILE_SIZE_EXCEEDED',
								{
									imageMinFileSize: formatFileSize(this.getImageMinFileSize()),
									imageMinFileSizeInBytes: this.getImageMinFileSize(),
								},
							),
						);

						return;
					}
				}
			}

			if (this.getMaxTotalFileSize() !== null && this.getUploader().getTotalSize() > this.getMaxTotalFileSize())
			{
				reject(
					new UploaderError(
						'MAX_TOTAL_FILE_SIZE_EXCEEDED',
						{
							maxTotalFileSize: formatFileSize(this.getMaxTotalFileSize()),
							maxTotalFileSizeInBytes: this.getMaxTotalFileSize(),
						},
					),
				);

				return;
			}

			resolve();
		});
	}

	getMaxFileSize(): ?number
	{
		return this.#maxFileSize;
	}

	setMaxFileSize(value: ?number)
	{
		if ((Type.isNumber(value) && value >= 0) || Type.isNull(value))
		{
			this.#maxFileSize = value;
		}
	}

	getMinFileSize(): number
	{
		return this.#minFileSize;
	}

	setMinFileSize(value: number)
	{
		if (Type.isNumber(value) && value >= 0)
		{
			this.#minFileSize = value;
		}
	}

	getMaxTotalFileSize(): ?number
	{
		return this.#maxTotalFileSize;
	}

	setMaxTotalFileSize(value: ?number)
	{
		if ((Type.isNumber(value) && value >= 0) || Type.isNull(value))
		{
			this.#maxTotalFileSize = value;
		}
	}

	getImageMaxFileSize(): ?number
	{
		return this.#imageMaxFileSize;
	}

	setImageMaxFileSize(value: ?number)
	{
		if ((Type.isNumber(value) && value >= 0) || Type.isNull(value))
		{
			this.#imageMaxFileSize = value;
		}
	}

	getImageMinFileSize(): number
	{
		return this.#imageMinFileSize;
	}

	setImageMinFileSize(value: number)
	{
		if (Type.isNumber(value) && value >= 0)
		{
			this.#imageMinFileSize = value;
		}
	}

	setTreatOversizeImageAsFile(value: boolean): void
	{
		if (Type.isBoolean(value))
		{
			this.#treatOversizeImageAsFile = value;
		}
	}

	shouldTreatOversizeImageAsFile(): boolean
	{
		return this.#treatOversizeImageAsFile;
	}
}
