const Marker = {
	JPEG: 0xffd8,
	APP1: 0xffe1,
	EXIF: 0x45786966,
	TIFF: 0x4949,
	Orientation: 0x0112,
	Unknown: 0xff00
};

const getUint16 = (view, offset, little = false) => view.getUint16(offset, little);
const getUint32 = (view, offset, little = false) => view.getUint32(offset, little);

const getJpegOrientation = file => {
	return new Promise((resolve, reject) => {
		const reader = new FileReader();
		reader.onload = function(e) {
			const view = new DataView(e.target.result);
			if (getUint16(view, 0) !== Marker.JPEG)
			{
				resolve(-1);
				return;
			}

			const length = view.byteLength;
			let offset = 2;

			while (offset < length)
			{
				const marker = getUint16(view, offset);
				offset += 2;

				// APP1 Marker
				if (marker === Marker.APP1)
				{
					if (getUint32(view, (offset += 2)) !== Marker.EXIF)
					{
						// no EXIF
						break;
					}

					const little = getUint16(view, (offset += 6)) === Marker.TIFF;
					offset += getUint32(view, offset + 4, little);

					const tags = getUint16(view, offset, little);
					offset += 2;

					for (let i = 0; i < tags; i++)
					{
						// Found the orientation tag
						if (getUint16(view, offset + i * 12, little) === Marker.Orientation)
						{
							resolve(getUint16(view, offset + i * 12 + 8, little));

							return;
						}
					}

				}
				else if ((marker & Marker.Unknown) !== Marker.Unknown)
				{
					break; // Invalid
				}
				else
				{
					offset += getUint16(view, offset);
				}
			}

			// Nothing found
			resolve(-1);
		};

		reader.readAsArrayBuffer(file.slice(0, 64 * 1024));
	});
};

export default getJpegOrientation;