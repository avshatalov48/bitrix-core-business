import type {History} from '../history';

/**
 * Calls on history actualize event
 * @param {BX.Landing.History} history
 * @return {Promise<BX.Landing.History>}
 */
export default function onActualize(history: History): Promise<History>
{
	const rootWindow = BX.Landing.PageObject.getRootWindow();
	BX.onCustomEvent(rootWindow.window, 'BX.Landing.History:actualize', [history]);
	return Promise.resolve(history);
}