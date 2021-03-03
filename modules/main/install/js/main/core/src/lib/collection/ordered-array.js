import Type from '../type';

export default class OrderedArray<T>
{
	comparator: Function = null;
	items: Array<T> = [];

	constructor(comparator: Function = null)
	{
		this.comparator = Type.isFunction(comparator) ? comparator : null;
	}

	add(item: T): number
	{
		let index = -1;
		if (this.comparator)
		{
			index = this.#searchIndexToInsert(item);
			this.items.splice(index, 0, item);
		}
		else
		{
			this.items.push(item);
		}

		return index;
	}

	has(item: T): boolean
	{
		return this.items.includes(item);
	}

	getIndex(item: T): number
	{
		return this.items.indexOf(item);
	}

	getByIndex(index: number): ?T
	{
		if (Type.isNumber(index) && index >= 0)
		{
			const item = this.items[index];
			return Type.isUndefined(item) ? null : item;
		}

		return null;
	}

	getFirst(): ?T
	{
		const first = this.items[0];

		return Type.isUndefined(first) ? null : first;
	}

	getLast(): ?T
	{
		const last = this.items[this.count() - 1];

		return Type.isUndefined(last) ? null : last;
	}

	count(): number
	{
		return this.items.length;
	}

	delete(item: T): boolean
	{
		const index = this.getIndex(item);
		if (index !== -1)
		{
			this.items.splice(index, 1);

			return true;
		}

		return false;
	}

	clear(): void
	{
		this.items = [];
	}

	[Symbol.iterator]()
	{
		return this.items[Symbol.iterator]();
	}

	forEach(callbackfn: (value: T, index: number, array: T[]) => void, thisArg?: any): void
	{
		return this.items.forEach(callbackfn, thisArg);
	}

	getAll(): Array<T>
	{
		return this.items;
	}

	getComparator(): ?Function
	{
		return this.comparator;
	}

	sort(): void
	{
		const comparator = this.getComparator();
		if (comparator === null)
		{
			return;
		}

		/*
		Simple implementation
		this.items.sort((item1, item2) => {
			return comparator(item1, item2);
		});
		*/

		// For stable sorting https://v8.dev/features/stable-sort
		const length = this.items.length;
		const indexes = new Array(length);
		for (let i = 0; i < length; i++)
		{
			indexes[i] = i;
		}

		// If the comparator returns zero, use the original indexes
		indexes.sort((index1, index2) => {
			return comparator(this.items[index1], this.items[index2]) || (index1 - index2);
		});

		for (let i = 0; i < length; i++)
		{
			indexes[i] = this.items[indexes[i]];
		}

		for (let i = 0; i < length; i++)
		{
			this.items[i] = indexes[i];
		}
	}

	#searchIndexToInsert(value: T): number
	{
		let low = 0;
		let high = this.items.length;
		while (low < high)
		{
			const mid = Math.floor((low + high) / 2);
			if (this.comparator(this.items[mid], value) >= 0)
			{
				high = mid;
			}
			else
			{
				low = mid + 1;
			}
		}

		return low;
	}
}