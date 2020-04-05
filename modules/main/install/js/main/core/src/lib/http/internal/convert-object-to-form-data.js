import Type from '../../type';

export default function objectToFormData(
	source: {[key: string]: any},
	formData: FormData = new FormData(),
	pre = null,
)
{
	if (Type.isUndefined(source))
	{
		return formData;
	}

	if (Type.isNull(source))
	{
		formData.append(pre, '');
	}
	else if (Type.isArray(source))
	{
		if (!source.length)
		{
			const key = `${pre}[]`;
			formData.append(key, '');
		}
		else
		{
			source.forEach((value, index) => {
				const key = `${pre}[${index}]`;
				objectToFormData(value, formData, key);
			});
		}
	}
	else if (Type.isDate(source))
	{
		formData.append(pre, source.toISOString());
	}
	else if (Type.isObject(source) && !Type.isFile(source) && !Type.isBlob(source))
	{
		Object.keys(source).forEach((property) => {
			const value = source[property];
			let preparedProperty = property;

			if (Type.isArray(value))
			{
				while (property.length > 2 && property.lastIndexOf('[]') === property.length - 2)
				{
					preparedProperty = property.substring(0, property.length - 2);
				}
			}

			const key = pre ? `${pre}[${preparedProperty}]` : preparedProperty;
			objectToFormData(value, formData, key);
		});
	}
	else
	{
		formData.append(pre, source);
	}

	return formData;
}