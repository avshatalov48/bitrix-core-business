import getArrayBuffer from '../../get-array-buffer';
import convertStringToBuffer from '../convert-string-to-buffer';
import compareBuffers from '../compare-buffers';
import type { ImageSize } from '../image-size-type';

const PNG_SIGNATURE = convertStringToBuffer('\x89PNG\r\n\x1a\n');
const IHDR_SIGNATURE = convertStringToBuffer('IHDR');
const FRIED_CHUNK_NAME = convertStringToBuffer('CgBI');

export default class Png
{
	getSize(file: File): ?ImageSize
	{
		return new Promise((resolve, reject) => {
			if (file.size < 40)
			{
				return reject(new Error('PNG signature not found.'));
			}

			const blob = file.slice(0, 40);
			getArrayBuffer(blob)
				.then((buffer: ArrayBuffer) => {
					const view = new DataView(buffer);

					if (!compareBuffers(view, PNG_SIGNATURE, 0))
					{
						return reject(new Error('PNG signature not found.'));
					}

					if (compareBuffers(view, FRIED_CHUNK_NAME, 12))
					{
						if (compareBuffers(view, IHDR_SIGNATURE, 28))
						{
							resolve({
								width: view.getUint32(32),
								height: view.getUint32(36),
							});
						}
						else
						{
							return reject(new Error('PNG IHDR not found.'));
						}
					}
					else if (compareBuffers(view, IHDR_SIGNATURE, 12))
					{
						resolve({
							width: view.getUint32(16),
							height: view.getUint32(20),
						});
					}
					else
					{
						return reject(new Error('PNG IHDR not found.'));
					}
				})
				.catch(error => {
					return reject(error);
				})
			;
		});
	}
};
