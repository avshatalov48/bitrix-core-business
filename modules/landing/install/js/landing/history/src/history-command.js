import {Type} from 'main.core';

/**
 * Implements interface for works with command of history
 * @param {{id: string, undo: function, redo: function}} options
 */
export default class Command
{
	constructor(options: {id: string, command: () => {}})
	{
		this.id = Type.isStringFilled(options.id) ? options.id : '#invalidCommand';
		this.command = Type.isFunction(options.command) ? options.command : (() => {});
	}
}