import Type from '../type';

export default function merge(current, target)
{
	return Object.entries(target).reduce((acc, [key, value]) => {
		if (
			!Type.isDomNode(acc[key])
			&& Type.isObjectLike(acc[key])
			&& Type.isObjectLike(value)
		)
		{
			acc[key] = merge(acc[key], value);
			return acc;
		}

		acc[key] = value;
		return acc;
	}, current);
}