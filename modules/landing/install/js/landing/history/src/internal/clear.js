import {INIT, RESOLVED} from './constants';
import type {History} from '../history';

/**
 * Clears history stack
 * @param {History} history
 * @return {Promise<History>}
 */
export default function clear(history: History): Promise<History>
{
	history.stack = [];
 	history.step = -1;
	history.commandState = RESOLVED;
	return Promise.resolve(history);
}