import getArrayBuffer from '../../get-array-buffer';
import type { ImageSize } from '../image-size-type';

const BMP_SIGNATURE = 0x424D; // BM

export default class Bmp
{
	getSize(file: File): ?ImageSize
	{
		return new Promise((resolve, reject) => {
			if (file.size < 26)
			{
				reject(new Error('BMP signature not found.'));

				return;
			}

			const blob = file.slice(0, 26);
			getArrayBuffer(blob)
				.then((buffer: ArrayBuffer): void => {
					const view = new DataView(buffer);
					if (!view.getUint16(0) === BMP_SIGNATURE)
					{
						reject(new Error('BMP signature not found.'));

						return;
					}

					resolve({
						width: view.getUint32(18, true),
						height: Math.abs(view.getInt32(22, true)),
					});
				})
				.catch((error): void => {
					reject(error);
				})
			;
		});
	}
}
