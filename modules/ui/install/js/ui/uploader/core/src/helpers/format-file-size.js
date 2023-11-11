import { Loc, Type } from 'main.core';

const formatFileSize = (size: number, base: number = 1024): string => {
	let i = 0;
	const units = getUnits();
	let currentSize = size;
	while (currentSize >= base && units[i + 1])
	{
		currentSize /= base;
		i++;
	}

	return (Type.isInteger(currentSize) ? currentSize : currentSize.toFixed(1)) + units[i];
};

let fileSizeUnits = null;
const getUnits = () => {
	if (fileSizeUnits !== null)
	{
		return fileSizeUnits;
	}

	const units = Loc.getMessage('UPLOADER_FILE_SIZE_POSTFIXES').split(/\|/);
	fileSizeUnits = Type.isArrayFilled(units) ? units : ['B', 'kB', 'MB', 'GB', 'TB'];

	return fileSizeUnits;
};

export default formatFileSize;
