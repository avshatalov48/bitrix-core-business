/**
 * History entry action for add node.
 * @param {object} entry History entry.
 * @return {Promise}
 */
export default function addNode(entry)
{
	// entry.block === null >> designer mode

	return new Promise((resolve, reject) => {
		const tags = entry.params.tags || {};
		top.BX.onCustomEvent(this, 'Landing:onHistoryAddNode', [tags]);
		resolve();
	});
}