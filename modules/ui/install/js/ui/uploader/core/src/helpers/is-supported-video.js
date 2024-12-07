import { Type } from 'main.core';
import getFileExtension from './get-file-extension';
import Uploader from '../uploader';

let videoExtensions = null;

const isSupportedVideo = (file: File | string, mimeType: string = null): boolean => {
	if (videoExtensions === null)
	{
		videoExtensions = Uploader.getVideoExtensions();
	}

	const fileName: string = Type.isFile(file) ? file.name : file;
	const type: string = Type.isFile(file) ? file.type : mimeType;
	const extension: string = getFileExtension(fileName).toLowerCase();

	return videoExtensions.includes(extension) && (type === null || /^video\/[\d.a-z-]+$/i.test(type));
};

export default isSupportedVideo;
