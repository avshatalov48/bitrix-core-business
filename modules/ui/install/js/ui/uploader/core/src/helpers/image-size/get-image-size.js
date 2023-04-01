import Gif from './types/gif';
import Png from './types/png';
import Bmp from './types/bmp';
import Jpeg from './types/jpeg';
import Webp from './types/webp';

import getFileExtension from '../get-file-extension';
import type { ImageSize } from './image-size-type';

const jpg = new Jpeg();
const typeHandlers = {
	gif: new Gif(),
	png: new Png(),
	bmp: new Bmp(),
	jpg: jpg,
	jpeg: jpg,
	jpe: jpg,
	webp: new Webp()
};

const getImageSize = (file: File): Promise<ImageSize> => {
	if (file.size === 0)
	{
		return Promise.reject(new Error('Unknown image type.'));
	}

	const extension = getFileExtension(file.name).toLowerCase();
	const type = file.type.replace(/^image\//, '');
	const typeHandler = typeHandlers[extension] || typeHandlers[type];
	if (!typeHandler)
	{
		return Promise.reject(new Error('Unknown image type.'));
	}

	return typeHandler.getSize(file);
};

export default getImageSize;

