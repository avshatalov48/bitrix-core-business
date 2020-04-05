const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {string} state
 * @param {object} entry
 * @return {Promise}
 */
export default function editLink(state, entry)
{
	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.block);

			if (!block)
			{
				return Promise.reject();
			}

			block.forceInit();
			const node = block.nodes.getBySelector(entry.selector);

			if (!node)
			{
				return Promise.reject();
			}

			return scrollTo(node.node)
				.then(highlight.bind(null, node.node))
				.then(() => {
					return node.setValue(entry[state], false, true);
				});
		});
}