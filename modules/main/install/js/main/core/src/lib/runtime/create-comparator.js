import Type from '../type';

export default function createComparator(fields, orders = [])
{
	return (a, b) => {
		const field = fields[0];
		const order = orders[0] || 'asc';

		if (Type.isUndefined(field))
		{
			return 0;
		}

		let valueA = a[field];
		let valueB = b[field];

		if (Type.isString(valueA) && Type.isString(valueB))
		{
			valueA = valueA.toLowerCase();
			valueB = valueB.toLowerCase();
		}

		if (valueA < valueB)
		{
			return order === 'asc' ? -1 : 1;
		}

		if (valueA > valueB)
		{
			return order === 'asc' ? 1 : -1;
		}

		return createComparator(fields.slice(1), orders.slice(1))(a, b);
	};
}