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

			getArrayBuffer(file)
				.then((buffer: ArrayBuffer) => {
					const view = new DataView(buffer);

					if (!compareBuffers(view, GIF87a, 0) && !compareBuffers(view, GIF89a, 0))
					{
						reject(new Error('GIF signature not found.'));

						return;
					}

					// * a static 4-byte sequence (\x00\x21\xF9\x04)
					// * 4 variable bytes
					// * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)
					// We read through the file til we reach the end of the file, or we've found
					// at least 2 frame headers
					let frames = 0;
					for (let i = 0, len = view.byteLength - 9; i < len && frames < 2; i++)
					{
						if (
							view.getUint8(i) === 0x00
							&& view.getUint8(i + 1) === 0x21
							&& view.getUint8(i + 2) === 0xF9
							&& view.getUint8(i + 3) === 0x04
							&& view.getUint8(i + 8) === 0x00
							&& (view.getUint8(i + 9) === 0x2C || view.getUint8(i + 9) === 0x21)
						)
						{
							frames++;
						}
					}

					const animated = frames > 1;

					resolve({
						width: view.getUint16(6, true),
						height: view.getUint16(8, true),
						animated,
					});
				})
				.catch((error) => {
					reject(error);
				})
			;
		});
	}
}
