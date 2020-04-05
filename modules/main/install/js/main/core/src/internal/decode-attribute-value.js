import Type from '../lib/type';
import Text from '../lib/text';

export default function decodeAttributeValue(value: any)
{
	if (Type.isString(value))
	{
		const decodedValue = Text.decode(value);
		let result;

		try
		{
			result = JSON.parse(decodedValue);
		}
		catch (e)
		{
			result = decodedValue;
		}

		if (result === decodedValue)
		{
			if (/^[\d.]+[.]?\d+$/.test(result))
			{
				return Number(result);
			}
		}

		if (result === 'true' || result === 'false')
		{
			return Boolean(result);
		}

		return result;
	}

	return value;
}