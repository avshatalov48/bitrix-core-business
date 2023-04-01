import {Type} from 'main.core';

export default class Entry
{
	constructor(options)
	{
		this.block = options.block;
		this.selector = options.selector;
		this.command = Type.isStringFilled(options.command) ? options.command : '#invalidCommand';
		this.params = options.params;
	}
}