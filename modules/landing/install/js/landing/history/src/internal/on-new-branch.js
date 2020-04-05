import type {History} from '../history';

/**
 * Calls on history creates new branch
 * @param {History} history
 * @return {Promise<History>}
 */
export default function onNewBranch(history: History): Promise<History>
{
	const rootWindow = BX.Landing.PageObject.getRootWindow();
	BX.onCustomEvent(rootWindow.window, 'BX.Landing.History:newBranch', [history]);
	return Promise.resolve(history);
}