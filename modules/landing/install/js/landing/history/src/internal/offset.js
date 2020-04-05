import {PENDING, REDO, RESOLVED, UNDO} from './constants';
import type {History} from '../history';

/**
 * Offsets history by offset length
 * @param {History} history
 * @param {Integer} offsetValue
 */
export default function offset(history: History, offsetValue: number): Promise<History>
{
	if (history.commandState === PENDING)
	{
		return Promise.resolve(history);
	}

	let position = history.position + offsetValue;
	let {state} = history;

	if (offsetValue < 0 && history.state !== UNDO)
	{
		position += 1;
		state = UNDO;
	}

	if (offsetValue > 0 && history.state !== REDO)
	{
		position -= 1;
		state = REDO;
	}

	if (position <= history.stack.length - 1 && position >= 0)
	{
		history.position = position;
		history.state = state;

		const entry = history.stack[position];

		if (entry)
		{
			const command = history.commands[entry.command];

			if (command)
			{
				history.commandState = PENDING;

				return command[state](entry)
					.then(() => {
						history.commandState = RESOLVED;
						return history;
					})
					.catch(() => {
						history.commandState = RESOLVED;
						return history[state === UNDO ? 'undo' : 'redo']();
					});
			}
		}
	}

	return Promise.resolve(history);
}