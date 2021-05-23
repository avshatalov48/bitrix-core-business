import OrderedArray from '../../src/lib/collections/ordered-array';
import Type from '../../src/lib/type';

const compareItems = (
	itemA,
	itemB,
	orderProperty: string,
	ascOrdering: boolean,
	nullsOrdering: boolean
) => {

	const valueA = itemA[orderProperty];
	const valueB = itemB[orderProperty];

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
};

const makeMultipleComparator = (order: {[key: string]: 'asc' | 'desc' }) => {

	const props = Object.keys(order);
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

	return (itemA, itemB) => {
		let i = 0;
		let result = 0;

		while (result === 0 && i < numberOfProperties)
		{
			const property = props[i];
			const direction = directions[i];

			result = compareItems(itemA, itemB, property, direction.ascOrdering, direction.nullsOrdering);
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

	const products = [
		{ id: 1, name: 'Bread', sort: 3, nullProperty: null },
		{ id: 2, name: 'Sausage', sort: 4, nullProperty: null },
		{ id: 3, name: 'Bacon', sort: 1, nullProperty: null },
		{ id: 4, name: 'Cheese', sort: null, nullProperty: null },
		{ id: 5, name: 'Milk', sort: 2, nullProperty: null },
		{ id: 6, name: 'Butter', sort: null, nullProperty: null },
		{ id: 7, name: 'Eggs', sort: 5, nullProperty: null },
		{ id: 8, name: 'Cucumber', sort: null, nullProperty: null },
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

	it('Should sort elements with nulls', () => {

		const orderByAsc = new OrderedArray(makeMultipleComparator({ sort: 'asc' }));
		const orderByAsc2 = new OrderedArray(makeMultipleComparator({ sort: 'asc nulls last' }));
		const orderByAsc3 = new OrderedArray(makeMultipleComparator({ sort: 'asc nulls first' }));

		products.forEach(element => {
			orderByAsc.add(element);
			orderByAsc2.add(element);
			orderByAsc3.add(element);
		});

		assert.equal(
			orderByAsc.getAll().map(element => element.name).join('|'),
			'Bacon|Milk|Bread|Sausage|Eggs|Cheese|Butter|Cucumber'
		);

		assert.equal(
			orderByAsc2.getAll().map(element => element.name).join('|'),
			'Bacon|Milk|Bread|Sausage|Eggs|Cheese|Butter|Cucumber'
		);

		assert.equal(
			orderByAsc3.getAll().map(element => element.name).join('|'),
			'Cheese|Butter|Cucumber|Bacon|Milk|Bread|Sausage|Eggs'
		);

		const orderByDesc = new OrderedArray(makeMultipleComparator({ sort: 'desc' }));
		const orderByDesc2 = new OrderedArray(makeMultipleComparator({ sort: 'desc nulls first' }));
		const orderByDesc3 = new OrderedArray(makeMultipleComparator({ sort: 'desc nulls last' }));

		products.forEach(element => {
			orderByDesc.add(element);
			orderByDesc2.add(element);
			orderByDesc3.add(element);
		});

		assert.equal(
			orderByDesc.getAll().map(element => element.name).join('|'),
			'Cheese|Butter|Cucumber|Eggs|Sausage|Bread|Milk|Bacon'
		);

		assert.equal(
			orderByDesc2.getAll().map(element => element.name).join('|'),
			'Cheese|Butter|Cucumber|Eggs|Sausage|Bread|Milk|Bacon'
		);

		assert.equal(
			orderByDesc3.getAll().map(element => element.name).join('|'),
			'Eggs|Sausage|Bread|Milk|Bacon|Cheese|Butter|Cucumber'
		);

		const orderByAscNullable = new OrderedArray(makeMultipleComparator({ nullProperty: 'asc' }));
		const orderByDescNullable = new OrderedArray(makeMultipleComparator({ nullProperty: 'desc' }));

		products.forEach(element => {
			orderByAscNullable.add(element);
			orderByDescNullable.add(element);
		});

		assert.equal(
			orderByAscNullable.getAll().map(element => element.id).join(''),
			'12345678'
		);
		assert.equal(
			orderByDescNullable.getAll().map(element => element.id).join(''),
			'12345678'
		);
	});
});