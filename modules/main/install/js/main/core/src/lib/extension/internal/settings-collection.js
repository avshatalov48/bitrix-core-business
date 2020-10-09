import Type from '../../type';
import convertPath from './convert-path';

export default class SettingsCollection
{
	constructor(options: {[key: string]: any} = {})
	{
		if (Type.isPlainObject(options))
		{
			Object.assign(this, options);
		}
	}

	get(path: string, defaultValue: any = null)
	{
		const convertedPath = convertPath(path);

		return convertedPath.reduce((acc, key) => {
			if (!Type.isNil(acc) && acc !== defaultValue)
			{
				if (!Type.isUndefined(acc[key]))
				{
					return acc[key];
				}

				return defaultValue;
			}

			return acc;
		}, this);
	}
}