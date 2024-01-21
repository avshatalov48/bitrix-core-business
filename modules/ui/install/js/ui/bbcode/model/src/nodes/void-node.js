import { Type } from 'main.core';

type VoidNodeOptions = {
	name: string,
	value?: string | number,
	attributes?: {[key: string]: any},
	parent?: any,
};

export class VoidNode
{
	name: string = '';
	value: string | number = '';
	attributes: {[key: string]: any} = {};
	parent: any = null;

	constructor(options: VoidNodeOptions = {})
	{
		this.setName(options.name);
		this.setValue(options.value);
		this.setAttributes(options.attributes);
		this.setParent(options.parent);
	}

	setName(name: string)
	{
		if (Type.isStringFilled(name))
		{
			this.name = name;
		}
	}

	getName(): string
	{
		return this.name;
	}

	setValue(value: string | number)
	{
		if (Type.isStringFilled(value) || Type.isNumber(value))
		{
			this.value = value;
		}
		else
		{
			this.value = '';
		}
	}

	getValue(): string | number
	{
		return this.value;
	}

	setAttributes(attributes: { [key: string]: any })
	{
		if (Type.isPlainObject(attributes))
		{
			this.attributes = { ...attributes };
		}
	}

	setAttribute(name: string, value: any)
	{
		if (Type.isStringFilled(name))
		{
			if (Type.isNil(value))
			{
				delete this.attributes[name];
			}
			else
			{
				this.attributes[name] = value;
			}
		}
	}

	getAttributes(): { [key: string]: any }
	{
		return { ...this.attributes };
	}

	getAttribute(key: string): any
	{
		return this.attributes[key];
	}

	setParent(node: any)
	{
		this.parent = node;
	}

	getParent(): any
	{
		return this.parent;
	}

	toString(): string
	{
		const value = this.getValue();
		const valueString = value ? `=${value}` : '';
		const attributes = Object
			.entries(this.getAttributes())
			.map(([key, attrValue]) => {
				return attrValue ? `${key}=${attrValue}` : key;
			})
			.join(' ');

		return `[${this.getName()}${valueString}${attributes ? ` ${attributes}` : ''}]`;
	}
}
