import {Type, Text} from 'main.core';
import {Main} from 'landing.main';
import asyncJsonParse from './async-json-parse';
import {MAX_ENTRIES_COUNT} from './constants';
import type {History} from '../history';

/**
 * Loads history from storage
 * @param {History} history
 * @return {Promise<History>}
 */
export default function loadStack(history: History)
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
			if (Type.isPlainObject(historyData) && currentPageId in historyData)
			{
				return historyData[currentPageId];
			}

			return Promise.reject();
		})
		.then((landingData) => {
			Object.keys(landingData.stack).forEach((key, index) => {
				history.stack.push(new BX.Landing.History.Entry(landingData.stack[key]));

				if (index >= MAX_ENTRIES_COUNT)
				{
					history.stack.shift();
				}
			});

			history.position = Math.min(Text.toNumber(landingData.position), history.stack.length - 1);
			history.state = landingData.state;
			return history;
		})
		.catch(() => {
			return history;
		});
}