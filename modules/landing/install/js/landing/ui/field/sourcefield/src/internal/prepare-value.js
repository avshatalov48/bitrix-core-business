import {Type} from 'main.core';
import type {SourceItem} from './prepare-sources';

type Value = {
	source: string,
	filter: Array<{key: string, name: string, value: any}>,
	sort: {
		by: string,
		order: 'DESC' | 'ASC',
	},
};

function prepareFilter(filter, source)
{
	return filter.reduce((acc, field) => {
		if (Type.isPlainObject(field))
		{
			return [...acc, {...field, url: source.url}];
		}

		return acc;
	}, []);
}

export default function prepareValue(value: any, sources: Array<SourceItem>): Value
{
	const [firstSource] = sources;

	if (!Type.isPlainObject(value))
	{
		return {
			source: firstSource.value,
			filter: prepareFilter([...firstSource.filter], firstSource),
			sort: {
				by: firstSource.sort.items[0].key,
				order: 'DESC',
			},
		};
	}

	const source = sources.find((item) => {
		return item.value === value.source;
	});

	if (
		!Type.isArray(value.filter)
		|| value.filter.length <= 0
	)
	{
		if (source)
		{
			value.filter = [...source.filter];
		}
	}

	value.filter = prepareFilter(value.filter, source);

	if (!Type.isPlainObject(value.sort))
	{
		value.sort = {};
	}

	if (!Type.isString(value.sort.by))
	{
		if (source)
		{
			value.sort.by = source.sort.items[0].value;
		}
	}

	if (!Type.isString(value.sort.order))
	{
		value.sort.order = 'DESC';
	}

	return value;
}