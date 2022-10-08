import { Type } from 'main.core';

const getExtensionFromType = (type) => {
	if (!Type.isStringFilled(type))
	{
		return '';
	}

	const subtype = type.split('/').pop();

	if (/javascript/.test(subtype))
	{
		return 'js';
	}

	if (/plain/.test(subtype))
	{
		return 'txt';
	}

	if (/svg/.test(subtype))
	{
		return 'svg';
	}

	if (/[a-z]+/.test(subtype))
	{
		return subtype;
	}

	return '';
};

export default getExtensionFromType;