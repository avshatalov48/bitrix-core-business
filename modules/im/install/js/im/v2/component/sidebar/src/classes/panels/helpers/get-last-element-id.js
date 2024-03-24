import { Type } from 'main.core';

export function getLastElementId(collection: {id: number}[], sort: 'ASC' | 'DESC' = 'ASC'): ?number
{
	if (collection.length === 0)
	{
		return null;
	}

	collection.sort((a, b) => {
		if (sort === 'ASC')
		{
			return a.id - b.id;
		}

		return b.id - a.id;
	});

	const [lastCollectionItem] = collection;

	if (Type.isNumber(lastCollectionItem.id))
	{
		return lastCollectionItem.id;
	}

	return null;
}
