import getArrayBuffer from '../../get-array-buffer';
import convertStringToBuffer from '../convert-string-to-buffer';
import compareBuffers from '../compare-buffers';

import type { ImageSize } from '../image-size-type';

const GIF87a = convertStringToBuffer('GIF87a');
const GIF89a = convertStringToBuffer('GIF89a');

export default class Gif
{
	getSize(file: File): ?ImageSize
	{
		return new Promise((resolve, reject) => {
			if (file.size < 10)
			{
				reject(new Error('GIF signature not found.'));

				return;
			}

			const blob = file.slice(0, 10);
			getArrayBuffer(blob)
				.then((buffer: ArrayBuffer) => {
					const view = new DataView(buffer);

					if (!compareBuffers(view, GIF87a, 0) && !compareBuffers(view, GIF89a, 0))
					{
						reject(new Error('GIF signature not found.'));

						return;
					}

					resolve({
						width: view.getUint16(6, true),
						height: view.getUint16(8, true),
					});
				})
				.catch((error) => {
					reject(error);
				})
			;
		});
	}
}
