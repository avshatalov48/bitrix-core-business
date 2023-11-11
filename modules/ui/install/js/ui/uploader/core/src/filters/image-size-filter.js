import { Extension, Type } from 'main.core';

import Filter from './filter';
import UploaderError from '../uploader-error';
import getImageSize from '../helpers/image-size/get-image-size';
import isResizableImage from '../helpers/is-resizable-image';

import type Uploader from '../uploader';
import type UploaderFile from '../uploader-file';
import type { UploaderOptions } from '../types/uploader-options';

export default class ImageSizeFilter extends Filter
{
	#imageMinWidth: number = 1;
	#imageMinHeight: number = 1;
	#imageMaxWidth: number = 7000;
	#imageMaxHeight: number = 7000;
	#ignoreUnknownImageTypes: boolean = false;
	#treatOversizeImageAsFile: boolean = false;

	constructor(uploader: Uploader, filterOptions: UploaderOptions = {})
	{
		super(uploader);

		const settings = Extension.getSettings('ui.uploader.core');
		this.#imageMinWidth = settings.get('imageMinWidth', this.#imageMinWidth);
		this.#imageMinHeight = settings.get('imageMinHeight', this.#imageMinHeight);
		this.#imageMaxWidth = settings.get('imageMaxWidth', this.#imageMaxWidth);
		this.#imageMaxHeight = settings.get('imageMaxHeight', this.#imageMaxHeight);

		const options: UploaderOptions = Type.isPlainObject(filterOptions) ? filterOptions : {};

		this.setImageMinWidth(options.imageMinWidth);
		this.setImageMinHeight(options.imageMinHeight);
		this.setImageMaxWidth(options.imageMaxWidth);
		this.setImageMaxHeight(options.imageMaxHeight);
		this.setIgnoreUnknownImageTypes(options.ignoreUnknownImageTypes);
		this.setTreatOversizeImageAsFile(options.treatOversizeImageAsFile);
	}

	apply(file: UploaderFile): Promise
	{
		return new Promise((resolve, reject): void => {
			if (!isResizableImage(file.getName(), file.getType()))
			{
				resolve();

				return;
			}

			getImageSize(file.getBinary())
				.then(({ width, height }): void => {
					file.setWidth(width);
					file.setHeight(height);

					if (width < this.getImageMinWidth() || height < this.getImageMinHeight())
					{
						if (this.shouldTreatOversizeImageAsFile())
						{
							file.setTreatImageAsFile(true);
							resolve();
						}
						else
						{
							reject(new UploaderError(
								'IMAGE_IS_TOO_SMALL',
								{
									minWidth: this.getImageMinWidth(),
									minHeight: this.getImageMinHeight(),
								},
							));
						}
					}
					else if (width > this.getImageMaxWidth() || height > this.getImageMaxHeight())
					{
						if (this.shouldTreatOversizeImageAsFile())
						{
							file.setTreatImageAsFile(true);
							resolve();
						}
						else
						{
							reject(new UploaderError(
								'IMAGE_IS_TOO_BIG',
								{
									maxWidth: this.getImageMaxWidth(),
									maxHeight: this.getImageMaxHeight(),
								},
							));
						}
					}
					else
					{
						resolve();
					}
				})
				.catch((error): void => {
					if (this.getIgnoreUnknownImageTypes())
					{
						file.setTreatImageAsFile(true);
						resolve();
					}
					else
					{
						if (error)
						{
							// eslint-disable-next-line no-console
							console.warn('Uploader ImageSizeFilter:', error);
						}

						reject(new UploaderError('IMAGE_TYPE_NOT_SUPPORTED'));
					}
				})
			;
		});
	}

	getImageMinWidth(): number
	{
		return this.#imageMinWidth;
	}

	setImageMinWidth(value: number): void
	{
		if (Type.isNumber(value) && value > 0)
		{
			this.#imageMinWidth = value;
		}
	}

	getImageMinHeight(): number
	{
		return this.#imageMinHeight;
	}

	setImageMinHeight(value: number): void
	{
		if (Type.isNumber(value) && value > 0)
		{
			this.#imageMinHeight = value;
		}
	}

	getImageMaxWidth(): number
	{
		return this.#imageMaxWidth;
	}

	setImageMaxWidth(value: number): void
	{
		if (Type.isNumber(value) && value > 0)
		{
			this.#imageMaxWidth = value;
		}
	}

	getImageMaxHeight(): number
	{
		return this.#imageMaxHeight;
	}

	setImageMaxHeight(value: number): void
	{
		if (Type.isNumber(value) && value > 0)
		{
			this.#imageMaxHeight = value;
		}
	}

	getIgnoreUnknownImageTypes(): boolean
	{
		return this.#ignoreUnknownImageTypes;
	}

	setIgnoreUnknownImageTypes(value: boolean): void
	{
		if (Type.isBoolean(value))
		{
			this.#ignoreUnknownImageTypes = value;
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
