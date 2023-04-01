import {Type, Text} from 'main.core';
import {Main} from 'landing.main';
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

	// todo: if design - no?

	return BX.Landing.Backend.getInstance()
		.action(
			"History::getForLanding",
			{lid: currentPageId},
		)
		.then((data: {stackCount: number, step: number}) => {
			history.stack = Text.toNumber(data.stackCount);
			history.step = Math.min(Text.toNumber(data.step), history.stack);

			return history;
		})
		.catch((e) => {
			return history;
		});
}