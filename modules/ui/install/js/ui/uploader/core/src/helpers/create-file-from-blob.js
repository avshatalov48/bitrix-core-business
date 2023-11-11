import { Type } from 'main.core';
import getExtensionFromType from './get-extension-from-type';

let counter = 0;
const createFileFromBlob = (blob: Blob, fileName: string): File => {
	let newFileName = fileName;
	if (!Type.isStringFilled(newFileName))
	{
		const date = new Date();
		newFileName = `File ${date.getFullYear()}-${date.getMonth()}-${date.getDate()}-${++counter}`;

		const extension = getExtensionFromType(blob.type);
		if (extension)
		{
			newFileName += `.${extension}`;
		}
	}

	try
	{
		return new File(
			[blob],
			newFileName,
			{
				lastModified: Date.now(),
				lastModifiedDate: new Date(),
				type: blob.type,
			},
		);
	}
	catch
	{
		const file = blob.slice(0, blob.size, blob.type);
		file.name = newFileName;
		file.lastModified = Date.now();
		file.lastModifiedDate = new Date();

		return file;
	}
};

export default createFileFromBlob;
