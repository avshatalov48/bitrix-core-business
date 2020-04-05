import {Type} from 'main.core';
import asyncJsonParse from './async-json-parse';
import asyncJsonStringify from './async-json-stringify';
import type {History} from '../history';

/**
 * Removes page history from storage
 * @param {int} pageId
 * @param {History} history
 * @return {Promise<History>}
 */
export default function removePageHistory(pageId, history: History): Promise<History>
{
	return asyncJsonParse(window.localStorage.history)
		.then((historyData) => {
			return Type.isPlainObject(historyData) ? historyData : {};
		})
		.then((all) => {
			if (pageId in all)
			{
				delete all[pageId];
			}

			return all;
		})
		.then(asyncJsonStringify)
		.then((allString) => {
			window.localStorage.history = allString;
			return history;
		});
}
