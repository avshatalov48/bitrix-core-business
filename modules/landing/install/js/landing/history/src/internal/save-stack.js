import {Type} from 'main.core';
import {Main} from 'landing.main';
import asyncJsonParse from './async-json-parse';
import asyncJsonStringify from './async-json-stringify';
import type {History} from '../history';

/**
 * Saves history to storage
 * @param {History} history
 * @return {Promise<History>}
 */
// todo: del
export default function saveStack(history: History): Promise<History>
{
	let currentPageId;

	try
	{
		currentPageId = Main.getInstance().id;
	}
	catch (err)
	{
		currentPageId = -1;
	}

	return asyncJsonParse(window.localStorage.history)
		.then((historyData) => {
			return Type.isPlainObject(historyData) ? historyData : {};
		})
		.then((all) => {
			all[currentPageId] = {};
			all[currentPageId].stack = history.stack;
			all[currentPageId].step = history.step;
			return all;
		})
		.then(asyncJsonStringify)
		.then((allString) => {
			window.localStorage.history = allString;
			return history;
		});
}