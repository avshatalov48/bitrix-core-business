import {Type} from 'main.core';

export default function changeFileExtension(fileName: string, extension: string)
{
	if (Type.isString(fileName) && Type.isString(extension))
	{
		const index = fileName.lastIndexOf('.');

		if (index > 0)
		{
			return `${fileName.substr(0, index)}.${extension}`;
		}
	}

	return fileName;
}