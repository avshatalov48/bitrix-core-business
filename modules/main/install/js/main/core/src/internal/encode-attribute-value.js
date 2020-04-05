import Type from '../lib/type';
import Text from '../lib/text';

export default function encodeAttributeValue(value: any)
{
	if (Type.isPlainObject(value) || Type.isArray(value))
	{
		return JSON.stringify(value);
	}

	return Text.encode(Text.decode(value));
}