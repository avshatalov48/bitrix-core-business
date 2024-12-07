const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
const editNode = function (entry)
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
				.then(highlight.bind(null, node.node, editNode.useRangeRect))
				.then(() => {
					return node.setValue(entry.params.value, false, true);
				});
		});
};

editNode.useRangeRect = true;

export default editNode;
