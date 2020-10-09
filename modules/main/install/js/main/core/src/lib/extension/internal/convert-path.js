import Type from '../../type';

export default function convertPath(path: string): Array<string>
{
	if (Type.isStringFilled(path))
	{
		return path
			.split('.')
			.reduce((acc, item) => {
				item
					.split(/\[['"]?(.+?)['"]?\]/g)
					.forEach((key) => {
						if (Type.isStringFilled(key))
						{
							acc.push(key);
						}
					});

				return acc;
			}, []);
	}

	return [];
}