import OrderedArray from '../../src/lib/collection/ordered-array';
import Type from '../../src/lib/type';

const makeComparator = (orderProperty: string, orderDirection: 'asc' | 'desc') => {
	const sortOrder = orderDirection === 'desc' ? -1 : 1;

	return function(itemA, itemB) {

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
};

const makeMultipleComparator = (order: {[key: string]: 'asc' | 'desc'}) => {
	const props = Object.keys(order);

	return (a, b) => {
		let i = 0;
		let result = 0;
		const numberOfProperties = props.length;

		while (result === 0 && i < numberOfProperties)
		{
			const orderProperty = props[i];
			const orderDirection = order[props[i]];

			result = makeComparator(orderProperty, orderDirection)(a, b);
			i += 1;
		}

		return result;
	};
};

const sortByName = function(a, b) {
	const nameA = a.name.toUpperCase(); // ignore upper and lowercase
	const nameB = b.name.toUpperCase(); // ignore upper and lowercase
	if (nameA < nameB)
	{
		return -1;
	}

	if (nameA > nameB)
	{
		return 1;
	}

	return 0;
};

const sortByNumber = (a, b) => {
	return a.sort - b.sort;
};

describe('OrderedArray', () => {

	const elements = [
		{ name: 'Blue', sort: 3 },
		{ name: 'Yellow', sort: 4 },
		{ name: 'Orange', sort: 2 },
		{ name: 'White', sort: 6 },
		{ name: 'Black', sort: 5 },
		{ name: 'Red', sort: 1 },
	];

	const elements2 = [
		{ name: 'Blue', sort: 3 },
		{ name: 'Yellow', sort: 4 },
		{ name: 'Orange', sort: 3 },
		{ name: 'White', sort: 6 },
		{ name: 'Black', sort: 4 },
		{ name: 'Red', sort: 1 },
		{ name: 'Pink', sort: 6 },
		{ name: 'Green', sort: 3 },
		{ name: 'Purple', sort: 1 },
		{ name: 'Brown', sort: 6 },
		{ name: 'Grey', sort: 7 },
	];

	const elements3 = [
		{ name: 'Blue', sort: 3, id: 1 },
		{ name: 'Yellow', sort: 4, id: 2 },
		{ name: 'Orange', sort: 3, id: 3 },
		{ name: 'White', sort: 6, id: 4 },
		{ name: 'White', sort: 6, id: 5 },
		{ name: 'Black', sort: 4, id: 6 },
		{ name: 'Red', sort: 1, id: 7 },
		{ name: 'Pink', sort: 6, id: 8 },
		{ name: 'Pink', sort: 6, id: 9 },
		{ name: 'Green', sort: 3, id: 10 },
		{ name: 'Purple', sort: 1, id: 11 },
		{ name: 'Brown', sort: 6, id: 12 },
		{ name: 'Grey', sort: 7, id: 13 },
		{ name: 'Grey', sort: 7, id: 14 },
		{ name: 'Grey', sort: 7, id: 15 },
	];

	it('Should behave like a simple array with a comparator', () => {
		const names = new OrderedArray();
		elements.forEach(element => {
			names.add(element);
		});

		assert.equal(
			elements.map(element => element.name).join('|'),
			names.getAll().map(element => element.name).join('|'),
		);
	});

	it('Should sort elements using a comparator', () => {

		const names = new OrderedArray(sortByName);
		elements.forEach(element => {
			names.add(element);
		});

		assert.equal(
			names.getAll().map(element => element.name).join('|'),
			'Black|Blue|Orange|Red|White|Yellow'
		);

		const numbers = new OrderedArray(sortByNumber);
		elements.forEach(element => {
			numbers.add(element);
		});

		assert.equal(
			numbers.getAll().map(element => element.sort).join(''),
			'123456'
		);

		const colors = new OrderedArray(makeMultipleComparator({ sort: 'asc', name: 'asc' }));
		elements2.forEach(element => {
			colors.add(element);
		});

		assert.equal(
			colors.getAll().map(element => element.sort).join(''),
			'11333446667'
		);

		assert.equal(
			colors.getAll().map(element => element.name).join('|'),
			'Purple|Red|Blue|Green|Orange|Black|Yellow|Brown|Pink|White|Grey'
		);

		const colors2 = new OrderedArray(makeMultipleComparator({ sort: 'desc', name: 'desc' }));
		elements2.forEach(element => {
			colors2.add(element);
		});

		assert.equal(
			colors2.getAll().map(element => element.sort).join(''),
			'76664433311'
		);

		assert.equal(
			colors2.getAll().map(element => element.name).join('|'),
			'Grey|White|Pink|Brown|Yellow|Black|Orange|Green|Blue|Red|Purple'
		);
	});

	it('Should resort elements', () => {

		const numbers = new OrderedArray(sortByNumber);
		elements.forEach(element => {
			numbers.add(element);
		});

		assert.equal(numbers.getFirst().name, 'Red');
		assert.equal(numbers.getLast().name, 'White');
		assert.equal(numbers.getByIndex(2).name, 'Blue');

		assert.equal(
			numbers.getAll().map(element => element.sort).join(''),
			'123456'
		);

		numbers.getFirst().sort = 10;
		numbers.getLast().sort = 0;
		numbers.sort();

		assert.equal(numbers.getFirst().name, 'White');
		assert.equal(numbers.getLast().name, 'Red');
		assert.equal(numbers.getByIndex(2).name, 'Blue');

		const colors = new OrderedArray(makeMultipleComparator({ sort: 'asc', name: 'desc' }));
		elements3.forEach(element => {
			colors.add(element);
		});

		assert.equal(
			colors.getAll().map(element => element.sort).join(''),
			'113334466666777'
		);

		assert.equal(
			colors.getAll().map(element => element.id).join('|'),
			'7|11|3|10|1|2|6|5|4|9|8|12|15|14|13'
		);

		assert.equal(
			colors.getAll().map(element => element.name).join('|'),
			'Red|Purple|Orange|Green|Blue|Yellow|Black|White|White|Pink|Pink|Brown|Grey|Grey|Grey'
		);

		colors.getByIndex(0).sort = 8;
		colors.getByIndex(9).sort = 5;
		colors.getByIndex(3).sort = 6;

		colors.sort();

		assert.equal(
			colors.getAll().map(element => element.sort).join(''),
			'133445666667778'
		);

		assert.equal(
			colors.getAll().map(element => element.id).join('|'),
			'11|3|1|2|6|9|5|4|8|10|12|15|14|13|7'
		);

		assert.equal(
			colors.getAll().map(element => element.name).join('|'),
			'Purple|Orange|Blue|Yellow|Black|Pink|White|White|Pink|Green|Brown|Grey|Grey|Grey|Red'
		);

		colors.getByIndex(8).sort = 1;
		colors.getByIndex(5).sort = 3;
		colors.getByIndex(4).sort = 5;

		colors.sort();

		assert.equal(
			colors.getAll().map(element => element.sort).join(''),
			'113334566667778'
		);

		assert.equal(
			colors.getAll().map(element => element.id).join('|'),
			'11|8|9|3|1|2|6|5|4|10|12|15|14|13|7'
		);

		assert.equal(
			colors.getAll().map(element => element.name).join('|'),
			'Purple|Pink|Pink|Orange|Blue|Yellow|Black|White|White|Green|Brown|Grey|Grey|Grey|Red'
		);
	});

});