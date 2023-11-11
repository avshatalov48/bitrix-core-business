import { Type } from 'main.core';
import getFileExtension from './get-file-extension';

const imageExtensions: Set<string> = new Set(['jpg', 'bmp', 'jpeg', 'jpe', 'gif', 'png', 'webp']);

const isResizableImage = (file: File | string, mimeType: string = null): boolean => {
	const fileName: string = Type.isFile(file) ? file.name : file;
	const type: string = Type.isFile(file) ? file.type : mimeType;
	const extension: string = getFileExtension(fileName).toLowerCase();

	return imageExtensions.has(extension) && (type === null || /^image\/[\d.a-z-]+$/i.test(type));
};

export default isResizableImage;
