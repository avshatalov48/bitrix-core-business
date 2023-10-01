import { Type } from 'main.core';

type RootNodeOptions = {
	children?: Array<any>,
};

export class RootNode
{
	children: Array<any> = [];

	constructor(options: RootNodeOptions = {})
	{
		this.setChildren(options.children);
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

	toString(): string
	{
		return this.getChildren()
			.map((child) => {
				return child.toString();
			})
			.join('');
	}
}
