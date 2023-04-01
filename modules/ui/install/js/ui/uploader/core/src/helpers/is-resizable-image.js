import { Type } from 'main.core';
import getFileExtension from './get-file-extension';

const imageExtensions = ['jpg', 'bmp', 'jpeg', 'jpe', 'gif', 'png', 'webp'];

const isResizableImage = (file: File | string, mimeType: string = null) => {
	const fileName = Type.isFile(file) ? file.name : file;
	const type = Type.isFile(file) ? file.type : mimeType;
	const extension = getFileExtension(fileName).toLowerCase();

	if (imageExtensions.includes(extension))
	{
		if (type === null || /^image\/[a-z0-9.-]+$/i.test(type))
		{
			return true;
		}
	}

	return false;
};

export default isResizableImage;
