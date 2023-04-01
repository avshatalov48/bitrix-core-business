const {scrollTo, highlight} = BX.Landing.Utils;

/**
 * @param {object} entry
 * @return {Promise}
 */
export default function updateContent(entry)
{
	return BX.Landing.PageObject.getInstance().blocks()
		.then((blocks) => {
			const block = blocks.get(entry.block);
			block.forceInit();

			return scrollTo(block.node)
				.then(() => {
					void highlight(block.node);
					return block.updateContent(entry.params.content, true);
				});
		});
}