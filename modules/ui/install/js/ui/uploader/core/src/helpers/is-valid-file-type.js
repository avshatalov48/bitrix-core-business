import { Type } from 'main.core';

const isValidFileType = (file: File, fileTypes: string[]): boolean => {
	if (!Type.isArrayFilled(fileTypes))
	{
		return true;
	}

	const mimeType = file.type;
	const baseMimeType = mimeType.replace(/\/.*$/, '');

	for (let i = 0; i < fileTypes.length; i++)
	{
		if (!Type.isStringFilled(fileTypes[i]))
		{
			continue;
		}

		const type = fileTypes[i].trim().toLowerCase();

		if (type.charAt(0) === '.') // extension case
		{
			if (file.name.toLowerCase().indexOf(type, file.name.length - type.length) !== -1)
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