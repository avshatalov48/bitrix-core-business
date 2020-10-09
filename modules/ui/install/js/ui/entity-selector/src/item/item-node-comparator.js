import { Type } from 'main.core';
import type ItemNode from './item-node';

export default class ItemNodeComparator
{
	static makeComparator(orderProperty: string, orderDirection: 'asc' | 'desc')
	{
		const sortOrder = orderDirection === 'desc' ? 1 : -1;

		return function(nodeA: ItemNode, nodeB: ItemNode) {
			const itemA = nodeA.getItem();
			const itemB = nodeB.getItem();

			const valueA = itemA[orderProperty];
			const valueB = itemB[orderProperty];

			let result = 0;
			if (Type.isString(valueA))
			{
				result = valueA.localeCompare(valueB);
			}
			else
			{
				if (Type.isNull(valueA) || Type.isNull(valueB))
				{
					result = valueA === valueB ? 0 : (Type.isNull(valueA) ? 1 : -1);
				}
				else
				{
					result = (valueA < valueB) ? -1 : (valueA > valueB ? 1 : 0);
				}
			}

			return result * sortOrder;
		};
	}

	static makeMultipleComparator(order: {[key: string]: 'asc' | 'desc'})
	{
		const props = Object.keys(order);

		return (a, b) => {
			let i = 0;
			let result = 0;
			const numberOfProperties = props.length;

			while (result === 0 && i < numberOfProperties)
			{
				const orderProperty = props[i];
				const orderDirection = order[props[i]];

				result = this.makeComparator(orderProperty, orderDirection)(a, b);
				i += 1;
			}

			return result;
		};
	}
}