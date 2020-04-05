const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {string} state
 * @param {object} entry
 * @return {Promise}
 */
export default function updateBlockState(state, entry)
{
	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.block);
			block.forceInit();

			return scrollTo(block.node)
				.then(() => {
					void highlight(block.node);
					block.updateBlockState(BX.clone(entry[state]), true);
				});
		});
}