import Type from '../type';

export function renderParam(param, value): string
{
	if (Type.isNil(value))
	{
		return param;
	}

	return `${param}=${value}`;
}
