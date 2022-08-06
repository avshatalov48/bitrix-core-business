import {Type} from 'main.core';
import Compressor from 'compressorjs/src/index';
import allowedSizeProps from './internal/allowed-size-props';
import urlToBlob from './internal/url-to-blob';
import type {ImageCompressorOptions} from './types';

export class ImageCompressor
{
	static maxOriginalPngSize = 5 * 1024 * 1024;

	constructor(file, options: ImageCompressorOptions = {})
	{
		this.file = file;
		this.options = {quality: 0.8, ...options};

		if (this.options.retina)
		{
			allowedSizeProps.forEach((prop) => {
				if (Type.isNumber(this.options[prop]))
				{
					this.options[prop] *= 2;
				}
			});
		}
	}

	static compress(file, options: ImageCompressorOptions = {}): Promise<File>
	{
		return urlToBlob(file)
			.then((blob) => {
				if (Type.isStringFilled(blob.type))
				{
					if (
						blob.type.includes('gif')
						|| (
							blob.type.includes('png')
							&& blob.size < ImageCompressor.maxOriginalPngSize
						)
					)
					{
						return blob;
					}
				}

				const compressor = new ImageCompressor(blob, options);
				return compressor.compress();
			});
	}

	compress(): Promise<File>
	{
		return new Promise((resolve, reject) => {
			void new Compressor(
				this.file,
				{...this.options, ...{success: resolve, error: reject}},
			);
		});
	}
}