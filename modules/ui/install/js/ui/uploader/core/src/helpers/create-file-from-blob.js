import { Type } from 'main.core';
import getExtensionFromType from './get-extension-from-type';

let counter = 0;
const createFileFromBlob = (blob: Blob, fileName: string): File => {

	if (!Type.isStringFilled(fileName))
	{
		const date = new Date();
		fileName = `File ${date.getFullYear()}-${date.getMonth()}-${date.getDate()}-${++counter}`;

		const extension = getExtensionFromType(blob.type);
		if (extension)
		{
			fileName += `.${extension}`;
		}
	}

	try
	{
		return new File(
			[blob],
			fileName,
			{
				lastModified: Date.now(),
				lastModifiedDate: new Date(),
				type: blob.type
			}
		);
	}
	catch (exception)
	{
		const file = blob.slice(0, blob.size, blob.type);
		file.name = fileName;
		file.lastModified = Date.now();
		file.lastModifiedDate = new Date();

		return file;
	}
};

export default createFileFromBlob;