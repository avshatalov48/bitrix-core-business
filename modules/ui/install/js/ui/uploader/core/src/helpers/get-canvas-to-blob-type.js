import { Browser } from 'main.core';

const supportedMimeTypes =
	Browser.isSafari()
		? ['image/jpeg', 'image/png']
		: ['image/jpeg', 'image/png', 'image/webp']
;

const getCanvasToBlobType = (blob: Blob, mimeType = 'image/jpeg', mimeTypeMode = 'auto'): string => {

	mimeType = supportedMimeTypes.includes(mimeType) ? mimeType : 'image/jpeg';
	if (mimeTypeMode === 'force')
	{
		return mimeType;
	}
	else
	{
		return supportedMimeTypes.includes(blob.type) ? blob.type : mimeType;
	}
};

export default getCanvasToBlobType;