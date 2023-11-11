import getFilenameWithoutExtension from './get-filename-without-extension';

const extensionMap = {
	jpeg: 'jpg',
};

const renameFileToMatchMimeType = (filename, mimeType) => {
	const name = getFilenameWithoutExtension(filename);
	const type = mimeType.split('/')[1];
	const extension = extensionMap[type] || type;

	return `${name}.${extension}`;
};

export default renameFileToMatchMimeType;
