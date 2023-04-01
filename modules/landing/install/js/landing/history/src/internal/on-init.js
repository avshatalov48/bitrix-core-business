import type {History} from '../history';

/**
 * Calls on init history object
 * @param history
 * @return {Promise<History>}
 */
export default function onInit(history: History): Promise<History>
{
	const rootWindow = BX.Landing.PageObject.getRootWindow();
	BX.onCustomEvent(rootWindow.window, 'BX.Landing.History:init', [history]);

	return Promise.resolve(history);
}