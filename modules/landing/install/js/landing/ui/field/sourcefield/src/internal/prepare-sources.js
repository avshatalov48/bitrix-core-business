import {Type, Runtime} from 'main.core';
import getFilterStub from './filter-stub';

export type SourceItem = {
	name: string,
	value: string,
	filter?: Array<{key: string, name: string, value: any}>,
	sort?: {
		items: Array<{name: string, value: string}>,
	},
	url?: string,
	settings?: {
		detailPage?: boolean,
	},
};

export default function prepareSources(sources: Array<SourceItem>, stubText = '')
{
	if (Type.isArray(sources))
	{
		return sources.reduce((acc, item) => {
			if (
				Type.isPlainObject(item)
				&& Type.isString(item.name)
				&& Type.isString(item.value)
			)
			{
				const source = Runtime.clone(item);

				if (
					!Type.isArray(source.filter)
					|| source.filter.length <= 0
				)
				{
					source.filter = [Runtime.clone(getFilterStub(stubText))];
				}

				if (
					!Type.isPlainObject(source.sort)
					|| !Type.isArray(source.sort.items)
				)
				{
					source.sort = {items: []};
				}

				return [...acc, source];
			}

			return acc;
		}, []);
	}

	return [];
}