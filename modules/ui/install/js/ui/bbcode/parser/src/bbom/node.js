import { Type } from 'main.core';

type NodeOptions = {
	name: string,
	value?: string | number,
	attributes?: {[key: string]: any},
	children?: Array<any>,
	parent?: any,
};

export class Node
{
	name: string = '';
	value: string | number = '';
	attributes: {[key: string]: any} = {};
	children: Array<any> = [];
	parent: any = null;

	constructor(options: NodeOptions = {})
	{
		this.setName(options.name);
		this.setValue(options.value);
		this.setAttributes(options.attributes);
		this.setChildren(options.children);
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

	getAttribute(name: string): any
	{
		return this.attributes[name];
	}

	getAttributes(): { [key: string]: any }
	{
		return { ...this.attributes };
	}

	setChildren(children: Array<any>)
	{
		if (Type.isArray(children))
		{
			this.children = [...children];
		}
	}

	appendChild(...children: Array<any>)
	{
		this.children.push(...children);
	}

	replaceChild(targetNode, ...children: Array<any>)
	{
		this.children = this.children.flatMap((node) => {
			if (node === targetNode)
			{
				return children;
			}

			return node;
		});
	}

	getChildren(): Array<any>
	{
		return [...this.children];
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
		const children = this.getChildren()
			.map((child) => {
				return child.toString();
			})
			.join('');

		// eslint-disable-next-line sonarjs/no-nested-template-literals
		return `[${this.getName()}${valueString}${attributes ? ` ${attributes}` : ''}]${children}[/${this.getName()}]`;
	}
}
