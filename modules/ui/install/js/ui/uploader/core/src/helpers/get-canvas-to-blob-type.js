import isSupportedMimeType from './is-supported-mime-type';

import type { ResizeImageOptions, ResizeImageMimeTypeMode, ResizeImageMimeType } from '../types/resize-image-options';

const getCanvasToBlobType = (blob: Blob, options: ResizeImageOptions): string => {
	const mimeType: ResizeImageMimeType = isSupportedMimeType(options.mimeType) ? options.mimeType : 'image/jpeg';
	const mimeTypeMode: ResizeImageMimeTypeMode = options.mimeTypeMode;
	if (mimeTypeMode === 'force')
	{
		return mimeType;
	}

	return isSupportedMimeType(blob.type) ? blob.type : mimeType;
};

export default getCanvasToBlobType;
