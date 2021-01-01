import {Type} from 'main.core';

/**
 * @memberOf BX.Landing.Collection
 */
export class BaseCollection extends Array
{
	add(value: any)
	{
		if (!this.includes(value))
		{
			this.push(value);
		}
	}

	remove(value: any)
	{
		const index = this.getIndex(value);
		if (index > -1)
		{
			this.splice(index, 1);
		}
	}

	getIndex(value: any): number
	{
		return this.indexOf(value);
	}

	/**
	 * @deprecated
	 * @see this.includes()
	 */
	contains(value: any): boolean
	{
		return this.includes(value);
	}

	isChanged(): boolean
	{
		return this.some((item) => item.isChanged());
	}

	fetchValues(): {[key: string]: any}
	{
		return this.reduce((acc, item) => {
			if (!item.selector.startsWith('-1'))
			{
				if (Type.isFunction(item.getAttrValue))
				{
					acc[item.selector] = item.getAttrValue();
				}
				else
				{
					acc[item.selector] = item.getValue();
				}
			}

			return acc;
		}, {});
	}

	fetchAdditionalValues(): {[key: string]: any}
	{
		return this.reduce((acc, item) => {
			if (!item.selector.startsWith('-1') && item.getAdditionalValue)
			{
				const values = item.getAdditionalValue();
				if (!Type.isNil(values))
				{
					acc[item.selector] = values;
				}
			}

			return acc;
		}, {});
	}

	fetchChanges(): BaseCollection
	{
		return this.filter((item) => {
			return 'isChanged' in item && 'getValue' in item && item.isChanged();
		});
	}

	clear()
	{
		this.splice(0, this.length);
	}

	toArray(): Array<any>
	{
		return [...this];
	}

	get(id: any): any
	{
		return this.find((item) => `${item.id}` === `${id}`);
	}

	getByLayout(layout: HTMLElement): any
	{
		return this.find((item) => {
			return Type.isObject(item) && item.layout === layout;
		});
	}
}