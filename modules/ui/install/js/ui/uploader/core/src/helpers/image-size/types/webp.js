import getArrayBuffer from '../../get-array-buffer';
import type { ImageSize } from '../image-size-type';

const RIFF_HEADER = 0x52494646; // RIFF
const WEBP_SIGNATURE = 0x57454250; // WEBP
const VP8_SIGNATURE = 0x56503820; // VP8
const VP8L_SIGNATURE = 0x5650384C; // VP8L
const VP8X_SIGNATURE = 0x56503858; // VP8X

export default class Webp
{
	getSize(file: File): Promise<?ImageSize>
	{
		return new Promise((resolve, reject) => {
			if (file.size < 16)
			{
				reject(new Error('WEBP signature not found.'));

				return;
			}

			const blob = file.slice(0, 30);
			getArrayBuffer(blob)
				.then((buffer: ArrayBuffer) => {
					const view = new DataView(buffer);
					if (view.getUint32(0) !== RIFF_HEADER && view.getUint32(8) !== WEBP_SIGNATURE)
					{
						reject(new Error('WEBP signature not found.'));

						return;
					}

					const headerType = view.getUint32(12);
					const headerView = new DataView(buffer, 20, 10);
					if (headerType === VP8_SIGNATURE && headerView.getUint8(0) !== 0x2F)
					{
						resolve({
							width: headerView.getUint16(6, true) & 0x3FFF,
							height: headerView.getUint16(8, true) & 0x3FFF,
						});

						return;
					}

					if (headerType === VP8L_SIGNATURE && headerView.getUint8(0) === 0x2F)
					{
						const bits = headerView.getUint32(1, true);

						resolve({
							width: (bits & 0x3FFF) + 1,
							height: ((bits >> 14) & 0x3FFF) + 1,
						});

						return;
					}

					if (headerType === VP8X_SIGNATURE)
					{
						const extendedHeader = headerView.getUint8(0);
						const validStart = (extendedHeader & 0xC0) === 0;
						const validEnd = (extendedHeader & 0x01) === 0;
						if (validStart && validEnd)
						{
							const width = 1 + (
								(headerView.getUint8(6) << 16)
								| (headerView.getUint8(5) << 8)
								| headerView.getUint8(4)
							);

							const height = 1 + (
								(Math.trunc(headerView.getUint8(9)))
								| (headerView.getUint8(8) << 8)
								| headerView.getUint8(7)
							);

							resolve({ width, height });

							return;
						}
					}

					reject(new Error('WEBP signature not found.'));
				})
				.catch((error) => {
					reject(error);
				})
			;
		});
	}
}
