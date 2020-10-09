import { Type } from 'main.core';

export default class ItemCollection<T>
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
			index = this.searchIndexToInsert(item);
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

	getItems(): Array<T>
	{
		return this.items;
	}

	searchIndexToInsert(value: T): number
	{
		let low = 0;
		let high = this.items.length;
		while (low < high)
		{
			const mid = Math.floor((low + high) / 2);
			if (this.comparator(this.items[mid], value) >= 0)
			{
				low = mid + 1;
			}
			else
			{
				high = mid;
			}
		}

		return low;
	}
}