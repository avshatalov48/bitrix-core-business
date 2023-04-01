/**
 * History entry action for remove node.
 * @param {object} entry History entry.
 * @return {Promise}
 */
export default function removeNode(entry)
{
	// entry.block === null >> designer mode

	return new Promise((resolve, reject) => {
		const tags = entry.params.tags || {};
		top.BX.onCustomEvent(this, 'Landing:onHistoryRemoveNode', [tags]);
		resolve();
	});
}