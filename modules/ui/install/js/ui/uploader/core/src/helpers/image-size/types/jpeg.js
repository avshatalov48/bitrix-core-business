import getArrayBuffer from '../../get-array-buffer';
import convertStringToBuffer from '../convert-string-to-buffer';
import compareBuffers from '../compare-buffers';

import type { ImageSize } from '../image-size-type';

const EXIF_SIGNATURE = convertStringToBuffer('Exif\0\0');

export default class Jpeg
{
	getSize(file: File): ?ImageSize
	{
		return new Promise((resolve, reject) => {
			if (file.size < 2)
			{
				reject(new Error('JPEG signature not found.'));

				return;
			}

			getArrayBuffer(file)
				.then((buffer: ArrayBuffer) => {
					const view = new DataView(buffer);
					if (view.getUint8(0) !== 0xFF || view.getUint8(1) !== 0xD8)
					{
						reject(new Error('JPEG signature not found.'));

						return;
					}

					let offset = 2;
					let orientation = -1;
					for (;;)
					{
						if (view.byteLength - offset < 2)
						{
							reject(new Error('JPEG signature not found.'));

							return;
						}

						if (view.getUint8(offset++) !== 0xFF)
						{
							reject(new Error('JPEG signature not found.'));

							return;
						}

						let code = view.getUint8(offset++);
						let length = 0;

						// skip padding bytes
						while (code === 0xFF)
						{
							code = view.getUint8(offset++);
						}

						if ((code >= 0xD0 && code <= 0xD9) || code === 0x01)
						{
							length = 0;
						}
						else if (code >= 0xC0 && code <= 0xFE)
						{
							// the rest of the unreserved markers
							if (view.byteLength - offset < 2)
							{
								reject(new Error('JPEG signature not found.'));

								return;
							}

							length = view.getUint16(offset) - 2;
							offset += 2;
						}
						else
						{
							reject(new Error('JPEG unknown markers.'));

							return;
						}

						if (code === 0xD9 /* EOI */ || code === 0xDA /* SOS */)
						{
							reject(new Error('JPEG end of the data stream.'));

							return;
						}

						// try to get orientation from Exif segment
						if (code === 0xE1 && length >= 10 && compareBuffers(view, EXIF_SIGNATURE, offset))
						{
							const exifBlock = new DataView(view.buffer, offset + 6, offset + length);
							orientation = getOrientation(exifBlock);
						}

						if (
							length >= 5
							&& (code >= 0xC0 && code <= 0xCF)
							&& code !== 0xC4 && code !== 0xC8 && code !== 0xCC
						)
						{
							if (view.byteLength - offset < length)
							{
								reject(new Error('JPEG size not found.'));

								return;
							}

							let width = view.getUint16(offset + 3);
							let height = view.getUint16(offset + 1);
							if (orientation >= 5 && orientation <= 8)
							{
								[width, height] = [height, width];
							}

							resolve({
								width,
								height,
								orientation,
							});

							return;
						}

						offset += length;
					}
				})
				.catch((error) => {
					reject(error);
				})
			;
		});
	}
}

const Marker = {
	BIG_ENDIAN: 0x4D4D,
	LITTLE_ENDIAN: 0x4949,
};

const getOrientation = (exifBlock: DataView): number => {
	const byteAlign = exifBlock.getUint16(0);
	const isBigEndian = byteAlign === Marker.BIG_ENDIAN;
	const isLittleEndian = byteAlign === Marker.LITTLE_ENDIAN;

	if (isBigEndian || isLittleEndian)
	{
		return extractOrientation(exifBlock, isLittleEndian);
	}

	return -1;
};

const extractOrientation = (exifBlock: DataView, littleEndian: boolean = false): number => {
	const offset = 8; // idf offset
	const idfDirectoryEntries = exifBlock.getUint16(offset, littleEndian);

	const IDF_ENTRY_BYTES = 12;
	const NUM_DIRECTORY_ENTRIES_BYTES = 2;

	for (let directoryEntryNumber = 0; directoryEntryNumber < idfDirectoryEntries; directoryEntryNumber++)
	{
		const start = offset + NUM_DIRECTORY_ENTRIES_BYTES + (directoryEntryNumber * IDF_ENTRY_BYTES);
		const end = start + IDF_ENTRY_BYTES;

		// Skip on corrupt EXIF blocks
		if (start > exifBlock.byteLength)
		{
			return -1;
		}

		const block = new DataView(exifBlock.buffer, exifBlock.byteOffset + start, end - start);
		const tagNumber = block.getUint16(0, littleEndian);

		// 274 is the `orientation` tag ID
		if (tagNumber === 274)
		{
			const dataFormat = block.getUint16(2, littleEndian);
			if (dataFormat !== 3)
			{
				return -1;
			}

			const numberOfComponents = block.getUint32(4, littleEndian);
			if (numberOfComponents !== 1)
			{
				return -1;
			}

			return block.getUint16(8, littleEndian);
		}
	}

	return -1;
};
