import {INIT, RESOLVED} from './constants';
import type {History} from '../history';

/**
 * Clears history stack
 * @param {History} history
 * @return {Promise<History>}
 */
export default function clear(history: History): Promise<History>
{
	history.stack = null;
	history.commandState = RESOLVED;

	return Promise.resolve(history);
}