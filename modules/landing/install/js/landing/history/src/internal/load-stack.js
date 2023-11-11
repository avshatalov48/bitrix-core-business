import {Type, Text} from 'main.core';
import {Main} from 'landing.main';
import type {History} from '../history';

/**
 * Loads history from storage
 * @param {History} history
 * @return {Promise<History>}
 */
export default function loadStack(history: History): Promise<History>
{
	return BX.Landing.Backend.getInstance()
		.action(
			history.getLoadBackendActionName(),
			history.getLoadBackendParams(),
		)
		.then((data: {stack: {[number]: string}, stackCount: number, step: number}) => {
			history.stack = Type.isObject(data.stack) ? data.stack : {};
			history.stackCount = Text.toNumber(data.stackCount);
			history.step = Math.min(Text.toNumber(data.step), history.stackCount);

			return history;
		})
		.catch((e) => {
			return history;
		});
}