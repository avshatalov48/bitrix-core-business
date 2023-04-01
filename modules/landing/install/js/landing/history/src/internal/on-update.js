import type {History} from '../history';

/**
 * Calls on update history stack
 * @param {History} history
 * @return {Promise<History>}
 */
export default function onUpdate(history: History): Promise<History>
{
	const rootWindow = BX.Landing.PageObject.getRootWindow();
	BX.onCustomEvent(rootWindow.window, 'BX.Landing.History:update', [history]);

	return Promise.resolve(history);
}