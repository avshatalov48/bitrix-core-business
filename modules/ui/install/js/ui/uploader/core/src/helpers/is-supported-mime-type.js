import { Browser } from 'main.core';

const supportedMimeTypes: string[] = (
	Browser.isSafari()
		? ['image/jpeg', 'image/png']
		: ['image/jpeg', 'image/png', 'image/webp']
);

const isSupportedMimeType = (mimeType: string): boolean => {
	return supportedMimeTypes.includes(mimeType);
};

export default isSupportedMimeType;
