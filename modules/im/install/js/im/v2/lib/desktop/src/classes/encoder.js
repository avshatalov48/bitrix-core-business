import {Type} from 'main.core';

const ENCODE_SEPARATOR = '!!';

export const Encoder = {
	encodeParams(params: {[key]: any}): string
	{
		if (!Type.isPlainObject(params))
		{
			return '';
		}

		let result = '';
		const first = true;

		Object.entries(params).forEach(([key, value]) => {
			const prefix = first ? '' : ENCODE_SEPARATOR;
			result += `${prefix}${key}${ENCODE_SEPARATOR}${value}`;
		});

		return result;
	},

	decodeParams(encodedParams: string): {[key]: any}
	{
		const result = {};
		if (!Type.isStringFilled(encodedParams))
		{
			return result;
		}

		const chunks = encodedParams.split(ENCODE_SEPARATOR);
		for (let i = 0; i < chunks.length; i += 2)
		{
			const key = chunks[i];
			const value = chunks[i+1];
			result[key] = value;
		}

		return result;
	},

	encodeParamsJson(params): string
	{
		if (!Type.isPlainObject(params))
		{
			return '{}';
		}

		let result = '';
		try
		{
			result = encodeURIComponent(JSON.stringify(params));
		}
		catch (error)
		{
			console.error('DesktopUtils: could not encode params.', error);
			result = '{}';
		}

		return result;
	},

	decodeParamsJson(encodedParams: string): {[key]: any}
	{
		let result = {};
		if (!Type.isStringFilled(encodedParams))
		{
			return result;
		}

		try
		{
			result = JSON.parse(decodeURIComponent(encodedParams));
		}
		catch (error)
		{
			console.error('DesktopUtils: could not decode encoded params.', error);
		}

		return result;
	}
};