/**
 * History entry action for add node.
 * @param {string} state State code.
 * @param {object} entry History entry.
 * @return {Promise}
 */
export default function addNode(state, entry)
{
	// entry.block === null >> designer mode

	return new Promise((resolve, reject) => {
		const tags = (entry.redo || {}).tags || ((entry.undo || {}).tags || []);
		top.BX.onCustomEvent(this, 'Landing:onHistoryAddNode', [tags]);
		resolve();
	});
}