import { Type } from 'main.core';
import type { BBCodeContentNode } from '../../nodes/node';
import { typeof BBCodeScheme } from '../bbcode-scheme';

export type BBCodeNodeConverter = (node: BBCodeContentNode, scheme: BBCodeScheme) => BBCodeContentNode | Array<BBCodeContentNode> | null;
export type BBCodeNodeStringifier = (node: BBCodeContentNode, scheme: BBCodeScheme) => string;
export type BBCodeNodeSerializer = (node: BBCodeContentNode, scheme: BBCodeScheme) => any;
export type BBCodeNodeName = string;
export type BBCodeGroupName = string;

export type BBCodeNodeSchemeOptions = {
	name: BBCodeNodeName | Array<BBCodeNodeName>,
	group: BBCodeGroupName | Array<BBCodeGroupName>,
	stringify?: BBCodeNodeStringifier,
	serialize?: BBCodeNodeSerializer,
	allowedIn?: Array<BBCodeNodeName>,
};

export class BBCodeNodeScheme
{
	name: Array<BBCodeNodeName> = [];
	group: Array<BBCodeGroupName> = [];
	stringifier: BBCodeNodeStringifier | null = null;
	serializer: BBCodeNodeSerializer | null = null;
	allowedIn: Array<BBCodeNodeName> = [];

	constructor(options: BBCodeNodeSchemeOptions)
	{
		if (!Type.isPlainObject(options))
		{
			throw new TypeError('options is not a object');
		}

		if (
			!Type.isArrayFilled(this.name)
			&& !Type.isArrayFilled(options.name)
			&& !Type.isStringFilled(options.name)
		)
		{
			throw new TypeError('options.name is not specified');
		}

		this.setGroup(options.group);
		this.setName(options.name);
		this.setAllowedIn(options.allowedIn);
		this.setStringifier(options.stringify);
		this.setSerializer(options.serialize);
	}

	setName(name: BBCodeNodeSchemeOptions['name'])
	{
		if (Type.isStringFilled(name))
		{
			this.name = [name];
		}

		if (Type.isArrayFilled(name))
		{
			this.name = name;
		}
	}

	getName(): Array<string>
	{
		return [...this.name];
	}

	removeName(...names: Array<BBCodeNodeName>)
	{
		this.setName(
			this.getName().filter((name: BBCodeNodeName) => {
				return !names.includes(name);
			}),
		);
	}

	setGroup(name: BBCodeNodeSchemeOptions['group'])
	{
		if (Type.isStringFilled(name))
		{
			this.group = [name];
		}

		if (Type.isArrayFilled(name))
		{
			this.group = name;
		}
	}

	removeGroup(...groups: Array<BBCodeGroupName>)
	{
		this.setGroup(
			this.getGroup().filter((group: BBCodeGroupName) => {
				return !groups.includes(group);
			}),
		);
	}

	getGroup(): Array<BBCodeGroupName>
	{
		return [...this.group];
	}

	hasGroup(groupName: string): boolean
	{
		return this.getGroup().includes(groupName);
	}

	setStringifier(stringifier: BBCodeNodeStringifier | null)
	{
		if (Type.isFunction(stringifier) || Type.isNull(stringifier))
		{
			this.stringifier = stringifier;
		}
	}

	getStringifier(): BBCodeNodeStringifier | null
	{
		return this.stringifier;
	}

	setSerializer(serializer: BBCodeNodeSerializer | null)
	{
		if (Type.isFunction(serializer) || Type.isNull(serializer))
		{
			this.serializer = serializer;
		}
	}

	getSerializer(): BBCodeNodeSerializer | null
	{
		return this.serializer;
	}

	setAllowedIn(allowedParents: Array<BBCodeNodeName>)
	{
		if (Type.isArray(allowedParents))
		{
			this.allowedIn = [...allowedParents];
		}
	}

	getAllowedIn(): Array<BBCodeNodeName>
	{
		return [...this.allowedIn];
	}

	isAllowedIn(tagName: string): boolean
	{
		const allowedIn: Array<BBCodeNodeName> = this.getAllowedIn();

		return (
			!Type.isArrayFilled(allowedIn)
			|| (
				Type.isArrayFilled(allowedIn)
				&& allowedIn.includes(tagName)
			)
		);
	}
}
