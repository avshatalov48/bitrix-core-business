import {Type} from 'main.core';

/**
 * Implements interface for works with command of history
 * @param {{id: string, undo: function, redo: function}} options
 */
export default class Command
{
	id: string;
	command: () => {};
	onBeforeCommand: () => {};

	constructor(options: {
		id: string,
		command: () => {},
		onBeforeCommand: () => {},
	})
	{
		this.id = Type.isStringFilled(options.id) ? options.id : '#invalidCommand';
		this.command = Type.isFunction(options.command) ? options.command : (() => {});
		this.onBeforeCommand =
			Type.isFunction(options.onBeforeCommand)
				? options.onBeforeCommand
				: () => {
					return Promise.resolve()
				};
	}
}