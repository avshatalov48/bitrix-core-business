import { Type, Text } from 'main.core';
import type ItemNode from './item-node';

export default class ItemNodeComparator
{
	static makeMultipleComparator(order: {[key: string]: 'asc' | 'desc'})
	{
		const props = Object.keys(order).map(property => `get${Text.capitalize(property)}`);

		/*
		asc *
		asc nulls last *
		asc nulls first

		desc *
		desc nulls first *
		desc nulls last
		*/
		const directions: Array<{ ascOrdering: boolean, nullsOrdering: boolean }> = [];

		Object.values(order).forEach((element) => {

			const direction = element.toLowerCase().trim();

			// Default sorting: 'asc' || 'asc nulls last'
			let ascOrdering = true;
			let nullsOrdering = true;

			if (direction === 'desc' || direction === 'desc nulls first')
			{
				ascOrdering = false;
			}
			else if (direction === 'asc nulls first')
			{
				nullsOrdering = false;
			}
			else if (direction === 'desc nulls last')
			{
				ascOrdering = false;
				nullsOrdering = false;
			}

			directions.push({ ascOrdering, nullsOrdering });
		});

		const numberOfProperties = props.length;

		return (nodeA: ItemNode, nodeB : ItemNode) => {
			let i = 0;
			let result = 0;

			while (result === 0 && i < numberOfProperties)
			{
				const propertyGetter = props[i];
				const direction = directions[i];

				result = this.compareItemNodes(
					nodeA, nodeB, propertyGetter, direction.ascOrdering, direction.nullsOrdering
				);

				i += 1;
			}

			return result;
		};
	}

	static compareItemNodes(
		nodeA: ItemNode,
		nodeB: ItemNode,
		propertyGetter: string,
		ascOrdering: boolean,
		nullsOrdering: boolean
	)
	{
		const itemA = nodeA.getItem();
		const itemB = nodeB.getItem();

		itemA.getCustomData().get();

		const valueA = itemA[propertyGetter]();
		const valueB = itemB[propertyGetter]();

		let result = 0;

		if (valueA !== null && valueB === null)
		{
			result = nullsOrdering ? -1 : 1;
		}
		else if (valueA === null && valueB !== null)
		{
			result = nullsOrdering ? 1 : -1;
		}
		else if (valueA === null && valueB === null)
		{
			result = ascOrdering ? -1 : 1;
		}
		else
		{
			if (Type.isString(valueA))
			{
				result = valueA.localeCompare(valueB);
			}
			else
			{
				result = valueA - valueB;
			}
		}

		const sortOrder = ascOrdering ? 1 : -1;

		return result * sortOrder;
	}
}