import { Type } from 'main.core';

const isValidFileType = (file: File, fileTypes: string[]): boolean => {
	if (!Type.isArrayFilled(fileTypes))
	{
		return true;
	}

	const mimeType = file.type;
	const baseMimeType = mimeType.replace(/\/.*$/, '');

	for (const fileType of fileTypes)
	{
		if (!Type.isStringFilled(fileType))
		{
			continue;
		}

		const type = fileType.trim().toLowerCase();

		if (type.charAt(0) === '.') // extension case
		{
			if (file.name.toLowerCase().includes(type, file.name.length - type.length))
			{
				return true;
			}
		}
		else if (/\/\*$/.test(type)) // image/* mime type case
		{
			if (baseMimeType === type.replace(/\/.*$/, ''))
			{
				return true;
			}
		}
		else if (mimeType === type)
		{
			return true;
		}
	}

	return false;
};

export default isValidFileType;
