import {Type} from 'main.core';

/**
 * Implements interface for works with command of history
 * @param {{id: string, undo: function, redo: function}} options
 */
export default class Command
{
	constructor(options: {id: string, undo: () => {}, redo: () => {}})
	{
		this.id = Type.isStringFilled(options.id) ? options.id : '#invalidCommand';
		this.undo = Type.isFunction(options.undo) ? options.undo : (() => {});
		this.redo = Type.isFunction(options.redo) ? options.redo : (() => {});
	}
}